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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$page_css = app_base_path() . '/customer/assets/css/customer.css';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $error = 'Invalid request. Please refresh the page.';
    } else {
        $customerID = trim($_POST['customer_id'] ?? '');
        $membershipID = trim($_POST['membership_id'] ?? '');

        try {
            // Only allow active customers to be updated.
            $customerStmt = $pdo->prepare(
                "SELECT CustomerID
                 FROM customers
                 WHERE CustomerID = ?
                   AND CustomerStatus = 'Active'
                 LIMIT 1"
            );
            $customerStmt->execute([$customerID]);
            $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                $error = 'Active customer was not found.';
            } else {
                // Verify that the selected membership exists.
                $membershipStmt = $pdo->prepare(
                    "SELECT MembershipID
                     FROM memberships
                     WHERE MembershipID = ?
                     LIMIT 1"
                );
                $membershipStmt->execute([$membershipID]);


                if (!$membershipStmt->fetch(PDO::FETCH_ASSOC)) {
                    $error = 'Please select a valid membership.';
                } else {
                    $updateStmt = $pdo->prepare(
                        "UPDATE customers
         SET MembershipID = ?
         WHERE CustomerID = ?
           AND CustomerStatus = 'Active'"
                    );

                    $updateStmt->execute([
                        $membershipID,
                        $customerID
                    ]);

                    // Verify the saved value directly from the database.
                    $checkStmt = $pdo->prepare(
                        "SELECT MembershipID, CustomerStatus
         FROM customers
         WHERE CustomerID = ?"
                    );

                    $checkStmt->execute([$customerID]);
                    $currentCustomer = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$currentCustomer) {
                        $error = 'Customer not found.';
                    } elseif ($currentCustomer['CustomerStatus'] !== 'Active') {
                        $error = 'Only active customers can change membership.';
                    } elseif ($currentCustomer['MembershipID'] !== $membershipID) {
                        $error = 'Membership was not updated.';
                    } else {
                        header('Location: Loyalty-Membership.php?updated=1');
                        exit;
                    }
                }
            }
        } catch (PDOException $e) {
            $error = 'Unable to update membership. Please try again.';
        }
    }
}

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $success = 'Customer membership updated successfully.';
}

$search = trim($_GET['search'] ?? '');
$filterMembership = trim($_GET['membership'] ?? '');

try {
    // Membership dropdown options.
    $membershipsStmt = $pdo->query(
        "SELECT MembershipID, MembershipName, DiscountRate, MinimumSpend
         FROM memberships
         ORDER BY id ASC"
    );
    $memberships = $membershipsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Summary counts.
    $summaryStmt = $pdo->query(
        "SELECT
            COUNT(*) AS total_customers,
            COALESCE(SUM(LoyaltyPoints), 0) AS total_points
         FROM customers
         WHERE CustomerStatus = 'Active'"
    );
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

    $membershipCountStmt = $pdo->query(
        "SELECT COUNT(*) FROM memberships"
    );
    $totalLevels = (int) $membershipCountStmt->fetchColumn();

    // Search and filter active customers.
    $sql = "
        SELECT
            c.CustomerID,
            c.CustomerName,
            c.Phone,
            c.Email,
            c.MembershipID,
            c.LoyaltyPoints,
            m.MembershipName,
            m.DiscountRate
        FROM customers AS c
        LEFT JOIN memberships AS m
            ON c.MembershipID = m.MembershipID
        WHERE c.CustomerStatus = 'Active'
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                c.CustomerID LIKE ?
                OR c.CustomerName LIKE ?
                OR c.Phone LIKE ?
                OR c.Email LIKE ?
            )
        ";

        $keyword = '%' . $search . '%';
        array_push(
            $params,
            $keyword,
            $keyword,
            $keyword,
            $keyword
        );
    }

    if ($filterMembership !== '') {
        $sql .= " AND c.MembershipID = ? ";
        $params[] = $filterMembership;
    }

    $sql .= " ORDER BY c.CustomerName ASC ";

    $customerStmt = $pdo->prepare($sql);
    $customerStmt->execute($params);
    $customers = $customerStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Unable to load loyalty membership data. Please check the database.');
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page loyalty-page">

    <div class="customer-page-header">
        <div>
            <h1>Loyalty Membership</h1>
            <p>Manage customer membership levels and loyalty points.</p>
        </div>

        <a href="Manage-Memberships.php" class="customer-btn customer-btn-primary">
            <i class="fa-solid fa-gem"></i>
            Manage Membership Levels
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="customer-alert customer-alert-success">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="customer-alert customer-alert-error">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="loyalty-summary-grid">
        <div class="loyalty-summary-card">
            <span class="loyalty-summary-icon">
                <i class="fa-solid fa-users"></i>
            </span>
            <div>
                <p>Active Customers</p>
                <h2><?= number_format((int) ($summary['total_customers'] ?? 0)) ?></h2>
            </div>
        </div>

        <div class="loyalty-summary-card">
            <span class="loyalty-summary-icon">
                <i class="fa-solid fa-gem"></i>
            </span>
            <div>
                <p>Membership Levels</p>
                <h2><?= number_format($totalLevels) ?></h2>
            </div>
        </div>

        <div class="loyalty-summary-card">
            <span class="loyalty-summary-icon">
                <i class="fa-solid fa-star"></i>
            </span>
            <div>
                <p>Total Loyalty Points</p>
                <h2><?= number_format((int) ($summary['total_points'] ?? 0)) ?></h2>
            </div>
        </div>
    </div>

    <div class="customer-panel">
        <div class="customer-panel-header">
            <div>
                <h2>Customer Membership List</h2>
                <p>Search customers and update their membership.</p>
            </div>
        </div>

        <form method="GET" class="loyalty-filter-form">
            <input
                type="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search ID, name, phone or email...">

            <select name="membership">
                <option value="">All Membership Levels</option>

                <?php foreach ($memberships as $membership): ?>
                    <option
                        value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $filterMembership === $membership['MembershipID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="customer-btn customer-btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>

            <a href="Loyalty-Membership.php" class="customer-btn customer-btn-secondary">
                Reset
            </a>
        </form>

        <div class="customer-table-wrapper">
            <table class="customer-table loyalty-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Membership</th>
                        <th>Discount</th>
                        <th>Loyalty Points</th>
                        <th>Change Membership</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="customer-empty-state">
                                No active customers found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <small class="loyalty-customer-id">
                                        <?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </td>

                                <td>
                                    <div>
                                        <?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <small class="loyalty-muted">
                                        <?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </td>

                                <td>
                                    <?php if (!empty($customer['MembershipName'])): ?>
                                        <span class="loyalty-level-badge">
                                            <?= htmlspecialchars($customer['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="loyalty-unassigned">Not assigned</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= number_format((float) ($customer['DiscountRate'] ?? 0), 2) ?>%
                                </td>

                                <td>
                                    <strong class="loyalty-points">
                                        <?= number_format((int) $customer['LoyaltyPoints']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <form method="POST" class="loyalty-change-form">
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                                        <input
                                            type="hidden"
                                            name="customer_id"
                                            value="<?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>">

                                        <select name="membership_id" required>
                                            <?php foreach ($memberships as $membership): ?>
                                                <option
                                                    value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>"

                                                    <?= $customer['MembershipID'] === $membership['MembershipID'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button
                                            type="submit"
                                            class="customer-btn customer-btn-primary loyalty-save-btn">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>