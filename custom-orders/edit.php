<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? '';

$orderId = isset($_GET['id']) ? $_GET['id'] : '';

$order = null;

/*
|--------------------------------------------------------------------------
| Load Order from Database
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        co.id AS db_id,
        co.CustomOrderID AS id,
        co.CustomerID AS customer_id,
        c.CustomerName AS customer,
        c.Phone AS phone,
        co.CustomerDescription AS description,
        co.DueDate AS due_date,
        co.DeliveryDate AS delivery_date,
        co.Deposit AS deposit,
        co.TotalAmount AS total,
        u.UserID AS goldsmith_id,
        u.UserName AS goldsmith,
        s.StatusName AS status,
        co.DesignImage AS design_image

    FROM custom_orders co

    LEFT JOIN customers c
        ON co.CustomerID = c.CustomerID

    LEFT JOIN users u
        ON co.GoldsmithID = u.UserID

    LEFT JOIN custom_order_status s
        ON co.StatusID = s.StatusID

    WHERE co.id = ? OR co.CustomOrderID = ?
";

$order = db_fetch_one(
    $sql,
    [$id, $id]
);


/*
|--------------------------------------------------------------------------
| Order Not Found
|--------------------------------------------------------------------------
*/

if ($order === null) {

    echo '<h2>Order not found.</h2>';
    exit;
}


/*
|--------------------------------------------------------------------------
| Update Order
|--------------------------------------------------------------------------
*/

$errors = array();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customer = isset($_POST['customer'])
        ? trim($_POST['customer'])
        : '';

    $phone = isset($_POST['phone'])
        ? trim($_POST['phone'])
        : '';

    $description = isset($_POST['description'])
        ? trim($_POST['description'])
        : '';

    $dueDate = isset($_POST['due_date'])
        ? trim($_POST['due_date'])
        : '';

    $deliveryDate = isset($_POST['delivery_date'])
        ? trim($_POST['delivery_date'])
        : '';

    $goldsmith = isset($_POST['goldsmith'])
        ? trim($_POST['goldsmith'])
        : '';

    $status = isset($_POST['status'])
        ? trim($_POST['status'])
        : 'Pending';


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($customer === '') {
        $errors[] = 'Customer is required.';
    }

    if ($phone === '') {
        $errors[] = 'Phone is required.';
    }

    if ($description === '') {
        $errors[] = 'Jewelry requirements are required.';
    }

    if ($dueDate === '') {
        $errors[] = 'Due date is required.';
    }

    if (!isValidCustomOrderStatus($status)) {
        $errors[] = 'Invalid order status.';
    }


    /*
    |--------------------------------------------------------------------------
    | Design Image
    |--------------------------------------------------------------------------
    */

    $designImage = $order['design_image'];

    if (
        isset($_FILES['design_image']) &&
        $_FILES['design_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $fileName = $_FILES['design_image']['name'];
        $tmpName = $_FILES['design_image']['tmp_name'];

        if (!isAllowedDesignImage($fileName)) {

            $errors[] =
                'Only JPG, JPEG, PNG and WEBP images are allowed.';
        } else {

            $uploadDirectory = __DIR__ . '/uploads/designs/';

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            $extension =
                strtolower(
                    pathinfo(
                        $fileName,
                        PATHINFO_EXTENSION
                    )
                );

            $newFileName =
                'design_' .
                date('Ymd_His') .
                '_' .
                mt_rand(1000, 9999) .
                '.' .
                $extension;

            $uploadPath =
                $uploadDirectory .
                $newFileName;

            if (
                move_uploaded_file(
                    $tmpName,
                    $uploadPath
                )
            ) {

                $designImage =
                    'uploads/designs/' .
                    $newFileName;
            } else {

                $errors[] =
                    'Unable to save design image.';
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Save Updated Order
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        // Find CustomerID
        $custRow = db_fetch_one(
            "SELECT CustomerID
         FROM customers
         WHERE CustomerName = ? OR Phone = ?",
            [$customer, $phone]
        );

        $customerId = $custRow
            ? $custRow['CustomerID']
            : $order['customer_id'];

        // Find GoldsmithID
        $goldsmithRow = db_fetch_one(
            "SELECT UserID
         FROM users
         WHERE UserName = ? OR UserID = ?",
            [$goldsmith, $goldsmith]
        );

        $goldsmithId = $goldsmithRow
            ? $goldsmithRow['UserID']
            : null;

        // Map status to StatusID
        $statusRow = db_fetch_one(
            "SELECT StatusID
         FROM custom_order_status
         WHERE StatusName = ?",
            [$status]
        );

        $statusId = $statusRow
            ? (int)$statusRow['StatusID']
            : 1;

        // Update custom order
        $stmt = $con->prepare("
        UPDATE custom_orders
        SET
            CustomerID = ?,
            CustomerDescription = ?,
            DueDate = ?,
            DeliveryDate = ?,
            GoldsmithID = ?,
            StatusID = ?,
            DesignImage = ?
        WHERE id = ? OR CustomOrderID = ?
    ");

        $stmt->execute([
            $customerId,
            $description,
            $dueDate,
            $deliveryDate ?: null,
            $goldsmithId,
            $statusId,
            $designImage,
            $order['db_id'],
            $order['id']
        ]);

        header(
            'Location: index.php?success=1'
        );
        exit;
    } // close if (empty($errors))
} // close if ($_SERVER['REQUEST_METHOD'] === 'POST')

include __DIR__ . '/../includes/header.php';

?>

<link rel="stylesheet" href="assets/css/customer-orders.css">
<link rel="stylesheet" href="assets/css/edit-order.css">

<div class="custom-orders-shell">

    <!-- Header -->

    <div class="custom-orders-top">

        <div>

            <div class="custom-orders-breadcrumb">

                <span>Custom Orders</span>

                <i class="fa-solid fa-chevron-right"></i>

                <span>Edit Order</span>

            </div>

            <h1 class="custom-orders-title">

                Edit Order
                <span><?php echo e($order['id']); ?></span>

            </h1>

            <p class="custom-orders-subtitle">

                Update custom jewelry order information

            </p>

        </div>


        <a
            href="view.php?id=<?php echo urlencode($order['id']); ?>"
            class="form-cancel-btn">

            <i class="fa-solid fa-arrow-left"></i>

            Back

        </a>

    </div>


    <!-- Error Messages -->

    <?php if (!empty($errors)): ?>

        <div class="form-alert error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <?php foreach ($errors as $error): ?>

                    <div>
                        <?php echo e($error); ?>
                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- Edit Form -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="custom-order-form">

        <div class="custom-order-section">

            <div class="form-section-title">

                <div class="form-section-icon">

                    <i class="fa-solid fa-gem"></i>

                </div>

                <div>

                    <h2>Order Information</h2>

                    <p>
                        Update customer and jewelry details
                    </p>

                </div>

            </div>


            <div class="form-grid">


                <!-- Customer -->

                <div class="form-group">

                    <label for="customer">
                        Customer
                    </label>

                    <input
                        type="text"
                        id="customer"
                        name="customer"
                        value="<?php echo e($order['customer']); ?>"
                        required>

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label for="phone">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="<?php echo e($order['phone']); ?>"
                        required>

                </div>


                <!-- Description -->

                <div class="form-group full-width">

                    <label for="description">
                        Jewelry Requirements
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        required><?php echo e($order['description']); ?></textarea>

                </div>


                <!-- Goldsmith -->

                <div class="form-group">

                    <label for="goldsmith">
                        Goldsmith
                    </label>

                    <input
                        type="text"
                        id="goldsmith"
                        name="goldsmith"
                        value="<?php echo e($order['goldsmith']); ?>">

                </div>


                <!-- Status -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status">

                        <?php foreach (
                            customOrderStatuses()
                            as $status
                        ): ?>

                            <option
                                value="<?php echo e($status); ?>"
                                <?php
                                if (
                                    $order['status'] ===
                                    $status
                                ) {
                                    echo 'selected';
                                }
                                ?>>
                                <?php echo e($status); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Due Date -->

                <div class="form-group">

                    <label for="due_date">
                        Due Date
                    </label>

                    <input
                        type="date"
                        id="due_date"
                        name="due_date"
                        value="<?php echo e($order['due_date']); ?>"
                        required>

                </div>


                <!-- Delivery Date -->

                <div class="form-group">

                    <label for="delivery_date">
                        Delivery Date
                    </label>

                    <input
                        type="date"
                        id="delivery_date"
                        name="delivery_date"
                        value="<?php echo e($order['delivery_date']); ?>">

                </div>


                <!-- Design Image -->

                <div class="form-group full-width">

                    <label for="design_image">
                        Design Image
                    </label>

                    <?php if (!empty($order['design_image'])): ?>

                        <div class="current-design-image">

                            <img
                                src="<?php echo e($order['design_image']); ?>"
                                alt="Current Design">

                            <span>
                                Current design image
                            </span>

                        </div>

                    <?php endif; ?>


                    <input
                        type="file"
                        id="design_image"
                        name="design_image"
                        accept=".jpg,.jpeg,.png,.webp">

                    <small>
                        JPG, JPEG, PNG or WEBP
                    </small>

                </div>

            </div>

        </div>


        <!-- Buttons -->

        <div class="form-actions">

            <a
                href="view.php?id=<?php echo urlencode($order['id']); ?>"
                class="form-cancel-btn">

                Cancel

            </a>

            <button
                type="submit"
                class="form-submit-btn">

                <i class="fa-solid fa-floppy-disk"></i>

                Update Order

            </button>

        </div>

    </form>

</div>