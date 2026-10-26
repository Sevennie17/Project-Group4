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
        $membershipID = trim($_POST['membership_id'] ?? '');
        $membershipName = trim($_POST['membership_name'] ?? '');
        $discountInput = $_POST['discount_rate'] ?? '';
        $minimumSpendInput = $_POST['minimum_spend'] ?? '';

        if (
            $membershipID === '' ||
            $membershipName === '' ||
            !is_numeric($discountInput) ||
            !is_numeric($minimumSpendInput)
        ) {
            $error = 'Please fill in all fields with valid values.';
        } else {
            $discountRate = (float) $discountInput;
            $minimumSpend = (float) $minimumSpendInput;

            if (mb_strlen($membershipName) > 50) {
                $error = 'Membership name must be 50 characters or fewer.';
            } elseif (
                $discountRate < 0 ||
                $discountRate > 100 ||
                $minimumSpend < 0
            ) {
                $error = 'Discount must be between 0 and 100, and minimum spend cannot be negative.';
            } else {
                try {
                    // Check this membership exists.
                    $existsStmt = $pdo->prepare(
                        "SELECT MembershipID
                         FROM memberships
                         WHERE MembershipID = ?
                         LIMIT 1"
                    );
                    $existsStmt->execute([$membershipID]);

                    if (!$existsStmt->fetch(PDO::FETCH_ASSOC)) {
                        $error = 'Membership level was not found.';
                    } else {
                        // Prevent duplicate membership names.
                        $duplicateStmt = $pdo->prepare(
                            "SELECT MembershipID
                             FROM memberships
                             WHERE MembershipName = ?
                               AND MembershipID <> ?
                             LIMIT 1"
                        );
                        $duplicateStmt->execute([
                            $membershipName,
                            $membershipID
                        ]);

                        if ($duplicateStmt->fetch(PDO::FETCH_ASSOC)) {
                            $error = 'That membership name already exists.';
                        } else {
                            $updateStmt = $pdo->prepare(
                                "UPDATE memberships
                                 SET MembershipName = ?,
                                     DiscountRate = ?,
                                     MinimumSpend = ?
                                 WHERE MembershipID = ?"
                            );


                            $updateStmt->execute([
                                $membershipName,
                                $discountRate,
                                $minimumSpend,
                                $membershipID
                            ]);

                            // Verify that the changes were saved.
                            $verifyStmt = $pdo->prepare(
                                "SELECT MembershipName, DiscountRate, MinimumSpend
                                FROM memberships
                                WHERE MembershipID = ?
                                LIMIT 1"
                            );

                            $verifyStmt->execute([$membershipID]);
                            $savedMembership = $verifyStmt->fetch(PDO::FETCH_ASSOC);

                            if (
                                $savedMembership &&
                                $savedMembership['MembershipName'] === $membershipName &&
                                (float) $savedMembership['DiscountRate'] === $discountRate &&
                                (float) $savedMembership['MinimumSpend'] === $minimumSpend
                            ) {
                                header('Location: Manage-Memberships.php?updated=1');
                                exit;
                            }

                            $error = 'Unable to verify the saved membership changes.';
                        }
                    }
                } catch (PDOException $e) {
                    $error = 'Unable to save membership changes. Please try again.';
                }
            }
        }
    }
}

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $success = 'Membership level updated successfully.';
}

try {
    $stmt = $pdo->query(
        "SELECT
            id,
            MembershipID,
            MembershipName,
            DiscountRate,
            MinimumSpend
         FROM memberships
         ORDER BY id ASC"
    );

    $memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit('Unable to load membership levels.');
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page loyalty-page">

    <div class="customer-page-header">
        <div>
            <h1>Manage Membership Levels</h1>
            <p>Update membership names, discount rates and minimum spend.</p>
        </div>

        <a href="Loyalty-Membership.php" class="customer-btn customer-btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Customers
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

    <div class="loyalty-level-cards">
        <?php foreach ($memberships as $membership): ?>
            <div class="loyalty-level-card">
                <div class="loyalty-level-card-icon">
                    <i class="fa-solid fa-gem"></i>
                </div>

                <h2>
                    <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <p class="loyalty-level-discount">
                    <?= number_format((float) $membership['DiscountRate'], 2) ?>%
                </p>

                <p class="loyalty-muted">Discount Rate</p>

                <div class="loyalty-level-minimum">
                    <span>Minimum Spend</span>
                    <strong>
                        $<?= number_format((float) $membership['MinimumSpend'], 2) ?>
                    </strong>
                </div>

                <small>
                    ID: <?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>
                </small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="customer-panel">
        <div class="customer-panel-header">
            <div>
                <h2>Edit Membership Levels</h2>
                <p>Change the fields and click Save Changes for that level.</p>
            </div>
        </div>

        <div class="loyalty-manage-list">
            <?php foreach ($memberships as $membership): ?>
                <form method="POST" class="loyalty-manage-form">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                    <input
                        type="hidden"
                        name="membership_id"
                        value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="loyalty-manage-heading">
                        <span class="loyalty-level-badge">
                            <?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <strong>
                            <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                        </strong>
                    </div>

                    <div class="loyalty-manage-fields">
                        <label>
                            Membership Name
                            <input
                                type="text"
                                name="membership_name"
                                maxlength="50"
                                required
                                value="<?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>">
                        </label>

                        <label>
                            Discount Rate (%)
                            <input
                                type="number"
                                name="discount_rate"
                                min="0"
                                max="100"
                                step="0.01"
                                required
                                value="<?= htmlspecialchars((string) $membership['DiscountRate'], ENT_QUOTES, 'UTF-8') ?>">
                        </label>

                        <label>
                            Minimum Spend ($)
                            <input
                                type="number"
                                name="minimum_spend"
                                min="0"
                                step="0.01"
                                required
                                value="<?= htmlspecialchars((string) $membership['MinimumSpend'], ENT_QUOTES, 'UTF-8') ?>">
                        </label>

                        <button
                            type="submit"
                            class="customer-btn customer-btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>

            <?php if (empty($memberships)): ?>
                <p class="customer-empty-state">No membership levels found.</p>
            <?php endif; ?>
        </div>
    </div>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>