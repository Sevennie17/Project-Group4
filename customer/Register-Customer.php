<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../config/database.php';

$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    http_response_code(500);
    exit('Database connection is unavailable.');
}

$page_css = app_base_path() . '/customer/assets/css/customer.css';

$errors = [];
$success = '';

$customer = [
    'CustomerName' => '',
    'Phone' => '',
    'Email' => '',
    'Address' => '',
    'BirthDate' => '',
    'MembershipID' => '',
];

$memberships = [];

try {
    $stmt = $pdo->query(
        'SELECT MembershipID, MembershipName, DiscountRate
     FROM memberships
     ORDER BY id ASC'
    );
    $memberships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $errors[] = 'Unable to load membership options.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($customer as $key => $value) {
        $customer[$key] = trim($_POST[$key] ?? '');
    }

    if ($customer['CustomerName'] === '') {
        $errors[] = 'Please enter the customer full name.';
    }

    if ($customer['Phone'] === '') {
        $errors[] = 'Please enter the phone number.';
    }

    if (
        $customer['Email'] !== '' &&
        !filter_var($customer['Email'], FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (
        $customer['BirthDate'] !== '' &&
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $customer['BirthDate'])
    ) {
        $errors[] = 'Please enter a valid birth date.';
    }

    if ($customer['MembershipID'] !== '') {
        $validMembershipIDs = array_column($memberships, 'MembershipID');

        if (!in_array($customer['MembershipID'], $validMembershipIDs, true)) {
            $errors[] = 'Please select a valid membership.';
        }
    }

    if (!$errors) {
        try {
            // Prevent duplicate phone numbers.
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM customers WHERE Phone = :phone'
            );
            $stmt->execute(['phone' => $customer['Phone']]);

            if ((int) $stmt->fetchColumn() > 0) {
                $errors[] = 'This phone number is already registered.';
            } else {
                $pdo->beginTransaction();

                // Generate the next CJW number safely.
                $stmt = $pdo->query(
                    "SELECT CustomerID
                     FROM customers
                     WHERE CustomerID REGEXP '^CJW[0-9]+$'
                     ORDER BY CAST(SUBSTRING(CustomerID, 4) AS UNSIGNED) DESC
                     LIMIT 1
                     FOR UPDATE"
                );

                $lastID = $stmt->fetchColumn();
                $nextNumber = $lastID
                    ? ((int) substr($lastID, 3)) + 1
                    : 1;

                $customerID = 'CJW' . str_pad(
                    (string) $nextNumber,
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                $stmt = $pdo->prepare(
                    'INSERT INTO customers
                    (
                        CustomerID,
                        CustomerName,
                        Phone,
                        Email,
                        Address,
                        BirthDate,
                        MembershipID,
                        LoyaltyPoints
                    )
                    VALUES
                    (
                        :customer_id,
                        :customer_name,
                        :phone,
                        :email,
                        :address,
                        :birth_date,
                        :membership_id,
                        0
                    )'
                );

                $stmt->execute([
                    'customer_id' => $customerID,
                    'customer_name' => $customer['CustomerName'],
                    'phone' => $customer['Phone'],
                    'email' => $customer['Email'] !== ''
                        ? $customer['Email']
                        : null,
                    'address' => $customer['Address'] !== ''
                        ? $customer['Address']
                        : null,
                    'birth_date' => $customer['BirthDate'] !== ''
                        ? $customer['BirthDate']
                        : null,
                    'membership_id' => $customer['MembershipID'] !== ''
                        ? $customer['MembershipID']
                        : null,
                ]);

                $pdo->commit();

                header(
                    'Location: Loyalty-Membership.php?registered=' .
                        urlencode($customerID)
                );
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Register customer error: ' . $e->getMessage());
            $errors[] = 'Unable to save the customer. Please check the data and try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="customer-page">
    <section class="customer-page-header">
        <div>
            <h1>Register Customer</h1>
            <p class="customer-subtitle">
                Add a new customer to your jewelry shop.
            </p>
        </div>

        <a href="index.php" class="customer-btn customer-btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Customers
        </a>
    </section>

    <?php if ($errors): ?>
        <div class="customer-alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="customer-panel">
        <div class="customer-panel-heading">
            <div>
                <h2>Customer Information</h2>
                <p>Fields marked * are required.</p>
            </div>
        </div>

        <form method="POST" action="Register-Customer.php">
            <div class="customer-form-grid">

                <div class="customer-form-group">
                    <label for="CustomerName">Full Name *</label>
                    <input
                        type="text"
                        id="CustomerName"
                        name="CustomerName"
                        maxlength="100"
                        value="<?= htmlspecialchars($customer['CustomerName'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="customer-form-group">
                    <label for="Phone">Phone Number *</label>
                    <input
                        type="tel"
                        id="Phone"
                        name="Phone"
                        maxlength="20"
                        value="<?= htmlspecialchars($customer['Phone'], ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="customer-form-group">
                    <label for="Email">Email</label>
                    <input
                        type="email"
                        id="Email"
                        name="Email"
                        maxlength="150"
                        value="<?= htmlspecialchars($customer['Email'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="customer-form-group">
                    <label for="BirthDate">Birth Date</label>
                    <input
                        type="date"
                        id="BirthDate"
                        name="BirthDate"
                        value="<?= htmlspecialchars($customer['BirthDate'], ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="customer-form-group customer-form-full">
                    <label for="Address">Address</label>
                    <textarea
                        id="Address"
                        name="Address"
                        rows="3"
                        maxlength="255"><?= htmlspecialchars($customer['Address'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="customer-form-group customer-form-full">
                    <label for="MembershipID">Membership Level</label>
                    <select id="MembershipID" name="MembershipID">
                        <option value="">No Membership</option>

                        <?php foreach ($memberships as $membership): ?>
                            <option
                                value="<?= htmlspecialchars($membership['MembershipID'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $customer['MembershipID'] === $membership['MembershipID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($membership['MembershipName'], ENT_QUOTES, 'UTF-8') ?>
                                (<?= number_format((float) ($membership['DiscountRate'] ?? 0), 2) ?>% discount)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="customer-form-actions">
                <a href="index.php" class="customer-btn customer-btn-outline">
                    Cancel
                </a>

                <button type="submit" class="customer-btn customer-btn-gold">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Customer
                </button>
            </div>
        </form>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>