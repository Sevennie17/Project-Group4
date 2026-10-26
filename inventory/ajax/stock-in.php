<?php

require_once __DIR__ . '/../../config/database.php';


/* =====================================================
   SAVE STOCK IN - POST
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    $productsData = $data['products'] ?? [];
    $stockDate = $data['stockDate'] ?? date('Y-m-d');
    $supplier = $data['supplier'] ?? '';
    $reference = $data['reference'] ?? '';


    /* CHECK PRODUCTS */

    if (empty($productsData)) {

        echo json_encode([
            'success' => false,
            'message' => 'No products found.'
        ]);

        exit;
    }


    /* PROCESS PRODUCTS */

    foreach ($productsData as $item) {

        $productID = $item['productCode'] ?? '';
        $quantity = (int)($item['quantity'] ?? 0);
        $unitCost = (float)($item['unitCost'] ?? 0);


        /* CHECK PRODUCT */

        if (empty($productID) || $quantity <= 0) {

            echo json_encode([
                'success' => false,
                'message' => 'Invalid product or quantity.'
            ]);

            exit;
        }


        $checkProduct = $pdo->prepare("
            SELECT ProductID
            FROM products
            WHERE ProductID = ?
            LIMIT 1
        ");

        $checkProduct->execute([
            $productID
        ]);


        if (!$checkProduct->fetch()) {

            echo json_encode([
                'success' => false,
                'message' => "Product {$productID} not found."
            ]);

            exit;
        }


        /* INSERT TRANSACTION */

        $insertTransaction = $pdo->prepare("
            INSERT INTO inventory_transactions
            (
                ProductID,
                transaction_type,
                quantity,
                unit_cost,
                supplier,
                reference,
                transaction_date
            )
            VALUES (
                ?,
                'Stock In',
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $insertTransaction->execute([
            $productID,
            $quantity,
            $unitCost,
            $supplier,
            $reference,
            $stockDate
        ]);


        /* UPDATE PRODUCT STOCK */

        $updateStock = $pdo->prepare("
            UPDATE products
            SET StockQuantity = StockQuantity + ?
            WHERE ProductID = ?
        ");

        $updateStock->execute([
            $quantity,
            $productID
        ]);
    }


    /* SUCCESS */

    echo json_encode([
        'success' => true,
        'message' => 'Stock In saved successfully.'
    ]);

    exit;
}


/* =====================================================
   LOAD PRODUCTS - GET / SHOW UI
===================================================== */

$products = $pdo->query("
    SELECT ProductID, ProductName, PurchasePrice
    FROM products
    ORDER BY ProductName ASC
")->fetchAll();


include __DIR__ . '/../../includes/header.php';

?>
<link rel="stylesheet" href="/Jewelry_Shop/inventory/assets/css/inventory.css">
<div class="stock-in-page">
    <!-- Breadcrumb -->
    <div class="stock-breadcrumb">
        <a href="/Jewelry_Shop/dashboards/">Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="/Jewelry_Shop/inventory/">Inventory</a>
        <i class="fa-solid fa-chevron-right"></i>
        <strong>Stock In</strong>
    </div>

    <!-- Page Header -->
    <div class="stock-page-header">

        <div class="stock-title-wrapper">

            <div class="stock-title-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>

            <div>
                <h1>Stock In</h1>
                <p>Add incoming products to your jewelry inventory.</p>
            </div>

        </div>

        <a
            href="/Jewelry_Shop/inventory/"
            class="back-inventory-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Inventory
        </a>

    </div>


    <!-- =========================================
         STOCK IN INFORMATION
    ========================================== -->

    <section class="stock-card">

        <div class="stock-card-header">

            <div class="section-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>

            <div>
                <h2>Stock In Information</h2>
                <p>Enter information about this stock receipt.</p>
            </div>

        </div>


        <div class="stock-info-grid">

            <!-- Stock In No -->
            <div class="form-group">

                <label>Stock In No.</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-tag"></i>

                    <input
                        type="text"
                        id="stockInNo"
                        class="form-control"
                        value="SI-0001"
                        readonly>

                </div>

            </div>


            <!-- Date -->
            <div class="form-group">

                <label>Date</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-calendar-days"></i>

                    <input
                        type="date"
                        id="stockDate"
                        class="form-control">

                </div>

            </div>


            <!-- Supplier -->
            <div class="form-group">

                <label>Supplier</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-truck-fast"></i>

                    <select
                        id="supplier"
                        class="form-control">

                        <option value="">
                            Select Supplier
                        </option>

                        <option value="Gold Supplier Co.">
                            Gold Supplier Co.
                        </option>

                        <option value="Diamond Jewelry Ltd.">
                            Diamond Jewelry Ltd.
                        </option>

                        <option value="Silver Supplier Co.">
                            Silver Supplier Co.
                        </option>

                    </select>

                </div>

            </div>


            <!-- Reference -->
            <div class="form-group">

                <label>Reference / Invoice</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-file-invoice"></i>

                    <input
                        type="text"
                        id="reference"
                        class="form-control"
                        placeholder="Enter invoice or reference number">

                </div>

            </div>

        </div>

    </section>



    <!-- =========================================
         ADD PRODUCTS
    ========================================== -->

    <section class="stock-card">

        <div class="stock-card-header">

            <div class="section-icon">
                <i class="fa-solid fa-cart-plus"></i>
            </div>

            <div>
                <h2>Add Products</h2>
                <p>Select products and enter stock quantities.</p>
            </div>

        </div>


        <!-- Add Product Form -->
        <div class="product-input-grid">

            <!-- Product -->
            <div class="form-group">

                <label>Product</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-gem"></i>

                    <select
                        id="productSelect"
                        class="form-control">

                        <option value="">
                            Select Product
                        </option>
                        <?php foreach ($products as $product): ?>
                            <option
                                value="<?= htmlspecialchars($product['ProductID']) ?>"
                                data-code="<?= htmlspecialchars($product['ProductID']) ?>"
                                data-cost="<?= htmlspecialchars($product['PurchasePrice']) ?>">
                                <?= htmlspecialchars($product['ProductName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Quantity -->
            <div class="form-group">

                <label>Quantity</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-hashtag"></i>

                    <input
                        type="number"
                        id="quantity"
                        class="form-control"
                        value="1"
                        min="1">

                </div>

            </div>


            <!-- Unit Cost -->
            <div class="form-group">

                <label>Unit Cost</label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-money-bill-wave"></i>

                    <input
                        type="number"
                        id="unitCost"
                        class="form-control"
                        value="0.00"
                        min="0"
                        step="0.01">

                </div>

            </div>


            <!-- Add Button -->
            <div class="add-product-wrapper">

                <button
                    type="button"
                    id="addProductBtn"
                    class="add-product-btn">

                    <i class="fa-solid fa-plus"></i>
                    Add

                </button>

            </div>

        </div>



        <!-- =========================================
             PRODUCT TABLE
        ========================================== -->

        <div class="table-wrapper">

            <table class="stock-product-table">

                <thead>

                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>

                </thead>


                <tbody id="productTableBody">


                </tbody>

            </table>

        </div>



        <!-- =========================================
             SUMMARY
        ========================================== -->

        <div class="stock-summary">

            <!-- Total Items -->
            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>

                <div class="summary-content">

                    <span>Total Items</span>

                    <strong id="totalItems">
                        4
                    </strong>

                    <small>
                        Different products
                    </small>

                </div>

            </div>


            <!-- Total Quantity -->
            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-cubes"></i>
                </div>

                <div class="summary-content">

                    <span>Total Quantity</span>

                    <strong id="totalQuantity">
                        35
                    </strong>

                    <small>
                        Units received
                    </small>

                </div>

            </div>


            <!-- Grand Total -->
            <div class="summary-card">

                <div class="summary-icon">
                    <i class="fa-solid fa-coins"></i>
                </div>

                <div class="summary-content">

                    <span>Grand Total</span>

                    <strong
                        id="grandTotal"
                        class="grand-total">
                        $1,740.00
                    </strong>

                    <small>
                        Total value of this stock in
                    </small>

                </div>

            </div>

        </div>

    </section>



    <!-- =========================================
         ACTION BUTTONS
    ========================================== -->

    <div class="stock-actions">

        <button
            type="button"
            id="cancelBtn"
            class="cancel-btn">

            <i class="fa-solid fa-xmark"></i>
            Cancel

        </button>


        <button
            type="button"
            id="saveStockBtn"
            class="save-stock-btn">

            <i class="fa-solid fa-floppy-disk"></i>
            Save Stock In

        </button>

    </div>

</div>
<script src="/Jewelry-app/inventory/assets/css/js/inventory.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>