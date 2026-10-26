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
$items = [];

// Get customer ID from the URL.
$customerID = trim($_GET['id'] ?? '');

if ($customerID === '') {
    http_response_code(400);
    $errors[] = 'Customer ID is required.';
} else {
    try {
        // Confirm that the customer exists.
        $customerStmt = $pdo->prepare(
            'SELECT CustomerID, CustomerName
             FROM customers
             WHERE CustomerID = ?
             LIMIT 1'
        );
        $customerStmt->execute([$customerID]);
        $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            http_response_code(404);
            $errors[] = 'Customer not found.';
        } else {
            // Load wishlist products.
            $stmt = $pdo->prepare(
                'SELECT
                    w.id AS WishlistID,
                    w.CreatedAt,
                    p.ProductID,
                    p.ProductName,
                    p.SKU,
                    p.SellingPrice,
                    p.Description
                 FROM wishlists AS w
                 INNER JOIN products AS p
                    ON p.ProductID = w.ProductID
                 WHERE w.CustomerID = ?
                 ORDER BY w.CreatedAt DESC'
            );

            $stmt->execute([$customerID]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log('Wishlist error: ' . $e->getMessage());
        $errors[] = 'Wishlist database error: ' . $e->getMessage();
    }
}

if (empty($_SESSION['wishlist_csrf'])) {
    $_SESSION['wishlist_csrf'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page">
    <div class="customer-page-header">
        <div>
            <h1>Wishlist</h1>
            <p>
                <?php if (!empty($customer)): ?>
                    Wishlist for
                    <strong>
                        <?= htmlspecialchars(
                            $customer['CustomerName'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>
                <?php else: ?>
                    Saved favorite products.
                <?php endif; ?>
            </p>
        </div>

        <a href="index.php"
            class="customer-btn customer-btn-secondary">
            &larr; Back to Customers
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="customer-alert customer-alert-error" role="alert">
            <?php foreach ($errors as $error): ?>
                <p>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($errors) && !empty($customer)): ?>
        <div class="customer-panel">
            <div class="customer-panel-heading">
                <div>
                    <h2>Favorite Products</h2>
                    <p>
                        <?= count($items) ?> saved product(s)
                    </p>
                </div>
            </div>

            <?php if (!$items): ?>
                <div class="customer-empty">
                    <p>No products have been added to this wishlist yet.</p>
                </div>
            <?php else: ?>
                <div class="customer-table-wrap">
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Date Added</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars(
                                            $item['ProductID'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars(
                                                $item['ProductName'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $item['SKU'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                    <td>
                                        $<?= number_format(
                                                (float)$item['SellingPrice'],
                                                2
                                            ) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars(
                                            $item['CreatedAt'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>
                                    <td>
                                        <form method="POST"
                                            action="wishlist-delete.php"
                                            onsubmit="return confirm('Remove this product from the wishlist?');">
                                            <input type="hidden"
                                                name="wishlist_id"
                                                value="<?= (int)$item['WishlistID'] ?>">

                                            <input type="hidden"
                                                name="CustomerID"
                                                value="<?= htmlspecialchars(
                                                            $customerID,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>">

                                            <input type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars(
                                                            $_SESSION['wishlist_csrf'],
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>">

                                            <button type="submit"
                                                class="customer-btn customer-btn-danger">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>