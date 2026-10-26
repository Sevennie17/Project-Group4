
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection is not available.'
    ]);
    exit;
}

$saleID = trim($_GET['sale_id'] ?? '');

if ($saleID === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Please enter an invoice number.'
    ]);
    exit;
}

try {
    // Find invoice in the existing sales table.
    $stmt = $pdo->prepare("
        SELECT
            SaleID,
            SaleDate,
            CustomerID,
            TotalAmount,
            Discount,
            MembershipDiscount,
            Tax,
            PaymentMethod,
            Status
        FROM sales
        WHERE SaleID = ?
        LIMIT 1
    ");

    $stmt->execute([$saleID]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invoice not found.'
        ]);
        exit;
    }

    if (
        strcasecmp($sale['Status'], 'Completed') !== 0 &&
        strcasecmp($sale['Status'], 'Paid') !== 0
    ) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Only completed or paid invoices can be returned.'
        ]);
        exit;
    }

    // Find products belonging to this sale.

    $itemsStmt = $pdo->prepare("
        SELECT
            sd.SaleDetailID,
            p.ProductID,
            p.ProductName,
            p.SKU,
            sd.Quantity,
            sd.UnitPrice
        FROM sale_details sd
        INNER JOIN products p
            ON p.ProductID = sd.ProductID
        WHERE sd.SaleID = ?
        ORDER BY sd.SaleDetailID
    ");


    $itemsStmt->execute([$saleID]);
    $rows = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    $items = [];

    foreach ($rows as $row) {
        $quantity = (int) $row['Quantity'];
        $price = (float) $row['UnitPrice'];
        $items[] = [
            'saleDetailID' => (int)$row['SaleDetailID'],
            'productID' => $row['ProductID'],
            'name' => $row['ProductName'],
            'sku' => $row['SKU'],
            'quantity' => $quantity,
            'price' => $price,
            'itemTotal' => $quantity * $price
        ];
    }

    if (!$items) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No products were found for this invoice.'
        ]);
        exit;
    }

    // Find customer details.
    $customerName = 'Walk-in Customer';
    $customerPhone = '-';
    $customerAddress = '-';

    if (!empty($sale['CustomerID'])) {
        $customerStmt = $pdo->prepare("
            SELECT CustomerName, Phone, Address
            FROM customers
            WHERE CustomerID = ?
            LIMIT 1
        ");

        $customerStmt->execute([$sale['CustomerID']]);
        $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            $customerName = $customer['CustomerName'] ?: 'Walk-in Customer';
            $customerPhone = $customer['Phone'] ?: '-';
            $customerAddress = $customer['Address'] ?: '-';
        }
    }

    echo json_encode([
        'success' => true,
        'invoice' => [
            'saleID' => $sale['SaleID'],
            'invoiceNumber' => $sale['SaleID'],
            'date' => $sale['SaleDate'],
            'customerID' => $sale['CustomerID'],
            'customer' => $customerName,
            'phone' => $customerPhone,
            'address' => $customerAddress,
            'payment' => $sale['PaymentMethod'],
            'status' => $sale['Status'],
            'subtotal' => array_sum(
                array_column($items, 'itemTotal')
            ),
            'discount' => (float) $sale['Discount'],
            'membershipDiscount' => (float) $sale['MembershipDiscount'],
            'tax' => (float) $sale['Tax'],
            'total' => (float) $sale['TotalAmount'],
            'items' => $items
        ]
    ]);
} catch (Throwable $e) {
    error_log('Find return invoice error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to search for this invoice.'
    ]);
}
