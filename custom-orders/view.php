<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/app.php';

$orderId = isset($_GET['id']) ? $_GET['id'] : '';

$order = null;


/*
|--------------------------------------------------------------------------
| Load Selected Order from Database
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../config/database.php';

$order = db_fetch_one("
    SELECT
        co.id AS db_id,
        co.CustomOrderID AS id,
        co.CustomerID AS customer_id,

        c.CustomerName AS customer,
        c.Phone AS phone,

        co.CustomerDescription AS description,
        co.DueDate AS due_date,
        co.DeliveryDate AS delivery_date,

        u.UserName AS goldsmith,

        co.Deposit AS deposit,
        co.TotalAmount AS total,

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
", [$orderId, $orderId]);

/*
|--------------------------------------------------------------------------
| Order Not Found
|--------------------------------------------------------------------------
*/

if ($order === null) {
    echo '<h2>Order not found.</h2>';
    exit;
}

include __DIR__ . '/../includes/header.php';

?>

<link rel="stylesheet" href="assets/css/customer-orders.css">

<div class="custom-orders-shell">

    <div class="custom-orders-top">

        <div>
            <div class="custom-orders-breadcrumb">
                <span>Custom Orders</span>
                <i class="fa-solid fa-chevron-right"></i>
                <span>View Order</span>
            </div>

            <h1 class="custom-orders-title">
                Order <span><?php echo e($order['id']); ?></span>
            </h1>

            <p class="custom-orders-subtitle">
                View custom jewelry order information
            </p>
        </div>

        <a href="index.php" class="form-cancel-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back
        </a>

    </div>


    <!-- ORDER INFORMATION -->

    <div class="custom-order-section">

        <div class="form-section-title">

            <div class="form-section-icon">
                <i class="fa-solid fa-gem"></i>
            </div>

            <div>
                <h2>Order Information</h2>
                <p>Customer and jewelry details</p>
            </div>

        </div>


        <div class="form-grid">

            <div class="form-group">

                <label>Customer</label>

                <div class="view-value">
                    <?php echo e($order['customer']); ?>
                </div>

            </div>


            <div class="form-group">

                <label>Phone</label>

                <div class="view-value">
                    <?php echo e($order['phone']); ?>
                </div>

            </div>


            <div class="form-group full-width">

                <label>Jewelry Requirements</label>

                <div class="view-value">
                    <?php echo nl2br(e($order['description'])); ?>
                </div>

            </div>


            <div class="form-group">

                <label>Goldsmith</label>

                <div class="view-value">

                    <?php

                    if (!empty($order['goldsmith'])) {
                        echo e($order['goldsmith']);
                    } else {
                        echo 'Not assigned';
                    }

                    ?>

                </div>

            </div>


            <div class="form-group">

                <label>Status</label>

                <div class="view-value">

                    <?php
                    echo customOrderStatusBadge($order['status']);
                    ?>

                </div>

            </div>


            <div class="form-group">

                <label>Due Date</label>

                <div class="view-value">
                    <?php
                    echo formatCustomOrderDate($order['due_date']);
                    ?>
                </div>

            </div>


            <div class="form-group">

                <label>Delivery Date</label>

                <div class="view-value">
                    <?php
                    echo formatCustomOrderDate($order['delivery_date']);
                    ?>
                </div>

            </div>

        </div>

    </div>


    <!-- DESIGN IMAGE -->

    <div class="custom-order-section">

        <div class="form-section-title">

            <div class="form-section-icon">
                <i class="fa-solid fa-image"></i>
            </div>

            <div>
                <h2>Design Image</h2>
                <p>Customer's jewelry design</p>
            </div>

        </div>


        <?php if (!empty($order['design_image'])): ?>

            <div class="order-design-image">

                <img
                    src="<?php echo e($order['design_image']); ?>"
                    alt="Design Image">

            </div>

        <?php else: ?>

            <div class="no-design-image">
                <i class="fa-solid fa-image"></i>
                <span>No design image uploaded.</span>
            </div>

        <?php endif; ?>

    </div>


</div>