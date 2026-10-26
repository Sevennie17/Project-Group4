<?php

require_once __DIR__ . '/../config/database.php';

/* =========================
   LOAD PRODUCTS FROM DATABASE
========================= */
try {
    $stmt = $con->prepare("
        SELECT
            p.ProductID,
            p.ProductName,
            p.SKU,
            p.SellingPrice,
            p.StockQuantity,
            c.CategoryName,
            pi.ImagePath
        FROM Products p
        LEFT JOIN Product_Categories c
            ON p.CategoryID = c.CategoryID
        LEFT JOIN Product_Images pi
            ON p.ProductID = pi.ProductID
            AND pi.IsPrimary = 1
        WHERE p.StockQuantity > 0
        ORDER BY p.id DESC
    ");

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

// =========================
// LOAD CUSTOMERS
// =========================

try {

    $customerStmt = $con->prepare("
            SELECT
                CustomerID,
                CustomerName
            FROM customers
            ORDER BY CustomerName ASC
        ");

    $customerStmt->execute();

    $customers = $customerStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    $customers = [];
}
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/Jewelry-app/sales/assets/css/sales.css?v=1">

<div class="sales-page">

    <!-- =========================
         PAGE HEADER
    ========================== -->
    <div class="page-header">

        <div>
            <h1>Sales</h1>
            <p>Process sales transactions and create invoices.</p>
        </div>

        <button class="btn-new-sale" id="newSaleBtn">
            <i class="fa-solid fa-plus"></i>
            New Sale
        </button>

    </div>


    <!-- =========================
         POS LAYOUT
    ========================== -->
    <div class="pos-layout">


        <!-- =====================
             LEFT : PRODUCTS
        ====================== -->
        <div class="products-section">

            <div class="products-card">

                <!-- Search -->
                <div class="search-row">

                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input
                            type="text"
                            id="productSearch"
                            placeholder="Search product, SKU or barcode...">
                    </div>

                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="Rings">Rings</option>
                        <option value="Necklaces">Necklaces</option>
                        <option value="Bracelets">Bracelets</option>
                        <option value="Earrings">Earrings</option>
                        <option value="Pendants">Pendants</option>
                        <option value="Watches">Watches</option>
                    </select>

                </div>


                <!-- Category Buttons -->
                <div class="category-buttons">

                    <button class="category-btn active" data-category="">
                        All
                    </button>

                    <button class="category-btn" data-category="Rings">
                        Rings
                    </button>

                    <button class="category-btn" data-category="Necklaces">
                        Necklaces
                    </button>

                    <button class="category-btn" data-category="Bracelets">
                        Bracelets
                    </button>

                    <button class="category-btn" data-category="Earrings">
                        Earrings
                    </button>

                    <button class="category-btn" data-category="Pendants">
                        Pendants
                    </button>

                    <button class="category-btn" data-category="Watches">
                        Watches
                    </button>

                </div>


                <!-- Products Header -->
                <div class="section-header">

                    <div>
                        <h2>Products</h2>
                        <p>Select a product to add to cart.</p>
                    </div>

                    <span class="product-count">
                        <?= count($products) ?> Products
                    </span>

                </div>


                <!-- =====================
                     PRODUCT GRID
                ====================== -->
                <div class="product-grid" id="productGrid">

                    <?php if (!empty($products)): ?>

                        <?php foreach ($products as $product): ?>

                            <div
                                class="product-item"
                                data-name="<?= htmlspecialchars($product['ProductName']) ?>"
                                data-sku="<?= htmlspecialchars($product['SKU']) ?>"
                                data-category="<?= htmlspecialchars($product['CategoryName'] ?? '') ?>"
                                data-price="<?= htmlspecialchars($product['SellingPrice']) ?>"
                                data-stock="<?= htmlspecialchars($product['StockQuantity']) ?>">

                                <div class="product-image">

                                    <?php if (!empty($product['ImagePath'])): ?>

                                        <img
                                            src="<?= htmlspecialchars(str_replace('/Jewelry​-app/', '/Jewelry-app/', $product['ImagePath'])) ?>"
                                            alt="<?= htmlspecialchars($product['ProductName']) ?>">

                                    <?php else: ?>

                                        <i class="fa-regular fa-gem"></i>

                                    <?php endif; ?>

                                </div>

                                <div class="product-info">

                                    <h3>
                                        <?= htmlspecialchars($product['ProductName']) ?>
                                    </h3>

                                    <span class="product-sku">
                                        <?= htmlspecialchars($product['SKU']) ?>
                                    </span>

                                    <strong class="product-price">
                                        $<?= number_format((float)$product['SellingPrice'], 2) ?>
                                    </strong>

                                    <?php if ((int)$product['StockQuantity'] <= 5): ?>

                                        <span class="stock low">
                                            Stock: <?= (int)$product['StockQuantity'] ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="stock available">
                                            Stock: <?= (int)$product['StockQuantity'] ?>
                                        </span>

                                    <?php endif; ?>

                                    <button
                                        class="add-cart-btn"
                                        onclick="addToCart(this)">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        Add to Cart
                                    </button>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="empty-products">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>No products available</h3>
                            <p>There are currently no products in stock.</p>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =====================
             RIGHT : CART
        ====================== -->
        <div class="cart-section">

            <div class="cart-card">


                <!-- Cart Header -->
                <div class="cart-header">

                    <h2>
                        <i class="fa-solid fa-cart-shopping"></i>
                        Cart
                        <span id="cartCount">(0 items)</span>
                    </h2>

                    <button
                        class="clear-cart-btn"
                        id="clearCartBtn">
                        <i class="fa-solid fa-trash"></i>
                        Clear
                    </button>

                </div>


                <!-- Cart Items -->
                <div class="cart-items" id="cartItems">

                    <div class="empty-cart">

                        <i class="fa-solid fa-cart-shopping"></i>

                        <h3>Your cart is empty</h3>

                        <p>
                            Add products from the catalog.
                        </p>

                    </div>

                </div>


                <!-- Customer -->
                <div class="checkout-section">

                    <div class="checkout-title">
                        <i class="fa-solid fa-user"></i>
                        Customer
                        <span>(Optional)</span>
                    </div>

                    <div class="customer-row">

                        <select id="customerSelect">

                            <option value="">
                                Search customer...
                            </option>

                            <?php foreach ($customers as $customer): ?>

                                <option value="<?= htmlspecialchars($customer['CustomerID']) ?>">
                                    <?= htmlspecialchars($customer['CustomerName']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button type="button" class="btn-small" id="newCustomerBtn">
                            <i class="fa-solid fa-plus"></i>
                            New
                        </button>

                    </div>

                </div>


                <!-- Membership -->
                <div class="checkout-section">

                    <div class="checkout-title">
                        <i class="fa-solid fa-crown"></i>
                        Membership
                    </div>

                    <select id="membership" disabled>
                        <option value="0">Select a customer first</option>
                    </select>

                </div>


                <!-- Discount & Tax -->
                <div class="summary-section">

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong id="subtotal">
                            $0.00
                        </strong>
                    </div>


                    <div class="summary-row">

                        <span>Discount</span>

                        <div class="discount-input">

                            <input
                                type="number"
                                id="discount"
                                value="0"
                                min="0">

                            <select id="discountType">

                                <option value="percent">
                                    %
                                </option>

                                <option value="fixed">
                                    $
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="summary-row">

                        <span>Membership Benefit</span>

                        <strong id="membershipDiscount">
                            -$0.00
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>Tax (8%)</span>

                        <strong id="tax">
                            $0.00
                        </strong>

                    </div>


                    <div class="total-row">

                        <span>Total</span>

                        <strong id="total">
                            $0.00
                        </strong>

                    </div>

                </div>


                <!-- Payment -->
                <div class="payment-section">

                    <div class="checkout-title">
                        <i class="fa-solid fa-money-bill"></i>
                        Payment Method
                    </div>


                    <div class="payment-methods">

                        <button
                            class="payment-btn active"
                            data-payment="cash">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Cash</span>
                        </button>


                        <button
                            class="payment-btn"
                            data-payment="card">
                            <i class="fa-regular fa-credit-card"></i>
                            <span>Card</span>
                        </button>


                        <button
                            class="payment-btn"
                            data-payment="bank">
                            <i class="fa-solid fa-building-columns"></i>
                            <span>Bank Transfer</span>
                        </button>


                        <button
                            class="payment-btn"
                            data-payment="mobile">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <span>Mobile Payment</span>
                        </button>


                        <button
                            class="payment-btn"
                            data-payment="qr">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>QR Payment</span>
                        </button>

                    </div>

                </div>



                <!-- Actions -->
                <div class="cart-actions">

                    <button
                        class="btn-invoice"
                        id="generateInvoiceBtn">
                        <i class="fa-solid fa-file-invoice"></i>
                        Generate Invoice
                    </button>

                    <button
                        class="btn-payment"
                        id="completePaymentBtn">
                        <i class="fa-solid fa-check"></i>
                        Complete Payment
                    </button>

                    <!-- Invoice History -->
                    <a
                        href="invoice-history.php"
                        class="btn-invoice-history">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Invoice History
                    </a>

                </div>

                <!-- Return / Exchange -->
                <a
                    href="return-exchange.php"
                    class="return-link">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    Product Return & Exchange
                </a>


            </div>

        </div>

    </div>

</div>

<!-- =========================
     NEW CUSTOMER MODAL
========================= -->
<div class="customer-modal" id="customerModal">

    <div class="customer-modal-overlay" id="customerModalOverlay"></div>

    <div class="customer-modal-box">

        <div class="customer-modal-header">
            <div>
                <h2>
                    <i class="fa-solid fa-user-plus"></i>
                    New Customer
                </h2>
                <p>Add a new customer to the system.</p>
            </div>

            <button type="button"
                class="customer-modal-close"
                id="closeCustomerModal">
                &times;
            </button>
        </div>

        <form id="newCustomerForm">

            <div class="customer-form-grid">

                <div class="form-group">
                    <label for="newCustomerName">
                        Customer Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="newCustomerName"
                        name="customerName"
                        placeholder="Enter customer name"
                        required>
                </div>

                <div class="form-group">
                    <label for="newCustomerPhone">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        id="newCustomerPhone"
                        name="phone"
                        placeholder="Enter phone number">
                </div>

                <div class="form-group">
                    <label for="newCustomerAddress">
                        Address
                    </label>

                    <input
                        type="text"
                        id="newCustomerAddress"
                        name="address"
                        placeholder="Enter address">
                </div>

                <div class="form-group">
                    <label for="newCustomerBirthDate">
                        Birth Date
                    </label>

                    <input
                        type="date"
                        id="newCustomerBirthDate"
                        name="birthDate">
                </div>

                <div class="form-group">
                    <label for="newCustomerMembership">
                        Membership
                    </label>

                    <select
                        id="newCustomerMembership"
                        name="membershipID">

                        <option value="">No Membership</option>
                        <option value="MJW004">Silver - 5%</option>
                        <option value="MJW005">Gold - 10%</option>
                        <option value="MJW006">Platinum - 15%</option>

                    </select>
                </div>

            </div>

            <div class="customer-modal-actions">

                <button
                    type="button"
                    class="customer-btn-cancel"
                    id="cancelCustomerBtn">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="customer-btn-save"
                    id="saveCustomerBtn">
                    <i class="fa-solid fa-check"></i>
                    Save Customer
                </button>

            </div>

        </form>

    </div>
</div>


<script src="assets/js/sales.js"></script>

<?php
include __DIR__ . '/../includes/footer.php';
?>