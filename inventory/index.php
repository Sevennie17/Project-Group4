<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$con = $con ?? null;

if (!$con instanceof PDO) {
    exit('Database connection is not available.');
}

$basePath = function_exists('app_base_path')
    ? rtrim(app_base_path(), '/')
    : '/Jewelry-app';

$pageTitle = 'Inventory Management';
$errorMessage = '';
$products = [];
$totalProducts = 0;
$totalUnits = 0;
$lowStockCount = 0;
$inventoryValue = 0;
$totalGoldWeight = null;
$hasBarcode = false;
$hasGoldWeight = false;
$lowStockLimit = 5;

try {
    // Detect optional columns without assuming they exist.
    $columns = $con->query("SHOW COLUMNS FROM Products")
        ->fetchAll(PDO::FETCH_COLUMN);
    $columnMap = [];

    foreach ($columns as $column) {
        $columnMap[strtolower($column)] = $column;
    }
    $hasLowStockThreshold = isset($columnMap['lowstockthreshold']);

    $lowStockLimit = $hasLowStockThreshold ? null : 5;

    $barcodeColumn = null;
    $goldWeightColumn = null;

    foreach (['barcode', 'bar_code', 'barcodevalue'] as $name) {
        if (isset($columnMap[$name])) {
            $barcodeColumn = $columnMap[$name];
            break;
        }
    }

    foreach (
        [
            'goldweight',
            'goldweightgrams',
            'gold_weight',
            'gold_weight_grams'
        ] as $name
    ) {
        if (isset($columnMap[$name])) {
            $goldWeightColumn = $columnMap[$name];
            break;
        }
    }

    $hasBarcode = $barcodeColumn !== null;
    $hasGoldWeight = $goldWeightColumn !== null;

    // Main inventory statistics.

    $lowStockCondition = $hasLowStockThreshold
        ? 'StockQuantity <= LowStockThreshold'
        : 'StockQuantity <= 5';

    $stats = $con->query("
    SELECT
        COUNT(*) AS total_products,
        COALESCE(SUM(StockQuantity), 0) AS total_units,
        COALESCE(
            SUM(StockQuantity * PurchasePrice), 0
        ) AS inventory_value,
        COALESCE(
            SUM(
                CASE
                    WHEN {$lowStockCondition}
                    THEN 1
                    ELSE 0
                END
            ), 0
        ) AS low_stock_count
    FROM Products
")->fetch(PDO::FETCH_ASSOC);

    $totalProducts = (int)$stats['total_products'];
    $totalUnits = (int)$stats['total_units'];
    $inventoryValue = (float)$stats['inventory_value'];
    $lowStockCount = (int)$stats['low_stock_count'];

    // Total gold weight, only when a matching column exists.
    if ($hasGoldWeight) {
        $weightSql = "
            SELECT COALESCE(
                SUM(StockQuantity * `{$goldWeightColumn}`), 0
            )
            FROM Products
        ";

        $totalGoldWeight = (float) $con->query(
            $weightSql
        )->fetchColumn();
    }

    // Low-stock products.

    $productSql = "
    SELECT
        ProductID,
        ProductName,
        SKU,
        StockQuantity,
        PurchasePrice,
        SellingPrice
    FROM Products
    WHERE {$lowStockCondition}
    ORDER BY StockQuantity ASC, ProductName ASC
    LIMIT 10
";

    $products = $con->query($productSql)
        ->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Inventory dashboard: ' . $e->getMessage());
    $errorMessage = 'Unable to load inventory data. Please check the database.';
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    /* ===== ANAJAK JEWELRY - PREMIUM INVENTORY DASHBOARD ===== */


    /* ===== INVENTORY DASHBOARD LAYOUT FIX ===== */

    .inventory-page {
        box-sizing: border-box;
        width: 100%;
        max-width: 1600px;
        margin: 0 auto;
        padding: 28px;
        background: #faf9f6;
        color: #2b2b2b;
    }

    .inventory-page *,
    .inventory-page *::before,
    .inventory-page *::after {
        box-sizing: border-box;
    }

    /* Four statistic cards in one row */
    .inventory-page .inventory-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        width: 100%;
        margin: 0 0 26px;
    }

    .inventory-page .inventory-stat {
        display: block;
        width: 100%;
        min-width: 0;
        padding: 22px;
        border: 1px solid #ece6d8;
        border-radius: 15px;
        background: #ffffff;
        box-shadow: 0 4px 14px rgba(80, 60, 10, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .inventory-page .inventory-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(80, 60, 10, 0.08);
    }


    .inventory-page .inventory-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .inventory-page .inventory-stat-icon {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
        background: #f8f1df;
        color: #b18a1d;
    }

    .inventory-page .inventory-stat h2 {
        margin: 18px 0 7px;
        font-size: 27px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .inventory-page .inventory-stat small {
        display: block;
        line-height: 1.6;
        color: #817b70;
    }

    /* Quick actions: three equal columns */

    .inventory-page .inventory-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        align-items: stretch;
    }

    .inventory-page .inventory-action {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }


    .inventory-page .inventory-actions {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 16px !important;
        align-items: stretch;
        width: 100%;
    }


    .inventory-page .inventory-action {
        display: flex !important;
        align-items: center;
        gap: 15px;
        min-width: 0;
        min-height: 92px;
        padding: 18px 20px;
        border: 1px solid #eee6d3;
        border-radius: 15px;
        background: linear-gradient(145deg, #ffffff 0%, #fffdf8 100%);
        box-shadow: 0 3px 12px rgba(92, 69, 15, 0.035);
        text-decoration: none;
        transition: border-color .22s ease,
            box-shadow .22s ease,
            transform .22s ease,
            background .22s ease;
    }

    .inventory-page .inventory-action:hover {
        border-color: #d6bd70;
        background: linear-gradient(145deg,
                #fffdf7 0%,
                #faf3df 100%);
        box-shadow: 0 8px 20px rgba(92, 69, 15, 0.085);
        transform: translateY(-3px);
    }

    .inventory-page .inventory-action:hover strong {
        color: #9b7618;
    }

    .inventory-page .inventory-action:hover small {
        color: #716344;
    }

    .inventory-page .inventory-action:focus-visible {
        outline: 3px solid #d8bd68;
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .inventory-page .inventory-action {
            transition: none;
        }

        .inventory-page .inventory-action:hover {
            transform: none;
        }
    }


    .inventory-page .inventory-action>span:last-child {
        display: block;
        flex: 1 1 0;
        min-width: 0;
        overflow-wrap: anywhere;
    }


    .inventory-page .inventory-action strong {
        display: block;
        margin-bottom: 5px;
        color: #302817;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: 0.1px;
    }

    .inventory-page .inventory-action small {
        display: block;
        color: #89816f;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.5;
    }


    /* Clean section and table layout */
    .inventory-page .inventory-section {
        display: block;
        width: 100%;
        min-width: 0;
        margin-bottom: 24px;
        padding: 23px;
        border: 1px solid #ebe6dc;
        border-radius: 15px;
        background: #fff;
        box-shadow: 0 4px 15px rgba(80, 60, 10, .025);
    }


    .inventory-page .inventory-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 20px;
    }

    .inventory-page .inventory-section-header h2 {
        margin: 0;
        color: #302817;
        font-size: 18px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .inventory-page .inventory-actions {
        gap: 16px;
    }


    .inventory-page .inventory-table-wrap {
        display: block;
        width: 100%;
        overflow-x: auto;
    }

    .inventory-page .inventory-table {
        width: 100%;
        min-width: 650px;
        border-collapse: collapse;
    }

    .inventory-page .inventory-table th {
        background: #faf8f3;
        color: #777064;
        white-space: nowrap;
    }

    .inventory-page .inventory-table th,
    .inventory-page .inventory-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #eee9df;
        text-align: left;
        font-size: 12px;
    }

    .inventory-page .inventory-table tbody tr:hover {
        background: #fdfbf6;
    }

    .inventory-page .inventory-badge {
        display: inline-block;
        white-space: nowrap;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Gold weight summary */
    .inventory-page .inventory-section>h2 {
        color: #b18a1d;
        font-size: 28px;
        margin: 16px 0 8px;
    }

    .inventory-page .inventory-section>p {
        line-height: 1.7;
        font-size: 13px;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .inventory-page .inventory-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .inventory-page .inventory-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }

    @media (max-width: 600px) {
        .inventory-page {
            padding: 14px;
        }

        .inventory-page .inventory-stats,
        .inventory-page .inventory-actions {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .inventory-page .inventory-section {
            padding: 15px;
        }
    }

    /* Inventory heading refinement */

    .inventory-page .inventory-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
        padding: 8px 0 4px;
    }

    .inventory-page .inventory-heading h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 8px;
        color: #29261f;
        font-size: 30px;
        font-weight: 750;
        line-height: 1.4;
        letter-spacing: -0.5px;
    }

    .inventory-page .inventory-heading h1 i {
        color: #c9a227;
        font-size: 27px;
    }

    .inventory-page .inventory-heading p {
        margin: 0;
        color: #817b70;
        font-size: 13px;
        line-height: 1.7;
    }

    .inventory-page .inventory-heading .inventory-link {
        flex-shrink: 0;
        padding: 12px 18px;
        border: 1px solid #d8c58d;
        border-radius: 10px;
        background: #fff;
        color: #997414;
        font-weight: 650;
        text-decoration: none;
        transition: all .2s ease;
    }

    .inventory-page .inventory-heading .inventory-link:hover {
        background: #f8f1df;
        border-color: #c9a227;
        transform: translateY(-1px);
    }

    @media (max-width: 600px) {
        .inventory-page .inventory-heading {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    /* ===== Gold Weight Overview refinement ===== */

    .inventory-page .inventory-section:has(.fa-scale-balanced) {
        position: relative;
        overflow: hidden;
        padding: 26px 28px;
        border: 1px solid #eadfbe;
        border-radius: 16px;
        background: linear-gradient(135deg, #ffffff 0%, #fffdf7 100%);
        box-shadow: 0 5px 18px rgba(111, 83, 16, 0.045);
    }

    .inventory-page .inventory-section:has(.fa-scale-balanced)::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #c9a227, #ead9a1);
    }

    .inventory-page .inventory-section:has(.fa-scale-balanced) h2 {
        color: #302b20;
        font-size: 20px;
        font-weight: 750;
        margin: 0 0 8px;
    }

    .inventory-page .inventory-section:has(.fa-scale-balanced) h2 i {
        color: #c9a227;
        margin-right: 6px;
    }

    .inventory-page .inventory-section:has(.fa-scale-balanced) p {
        color: #817b70;
        font-size: 12px;
        line-height: 1.7;
        margin: 8px 0;
    }

    .inventory-page .inventory-section:has(.fa-scale-balanced)>h2:not(:first-child) {
        display: inline-block;
        margin: 12px 0 6px;
        padding: 10px 16px;
        border: 1px solid #f0e4bf;
        border-radius: 12px;
        background: #faf4e4;
        color: #a77d0e;
        font-size: 28px;
        letter-spacing: -0.5px;
    }

    @media (max-width: 600px) {
        .inventory-page .inventory-section:has(.fa-scale-balanced) {
            padding: 20px;
        }

        .inventory-page .inventory-section:has(.fa-scale-balanced)>h2:not(:first-child) {
            font-size: 24px;
        }
    }

    /* ===== Low Stock Alert refinement ===== */

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) {
        border: 1px solid #f0dfd8;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 5px 18px rgba(100, 40, 25, 0.035);
        overflow: hidden;
    }

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) h2 {
        color: #302b27;
        font-size: 20px;
        font-weight: 750;
    }

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) h2 i {
        color: #c9a227;
        margin-right: 6px;
    }

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) table th {
        padding: 14px 12px;
        background: #faf8f3;
        color: #756e63;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f0ece4;
        font-size: 12px;
        vertical-align: middle;
    }

    .inventory-page .inventory-section:has(.fa-triangle-exclamation) table tbody tr:hover {
        background: #fdfbf6;
    }

    /* Low-stock status badge */
    .inventory-page .inventory-badge.low {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 11px;
        border-radius: 20px;
        background: #fff0ed;
        color: #b83f35;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .inventory-page .inventory-badge.out {
        background: #c9443e;
        color: #fff;
    }

    @media (max-width: 600px) {
        .inventory-page .inventory-section:has(.fa-triangle-exclamation) {
            padding: 16px;
        }

        .inventory-page .inventory-section:has(.fa-triangle-exclamation) table {
            min-width: 650px;
        }
    }

    /* ===== Premium Statistics Cards ===== */

    .inventory-page .inventory-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid #ece5d5;
        border-radius: 16px;
        background: linear-gradient(145deg, #ffffff 0%, #fffdf8 100%);
        box-shadow: 0 4px 14px rgba(78, 59, 16, 0.035);
        transition: transform .22s ease,
            box-shadow .22s ease,
            border-color .22s ease;
    }

    .inventory-page .inventory-stat::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, #c9a227, #f0dfaa);
        opacity: .8;
    }

    .inventory-page .inventory-stat:hover {
        transform: translateY(-4px);
        border-color: #dfcd98;
        box-shadow: 0 10px 24px rgba(78, 59, 16, 0.08);
    }

    .inventory-page .inventory-stat-icon {
        border: 1px solid #f0e5c5;
        border-radius: 13px;
        background: #f9f2df;
        color: #a78013;
    }

    .inventory-page .inventory-stat h2 {
        color: #29261f;
        font-size: 28px;
        font-weight: 750;
        letter-spacing: -.5px;
        font-variant-numeric: tabular-nums;
    }

    .inventory-page .inventory-stat-label {
        color: #716b60;
        font-weight: 650;
    }

    .inventory-page .inventory-stat small {
        color: #8a8377;
        line-height: 1.65;
    }

    @media (prefers-reduced-motion: reduce) {
        .inventory-page .inventory-stat {
            transition: none;
        }

        .inventory-page .inventory-stat:hover {
            transform: none;
        }
    }

    .inventory-page .inventory-action-icon {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
        border: 1px solid #efe2bd;
        border-radius: 13px;
        background: linear-gradient(145deg, #fffaf0, #f5ebce);
        color: #a98216;
        line-height: 1;
        text-align: center;
        transition: background .2s ease, color .2s ease,
            border-color .2s ease;
    }

    .inventory-page .inventory-action-icon i {
        display: block;
        margin: 0;
        padding: 0;
        line-height: 1;
        font-size: 17px;
    }

    .inventory-page .inventory-action:hover .inventory-action-icon {
        background: #c9a227;
        border-color: #c9a227;
        color: #fff;
    }

    @media (max-width: 900px) {
        .inventory-page .inventory-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
    }

    @media (max-width: 560px) {
        .inventory-page .inventory-actions {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .inventory-page .inventory-action {
            min-height: 82px;
            padding: 14px 16px;
        }
    }
</style>

<main class="inventory-page">

    <section class="inventory-heading">
        <div>
            <h1><i class="fa-solid fa-boxes-stacked inventory-gold"></i>
                Inventory Management
            </h1>
            <p>Monitor jewelry stock, inventory value and gold weight.</p>
        </div>

        <a class="inventory-link"
            href="<?= htmlspecialchars($basePath) ?>/products/index.php">
            <i class="fa-solid fa-gem"></i> View Products
        </a>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="inventory-alert">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <section class="inventory-stats">

        <article class="inventory-stat">
            <div class="inventory-stat-top">
                <span class="inventory-stat-label">Total Products</span>
                <span class="inventory-stat-icon">
                    <i class="fa-solid fa-gem"></i>
                </span>
            </div>
            <h2><?= number_format($totalProducts) ?></h2>
            <small>Products registered in the database</small>
        </article>

        <article class="inventory-stat">
            <div class="inventory-stat-top">
                <span class="inventory-stat-label">Available Stock</span>
                <span class="inventory-stat-icon">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <h2><?= number_format($totalUnits) ?></h2>
            <small>Total units across all products</small>
        </article>

        <article class="inventory-stat">
            <div class="inventory-stat-top">
                <span class="inventory-stat-label">Low Stock Alert</span>
                <span class="inventory-stat-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
            </div>
            <h2><?= number_format($lowStockCount) ?></h2>
            <small>Products at or below their minimum stock level</small>
        </article>

        <article class="inventory-stat">
            <div class="inventory-stat-top">
                <span class="inventory-stat-label">Inventory Value</span>
                <span class="inventory-stat-icon">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <h2>$<?= number_format($inventoryValue, 2) ?></h2>
            <small>Stock quantity × purchase price</small>
        </article>

    </section>

    <section class="inventory-section">
        <div class="inventory-section-header">
            <div>
                <h2>Quick Actions</h2>
                <p>Select an inventory task to continue.</p>
            </div>
        </div>

        <div class="inventory-actions">

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/stock-in.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-arrow-down"></i>
                </span>
                <span>
                    <strong>Stock In</strong>
                    <small>Receive products and increase stock.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/stock-out.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-arrow-up"></i>
                </span>
                <span>
                    <strong>Stock Out</strong>
                    <small>Record stock leaving inventory.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/adjustment.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-sliders"></i>
                </span>
                <span>
                    <strong>Inventory Adjustment</strong>
                    <small>Correct stock count discrepancies.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/history.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <span>
                    <strong>Inventory History</strong>
                    <small>Review inventory transactions.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/barcode.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-barcode"></i>
                </span>
                <span>
                    <strong>Barcode Tracking</strong>
                    <small>Find products using barcode or SKU.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/gold-weight.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-scale-balanced"></i>
                </span>
                <span>
                    <strong>Gold Weight Tracking</strong>
                    <small>Review product gold weights.</small>
                </span>
            </a>

            <a class="inventory-action"
                href="<?= htmlspecialchars($basePath) ?>/inventory/low-stock.php">
                <span class="inventory-action-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <span>
                    <strong>Low Stock Alert</strong>
                    <small>Monitor low stock and update minimum stock levels.</small>
                </span>
            </a>

        </div>
    </section>

    <section class="inventory-section">
        <div class="inventory-section-header">
            <div>
                <h2><i class="fa-solid fa-triangle-exclamation inventory-gold"></i>
                    Low Stock Alert
                </h2>
                <p>Products that need attention or replenishment.</p>
            </div>
            <span class="inventory-badge low">
                <?= number_format($lowStockCount) ?> products
            </span>
        </div>

        <div class="inventory-table-wrap">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>Purchase Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars(
                                        (string) $product['ProductID']
                                    ) ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            (string) $product['ProductName']
                                        ) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars(
                                        (string) ($product['SKU'] ?? '—')
                                    ) ?>
                                </td>
                                <td>
                                    <?= number_format(
                                        (int) $product['StockQuantity']
                                    ) ?>
                                </td>
                                <td>
                                    $<?= number_format(
                                            (float) $product['PurchasePrice'],
                                            2
                                        ) ?>
                                </td>
                                <td>
                                    <?php if ((int) $product['StockQuantity'] <= 0): ?>
                                        <span class="inventory-badge out">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="inventory-badge low">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="inventory-empty">
                                No low-stock products found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="inventory-section">
        <div class="inventory-section-header">
            <div>
                <h2><i class="fa-solid fa-scale-balanced inventory-gold"></i>
                    Gold Weight Overview
                </h2>
                <p>Gold weight is calculated from the available product data.</p>
            </div>
        </div>

        <?php if ($hasGoldWeight): ?>
            <h2>
                <?= number_format((float) $totalGoldWeight, 3) ?> g
            </h2>
            <p>Estimated total gold weight across current stock.</p>
        <?php else: ?>
            <p>
                Gold weight has not been connected yet. We will add this
                using the actual product weight field in the next step.
            </p>
        <?php endif; ?>

        <?php if ($hasBarcode): ?>
            <p><i class="fa-solid fa-barcode inventory-gold"></i>
                A barcode column was detected in Products.
            </p>
        <?php else: ?>
            <p>
                <i class="fa-solid fa-circle-info inventory-gold"></i>
                Barcode tracking needs to be connected to the product data.
            </p>
        <?php endif; ?>
    </section>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>