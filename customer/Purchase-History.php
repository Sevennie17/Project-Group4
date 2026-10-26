<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../config/database.php';

$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    exit('Database connection is not available.');
}

$page_css = app_base_path() . '/customer/assets/css/customer.css';

$errors = [];
$customer = null;
$sales = [];
$totalSpent = 0;
$totalSales = 0;

$customerID = trim($_GET['id'] ?? '');

if ($customerID === '') {
    header('Location: index.php');
    exit;
}

try {
    // Get customer information
    $customerStmt = $pdo->prepare(
        'SELECT CustomerID, CustomerName, Phone, Email, Address,
                MembershipID, LoyaltyPoints, CustomerStatus
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

        // Get the customer's sales history
        $salesStmt = $pdo->prepare(
            'SELECT SaleID, SaleDate, TotalAmount, PaymentMethod,
                    Discount, MembershipDiscount, Tax, Status
             FROM sales
             WHERE CustomerID = ?
             ORDER BY SaleDate DESC'
        );

        $salesStmt->execute([$customerID]);
        $sales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalSales = count($sales);
        $totalSpent = 0;

        foreach ($sales as $sale) {
            $totalSpent += (float) $sale['TotalAmount'];
        }
    }
} catch (PDOException $e) {
    $errors[] = 'Unable to load purchase history. Please check the database structure.';
}

+

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page purchase-history-page">

    <div class="customer-page-header">
        <div>
            <h1>Purchase History</h1>
            <p>View the customer's previous sales and payments.</p>
        </div>

        <a href="index.php" class="customer-btn customer-btn-secondary">
            &larr; Back to Customers
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="customer-alert-error" role="alert">
            <strong>Unable to load purchase history</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($customer): ?>

        <div class="purchase-customer-card">
            <div class="purchase-customer-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="purchase-customer-info">
                <h2>
                    <?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <p>
                    Customer ID:
                    <strong>
                        <?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </p>

                <p>
                    Phone:
                    <?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8') ?: '—' ?>
                </p>

                <span class="customer-status-badge <?= ($customer['CustomerStatus'] ?? 'Active') === 'Inactive' ? 'is-inactive' : 'is-active' ?>">
                    <?= htmlspecialchars($customer['CustomerStatus'] ?? 'Active', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>

        <div class="purchase-summary-grid">
            <div class="purchase-summary-card">
                <div class="purchase-summary-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <p>Total Transactions</p>
                    <h3><?= number_format($totalSales) ?></h3>
                </div>
            </div>

            <div class="purchase-summary-card">
                <div class="purchase-summary-icon">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div>
                    <p>Total Sales Amount</p>
                    <h3>$<?= number_format($totalSpent, 2) ?></h3>
                </div>
            </div>

            <div class="purchase-summary-card">
                <div class="purchase-summary-icon">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div>
                    <p>Loyalty Points</p>
                    <h3><?= number_format((int) $customer['LoyaltyPoints']) ?></h3>
                </div>
            </div>
        </div>

        <div class="purchase-history-card">
            <div class="purchase-history-heading">
                <div>
                    <h2>Sales Transactions</h2>
                    <p>Complete sales history for this customer.</p>
                </div>

                <span class="purchase-count">
                    <?= number_format($totalSales) ?> transaction(s)
                </span>
            </div>

            <div class="purchase-table-wrapper">
                <table class="purchase-history-table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!$sales): ?>
                            <tr>
                                <td colspan="7" class="purchase-empty">
                                    <i class="fa-solid fa-receipt"></i>
                                    <p>No purchase history found for this customer.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td class="purchase-sale-id">
                                        <?= htmlspecialchars($sale['SaleID'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($sale['SaleDate'])): ?>
                                            <?= htmlspecialchars(date('d M Y, H:i', strtotime($sale['SaleDate'])), ENT_QUOTES, 'UTF-8') ?>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($sale['PaymentMethod'] ?? '', ENT_QUOTES, 'UTF-8') ?: '—' ?>
                                    </td>

                                    <td>
                                        $<?= number_format(
                                                (float) $sale['Discount'] +
                                                    (float) $sale['MembershipDiscount'],
                                                2
                                            ) ?>
                                    </td>

                                    <td>
                                        $<?= number_format((float) $sale['Tax'], 2) ?>
                                    </td>

                                    <td class="purchase-amount">
                                        $<?= number_format((float) $sale['TotalAmount'], 2) ?>
                                    </td>

                                    <td>
                                        <span class="purchase-status">
                                            <?= htmlspecialchars($sale['Status'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="purchase-history-footer">
                <span>
                    Showing <?= number_format($totalSales) ?> transaction(s)
                </span>

                <strong>
                    Total: $<?= number_format($totalSpent, 2) ?>
                </strong>
            </div>
        </div>

    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>