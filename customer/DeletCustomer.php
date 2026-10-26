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

if (empty($_SESSION['customer_delete_csrf'])) {
    $_SESSION['customer_delete_csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$customer = null;
$customerID = trim($_GET['id'] ?? $_POST['CustomerID'] ?? '');

if ($customerID === '') {
    header('Location: index.php');
    exit;
}

// Find customer
$stmt = $pdo->prepare(
    'SELECT CustomerID, CustomerName, Phone, Email, Address,
            MembershipID, LoyaltyPoints, CustomerStatus
     FROM customers
     WHERE CustomerID = ?
     LIMIT 1'
);
$stmt->execute([$customerID]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    http_response_code(404);
    $errors[] = 'Customer not found.';
}

// Deactivate only after POST confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customer) {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['customer_delete_csrf'], $token)
    ) {
        $errors[] = 'Session expired. Please reload the page and try again.';
    } elseif ($customer['CustomerStatus'] === 'Inactive') {
        $errors[] = 'This customer is already inactive.';
    } else {
        try {
            $update = $pdo->prepare(
                "UPDATE customers
                 SET CustomerStatus = 'Inactive'
                 WHERE CustomerID = ?
                   AND CustomerStatus = 'Active'"
            );

            $update->execute([$customerID]);

            if ($update->rowCount() > 0) {
                unset($_SESSION['customer_delete_csrf']);

                header('Location: index.php?deactivated=1');
                exit;
            }

            $errors[] = 'Customer status was not changed. Please refresh and try again.';
        } catch (PDOException $e) {
            $errors[] = 'Unable to deactivate this customer. Please try again.';
        }
    }

    // Refresh displayed status after an unsuccessful attempt
    $stmt = $pdo->prepare(
        'SELECT CustomerID, CustomerName, Phone, Email, Address,
                MembershipID, LoyaltyPoints, CustomerStatus
         FROM customers
         WHERE CustomerID = ?
         LIMIT 1'
    );
    $stmt->execute([$customerID]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page">
    <div class="customer-page-header">
        <div>
            <h1>Deactivate Customer</h1>
            <p>Review the customer information before confirming.</p>
        </div>

        <a href="index.php" class="customer-btn customer-btn-secondary">
            &larr; Back to Customers
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="customer-alert-error" role="alert">
            <strong>Unable to deactivate customer</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($customer): ?>
        <div class="customer-delete-card">
            <div class="customer-delete-icon">!</div>

            <h2>
                <?= $customer['CustomerStatus'] === 'Inactive'
                    ? 'This customer is already inactive.'
                    : 'Are you sure you want to deactivate this customer?' ?>
            </h2>

            <p class="customer-delete-warning">
                The customer record and sales history will be kept.
                This changes the customer status to Inactive.
            </p>

            <div class="customer-delete-details">
                <div>
                    <span>Customer ID</span>
                    <strong><?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <div>
                    <span>Full Name</span>
                    <strong><?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <div>
                    <span>Phone Number</span>
                    <strong><?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8') ?: '—' ?></strong>
                </div>

                <div>
                    <span>Email</span>
                    <strong><?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?: '—' ?></strong>
                </div>

                <div>
                    <span>Membership</span>
                    <strong><?= htmlspecialchars($customer['MembershipID'] ?? '', ENT_QUOTES, 'UTF-8') ?: 'None' ?></strong>
                </div>

                <div>
                    <span>Loyalty Points</span>
                    <strong><?= (int)$customer['LoyaltyPoints'] ?></strong>
                </div>

                <div>
                    <span>Current Status</span>
                    <strong><?= htmlspecialchars($customer['CustomerStatus'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>

            <div class="customer-form-actions">
                <a href="index.php" class="customer-btn customer-btn-secondary">
                    Cancel
                </a>

                <?php if ($customer['CustomerStatus'] === 'Active'): ?>
                    <form method="POST" action="">
                        <input
                            type="hidden"
                            name="CustomerID"
                            value="<?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($_SESSION['customer_delete_csrf'], ENT_QUOTES, 'UTF-8') ?>">

                        <button
                            type="submit"
                            class="customer-btn customer-btn-danger"
                            onclick="return confirm('Deactivate this customer? Sales history will be preserved.');">
                            Confirm Deactivate
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>