<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($con) || !$con instanceof PDO) {
    exit('Database connection is not available.');
}

$basePath = function_exists('app_base_path')
    ? rtrim(app_base_path(), '/')
    : '/Jewelry-app';

if (empty($_SESSION['inventory_csrf'])) {
    $_SESSION['inventory_csrf'] = bin2hex(random_bytes(32));
}

function lowStockEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = $_SESSION['low_stock_success'] ?? '';
unset($_SESSION['low_stock_success']);

$search = trim((string)($_GET['search'] ?? ''));
$status = (string)($_GET['status'] ?? 'low');

$allowedStatuses = ['all', 'low', 'out'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'low';
}

// Update minimum stock level.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (
        !is_string($csrf) ||
        !hash_equals($_SESSION['inventory_csrf'], $csrf)
    ) {
        $error = 'Invalid session token. Please refresh the page.';
    } else {
        $productID = trim((string)($_POST['ProductID'] ?? ''));
        $thresholdRaw = $_POST['LowStockThreshold'] ?? '';

        $threshold = filter_var(
            $thresholdRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 1000000]]
        );

        if ($productID === '') {
            $error = 'Please select a valid product.';
        } elseif ($threshold === false) {
            $error = 'Minimum stock must be a whole number from 0 to 1,000,000.';
        } else {
            try {
                $stmt = $con->prepare("
                    UPDATE Products
                    SET LowStockThreshold = :threshold
                    WHERE ProductID = :product_id
                ");

                $stmt->execute([
                    ':threshold' => $threshold,
                    ':product_id' => $productID
                ]);

                if ($stmt->rowCount() > 0) {
                    $_SESSION['low_stock_success'] =
                        'Minimum stock level updated successfully.';
                    header(
                        'Location: ' . $basePath .
                            '/inventory/low-stock.php?status=' .
                            rawurlencode($status) .
                            '&search=' . rawurlencode($search)
                    );
                    exit;
                }

                // rowCount can be zero if the value was unchanged.
                $check = $con->prepare("
                    SELECT ProductID
                    FROM Products
                    WHERE ProductID = :product_id
                ");
                $check->execute([':product_id' => $productID]);

                if ($check->fetchColumn()) {
                    $_SESSION['low_stock_success'] =
                        'Minimum stock level saved.';
                    header(
                        'Location: ' . $basePath .
                            '/inventory/low-stock.php?status=' .
                            rawurlencode($status) .
                            '&search=' . rawurlencode($search)
                    );
                    exit;
                }

                $error = 'Product not found.';
            } catch (Throwable $e) {
                error_log('Low Stock update error: ' . $e->getMessage());
                $error = 'Unable to update minimum stock level. Check the database column.';
            }
        }
    }
}

$products = [];
$totalProducts = 0;
$lowCount = 0;
$outCount = 0;

try {
    $stats = $con->query("
        SELECT
            COUNT(*) AS total_products,
            COALESCE(SUM(
                CASE
                    WHEN StockQuantity = 0 THEN 1
                    ELSE 0
                END
            ), 0) AS out_count,
            COALESCE(SUM(
                CASE
                    WHEN StockQuantity > 0
                     AND StockQuantity <= LowStockThreshold
                    THEN 1
                    ELSE 0
                END
            ), 0) AS low_count
        FROM Products
    ")->fetch(PDO::FETCH_ASSOC);

    $totalProducts = (int)$stats['total_products'];
    $lowCount = (int)$stats['low_count'];
    $outCount = (int)$stats['out_count'];

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(
            ProductID LIKE :product_id
            OR ProductName LIKE :product_name
            OR SKU LIKE :sku
        )";

        $keyword = '%' . $search . '%';
        $params[':product_id'] = $keyword;
        $params[':product_name'] = $keyword;
        $params[':sku'] = $keyword;
    }

    if ($status === 'low') {
        $where[] = 'StockQuantity > 0 AND StockQuantity <= LowStockThreshold';
    } elseif ($status === 'out') {
        $where[] = 'StockQuantity = 0';
    } else {
        $where[] = 'StockQuantity <= LowStockThreshold';
    }

    $whereSql = 'WHERE ' . implode(' AND ', $where);

    $stmt = $con->prepare("
        SELECT
            ProductID,
            ProductName,
            SKU,
            StockQuantity,
            LowStockThreshold,
            PurchasePrice
        FROM Products
        $whereSql
        ORDER BY
            CASE WHEN StockQuantity = 0 THEN 0 ELSE 1 END,
            StockQuantity ASC,
            ProductName ASC
    ");

    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Low Stock page error: ' . $e->getMessage());
    $error = 'Unable to load low-stock products. Check the database column and connection.';
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .low-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .low-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .low-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .low-heading p {
        margin: 0;
        color: #777;
    }

    .low-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .low-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .low-stat {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 20px;
    }

    .low-stat span {
        color: #777;
        font-size: 13px;
    }

    .low-stat strong {
        display: block;
        margin-top: 10px;
        font-size: 27px;
    }

    .low-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 22px;
    }

    .low-card h2 {
        margin: 0 0 7px;
        font-size: 19px;
    }

    .low-card>p {
        margin: 0 0 20px;
        color: #777;
        font-size: 13px;
    }

    .low-message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .low-message.success {
        background: #e8f6ed;
        color: #17663b;
    }

    .low-message.error {
        background: #fdeaea;
        color: #a32929;
    }

    .low-filters {
        display: flex;
        gap: 12px;
        align-items: end;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .low-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        flex: 1;
        min-width: 180px;
    }

    .low-field label {
        font-size: 12px;
        font-weight: 600;
    }

    .low-field input,
    .low-field select {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        background: #fff;
    }

    .low-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 11px 15px;
        border: 0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    .low-btn.primary {
        background: #c9a227;
        color: #fff;
    }

    .low-btn.secondary {
        background: #f3f3f3;
        color: #444;
    }

    .low-table-wrap {
        overflow-x: auto;
    }

    .low-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .low-table th,
    .low-table td {
        padding: 13px 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
        font-size: 13px;
        vertical-align: middle;
    }

    .low-table th {
        background: #faf9f6;
        color: #777;
        font-weight: 600;
    }

    .low-table tbody tr:last-child td {
        border-bottom: none;
    }

    .low-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .low-badge.out {
        background: #dc3545;
        color: #fff;
    }

    .low-badge.low {
        background: #fdeaea;
        color: #a32929;
    }

    .low-stock-number {
        font-weight: 700;
    }

    .low-threshold-form {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .low-threshold-form input {
        width: 75px;
        min-width: 0;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 7px;
    }

    .low-threshold-form button {
        padding: 8px 10px;
        border: 0;
        border-radius: 7px;
        background: #c9a227;
        color: white;
        cursor: pointer;
    }

    .low-empty {
        padding: 35px !important;
        text-align: center !important;
        color: #777;
    }

    @media (max-width: 700px) {
        .low-page {
            padding: 15px;
        }

        .low-stats {
            grid-template-columns: 1fr;
        }

        .low-card {
            padding: 15px;
        }
    }
</style>

<main class="low-page">
    <div class="low-heading">
        <div>
            <h1>
                <i class="fa-solid fa-triangle-exclamation"
                    style="color:#c9a227"></i>
                Low Stock Alert
            </h1>
            <p>Monitor stock shortages and configure product-level minimums.</p>
        </div>

        <a class="low-back"
            href="<?= lowStockEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="low-message success">
            <i class="fa-solid fa-circle-check"></i>
            <?= lowStockEscape($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="low-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= lowStockEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="low-stats">
        <div class="low-stat">
            <span>Total Products</span>
            <strong><?= number_format($totalProducts) ?></strong>
        </div>
        <div class="low-stat">
            <span>Low Stock Products</span>
            <strong><?= number_format($lowCount) ?></strong>
        </div>
        <div class="low-stat">
            <span>Out of Stock</span>
            <strong><?= number_format($outCount) ?></strong>
        </div>
    </section>

    <section class="low-card">
        <h2>Stock Alerts</h2>
        <p>
            A product is low when its stock is greater than zero but at or
            below its configured minimum. Zero stock is shown separately.
        </p>

        <form method="GET" class="low-filters">
            <div class="low-field">
                <label for="search">Search Product</label>
                <input type="search"
                    id="search"
                    name="search"
                    value="<?= lowStockEscape($search) ?>"
                    placeholder="Product ID, name or SKU">
            </div>

            <div class="low-field" style="max-width:220px">
                <label for="status">Stock Status</label>
                <select name="status" id="status">
                    <option value="low" <?= $status === 'low' ? 'selected' : '' ?>>
                        Low Stock
                    </option>
                    <option value="out" <?= $status === 'out' ? 'selected' : '' ?>>
                        Out of Stock
                    </option>
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>
                        All Alerts
                    </option>
                </select>
            </div>

            <button class="low-btn primary" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Filter
            </button>

            <a class="low-btn secondary"
                href="<?= lowStockEscape($basePath) ?>/inventory/low-stock.php">
                Reset
            </a>
        </form>

        <div class="low-table-wrap">
            <table class="low-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Minimum Stock</th>
                        <th>Status</th>
                        <th>Update Minimum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $stock = (int)$product['StockQuantity'];
                            $threshold = (int)$product['LowStockThreshold'];
                            ?>
                            <tr>
                                <td><?= lowStockEscape($product['ProductID']) ?></td>
                                <td>
                                    <strong>
                                        <?= lowStockEscape($product['ProductName']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?= lowStockEscape($product['SKU'] ?? '—') ?>
                                </td>
                                <td>
                                    <span class="low-stock-number">
                                        <?= number_format($stock) ?>
                                    </span>
                                </td>
                                <td><?= number_format($threshold) ?></td>
                                <td>
                                    <?php if ($stock === 0): ?>
                                        <span class="low-badge out">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="low-badge low">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="low-threshold-form">
                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?= lowStockEscape($_SESSION['inventory_csrf']) ?>">
                                        <input type="hidden"
                                            name="ProductID"
                                            value="<?= lowStockEscape($product['ProductID']) ?>">
                                        <input type="hidden"
                                            name="search"
                                            value="<?= lowStockEscape($search) ?>">
                                        <input type="hidden"
                                            name="status"
                                            value="<?= lowStockEscape($status) ?>">

                                        <input type="number"
                                            name="LowStockThreshold"
                                            min="0"
                                            max="1000000"
                                            step="1"
                                            value="<?= $threshold ?>"
                                            aria-label="Minimum stock for <?= lowStockEscape($product['ProductName']) ?>"
                                            required>

                                        <button type="submit" title="Save minimum stock">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="low-empty">
                                <i class="fa-solid fa-circle-check"
                                    style="font-size:25px;display:block;margin-bottom:10px;color:#198754"></i>
                                No products match this stock alert filter.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>