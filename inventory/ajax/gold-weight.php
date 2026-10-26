<?php
include __DIR__ . '/../../includes/header.php';
?>

<link rel="stylesheet" href="/Jewelry-app/inventory/assets/css/inventory.css">

<div class="stock-in-page gold-weight-page">

    <!-- BREADCRUMB -->
    <div class="stock-breadcrumb">
        <a href="/Jewelry-app/dashboards/">Home</a>

        <i class="fa-solid fa-chevron-right"></i>

        <a href="/Jewelry-app/inventory/">Inventory</a>

        <i class="fa-solid fa-chevron-right"></i>

        <strong>Gold Weight</strong>
    </div>


    <!-- PAGE HEADER -->
    <div class="stock-page-header">

        <div class="stock-title-wrapper">

            <div class="stock-title-icon gold-weight-icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>

            <div>
                <h1>Gold Weight</h1>

                <p>
                    Manage and track gold weight of jewelry products.
                </p>
            </div>

        </div>


        <a
            href="/Jewelry_Shop/inventory/"
            class="back-inventory-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Inventory
        </a>

    </div>


    <!-- GOLD WEIGHT INFORMATION -->
    <section class="stock-card">

        <div class="stock-card-header">

            <div class="section-icon gold-section-icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>

            <div>
                <h2>Gold Weight Information</h2>

                <p>
                    Enter and update gold weight information.
                </p>
            </div>

        </div>


        <div class="gold-weight-info-grid">


            <!-- PRODUCT -->
            <div class="form-group">

                <label>
                    Product
                    <span class="required-star">*</span>
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-gem"></i>

                    <select
                        id="goldProduct"
                        class="form-control">

                        <option value="">
                            Select Product
                        </option>

                        <option
                            value="Diamond Ring"
                            data-code="DR001"
                            data-purity="18K"
                            data-weight="3.50">
                            Diamond Ring
                        </option>

                        <option
                            value="Gold Necklace"
                            data-code="GN002"
                            data-purity="18K"
                            data-weight="5.20">
                            Gold Necklace
                        </option>

                        <option
                            value="Gold Earrings"
                            data-code="GE003"
                            data-purity="22K"
                            data-weight="4.80">
                            Gold Earrings
                        </option>

                        <option
                            value="Gold Bracelet"
                            data-code="GB004"
                            data-purity="24K"
                            data-weight="8.10">
                            Gold Bracelet
                        </option>

                        <option
                            value="Gold Pendant"
                            data-code="GP005"
                            data-purity="18K"
                            data-weight="2.30">
                            Gold Pendant
                        </option>

                    </select>

                </div>

            </div>


            <!-- PRODUCT CODE -->
            <div class="form-group">

                <label>
                    Product Code
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-barcode"></i>

                    <input
                        type="text"
                        id="goldProductCode"
                        class="form-control"
                        placeholder="Auto-filled"
                        readonly>

                </div>

            </div>


            <!-- PURITY -->
            <div class="form-group">

                <label>
                    Gold Purity
                    <span class="required-star">*</span>
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-gem"></i>

                    <select
                        id="goldPurity"
                        class="form-control">

                        <option value="18K">18K</option>
                        <option value="22K">22K</option>
                        <option value="24K">24K</option>

                    </select>

                </div>

            </div>


            <!-- CURRENT WEIGHT -->
            <div class="form-group">

                <label>
                    Current Gold Weight
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-weight-scale"></i>

                    <input
                        type="text"
                        id="goldCurrentWeight"
                        class="form-control"
                        value="0.00 g"
                        readonly>

                </div>

            </div>


            <!-- NEW WEIGHT -->
            <div class="form-group">

                <label>
                    New Gold Weight
                    <span class="required-star">*</span>
                </label>

                <div class="input-icon-wrapper gold-weight-input">

                    <i class="fa-solid fa-scale-balanced"></i>

                    <input
                        type="number"
                        id="goldNewWeight"
                        class="form-control"
                        placeholder="Enter weight"
                        min="0"
                        step="0.01">

                    <span class="weight-unit">
                        g
                    </span>

                </div>

            </div>


            <!-- ADJUSTMENT TYPE -->
            <div class="form-group">

                <label>
                    Adjustment Type
                    <span class="required-star">*</span>
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-arrows-rotate"></i>

                    <select
                        id="goldAdjustment"
                        class="form-control">

                        <option value="">
                            Select Adjustment
                        </option>

                        <option value="increase">
                            Increase
                        </option>

                        <option value="decrease">
                            Decrease
                        </option>

                        <option value="correction">
                            Correction
                        </option>

                    </select>

                </div>

            </div>


            <!-- NOTES -->
            <div class="form-group gold-notes">

                <label>
                    Notes
                </label>

                <div class="input-icon-wrapper">

                    <i class="fa-solid fa-note-sticky"></i>

                    <input
                        type="text"
                        id="goldNotes"
                        class="form-control"
                        placeholder="Enter notes...">

                </div>

            </div>

        </div>

    </section>


    <!-- GOLD WEIGHT SUMMARY -->
    <section class="stock-card">

        <div class="stock-card-header">

            <div class="section-icon gold-section-icon">
                <i class="fa-solid fa-chart-simple"></i>
            </div>

            <div>
                <h2>Gold Weight Summary</h2>

                <p>
                    Current gold weight overview.
                </p>
            </div>

        </div>


        <div class="gold-summary-grid">


            <div class="gold-summary-box">

                <div class="gold-summary-box-icon">
                    <i class="fa-solid fa-scale-balanced"></i>
                </div>

                <div>
                    <span>Total Gold Weight</span>
                    <strong>1,258.50 g</strong>
                </div>

            </div>


            <div class="gold-summary-box">

                <div class="gold-summary-box-icon">
                    <i class="fa-solid fa-coins"></i>
                </div>

                <div>
                    <span>Total Pure Gold</span>
                    <strong>1,037.20 g</strong>
                </div>

            </div>


            <div class="gold-summary-box">

                <div class="gold-summary-box-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>

                <div>
                    <span>Products With Gold</span>
                    <strong>128</strong>
                </div>

            </div>


            <div class="gold-summary-box">

                <div class="gold-summary-box-icon">
                    <i class="fa-solid fa-chart-line"></i>
                </div>

                <div>
                    <span>Average Weight</span>
                    <strong>9.83 g</strong>
                </div>

            </div>

        </div>

    </section>


    <!-- GOLD WEIGHT HISTORY -->
    <section class="stock-card gold-history-card">

        <div class="stock-card-header">

            <div class="section-icon gold-section-icon">

                <i class="fa-solid fa-clock-rotate-left"></i>

            </div>

            <div>

                <h2>Gold Weight History</h2>

                <p>
                    View previous gold weight adjustments.
                </p>

            </div>

        </div>


        <div class="gold-history-search">

            <div class="input-icon-wrapper">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="goldHistorySearch"
                    class="form-control"
                    placeholder="Search product or code...">

            </div>

        </div>


        <div class="table-wrapper">

            <table class="stock-product-table gold-history-table">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Purity</th>
                        <th>Old Weight</th>
                        <th>New Weight</th>
                        <th>Change</th>
                        <th>Adjusted By</th>
                        <th>Date</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody id="goldHistoryBody">


                    <tr>

                        <td>1</td>

                        <td>
                            <div class="gold-product-cell">

                                <div class="gold-product-icon">
                                    <i class="fa-solid fa-ring"></i>
                                </div>

                                <span>
                                    Diamond Ring
                                </span>

                            </div>
                        </td>

                        <td>DR001</td>

                        <td>
                            <span class="gold-purity purity-18">
                                18K
                            </span>
                        </td>

                        <td>3.50 g</td>

                        <td>3.75 g</td>

                        <td class="weight-increase">
                            +0.25 g
                        </td>

                        <td>Admin</td>

                        <td>15 Sep 2026</td>

                        <td>

                            <button
                                type="button"
                                class="gold-history-btn view">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button
                                type="button"
                                class="gold-history-btn edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button
                                type="button"
                                class="gold-history-btn delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                    <tr>

                        <td>2</td>

                        <td>
                            <div class="gold-product-cell">

                                <div class="gold-product-icon">
                                    <i class="fa-solid fa-link"></i>
                                </div>

                                <span>
                                    Gold Necklace
                                </span>

                            </div>
                        </td>

                        <td>GN002</td>

                        <td>
                            <span class="gold-purity purity-18">
                                18K
                            </span>
                        </td>

                        <td>5.20 g</td>

                        <td>5.50 g</td>

                        <td class="weight-increase">
                            +0.30 g
                        </td>

                        <td>Admin</td>

                        <td>14 Sep 2026</td>

                        <td>

                            <button class="gold-history-btn view">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button class="gold-history-btn edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button class="gold-history-btn delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                    <tr>

                        <td>3</td>

                        <td>
                            <div class="gold-product-cell">

                                <div class="gold-product-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>

                                <span>
                                    Gold Earrings
                                </span>

                            </div>
                        </td>

                        <td>GE003</td>

                        <td>
                            <span class="gold-purity purity-22">
                                22K
                            </span>
                        </td>

                        <td>4.80 g</td>

                        <td>4.60 g</td>

                        <td class="weight-decrease">
                            -0.20 g
                        </td>

                        <td>Admin</td>

                        <td>13 Sep 2026</td>

                        <td>

                            <button class="gold-history-btn view">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button class="gold-history-btn edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button class="gold-history-btn delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                        </td>

                    </tr>


                </tbody>

            </table>

        </div>

    </section>


    <!-- ACTION BUTTONS -->

    <div class="stock-actions">

        <button
            type="button"
            id="goldCancelBtn"
            class="cancel-btn">

            <i class="fa-solid fa-xmark"></i>

            Cancel

        </button>


        <button
            type="button"
            id="saveGoldWeightBtn"
            class="save-stock-btn">

            <i class="fa-solid fa-floppy-disk"></i>

            Save Gold Weight

        </button>

    </div>

</div>


<script src="/Jewelry-app/inventory/assets/js/inventory.js"></script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>