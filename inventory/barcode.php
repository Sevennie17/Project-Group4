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

function barcodeEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = $_SESSION['barcode_success'] ?? '';
unset($_SESSION['barcode_success']);

$search = trim((string)($_GET['search'] ?? ''));
$products = [];

// Assign a barcode to a product.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (
        !is_string($csrf) ||
        !hash_equals($_SESSION['inventory_csrf'], $csrf)
    ) {
        $error = 'Your session expired. Please refresh the page.';
    } else {
        $productID = trim((string)($_POST['ProductID'] ?? ''));
        $barcode = trim((string)($_POST['Barcode'] ?? ''));

        if ($productID === '') {
            $error = 'Please select a product.';
        } elseif ($barcode === '') {
            $error = 'Please enter a barcode.';
        } elseif (strlen($barcode) > 100) {
            $error = 'Barcode must not exceed 100 characters.';
        } elseif (preg_match('/[\x00-\x1F\x7F]/', $barcode)) {
            $error = 'Barcode contains invalid characters.';
        } else {
            try {
                // Check that the product exists.
                $checkProduct = $con->prepare("
                    SELECT ProductID, ProductName
                    FROM Products
                    WHERE ProductID = :id
                    LIMIT 1
                ");
                $checkProduct->execute([':id' => $productID]);
                $product = $checkProduct->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new RuntimeException('Product not found.');
                }

                // Check whether another product already uses this barcode.
                $checkBarcode = $con->prepare("
                    SELECT ProductID
                    FROM Products
                    WHERE Barcode = :barcode
                      AND ProductID <> :id
                    LIMIT 1
                ");
                $checkBarcode->execute([
                    ':barcode' => $barcode,
                    ':id' => $productID
                ]);

                if ($checkBarcode->fetchColumn()) {
                    throw new RuntimeException(
                        'This barcode is already assigned to another product.'
                    );
                }

                $update = $con->prepare("
                    UPDATE Products
                    SET Barcode = :barcode
                    WHERE ProductID = :id
                ");
                $update->execute([
                    ':barcode' => $barcode,
                    ':id' => $productID
                ]);

                $_SESSION['barcode_success'] =
                    'Barcode saved successfully for ' .
                    $product['ProductName'] . '.';

                header(
                    'Location: ' . $basePath .
                        '/inventory/barcode.php?search=' .
                        rawurlencode($search)
                );
                exit;
            } catch (PDOException $e) {
                error_log('Barcode database error: ' . $e->getMessage());

                if ($e->getCode() === '23000') {
                    $error = 'This barcode is already in use. Please use a different one.';
                } else {
                    $error = 'Unable to save the barcode. Please check your database.';
                }
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            } catch (Throwable $e) {
                error_log('Barcode error: ' . $e->getMessage());
                $error = 'An unexpected error occurred.';
            }
        }
    }
}

// Search by barcode, SKU, product ID or product name.
try {
    if ($search !== '') {
        $stmt = $con->prepare("
            SELECT
                ProductID,
                ProductName,
                SKU,
                Barcode,
                StockQuantity,
                LowStockThreshold
            FROM Products
            WHERE ProductID LIKE :id
               OR ProductName LIKE :name
               OR SKU LIKE :sku
               OR Barcode LIKE :barcode
            ORDER BY ProductName ASC
        ");

        $keyword = '%' . $search . '%';

        $stmt->execute([
            ':id' => $keyword,
            ':name' => $keyword,
            ':sku' => $keyword,
            ':barcode' => $keyword
        ]);
    } else {
        $stmt = $con->query("
            SELECT
                ProductID,
                ProductName,
                SKU,
                Barcode,
                StockQuantity,
                LowStockThreshold
            FROM Products
            ORDER BY ProductName ASC
        ");
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Barcode product search error: ' . $e->getMessage());
    $error = 'Unable to load products. Confirm that the Barcode column exists.';
}

$totalProducts = count($products);
$assignedBarcodes = 0;

foreach ($products as $product) {
    if (!empty($product['Barcode'])) {
        $assignedBarcodes++;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .barcode-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .barcode-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .barcode-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .barcode-heading p {
        margin: 0;
        color: #777;
    }

    .barcode-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .barcode-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .barcode-stat,
    .barcode-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 13px;
    }

    .barcode-stat {
        padding: 20px;
    }

    .barcode-stat span {
        color: #777;
        font-size: 13px;
    }

    .barcode-stat strong {
        display: block;
        margin-top: 10px;
        font-size: 26px;
    }

    .barcode-card {
        padding: 22px;
    }

    .barcode-card h2 {
        margin: 0 0 7px;
        font-size: 19px;
    }

    .barcode-description {
        color: #777;
        font-size: 13px;
        margin: 0 0 20px;
    }

    .barcode-message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .barcode-message.success {
        background: #e8f6ed;
        color: #17663b;
    }

    .barcode-message.error {
        background: #fdeaea;
        color: #a32929;
    }

    .barcode-search {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .barcode-search input {
        flex: 1;
        min-width: 0;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font: inherit;
    }

    .barcode-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        padding: 10px 14px;
        border: 0;
        border-radius: 8px;
        background: #c9a227;
        color: #fff;
        font-weight: 600;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .barcode-btn.secondary {
        background: #f3f3f3;
        color: #444;
    }

    .barcode-table-wrap {
        overflow-x: auto;
    }

    .barcode-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .barcode-table th,
    .barcode-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
        font-size: 13px;
        vertical-align: middle;
    }

    .barcode-table th {
        background: #faf9f6;
        color: #777;
        font-weight: 600;
    }

    .barcode-table tr:last-child td {
        border-bottom: none;
    }

    .barcode-form {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .barcode-form input {
        width: 160px;
        max-width: 100%;
        min-width: 0;
        padding: 9px;
        border: 1px solid #ddd;
        border-radius: 7px;
        font: inherit;
    }

    .barcode-tag {
        display: inline-block;
        padding: 6px 9px;
        border-radius: 6px;
        background: #faf9f6;
        border: 1px solid #eee;
        font-family: monospace;
        overflow-wrap: anywhere;
    }

    .barcode-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .barcode-status.assigned {
        background: #e5f5eb;
        color: #17663b;
    }

    .barcode-status.missing {
        background: #fff4db;
        color: #8b6500;
    }

    .barcode-empty {
        padding: 35px !important;
        text-align: center !important;
        color: #777;
    }

    @media (max-width: 650px) {
        .barcode-page {
            padding: 15px;
        }

        .barcode-stats {
            grid-template-columns: 1fr;
        }

        .barcode-card {
            padding: 15px;
        }

        .barcode-search {
            flex-wrap: wrap;
        }

        .barcode-search input {
            flex-basis: 100%;
        }

        .barcode-form input {
            width: 120px;
        }
    }
</style>

<main class="barcode-page">
    <div class="barcode-heading">
        <div>
            <h1>
                <i class="fa-solid fa-barcode"
                    style="color:#c9a227"></i>
                Barcode Tracking
            </h1>
            <p>Search products and assign unique barcodes for inventory tracking.</p>
        </div>

        <a class="barcode-back"
            href="<?= barcodeEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="barcode-message success">
            <i class="fa-solid fa-circle-check"></i>
            <?= barcodeEscape($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="barcode-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= barcodeEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="barcode-stats">
        <div class="barcode-stat">
            <span>Products Found</span>
            <strong><?= number_format($totalProducts) ?></strong>
        </div>
        <div class="barcode-stat">
            <span>Products with Barcode</span>
            <strong><?= number_format($assignedBarcodes) ?></strong>
        </div>
    </section>

    <section class="barcode-card">
        <h2>Product Barcode List</h2>
        <p class="barcode-description">
            Search using barcode, SKU, product ID or product name.
            You can type a barcode manually or use a scanner that inputs text.
        </p>

        <form method="GET" class="barcode-search">
            <input type="search"
                name="search"
                value="<?= barcodeEscape($search) ?>"
                placeholder="Scan or enter Barcode, SKU, Product ID or name"
                aria-label="Search products">

            <button class="barcode-btn" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>

            <a class="barcode-btn secondary"
                href="<?= barcodeEscape($basePath) ?>/inventory/barcode.php">
                Reset
            </a>
        </form>

        <div class="barcode-table-wrap">
            <table class="barcode-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Barcode</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Assign / Update Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= barcodeEscape($product['ProductID']) ?></td>
                                <td><strong><?= barcodeEscape($product['ProductName']) ?></strong></td>
                                <td><?= barcodeEscape($product['SKU']) ?></td>
                                <td>
                                    <?php if (!empty($product['Barcode'])): ?>
                                        <span class="barcode-tag">
                                            <?= barcodeEscape($product['Barcode']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#999">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format((int)$product['StockQuantity']) ?></td>
                                <td>
                                    <?php if (!empty($product['Barcode'])): ?>
                                        <span class="barcode-status assigned">Assigned</span>
                                    <?php else: ?>
                                        <span class="barcode-status missing">Missing</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="barcode-form">
                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?= barcodeEscape($_SESSION['inventory_csrf']) ?>">
                                        <input type="hidden"
                                            name="ProductID"
                                            value="<?= barcodeEscape($product['ProductID']) ?>">
                                        <input type="hidden"
                                            name="search"
                                            value="<?= barcodeEscape($search) ?>">

                                        <input type="text"
                                            name="Barcode"
                                            maxlength="100"
                                            value="<?= barcodeEscape($product['Barcode'] ?? '') ?>"
                                            placeholder="Enter barcode"
                                            aria-label="Barcode for <?= barcodeEscape($product['ProductName']) ?>"
                                            required>

                                        <button class="barcode-btn" type="submit">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="barcode-empty">
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