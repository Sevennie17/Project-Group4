<?php
include __DIR__ . '/../../includes/header.php';
?>

<link rel="stylesheet" href="/Jewelry-app/inventory/assets/css/inventory.css">

<div class="adjustment-page">

    <!-- =====================================================
         BREADCRUMB
    ====================================================== -->
    <div class="adjustment-breadcrumb">

        <a href="/Jewelrya-app/dashboards/">
            <i class="fa-solid fa-house"></i>
            Home
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <a href="/Jewelry_Shop/inventory/">
            Inventory
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <strong>Adjustment</strong>

    </div>


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->
    <div class="adjustment-page-header">

        <div class="adjustment-title-wrapper">

            <div class="adjustment-title-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>

            <div>
                <h1>Inventory Adjustment</h1>
                <p>
                    Adjust product quantities to correct inventory records.
                </p>
            </div>

        </div>

        <a
            href="/Jewelry_Shop/inventory/"
            class="back-inventory-btn adjustment-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Inventory
        </a>

    </div>


    <!-- =====================================================
         ADJUSTMENT INFORMATION
    ====================================================== -->
    <section class="adjustment-card">

        <div class="adjustment-card-header">

            <div class="adjustment-section-icon">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>

            <div>
                <h2>Adjustment Information</h2>
                <p>
                    Enter information about this adjustment transaction.
                </p>
            </div>

        </div>


        <div class="adjustment-info-grid">

            <!-- Adjustment Number -->
            <div class="form-group">

                <label>Adjustment No.</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-hashtag"></i>

                    <input
                        type="text"
                        id="adjustmentNo"
                        class="form-control"
                        value="ADJ-0001"
                        readonly>

                </div>

            </div>


            <!-- Date -->
            <div class="form-group">

                <label>Date</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-calendar-days"></i>

                    <input
                        type="date"
                        id="adjustmentDate"
                        class="form-control">

                </div>

            </div>


            <!-- Adjusted By -->
            <div class="form-group">

                <label>Adjusted By</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-user-gear"></i>

                    <select
                        id="adjustedBy"
                        class="form-control">

                        <option value="">Select User</option>
                        <option value="Admin" selected>Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Staff">Staff</option>

                    </select>

                </div>

            </div>


            <!-- Reference -->
            <div class="form-group">

                <label>Reference / Document</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-paperclip"></i>

                    <input
                        type="text"
                        id="adjustmentReference"
                        class="form-control"
                        placeholder="Enter reference or document number">

                </div>

            </div>


            <!-- Reason -->
            <div class="form-group">

                <label>Reason</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-tag"></i>

                    <select
                        id="adjustmentReason"
                        class="form-control">

                        <option value="">Select reason</option>

                        <option value="Stock Count Correction">
                            Stock Count Correction
                        </option>

                        <option value="Damaged Item">
                            Damaged Item
                        </option>

                        <option value="Found During Stock Count">
                            Found During Stock Count
                        </option>

                        <option value="Lost Item">
                            Lost Item
                        </option>

                        <option value="System Correction">
                            System Correction
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

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-note-sticky"></i>

                    <input
                        type="text"
                        id="adjustmentNotes"
                        class="form-control"
                        placeholder="Optional notes...">

                </div>

            </div>

        </div>

    </section>



    <!-- =====================================================
         ADJUST PRODUCTS
    ====================================================== -->
    <section class="adjustment-card">

        <div class="adjustment-card-header">

            <div class="adjustment-section-icon">
                <i class="fa-solid fa-gem"></i>
            </div>

            <div>

                <h2>Adjust Products</h2>

                <p>
                    Select products and enter adjustment details.
                </p>

            </div>

        </div>


        <!-- Product Input -->
        <div class="adjustment-product-grid">


            <!-- Product -->
            <div class="form-group">

                <label>Product</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-ring"></i>

                    <select
                        id="adjustmentProduct"
                        class="form-control">

                        <option value="">
                            Select Product
                        </option>

                        <option
                            value="Gold Necklace"
                            data-code="GN001"
                            data-stock="25">
                            Gold Necklace
                        </option>

                        <option
                            value="Diamond Ring"
                            data-code="DR002"
                            data-stock="12">
                            Diamond Ring
                        </option>

                        <option
                            value="Gold Earrings"
                            data-code="GE003"
                            data-stock="40">
                            Gold Earrings
                        </option>

                        <option
                            value="Silver Bracelet"
                            data-code="SB004"
                            data-stock="15">
                            Silver Bracelet
                        </option>

                        <option
                            value="Ruby Pendant"
                            data-code="RP005"
                            data-stock="8">
                            Ruby Pendant
                        </option>

                    </select>

                </div>

            </div>


            <!-- Current Stock -->
            <div class="form-group">

                <label>Current Stock</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-layer-group"></i>

                    <input
                        type="number"
                        id="adjustmentCurrentStock"
                        class="form-control"
                        value="0"
                        readonly>

                </div>

            </div>


            <!-- Adjustment Type -->
            <div class="form-group">

                <label>Adjustment Type</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-arrows-up-down"></i>

                    <select
                        id="adjustmentType"
                        class="form-control">

                        <option value="increase">
                            Increase (+)
                        </option>

                        <option value="decrease">
                            Decrease (-)
                        </option>

                    </select>

                </div>

            </div>


            <!-- Quantity -->
            <div class="form-group">

                <label>Quantity</label>

                <div class="adjustment-input-wrapper">

                    <i class="fa-solid fa-hashtag"></i>

                    <input
                        type="number"
                        id="adjustmentQuantity"
                        class="form-control"
                        value="1"
                        min="1">

                </div>

            </div>


            <!-- Add Button -->
            <div class="adjustment-add-wrapper">

                <button
                    type="button"
                    id="addAdjustmentBtn"
                    class="adjustment-add-btn">

                    <i class="fa-solid fa-plus"></i>

                    Add to List

                </button>

            </div>

        </div>



        <!-- =================================================
             ADJUSTMENT TABLE
        ================================================== -->
        <div class="adjustment-table-wrapper">

            <table class="adjustment-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Product</th>

                        <th>Code</th>

                        <th>Current Stock</th>

                        <th>Adjustment Type</th>

                        <th>Quantity</th>

                        <th>New Stock</th>

                        <th>Remarks</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody id="adjustmentTableBody">

                    <!-- ROW 1 -->
                    <tr
                        data-product="Gold Necklace"
                        data-code="GN001"
                        data-stock="25"
                        data-type="increase"
                        data-quantity="5">

                        <td>1</td>

                        <td>

                            <div class="adjustment-product-name">

                                <div class="adjustment-product-icon">
                                    <i class="fa-solid fa-link"></i>
                                </div>

                                <span>Gold Necklace</span>

                            </div>

                        </td>

                        <td>GN001</td>

                        <td>25</td>

                        <td>

                            <span class="adjustment-type increase">

                                <i class="fa-solid fa-arrow-up"></i>

                                Increase (+)

                            </span>

                        </td>

                        <td>5</td>

                        <td>30</td>

                        <td>Stock count correction</td>

                        <td>

                            <button
                                type="button"
                                class="adjustment-delete">

                                <i class="fa-solid fa-trash-can"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- ROW 2 -->
                    <tr
                        data-product="Diamond Ring"
                        data-code="DR002"
                        data-stock="12"
                        data-type="decrease"
                        data-quantity="2">

                        <td>2</td>

                        <td>

                            <div class="adjustment-product-name">

                                <div class="adjustment-product-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>

                                <span>Diamond Ring</span>

                            </div>

                        </td>

                        <td>DR002</td>

                        <td>12</td>

                        <td>

                            <span class="adjustment-type decrease">

                                <i class="fa-solid fa-arrow-down"></i>

                                Decrease (-)

                            </span>

                        </td>

                        <td>2</td>

                        <td>10</td>

                        <td>Damaged item</td>

                        <td>

                            <button
                                type="button"
                                class="adjustment-delete">

                                <i class="fa-solid fa-trash-can"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- ROW 3 -->
                    <tr
                        data-product="Gold Earrings"
                        data-code="GE003"
                        data-stock="40"
                        data-type="increase"
                        data-quantity="10">

                        <td>3</td>

                        <td>

                            <div class="adjustment-product-name">

                                <div class="adjustment-product-icon">
                                    <i class="fa-solid fa-ear-listen"></i>
                                </div>

                                <span>Gold Earrings</span>

                            </div>

                        </td>

                        <td>GE003</td>

                        <td>40</td>

                        <td>

                            <span class="adjustment-type increase">

                                <i class="fa-solid fa-arrow-up"></i>

                                Increase (+)

                            </span>

                        </td>

                        <td>10</td>

                        <td>50</td>

                        <td>New stock found</td>

                        <td>

                            <button
                                type="button"
                                class="adjustment-delete">

                                <i class="fa-solid fa-trash-can"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- ROW 4 -->
                    <tr
                        data-product="Silver Bracelet"
                        data-code="SB004"
                        data-stock="15"
                        data-type="decrease"
                        data-quantity="3">

                        <td>4</td>

                        <td>

                            <div class="adjustment-product-name">

                                <div class="adjustment-product-icon">
                                    <i class="fa-solid fa-circle-notch"></i>
                                </div>

                                <span>Silver Bracelet</span>

                            </div>

                        </td>

                        <td>SB004</td>

                        <td>15</td>

                        <td>

                            <span class="adjustment-type decrease">

                                <i class="fa-solid fa-arrow-down"></i>

                                Decrease (-)

                            </span>

                        </td>

                        <td>3</td>

                        <td>12</td>

                        <td>Sales return adjustment</td>

                        <td>

                            <button
                                type="button"
                                class="adjustment-delete">

                                <i class="fa-solid fa-trash-can"></i>

                            </button>

                        </td>

                    </tr>



                    <!-- ROW 5 -->
                    <tr
                        data-product="Ruby Pendant"
                        data-code="RP005"
                        data-stock="8"
                        data-type="increase"
                        data-quantity="4">

                        <td>5</td>

                        <td>

                            <div class="adjustment-product-name">

                                <div class="adjustment-product-icon">
                                    <i class="fa-solid fa-heart"></i>
                                </div>

                                <span>Ruby Pendant</span>

                            </div>

                        </td>

                        <td>RP005</td>

                        <td>8</td>

                        <td>

                            <span class="adjustment-type increase">

                                <i class="fa-solid fa-arrow-up"></i>

                                Increase (+)

                            </span>

                        </td>

                        <td>4</td>

                        <td>12</td>

                        <td>Found during stock count</td>

                        <td>

                            <button
                                type="button"
                                class="adjustment-delete">

                                <i class="fa-solid fa-trash-can"></i>

                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>



        <!-- =================================================
             SUMMARY
        ================================================== -->
        <div class="adjustment-summary">


            <!-- Total Items -->
            <div class="adjustment-summary-card">

                <div class="adjustment-summary-icon">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>

                <div>

                    <span>Total Items</span>

                    <strong id="adjustmentTotalItems">
                        5
                    </strong>

                    <small>
                        Different products
                    </small>

                </div>

            </div>



            <!-- Total Increase -->
            <div class="adjustment-summary-card increase-card">

                <div class="adjustment-summary-icon">
                    <i class="fa-solid fa-arrow-up"></i>
                </div>

                <div>

                    <span>Total Increase</span>

                    <strong id="adjustmentTotalIncrease">
                        19
                    </strong>

                    <small>
                        Units added
                    </small>

                </div>

            </div>



            <!-- Total Decrease -->
            <div class="adjustment-summary-card decrease-card">

                <div class="adjustment-summary-icon">
                    <i class="fa-solid fa-arrow-down"></i>
                </div>

                <div>

                    <span>Total Decrease</span>

                    <strong id="adjustmentTotalDecrease">
                        5
                    </strong>

                    <small>
                        Units removed
                    </small>

                </div>

            </div>



            <!-- Net Change -->
            <div class="adjustment-summary-card net-card">

                <div class="adjustment-summary-icon">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>

                <div>

                    <span>Net Change</span>

                    <strong id="adjustmentNetChange">
                        +14
                    </strong>

                    <small>
                        Units overall
                    </small>

                </div>

            </div>

        </div>



        <!-- =================================================
             ACTION BUTTONS
        ================================================== -->
        <div class="adjustment-actions">

            <button
                type="button"
                id="cancelAdjustmentBtn"
                class="adjustment-cancel-btn">

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </button>


            <button
                type="button"
                id="saveAdjustmentBtn"
                class="adjustment-save-btn">

                <i class="fa-solid fa-floppy-disk"></i>

                Save Adjustment

            </button>

        </div>


    </section>

</div>


<script src="/Jewelry-app/inventory/assets/js/inventory.js"></script>


<?php
include __DIR__ . '/../../includes/footer.php';
?>