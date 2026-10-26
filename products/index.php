<?php

require_once __DIR__ . '/../config/database.php';

/* ==============================
   PAGINATION
============================== */

$productsPerPage = 10;

$currentPage = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($currentPage < 1) {
    $currentPage = 1;
}


/* ==============================
   COUNT PRODUCTS
============================== */

$countStmt = $con->prepare("
    SELECT COUNT(*)
    FROM Products
");

$countStmt->execute();

$totalProducts = (int) $countStmt->fetchColumn();

$totalPages = max(
    1,
    (int) ceil($totalProducts / $productsPerPage)
);

if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $productsPerPage;


/* ==============================
   GET PRODUCTS
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

    ORDER BY p.id DESC

    LIMIT :limit OFFSET :offset
";

$stmt = $con->prepare($sql);

$stmt->bindValue(
    ':limit',
    $productsPerPage,
    PDO::PARAM_INT
);

$stmt->bindValue(
    ':offset',
    $offset,
    PDO::PARAM_INT
);

$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ==============================
   GET CATEGORIES & STATUS
   ============================== */

$categoryStmt = $con->prepare("
    SELECT CategoryID, CategoryName
    FROM Product_Categories
    ORDER BY CategoryName ASC
");

$categoryStmt->execute();

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


$statusStmt = $con->prepare("
    SELECT PStatusID, PStatusName
    FROM Product_Status
    ORDER BY PStatusName ASC
");

$statusStmt->execute();

$statuses = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

/* ==============================
   HEADER
============================== */

include __DIR__ . '/../includes/header.php';

?>
<link
    rel="stylesheet"
    href="/Jewelry_Shop/products/assets/css/products.css">


<div class="products-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <section class="products-page-header">

        <div>

            <h1>Products</h1>

            <p>
                Manage your jewelry products and inventory.
            </p>

        </div>


        <a
            href="/Jewelry_Shop/products/add.php"
            class="btn-add-product">

            <i class="fa-solid fa-plus"></i>

            Add Product

        </a>

    </section>



    <!-- =====================================================
         SEARCH & FILTER
    ====================================================== -->

    <section class="product-filter-card">


        <div class="product-search">

            <i class="fa-solid fa-magnifying-glass"></i>

            <input
                type="text"
                id="productSearch"
                placeholder="Search product, SKU or Product ID..."
                autocomplete="off">

        </div>



        <select
            id="categoryFilter"
            class="product-filter">

            <option value="">
                All Categories
            </option>

            <?php foreach ($categories as $category): ?>

                <option value="<?= htmlspecialchars($category['CategoryName']) ?>">
                    <?= htmlspecialchars($category['CategoryName']) ?>
                </option>

            <?php endforeach; ?>

        </select>



        <select
            id="statusFilter"
            class="product-filter">

            <option value="">
                All Status
            </option>

            <?php foreach ($statuses as $status): ?>

                <option value="<?= htmlspecialchars($status['PStatusName']) ?>">
                    <?= htmlspecialchars($status['PStatusName']) ?>
                </option>

            <?php endforeach; ?>

        </select>



        <button
            type="button"
            id="resetFilters"
            class="reset-btn">

            <i class="fa-solid fa-rotate-left"></i>

            Reset

        </button>


    </section>



    <!-- =====================================================
         PRODUCT TABLE
    ====================================================== -->

    <section class="products-card">


        <div class="products-card-header">

            <div>

                <h3>
                    Jewelry Products
                </h3>

                <p>
                    List of all jewelry products
                </p>

            </div>


            <span
                class="product-count"
                id="productCount">

                <?= $totalProducts ?>

                Products

            </span>


        </div>



        <div class="product-table-wrapper">


            <table class="product-table">


                <thead>

                    <tr>

                        <th>Product</th>

                        <th>SKU</th>

                        <th>Category</th>

                        <th>Brand</th>

                        <th>Purchase Price</th>

                        <th>Selling Price</th>

                        <th>Stock</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>



                <tbody id="productTableBody">


                    <?php if (!empty($products)): ?>


                        <?php foreach ($products as $product): ?>


                            <tr
                                data-category="<?= htmlspecialchars(
                                                    $product['CategoryName'] ?? ''
                                                ) ?>"
                                data-status="<?= htmlspecialchars(
                                                    $product['PStatusName'] ?? ''
                                                ) ?>"
                                data-search="<?= htmlspecialchars(
                                                    strtolower(
                                                        ($product['ProductName'] ?? '') . ' ' .
                                                            ($product['SKU'] ?? '') . ' ' .
                                                            ($product['ProductID'] ?? '')
                                                    )
                                                ) ?>">


                                <!-- PRODUCT -->

                                <td>

                                    <div class="product-name-wrapper">


                                        <div class="product-image">

                                            <?php if (!empty($product['ImagePath'])): ?>

                                                <img
                                                    src="<?= htmlspecialchars($product['ImagePath']) ?>"
                                                    alt="<?= htmlspecialchars($product['ProductName']) ?>">

                                            <?php else: ?>

                                                <i class="fa-regular fa-gem"></i>

                                            <?php endif; ?>

                                        </div>


                                        <div>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $product['ProductName']
                                                ) ?>

                                            </strong>


                                            <small>

                                                ID:

                                                <?= htmlspecialchars(
                                                    $product['ProductID']
                                                ) ?>

                                            </small>

                                        </div>


                                    </div>

                                </td>



                                <!-- SKU -->

                                <td>

                                    <?= htmlspecialchars(
                                        $product['SKU']
                                    ) ?>

                                </td>



                                <!-- CATEGORY -->

                                <td>

                                    <?= htmlspecialchars(
                                        $product['CategoryName'] ?? '—'
                                    ) ?>

                                </td>



                                <!-- BRAND -->

                                <td>

                                    <?= htmlspecialchars(
                                        $product['BrandName'] ?? '—'
                                    ) ?>

                                </td>



                                <!-- PURCHASE PRICE -->

                                <td>

                                    $<?= number_format(
                                            (float) $product['PurchasePrice'],
                                            2
                                        ) ?>

                                </td>



                                <!-- SELLING PRICE -->

                                <td class="product-price">

                                    $<?= number_format(
                                            (float) $product['SellingPrice'],
                                            2
                                        ) ?>

                                </td>



                                <!-- STOCK -->

                                <td>

                                    <span class="stock-number">

                                        <?= (int) $product['StockQuantity'] ?>

                                    </span>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span class="status-badge">

                                        <?= htmlspecialchars(
                                            $product['PStatusName'] ?? '—'
                                        ) ?>

                                    </span>

                                </td>



                                <!-- ACTION -->

                                <td>

                                    <div class="product-actions">
                                        <a
                                            href="<?= htmlspecialchars(
                                                        app_base_path() . '/customer/wishlist-add.php?product_id=' .
                                                            rawurlencode($product['ProductID']),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>"
                                            class="action-btn"
                                            title="Add to Wishlist"
                                            aria-label="Add to Wishlist">
                                            <i class="fa-solid fa-heart"></i>
                                        </a>

                                        <a
                                            href="/Jewelry_Shop/products/view.php?id=<?= $product['id'] ?>"
                                            class="action-btn view"
                                            title="View">

                                            <i class="fa-regular fa-eye"></i>

                                        </a>



                                        <a
                                            href="/Jewelry_Shop/products/edit.php?id=<?= $product['id'] ?>"
                                            class="action-btn edit"
                                            title="Edit">

                                            <i class="fa-solid fa-pen"></i>

                                        </a>



                                        <a
                                            href="/Jewelry_Shop/products/delete.php?id=<?= $product['id'] ?>"
                                            class="action-btn delete"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this product?')">

                                            <i class="fa-regular fa-trash-can"></i>

                                        </a>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="9"
                                class="empty-state">

                                No products found.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>



        <!-- =================================================
             PAGINATION
        ================================================== -->

        <div class="product-pagination">


            <span>

                Showing

                <strong id="showingCount">
                    <?= $totalProducts ?>
                </strong>

                products

            </span>



            <div class="pagination">


                <!-- PREVIOUS -->

                <?php if ($currentPage > 1): ?>

                    <a
                        href="?page=<?= $currentPage - 1 ?>"
                        class="pagination-btn">

                        <i class="fa-solid fa-chevron-left"></i>

                    </a>

                <?php else: ?>

                    <span class="pagination-btn disabled">

                        <i class="fa-solid fa-chevron-left"></i>

                    </span>

                <?php endif; ?>



                <!-- PAGE NUMBERS -->

                <?php for (
                    $page = 1;
                    $page <= $totalPages;
                    $page++
                ): ?>


                    <?php if ($page == $currentPage): ?>

                        <span class="pagination-btn active">

                            <?= $page ?>

                        </span>

                    <?php else: ?>

                        <a
                            href="?page=<?= $page ?>"
                            class="pagination-btn">

                            <?= $page ?>

                        </a>

                    <?php endif; ?>


                <?php endfor; ?>



                <!-- NEXT -->

                <?php if ($currentPage < $totalPages): ?>

                    <a
                        href="?page=<?= $currentPage + 1 ?>"
                        class="pagination-btn">

                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                <?php else: ?>

                    <span class="pagination-btn disabled">

                        <i class="fa-solid fa-chevron-right"></i>

                    </span>

                <?php endif; ?>


            </div>


        </div>


    </section>


</div>



<!-- =====================================================
     PRODUCTS JAVASCRIPT
====================================================== -->

<script src="/Jewelry_Shop/products/assets/js/products.js"></script>


<?php include __DIR__ . '/../includes/footer.php'; ?>