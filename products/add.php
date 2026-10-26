<?php

require_once __DIR__ . '/../config/database.php';

// Get Categories
$categories = $con->query("
    SELECT CategoryID, CategoryName
    FROM Product_Categories
    ORDER BY CategoryName
")->fetchAll(PDO::FETCH_ASSOC);

// Get Brands
$brands = $con->query("
    SELECT BrandID, BrandName
    FROM Product_Brand
    ORDER BY BrandName
")->fetchAll(PDO::FETCH_ASSOC);

// Get Product Status
$statuses = $con->query("
    SELECT PStatusID, PStatusName
    FROM Product_Status
    ORDER BY PStatusName
")->fetchAll(PDO::FETCH_ASSOC);

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        /* =========================================
           GET FORM DATA
        ========================================= */

        $product_name = trim($_POST['product_name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');

        $category_id = trim($_POST['category_id'] ?? '');
        $brand_id = trim($_POST['brand_id'] ?? '');
        $status_id = trim($_POST['status_id'] ?? '');

        $barcode = trim($_POST['barcode'] ?? '');

        $material = trim($_POST['material'] ?? '');
        $purity = trim($_POST['purity'] ?? '');

        $weight = !empty($_POST['weight'])
            ? $_POST['weight']
            : null;

        $gemstone_type = trim($_POST['gemstone_type'] ?? '');

        $gemstone_weight = !empty($_POST['gemstone_weight'])
            ? $_POST['gemstone_weight']
            : null;

        $purchase_price = $_POST['purchase_price'] ?? 0;
        $selling_price = $_POST['selling_price'] ?? 0;
        $stock_quantity = $_POST['stock_quantity'] ?? 0;

        $warranty_period = !empty($_POST['warranty_period'])
            ? $_POST['warranty_period']
            : null;

        /* =========================================
        VALIDATION
        ========================================= */

        if (
            $product_name === '' ||
            $sku === '' ||
            $category_id === '' ||
            $brand_id === '' ||
            $status_id === '' ||
            $barcode === '' ||
            $material === '' ||
            $purity === '' ||
            $weight === null ||
            $purchase_price === '' ||
            $selling_price === '' ||
            $stock_quantity === ''
        ) {
            throw new Exception(
                'Please fill in all required fields.'
            );
        }

        /* =========================================
   CHECK DUPLICATE SKU
========================================= */

        $skuCheck = $con->prepare("
    SELECT COUNT(*)
    FROM Products
    WHERE LOWER(TRIM(SKU)) = LOWER(TRIM(:sku))
");

        $skuCheck->execute([
            ':sku' => $sku
        ]);

        if ((int)$skuCheck->fetchColumn() > 0) {
            throw new Exception(
                'SKU already exists. Please use a different SKU.'
            );
        }


        /* =========================================
        CHECK DUPLICATE BARCODE
        ========================================= */

        $barcodeCheck = $con->prepare("
            SELECT COUNT(*)
            FROM Product_Barcodes
            WHERE Barcode = :barcode
        ");

        $barcodeCheck->execute([
            ':barcode' => $barcode
        ]);

        if ((int)$barcodeCheck->fetchColumn() > 0) {
            throw new Exception(
                'Barcode already exists. Please use a different barcode.'
            );
        }

        /* =========================================
        NUMERIC VALIDATION
        ========================================= */

        if (!is_numeric($weight) || (float)$weight <= 0) {
            throw new Exception(
                'Weight must be greater than 0.'
            );
        }

        if (!is_numeric($purchase_price) || (float)$purchase_price <= 0) {
            throw new Exception(
                'Purchase Price must be greater than 0.'
            );
        }

        if (!is_numeric($selling_price) || (float)$selling_price <= 0) {
            throw new Exception(
                'Selling Price must be greater than 0.'
            );
        }

        if (!is_numeric($stock_quantity) || (int)$stock_quantity < 0) {
            throw new Exception(
                'Stock Quantity cannot be negative.'
            );
        }


        /* =========================================
        PRICE VALIDATION
        ========================================= */

        if ((float)$selling_price < (float)$purchase_price) {
            throw new Exception(
                'Selling Price cannot be lower than Purchase Price.'
            );
        }


        /* =========================================
        GEMSTONE WEIGHT VALIDATION
        ========================================= */

        if (
            $gemstone_weight !== null &&
            (!is_numeric($gemstone_weight) ||
                (float)$gemstone_weight < 0)
        ) {
            throw new Exception(
                'Gemstone Weight cannot be negative.'
            );
        }


        /* =========================================
        WARRANTY VALIDATION
        ========================================= */

        if (
            $warranty_period !== null &&
            (!is_numeric($warranty_period) ||
                (int)$warranty_period < 0)
        ) {
            throw new Exception(
                'Warranty Period cannot be negative.'
            );
        }


        /* =========================================
           IMAGE UPLOAD
        ========================================= */

        $imagePath = null;

        if (
            isset($_FILES['product_image']) &&
            $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES['product_image']['error']
                !== UPLOAD_ERR_OK
            ) {

                throw new Exception(
                    'Image upload failed.'
                );
            }


            if (
                $_FILES['product_image']['size']
                > 2 * 1024 * 1024
            ) {

                throw new Exception(
                    'Image is too large. Maximum size is 2MB.'
                );
            }


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


            if (
                !in_array(
                    $extension,
                    $allowedExtensions,
                    true
                )
            ) {

                throw new Exception(
                    'Invalid image format.'
                );
            }


            $uploadDirectory =
                __DIR__ . '/../picture/products/';


            if (!is_dir($uploadDirectory)) {

                if (!mkdir(
                    $uploadDirectory,
                    0777,
                    true
                )) {

                    throw new Exception(
                        'Could not create upload folder.'
                    );
                }
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
                    'Could not save image.'
                );
            }


            $imagePath =
                '/Jewelry_Shop/picture/products/' .
                $fileName;
        }


        /* =========================================
           START TRANSACTION
        ========================================= */

        $con->beginTransaction();


        /* =========================================
           1. INSERT PRODUCTS
        ========================================= */

        $sql = "
            INSERT INTO Products (
                ProductName,
                SKU,
                PurchasePrice,
                SellingPrice,
                StockQuantity,
                Description,
                CategoryID,
                BrandID,
                PStatusID
            )
            VALUES (
                :product_name,
                :sku,
                :purchase_price,
                :selling_price,
                :stock_quantity,
                :description,
                :category_id,
                :brand_id,
                :status_id
            )
        ";


        $stmt = $con->prepare($sql);


        $stmt->execute([

            ':product_name' =>
            $product_name,

            ':sku' =>
            $sku,

            ':purchase_price' =>
            $purchase_price,

            ':selling_price' =>
            $selling_price,

            ':stock_quantity' =>
            $stock_quantity,

            ':description' =>
            null,

            ':category_id' =>
            $category_id,

            ':brand_id' =>
            $brand_id !== ''
                ? $brand_id
                : null,

            ':status_id' =>
            $status_id !== ''
                ? $status_id
                : null
        ]);


        /* =========================================
           GET GENERATED ProductID
        ========================================= */

        $productId = $con->lastInsertId();


        $stmt = $con->prepare("
            SELECT ProductID
            FROM Products
            WHERE id = ?
        ");

        $stmt->execute([
            $productId
        ]);

        $productID =
            $stmt->fetchColumn();


        /* =========================================
           2. PRODUCT DETAILS
        ========================================= */

        $stmt = $con->prepare("
            INSERT INTO Product_Details (
                ProductID,
                Material,
                Purity,
                Weight,
                GemstoneType,
                GemstoneWeight,
                WarrantyPeriod
            )
            VALUES (
                :product_id,
                :material,
                :purity,
                :weight,
                :gemstone_type,
                :gemstone_weight,
                :warranty_period
            )
        ");


        $stmt->execute([

            ':product_id' =>
            $productID,

            ':material' =>
            $material !== ''
                ? $material
                : null,

            ':purity' =>
            $purity !== ''
                ? $purity
                : null,

            ':weight' =>
            $weight,

            ':gemstone_type' =>
            $gemstone_type !== ''
                ? $gemstone_type
                : null,

            ':gemstone_weight' =>
            $gemstone_weight,

            ':warranty_period' =>
            $warranty_period
        ]);


        /* =========================================
           3. PRODUCT BARCODE
        ========================================= */

        if ($barcode !== '') {

            $stmt = $con->prepare("
                INSERT INTO Product_Barcodes (
                    ProductID,
                    Barcode
                )
                VALUES (
                    :product_id,
                    :barcode
                )
            ");


            $stmt->execute([

                ':product_id' =>
                $productID,

                ':barcode' =>
                $barcode
            ]);
        }


        /* =========================================
           4. PRODUCT IMAGE
        ========================================= */

        if ($imagePath !== null) {

            $imageID =
                'PIMG' .
                str_pad(
                    (string) random_int(
                        1,
                        999999
                    ),
                    6,
                    '0',
                    STR_PAD_LEFT
                );


            $stmt = $con->prepare("
                INSERT INTO Product_Images (
                    ProductImageID,
                    ProductID,
                    ImagePath,
                    IsPrimary
                )
                VALUES (
                    :image_id,
                    :product_id,
                    :image_path,
                    1
                )
            ");


            $stmt->execute([

                ':image_id' =>
                $imageID,

                ':product_id' =>
                $productID,

                ':image_path' =>
                $imagePath
            ]);
        }


        /* =========================================
           COMMIT
        ========================================= */

        $con->commit();


        header(
            'Location: /Jewelry_Shop/products/index.php'
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

?>

<?php

$page_css = '/Jewelry_Shop/products/assets/css/products.css';

include __DIR__ . '/../includes/header.php';

?>
<div class="products-page">
    <?php if ($errorMessage !== ''): ?>
        <div class="product-error-message" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- PAGE HEADER -->
    <section class="products-page-header">

        <div>
            <h1>Add Jewelry Product</h1>
            <p>Create a new jewelry product.</p>
        </div>

        <a href="/Jewelry_Shop/products/" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Products
        </a>

    </section>


    <!-- PRODUCT FORM -->
    <section class="product-form-card">

        <div class="form-card-header">

            <div>
                <h3>Product Information</h3>
                <p>Enter the details of the jewelry product.</p>
            </div>

        </div>


        <form action="" method="POST" enctype="multipart/form-data">


            <!-- BASIC INFORMATION -->
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
                            placeholder="Enter product name"
                            required>

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
                            placeholder="e.g. RING-001"
                            required>
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
                                <option value="<?= htmlspecialchars($category['CategoryID']) ?>">
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

                        <select name="brand_id" required>
                            <option value="">Select Brand</option>

                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= htmlspecialchars($brand['BrandID']) ?>">
                                    <?= htmlspecialchars($brand['BrandName']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- BARCODE / QR CODE -->
            <div class="form-section">

                <div class="form-section-title">
                    <i class="fa-solid fa-barcode"></i>
                    Barcode / QR Code
                </div>


                <div class="form-grid">

                    <!-- Barcode -->
                    <div class="form-group">

                        <label>
                            Barcode
                        </label>

                        <div class="input-with-button">

                            <input
                                type="text"
                                name="barcode"
                                id="barcode"
                                placeholder="Enter barcode"
                                required>

                            <button
                                type="button"
                                class="btn-generate"
                                id="generateBarcode">
                                Generate
                            </button>

                        </div>

                    </div>


                    <!-- QR Code -->
                    <div class="form-group">

                        <label>
                            QR Code
                        </label>

                        <div class="qr-placeholder" id="qrCode">

                            <i class="fa-solid fa-qrcode"></i>

                            <span>
                                QR Code will appear here
                            </span>

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


            <!-- JEWELRY DETAILS -->
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
                            placeholder="e.g. Gold, Silver, Platinum"
                            required>

                    </div>


                    <!-- Purity -->
                    <div class="form-group">

                        <label>
                            Purity
                        </label>

                        <select name="purity" ​required>>

                            <option value="">
                                Select Purity
                            </option>

                            <option value="18K">
                                18K
                            </option>

                            <option value="22K">
                                22K
                            </option>

                            <option value="24K">
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
                                placeholder="0.00"
                                required>

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
                                placeholder="0.00">

                            <span>
                                Carat
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- PRICE & STOCK -->
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
                                placeholder="0.00"
                                required>

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
                                placeholder="0.00"
                                required>

                        </div>

                    </div>


                    <!-- Stock Quantity -->
                    <div class="form-group">

                        <label>
                            Stock Quantity
                            <span>*</span>
                        </label>

                        <input
                            type="number"
                            name="stock_quantity"
                            min="0"
                            placeholder="Enter quantity"
                            required>

                    </div>


                    <!-- Warranty Period -->
                    <div class="form-group">

                        <label>
                            Warranty Period
                        </label>

                        <div class="input-unit">

                            <input
                                type="number"
                                name="warranty_period"
                                min="0"
                                placeholder="0">

                            <span>
                                Months
                            </span>

                        </div>

                    </div>


                    <!-- Product Status -->
                    <div class="form-group">

                        <label>
                            Product Status
                        </label>

                        <select name="status_id" required>
                            <option value="">Select Status</option>

                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= htmlspecialchars($status['PStatusID']) ?>">
                                    <?= htmlspecialchars($status['PStatusName']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- PRODUCT IMAGE -->
            <div class="form-section">

                <div class="form-section-title">
                    <i class="fa-regular fa-image"></i>
                    Product Image
                </div>


                <div class="image-upload">

                    <div
                        class="image-preview"
                        id="imagePreview">

                        <i class="fa-regular fa-image"></i>

                        <span>
                            No image selected
                        </span>

                    </div>


                    <div class="image-upload-content">

                        <label
                            for="productImage"
                            class="upload-button">

                            <i class="fa-solid fa-cloud-arrow-up"></i>

                            Choose Image

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


            <!-- FORM ACTIONS -->
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

                    Save Product

                </button>

            </div>


        </form>

    </section>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script src="/Jewelry_Shop/products/assets/js/products.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>