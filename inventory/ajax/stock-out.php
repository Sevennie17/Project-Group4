<?php
include __DIR__ . '/../../includes/header.php';
?>

<link rel="stylesheet" href="/Jewelry-app/inventory/assets/css/inventory.css">

<div class="stock-out-page">

    <!-- =========================================
         BREADCRUMB
    ========================================== -->
    <div class="stock-out-breadcrumb">

        <a href="/Jewelry_Shop/dashboards/">
            <i class="fa-solid fa-house"></i>
            Home
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <a href="/Jewelry_Shop/inventory/">
            Inventory
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <strong>Stock Out</strong>

    </div>


    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="stock-out-page-header">

        <div class="stock-out-title-wrapper">

            <div class="stock-out-title-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>

            <div>
                <h1>Stock Out</h1>

                <p>
                    Remove outgoing products from your jewelry inventory.
                </p>
            </div>

        </div>


        <a
            href="/Jewelry_Shop/inventory/"
            class="back-inventory-btn stock-out-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Inventory
        </a>

    </div>


    <!-- =========================================
         STOCK OUT INFORMATION
    ========================================== -->
    <section class="stock-out-card">

        <div class="stock-out-card-header">

            <div class="stock-out-section-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>

            <div>
                <h2>Stock Out Information</h2>

                <p>
                    Enter information about this stock transaction.
                </p>
            </div>

        </div>


        <div class="stock-out-info-grid">

            <!-- Stock Out Number -->
            <div class="form-group">

                <label>Stock Out No.</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-hashtag"></i>

                    <input
                        type="text"
                        id="stockOutNo"
                        class="form-control"
                        value="SO-0001"
                        readonly>

                </div>

            </div>


            <!-- Date -->
            <div class="form-group">

                <label>Date</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-calendar-check"></i>

                    <input
                        type="date"
                        id="stockOutDate"
                        class="form-control">

                </div>

            </div>


            <!-- Customer -->
            <div class="form-group">

                <label>Customer / Recipient</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-user-tag"></i>

                    <select
                        id="customer"
                        class="form-control">

                        <option value="">
                            Select Customer
                        </option>

                        <option value="Customer 01">
                            Customer 01
                        </option>

                        <option value="Customer 02">
                            Customer 02
                        </option>

                        <option value="Walk-in Customer">
                            Walk-in Customer
                        </option>

                    </select>

                </div>

            </div>


            <!-- Reference -->
            <div class="form-group">

                <label>Reference / Invoice</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-file-circle-minus"></i>

                    <input
                        type="text"
                        id="outReference"
                        class="form-control"
                        placeholder="Enter invoice or reference number">

                </div>

            </div>


            <!-- Reason -->
            <div class="form-group">

                <label>Reason</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-list-check"></i>

                    <select
                        id="outReason"
                        class="form-control">

                        <option value="">
                            Select reason
                        </option>

                        <option value="Customer Sale">
                            Customer Sale
                        </option>

                        <option value="Repair / Service">
                            Repair / Service
                        </option>

                        <option value="Gift / Promotion">
                            Gift / Promotion
                        </option>

                        <option value="Transfer">
                            Transfer
                        </option>

                        <option value="Damaged">
                            Damaged / Defective
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>

            </div>


            <!-- Notes -->
            <div class="form-group">

                <label>Notes</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-note-sticky"></i>

                    <input
                        type="text"
                        id="outNotes"
                        class="form-control"
                        placeholder="Optional notes...">

                </div>

            </div>

        </div>

    </section>



    <!-- =========================================
         REMOVE PRODUCTS
    ========================================== -->
    <section class="stock-out-card">

        <div class="stock-out-card-header">

            <div class="stock-out-section-icon">
                <i class="fa-solid fa-cart-arrow-down"></i>
            </div>

            <div>
                <h2>Remove Products</h2>

                <p>
                    Select products and enter outgoing quantities.
                </p>
            </div>

        </div>


        <!-- PRODUCT INPUT -->
        <div class="stock-out-product-grid">


            <!-- Product -->
            <div class="form-group">

                <label>Product</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-gem"></i>

                    <select
                        id="stockOutProduct"
                        class="form-control">

                        <option value="">
                            Select Product
                        </option>

                        <option
                            value="Gold Necklace"
                            data-code="N001"
                            data-stock="50"
                            data-price="45">
                            Gold Necklace
                        </option>

                        <option
                            value="Diamond Ring"
                            data-code="R002"
                            data-stock="20"
                            data-price="80">
                            Diamond Ring
                        </option>

                        <option
                            value="Gold Bracelet"
                            data-code="B003"
                            data-stock="30"
                            data-price="60">
                            Gold Bracelet
                        </option>

                        <option
                            value="Gold Earrings"
                            data-code="E004"
                            data-stock="15"
                            data-price="70">
                            Gold Earrings
                        </option>

                        <option
                            value="Silver Ring"
                            data-code="S005"
                            data-stock="25"
                            data-price="30">
                            Silver Ring
                        </option>

                    </select>

                </div>

            </div>


            <!-- Available Stock -->
            <div class="form-group">

                <label>Available Stock</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-layer-group"></i>

                    <input
                        type="number"
                        id="availableStock"
                        class="form-control"
                        value="0"
                        readonly>

                </div>

            </div>


            <!-- Quantity -->
            <div class="form-group">

                <label>Quantity</label>

                <div class="stock-out-input-wrapper">

                    <i class="fa-solid fa-arrow-down-1-9"></i>

                    <input
                        type="number"
                        id="outQuantity"
                        class="form-control"
                        value="1"
                        min="1">

                </div>

            </div>


            <!-- Add -->
            <div class="stock-out-add-wrapper">

                <button
                    type="button"
                    id="addStockOutBtn"
                    class="stock-out-add-btn">

                    <i class="fa-solid fa-cart-arrow-down"></i>

                    Add to List

                </button>

            </div>

        </div>



        <!-- =========================================
             PRODUCT TABLE
        ========================================== -->

        <div class="stock-out-table-wrapper">

            <table class="stock-out-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Product</th>

                        <th>Code</th>

                        <th>Available Stock</th>

                        <th>Quantity</th>

                        <th>Unit Price</th>

                        <th>Total</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody id="stockOutTableBody">

                    <!-- Product 1 -->
                    <tr
                        data-product="Gold Necklace"
                        data-code="N001"
                        data-stock="50"
                        data-quantity="5"
                        data-price="45">

                        <td>1</td>

                        <td>
                            <div class="stock-out-product-name">

                                <div class="stock-out-product-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>

                                <span>
                                    Gold Necklace
                                </span>

                            </div>
                        </td>

                        <td>N001</td>

                        <td>50</td>

                        <td>
                            <input
                                type="number"
                                class="stock-out-qty-input"
                                value="5"
                                min="1"
                                max="50">
                        </td>

                        <td>$45.00</td>

                        <td class="stock-out-total">
                            $225.00
                        </td>

                        <td>

                            <button
                                type="button"
                                class="stock-out-delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                        </td>

                    </tr>


                    <!-- Product 2 -->
                    <tr
                        data-product="Diamond Ring"
                        data-code="R002"
                        data-stock="20"
                        data-quantity="2"
                        data-price="80">

                        <td>2</td>

                        <td>
                            <div class="stock-out-product-name">

                                <div class="stock-out-product-icon">
                                    <i class="fa-solid fa-ring"></i>
                                </div>

                                <span>
                                    Diamond Ring
                                </span>

                            </div>
                        </td>

                        <td>R002</td>

                        <td>20</td>

                        <td>
                            <input
                                type="number"
                                class="stock-out-qty-input"
                                value="2"
                                min="1"
                                max="20">
                        </td>

                        <td>$80.00</td>

                        <td class="stock-out-total">
                            $160.00
                        </td>

                        <td>

                            <button
                                type="button"
                                class="stock-out-delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                        </td>

                    </tr>


                    <!-- Product 3 -->
                    <tr
                        data-product="Gold Bracelet"
                        data-code="B003"
                        data-stock="30"
                        data-quantity="3"
                        data-price="60">

                        <td>3</td>

                        <td>
                            <div class="stock-out-product-name">

                                <div class="stock-out-product-icon">
                                    <i class="fa-solid fa-circle-notch"></i>
                                </div>

                                <span>
                                    Gold Bracelet
                                </span>

                            </div>
                        </td>

                        <td>B003</td>

                        <td>30</td>

                        <td>
                            <input
                                type="number"
                                class="stock-out-qty-input"
                                value="3"
                                min="1"
                                max="30">
                        </td>

                        <td>$60.00</td>

                        <td class="stock-out-total">
                            $180.00
                        </td>

                        <td>

                            <button
                                type="button"
                                class="stock-out-delete">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>



        <!-- =========================================
             SUMMARY
        ========================================== -->

        <div class="stock-out-summary">


            <!-- Total Items -->
            <div class="stock-out-summary-card">

                <div class="stock-out-summary-icon">

                    <i class="fa-solid fa-boxes-stacked"></i>

                </div>

                <div>

                    <span>Total Items</span>

                    <strong id="stockOutTotalItems">
                        3
                    </strong>

                    <small>
                        Different products
                    </small>

                </div>

            </div>


            <!-- Total Quantity -->
            <div class="stock-out-summary-card">

                <div class="stock-out-summary-icon">

                    <i class="fa-solid fa-cubes-stacked"></i>

                </div>

                <div>

                    <span>Total Quantity</span>

                    <strong id="stockOutTotalQuantity">
                        10
                    </strong>

                    <small>
                        Units removed
                    </small>

                </div>

            </div>


            <!-- Grand Total -->
            <div class="stock-out-summary-card">

                <div class="stock-out-summary-icon">

                    <i class="fa-solid fa-coins"></i>

                </div>

                <div>

                    <span>Grand Total</span>

                    <strong id="stockOutGrandTotal">
                        $565.00
                    </strong>

                    <small>
                        Total value
                    </small>

                </div>

            </div>


        </div>



        <!-- =========================================
             ACTION BUTTONS
        ========================================== -->

        <div class="stock-out-actions">

            <button
                type="button"
                id="cancelStockOutBtn"
                class="stock-out-cancel-btn">

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </button>


            <button
                type="button"
                id="saveStockOutBtn"
                class="stock-out-save-btn">

                <i class="fa-solid fa-check-double"></i>

                Save Stock Out

            </button>

        </div>

    </section>

</div>


<script src="/Jewelry-app/inventory/assets/js/inventory.js"></script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>