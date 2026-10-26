<?php
include __DIR__ . '/../../includes/header.php';
?>

<link rel="stylesheet" href="/Jewelry-app/inventory/assets/css/inventory.css?v=2">

<div class="barcode-page">

    <!-- ================= BREADCRUMB ================= -->
    <div class="barcode-breadcrumb">
        <a href="/Jewelry-app/dashboards/">
            <i class="fa-solid fa-house"></i>
            Home
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <a href="/Jewelry_Shop/inventory/">
            Inventory
        </a>

        <i class="fa-solid fa-chevron-right"></i>

        <strong>Barcode</strong>
    </div>


    <!-- ================= PAGE HEADER ================= -->
    <div class="barcode-page-header">

        <div class="barcode-title-wrapper">

            <div class="barcode-title-icon">
                <i class="fa-solid fa-barcode"></i>
            </div>

            <div>
                <h1>Barcode Management</h1>
                <p>Generate, manage and print product barcodes.</p>
            </div>

        </div>

        <a
            href="/Jewelry_Shop/inventory/"
            class="barcode-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Inventory
        </a>

    </div>


    <!-- =================================================
         TOP AREA
    ================================================== -->

    <div class="barcode-top-grid">


        <!-- SEARCH -->
        <section class="barcode-card">

            <div class="barcode-card-title">

                <div class="barcode-card-icon">
                    <i class="fa-solid fa-qrcode"></i>
                </div>

                <div>
                    <h2>Search / Scan Barcode</h2>
                    <p>
                        Scan a barcode or enter barcode number to find product.
                    </p>
                </div>

            </div>


            <div class="barcode-search-row">

                <div class="barcode-input">

                    <div class="barcode-input-icon">
                        <i class="fa-solid fa-barcode"></i>
                    </div>

                    <input
                        type="text"
                        id="barcodeSearchInput"
                        placeholder="Enter or scan barcode number...">

                </div>


                <button
                    type="button"
                    id="barcodeSearchBtn"
                    class="barcode-btn barcode-btn-gold">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Search
                </button>

            </div>

        </section>



        <!-- GENERATE -->
        <section class="barcode-card">

            <div class="barcode-card-title">

                <div class="barcode-card-icon">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>

                <div>
                    <h2>Generate New Barcode</h2>
                    <p>
                        Select a product to generate and print a new barcode.
                    </p>
                </div>

            </div>


            <div class="barcode-generate-row">


                <div class="barcode-form-item">

                    <label>Product</label>

                    <div class="barcode-input">

                        <div class="barcode-input-icon">
                            <i class="fa-solid fa-gem"></i>
                        </div>

                        <select id="barcodeProduct">

                            <option value="">
                                Select Product
                            </option>

                            <option
                                value="Gold Necklace"
                                data-code="GN001"
                                data-category="Necklace"
                                data-brand="ANAJAK"
                                data-purity="18K"
                                data-weight="5.25"
                                data-price="450.00"
                                data-stock="25">
                                Gold Necklace
                            </option>

                            <option
                                value="Diamond Ring"
                                data-code="DR002"
                                data-category="Ring"
                                data-brand="ANAJAK"
                                data-purity="18K"
                                data-weight="3.10"
                                data-price="850.00"
                                data-stock="12">
                                Diamond Ring
                            </option>

                            <option
                                value="Gold Earrings"
                                data-code="GE003"
                                data-category="Earrings"
                                data-brand="ANAJAK"
                                data-purity="18K"
                                data-weight="4.80"
                                data-price="320.00"
                                data-stock="40">
                                Gold Earrings
                            </option>

                            <option
                                value="Silver Bracelet"
                                data-code="SB004"
                                data-category="Bracelet"
                                data-brand="ANAJAK"
                                data-purity="925"
                                data-weight="6.20"
                                data-price="280.00"
                                data-stock="15">
                                Silver Bracelet
                            </option>

                            <option
                                value="Ruby Pendant"
                                data-code="RP005"
                                data-category="Pendant"
                                data-brand="ANAJAK"
                                data-purity="18K"
                                data-weight="2.90"
                                data-price="520.00"
                                data-stock="8">
                                Ruby Pendant
                            </option>

                        </select>

                    </div>

                </div>



                <div class="barcode-form-item">

                    <label>Quantity</label>

                    <div class="barcode-input">

                        <div class="barcode-input-icon">
                            <i class="fa-solid fa-hashtag"></i>
                        </div>

                        <input
                            type="number"
                            id="barcodeQuantity"
                            value="1"
                            min="1">

                    </div>

                </div>

            </div>


            <div class="barcode-generate-buttons">

                <button
                    type="button"
                    id="generateBarcodeBtn"
                    class="barcode-btn barcode-btn-light">
                    <i class="fa-solid fa-gears"></i>
                    Generate Barcode
                </button>


                <button
                    type="button"
                    id="topPrintBarcodeBtn"
                    class="barcode-btn barcode-btn-gold">
                    <i class="fa-solid fa-print"></i>
                    Print Barcode
                </button>

            </div>

        </section>

    </div>



    <!-- =================================================
         PRODUCT + PREVIEW
    ================================================== -->

    <div class="barcode-main-grid">


        <!-- ================= PRODUCT INFORMATION ================= -->

        <section class="barcode-card">

            <div class="barcode-card-title">

                <div class="barcode-card-icon">
                    <i class="fa-solid fa-box-open"></i>
                </div>

                <div>
                    <h2>Product Information</h2>
                    <p>
                        Product details will be shown here.
                    </p>
                </div>

            </div>


            <div class="barcode-product-layout">


                <!-- IMAGE -->

                <div class="barcode-product-photo">

                    <div
                        class="barcode-product-image"
                        id="productImageBox">
                        <i
                            id="productImageIcon"
                            class="fa-solid fa-gem"></i>
                    </div>

                </div>



                <!-- INFORMATION -->

                <div class="barcode-product-details">


                    <div class="barcode-detail">

                        <span>Product Name</span>

                        <strong id="detailProductName">
                            Gold Necklace
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Product Code</span>

                        <strong id="detailProductCode">
                            GN001
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Barcode</span>

                        <strong id="detailBarcode">
                            8901234567890
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Category</span>

                        <strong id="detailCategory">
                            Necklace
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Brand</span>

                        <strong id="detailBrand">
                            ANAJAK
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Current Stock</span>

                        <strong id="detailStock">
                            25 pcs
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Price</span>

                        <strong id="detailPrice">
                            $450.00
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Gold Weight</span>

                        <strong id="detailWeight">
                            5.25 g
                        </strong>

                    </div>


                    <div class="barcode-detail">

                        <span>Status</span>

                        <strong
                            id="detailStatus"
                            class="barcode-status-good">
                            In Stock
                        </strong>

                    </div>


                    <div class="barcode-product-actions">

                        <button
                            type="button"
                            id="editBarcodeBtn"
                            class="barcode-btn barcode-btn-light">
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Item
                        </button>


                        <button
                            type="button"
                            id="historyBarcodeBtn"
                            class="barcode-btn barcode-btn-light">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            View History
                        </button>

                    </div>

                </div>

            </div>

        </section>



        <!-- ================= BARCODE PREVIEW ================= -->

        <section class="barcode-card">

            <div class="barcode-card-title">

                <div class="barcode-card-icon">
                    <i class="fa-solid fa-tags"></i>
                </div>

                <div>
                    <h2>Barcode Preview</h2>
                    <p>
                        This is how the barcode will look.
                    </p>
                </div>

            </div>


            <div class="barcode-preview-box">

                <div class="barcode-preview-brand">

                    <i class="fa-solid fa-gem"></i>

                    <strong>
                        ANAJAK JEWELRY
                    </strong>

                </div>


                <small>
                    Jewelry Inventory
                </small>


                <div
                    id="barcodeVisual"
                    class="barcode-visual">

                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>

                </div>


                <strong
                    id="previewBarcodeNumber"
                    class="preview-barcode-number">
                    8901234567890
                </strong>


                <strong
                    id="previewProductName"
                    class="preview-product-name">
                    Gold Necklace
                </strong>


                <div class="preview-product-info">

                    <span id="previewWeight">
                        5.25 g
                    </span>

                    <span>•</span>

                    <span id="previewPrice">
                        $450.00
                    </span>

                </div>

            </div>


            <div class="barcode-preview-buttons">

                <button
                    type="button"
                    id="downloadBarcodeBtn"
                    class="barcode-btn barcode-btn-light">
                    <i class="fa-solid fa-file-arrow-down"></i>
                    Download
                </button>


                <button
                    type="button"
                    id="previewPrintBtn"
                    class="barcode-btn barcode-btn-gold">
                    <i class="fa-solid fa-print"></i>
                    Print
                </button>

            </div>

        </section>

    </div>



    <!-- =================================================
         BARCODE LIST
    ================================================== -->

    <section class="barcode-card barcode-list-card">

        <div class="barcode-list-header">


            <div class="barcode-card-title">

                <div class="barcode-card-icon">
                    <i class="fa-solid fa-list-check"></i>
                </div>

                <div>
                    <h2>Barcode List</h2>
                    <p>
                        List of all generated barcodes.
                    </p>
                </div>

            </div>


            <div class="barcode-table-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="barcodeTableSearch"
                    placeholder="Search product or barcode...">

            </div>

        </div>



        <div class="barcode-table-wrapper">

            <table class="barcode-table">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Barcode</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Gold Weight</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody id="barcodeTableBody">


                    <!-- GOLD NECKLACE -->

                    <tr>

                        <td>1</td>

                        <td>

                            <div class="table-product">

                                <div class="table-product-icon necklace-icon">
                                    <i class="fa-solid fa-link"></i>
                                </div>

                                <span>
                                    Gold Necklace
                                </span>

                            </div>

                        </td>

                        <td>GN001</td>

                        <td class="table-barcode">
                            8901234567890
                        </td>

                        <td>Necklace</td>

                        <td>$450.00</td>

                        <td>5.25 g</td>

                        <td>25</td>

                        <td>
                            <span class="table-status in-stock">
                                In Stock
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <button
                                    class="table-print"
                                    title="Print">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <button
                                    class="table-edit"
                                    title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button
                                    class="table-delete"
                                    title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </div>

                        </td>

                    </tr>



                    <!-- DIAMOND RING -->

                    <tr>

                        <td>2</td>

                        <td>

                            <div class="table-product">

                                <div class="table-product-icon ring-icon">
                                    <i class="fa-solid fa-ring"></i>
                                </div>

                                <span>
                                    Diamond Ring
                                </span>

                            </div>

                        </td>

                        <td>DR002</td>

                        <td class="table-barcode">
                            8901234567891
                        </td>

                        <td>Ring</td>

                        <td>$850.00</td>

                        <td>3.10 g</td>

                        <td>12</td>

                        <td>
                            <span class="table-status in-stock">
                                In Stock
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <button class="table-print">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <button class="table-edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button class="table-delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </div>

                        </td>

                    </tr>



                    <!-- EARRINGS -->

                    <tr>

                        <td>3</td>

                        <td>

                            <div class="table-product">

                                <div class="table-product-icon earrings-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>

                                <span>
                                    Gold Earrings
                                </span>

                            </div>

                        </td>

                        <td>GE003</td>

                        <td class="table-barcode">
                            8901234567892
                        </td>

                        <td>Earrings</td>

                        <td>$320.00</td>

                        <td>4.80 g</td>

                        <td>40</td>

                        <td>
                            <span class="table-status in-stock">
                                In Stock
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <button class="table-print">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <button class="table-edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button class="table-delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </div>

                        </td>

                    </tr>



                    <!-- BRACELET -->

                    <tr>

                        <td>4</td>

                        <td>

                            <div class="table-product">

                                <div class="table-product-icon bracelet-icon">
                                    <i class="fa-solid fa-circle-notch"></i>
                                </div>

                                <span>
                                    Silver Bracelet
                                </span>

                            </div>

                        </td>

                        <td>SB004</td>

                        <td class="table-barcode">
                            8901234567893
                        </td>

                        <td>Bracelet</td>

                        <td>$280.00</td>

                        <td>6.20 g</td>

                        <td>15</td>

                        <td>
                            <span class="table-status low-stock">
                                Low Stock
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <button class="table-print">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <button class="table-edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button class="table-delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </div>

                        </td>

                    </tr>



                    <!-- PENDANT -->

                    <tr>

                        <td>5</td>

                        <td>

                            <div class="table-product">

                                <div class="table-product-icon pendant-icon">
                                    <i class="fa-solid fa-gem"></i>
                                </div>

                                <span>
                                    Ruby Pendant
                                </span>

                            </div>

                        </td>

                        <td>RP005</td>

                        <td class="table-barcode">
                            8901234567894
                        </td>

                        <td>Pendant</td>

                        <td>$520.00</td>

                        <td>2.90 g</td>

                        <td>8</td>

                        <td>
                            <span class="table-status in-stock">
                                In Stock
                            </span>
                        </td>

                        <td>

                            <div class="table-actions">

                                <button class="table-print">
                                    <i class="fa-solid fa-print"></i>
                                </button>

                                <button class="table-edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <button class="table-delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <div class="barcode-list-footer">

            <span>
                Showing 1 to 5 of 5 entries
            </span>

            <div class="barcode-pagination">

                <button>
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <button class="active">
                    1
                </button>

                <button>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>

            </div>

        </div>

    </section>

</div>


<script src="/Jewelry-app/inventory/assets/js/inventory.js"></script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>