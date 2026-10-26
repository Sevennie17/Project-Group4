<?php

require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}

/* ==============================
   GET PRODUCT DETAILS
============================== */

$sql = "
    SELECT
        p.id,
        p.ProductID,
        p.ProductName,
        p.SKU,
        p.PurchasePrice,
        p.SellingPrice,
        p.StockQuantity,
        p.Description,

        c.CategoryName,
        b.BrandName,
        s.PStatusName,

        d.Material,
        d.Purity,
        d.Weight,
        d.GemstoneType,
        d.GemstoneWeight,
        d.WarrantyPeriod,

        bc.Barcode,

        pi.ImagePath

    FROM Products p

    LEFT JOIN Product_Categories c
        ON p.CategoryID = c.CategoryID

    LEFT JOIN Product_Brand b
        ON p.BrandID = b.BrandID

    LEFT JOIN Product_Status s
        ON p.PStatusID = s.PStatusID

    LEFT JOIN Product_Details d
        ON p.ProductID = d.ProductID

    LEFT JOIN Product_Barcodes bc
        ON p.ProductID = bc.ProductID

    LEFT JOIN Product_Images pi
        ON p.ProductID = pi.ProductID
        AND pi.IsPrimary = 1

    WHERE p.id = :id

    LIMIT 1
";

$stmt = $con->prepare($sql);

$stmt->execute([
    ':id' => $id
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Product not found.');
}

$page_css = '/Jewelry_Shop/products/assets/css/products.css';

include __DIR__ . '/../includes/header.php';

?>

<div class="products-page">
    <!-- PAGE HEADER -->
    <section class="products-page-header">
        <div>
            <h1>Product Details</h1>
            <p>View detailed information about this jewelry product.</p>
        </div>
        <a href="/Jewelry_Shop/products/" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Products
        </a>
    </section>

    <!-- PRODUCT DETAIL -->
    <section class="product-detail-card">
        <!-- TOP SECTION -->
        <div class="product-detail-top">
            <div class="product-detail-image">
                <?php if (!empty($product['ImagePath'])): ?>

                    <img
                        src="<?= htmlspecialchars($product['ImagePath']) ?>"
                        alt="<?= htmlspecialchars($product['ProductName']) ?>"
                        class="product-detail-main-image">

                <?php else: ?>

                    <div class="detail-image-placeholder">
                        <i class="fa-regular fa-gem"></i>
                        <span>No image available</span>
                    </div>

                <?php endif; ?>
            </div>

            <div class="product-summary">
                <div class="product-detail-title">
                    <div>
                        <span class="detail-category">​ <?= htmlspecialchars($product['CategoryName'] ?? '-') ?> </span>
                        <h2> <?= htmlspecialchars($product['ProductName']) ?> </h2>
                        <p class="detail-sku"> SKU: <?= htmlspecialchars($product['SKU']) ?> </p>
                    </div>
                    <span class="status-badge active">
                        <?= htmlspecialchars($product['PStatusName'] ?? '-') ?>
                    </span>
                </div>

                <div class="product-price-detail"> $<?= number_format($product['SellingPrice'], 2) ?> </div>

                <div class="product-summary-info">
                    <div>
                        <span>Stock Quantity</span>
                        <strong> <?= htmlspecialchars($product['StockQuantity']) ?> pcs </strong>
                    </div>
                    <div>
                        <span>Weight</span>
                        <strong> <?= htmlspecialchars($product['Weight'] ?? '0') ?> g </strong>
                    </div>
                    <div>
                        <span>Purity</span>
                        <strong> <?= htmlspecialchars($product['Purity'] ?? '-') ?> </strong>
                    </div>
                </div>

                <div class="detail-actions">

                    <a
                        href="/Jewelry_Shop/products/edit.php?id=<?= $product['id'] ?>"
                        class="btn-edit-product">
                        <i class="fa-solid fa-pen"></i>
                        Edit Product
                    </a>

                    <a
                        href="/Jewelry_Shop/products/delete.php?id=<?= $product['id'] ?>"
                        class="btn-delete-product">
                        <i class="fa-regular fa-trash-can"></i>
                        Delete
                    </a>
                </div>
            </div>
        </div>

        <!-- PRODUCT INFORMATION -->
        <div class="product-information-section">
            <div class="detail-section-title">
                <i class="fa-solid fa-circle-info"></i>
                Product Information
            </div>
            <div class="detail-info-grid">
                <div class="detail-info-item">
                    <span>Product Name</span>
                    <strong>Gold Diamond Ring</strong>
                </div>
                <div class="detail-info-item">
                    <span>Product Code (SKU)</span>
                    <strong>RING-001</strong>
                </div>
                <div class="detail-info-item">
                    <span>Category</span>
                    <strong>Rings</strong>
                </div>
                <div class="detail-info-item">
                    <span>Brand</span>
                    <strong>ANAJAK</strong>
                </div>
                <div class="detail-info-item">
                    <span>Material</span>
                    <strong>Gold</strong>
                </div>
                <div class="detail-info-item">
                    <span>Purity</span>
                    <strong>18K</strong>
                </div>
                <div class="detail-info-item">
                    <span>Weight</span>
                    <strong>5.25 grams</strong>
                </div>
                <div class="detail-info-item">
                    <span>Gemstone Type</span>
                    <strong>Diamond</strong>
                </div>
                <div class="detail-info-item">
                    <span>Gemstone Weight</span>
                    <strong>0.50 Carat</strong>
                </div>
                <div class="detail-info-item">
                    <span>Purchase Price</span>
                    <strong>$550.00</strong>
                </div>
                <div class="detail-info-item">
                    <span>Selling Price</span>
                    <strong>$850.00</strong>
                </div>
                <div class="detail-info-item">
                    <span>Stock Quantity</span>
                    <strong>12 pcs</strong>
                </div>
                <div class="detail-info-item">
                    <span>Warranty Period</span>
                    <strong>12 Months</strong>
                </div>
                <div class="detail-info-item">
                    <span>Product Status</span>
                    <strong><span class="status-badge active">Active</span></strong>
                </div>
            </div>
        </div>

        <!-- BARCODE / QR -->
        <div class="product-information-section">
            <div class="detail-section-title">
                <i class="fa-solid fa-barcode"></i>
                Barcode / QR Code
            </div>
            <div class="code-display">
                <div class="barcode-box">
                    <span class="code-label">Barcode</span>
                    <div class="barcode-placeholder">|||| ||| |||| || |||</div>
                    <strong>
                        <?= htmlspecialchars($product['Barcode'] ?? 'No barcode') ?>
                    </strong>
                </div>
                <div class="qr-box">
                    <span class="code-label">QR Code</span>
                    <div class="qr-code-placeholder"><i class="fa-solid fa-qrcode"></i></div>
                </div>
            </div>
        </div>

        <!-- WARRANTY -->
        <div class="product-information-section">
            <div class="detail-section-title">
                <i class="fa-solid fa-shield-halved"></i>
                Warranty
            </div>
            <div class="warranty-info">
                <div class="warranty-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <strong>12 Months Warranty</strong>
                    <p>
                        This product includes a 12-month warranty
                        from the purchase date.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="/Jewelry_Shop/products/assets/js/products.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>