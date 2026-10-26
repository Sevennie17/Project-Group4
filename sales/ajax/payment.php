<?php

require_once __DIR__ . '/../../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');


/* =========================================================
   ONLY POST REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

    exit;
}


try {

    /* =====================================================
       GET DATA
    ===================================================== */

    $invoiceNumber = trim($_POST['invoiceNumber'] ?? '');

    $customerID = trim($_POST['customerID'] ?? '');
    error_log("PAYMENT CustomerID received: [" . $customerID . "]");

    $paymentMethod = trim($_POST['paymentMethod'] ?? 'Cash');

    $itemsJson = $_POST['items'] ?? '[]';

    $discountValue = (float)($_POST['discountValue'] ?? 0);

    $discountType = $_POST['discountType'] ?? 'percent';

    // Membership rate must be loaded from the database.
    // Do not trust the rate sent by JavaScript.
    $membershipRate = 0;


    /* =====================================================
       VALIDATE BASIC DATA
    ===================================================== */

    if ($invoiceNumber === '') {

        throw new Exception(
            'Invoice number is required.'
        );
    }


    if (!is_array($items = json_decode($itemsJson, true))) {

        throw new Exception(
            'Invalid cart data.'
        );
    }


    if (count($items) === 0) {

        throw new Exception(
            'Cart is empty.'
        );
    }


    /* =====================================================
       ALLOWED PAYMENT METHODS
    ===================================================== */

    $allowedMethods = [
        'Cash',
        'Card',
        'Bank Transfer',
        'Mobile Payment',
        'QR Payment'
    ];


    if (!in_array($paymentMethod, $allowedMethods, true)) {

        throw new Exception(
            'Invalid payment method.'
        );
    }


    /* =====================================================
       VALIDATE DISCOUNT
    ===================================================== */

    if ($discountValue < 0) {

        throw new Exception(
            'Invalid discount.'
        );
    }


    if (!in_array($discountType, ['percent', 'amount'], true)) {

        throw new Exception(
            'Invalid discount type.'
        );
    }


    if ($membershipRate < 0 || $membershipRate > 100) {

        throw new Exception(
            'Invalid membership discount.'
        );
    }


    /* =====================================================
       START TRANSACTION
    ===================================================== */

    $con->beginTransaction();


    /* =====================================================
       CREATE SALE ID
       Database SaleID = VARCHAR(10)
    ===================================================== */

    do {

        $saleID =
            'S' .
            date('ymd') .
            str_pad(
                (string)random_int(0, 999),
                3,
                '0',
                STR_PAD_LEFT
            );

        $checkSale = $con->prepare("
            SELECT COUNT(*)
            FROM sales
            WHERE SaleID = :saleID
        ");

        $checkSale->execute([
            ':saleID' => $saleID
        ]);
    } while ((int)$checkSale->fetchColumn() > 0);

    /* =====================================================
   LOAD CUSTOMER MEMBERSHIP FROM DATABASE
===================================================== */

    if ($customerID !== '') {
        $membershipStmt = $con->prepare("
        SELECT
            c.CustomerStatus,
            c.MembershipID,
            COALESCE(m.DiscountRate, 0) AS DiscountRate,
            COALESCE(m.MinimumSpend, 0) AS MinimumSpend
        FROM customers AS c
        LEFT JOIN memberships AS m
            ON c.MembershipID = m.MembershipID
        WHERE c.CustomerID = :customerID
        LIMIT 1
    ");

        $membershipStmt->execute([
            ':customerID' => $customerID
        ]);

        $customerMembership = $membershipStmt->fetch(PDO::FETCH_ASSOC);

        if (!$customerMembership) {
            throw new Exception('Customer not found.');
        }

        if ($customerMembership['CustomerStatus'] !== 'Active') {
            throw new Exception('This customer is inactive.');
        }

        $membershipRate =
            (float)$customerMembership['DiscountRate'];

        $minimumSpend =
            (float)$customerMembership['MinimumSpend'];
    } else {
        $membershipRate = 0;
        $minimumSpend = 0;
    }

    /* =====================================================
       CALCULATE SUBTOTAL FROM DATABASE
    ===================================================== */

    $subtotal = 0;

    $saleItems = [];


    $productStmt = $con->prepare("
        SELECT
            ProductID,
            ProductName,
            SKU,
            SellingPrice,
            StockQuantity
        FROM products
        WHERE SKU = :sku
        FOR UPDATE
    ");


    foreach ($items as $item) {

        $sku = trim($item['sku'] ?? '');

        $quantity = (int)($item['quantity'] ?? 0);


        if ($sku === '') {

            throw new Exception(
                'Product SKU is missing.'
            );
        }


        if ($quantity <= 0) {

            throw new Exception(
                'Invalid product quantity.'
            );
        }


        $productStmt->execute([
            ':sku' => $sku
        ]);


        $product = $productStmt->fetch(PDO::FETCH_ASSOC);


        if (!$product) {

            throw new Exception(
                "Product with SKU {$sku} was not found."
            );
        }


        /* =================================================
           CHECK STOCK
        ================================================= */

        if ($quantity > (int)$product['StockQuantity']) {

            throw new Exception(
                "Not enough stock for {$product['ProductName']}."
            );
        }


        $unitPrice = (float)$product['SellingPrice'];

        $itemTotal = $unitPrice * $quantity;

        $subtotal += $itemTotal;


        $saleItems[] = [
            'ProductID' => $product['ProductID'],
            'ProductName' => $product['ProductName'],
            'SKU' => $product['SKU'],
            'Quantity' => $quantity,
            'UnitPrice' => $unitPrice,
            'ItemTotal' => $itemTotal
        ];
    }


    /* =====================================================
       MANUAL DISCOUNT
    ===================================================== */

    if ($discountType === 'percent') {

        $discount =
            $subtotal * ($discountValue / 100);
    } else {

        $discount = $discountValue;
    }


    $discount = min(
        max($discount, 0),
        $subtotal
    );


    /* =====================================================
       MEMBERSHIP DISCOUNT
    ===================================================== */

    $eligibleAmount = max($subtotal - $discount, 0);

    if ($eligibleAmount >= $minimumSpend) {
        $membershipDiscount =
            $eligibleAmount * ($membershipRate / 100);
    } else {
        $membershipDiscount = 0;
    }


    /* =====================================================
       TAX
    ===================================================== */

    $taxableAmount =
        max(
            $subtotal
                - $discount
                - $membershipDiscount,
            0
        );


    $tax =
        $taxableAmount * 0.08;


    /* =====================================================
       FINAL TOTAL
    ===================================================== */

    $total =
        $taxableAmount + $tax;


    /* =====================================================
       INSERT SALES
    ===================================================== */

    $saleStmt = $con->prepare("
        INSERT INTO sales (
            SaleID,
            SaleDate,
            TotalAmount,
            CustomerID,
            UserID,
            PaymentMethod,
            Discount,
            MembershipDiscount,
            Tax,
            Status
        )
        VALUES (
            :saleID,
            NOW(),
            :totalAmount,
            :customerID,
            :userID,
            :paymentMethod,
            :discount,
            :membershipDiscount,
            :tax,
            'Completed'
        )
    ");


    /*
     * UserID can be NULL if your authentication
     * system does not provide it.
     */

    $userID = $_SESSION['UserID'] ?? null;


    $saleStmt->execute([
        ':saleID' =>
        $saleID,

        ':totalAmount' =>
        number_format($total, 2, '.', ''),

        ':customerID' =>
        $customerID !== ''
            ? $customerID
            : null,

        ':userID' =>
        $userID,

        ':paymentMethod' =>
        $paymentMethod,

        ':discount' =>
        number_format($discount, 2, '.', ''),

        ':membershipDiscount' =>
        number_format(
            $membershipDiscount,
            2,
            '.',
            ''
        ),

        ':tax' =>
        number_format(
            $tax,
            2,
            '.',
            ''
        )
    ]);
    error_log("SALE INSERTED: " . $saleID);


    /* =====================================================
       INSERT SALE DETAILS
    ===================================================== */

    $detailStmt = $con->prepare("
        INSERT INTO sale_details (
            SaleID,
            ProductID,
            Quantity,
            UnitPrice
        )
        VALUES (
            :saleID,
            :productID,
            :quantity,
            :unitPrice
        )
    ");


    /* =====================================================
       UPDATE STOCK
    ===================================================== */

    $stockStmt = $con->prepare("
        UPDATE products
        SET StockQuantity = StockQuantity - :quantity
        WHERE ProductID = :productID
          AND StockQuantity >= :quantity
    ");


    foreach ($saleItems as $saleItem) {

        /* -----------------------------------------------
           INSERT DETAIL
        ----------------------------------------------- */

        $detailStmt->execute([

            ':saleID' =>
            $saleID,

            ':productID' =>
            $saleItem['ProductID'],

            ':quantity' =>
            $saleItem['Quantity'],

            ':unitPrice' =>
            number_format(
                $saleItem['UnitPrice'],
                2,
                '.',
                ''
            )
        ]);


        /* -----------------------------------------------
           UPDATE STOCK
        ----------------------------------------------- */

        $stockStmt->execute([

            ':quantity' =>
            $saleItem['Quantity'],

            ':productID' =>
            $saleItem['ProductID']
        ]);


        if ($stockStmt->rowCount() !== 1) {

            throw new Exception(
                "Unable to update stock for {$saleItem['ProductName']}."
            );
        }
    }

    // =============================================
    // ADD LOYALTY POINTS
    // $100 spent = 1 point
    // =============================================

    if ($customerID !== '') {

        // Calculate points from the final amount paid.
        $pointsEarned = (int) floor($total / 1000);

        if ($pointsEarned > 0) {

            $pointsStmt = $con->prepare("
            UPDATE customers
            SET LoyaltyPoints = LoyaltyPoints + :points
            WHERE CustomerID = :customerID
              AND CustomerStatus = 'Active'
        ");

            $pointsStmt->execute([
                ':points' => $pointsEarned,
                ':customerID' => $customerID
            ]);

            if ($pointsStmt->rowCount() !== 1) {
                throw new Exception(
                    'Unable to update customer loyalty points.'
                );
            }
        }
    }

    // Commit only after sale, stock and points succeed.
    $con->commit();

    /* =====================================================
       COMMIT
    ===================================================== */

    $con->commit();


    /* =====================================================
       SUCCESS RESPONSE
    ===================================================== */

    echo json_encode([

        'success' => true,

        'message' =>
        'Payment completed and sale saved successfully.',

        'data' => [

            'saleID' =>
            $saleID,

            'invoiceNumber' =>
            $invoiceNumber,

            'customerID' =>
            $customerID,

            'paymentMethod' =>
            $paymentMethod,

            'subtotal' =>
            number_format(
                $subtotal,
                2,
                '.',
                ''
            ),

            'discount' =>
            number_format(
                $discount,
                2,
                '.',
                ''
            ),

            'membershipDiscount' =>
            number_format(
                $membershipDiscount,
                2,
                '.',
                ''
            ),

            'tax' =>
            number_format(
                $tax,
                2,
                '.',
                ''
            ),

            'total' =>
            number_format(
                $total,
                2,
                '.',
                ''
            ),

            'status' =>
            'Completed',

            'paidAt' =>
            date('Y-m-d H:i:s')
        ]

    ]);

    exit;
} catch (Throwable $e) {


    /* =====================================================
       ROLLBACK
    ===================================================== */

    if (
        isset($con)
        && $con instanceof PDO
        && $con->inTransaction()
    ) {

        $con->rollBack();
    }


    echo json_encode([

        'success' => false,

        'message' =>
        $e->getMessage()

    ]);

    exit;
}
