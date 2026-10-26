<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = $pdo ?? ($con ?? null);

function respond(int $status, array $data): never
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    exit;
}

// Check database connection.
if (!$pdo instanceof PDO) {
    respond(500, [
        'success' => false,
        'message' => 'Database connection is unavailable.'
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'success' => false,
        'message' => 'POST method required.'
    ]);
}

try {
    // --------------------------------------------------
    // 1. READ REQUEST
    // --------------------------------------------------

    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        respond(400, [
            'success' => false,
            'message' => 'Invalid request data.'
        ]);
    }

    $saleID = trim((string)($input['saleID'] ?? ''));
    $type = $input['type'] ?? '';
    $reason = trim((string)($input['reason'] ?? ''));
    $notes = trim((string)($input['notes'] ?? ''));
    $items = $input['items'] ?? [];

    // Exchange is not implemented yet.
    if ($type === 'exchange') {
        respond(422, [
            'success' => false,
            'message' => 'Exchange is not available yet. Please use Return only.'
        ]);
    }

    if (
        $saleID === '' ||
        strlen($saleID) > 10 ||
        $type !== 'return'
    ) {
        respond(422, [
            'success' => false,
            'message' => 'Invalid invoice or transaction type.'
        ]);
    }

    if (
        $reason === '' ||
        !is_array($items) ||
        count($items) === 0
    ) {
        respond(422, [
            'success' => false,
            'message' => 'Select products and a reason.'
        ]);
    }

    // --------------------------------------------------
    // 2. VALIDATE REQUEST ITEMS
    // --------------------------------------------------

    $requestedItems = [];
    $seenDetailIDs = [];

    foreach ($items as $item) {
        if (!is_array($item)) {
            respond(422, [
                'success' => false,
                'message' => 'Invalid product data.'
            ]);
        }

        $detailID = trim(
            (string)($item['saleDetailID'] ?? '')
        );

        $quantity = filter_var(
            $item['quantity'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $detailID === '' ||
            !ctype_digit($detailID) ||
            $detailID === '0' ||
            $quantity === false ||
            $quantity < 1
        ) {
            respond(422, [
                'success' => false,
                'message' => 'Invalid product or quantity.'
            ]);
        }

        if (isset($seenDetailIDs[$detailID])) {
            respond(422, [
                'success' => false,
                'message' => 'Duplicate products were submitted.'
            ]);
        }

        $seenDetailIDs[$detailID] = true;

        $requestedItems[] = [
            'saleDetailID' => $detailID,
            'quantity' => $quantity
        ];
    }

    // --------------------------------------------------
    // 3. AUTHENTICATED USER
    // --------------------------------------------------

    $userID = $_SESSION['user']['UserID'] ?? null;

    if (
        !is_scalar($userID) ||
        trim((string)$userID) === '' ||
        strlen(trim((string)$userID)) > 10
    ) {
        respond(401, [
            'success' => false,
            'message' => 'Unable to identify the authenticated user.'
        ]);
    }

    $userID = trim((string)$userID);

    // --------------------------------------------------
    // 4. START TRANSACTION
    // --------------------------------------------------

    $pdo->beginTransaction();

    // Lock invoice to serialize returns for the same sale.
    $saleStmt = $pdo->prepare(
        "SELECT
            SaleID,
            CustomerID,
            TotalAmount,
            Discount,
            MembershipDiscount,
            Tax,
            Status
         FROM sales
         WHERE SaleID = ?
         LIMIT 1
         FOR UPDATE"
    );

    $saleStmt->execute([$saleID]);

    $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        throw new RuntimeException('Invoice not found.');
    }

    if (
        !in_array(
            strtolower((string)$sale['Status']),
            ['completed', 'paid'],
            true
        )
    ) {
        throw new RuntimeException(
            'Only completed or paid sales can be returned.'
        );
    }

    // --------------------------------------------------
    // 5. LOAD SALE DETAILS
    // --------------------------------------------------

    $detailsStmt = $pdo->prepare(
        "SELECT
            SaleDetailID,
            ProductID,
            Quantity,
            UnitPrice
         FROM sale_details
         WHERE SaleID = ?
         FOR UPDATE"
    );

    $detailsStmt->execute([$saleID]);

    $saleDetails = [];

    foreach ($detailsStmt->fetchAll(PDO::FETCH_ASSOC) as $detail) {
        $saleDetails[(string)$detail['SaleDetailID']] = $detail;
    }

    if (!$saleDetails) {
        throw new RuntimeException(
            'No products were found for this invoice.'
        );
    }

    // --------------------------------------------------
    // 6. CALCULATE INVOICE SUBTOTAL
    // --------------------------------------------------

    $subtotalStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(Quantity * UnitPrice), 0)
         FROM sale_details
         WHERE SaleID = ?"
    );

    $subtotalStmt->execute([$saleID]);

    $invoiceSubtotal = (float)$subtotalStmt->fetchColumn();

    if ($invoiceSubtotal <= 0) {
        throw new RuntimeException(
            'Cannot calculate the invoice subtotal.'
        );
    }

    $discount = (float)($sale['Discount'] ?? 0);

    $membershipDiscount = (float)(
        $sale['MembershipDiscount'] ?? 0
    );

    $invoiceTax = (float)($sale['Tax'] ?? 0);

    if (
        $discount < 0 ||
        $membershipDiscount < 0 ||
        $invoiceTax < 0 ||
        $discount > $invoiceSubtotal ||
        $membershipDiscount > ($invoiceSubtotal - $discount)
    ) {
        throw new RuntimeException(
            'Invalid invoice discount or tax.'
        );
    }

    // Match the POS discount order.
    $eligibleAmount = max(
        $invoiceSubtotal - $discount,
        0
    );

    $invoiceTaxableAmount = max(
        $eligibleAmount - $membershipDiscount,
        0
    );

    // Derive effective rates from saved invoice totals.
    $membershipRate = $eligibleAmount > 0
        ? $membershipDiscount / $eligibleAmount
        : 0;

    $taxRate = $invoiceTaxableAmount > 0
        ? $invoiceTax / $invoiceTaxableAmount
        : 0;

    // --------------------------------------------------
    // 7. VALIDATE RETURN QUANTITIES
    // --------------------------------------------------

    $validatedItems = [];
    $selectedGross = 0.0;

    $alreadyStmt = $pdo->prepare(
        "SELECT COALESCE(SUM(srd.Quantity), 0)
         FROM sales_return_details AS srd
         INNER JOIN sales_returns AS sr
             ON sr.ReturnID = srd.ReturnID
         WHERE srd.SaleDetailID = ?
           AND sr.Status = 'Completed'"
    );

    foreach ($requestedItems as $requested) {
        $detailID = $requested['saleDetailID'];
        $quantity = $requested['quantity'];

        if (!isset($saleDetails[$detailID])) {
            throw new RuntimeException(
                'A selected product does not belong to this invoice.'
            );
        }

        $detail = $saleDetails[$detailID];

        $alreadyStmt->execute([$detailID]);

        $alreadyReturned = (int)$alreadyStmt->fetchColumn();

        $originalQuantity = (int)$detail['Quantity'];

        if (
            $originalQuantity < 1 ||
            $quantity + $alreadyReturned > $originalQuantity
        ) {
            throw new RuntimeException(
                'Return quantity exceeds the remaining quantity.'
            );
        }

        $price = (float)$detail['UnitPrice'];

        if ($price < 0) {
            throw new RuntimeException('Invalid product price.');
        }

        $lineGross = $price * $quantity;

        $selectedGross += $lineGross;

        $validatedItems[] = [
            'saleDetailID' => $detailID,
            'productID' => (string)$detail['ProductID'],
            'quantity' => $quantity,
            'price' => $price,
            'gross' => $lineGross
        ];
    }

    if ($selectedGross <= 0) {
        throw new RuntimeException(
            'The selected return amount must be greater than zero.'
        );
    }

    // --------------------------------------------------
    // 8. CALCULATE REFUND
    // --------------------------------------------------

    $refundAmount = 0.0;

    foreach ($validatedItems as &$item) {
        $itemRatio = $item['gross'] / $invoiceSubtotal;

        // Allocate manual discount proportionally.
        $itemDiscount = $discount * $itemRatio;

        $afterManualDiscount = max(
            $item['gross'] - $itemDiscount,
            0
        );

        // Allocate membership discount after manual discount.
        $itemMembershipDiscount =
            $afterManualDiscount * $membershipRate;

        $taxableAmount = max(
            $afterManualDiscount - $itemMembershipDiscount,
            0
        );

        $itemTax = $taxableAmount * $taxRate;

        $itemRefund = round(
            $taxableAmount + $itemTax,
            2
        );

        $item['refundAmount'] = $itemRefund;

        $refundAmount += $itemRefund;
    }

    unset($item);

    $refundAmount = round($refundAmount, 2);

    if ($refundAmount <= 0) {
        throw new RuntimeException(
            'The calculated refund amount must be greater than zero.'
        );
    }

    // --------------------------------------------------
    // 9. GENERATE RETURN ID
    // --------------------------------------------------

    // 4 + 12 + 4 = 20 characters.
    $returnID = 'RET-'
        . date('ymdHis')
        . bin2hex(random_bytes(2));

    $customerID = $sale['CustomerID'] ?: null;

    $returnReason = $reason;

    if ($notes !== '') {
        $returnReason .= "\nNotes: " . $notes;
    }

    // --------------------------------------------------
    // 10. SAVE RETURN HEADER
    // --------------------------------------------------

    $insertReturn = $pdo->prepare(
        "INSERT INTO sales_returns
            (
                ReturnID,
                SaleID,
                CustomerID,
                ReturnType,
                RefundAmount,
                ExtraPayment,
                Reason,
                Status,
                UserID
            )
         VALUES (?, ?, ?, 'Return', ?, 0, ?, 'Completed', ?)"
    );

    $insertReturn->execute([
        $returnID,
        $saleID,
        $customerID,
        $refundAmount,
        $returnReason,
        $userID
    ]);

    // --------------------------------------------------
    // 11. SAVE DETAILS AND RESTOCK
    // --------------------------------------------------

    $insertDetail = $pdo->prepare(
        "INSERT INTO sales_return_details
            (
                ReturnID,
                SaleDetailID,
                ProductID,
                Quantity,
                UnitPrice
            )
         VALUES (?, ?, ?, ?, ?)"
    );

    $stockStmt = $pdo->prepare(
        "UPDATE products
         SET StockQuantity = StockQuantity + ?
         WHERE ProductID = ?"
    );

    foreach ($validatedItems as $item) {
        $insertDetail->execute([
            $returnID,
            $item['saleDetailID'],
            $item['productID'],
            $item['quantity'],
            $item['price']
        ]);

        $stockStmt->execute([
            $item['quantity'],
            $item['productID']
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to update stock for a returned product.'
            );
        }
    }

    // --------------------------------------------------
    // 12. COMMIT
    // --------------------------------------------------

    $pdo->commit();

    respond(200, [
        'success' => true,
        'message' => 'Return recorded successfully.',
        'returnID' => $returnID,
        'refundAmount' => $refundAmount
    ]);
} catch (Throwable $e) {
    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    error_log('Process return error: ' . $e->getMessage());

    $knownErrors = [
        'Invoice not found.',
        'Only completed or paid sales can be returned.',
        'No products were found for this invoice.',
        'Cannot calculate the invoice subtotal.',
        'Invalid invoice discount or tax.',
        'A selected product does not belong to this invoice.',
        'Return quantity exceeds the remaining quantity.',
        'Invalid product price.',
        'The selected return amount must be greater than zero.',
        'Unable to update stock for a returned product.'
    ];

    if (in_array($e->getMessage(), $knownErrors, true)) {
        respond(422, [
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    respond(500, [
        'success' => false,
        'message' => 'Unable to process the transaction. Check the PHP error log.'
    ]);
}
