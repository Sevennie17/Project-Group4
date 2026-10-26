
<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../config/database.php';

$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    exit('Database connection is not available.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$customerID = trim($_POST['CustomerID'] ?? '');
$wishlistID = filter_input(
    INPUT_POST,
    'wishlist_id',
    FILTER_VALIDATE_INT
);
$token = $_POST['csrf_token'] ?? '';

if (
    $customerID === '' ||
    !$wishlistID ||
    !is_string($token) ||
    empty($_SESSION['wishlist_csrf']) ||
    !hash_equals($_SESSION['wishlist_csrf'], $token)
) {
    http_response_code(400);
    exit('Invalid request. Please return to the wishlist and try again.');
}

try {
    $stmt = $pdo->prepare(
        'DELETE FROM wishlists
         WHERE id = ?
           AND CustomerID = ?'
    );

    $stmt->execute([$wishlistID, $customerID]);

    header(
        'Location: Wishlist.php?id=' . urlencode($customerID)
    );
    exit;
} catch (PDOException $e) {
    error_log('Wishlist removal error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to remove this product from the wishlist.');
}
