<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../config/database.php';

// Use the existing database connection.
$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    error_log('Customer page: PDO connection is unavailable.');
    http_response_code(500);
    exit('Database connection is unavailable. Please check config/database.php.');
}

// Load shared layout and this page's CSS.
$page_css = app_base_path() . '/customer/assets/css/customer.css';

$search = trim($_GET['search'] ?? '');
$membershipFilter = trim($_GET['membership'] ?? '');

$customers = [];
$memberships = [];
$totalCustomers = 0;
$totalPoints = 0;
$goldMembers = 0;
$pageError = '';

try {
    // Membership dropdown.
    $stmt = $pdo->query("
        SELECT MembershipID, MembershipName
        FROM memberships
        ORDER BY MembershipName
    ");
    $memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Customer list and membership level.
    $sql = "
        SELECT
            c.CustomerID,
            c.CustomerName,
            c.Phone,
            c.Email,
            c.Address,
            c.MembershipID,
            c.LoyaltyPoints,
            m.MembershipName,
            c.CustomerStatus
            FROM customers AS c
        LEFT JOIN memberships AS m
            ON c.MembershipID = m.MembershipID
        WHERE 1 = 1
    ";

    $params = [];

    if ($search !== '') {
        $sql .= "
            AND (
                c.CustomerID LIKE :customer_id
                OR c.CustomerName LIKE :customer_name
                OR c.Phone LIKE :phone
                OR c.Email LIKE :email
            )
        ";

        $keyword = '%' . $search . '%';

        $params['customer_id'] = $keyword;
        $params['customer_name'] = $keyword;
        $params['phone'] = $keyword;
        $params['email'] = $keyword;
    }

    if ($membershipFilter !== '') {
        $sql .= " AND c.MembershipID = :membership_id";
        $params['membership_id'] = $membershipFilter;
    }

    $sql .= " ORDER BY c.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Dashboard statistics.
    $stats = $pdo->query("
        SELECT
            COUNT(*) AS total_customers,
            COALESCE(SUM(LoyaltyPoints), 0) AS total_points
        FROM customers
    ")->fetch(PDO::FETCH_ASSOC);

    $totalCustomers = (int) $stats['total_customers'];
    $totalPoints = (int) $stats['total_points'];

    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM customers AS c
        INNER JOIN memberships AS m
            ON c.MembershipID = m.MembershipID
        WHERE LOWER(m.MembershipName) = 'gold'
    ");

    $goldMembers = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log('Customer dashboard error: ' . $e->getMessage());
    $pageError = 'Unable to load customer information. Please check the database.';
}

require_once __DIR__ . '/../includes/header.php';
?>


<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Deactivate Success Message -->
<?php if (isset($_GET['deactivated']) && $_GET['deactivated'] === '1'): ?>
    <div class="customer-alert customer-alert-success">
        Customer deactivated successfully.
        Sales history has been preserved.
    </div>
<?php endif; ?>

<!-- បន្តកូដ Customer Dashboard របស់ប្អូននៅខាងក្រោម -->


<div class="customer-page">

    <section class="customer-page-header">
        <div>
            <h1>Customer Management</h1>
            <p class="customer-subtitle">
                Manage customers, memberships and loyalty points.
            </p>
        </div>

        <div class="customer-header-actions">

            <a href="Purchase-History.php"
                class="customer-btn customer-btn-primary">
                <i class="fa-solid fa-clock-rotate-left"></i>
                Purchase History
            </a>

            <a href="Register-Customer.php"
                class="customer-btn customer-btn-gold">
                <i class="fa-solid fa-user-plus"></i>
                Add Customer
            </a>

        </div>
    </section>
</div>


<?php if ($pageError !== ''): ?>
    <div class="customer-alert">
        <?= htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<section class="customer-stats">

    <article class="customer-stat-card">
        <div class="customer-stat-icon">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <p>Total Customers</p>
            <h2><?= number_format($totalCustomers) ?></h2>
            <span>Registered customers</span>
        </div>
    </article>

    <article class="customer-stat-card">
        <div class="customer-stat-icon">
            <i class="fa-solid fa-crown"></i>
        </div>
        <div>
            <p>Gold Members</p>
            <h2><?= number_format($goldMembers) ?></h2>
            <span>Gold membership level</span>
        </div>
    </article>

    <article class="customer-stat-card">
        <div class="customer-stat-icon">
            <i class="fa-solid fa-star"></i>
        </div>
        <div>
            <p>Loyalty Points</p>
            <h2><?= number_format($totalPoints) ?></h2>
            <span>Total points earned</span>
        </div>
    </article>

    <article class="customer-stat-card">
        <div class="customer-stat-icon">
            <i class="fa-solid fa-gem"></i>
        </div>
        <div>
            <p>Membership Types</p>
            <h2><?= number_format(count($memberships)) ?></h2>
            <span>Available levels</span>
        </div>
    </article>

</section>

<section class="customer-panel">

    <div class="customer-panel-heading">
        <div>
            <h2>Customer List</h2>
            <p>Search and manage your registered customers.</p>
        </div>

        <a href="Loyalty-Membership.php"
            class="customer-btn customer-btn-outline">
            <i class="fa-solid fa-crown"></i>
            Memberships
        </a>
    </div>

    <form method="GET" action="index.php" class="customer-search-form">

        <div class="customer-search-input">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Search ID, name, phone or email...">
        </div>

        <select name="membership" aria-label="Filter by membership">
            <option value="">All Memberships</option>

            <?php foreach ($memberships as $membership): ?>
                <option
                    value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>"
                    <?= $membershipFilter === $membership['MembershipID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="customer-btn customer-btn-gold">
            Search
        </button>

        <a href="index.php" class="customer-btn customer-btn-outline">
            Reset
        </a>

    </form>

    <div class="customer-table-wrap">
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Customer ID</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Membership</th>
                    <th>Loyalty Points</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" class="customer-empty">
                            <i class="fa-regular fa-folder-open"></i>
                            <p>No customers found.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <span class="customer-status-badge <?= ($customer['CustomerStatus'] ?? 'Active') === 'Inactive' ? 'is-inactive' : 'is-active' ?>">
                                    <?= htmlspecialchars($customer['CustomerStatus'] ?? 'Active', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td class="customer-id">
                                <?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td class="customer-full-name">
                                <?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <span class="customer-membership-badge">
                                    <?= htmlspecialchars($customer['MembershipName'] ?? 'No Membership', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <td class="customer-points">
                                <i class="fa-solid fa-star"></i>
                                <?= number_format((int) $customer['LoyaltyPoints']) ?>
                            </td>

                            <td>
                                <div class="customer-actions">
                                    <a
                                        class="customer-action"
                                        href="Wishlist.php?id=<?= urlencode($customer['CustomerID']) ?>"
                                        title="Wishlist">
                                        <i class="fa-solid fa-heart"></i>
                                    </a>
                                    <a
                                        class="customer-action view"
                                        href="Purchase-History.php?id=<?= urlencode($customer['CustomerID']) ?>"
                                        title="Purchase History"><i class="fa-solid fa-eye"></i></a>

                                    <a
                                        class="customer-action edit"
                                        href="Update-Customer.php?id=<?= urlencode($customer['CustomerID']) ?>"
                                        title="Edit Customer"><i class="fa-solid fa-pen"></i></a>


                                    <?php if (($customer['CustomerStatus'] ?? 'Active') === 'Active'): ?>
                                        <a
                                            class="customer-action delete"
                                            href="DeletCustomer.php?id=<?= urlencode($customer['CustomerID']) ?>"
                                            title="Deactivate Customer"
                                            aria-label="Deactivate Customer">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span
                                            class="customer-action"
                                            title="Customer is inactive"
                                            aria-label="Customer is inactive"
                                            style="opacity: 0.45; cursor: not-allowed;">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </span>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="customer-table-footer">
        Showing <?= number_format(count($customers)) ?> customer(s)
    </div>

</section>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>