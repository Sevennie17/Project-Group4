<?php
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="assets/css/sales.css">

<style>
    /* ========================================
   INVOICE PAGE
======================================== */

    .invoice-page {
        padding: var(--spacing-xl);
    }

    .invoice-card {
        max-width: 1000px;
        margin: 0 auto;

        background: var(--primary-color);

        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-lg);

        box-shadow: var(--shadow-sm);

        padding: var(--spacing-xl);
    }


    /* ========================================
   TOP
======================================== */

    .invoice-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;

        padding-bottom: var(--spacing-lg);

        border-bottom: 1px solid var(--border-color);
    }

    .invoice-company h1 {
        font-size: 24px;
        color: var(--text-color);
    }

    .invoice-company p {
        color: var(--text-muted);
        font-size: 13px;
    }

    .invoice-title {
        text-align: right;
    }

    .invoice-title h2 {
        color: var(--gold-color);
        font-size: 28px;
        margin-bottom: 5px;
    }

    .invoice-title p {
        color: var(--text-muted);
        font-size: 13px;
    }


    /* ========================================
   INFORMATION
======================================== */

    .invoice-info {
        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: var(--spacing-xl);

        padding: var(--spacing-lg) 0;
    }

    .invoice-info h3 {
        font-size: 14px;
        margin-bottom: 7px;
    }

    .invoice-info p {
        color: var(--text-muted);
        font-size: 13px;
        margin-bottom: 2px;
    }


    /* ========================================
   TABLE
======================================== */

    .invoice-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .invoice-table {
        width: 100%;

        border-collapse: collapse;

        margin-bottom: var(--spacing-lg);
    }

    .invoice-table th {
        padding: 12px;

        background: var(--active-color);

        color: var(--text-color);

        font-size: 13px;

        text-align: left;
    }

    .invoice-table td {
        padding: 12px;

        border-bottom: 1px solid var(--border-color);

        font-size: 13px;
    }

    .invoice-table th:last-child,
    .invoice-table td:last-child {
        text-align: right;
    }

    .invoice-table .item-total {
        font-weight: 600;
    }


    /* ========================================
   SUMMARY
======================================== */

    .invoice-summary {
        width: 330px;

        margin-left: auto;
    }

    .summary-line {
        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 6px 0;

        font-size: 13px;
    }

    .summary-line span {
        color: var(--text-muted);
    }

    .summary-line strong {
        color: var(--text-color);
    }


    /* TOTAL */

    .invoice-total {
        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-top: 8px;

        padding: 13px;

        background: var(--active-color);

        border-radius: var(--border-radius-sm);

        color: var(--gold-color);

        font-size: 18px;

        font-weight: 700;
    }


    /* ========================================
   PAYMENT INFO
======================================== */

    .payment-info {
        margin-top: var(--spacing-lg);

        padding: 14px;

        background: var(--secondary-color);

        border-radius: var(--border-radius-sm);

        font-size: 13px;
    }

    .payment-info strong {
        color: var(--gold-color);
    }


    /* ========================================
   FOOTER
======================================== */

    .invoice-footer {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: var(--spacing-lg);

        margin-top: var(--spacing-xl);

        padding-top: var(--spacing-lg);

        border-top: 1px solid var(--border-color);
    }

    .invoice-footer p {
        color: var(--text-muted);

        font-size: 12px;
    }

    .invoice-actions {
        display: flex;

        gap: 8px;
    }


    /* ========================================
   BUTTONS
======================================== */

    .invoice-actions button,
    .invoice-actions a {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        padding: 10px 16px;

        border-radius: var(--border-radius-sm);

        font-size: 13px;

        font-weight: 600;

        cursor: pointer;

        transition: var(--transition);
    }

    .btn-back {
        border: 1px solid var(--border-color);

        background: var(--primary-color);

        color: var(--text-color);
    }

    .btn-back:hover {
        border-color: var(--gold-color);

        color: var(--gold-color);
    }

    .btn-print {
        border: 1px solid var(--gold-color);

        background: var(--gold-color);

        color: var(--primary-color);
    }

    .btn-print:hover {
        background: #b18d1f;
    }


    /* ========================================
   EMPTY / ERROR
======================================== */

    .invoice-error {
        max-width: 600px;

        margin: 80px auto;

        padding: 30px;

        text-align: center;

        background: var(--primary-color);

        border: 1px solid var(--border-color);

        border-radius: var(--border-radius-lg);

        box-shadow: var(--shadow-sm);
    }

    .invoice-error i {
        color: var(--gold-color);

        font-size: 35px;

        margin-bottom: 15px;
    }

    .invoice-error h2 {
        margin-bottom: 8px;
    }

    .invoice-error p {
        color: var(--text-muted);

        font-size: 14px;

        margin-bottom: 20px;
    }


    /* ========================================
   RESPONSIVE
======================================== */

    @media (max-width: 700px) {

        .invoice-page {
            padding: var(--spacing-md);
        }

        .invoice-card {
            padding: var(--spacing-lg);
        }

        .invoice-top {
            flex-direction: column;

            gap: var(--spacing-md);
        }

        .invoice-title {
            text-align: left;
        }

        .invoice-info {
            grid-template-columns: 1fr;

            gap: var(--spacing-md);
        }

        .invoice-summary {
            width: 100%;
        }

        .invoice-footer {
            flex-direction: column;

            align-items: flex-start;
        }

        .invoice-actions {
            width: 100%;
        }

        .invoice-actions a,
        .invoice-actions button {
            flex: 1;
        }

    }


    /* ========================================
   PRINT
======================================== */

    @media print {

        body {
            background: #ffffff;
        }

        .invoice-page {
            padding: 0;
        }

        .invoice-card {
            max-width: 100%;

            padding: 0;

            border: none;

            box-shadow: none;
        }

        .invoice-actions {
            display: none;
        }

    }
</style>


<div class="invoice-page">

    <div id="invoiceContent">

        <!-- JavaScript will create invoice here -->

    </div>

</div>


<script>
    document.addEventListener("DOMContentLoaded", function() {

        /* ========================================
           GET INVOICE DATA
        ======================================== */

        const savedInvoice =
            localStorage.getItem("currentInvoice");


        const container =
            document.getElementById("invoiceContent");


        /* ========================================
           CHECK DATA
        ======================================== */

        if (!savedInvoice) {

            container.innerHTML = `

            <div class="invoice-error">

                <i class="fa-solid fa-file-invoice"></i>

                <h2>No Invoice Found</h2>

                <p>
                    There is no invoice data available.
                    Please create a sale first.
                </p>

                <a
                    href="index.php"
                    class="btn-print"
                    style="
                        display:inline-flex;
                        text-decoration:none;
                    "
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Sales
                </a>

            </div>

        `;

            return;
        }


        /* ========================================
           PARSE DATA
        ======================================== */

        let invoice;

        try {

            invoice =
                JSON.parse(savedInvoice);

        } catch (error) {

            container.innerHTML = `

            <div class="invoice-error">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <h2>Invalid Invoice Data</h2>

                <p>
                    The invoice data could not be loaded.
                </p>

                <a
                    href="index.php"
                    class="btn-print"
                    style="
                        display:inline-flex;
                        text-decoration:none;
                    "
                >
                    Back to Sales
                </a>

            </div>

        `;

            return;
        }


        /* ========================================
           FORMAT MONEY
        ======================================== */

        function money(amount) {

            return "$" +
                Number(amount || 0)
                .toFixed(2);

        }


        /* ========================================
           PRODUCTS
        ======================================== */

        let productRows = "";


        if (
            invoice.items &&
            invoice.items.length > 0
        ) {

            invoice.items.forEach(
                function(item, index) {

                    productRows += `

                    <tr>

                        <td>
                            ${index + 1}
                        </td>

                        <td>
                            ${item.name}
                        </td>

                        <td>
                            ${item.sku}
                        </td>

                        <td>
                            ${item.quantity}
                        </td>

                        <td>
                            ${money(item.price)}
                        </td>

                        <td class="item-total">
                            ${money(item.itemTotal)}
                        </td>

                    </tr>

                `;

                }
            );

        } else {

            productRows = `

            <tr>

                <td colspan="6"
                    style="text-align:center;">

                    No products

                </td>

            </tr>

        `;

        }


        /* ========================================
           CREATE INVOICE
        ======================================== */

        container.innerHTML = `

        <div class="invoice-card">


            <!-- HEADER -->

            <div class="invoice-top">

                <div class="invoice-company">

                    <h1>
                        ANAJAK JEWELRY
                    </h1>

                    <p>
                        Jewelry Shop Management System
                    </p>

                    <p>
                        Phnom Penh, Cambodia
                    </p>

                </div>


                <div class="invoice-title">

                    <h2>
                        INVOICE
                    </h2>

                    <p>
                        Invoice No:
                        ${invoice.invoiceNumber}
                    </p>

                    <p>
                        Date:
                        ${invoice.date}
                    </p>

                </div>

            </div>


            <!-- CUSTOMER INFO -->

            <div class="invoice-info">
                <div>
                    <h3>Bill To</h3>
                    <p>${invoice.customer}</p>
                    <p>Phone: ${invoice.phone || '-'}</p>
                    <p>Address: ${invoice.address || '-'}</p>
                </div>

                <div>
                    <h3>Sales Information</h3>
                    <p>Sales Person: Admin</p>
                    <p>Payment:${invoice.payment}</p>
                    <p>Status: ${invoice.status || "Pending"}</p>
                </div>
            </div>


            <!-- PRODUCTS -->

            <div class="invoice-table-wrapper">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th> Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>

                        ${productRows}

                    </tbody>

                </table>

            </div>


            <!-- SUMMARY -->

            <div class="invoice-summary">


                <div class="summary-line">

                    <span>
                        Subtotal
                    </span>

                    <strong>
                        ${money(invoice.subtotal)}
                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Discount
                    </span>

                    <strong>
                        -${money(invoice.discount)}
                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Membership
                    </span>

                    <strong>
                        -${money(invoice.membershipDiscount)}
                    </strong>

                </div>


                <div class="summary-line">

                    <span>
                        Tax (8%)
                    </span>

                    <strong>
                        ${money(invoice.tax)}
                    </strong>

                </div>


                <div class="invoice-total">

                    <span>
                        Total
                    </span>

                    <strong>
                        ${money(invoice.total)}
                    </strong>

                </div>


            </div>


            <!-- PAYMENT -->

            <div class="payment-info">

                Payment Method:

                <strong>
                    ${invoice.payment}
                </strong>

            </div>


            <!-- FOOTER -->

            <div class="invoice-footer">

                <p>
                    Thank you for shopping with
                    ANAJAK JEWELRY.
                </p>


                <div class="invoice-actions">

                    <a
                        href="index.php"
                        class="btn-back"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to Sales
                    </a>


                   ${
                    invoice.status === "Preview" || !invoice.saleID
                        ? `<button
                            type="button"
                            class="btn-print"
                            onclick="alert('This is only an invoice preview. Please complete payment before opening the receipt.');"
                        >
                            <i class="fa-solid fa-receipt"></i>
                            Receipt
                        </button>`
                        : `<a
                            href="receipt.php?sale_id=${encodeURIComponent(invoice.saleID)}"
                            class="btn-print"
                            style="text-decoration:none;"
                        >
                            <i class="fa-solid fa-receipt"></i>
                            Receipt
                        </a>`
                }

                        <button
                            class="btn-print"
                            onclick="window.print()"
                        >
                            <i class="fa-solid fa-print"></i>
                            Print Invoice
                        </button>

                </div>

            </div>


        </div>

    `;

    });
</script>


<?php
include __DIR__ . '/../includes/footer.php';
?>