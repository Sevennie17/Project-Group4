<?php

session_start();

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../config/database.php';


// =====================================================
// 1. DEFAULT DATA
// =====================================================

$order = defaultCustomOrder();

$errors = array();


// =====================================================
// 2. HANDLE FORM SUBMIT
// =====================================================

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
        ? $_POST['due_date']
        : '';

    $deliveryDate = isset($_POST['delivery_date'])
        ? $_POST['delivery_date']
        : '';

    $goldsmith = isset($_POST['goldsmith'])
        ? trim($_POST['goldsmith'])
        : '';

    $status = isset($_POST['status'])
        ? $_POST['status']
        : 'Pending';


    // =================================================
    // 3. VALIDATION
    // =================================================

    if ($customer === '') {
        $errors[] = 'Customer name is required.';
    }

    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }

    if ($description === '') {
        $errors[] = 'Jewelry requirements are required.';
    }

    if (!isValidCustomOrderStatus($status)) {
        $errors[] = 'Invalid order status.';
    }


    // =================================================
    // 4. DESIGN IMAGE UPLOAD
    // =================================================


    $designImage = '';

    if (
        isset($_FILES['design_image']) &&
        $_FILES['design_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['design_image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'Design image upload failed.';
        } else {

            $fileName = $_FILES['design_image']['name'];

            if (!isAllowedDesignImage($fileName)) {

                $errors[] =
                    'Only JPG, JPEG, PNG and WEBP images are allowed.';
            } else {

                /*
 * Make sure uploads folder exists
 */
                $uploadDirectory =
                    __DIR__ . DIRECTORY_SEPARATOR .
                    'uploads' . DIRECTORY_SEPARATOR .
                    'designs' . DIRECTORY_SEPARATOR;

                if (!is_dir($uploadDirectory)) {
                    $errors[] = 'uploads/designs folder was not found.';
                }

                /*
 * Check folder permission
 */
                if (
                    empty($errors) &&
                    !is_writable($uploadDirectory)
                ) {
                    $errors[] = 'The uploads/designs folder is not writable.';
                }

                if (empty($errors)) {

                    $extension = strtolower(
                        pathinfo(
                            $_FILES['design_image']['name'],
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
                        $uploadDirectory . $newFileName;



                    if (
                        move_uploaded_file(
                            $_FILES['design_image']['tmp_name'],
                            $uploadPath
                        )
                    ) {

                        $designImage =
                            'uploads/designs/' . $newFileName;
                    } else {

                        $errors[] =
                            'Unable to move uploaded image to uploads/designs/.';
                    }
                } // close empty($errors)

            } // close isAllowedDesignImage else

        } // close upload error else

    } // close $_FILES upload if
    if (empty($errors)) {
        require_once __DIR__ . '/../config/database.php';
        global $con;

        $deposit = (float)($_POST['deposit'] ?? 0);
        $total = (float)($_POST['total'] ?? 0);

        // Map status to StatusID
        $statusRow = db_fetch_one("SELECT StatusID FROM custom_order_status WHERE StatusName = ?", [$status]);
        $statusId = $statusRow ? (int)$statusRow['StatusID'] : 1;

        // Match CustomerID if exists
        $custRow = db_fetch_one("SELECT CustomerID FROM customers WHERE CustomerName = ? OR Phone = ?", [$customer, $phone]);
        $customerId = $custRow ? $custRow['CustomerID'] : null;

        // Generate Custom Order ID
        $customOrderId = 'CO' . date('ymdHis');

        // Insert custom order
        $stmt = $con->prepare("
            INSERT INTO custom_orders (
            CustomOrderID,
            DueDate,
            DeliveryDate,
            Deposit,
            TotalAmount,
            CustomerDescription,
            DesignImage,
            CustomerID,
            StatusID,
            GoldsmithID
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $customOrderId,
            $dueDate,
            $deliveryDate,
            $deposit,
            $total,
            $description,
            $designImage,
            $customerId,
            $statusId,
            $goldsmith ?: null
        ]);

        header('Location: index.php?success=1');
        exit;
    } // close if (empty($errors))


    // =================================================
    // 6. KEEP DATA IF ERROR
    // =================================================

    $order = array(
        'id' => '',
        'customer' => $customer,
        'phone' => $phone,
        'description' => $description,
        'due_date' => $dueDate,
        'delivery_date' => $deliveryDate,
        'goldsmith' => $goldsmith,
        'design_image' => $designImage,
        'status' => $status
    );
} // close POST


// =====================================================
// 7. HEADER
// =====================================================

include __DIR__ . '/../includes/header.php';

?>

<link
    rel="stylesheet"
    href="assets/css/customer-orders.css">


<div class="custom-orders-shell">


    <!-- =================================================
         PAGE HEADER
    ================================================== -->

    <div class="custom-orders-top">

        <div>

            <div class="custom-orders-breadcrumb">

                <span>Custom Orders</span>

                <i class="fa-solid fa-chevron-right"></i>

                <span>New Order</span>

            </div>


            <h1 class="custom-orders-title">
                Create <span>Custom Order</span>
            </h1>


            <p class="custom-orders-subtitle">
                Create a new custom jewelry order and record customer requirements.
            </p>

        </div>

    </div>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if (!empty($errors)) : ?>

        <div class="custom-order-alert error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <div>

                <?php foreach ($errors as $error) : ?>

                    <div>
                        <?php echo e($error); ?>
                    </div>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- =================================================
         FORM
    ================================================== -->

    <form
        method="POST"
        enctype="multipart/form-data"
        class="custom-order-form">


        <!-- =================================================
             CUSTOMER INFORMATION
        ================================================== -->

        <div class="custom-order-section">

            <div class="form-section-title">

                <div class="form-section-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>

                    <h2>Customer Information</h2>

                    <p>
                        Enter customer contact information.
                    </p>

                </div>

            </div>


            <div class="form-grid">


                <!-- Customer Name -->

                <div class="form-group">

                    <label for="customer">
                        Customer Name
                        <span>*</span>
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-user"></i>

                        <input
                            type="text"
                            id="customer"
                            name="customer"
                            value="<?php echo e($order['customer']); ?>"
                            placeholder="Enter customer name"
                            required>

                    </div>

                </div>


                <!-- Phone -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                        <span>*</span>
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-phone"></i>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="<?php echo e($order['phone']); ?>"
                            placeholder="+855 XX XXX XXX"
                            required>

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             JEWELRY INFORMATION
        ================================================== -->

        <div class="custom-order-section">

            <div class="form-section-title">

                <div class="form-section-icon">
                    <i class="fa-solid fa-gem"></i>
                </div>

                <div>

                    <h2>Jewelry Information</h2>

                    <p>
                        Record the customer's custom jewelry requirements.
                    </p>

                </div>

            </div>


            <div class="form-grid">


                <!-- Requirements -->

                <div class="form-group full-width">

                    <label for="description">

                        Jewelry Type & Requirements

                        <span>*</span>

                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Example: Custom engagement ring, gold 18K, diamond stone, size 7..."
                        required><?php echo e($order['description']); ?></textarea>

                </div>


                <!-- Goldsmith -->

                <div class="form-group">

                    <label for="goldsmith">
                        Goldsmith
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-hammer"></i>

                        <select
                            id="goldsmith"
                            name="goldsmith">

                            <option value="">-- Select Goldsmith --</option>

                            <?php
                            $goldsmithUsers = db_fetch_all("
                                SELECT UserID, UserName
                                FROM users
                                ORDER BY UserName ASC
                            ");

                            foreach ($goldsmithUsers as $user):
                            ?>

                                <option
                                    value="<?php echo e($user['UserID']); ?>"
                                    <?php
                                    echo ($order['goldsmith'] === $user['UserID'])
                                        ? 'selected'
                                        : '';
                                    ?>>
                                    <?php echo e($user['UserName']); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <!-- Status -->

                <div class="form-group">

                    <label for="status">
                        Order Status
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-list-check"></i>

                        <select
                            id="status"
                            name="status">

                            <?php foreach (
                                customOrderStatuses()
                                as $status
                            ) : ?>

                                <option
                                    value="<?php echo e($status); ?>"
                                    <?php
                                    if (
                                        $order['status'] === $status
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>>

                                    <?php echo e($status); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             DESIGN IMAGE
        ================================================== -->

        <div class="custom-order-section">

            <div class="form-section-title">

                <div class="form-section-icon">
                    <i class="fa-solid fa-image"></i>
                </div>

                <div>

                    <h2>Design Image</h2>

                    <p>
                        Upload the customer's jewelry design.
                    </p>

                </div>

            </div>


            <div class="design-upload-box">

                <i class="fa-solid fa-cloud-arrow-up"></i>

                <h3>
                    Upload Design Image
                </h3>

                <p>
                    JPG, JPEG, PNG or WEBP
                </p>


                <input
                    type="file"
                    id="design_image"
                    name="design_image"
                    accept=".jpg,.jpeg,.png,.webp">


                <div
                    id="file-name"
                    class="selected-file"></div>

            </div>

        </div>


        <!-- =================================================
             SCHEDULE
        ================================================== -->

        <div class="custom-order-section">

            <div class="form-section-title">

                <div class="form-section-icon">

                    <i class="fa-solid fa-calendar-days"></i>

                </div>

                <div>

                    <h2>Schedule</h2>

                    <p>
                        Set production and delivery dates.
                    </p>

                </div>

            </div>


            <div class="form-grid">


                <!-- Due Date -->

                <div class="form-group">

                    <label for="due_date">
                        Due Date
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-calendar"></i>

                        <input
                            type="date"
                            id="due_date"
                            name="due_date"
                            value="<?php echo e($order['due_date']); ?>">

                    </div>

                </div>


                <!-- Delivery Date -->

                <div class="form-group">

                    <label for="delivery_date">
                        Delivery Date
                    </label>

                    <div class="input-with-icon">

                        <i class="fa-solid fa-truck"></i>

                        <input
                            type="date"
                            id="delivery_date"
                            name="delivery_date"
                            value="<?php echo e($order['delivery_date']); ?>">

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             FORM BUTTONS
        ================================================== -->

        <div class="form-actions">


            <a
                href="index.php"
                class="form-cancel-btn">

                <i class="fa-solid fa-arrow-left"></i>

                Cancel

            </a>


            <button
                type="submit"
                class="form-save-btn">

                <i class="fa-solid fa-floppy-disk"></i>

                Save Custom Order

            </button>


        </div>


    </form>

</div>


<?php
include __DIR__ . '/../includes/footer.php';
?>​