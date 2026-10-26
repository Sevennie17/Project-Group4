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

if (empty($_SESSION['gold_weight_csrf'])) {
    $_SESSION['gold_weight_csrf'] = bin2hex(random_bytes(32));
}

function goldWeightEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = $_SESSION['gold_weight_success'] ?? '';
unset($_SESSION['gold_weight_success']);

$search = trim((string)($_GET['search'] ?? ''));
$status = (string)($_GET['status'] ?? 'all');

if (!in_array($status, ['all', 'entered', 'missing'], true)) {
    $status = 'all';
}

// Save a product's gold weight.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (
        !is_string($csrf) ||
        !hash_equals($_SESSION['gold_weight_csrf'], $csrf)
    ) {
        $error = 'Your session expired. Please refresh the page.';
    } else {
        $productID = trim((string)($_POST['ProductID'] ?? ''));
        $weightRaw = trim((string)($_POST['GoldWeight'] ?? ''));

        if ($productID === '') {
            $error = 'Please select a valid product.';
        } elseif (
            $weightRaw === '' ||
            !is_numeric($weightRaw) ||
            !is_finite((float)$weightRaw) ||
            (float)$weightRaw < 0 ||
            (float)$weightRaw > 9999999.999
        ) {
            $error = 'Enter a valid gold weight greater than or equal to zero.';
        } else {
            try {
                $check = $con->prepare("
                    SELECT ProductID, ProductName
                    FROM Products
                    WHERE ProductID = :id
                    LIMIT 1
                ");
                $check->execute([':id' => $productID]);
                $product = $check->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    $error = 'Product not found.';
                } else {
                    $weight = round((float)$weightRaw, 3);

                    $update = $con->prepare("
                        UPDATE Products
                        SET GoldWeight = :weight
                        WHERE ProductID = :id
                    ");

                    $update->execute([
                        ':weight' => number_format($weight, 3, '.', ''),
                        ':id' => $productID
                    ]);

                    $_SESSION['gold_weight_success'] =
                        'Gold weight saved successfully for ' .
                        $product['ProductName'] . '.';

                    header(
                        'Location: ' . $basePath .
                            '/inventory/gold-weight.php?status=' .
                            rawurlencode($status) .
                            '&search=' . rawurlencode($search)
                    );
                    exit;
                }
            } catch (PDOException $e) {
                error_log('Gold Weight database error: ' . $e->getMessage());
                $error = 'Unable to save gold weight. Confirm that Products.GoldWeight exists.';
            } catch (Throwable $e) {
                error_log('Gold Weight error: ' . $e->getMessage());
                $error = 'An unexpected error occurred while saving the weight.';
            }
        }
    }
}

// Load products and calculate stock weight totals.
$products = [];
$totalProducts = 0;
$enteredCount = 0;
$missingCount = 0;
$totalGoldWeight = 0.0;
$stockGoldWeight = 0.0;

try {
    $stats = $con->query("
        SELECT
            COUNT(*) AS total_products,
            COALESCE(SUM(
                CASE
                    WHEN GoldWeight IS NOT NULL THEN 1
                    ELSE 0
                END
            ), 0) AS entered_count,
            COALESCE(SUM(
                CASE
                    WHEN GoldWeight IS NULL THEN 1
                    ELSE 0
                END
            ), 0) AS missing_count,
            COALESCE(SUM(
                CASE
                    WHEN GoldWeight IS NOT NULL
                    THEN GoldWeight
                    ELSE 0
                END
            ), 0) AS total_gold_weight,
            COALESCE(SUM(
                CASE
                    WHEN GoldWeight IS NOT NULL
                    THEN GoldWeight * GREATEST(COALESCE(StockQuantity, 0), 0)
                    ELSE 0
                END
            ), 0) AS stock_gold_weight
        FROM Products
    ")->fetch(PDO::FETCH_ASSOC);

    $totalProducts = (int)$stats['total_products'];
    $enteredCount = (int)$stats['entered_count'];
    $missingCount = (int)$stats['missing_count'];
    $totalGoldWeight = (float)$stats['total_gold_weight'];
    $stockGoldWeight = (float)$stats['stock_gold_weight'];

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(
            ProductID LIKE :product_id
            OR ProductName LIKE :product_name
            OR SKU LIKE :sku
            OR Barcode LIKE :barcode
        )";

        $keyword = '%' . $search . '%';

        $params[':product_id'] = $keyword;
        $params[':product_name'] = $keyword;
        $params[':sku'] = $keyword;
        $params[':barcode'] = $keyword;
    }

    if ($status === 'entered') {
        $where[] = 'GoldWeight IS NOT NULL';
    } elseif ($status === 'missing') {
        $where[] = 'GoldWeight IS NULL';
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $con->prepare("
        SELECT
            ProductID,
            ProductName,
            SKU,
            Barcode,
            StockQuantity,
            GoldWeight
        FROM Products
        $whereSql
        ORDER BY
            CASE WHEN GoldWeight IS NULL THEN 0 ELSE 1 END,
            ProductName ASC
    ");

    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Gold Weight load error: ' . $e->getMessage());
    $error = 'Unable to load gold weight data. Check the GoldWeight database column.';
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .gold-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .gold-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .gold-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .gold-heading p {
        margin: 0;
        color: #777;
    }

    .gold-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .gold-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .gold-stat,
    .gold-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 13px;
    }

    .gold-stat {
        padding: 19px;
    }

    .gold-stat span {
        display: block;
        color: #777;
        font-size: 13px;
    }

    .gold-stat strong {
        display: block;
        margin-top: 10px;
        font-size: 25px;
    }

    .gold-stat small {
        color: #777;
        font-size: 11px;
    }

    .gold-card {
        padding: 22px;
    }

    .gold-card h2 {
        margin: 0 0 7px;
        font-size: 19px;
    }

    .gold-description {
        margin: 0 0 20px;
        color: #777;
        font-size: 13px;
    }

    .gold-message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .gold-message.success {
        background: #e8f6ed;
        color: #17663b;
    }

    .gold-message.error {
        background: #fdeaea;
        color: #a32929;
    }

    .gold-filters {
        display: flex;
        align-items: end;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .gold-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        flex: 1;
        min-width: 190px;
    }

    .gold-field label {
        font-size: 12px;
        font-weight: 600;
    }

    .gold-field input,
    .gold-field select {
        box-sizing: border-box;
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        font: inherit;
        font-size: 13px;
    }

    .gold-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 11px 14px;
        border: 0;
        border-radius: 8px;
        background: #c9a227;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .gold-btn.secondary {
        background: #f3f3f3;
        color: #444;
    }

    .gold-table-wrap {
        overflow-x: auto;
    }

    .gold-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }

    .gold-table th,
    .gold-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
        vertical-align: middle;
        font-size: 13px;
    }

    .gold-table th {
        background: #faf9f6;
        color: #777;
        font-weight: 600;
    }

    .gold-table tbody tr:last-child td {
        border-bottom: none;
    }

    .gold-subtext {
        color: #777;
        font-size: 11px;
    }

    .gold-weight-form {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .gold-weight-form input {
        box-sizing: border-box;
        width: 110px;
        min-width: 0;
        padding: 9px;
        border: 1px solid #ddd;
        border-radius: 7px;
        font: inherit;
    }

    .gold-weight-form button {
        padding: 9px 11px;
    }

    .gold-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .gold-badge.entered {
        background: #e5f5eb;
        color: #17663b;
    }

    .gold-badge.missing {
        background: #fff4db;
        color: #8b6500;
    }

    .gold-empty {
        padding: 35px !important;
        text-align: center !important;
        color: #777;
    }

    @media (max-width: 1100px) {
        .gold-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .gold-page {
            padding: 15px;
        }

        .gold-stats {
            grid-template-columns: 1fr;
        }

        .gold-card {
            padding: 15px;
        }
    }
</style>

<main class="gold-page">
    <div class="gold-heading">
        <div>
            <h1>
                <i class="fa-solid fa-scale-balanced"
                    style="color:#c9a227"></i>
                Gold Weight Tracking
            </h1>
            <p>Record gold weight per product and calculate total weight in stock.</p>
        </div>

        <a class="gold-back"
            href="<?= goldWeightEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="gold-message success">
            <i class="fa-solid fa-circle-check"></i>
            <?= goldWeightEscape($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="gold-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= goldWeightEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="gold-stats">
        <div class="gold-stat">
            <span>Total Products</span>
            <strong><?= number_format($totalProducts) ?></strong>
            <small>Registered in inventory</small>
        </div>

        <div class="gold-stat">
            <span>Weight Entered</span>
            <strong><?= number_format($enteredCount) ?></strong>
            <small>Products with a recorded weight</small>
        </div>

        <div class="gold-stat">
            <span>Weight Missing</span>
            <strong><?= number_format($missingCount) ?></strong>
            <small>Products still needing a weight</small>
        </div>

        <div class="gold-stat">
            <span>Total Gold in Stock</span>
            <strong><?= number_format($stockGoldWeight, 3) ?> g</strong>
            <small>Weight per product × stock quantity</small>
        </div>
    </section>

    <section class="gold-card">
        <h2>Product Gold Weight</h2>
        <p class="gold-description">
            Enter the gold weight of one item in grams (g).
            The total stock weight is calculated automatically.
        </p>

        <form method="GET" class="gold-filters">
            <div class="gold-field">
                <label for="search">Search Product</label>
                <input type="search"
                    name="search"
                    id="search"
                    value="<?= goldWeightEscape($search) ?>"
                    placeholder="Product ID, name, SKU or barcode">
            </div>

            <div class="gold-field" style="max-width:220px">
                <label for="status">Weight Status</label>
                <select name="status" id="status">
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>
                        All Products
                    </option>
                    <option value="entered" <?= $status === 'entered' ? 'selected' : '' ?>>
                        Weight Entered
                    </option>
                    <option value="missing" <?= $status === 'missing' ? 'selected' : '' ?>>
                        Weight Missing
                    </option>
                </select>
            </div>

            <button class="gold-btn" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Filter
            </button>

            <a class="gold-btn secondary"
                href="<?= goldWeightEscape($basePath) ?>/inventory/gold-weight.php">
                Reset
            </a>
        </form>

        <div class="gold-table-wrap">
            <table class="gold-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Stock Quantity</th>
                        <th>Weight / Item (g)</th>
                        <th>Total Stock Weight (g)</th>
                        <th>Status</th>
                        <th>Update Weight</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products): ?>
                        <?php foreach ($products as $product): ?>
                            <?php
                            $stock = max(0, (int)$product['StockQuantity']);
                            $weight = $product['GoldWeight'] === null
                                ? null
                                : (float)$product['GoldWeight'];
                            $lineWeight = $weight === null
                                ? null
                                : $weight * $stock;
                            ?>
                            <tr>
                                <td><?= goldWeightEscape($product['ProductID']) ?></td>

                                <td>
                                    <strong><?= goldWeightEscape($product['ProductName']) ?></strong>
                                </td>

                                <td><?= goldWeightEscape($product['SKU']) ?></td>

                                <td><?= number_format($stock) ?></td>

                                <td>
                                    <?= $weight === null
                                        ? '—'
                                        : number_format($weight, 3) . ' g' ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= $lineWeight === null
                                            ? '—'
                                            : number_format($lineWeight, 3) . ' g' ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php if ($weight === null): ?>
                                        <span class="gold-badge missing">Missing</span>
                                    <?php else: ?>
                                        <span class="gold-badge entered">Recorded</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <form method="POST" class="gold-weight-form">
                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?= goldWeightEscape($_SESSION['gold_weight_csrf']) ?>">

                                        <input type="hidden"
                                            name="ProductID"
                                            value="<?= goldWeightEscape($product['ProductID']) ?>">

                                        <input type="hidden"
                                            name="search"
                                            value="<?= goldWeightEscape($search) ?>">

                                        <input type="hidden"
                                            name="status"
                                            value="<?= goldWeightEscape($status) ?>">

                                        <input type="number"
                                            name="GoldWeight"
                                            min="0"
                                            max="9999999.999"
                                            step="0.001"
                                            value="<?= $weight === null
                                                        ? ''
                                                        : number_format($weight, 3, '.', '') ?>"
                                            placeholder="e.g. 3.500"
                                            aria-label="Gold weight for <?= goldWeightEscape($product['ProductName']) ?>"
                                            required>

                                        <button class="gold-btn"
                                            type="submit"
                                            title="Save gold weight">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="gold-empty">
                                No matching products found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>