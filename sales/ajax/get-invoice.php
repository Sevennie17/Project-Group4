
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
        'message' => 'Sale ID is required.'
    ]);
    exit;
}

try {
    $saleStmt = $pdo->prepare("
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

    $saleStmt->execute([$saleID]);
    $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Invoice not found.'
        ]);
        exit;
    }


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

    $subtotal = 0;

    foreach ($rows as $row) {
        $quantity = (int) $row['Quantity'];
        $price = (float) $row['UnitPrice'];
        $itemTotal = $quantity * $price;
        $subtotal += $itemTotal;
        $items[] = [
            'saleDetailID' => (int)$row['SaleDetailID'],
            'productID' => $row['ProductID'],
            'name' => $row['ProductName'],
            'sku' => $row['SKU'],
            'quantity' => $quantity,
            'price' => $price,
            'itemTotal' => $itemTotal
        ];
    }


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
            'customer' => $customerName,
            'phone' => $customerPhone,
            'address' => $customerAddress,
            'payment' => $sale['PaymentMethod'],
            'status' => $sale['Status'],
            'subtotal' => $subtotal,
            'discount' => (float) $sale['Discount'],
            'membershipDiscount' =>
            (float) $sale['MembershipDiscount'],
            'tax' => (float) $sale['Tax'],
            'total' => (float) $sale['TotalAmount'],
            'items' => $items
        ]
    ]);
} catch (Throwable $e) {
    error_log('Get invoice error: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to load invoice.'
    ]);
}
