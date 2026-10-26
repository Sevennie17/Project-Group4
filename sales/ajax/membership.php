
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../../config/database.php';

$pdo = $pdo ?? ($con ?? null);

header('Content-Type: application/json; charset=utf-8');

if (!$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection is not available.'
    ]);
    exit;
}

$customerID = trim($_GET['customer_id'] ?? '');

if ($customerID === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Customer ID is required.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT
            c.CustomerID,
            c.CustomerName,
            c.CustomerStatus,
            c.MembershipID,
            COALESCE(m.MembershipName, 'No Membership') AS MembershipName,
            COALESCE(m.DiscountRate, 0) AS DiscountRate,
            COALESCE(m.MinimumSpend, 0) AS MinimumSpend
         FROM customers AS c
         LEFT JOIN memberships AS m
            ON c.MembershipID = m.MembershipID
         WHERE c.CustomerID = ?
         LIMIT 1"
    );

    $stmt->execute([$customerID]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found.'
        ]);
        exit;
    }

    if ($customer['CustomerStatus'] !== 'Active') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'This customer is inactive.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'customer' => [
            'CustomerID' => $customer['CustomerID'],
            'CustomerName' => $customer['CustomerName'],
            'MembershipID' => $customer['MembershipID'],
            'MembershipName' => $customer['MembershipName'],
            'DiscountRate' => (float) $customer['DiscountRate'],
            'MinimumSpend' => (float) $customer['MinimumSpend']
        ]
    ]);
} catch (PDOException $e) {
    error_log('Membership lookup error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load customer membership.'
    ]);
}
