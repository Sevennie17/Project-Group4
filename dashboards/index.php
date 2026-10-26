<?php include __DIR__ . '/../includes/header.php'; ?>

<link rel="stylesheet" href="/Jewelry-app/assets/css/style.css">
<link rel="stylesheet" href="/Jewelry-app/dashboards/assets/css/dashboard.css">

<div class="dashboard-content">

    <!-- Dashboard Header -->
    <section class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back! Here's an overview</p>
        </div>
    </section>

    <!-- Dashboard Statistics -->
    <?php include __DIR__ . '/components/start-cards.php'; ?>

    <!-- Charts -->
    <?php include __DIR__ . '/components/charts.php'; ?>

    <!-- Tables -->
    <div class="dashboard-grid">

        <?php include __DIR__ . '/components/low-stock.php'; ?>

        <?php include __DIR__ . '/components/best-selling.php'; ?>

    </div>

    <!-- Orders -->
    <?php include __DIR__ . '/components/orders.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/Jewelry-app/dashboards/assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>