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

function adjustmentEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = $_SESSION['inventory_adjustment_success'] ?? '';
unset($_SESSION['inventory_adjustment_success']);

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
        $error = 'Invalid session token. Please refresh the page.';
    } else {
        $productID = trim((string)($_POST['ProductID'] ?? ''));
        $newStockRaw = $_POST['NewStock'] ?? '';
        $reason = trim((string)($_POST['Reason'] ?? ''));

        $newStock = filter_var(
            $newStockRaw,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );

        if ($productID === '') {
            $error = 'Please select a product.';
        } elseif ($newStock === false) {
            $error = 'Actual stock must be a whole number and cannot be negative.';
        } elseif ($reason === '') {
            $error = 'Please provide a reason for this adjustment.';
        } elseif (strlen($reason) > 255) {
            $error = 'The reason cannot exceed 255 characters.';
        } else {
            try {
                $con->beginTransaction();

                // Lock the product row while adjusting stock.
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
                    throw new RuntimeException(
                        'The selected product was not found.'
                    );
                }

                $previousStock = (int)$product['StockQuantity'];
                $newStock = (int)$newStock;
                $difference = $newStock - $previousStock;

                if ($difference === 0) {
                    throw new RuntimeException(
                        'The actual stock is the same as current stock. No adjustment is needed.'
                    );
                }

                $update = $con->prepare("
                    UPDATE Products
                    SET StockQuantity = :new_stock
                    WHERE ProductID = :id
                ");
                $update->execute([
                    ':new_stock' => $newStock,
                    ':id' => $productID
                ]);

                // Quantity records the absolute size of the adjustment.
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
                        'Adjustment',
                        :quantity,
                        :previous_stock,
                        :new_stock,
                        :reason,
                        NULL
                    )
                ");

                $history->execute([
                    ':product_id' => $productID,
                    ':quantity' => abs($difference),
                    ':previous_stock' => $previousStock,
                    ':new_stock' => $newStock,
                    ':reason' => $reason
                ]);

                $con->commit();

                $_SESSION['inventory_adjustment_success'] =
                    'Inventory adjusted successfully. ' .
                    $product['ProductName'] . ': ' .
                    $previousStock . ' → ' . $newStock . ' units.';

                header(
                    'Location: ' . $basePath . '/inventory/adjustment.php'
                );
                exit;
            } catch (Throwable $e) {
                if ($con->inTransaction()) {
                    $con->rollBack();
                }

                error_log('Inventory Adjustment error: ' . $e->getMessage());

                $error = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Unable to save the adjustment. Please check the database.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    .adjust-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .adjust-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .adjust-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .adjust-heading p {
        margin: 0;
        color: #777;
    }

    .adjust-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .adjust-card {
        max-width: 850px;
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 25px;
    }

    .adjust-card h2 {
        margin: 0 0 7px;
        font-size: 19px;
    }

    .adjust-intro {
        color: #777;
        font-size: 13px;
        margin: 0 0 23px;
    }

    .adjust-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 19px;
    }

    .adjust-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .adjust-field.full {
        grid-column: 1 / -1;
    }

    .adjust-field label {
        font-size: 13px;
        font-weight: 600;
    }

    .adjust-field input,
    .adjust-field select,
    .adjust-field textarea {
        width: 100%;
        padding: 12px 13px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font: inherit;
        font-size: 14px;
        background: #fff;
        box-sizing: border-box;
    }

    .adjust-field input:focus,
    .adjust-field select:focus,
    .adjust-field textarea:focus {
        outline: 2px solid #e8d7a3;
        border-color: #c9a227;
    }

    .adjust-current,
    .adjust-preview {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 13px;
        background: #faf9f6;
        font-size: 13px;
        color: #666;
    }

    .adjust-current strong,
    .adjust-preview strong {
        color: #2b2b2b;
        font-size: 17px;
    }

    .adjust-actions {
        display: flex;
        gap: 12px;
        margin-top: 23px;
        flex-wrap: wrap;
    }

    .adjust-btn {
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

    .adjust-primary {
        background: #c9a227;
        color: #fff;
    }

    .adjust-secondary {
        background: #f3f3f3;
        color: #444;
    }

    .adjust-message {
        padding: 13px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
        font-size: 14px;
    }

    .adjust-message.success {
        background: #e8f6ed;
        color: #17663b;
    }

    .adjust-message.error {
        background: #fdeaea;
        color: #a32929;
    }

    @media (max-width: 650px) {
        .adjust-page {
            padding: 15px;
        }

        .adjust-card {
            padding: 17px;
        }

        .adjust-grid {
            grid-template-columns: 1fr;
        }

        .adjust-field.full {
            grid-column: auto;
        }
    }
</style>

<main class="adjust-page">
    <div class="adjust-heading">
        <div>
            <h1>
                <i class="fa-solid fa-sliders" style="color:#c9a227"></i>
                Inventory Adjustment
            </h1>
            <p>Correct inventory to match the actual physical stock count.</p>
        </div>

        <a class="adjust-back"
            href="<?= adjustmentEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($success !== ''): ?>
        <div class="adjust-message success">
            <i class="fa-solid fa-circle-check"></i>
            <?= adjustmentEscape($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="adjust-message error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= adjustmentEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="adjust-card">
        <h2>Adjust Product Stock</h2>
        <p class="adjust-intro">
            Enter the actual physical quantity counted. The difference
            will be recorded in inventory history.
        </p>

        <form method="POST" action="">
            <input type="hidden"
                name="csrf_token"
                value="<?= adjustmentEscape($_SESSION['inventory_csrf']) ?>">

            <div class="adjust-grid">
                <div class="adjust-field full">
                    <label for="ProductID">Product *</label>
                    <select name="ProductID" id="ProductID" required>
                        <option value="">-- Select Product --</option>

                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= adjustmentEscape($product['ProductID']) ?>"
                                data-stock="<?= (int)$product['StockQuantity'] ?>"
                                <?= (($_POST['ProductID'] ?? '') === $product['ProductID'])
                                    ? 'selected' : '' ?>>
                                <?= adjustmentEscape($product['ProductName']) ?>
                                — <?= adjustmentEscape($product['ProductID']) ?>
                                <?= !empty($product['SKU'])
                                    ? ' (' . adjustmentEscape($product['SKU']) . ')'
                                    : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="adjust-current" id="currentStock">
                        Current system stock: <strong>—</strong>
                    </div>
                </div>

                <div class="adjust-field">
                    <label for="NewStock">Actual Physical Stock *</label>
                    <input type="number"
                        name="NewStock"
                        id="NewStock"
                        min="0"
                        step="1"
                        value="<?= adjustmentEscape($_POST['NewStock'] ?? '') ?>"
                        placeholder="Enter the actual count"
                        required>
                </div>

                <div class="adjust-field">
                    <label>Adjustment Preview</label>
                    <div class="adjust-preview" id="adjustmentPreview">
                        Difference: <strong>—</strong>
                    </div>
                </div>

                <div class="adjust-field full">
                    <label for="Reason">Reason for Adjustment *</label>
                    <textarea name="Reason"
                        id="Reason"
                        rows="3"
                        maxlength="255"
                        required
                        placeholder="e.g. Physical stock count, damaged item, correction"><?= adjustmentEscape($_POST['Reason'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="adjust-actions">
                <button type="submit" class="adjust-btn adjust-primary">
                    <i class="fa-solid fa-check"></i> Save Adjustment
                </button>

                <a href="<?= adjustmentEscape($basePath) ?>/inventory/index.php"
                    class="adjust-btn adjust-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</main>

<script>
    (function() {
        const productSelect = document.getElementById('ProductID');
        const newStockInput = document.getElementById('NewStock');
        const currentStockBox = document.getElementById('currentStock');
        const previewBox = document.getElementById('adjustmentPreview');

        let currentStock = null;

        function updatePreview() {
            const option = productSelect.options[productSelect.selectedIndex];

            if (!option || !option.value) {
                currentStock = null;
                currentStockBox.innerHTML =
                    'Current system stock: <strong>—</strong>';
                previewBox.innerHTML = 'Difference: <strong>—</strong>';
                return;
            }

            currentStock = Number(option.dataset.stock || 0);

            currentStockBox.innerHTML =
                'Current system stock: <strong>' +
                currentStock.toLocaleString() + ' units</strong>';

            if (newStockInput.value === '') {
                previewBox.innerHTML = 'Difference: <strong>—</strong>';
                return;
            }

            const actualStock = Number(newStockInput.value);

            if (!Number.isInteger(actualStock) || actualStock < 0) {
                previewBox.innerHTML =
                    'Please enter a valid non-negative whole number.';
                return;
            }

            const difference = actualStock - currentStock;
            let description;

            if (difference > 0) {
                description = 'Increase by ' + difference + ' units';
            } else if (difference < 0) {
                description = 'Decrease by ' + Math.abs(difference) + ' units';
            } else {
                description = 'No change required';
            }

            previewBox.innerHTML =
                'Difference: <strong>' + description + '</strong>';
        }

        productSelect.addEventListener('change', updatePreview);
        newStockInput.addEventListener('input', updatePreview);
        updatePreview();
    })();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>