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
$success = '';
$customer = null;

// Get customer ID from the dashboard Edit link
$customerID = trim($_GET['id'] ?? $_POST['CustomerID'] ?? '');

if ($customerID === '') {
    header('Location: index.php');
    exit;
}

// Load selected customer
$stmt = $pdo->prepare(
    'SELECT CustomerID, CustomerName, Phone, Email, Address,
            BirthDate, MembershipID, LoyaltyPoints
     FROM customers
     WHERE CustomerID = ?
     LIMIT 1'
);
$stmt->execute([$customerID]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    http_response_code(404);
    $errors[] = 'Customer not found.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['CustomerName'] ?? '');
    $phone = trim($_POST['Phone'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $address = trim($_POST['Address'] ?? '');
    $birthDate = trim($_POST['BirthDate'] ?? '');
    $membershipID = trim($_POST['MembershipID'] ?? '');

    if ($name === '') {
        $errors[] = 'Please enter the customer name.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Customer name must not exceed 100 characters.';
    }

    if ($phone !== '' && mb_strlen($phone) > 20) {
        $errors[] = 'Phone number must not exceed 20 characters.';
    }

    if (
        $email !== '' &&
        (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
            mb_strlen($email) > 150)
    ) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (mb_strlen($address) > 255) {
        $errors[] = 'Address must not exceed 255 characters.';
    }

    if ($birthDate !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $birthDate);

        if (!$date || $date->format('Y-m-d') !== $birthDate) {
            $errors[] = 'Please enter a valid birth date.';
        } elseif ($birthDate > date('Y-m-d')) {
            $errors[] = 'Birth date cannot be in the future.';
        }
    }

    if ($membershipID !== '') {
        $checkMembership = $pdo->prepare(
            'SELECT COUNT(*) FROM memberships WHERE MembershipID = ?'
        );
        $checkMembership->execute([$membershipID]);

        if (!$checkMembership->fetchColumn()) {
            $errors[] = 'Please select a valid membership level.';
        }
    }

    if ($phone !== '') {
        $checkPhone = $pdo->prepare(
            'SELECT COUNT(*) FROM customers
             WHERE Phone = ? AND CustomerID <> ?'
        );
        $checkPhone->execute([$phone, $customerID]);

        if ($checkPhone->fetchColumn() > 0) {
            $errors[] = 'This phone number is already used by another customer.';
        }
    }

    if (!$errors) {
        try {
            $update = $pdo->prepare(
                'UPDATE customers
                 SET CustomerName = ?,
                     Phone = ?,
                     Email = ?,
                     Address = ?,
                     BirthDate = ?,
                     MembershipID = ?
                 WHERE CustomerID = ?'
            );

            $update->execute([
                $name,
                $phone !== '' ? $phone : null,
                $email !== '' ? $email : null,
                $address !== '' ? $address : null,
                $birthDate !== '' ? $birthDate : null,
                $membershipID !== '' ? $membershipID : null,
                $customerID
            ]);

            header(
                'Location: index.php?updated=' . urlencode($customerID)
            );
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Unable to update customer. Please try again.';
        }
    }

    // Keep submitted values in the form when validation fails
    $customer['CustomerName'] = $name;
    $customer['Phone'] = $phone;
    $customer['Email'] = $email;
    $customer['Address'] = $address;
    $customer['BirthDate'] = $birthDate;
    $customer['MembershipID'] = $membershipID;
}

// Load membership options
$membershipStmt = $pdo->query(
    'SELECT MembershipID, MembershipName, DiscountRate
     FROM memberships
     ORDER BY id ASC'
);
$memberships = $membershipStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<section class="customer-page">
    <div class="customer-page-header">
        <div>
            <h1>Update Customer</h1>
            <p>Edit customer information and save your changes.</p>
        </div>

        <a href="index.php" class="customer-btn customer-btn-secondary">
            &larr; Back to Customers
        </a>
    </div>

    <?php if ($errors): ?>
        <div class="customer-alert customer-alert-error" role="alert">
            <strong>Please check the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($customer): ?>
        <div class="customer-form-card">
            <div class="customer-form-heading">
                <div>
                    <h2>Customer Information</h2>
                    <p>Customer ID: <strong><?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                </div>
                <span class="customer-membership-badge">
                    <?= htmlspecialchars($customer['MembershipID'] ?: 'No Membership', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <form method="POST" action="">
                <!-- CustomerID is used to identify the record, never changed -->
                <input
                    type="hidden"
                    name="CustomerID"
                    value="<?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="customer-form-grid">
                    <div class="customer-form-group">
                        <label for="CustomerIDDisplay">Customer ID</label>
                        <input
                            id="CustomerIDDisplay"
                            type="text"
                            value="<?= htmlspecialchars($customer['CustomerID'], ENT_QUOTES, 'UTF-8') ?>"
                            readonly>
                    </div>

                    <div class="customer-form-group">
                        <label for="CustomerName">Full Name *</label>
                        <input
                            id="CustomerName"
                            name="CustomerName"
                            type="text"
                            maxlength="100"
                            value="<?= htmlspecialchars($customer['CustomerName'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required>
                    </div>

                    <div class="customer-form-group">
                        <label for="Phone">Phone Number</label>
                        <input
                            id="Phone"
                            name="Phone"
                            type="tel"
                            maxlength="20"
                            value="<?= htmlspecialchars($customer['Phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="customer-form-group">
                        <label for="Email">Email</label>
                        <input
                            id="Email"
                            name="Email"
                            type="email"
                            maxlength="150"
                            value="<?= htmlspecialchars($customer['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="customer-form-group">
                        <label for="BirthDate">Birth Date</label>
                        <input
                            id="BirthDate"
                            name="BirthDate"
                            type="date"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($customer['BirthDate'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="customer-form-group">
                        <label for="MembershipID">Membership Level</label>
                        <select id="MembershipID" name="MembershipID">
                            <option value="">No Membership</option>

                            <?php foreach ($memberships as $membership): ?>
                                <option
                                    value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>"
                                    <?= ($customer['MembershipID'] ?? '') === $membership['MembershipID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                                    (<?= htmlspecialchars((string)$membership['DiscountRate'], ENT_QUOTES, 'UTF-8') ?>%)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="customer-form-group customer-form-full">
                        <label for="Address">Address</label>
                        <textarea
                            id="Address"
                            name="Address"
                            rows="3"
                            maxlength="255"><?= htmlspecialchars($customer['Address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="customer-form-group">
                        <label for="LoyaltyPoints">Loyalty Points</label>
                        <input
                            id="LoyaltyPoints"
                            type="number"
                            value="<?= (int)$customer['LoyaltyPoints'] ?>"
                            readonly>
                        <small>Loyalty points are preserved and cannot be edited here.</small>
                    </div>
                </div>

                <div class="customer-form-actions">
                    <a href="index.php" class="customer-btn customer-btn-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="customer-btn customer-btn-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>