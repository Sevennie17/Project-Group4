<?php

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

try {

    // =========================
    // GET FORM DATA
    // =========================

    $customerName = trim($_POST['customerName'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $birthDate    = trim($_POST['birthDate'] ?? '');
    $membershipID = trim($_POST['membershipID'] ?? '');

    // =========================
    // VALIDATION
    // =========================

    if ($customerName === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Customer name is required.'
        ]);
        exit;
    }

    // =========================
    // CHECK DUPLICATE PHONE
    // =========================

    if ($phone !== '') {

        $check = $con->prepare("
            SELECT CustomerID
            FROM customers
            WHERE Phone = ?
            LIMIT 1
        ");

        $check->execute([$phone]);

        if ($check->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'This phone number already exists.'
            ]);
            exit;
        }
    }

    // =========================
    // GENERATE CUSTOMER ID
    // CJW001, CJW002, CJW003...
    // =========================

    $stmt = $con->query("
        SELECT CustomerID
        FROM customers
        WHERE CustomerID LIKE 'CJW%'
        ORDER BY CustomerID DESC
        LIMIT 1
    ");

    $lastCustomer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastCustomer && !empty($lastCustomer['CustomerID'])) {

        $lastNumber = (int) substr(
            $lastCustomer['CustomerID'],
            3
        );

        $nextNumber = $lastNumber + 1;
    } else {

        $nextNumber = 1;
    }

    $customerID = 'CJW' . str_pad(
        $nextNumber,
        3,
        '0',
        STR_PAD_LEFT
    );

    // =========================
    // CHECK / FIND MEMBERSHIP
    // =========================

    if ($membershipID !== '') {

        $membershipCheck = $con->prepare("
        SELECT MembershipID
        FROM memberships
        WHERE MembershipID = ?
           OR MembershipName = ?
        LIMIT 1
    ");

        $membershipCheck->execute([
            $membershipID,
            $membershipID
        ]);

        $membership = $membershipCheck->fetch(PDO::FETCH_ASSOC);

        if (!$membership) {
            echo json_encode([
                'success' => false,
                'message' => 'Selected membership does not exist.'
            ]);
            exit;
        }

        // Use the real MembershipID from database
        $membershipID = $membership['MembershipID'];
    }

    // =========================
    // INSERT CUSTOMER
    // =========================

    $insert = $con->prepare("
        INSERT INTO customers
        (
            CustomerID,
            CustomerName,
            Phone,
            Address,
            BirthDate,
            MembershipID
        )
        VALUES
        (
            :customerID,
            :customerName,
            :phone,
            :address,
            :birthDate,
            :membershipID
        )
    ");

    $insert->execute([
        ':customerID'   => $customerID,
        ':customerName' => $customerName,
        ':phone'        => ($phone !== '' ? $phone : null),
        ':address'      => ($address !== '' ? $address : null),
        ':birthDate'    => ($birthDate !== '' ? $birthDate : null),
        ':membershipID' => ($membershipID !== '' ? $membershipID : null)
    ]);

    // =========================
    // SUCCESS
    // =========================

    echo json_encode([
        'success' => true,
        'message' => 'Customer created successfully.',
        'customer' => [
            'CustomerID'   => $customerID,
            'CustomerName' => $customerName
        ]
    ]);
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
