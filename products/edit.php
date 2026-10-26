<?php

require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}

$id = (int) $id;


/* ========================================
   UPDATE PRODUCT
======================================== */

$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        $product_name = trim($_POST['product_name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');

        $category_id = trim($_POST['category_id'] ?? '');
        $brand_id = trim($_POST['brand_id'] ?? '');
        $status_id = trim($_POST['status_id'] ?? '');

        $barcode = trim($_POST['barcode'] ?? '');

        $material = trim($_POST['material'] ?? '');
        $purity = trim($_POST['purity'] ?? '');

        $weight = ($_POST['weight'] ?? '') !== ''
            ? $_POST['weight']
            : null;

        $gemstone_type = trim(
            $_POST['gemstone_type'] ?? ''
        );

        $gemstone_weight = ($_POST['gemstone_weight'] ?? '') !== ''
            ? $_POST['gemstone_weight']
            : null;

        $purchase_price = $_POST['purchase_price'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;

        $warranty_period = ($_POST['warranty_period'] ?? '') !== ''
            ? $_POST['warranty_period']
            : null;


        /* ========================================
           VALIDATION
        ======================================== */

        if (
            $product_name === '' ||
            $sku === '' ||
            $category_id === '' ||
            $purchase_price === '' ||
            $selling_price === '' ||
            $stock_quantity === ''
        ) {
            throw new Exception(
                'Please fill in all required fields.'
            );
        }


        /* ========================================
           TRANSACTION
        ======================================== */

        $con->beginTransaction();

        /* ========================================
        CHECK DUPLICATE SKU
        ======================================== */

        $skuCheck = $con->prepare("
        SELECT COUNT(*)
        FROM Products
        WHERE SKU = :sku
        AND id != :id
        ");

        $skuCheck->execute([
            ':sku' => $sku,
            ':id' => $id
        ]);

        if ((int)$skuCheck->fetchColumn() > 0) {
            throw new Exception(
                'SKU already exists. Please use a different SKU.'
            );
        }


        /* ========================================
           UPDATE PRODUCTS
        ======================================== */

        $sql = "
            UPDATE Products
            SET
                ProductName = :product_name,
                SKU = :sku,
                PurchasePrice = :purchase_price,
                SellingPrice = :selling_price,
                StockQuantity = :stock_quantity,
                CategoryID = :category_id,
                BrandID = :brand_id,
                PStatusID = :status_id
            WHERE id = :id
        ";

        $stmt = $con->prepare($sql);

        $stmt->execute([
            ':product_name' => $product_name,
            ':sku' => $sku,
            ':purchase_price' => $purchase_price,
            ':selling_price' => $selling_price,
            ':stock_quantity' => $stock_quantity,
            ':category_id' => $category_id,
            ':brand_id' => $brand_id !== ''
                ? $brand_id
                : null,
            ':status_id' => $status_id !== ''
                ? $status_id
                : null,
            ':id' => $id
        ]);


        /* ========================================
           GET PRODUCT ID
        ======================================== */

        $stmt = $con->prepare("
            SELECT ProductID
            FROM Products
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $productID = $stmt->fetchColumn();

        if (!$productID) {
            throw new Exception(
                'Product ID not found.'
            );
        }


        /* ========================================
           UPDATE PRODUCT DETAILS
        ======================================== */

        $sql = "
            INSERT INTO Product_Details
            (
                ProductID,
                Material,
                Purity,
                Weight,
                GemstoneType,
                GemstoneWeight,
                WarrantyPeriod
            )
            VALUES
            (
                :product_id,
                :material,
                :purity,
                :weight,
                :gemstone_type,
                :gemstone_weight,
                :warranty_period
            )
            ON DUPLICATE KEY UPDATE
                Material = VALUES(Material),
                Purity = VALUES(Purity),
                Weight = VALUES(Weight),
                GemstoneType = VALUES(GemstoneType),
                GemstoneWeight = VALUES(GemstoneWeight),
                WarrantyPeriod = VALUES(WarrantyPeriod)
        ";

        $stmt = $con->prepare($sql);

        $stmt->execute([
            ':product_id' => $productID,
            ':material' => $material !== ''
                ? $material
                : null,
            ':purity' => $purity !== ''
                ? $purity
                : null,
            ':weight' => $weight,
            ':gemstone_type' => $gemstone_type !== ''
                ? $gemstone_type
                : null,
            ':gemstone_weight' => $gemstone_weight,
            ':warranty_period' => $warranty_period
        ]);


        /* ========================================
           UPDATE BARCODE
        ======================================== */

        if ($barcode !== '') {

            $sql = "
                INSERT INTO Product_Barcodes
                (
                    ProductID,
                    Barcode
                )
                VALUES
                (
                    :product_id,
                    :barcode
                )
                ON DUPLICATE KEY UPDATE
                    Barcode = VALUES(Barcode)
            ";

            $stmt = $con->prepare($sql);

            $stmt->execute([
                ':product_id' => $productID,
                ':barcode' => $barcode
            ]);
        }


        /* ========================================
           IMAGE UPLOAD
        ======================================== */

        if (
            isset($_FILES['product_image']) &&
            $_FILES['product_image']['error'] === UPLOAD_ERR_OK
        ) {

            $allowedExtensions = [
                'jpg',
                'jpeg',
                'png',
                'webp'
            ];

            $extension = strtolower(
                pathinfo(
                    $_FILES['product_image']['name'],
                    PATHINFO_EXTENSION
                )
            );

            if (!in_array($extension, $allowedExtensions, true)) {
                throw new Exception(
                    'Invalid image format.'
                );
            }

            $uploadDirectory =
                __DIR__ . '/../picture/products/';

            if (!is_dir($uploadDirectory)) {
                mkdir(
                    $uploadDirectory,
                    0777,
                    true
                );
            }

            $fileName =
                'product_' .
                uniqid() .
                '.' .
                $extension;

            $uploadPath =
                $uploadDirectory .
                $fileName;

            if (
                !move_uploaded_file(
                    $_FILES['product_image']['tmp_name'],
                    $uploadPath
                )
            ) {
                throw new Exception(
                    'Failed to upload image.'
                );
            }

            $imagePath =
                '/Jewelry_Shop/picture/products/' .
                $fileName;


            /* Set old images to non-primary */

            $stmt = $con->prepare("
                UPDATE Product_Images
                SET IsPrimary = 0
                WHERE ProductID = :product_id
            ");

            $stmt->execute([
                ':product_id' => $productID
            ]);


            /* Insert new image */

            $imageID =
                'PIMG' .
                strtoupper(
                    substr(
                        uniqid(),
                        -8
                    )
                );

            $stmt = $con->prepare("
                INSERT INTO Product_Images
                (
                    ProductImageID,
                    ProductID,
                    ImagePath,
                    IsPrimary
                )
                VALUES
                (
                    :image_id,
                    :product_id,
                    :image_path,
                    1
                )
            ");

            $stmt->execute([
                ':image_id' => $imageID,
                ':product_id' => $productID,
                ':image_path' => $imagePath
            ]);
        }


        /* ========================================
           COMMIT
        ======================================== */

        $con->commit();

        header(
            'Location: /Jewelry_Shop/products/view.php?id=' .
                $id
        );

        exit;
    } catch (PDOException $e) {

        if ($con->inTransaction()) {
            $con->rollBack();
        }

        $errorMessage = 'Database Error: ' . $e->getMessage();
    } catch (Exception $e) {

        if ($con->inTransaction()) {
            $con->rollBack();
        }

        $errorMessage = $e->getMessage();
    }
}


/* ========================================
   GET PRODUCT FOR EDIT
======================================== */

$sql = "
    SELECT
        p.id,
        p.ProductID,
        p.ProductName,
        p.SKU,
        p.PurchasePrice,
        p.SellingPrice,
        p.StockQuantity,

        p.CategoryID,
        p.BrandID,
        p.PStatusID,

        d.Material,
        d.Purity,
        d.Weight,
        d.GemstoneType,
        d.GemstoneWeight,
        d.WarrantyPeriod,

        bc.Barcode,

        pi.ImagePath

    FROM Products p

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


/* ========================================
   DROPDOWN DATA
======================================== */

$categories = $con
    ->query("
        SELECT CategoryID, CategoryName
        FROM Product_Categories
        ORDER BY CategoryName
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


$brands = $con
    ->query("
        SELECT BrandID, BrandName
        FROM Product_Brand
        ORDER BY BrandName
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


$statuses = $con
    ->query("
        SELECT PStatusID, PStatusName
        FROM Product_Status
        ORDER BY PStatusName
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


$page_css =
    '/Jewelry_Shop/products/assets/css/products.css';

include __DIR__ . '/../includes/header.php';

?>

<div class="products-page">
    <?php if ($errorMessage !== ''): ?>
        <div class="product-error-message">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>


    <!-- ==================================
         PAGE HEADER
    =================================== -->

    <section class="products-page-header">

        <div>

            <h1>Edit Jewelry Product</h1>

            <p>
                Update product information.
            </p>

        </div>


        <a
            href="/Jewelry_Shop/products/"
            class="btn-secondary">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Products

        </a>

    </section>


    <!-- ==================================
         PRODUCT FORM
    =================================== -->

    <section class="product-form-card">

        <div class="form-card-header">

            <div>

                <h3>
                    Product Information
                </h3>

                <p>
                    Update the details of this jewelry product.
                </p>

            </div>

        </div>


        <form
            action=""
            method="POST"
            enctype="multipart/form-data">

            <input
                type="hidden"
                name="id"
                value="<?= htmlspecialchars($product['id']) ?>">


            <!-- ==================================
                 BASIC INFORMATION
            =================================== -->

            <div class="form-section">

                <div class="form-section-title">

                    <i class="fa-solid fa-circle-info"></i>

                    Basic Information

                </div>


                <div class="form-grid">


                    <!-- Product Name -->

                    <div class="form-group">

                        <label>
                            Product Name
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="product_name"
                            value="<?= htmlspecialchars($product['ProductName'] ?? '') ?>"
                            placeholder="Enter product name">

                    </div>


                    <!-- SKU -->

                    <div class="form-group">

                        <label>
                            Product Code (SKU)
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="sku"
                            value="<?= htmlspecialchars($product['SKU']) ?>"
                            placeholder="e.g. RING-001">

                    </div>


                    <!-- Category -->

                    <div class="form-group">

                        <label>
                            Category
                            <span>*</span>
                        </label>

                        <select name="category_id" required>
                            <option value="">Select Category</option>

                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= htmlspecialchars($category['CategoryID']) ?>"
                                    <?= ($product['CategoryID'] ?? '') === $category['CategoryID']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>

                    </div>


                    <!-- Brand -->

                    <div class="form-group">

                        <label>
                            Brand
                        </label>

                        <select name="brand_id">
                            <option value="">Select Brand</option>

                            <?php foreach ($brands as $brand): ?>

                                <option
                                    value="<?= htmlspecialchars($brand['BrandID']) ?>"
                                    <?= ($product['BrandID'] ?? '') === $brand['BrandID']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= htmlspecialchars($brand['BrandName']) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>

                    </div>

                </div>

            </div>


            <!-- ==================================
                 BARCODE
            =================================== -->

            <div class="form-section">

                <div class="form-section-title">

                    <i class="fa-solid fa-barcode"></i>

                    Barcode / QR Code

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label>
                            Barcode
                        </label>

                        <div class="input-with-button">

                            <input
                                type="text"
                                name="barcode"
                                id="barcode"
                                value="<?= htmlspecialchars($product['Barcode'] ?? '') ?>"
                                placeholder="Enter barcode">

                            <button
                                type="button"
                                class="btn-generate"
                                id="generateBarcode">
                                Generate
                            </button>

                        </div>

                    </div>


                    <div class="form-group">

                        <label>
                            QR Code
                        </label>

                        <div class="qr-card">
                            <div class="qr-card-header">
                                <div>
                                    <h3>Product QR Code</h3>
                                    <p>Scan to view product information</p>
                                </div>

                                <i class="fa-solid fa-qrcode qr-header-icon"></i>
                            </div>

                            <div class="qr-preview" id="qrCode">
                                <div class="qr-empty">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span>QR Code Preview</span>
                                </div>
                            </div>

                            <div class="qr-product-info">
                                <strong id="qrProductName">Product Name</strong>
                                <span id="qrProductSku">SKU: —</span>
                            </div>

                            <button
                                type="button"
                                class="btn-generate"
                                id="generateQRCode">
                                <i class="fa-solid fa-qrcode"></i>
                                Generate QR Code
                            </button>
                        </div>

                    </div>

                </div>


                <!-- ==================================
                 JEWELRY DETAILS
            =================================== -->

                <div class="form-section">

                    <div class="form-section-title">

                        <i class="fa-solid fa-gem"></i>

                        Jewelry Details

                    </div>


                    <div class="form-grid">


                        <!-- Material -->

                        <div class="form-group">

                            <label>
                                Material
                            </label>

                            <input
                                type="text"
                                name="material"
                                value="<?= htmlspecialchars($product['Material'] ?? '') ?>"
                                placeholder="e.g. Gold, Silver, Platinum">

                        </div>


                        <!-- Purity -->

                        <div class="form-group">

                            <label>
                                Purity
                            </label>

                            <select name="purity">

                                <option value="">
                                    Select Purity
                                </option>

                                <option
                                    value="18K"
                                    <?= $product['Purity'] === '18K' ? 'selected' : '' ?>>
                                    18K
                                </option>

                                <option
                                    value="22K"
                                    <?= $product['Purity'] === '22K' ? 'selected' : '' ?>>
                                    22K
                                </option>

                                <option
                                    value="24K"
                                    <?= $product['Purity'] === '24K' ? 'selected' : '' ?>>
                                    24K
                                </option>

                            </select>

                        </div>


                        <!-- Weight -->

                        <div class="form-group">

                            <label>
                                Weight (grams)
                            </label>

                            <div class="input-unit">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="weight"
                                    value="<?= htmlspecialchars($product['Weight'] ?? '') ?>"
                                    placeholder="0.00">

                                <span>
                                    grams
                                </span>

                            </div>

                        </div>


                        <!-- Gemstone Type -->

                        <div class="form-group">

                            <label>
                                Gemstone Type
                            </label>

                            <input
                                type="text"
                                name="gemstone_type"
                                value="<?= htmlspecialchars($product['GemstoneType'] ?? '') ?>"
                                placeholder="e.g. Diamond, Ruby, Pearl">

                        </div>


                        <!-- Gemstone Weight -->

                        <div class="form-group">

                            <label>
                                Gemstone Weight
                            </label>

                            <div class="input-unit">

                                <input
                                    type="number"
                                    step="0.01"
                                    name="gemstone_weight"
                                    value="<?= htmlspecialchars($product['GemstoneWeight'] ?? '') ?>"
                                    placeholder="0.00">

                                <span>
                                    Carat
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================
                 PRICE & STOCK
            =================================== -->

                <div class="form-section">

                    <div class="form-section-title">

                        <i class="fa-solid fa-sack-dollar"></i>

                        Price & Stock

                    </div>


                    <div class="form-grid">


                        <!-- Purchase Price -->

                        <div class="form-group">

                            <label>
                                Purchase Price
                                <span>*</span>
                            </label>

                            <div class="input-unit">

                                <span>$</span>

                                <input
                                    type="number"
                                    step="0.01"
                                    name="purchase_price"
                                    value="<?= htmlspecialchars($product['PurchasePrice']) ?>"
                                    placeholder="0.00">

                            </div>

                        </div>


                        <!-- Selling Price -->

                        <div class="form-group">

                            <label>
                                Selling Price
                                <span>*</span>
                            </label>

                            <div class="input-unit">

                                <span>$</span>

                                <input
                                    type="number"
                                    step="0.01"
                                    name="selling_price"
                                    value="<?= htmlspecialchars($product['SellingPrice']) ?>"
                                    placeholder="0.00">

                            </div>

                        </div>


                        <!-- Stock -->

                        <div class="form-group">

                            <label>
                                Stock Quantity
                                <span>*</span>
                            </label>

                            <input
                                type="number"
                                name="stock_quantity"
                                min="0"
                                value="<?= htmlspecialchars($product['StockQuantity']) ?>"
                                placeholder="Enter quantity">

                        </div>


                        <!-- Warranty -->

                        <div class="form-group">

                            <label>
                                Warranty Period
                            </label>

                            <div class="input-unit">

                                <input
                                    type="number"
                                    name="warranty_period"
                                    min="0"
                                    value="<?= htmlspecialchars($product['WarrantyPeriod'] ?? '') ?>"
                                    placeholder="0">

                                <span>
                                    Months
                                </span>

                            </div>

                        </div>


                        <!-- Status -->

                        <div class="form-group">

                            <label>
                                Product Status
                            </label>

                            <select name="status_id">
                                <option value="">Select Status</option>

                                <?php foreach ($statuses as $status): ?>

                                    <option
                                        value="<?= htmlspecialchars($status['PStatusID']) ?>"
                                        <?= ($product['PStatusID'] ?? '') === $status['PStatusID']
                                            ? 'selected'
                                            : '' ?>>
                                        <?= htmlspecialchars($status['PStatusName']) ?>
                                    </option>

                                <?php endforeach; ?>
                            </select>

                        </div>

                    </div>

                </div>


                <!-- ==================================
                 PRODUCT IMAGE
            =================================== -->

                <div class="form-section">

                    <div class="form-section-title">

                        <i class="fa-regular fa-image"></i>

                        Product Image

                    </div>


                    <div class="image-upload">


                        <div
                            class="image-preview"
                            id="imagePreview">

                            <?php if (!empty($product['ImagePath'])): ?>

                                <img
                                    src="<?= htmlspecialchars($product['ImagePath']) ?>"
                                    alt="<?= htmlspecialchars($product['ProductName']) ?>">

                            <?php else: ?>

                                <i class="fa-regular fa-image"></i>

                                <span>
                                    No image selected
                                </span>

                            <?php endif; ?>

                        </div>


                        <div class="image-upload-content">

                            <label
                                for="productImage"
                                class="upload-button">

                                <i class="fa-solid fa-cloud-arrow-up"></i>

                                Choose New Image

                            </label>


                            <input
                                type="file"
                                name="product_image"
                                id="productImage"
                                accept="image/*"
                                hidden>


                            <p>
                                JPG, PNG or WEBP.
                                Maximum recommended size 2MB.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- ==================================
                 ACTIONS
            =================================== -->

                <div class="form-actions">

                    <a
                        href="/Jewelry_Shop/products/"
                        class="btn-cancel">
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn-save-product">

                        <i class="fa-solid fa-check"></i>

                        Update Product

                    </button>

                </div>


        </form>

    </section>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script src="/Jewelry_Shop/products/assets/js/products.js"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>