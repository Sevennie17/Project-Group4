<?php
include __DIR__ . '/../includes/header.php';
?>

<style>
    /* ========================================
   RECEIPT PAGE
======================================== */

    .receipt-page {
        padding: 30px;
        display: flex;
        justify-content: center;
    }

    .receipt-wrapper {
        width: 100%;
        max-width: 430px;
    }


    /* ========================================
   RECEIPT
======================================== */

    .receipt {
        background: var(--primary-color);

        border: 1px solid var(--border-color);

        border-radius: var(--border-radius-lg);

        box-shadow: var(--shadow-sm);

        padding: 25px;
    }


    /* ========================================
   STORE HEADER
======================================== */

    .receipt-header {
        text-align: center;

        padding-bottom: 18px;

        border-bottom: 1px dashed var(--border-color);
    }

    .receipt-header h1 {
        font-size: 22px;

        letter-spacing: 1px;

        margin-bottom: 3px;
    }

    .receipt-header p {
        color: var(--text-muted);

        font-size: 12px;

        margin: 2px 0;
    }


    /* ========================================
   RECEIPT TITLE
======================================== */

    .receipt-title {
        text-align: center;

        padding: 15px 0;
    }

    .receipt-title h2 {
        font-size: 18px;

        color: var(--gold-color);

        letter-spacing: 1px;
    }


    /* ========================================
   INFO
======================================== */

    .receipt-info {
        padding-bottom: 15px;

        border-bottom: 1px dashed var(--border-color);
    }

    .receipt-info-row {
        display: flex;

        justify-content: space-between;

        gap: 15px;

        font-size: 12px;

        margin-bottom: 5px;
    }

    .receipt-info-row span:first-child {
        color: var(--text-muted);
    }

    .receipt-info-row span:last-child {
        font-weight: 600;

        text-align: right;
    }


    /* ========================================
   ITEMS
======================================== */

    .receipt-items {
        padding: 15px 0;

        border-bottom: 1px dashed var(--border-color);
    }

    .receipt-item {
        margin-bottom: 13px;
    }

    .receipt-item:last-child {
        margin-bottom: 0;
    }

    .receipt-item-top {
        display: flex;

        justify-content: space-between;

        gap: 10px;

        font-size: 13px;

        font-weight: 600;
    }

    .receipt-item-name {
        min-width: 0;
    }

    .receipt-item-price {
        white-space: nowrap;
    }

    .receipt-item-bottom {
        display: flex;

        justify-content: space-between;

        gap: 10px;

        color: var(--text-muted);

        font-size: 11px;

        margin-top: 2px;
    }


    /* ========================================
   SUMMARY
======================================== */

    .receipt-summary {
        padding: 15px 0;

        border-bottom: 1px dashed var(--border-color);
    }

    .receipt-summary-row {
        display: flex;

        justify-content: space-between;

        font-size: 12px;

        margin-bottom: 7px;
    }

    .receipt-summary-row span:first-child {
        color: var(--text-muted);
    }

    .receipt-total {
        display: flex;

        justify-content: space-between;

        align-items: center;

        margin-top: 10px;

        padding-top: 10px;

        border-top: 1px solid var(--border-color);

        font-size: 18px;

        font-weight: 700;

        color: var(--gold-color);
    }


    /* ========================================
   PAYMENT
======================================== */

    .receipt-payment {
        text-align: center;

        padding: 15px 0;

        border-bottom: 1px dashed var(--border-color);
    }

    .receipt-payment p {
        color: var(--text-muted);

        font-size: 12px;

        margin-bottom: 4px;
    }

    .receipt-payment strong {
        font-size: 14px;

        color: var(--text-color);
    }


    /* ========================================
   THANK YOU
======================================== */

    .receipt-thank-you {
        text-align: center;

        padding: 18px 0 5px;
    }

    .receipt-thank-you p {
        font-size: 12px;

        color: var(--text-muted);
    }

    .receipt-thank-you strong {
        display: block;

        margin-top: 4px;

        font-size: 13px;

        color: var(--gold-color);
    }


    /* ========================================
   ACTIONS
======================================== */

    .receipt-actions {
        display: flex;

        justify-content: center;

        gap: 8px;

        margin-top: 18px;
    }

    .receipt-actions a,
    .receipt-actions button {
        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 6px;

        padding: 10px 15px;

        border-radius: var(--border-radius-sm);

        font-size: 13px;

        font-weight: 600;

        cursor: pointer;

        transition: var(--transition);
    }

    .receipt-back {
        border: 1px solid var(--border-color);

        background: var(--primary-color);

        color: var(--text-color);
    }

    .receipt-back:hover {
        border-color: var(--gold-color);

        color: var(--gold-color);
    }

    .receipt-print {
        border: 1px solid var(--gold-color);

        background: var(--gold-color);

        color: var(--primary-color);
    }

    .receipt-print:hover {
        background: #b18d1f;
    }


    /* ========================================
   ERROR
======================================== */

    .receipt-error {
        text-align: center;

        background: var(--primary-color);

        border: 1px solid var(--border-color);

        border-radius: var(--border-radius-lg);

        box-shadow: var(--shadow-sm);

        padding: 30px;
    }

    .receipt-error i {
        color: var(--gold-color);

        font-size: 35px;

        margin-bottom: 12px;
    }

    .receipt-error h2 {
        font-size: 20px;

        margin-bottom: 7px;
    }

    .receipt-error p {
        color: var(--text-muted);

        font-size: 13px;

        margin-bottom: 18px;
    }


    /* ========================================
   MOBILE
======================================== */

    @media (max-width: 500px) {

        .receipt-page {
            padding: 15px;
        }

        .receipt {
            padding: 20px;
        }

        .receipt-actions {
            flex-direction: column;
        }

        .receipt-actions a,
        .receipt-actions button {
            width: 100%;
        }

    }


    /* ========================================
   PRINT
======================================== */

    @media print {

        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        body {
            background: #ffffff;
        }

        header,
        nav,
        aside,
        .sidebar,
        .receipt-actions {
            display: none !important;
        }

        .receipt-page {
            display: block;

            padding: 0;

            margin: 0;
        }

        .receipt-wrapper {
            max-width: 100%;
        }

        .receipt {
            width: 100%;

            padding: 0;

            border: none;

            border-radius: 0;

            box-shadow: none;
        }

    }
</style>


<div class="receipt-page">

    <div class="receipt-wrapper">

        <div id="receiptContent">
            <!-- JavaScript will load receipt -->
        </div>

    </div>

</div>


<script>
    document.addEventListener("DOMContentLoaded", async function() {
        const container = document.getElementById("receiptContent");

        const params = new URLSearchParams(window.location.search);
        const saleID = params.get("sale_id");

        if (!saleID) {
            container.innerHTML = `
            <div class="receipt-error">
                <i class="fa-solid fa-receipt"></i>
                <h2>No Receipt Found</h2>
                <p>Sale ID is missing. Please open a completed sale.</p>
                <a href="index.php" class="receipt-print"
                   style="display:inline-flex;text-decoration:none">
                    Back to Sales
                </a>
            </div>`;
            return;
        }

        try {
            const response = await fetch(
                `ajax/get-invoice.php?sale_id=${encodeURIComponent(saleID)}`, {
                    headers: {
                        Accept: "application/json"
                    }
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Unable to load receipt.");
            }

            const invoice = result.invoice;

            // កូដបង្ហាញ Receipt ចាស់របស់អូនបន្តនៅទីនេះ



            /* ========================================
               MONEY FORMAT
            ======================================== */

            function money(amount) {

                return "$" +
                    Number(amount || 0).toFixed(2);

            }


            /* ========================================
               ITEMS
            ======================================== */

            let itemsHTML = "";


            if (
                invoice.items &&
                invoice.items.length > 0
            ) {

                invoice.items.forEach(function(item) {

                    const itemTotal =
                        Number(item.itemTotal || 0);


                    itemsHTML += `

                <div class="receipt-item">

                    <div class="receipt-item-top">

                        <span class="receipt-item-name">
                            ${item.name}
                        </span>

                        <span class="receipt-item-price">
                            ${money(itemTotal)}
                        </span>

                    </div>


                    <div class="receipt-item-bottom">

                        <span>
                            ${item.sku}
                            ×
                            ${item.quantity}
                        </span>

                        <span>
                            ${money(item.price)} each
                        </span>

                    </div>

                </div>

            `;

                });

            } else {

                itemsHTML = `
            <p style="
                text-align:center;
                color:var(--text-muted);
                font-size:12px;
            ">
                No products
            </p>
        `;

            }


            /* ========================================
               RECEIPT HTML
            ======================================== */

            container.innerHTML = `

        <div class="receipt">


            <!-- STORE -->

            <div class="receipt-header">

                <h1>
                    ANAJAK JEWELRY
                </h1>

                <p>
                    Jewelry Shop
                </p>

                <p>
                    Phnom Penh, Cambodia
                </p>

            </div>


            <!-- TITLE -->

            <div class="receipt-title">

                <h2>
                    SALES RECEIPT
                </h2>

            </div>


            <!-- INFORMATION -->

            <div class="receipt-info">

                <div class="receipt-info-row">

                    <span>
                        Invoice No.
                    </span>

                    <span>
                        ${invoice.invoiceNumber}
                    </span>

                </div>

                <div class="receipt-info-row">
                    <span>Phone</span>
                    <span>${invoice.phone || '-'}</span>
                </div>

                <div class="receipt-info-row">
                    <span>Address</span>
                    <span>${invoice.address || '-'}</span>
                </div>

                <div class="receipt-info-row">

                    <span>
                        Date
                    </span>

                    <span>
                        ${invoice.date}
                    </span>

                </div>


                <div class="receipt-info-row">

                    <span>
                        Customer
                    </span>

                    <span>
                        ${invoice.customer}
                    </span>

                </div>

                <div class="receipt-info-row">
                    <span>Phone</span>
                    <span>${invoice.phone || '-'}</span>
                </div>

                <div class="receipt-info-row">
                    <span>Address</span>
                    <span>${invoice.address || '-'}</span>
                </div>

                <div class="receipt-info-row">

                    <span>
                        Sales Person
                    </span>

                    <span>
                        Admin
                    </span>

                </div>

            </div>


            <!-- ITEMS -->

            <div class="receipt-items">

                ${itemsHTML}

            </div>


            <!-- SUMMARY -->

            <div class="receipt-summary">


                <div class="receipt-summary-row">

                    <span>
                        Subtotal
                    </span>

                    <strong>
                        ${money(invoice.subtotal)}
                    </strong>

                </div>


                <div class="receipt-summary-row">

                    <span>
                        Discount
                    </span>

                    <strong>
                        -${money(invoice.discount)}
                    </strong>

                </div>


                <div class="receipt-summary-row">

                    <span>
                        Membership
                    </span>

                    <strong>
                        -${money(
                            invoice.membershipDiscount
                        )}
                    </strong>

                </div>


                <div class="receipt-summary-row">

                    <span>
                        Tax
                    </span>

                    <strong>
                        ${money(invoice.tax)}
                    </strong>

                </div>


                <div class="receipt-total">

                    <span>
                        TOTAL
                    </span>

                    <strong>
                        ${money(invoice.total)}
                    </strong>

                </div>

            </div>


            <!-- PAYMENT -->

            <div class="receipt-payment">

                <p>
                    Payment Method
                </p>

                <strong>
                    ${invoice.payment}
                </strong>

            </div>


            <!-- THANK YOU -->

            <div class="receipt-thank-you">

                <p>
                    Thank you for your purchase!
                </p>

                <strong>
                    ANAJAK JEWELRY
                </strong>

            </div>


            <!-- ACTIONS -->

            <div class="receipt-actions">

                <a
                    href="invoice.php?sale_id=${encodeURIComponent(invoice.saleID)}"
                    class="receipt-back"
                >
                    <i class="fa-solid fa-file-invoice"></i>
                    Invoice
                </a>
                
                <a
                    href="index.php"
                    class="receipt-back"
                >
                    <i class="fa-solid fa-arrow-left"></i>
                    Sales
                </a>


                <button
                    class="receipt-print"
                    onclick="printReceipt()"
                >
                    <i class="fa-solid fa-print"></i>
                    Print Receipt
                </button>

            </div>


       
        </div>
        `;

        } catch (error) {
            console.error("Receipt loading error:", error);

            container.innerHTML = `
            <div class="receipt-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h2>Unable to Load Receipt</h2>
                <p>${error.message}</p>
                <a href="index.php" class="receipt-print"
                   style="display:inline-flex;text-decoration:none">
                    Back to Sales
                </a>
            </div>
        `;
        }
    });



    /* ========================================
       PRINT RECEIPT
    ======================================== */

    function printReceipt() {

        window.print();

    }
</script>


<?php
include __DIR__ . '/../includes/footer.php';
?>