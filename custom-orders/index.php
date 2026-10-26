<?php

// ==========================================
// START SESSION
// ==========================================

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}




// ==========================================
// CUSTOM JEWELRY ORDERS DATA FROM DATABASE
// ==========================================

require_once __DIR__ . '/../config/database.php';


$orders = db_fetch_all("
    SELECT
        co.id AS db_id,
        co.CustomOrderID AS id,
        c.CustomerName AS customer,
        c.Phone AS phone,
        co.CustomerDescription AS description,
        co.DueDate AS due_date,
        co.DeliveryDate AS delivery_date,
        u.UserName AS goldsmith,
        co.Deposit AS deposit,
        co.TotalAmount AS total,
        cos.StatusName AS status,
        co.DesignImage AS design_image

    FROM custom_orders co

    LEFT JOIN customers c
        ON c.CustomerID = co.CustomerID

    LEFT JOIN custom_order_status cos
        ON cos.StatusID = co.StatusID

    LEFT JOIN users u
        ON u.UserID = co.GoldsmithID

    ORDER BY co.id DESC
");



// ==========================================
// CALCULATE ORDER COUNTS
// PHP 7.3 COMPATIBLE
// ==========================================


$totalOrders = count($orders);

$pendingCount = 0;
$designingCount = 0;
$manufacturingCount = 0;
$qualityCount = 0;
$readyCount = 0;
$deliveredCount = 0;

foreach ($orders as $order) {

    switch ($order['status']) {

        case 'Pending':
            $pendingCount++;
            break;

        case 'Designing':
            $designingCount++;
            break;

        case 'Manufacturing':
            $manufacturingCount++;
            break;

        case 'Quality Inspection':
            $qualityCount++;
            break;

        case 'Ready for Pickup':
            $readyCount++;
            break;

        case 'Delivered':
            $deliveredCount++;
            break;
    }
}


// ==========================================
// HEADER / SIDEBAR
// ==========================================

include __DIR__ . '/../includes/header.php';

?>

<!-- CUSTOM ORDERS CSS -->
<link rel="stylesheet" href="assets/css/customer-orders.css">


<!-- ==========================================
     CUSTOM ORDERS PAGE
========================================== -->

<div class="custom-orders-shell">


    <!-- PAGE HEADER -->

    <div class="custom-orders-top">

        <div class="custom-orders-title-wrap">

            <div class="custom-orders-breadcrumb">

                <span>Dashboard</span>

                <i class="fa-solid fa-chevron-right"></i>

                <span>Customer Orders</span>

            </div>


            <h1 class="custom-orders-title">
                Custom <span>Orders</span>
            </h1>


            <p class="custom-orders-subtitle">
                Track and manage custom jewelry requests
            </p>

        </div>


        <a href="add.php" class="new-order-btn">

            <i class="fa-solid fa-plus"></i>

            New Custom Order

        </a>

    </div>



    <!-- ==========================================
         STATISTICS
    =========================================== -->

    <div class="stats-grid">


        <!-- TOTAL ORDERS -->

        <div class="stat-card">

            <div class="stat-icon-box total">

                <i class="fa-solid fa-list-check"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $totalOrders; ?>
                </div>

                <div class="stat-label">
                    Total Orders
                </div>

            </div>

        </div>



        <!-- PENDING -->

        <div class="stat-card">

            <div class="stat-icon-box pending">

                <i class="fa-regular fa-clock"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $pendingCount; ?>
                </div>

                <div class="stat-label">
                    Pending
                </div>

            </div>

        </div>



        <!-- DESIGNING -->

        <div class="stat-card">

            <div class="stat-icon-box designing">

                <i class="fa-solid fa-pen-ruler"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $designingCount; ?>
                </div>

                <div class="stat-label">
                    Designing
                </div>

            </div>

        </div>



        <!-- MANUFACTURING -->

        <div class="stat-card">

            <div class="stat-icon-box manufacturing">

                <i class="fa-solid fa-hammer"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $manufacturingCount; ?>
                </div>

                <div class="stat-label">
                    Manufacturing
                </div>

            </div>

        </div>



        <!-- QUALITY INSPECTION -->

        <div class="stat-card">

            <div class="stat-icon-box quality">

                <i class="fa-solid fa-magnifying-glass"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $qualityCount; ?>
                </div>

                <div class="stat-label">
                    Quality Inspection
                </div>

            </div>

        </div>



        <!-- READY FOR PICKUP -->

        <div class="stat-card">

            <div class="stat-icon-box ready">

                <i class="fa-solid fa-box-open"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $readyCount; ?>
                </div>

                <div class="stat-label">
                    Ready for Pickup
                </div>

            </div>

        </div>



        <!-- DELIVERED -->

        <div class="stat-card">

            <div class="stat-icon-box delivered">

                <i class="fa-solid fa-circle-check"></i>

            </div>

            <div>

                <div class="stat-number">
                    <?php echo $deliveredCount; ?>
                </div>

                <div class="stat-label">
                    Delivered
                </div>

            </div>

        </div>


    </div>



    <!-- ==========================================
         ORDERS PANEL
    =========================================== -->

    <div class="orders-panel">


        <!-- PANEL HEADER -->

        <div class="panel-header">


            <div class="panel-title">

                <div class="panel-icon">

                    <i class="fa-solid fa-gem"></i>

                </div>


                <div>

                    <h2>
                        Custom Jewelry Orders
                    </h2>

                    <p>
                        Manage customer custom jewelry orders
                    </p>

                </div>

            </div>



            <!-- SEARCH AND FILTER -->

            <div class="order-tools">


                <div class="search-box">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        id="orderSearch"
                        placeholder="Search orders...">

                </div>



                <select id="statusFilter">

                    <option value="all">
                        All Status
                    </option>

                    <option value="Pending">
                        Pending
                    </option>

                    <option value="Designing">
                        Designing
                    </option>

                    <option value="Manufacturing">
                        Manufacturing
                    </option>

                    <option value="Quality Inspection">
                        Quality Inspection
                    </option>

                    <option value="Ready for Pickup">
                        Ready for Pickup
                    </option>

                    <option value="Delivered">
                        Delivered
                    </option>

                    <option value="Cancelled">
                        Cancelled
                    </option>

                </select>


            </div>

        </div>



        <!-- ==========================================
             ORDERS TABLE
        =========================================== -->

        <div class="orders-table-wrapper">

            <table class="orders-table">


                <thead>

                    <tr>

                        <th>Order #</th>

                        <th>Customer</th>

                        <th>Jewelry</th>

                        <th>Design</th>

                        <th>Goldsmith</th>
                        ​​​​​​​
                        <th>Due / Delivery</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>



                <tbody id="ordersTableBody">


                    <?php foreach ($orders as $order): ?>


                        <tr
                            data-status="<?php echo htmlspecialchars($order['status'] ?? ''); ?>"
                            data-search="<?php echo htmlspecialchars(
                                                $order['id'] . ' ' .
                                                    ($order['customer'] ?? '') . ' ' .
                                                    ($order['description'] ?? '') . ' ' .
                                                    ($order['goldsmith'] ?? '')
                                            ); ?>">

                            <!-- ORDER ID -->


                            <td>

                                <strong class="order-id">

                                    <?php echo htmlspecialchars($order['id']); ?>

                                </strong>

                            </td>



                            <!-- CUSTOMER -->

                            <td>
                                <div class="customer-cell">

                                    <div>
                                        <span class="customer-name">
                                            <?php
                                            echo htmlspecialchars(
                                                $order['customer'] ?? ''
                                            );
                                            ?>
                                        </span>

                                        <span class="customer-phone">
                                            <?php
                                            echo htmlspecialchars(
                                                $order['phone'] ?? ''
                                            );
                                            ?>
                                        </span>
                                    </div>

                                </div>
                            </td>
                            <!-- JEWELRY -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $order['description']
                                );
                                ?>

                            </td>



                            <!-- DESIGN -->

                            <td>

                                <div class="design-cell">


                                    <div class="design-preview">

                                        <?php if (!empty($order['design_image'])): ?>

                                            <img
                                                src="<?php echo htmlspecialchars($order['design_image']); ?>"
                                                alt="Jewelry Design">

                                        <?php else: ?>

                                            <i class="fa-regular fa-image"></i>

                                        <?php endif; ?>

                                    </div>


                                    <span class="design-count">

                                        <?php
                                        echo !empty($order['design_image'])
                                            ? 'Design'
                                            : 'No Image';
                                        ?>

                                    </span>


                                </div>

                            </td>



                            <!-- GOLDSMITH -->

                            <td>

                                <?php if (!empty($order['goldsmith'])): ?>

                                    <div class="goldsmith-cell">

                                        <i class="fa-solid fa-user-gear goldsmith-icon"></i>

                                        <span>

                                            <?php
                                            echo htmlspecialchars(
                                                $order['goldsmith']
                                            );
                                            ?>

                                        </span>

                                    </div>

                                <?php else: ?>

                                    <span class="unassigned">
                                        Not Assigned
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ⭐ DUE / DELIVERY -->

                            <td class="date-info">

                                <div class="date-row">
                                    <span class="date-label">Due:</span>

                                    <span>
                                        <?php
                                        echo date(
                                            'M d, Y',
                                            strtotime($order['due_date'])
                                        );
                                        ?>
                                    </span>
                                </div>

                                <div class="date-row">
                                    <span class="date-label">Delivery:</span>

                                    <span>
                                        <?php
                                        if (!empty($order['delivery_date'])) {
                                            echo date(
                                                'M d, Y',
                                                strtotime($order['delivery_date'])
                                            );
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>

                                <!-- STATUS -->

                            <td>

                                <?php

                                // PHP 7.3 compatible
                                $statusClass = 'status-pending';

                                switch ($order['status']) {

                                    case 'Pending':
                                        $statusClass = 'status-pending';
                                        break;

                                    case 'Designing':
                                        $statusClass = 'status-designing';
                                        break;

                                    case 'Manufacturing':
                                        $statusClass = 'status-manufacturing';
                                        break;

                                    case 'Quality Inspection':
                                        $statusClass = 'status-quality';
                                        break;

                                    case 'Ready for Pickup':
                                        $statusClass = 'status-ready';
                                        break;

                                    case 'Delivered':
                                        $statusClass = 'status-delivered';
                                        break;

                                    case 'Cancelled':
                                        $statusClass = 'status-cancelled';
                                        break;
                                }

                                ?>


                                <span class="status-badge <?php echo $statusClass; ?>">

                                    <?php
                                    echo htmlspecialchars(
                                        $order['status']
                                    );
                                    ?>

                                </span>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div class="action-buttons">


                                    <a
                                        href="view.php?id=<?php echo urlencode($order['id']); ?>"
                                        class="action-btn"
                                        title="View Order">

                                        <i class="fa-regular fa-eye"></i>

                                    </a>



                                    <a
                                        href="edit.php?id=<?php echo urlencode($order['id']); ?>"
                                        class="action-btn"
                                        title="Edit Order">

                                        <i class="fa-regular fa-pen-to-square"></i>

                                    </a>


                                    ​​​​​​​​​​​​​​​​​​​​<form
                                        action="delete.php"
                                        method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this order?');">
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php echo htmlspecialchars($order['id']); ?>">

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete Order">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>

                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>

        </div>



        <!-- TABLE FOOTER -->

        <div class="orders-footer">

            <span>

                Showing

                <strong id="visibleCount">

                    <?php echo count($orders); ?>

                </strong>

                orders

            </span>

        </div>


    </div>


</div>



<!-- CUSTOMER ORDERS JAVASCRIPT -->

<script src="assets/js/customer-orders.js"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>