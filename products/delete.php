<?php

require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;

/* ==============================
   DELETE PRODUCT
   ============================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['confirm_delete'])
) {

    if (!$id || !is_numeric($id)) {
        die('Invalid product ID.');
    }

    try {

        $con->beginTransaction();

        /* ==============================
           GET PRODUCT + PRIMARY IMAGE
           ============================== */

        $stmt = $con->prepare("
            SELECT
                p.id,
                p.ProductID,
                p.ProductName,
                pi.ImagePath
            FROM Products p
            LEFT JOIN Product_Images pi
                ON p.ProductID = pi.ProductID
                AND pi.IsPrimary = 1
            WHERE p.id = :id
        ");

        $stmt->execute([
            ':id' => $id
        ]);

        $productToDelete = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$productToDelete) {
            $con->rollBack();
            die('Product not found.');
        }

        /* ==============================
           SAVE IMAGE PATH
           ============================== */

        $imagePath = $productToDelete['ImagePath'] ?? null;

        /* ==============================
           DELETE PRODUCT
           
           Related records will also be
           deleted automatically where
           FOREIGN KEY uses ON DELETE CASCADE.
           ============================== */

        $deleteStmt = $con->prepare("
            DELETE FROM Products
            WHERE id = :id
        ");

        $deleteStmt->execute([
            ':id' => $id
        ]);

        /* ==============================
           COMMIT
           ============================== */

        $con->commit();

        /* ==============================
           DELETE IMAGE FILE
           ============================== */

        if (!empty($imagePath)) {

            $fullImagePath =
                $_SERVER['DOCUMENT_ROOT'] .
                $imagePath;

            if (file_exists($fullImagePath)) {
                unlink($fullImagePath);
            }
        }

        /* ==============================
           BACK TO PRODUCT LIST
           ============================== */

        header(
            'Location: /Jewelry_Shop/products/index.php'
        );

        exit;
    } catch (PDOException $e) {

        if ($con->inTransaction()) {
            $con->rollBack();
        }

        die('Database Error: ' .
            htmlspecialchars($e->getMessage()));
    }
}


/* ==============================
   VALIDATE PRODUCT ID
   ============================== */

if (!$id || !is_numeric($id)) {
    die('Invalid product ID.');
}


/* ==============================
   GET PRODUCT INFORMATION
   ============================== */

$stmt = $con->prepare("
    SELECT
        p.id,
        p.ProductID,
        p.ProductName,
        p.SKU,
        p.StockQuantity,
        p.SellingPrice,

        c.CategoryName,

        b.BrandName,

        s.PStatusName,

        pi.ImagePath

    FROM Products p

    LEFT JOIN Product_Categories c
        ON p.CategoryID = c.CategoryID

    LEFT JOIN Product_Brand b
        ON p.BrandID = b.BrandID

    LEFT JOIN Product_Status s
        ON p.PStatusID = s.PStatusID

    LEFT JOIN Product_Images pi
        ON p.ProductID = pi.ProductID
        AND pi.IsPrimary = 1

    WHERE p.id = :id
");

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

    <!-- Page Header -->

    <div class="page-header">

        <div>

            <h1>
                <i class="fa-solid fa-trash-can"></i>
                Delete Product
            </h1>

            <p>
                Remove this product from your product list
            </p>

        </div>

        <a href="index.php" class="btn-secondary">

            <i class="fa-solid fa-arrow-left"></i>

            Back to Products

        </a>

    </div>


    <!-- Delete Card -->

    <div class="delete-product-card">

        <!-- Warning Icon -->

        <div class="delete-icon">

            <i class="fa-solid fa-trash-can"></i>

        </div>


        <!-- Title -->

        <h2>Delete Product?</h2>

        <p class="delete-message">

            Are you sure you want to delete this product?

            This action cannot be undone.

        </p>


        <!-- Product Information -->

        <div class="delete-product-info">

            <div class="delete-product-avatar">

                <?php if (!empty($product['ImagePath'])): ?>

                    <img
                        src="<?= htmlspecialchars($product['ImagePath']) ?>"
                        alt="<?= htmlspecialchars($product['ProductName']) ?>"
                        style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">

                <?php else: ?>

                    <i class="fa-solid fa-ring"></i>

                <?php endif; ?>

            </div>


            <div class="delete-product-details">

                <h3>

                    <?= htmlspecialchars(
                        $product['ProductName'] ?? ''
                    ) ?>

                </h3>


                <span class="product-sku">

                    SKU:
                    <?= htmlspecialchars(
                        $product['SKU'] ?? ''
                    ) ?>

                </span>


                <div class="delete-product-meta">

                    <span>

                        <i class="fa-solid fa-layer-group"></i>

                        <?= htmlspecialchars(
                            $product['CategoryName'] ?? 'N/A'
                        ) ?>

                    </span>


                    <span>

                        <i class="fa-solid fa-box"></i>

                        <?= htmlspecialchars(
                            $product['StockQuantity'] ?? 0
                        ) ?>

                        in stock

                    </span>


                    <span>

                        <i class="fa-solid fa-dollar-sign"></i>

                        $
                        <?= number_format(
                            (float)($product['SellingPrice'] ?? 0),
                            2
                        ) ?>

                    </span>

                </div>

            </div>

        </div>


        <!-- Warning -->

        <div class="delete-warning">

            <i class="fa-solid fa-triangle-exclamation"></i>

            <div>

                <strong>Warning</strong>

                <p>

                    Deleting this product will permanently remove it
                    from the product list.

                </p>

            </div>

        </div>


        <!-- Actions -->

        <div class="delete-actions">

            <a
                href="index.php"
                class="btn-cancel-delete">

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </a>


            <button
                type="button"
                class="btn-confirm-delete"
                id="confirmDelete">

                <i class="fa-solid fa-trash-can"></i>

                Delete Product

            </button>

        </div>

    </div>

</div>


<!-- Delete Confirmation Modal -->

<div
    class="delete-modal"
    id="deleteModal">

    <div class="delete-modal-content">

        <div class="modal-delete-icon">

            <i class="fa-solid fa-trash-can"></i>

        </div>


        <h3>Confirm Delete</h3>


        <p>

            Are you really sure you want to delete

            <strong>
                <?= htmlspecialchars(
                    $product['ProductName'] ?? ''
                ) ?>
            </strong>?

        </p>


        <div class="modal-actions">

            <button
                type="button"
                class="modal-cancel"
                id="cancelDelete">

                Cancel

            </button>


            <form
                method="POST"
                class="delete-form">

                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars($id) ?>">

                <input
                    type="hidden"
                    name="confirm_delete"
                    value="1">


                <button
                    type="submit"
                    class="modal-confirm">

                    <i class="fa-solid fa-trash-can"></i>

                    Yes, Delete

                </button>

            </form>

        </div>

    </div>

</div>


<script>
    const confirmDelete =
        document.getElementById('confirmDelete');

    const deleteModal =
        document.getElementById('deleteModal');

    const cancelDelete =
        document.getElementById('cancelDelete');


    // Open modal

    confirmDelete.addEventListener('click', function() {

        deleteModal.classList.add('show');

    });


    // Close modal

    cancelDelete.addEventListener('click', function() {

        deleteModal.classList.remove('show');

    });


    // Close when clicking outside

    deleteModal.addEventListener('click', function(e) {

        if (e.target === deleteModal) {

            deleteModal.classList.remove('show');

        }

    });
</script>


<?php include __DIR__ . '/../includes/footer.php'; ?>