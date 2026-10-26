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

$page_css = app_base_path() . '/customer/assets/css/customer.css';

$errors = [];
$success = '';
$product = null;
$customers = [];

$productID = trim($_POST['ProductID'] ?? $_GET['product_id'] ?? '');

if ($productID === '') {
    http_response_code(400);
    exit('Product ID is required.');
}

if (empty($_SESSION['wishlist_add_csrf'])) {
    $_SESSION['wishlist_add_csrf'] = bin2hex(random_bytes(32));
}

try {
    // Find product
    $stmt = $pdo->prepare(
        'SELECT ProductID, ProductName, SKU, SellingPrice
         FROM products
         WHERE ProductID = ?
         LIMIT 1'
    );
    $stmt->execute([$productID]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        exit('Product not found.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customerID = trim($_POST['CustomerID'] ?? '');
        $token = $_POST['csrf_token'] ?? '';

        if (
            !is_string($token) ||
            empty($_SESSION['wishlist_add_csrf']) ||
            !hash_equals($_SESSION['wishlist_add_csrf'], $token)
        ) {
            $errors[] = 'Your session expired. Please try again.';
        } elseif ($customerID === '') {
            $errors[] = 'Please select a customer.';
        } else {
            // Confirm customer exists and is active
            $customerStmt = $pdo->prepare(
                "SELECT CustomerID
                 FROM customers
                 WHERE CustomerID = ?
                   AND CustomerStatus = 'Active'
                 LIMIT 1"
            );
            $customerStmt->execute([$customerID]);

            if (!$customerStmt->fetchColumn()) {
                $errors[] = 'Please select a valid active customer.';
            } else {
                // Check for an existing wishlist item
                $check = $pdo->prepare(
                    'SELECT id
                     FROM wishlists
                     WHERE CustomerID = ?
                       AND ProductID = ?
                     LIMIT 1'
                );
                $check->execute([$customerID, $productID]);

                if ($check->fetchColumn()) {
                    $errors[] = 'This product is already in this customer\'s wishlist.';
                } else {
                    $insert = $pdo->prepare(
                        'INSERT INTO wishlists (CustomerID, ProductID)
                         VALUES (?, ?)'
                    );
                    $insert->execute([$customerID, $productID]);

                    header(
                        'Location: Wishlist.php?id=' . urlencode($customerID) . '&added=1'
                    );
                    exit;
                }
            }
        }
    }

    // Load active customers for the selection list
    $customerListStmt = $pdo->query(
        "SELECT CustomerID, CustomerName
         FROM customers
         WHERE CustomerStatus = 'Active'
         ORDER BY CustomerName ASC"
    );
    $customers = $customerListStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Wishlist add error: ' . $e->getMessage());
    $errors[] = 'Unable to process the wishlist request. Please try again.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page">
    <div class="customer-page-header">
        <div>
            <h1>Add to Wishlist</h1>
            <p>Select the customer who wants to save this product.</p>
        </div>

        <a href="<?= htmlspecialchars(
                        app_base_path() . '/products/',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>" class="customer-btn customer-btn-secondary">
            &larr; Back to Products
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="customer-alert-error" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($product): ?>
        <div class="customer-panel">
            <div class="customer-panel-heading">
                <div>
                    <h2><?= htmlspecialchars(
                            $product['ProductName'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></h2>
                    <p>
                        SKU:
                        <?= htmlspecialchars(
                            $product['SKU'] ?? '—',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                        &middot;
                        Price: $<?= number_format(
                                    (float)$product['SellingPrice'],
                                    2
                                ) ?>
                    </p>
                </div>
            </div>

            <?php if (!$customers): ?>
                <p>No active customers are available.</p>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden"
                        name="ProductID"
                        value="<?= htmlspecialchars(
                                    $productID,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                    <input type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(
                                    $_SESSION['wishlist_add_csrf'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">

                    <div class="customer-form-group">
                        <label for="CustomerID">Select Customer</label>

                        <select id="CustomerID"
                            name="CustomerID"
                            required>
                            <option value="">-- Choose a customer --</option>

                            <?php foreach ($customers as $customer): ?>
                                <option
                                    value="<?= htmlspecialchars(
                                                $customer['CustomerID'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                    <?= (
                                        ($_POST['CustomerID'] ?? '') ===
                                        $customer['CustomerID']
                                    ) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(
                                        $customer['CustomerID'] . ' — ' .
                                            $customer['CustomerName'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="customer-form-actions">
                        <a href="<?= htmlspecialchars(
                                        app_base_path() . '/products/',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>" class="customer-btn customer-btn-secondary">
                            Cancel
                        </a>

                        <button type="submit"
                            class="customer-btn customer-btn-gold">
                            <i class="fa-solid fa-heart"></i>
                            Add to Wishlist
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>