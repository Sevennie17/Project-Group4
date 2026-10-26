<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($con) || !$con instanceof PDO) {
    exit('Database connection is not available.');
}

if (empty($_SESSION['inventory_csrf'])) {
    $_SESSION['inventory_csrf'] = bin2hex(random_bytes(32));
}

function inventoryEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$basePath = function_exists('app_base_path')
    ? rtrim(app_base_path(), '/')
    : '/Jewelry-app';

$error = '';
$success = $_SESSION['inventory_success'] ?? '';
unset($_SESSION['inventory_success']);

$products = $con->query("
    SELECT ProductID, ProductName, SKU, StockQuantity
    FROM Products
    ORDER BY ProductName ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';

    if (
        !is_string($csrf) ||
        !hash_equals($_SESSION['inventory_csrf'], $csrf)
    ) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        $productID = trim((string)($_POST['ProductID'] ?? ''));
        $quantityRaw = $_POST['Quantity'] ?? '';
        $reason = trim((string)($_POST['Reason'] ?? ''));
        $reference = trim((string)($_POST['ReferenceNumber'] ?? ''));

        $quantity = filter_var(
            $quantityRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($productID === '') {
            $error = 'Please select a product.';
        } elseif ($quantity === false) {
            $error = 'Quantity must be a whole number greater than zero.';
        } elseif ($reason === '') {
            $error = 'Please enter a reason for this stock-in transaction.';
        } elseif (strlen($reason) > 255 || strlen($reference) > 100) {
            $error = 'The reason or reference number is too long.';
        } else {
            try {
                $con->beginTransaction();

                // Lock the product row to prevent concurrent stock updates.
                $find = $con->prepare("
                    SELECT ProductID, ProductName, StockQuantity
                    FROM Products
                    WHERE ProductID = :id
                    LIMIT 1
                    FOR UPDATE
                ");
                $find->execute([':id' => $productID]);
                $product = $find->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new RuntimeException('The selected product was not found.');
                }

                $previousStock = (int)$product['StockQuantity'];

                if ($quantity > PHP_INT_MAX - $previousStock) {
                    throw new RuntimeException('The resulting stock quantity is too large.');
                }

                $newStock = $previousStock + $quantity;

                $update = $con->prepare("
                    UPDATE Products
                    SET StockQuantity = :new_stock
                    WHERE ProductID = :id
                ");
                $update->execute([
                    ':new_stock' => $newStock,
                    ':id' => $productID
                ]);

                $history = $con->prepare("
                    INSERT INTO inventory_transactions
                    (
                        ProductID,
                        TransactionType,
                        Quantity,
                        PreviousStock,
                        NewStock,
                        Reason,
                        ReferenceNumber
                    )
                    VALUES
                    (
                        :product_id,
                        'Stock In',
                        :quantity,
                        :previous_stock,
                        :new_stock,
                        :reason,
                        :reference
                    )
                ");

                $history->execute([
                    ':product_id' => $productID,
                    ':quantity' => $quantity,
                    ':previous_stock' => $previousStock,
                    ':new_stock' => $newStock,
                    ':reason' => $reason,
                    ':reference' => $reference !== '' ? $reference : null
                ]);

                $con->commit();

                $_SESSION['inventory_success'] =
                    'Stock added successfully. ' .
                    $product['ProductName'] . ': ' .
                    $previousStock . ' → ' . $newStock . ' units.';

                header('Location: ' . $basePath . '/inventory/stock-in.php');
                exit;
            } catch (Throwable $e) {
                if ($con->inTransaction()) {
                    $con->rollBack();
                }

                error_log('Stock In error: ' . $e->getMessage());

                $error = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Unable to save stock-in transaction. Please check the database.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .stock-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .stock-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .stock-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .stock-heading p {
        margin: 0;
        color: #777;
    }

    .stock-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .stock-card {
        max-width: 850px;
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 25px;
    }

    .stock-card h2 {
        margin: 0 0 7px;
        font-size: 19px;
    }

    .stock-card .intro {
        color: #777;
        font-size: 13px;
        margin: 0 0 23px;
    }

    .stock-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 19px;
    }

    .stock-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .stock-field.full {
        grid-column: 1 / -1;
    }

    .stock-field label {
        font-size: 13px;
        font-weight: 600;
    }

    .stock-field input,
    .stock-field select,
    .stock-field textarea {
        width: 100%;
        padding: 12px 13px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font: inherit;
        font-size: 14px;
        background: #fff;
        box-sizing: border-box;
    }

    .stock-field input:focus,
    .stock-field select:focus,
    .stock-field textarea:focus {
        outline: 2px solid #e8d7a3;
        border-color: #c9a227;
    }

    .stock-current {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 12px;
        background: #faf9f6;
        color: #666;
        font-size: 13px;
    }

    .stock-current strong {
        color: #2b2b2b;
        font-size: 17px;
    }

    .stock-actions {
        display: flex;
        gap: 12px;
        margin-top: 23px;
        flex-wrap: wrap;
    }

    .stock-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        border: 0;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .stock-btn-primary {
        background: #c9a227;
        color: #fff;
    }

    .stock-btn-secondary {
        background: #f3f3f3;
        color: #444;
    }

    .stock-message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .stock-message.success {
        background: #e8f6ed;
        color: #17663b;
    }

    .stock-message.error {
        background: #fdeaea;
        color: #a32929;
    }

    @media (max-width: 650px) {
        .stock-page {
            padding: 15px;
        }

        .stock-card {
            padding: 17px;
        }

        .stock-grid {
            grid-template-columns: 1fr;
        }

        .stock-field.full {
            grid-column: auto;
        }
    }
</style>

<main class="stock-page">
    <div class="stock-heading">
        <div>
            <h1>
                <i class="fa-solid fa-boxes-stacked" style="color:#c9a227"></i>
                Stock In
            </h1>
            <p>Receive jewelry products and update inventory quantities.</p>
        </div>

        <a class="stock-back"
            href="<?= inventoryEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="stock-message success">
            <i class="fa-solid fa-circle-check"></i>
            <?= inventoryEscape($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="stock-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= inventoryEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="stock-card">
        <h2>Receive Products</h2>
        <p class="intro">
            Select an existing product and enter the quantity received.
        </p>

        <form method="POST" action="">
            <input type="hidden"
                name="csrf_token"
                value="<?= inventoryEscape($_SESSION['inventory_csrf']) ?>">

            <div class="stock-grid">
                <div class="stock-field full">
                    <label for="ProductID">Product *</label>
                    <select name="ProductID" id="ProductID" required>
                        <option value="">-- Select Product --</option>

                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= inventoryEscape($product['ProductID']) ?>"
                                data-stock="<?= (int)$product['StockQuantity'] ?>"
                                <?= (($_POST['ProductID'] ?? '') === $product['ProductID'])
                                    ? 'selected' : '' ?>>
                                <?= inventoryEscape($product['ProductName']) ?>
                                — <?= inventoryEscape($product['ProductID']) ?>
                                <?= $product['SKU']
                                    ? ' (' . inventoryEscape($product['SKU']) . ')'
                                    : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="stock-current" id="currentStock">
                        Current stock: <strong>—</strong>
                    </div>
                </div>

                <div class="stock-field">
                    <label for="Quantity">Quantity to Add *</label>
                    <input type="number"
                        name="Quantity"
                        id="Quantity"
                        min="1"
                        step="1"
                        value="<?= inventoryEscape($_POST['Quantity'] ?? '1') ?>"
                        required>
                </div>

                <div class="stock-field">
                    <label for="ReferenceNumber">Reference / Invoice No.</label>
                    <input type="text"
                        name="ReferenceNumber"
                        id="ReferenceNumber"
                        maxlength="100"
                        value="<?= inventoryEscape($_POST['ReferenceNumber'] ?? '') ?>"
                        placeholder="Optional reference number">
                </div>

                <div class="stock-field full">
                    <label for="Reason">Reason *</label>
                    <textarea name="Reason"
                        id="Reason"
                        rows="3"
                        maxlength="255"
                        required
                        placeholder="e.g. New stock received from supplier"><?= inventoryEscape($_POST['Reason'] ?? 'New stock received') ?></textarea>
                </div>
            </div>

            <div class="stock-actions">
                <button type="submit" class="stock-btn stock-btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Stock
                </button>

                <a href="<?= inventoryEscape($basePath) ?>/inventory/index.php"
                    class="stock-btn stock-btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</main>

<script>
    (function() {
        const productSelect = document.getElementById('ProductID');
        const currentStock = document.getElementById('currentStock');

        function showCurrentStock() {
            const option = productSelect.options[productSelect.selectedIndex];

            if (!option || !option.value) {
                currentStock.innerHTML = 'Current stock: <strong>—</strong>';
                return;
            }

            const stock = Number(option.dataset.stock || 0);
            currentStock.innerHTML =
                'Current stock: <strong>' + stock.toLocaleString() + ' units</strong>';
        }

        productSelect.addEventListener('change', showCurrentStock);
        showCurrentStock();
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>