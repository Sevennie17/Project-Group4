<?php
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="assets/css/sales.css">

<div class="sales-page">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
                Return & Exchange
            </h1>
            <p>Process product returns and exchanges</p>
        </div>

        <a href="index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Sales
        </a>
    </div>


    <!-- Search Invoice -->
    <div class="sales-card return-search-card">

        <div class="card-title">
            <i class="fa-solid fa-magnifying-glass"></i>
            Find Invoice
        </div>

        <div class="invoice-search">
            <input
                type="text"
                id="invoiceNumber"
                placeholder="Enter Invoice Number (e.g. INV-123456789)">

            <button type="button" class="btn-primary" onclick="findInvoice()">
                <i class="fa-solid fa-search"></i>
                Search Invoice
            </button>
        </div>

    </div>


    <!-- Invoice Information -->
    <div class="sales-card" id="invoiceSection" style="display:none;">

        <div class="card-title">
            <i class="fa-solid fa-file-invoice"></i>
            Invoice Information
        </div>

        <div class="invoice-info-grid">

            <div class="info-box">
                <span>Invoice Number</span>
                <strong id="displayInvoice">-</strong>
            </div>

            <div class="info-box">
                <span>Date</span>
                <strong id="displayDate">-</strong>
            </div>

            <div class="info-box">
                <span>Customer</span>
                <strong id="displayCustomer">-</strong>
            </div>

            <div class="info-box">
                <span>Payment</span>
                <strong id="displayPayment">-</strong>
            </div>

        </div>

    </div>


    <!-- Return / Exchange -->
    <div class="sales-card" id="returnSection" style="display:none;">

        <div class="card-title">
            <i class="fa-solid fa-box-open"></i>
            Return / Exchange

            <div class="return-type-buttons">

                <button
                    type="button"
                    class="return-type active"
                    data-type="return"
                    onclick="selectReturnType('return')">
                    <i class="fa-solid fa-rotate-left"></i>
                    Return
                </button>

                <button
                    type="button"
                    class="return-type"
                    data-type="exchange"
                    onclick="selectReturnType('exchange')">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    Exchange
                </button>

            </div>
        </div>


        <!-- Products -->
        <div class="table-wrapper">

            <table class="sales-table">

                <thead>
                    <tr>
                        <th width="50">Select</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Sold Qty</th>
                        <th>Return Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody id="returnProducts">
                </tbody>

            </table>

        </div>


        <!-- Reason -->
        <div class="return-form">

            <div class="form-group">

                <label for="returnReason">
                    Return Reason
                </label>

                <select id="returnReason">

                    <option value="">
                        Select reason
                    </option>

                    <option value="defective">
                        Defective Product
                    </option>

                    <option value="wrong">
                        Wrong Product
                    </option>

                    <option value="customer">
                        Customer Changed Mind
                    </option>

                    <option value="damaged">
                        Damaged Product
                    </option>

                    <option value="other">
                        Other
                    </option>

                </select>

            </div>


            <div class="form-group">

                <label for="returnNotes">
                    Notes
                </label>

                <textarea
                    id="returnNotes"
                    rows="4"
                    placeholder="Enter additional notes..."></textarea>

            </div>

        </div>


        <!-- Refund Summary -->
        <div class="refund-summary">

            <div>
                <span>Selected Items</span>
                <strong id="selectedItems">0</strong>
            </div>

            <div>
                <span>Refund Amount</span>
                <strong id="refundAmount">$0.00</strong>
            </div>

        </div>


        <!-- Actions -->
        <div class="return-actions">

            <a href="index.php" class="btn-secondary">
                <i class="fa-solid fa-xmark"></i>
                Cancel
            </a>

            <button
                type="button"
                class="btn-primary"
                onclick="processReturn()">
                <i class="fa-solid fa-check"></i>
                Process Return
            </button>

        </div>

    </div>

</div>


<script>
    let currentInvoice = null;
    let returnType = "return";


    /* ============================
       FIND INVOICE
    ============================ */


    async function findInvoice() {
        const invoiceNumber = document
            .getElementById("invoiceNumber")
            .value.trim();

        if (!invoiceNumber) {
            alert("Please enter an invoice number.");
            return;
        }

        try {
            const response = await fetch(
                `ajax/find-return-invoice.php?sale_id=${encodeURIComponent(invoiceNumber)}`, {
                    headers: {
                        Accept: "application/json"
                    }
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || "Invoice not found.");
            }

            currentInvoice = result.invoice;
            displayInvoice(currentInvoice);

        } catch (error) {
            console.error("Invoice search error:", error);
            alert(error.message || "Unable to search for invoice.");
        }
    }



    /* ============================
       DISPLAY INVOICE
    ============================ */

    function displayInvoice(invoice) {

        document.getElementById("invoiceSection").style.display = "block";

        document.getElementById("returnSection").style.display = "block";


        document.getElementById("displayInvoice").textContent =
            invoice.invoiceNumber || "-";


        document.getElementById("displayDate").textContent =
            invoice.date || "-";


        document.getElementById("displayCustomer").textContent =
            invoice.customer || "Walk-in Customer";


        document.getElementById("displayPayment").textContent =
            invoice.payment || "Cash";


        renderReturnProducts(invoice.items || []);

    }


    /* ============================
       RENDER PRODUCTS
    ============================ */

    function renderReturnProducts(items) {

        const tbody =
            document.getElementById("returnProducts");


        tbody.innerHTML = "";


        if (!items.length) {

            tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center;">
                    No products found in this invoice.
                </td>
            </tr>
        `;

            return;
        }


        items.forEach((item, index) => {

            const row = document.createElement("tr");


            row.innerHTML = `

            <td>
                <input
                    type="checkbox"
                    class="return-checkbox"
                    data-index="${index}"
                    onchange="calculateRefund()"
                >
            </td>

            <td>
                <strong>${escapeHTML(item.name)}</strong>
            </td>

            <td>
                ${escapeHTML(item.sku || "-")}
            </td>

            <td>
                ${item.quantity}
            </td>

            <td>

                <input
                    type="number"
                    class="return-qty"
                    data-index="${index}"
                    value="1"
                    min="1"
                    max="${item.quantity}"
                    onchange="calculateRefund()"
                >

            </td>

            <td>
                $${Number(item.price).toFixed(2)}
            </td>

            <td>
                $<span class="return-amount" data-index="${index}">
                    0.00
                </span>
            </td>

        `;


            tbody.appendChild(row);

        });

    }


    /* ============================
       RETURN TYPE
    ============================ */

    function selectReturnType(type) {

        returnType = type;


        document
            .querySelectorAll(".return-type")
            .forEach(button => {

                button.classList.remove("active");

            });


        document
            .querySelector(
                `.return-type[data-type="${type}"]`
            )
            .classList.add("active");


        const processButton =
            document.querySelector(".return-actions .btn-primary");


        if (type === "exchange") {

            processButton.innerHTML = `
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
            Process Exchange
        `;

        } else {

            processButton.innerHTML = `
            <i class="fa-solid fa-check"></i>
            Process Return
        `;

        }

    }


    /* ============================
       CALCULATE REFUND
    ============================ */

    function calculateRefund() {

        if (!currentInvoice) return;


        let total = 0;

        let selected = 0;


        const checkboxes =
            document.querySelectorAll(".return-checkbox");


        checkboxes.forEach(checkbox => {

            const index =
                Number(checkbox.dataset.index);


            const item =
                currentInvoice.items[index];


            const qtyInput =
                document.querySelector(
                    `.return-qty[data-index="${index}"]`
                );


            const amountElement =
                document.querySelector(
                    `.return-amount[data-index="${index}"]`
                );


            if (checkbox.checked) {

                selected++;


                let qty =
                    Number(qtyInput.value);


                if (qty < 1) qty = 1;

                if (qty > item.quantity) {

                    qty = item.quantity;

                    qtyInput.value = qty;

                }


                const amount =
                    qty * Number(item.price);


                total += amount;


                amountElement.textContent =
                    amount.toFixed(2);

            } else {

                amountElement.textContent =
                    "0.00";

            }

        });


        document.getElementById("selectedItems").textContent =
            selected;


        document.getElementById("refundAmount").textContent =
            "$" + total.toFixed(2);

    }


    /* ============================
       PROCESS RETURN
    ============================ */


    async function processReturn() {
        if (!currentInvoice) {
            alert("Please search for an invoice first.");
            return;
        }

        // Exchange needs new-product selection and price calculation first.
        if (returnType === "exchange") {
            alert(
                "Exchange is not ready yet. Please use Return only until the exchange workflow is completed."
            );
            return;
        }

        const selected = [
            ...document.querySelectorAll(".return-checkbox:checked")
        ];

        if (!selected.length) {
            alert("Please select at least one product.");
            return;
        }

        const reason = document.getElementById("returnReason").value;

        if (!reason) {
            alert("Please select a return reason.");
            return;
        }

        const items = [];

        for (const checkbox of selected) {
            const index = Number(checkbox.dataset.index);
            const item = currentInvoice.items[index];

            const qtyInput = document.querySelector(
                `.return-qty[data-index="${index}"]`
            );

            const quantity = Number(qtyInput.value);

            if (
                !item ||
                !item.saleDetailID ||
                !Number.isInteger(quantity) ||
                quantity < 1 ||
                quantity > Number(item.quantity)
            ) {
                alert("Please check the selected product quantities.");
                return;
            }

            items.push({
                saleDetailID: Number(item.saleDetailID),
                quantity: quantity
            });
        }

        const refundAmount = items.reduce((total, selectedItem) => {
            const item = currentInvoice.items.find(
                product =>
                Number(product.saleDetailID) === selectedItem.saleDetailID
            );

            return total + selectedItem.quantity * Number(item.price);
        }, 0);

        const confirmed = confirm(
            `Process this return?\n\n` +
            `Invoice: ${currentInvoice.invoiceNumber}\n` +
            `Refund Amount: $${refundAmount.toFixed(2)}`
        );

        if (!confirmed) return;

        const processButton = document.querySelector(
            ".return-actions .btn-primary"
        );

        if (processButton.disabled) return;

        const originalText = processButton.innerHTML;
        processButton.disabled = true;
        processButton.textContent = "Processing...";

        try {
            const response = await fetch("ajax/process-return.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    saleID: currentInvoice.saleID,
                    type: "return",
                    reason: reason,
                    notes: document.getElementById("returnNotes").value.trim(),
                    items: items
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Unable to process this return."
                );
            }

            alert(
                `Return saved successfully!\n\n` +
                `Reference: ${result.returnID}\n` +
                `Refund Amount: $${Number(result.refundAmount).toFixed(2)}`
            );

            window.location.href = "index.php";

        } catch (error) {
            console.error("Process return error:", error);
            alert(error.message || "Unable to process this return.");

            processButton.disabled = false;
            processButton.innerHTML = originalText;
        }
    }



    /* ============================
       SECURITY
    ============================ */

    function escapeHTML(value) {

        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    }
</script>


<style>
    /* Return & Exchange */

    /* =========================================
   ANAJAK JEWELRY — RETURN & EXCHANGE
========================================= */

    .sales-page {
        max-width: 1600px;
        margin: 0 auto;
        padding: 30px 32px 40px;
        color: var(--text-color, #2b2b2b);
    }

    /* Page header */
    .sales-page .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
    }

    .sales-page .page-header h1 {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 8px;
        font-size: 27px;
        font-weight: 700;
        letter-spacing: -0.5px;
        color: #292929;
    }

    .sales-page .page-header h1 i {
        color: var(--gold-color, #c9a227);
    }

    .sales-page .page-header p {
        margin: 0;
        color: var(--text-muted, #777);
        font-size: 14px;
    }

    .sales-page .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border: 1px solid #e8e1d0;
        border-radius: 9px;
        background: #fff;
        color: #49443a;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .sales-page .btn-back:hover {
        border-color: var(--gold-color, #c9a227);
        background: #faf6e9;
    }

    /* Cards */
    .sales-page .sales-card {
        margin-bottom: 22px;
        padding: 24px;
        border: 1px solid #ece8df;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 18px rgba(36, 30, 15, 0.045);
    }

    .sales-page .card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: #292929;
        font-size: 16px;
        font-weight: 700;
    }

    .sales-page .card-title>i {
        color: var(--gold-color, #c9a227);
    }

    /* Search invoice */
    .sales-page .return-search-card {
        margin-bottom: 22px;
    }

    .sales-page .invoice-search {
        display: flex;
        align-items: stretch;
        gap: 12px;
    }

    .sales-page .invoice-search input {
        box-sizing: border-box;
        min-width: 0;
        min-height: 46px;
        padding: 12px 15px;
        border: 1px solid #dedbd3;
        border-radius: 9px;
        outline: none;
        background: #fdfdfc;
        color: #2b2b2b;
        font-size: 14px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .sales-page .invoice-search input:focus {
        border-color: var(--gold-color, #c9a227);
        box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.13);
    }

    /* Buttons */
    .sales-page .btn-primary,
    .sales-page .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 10px 17px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s, transform 0.2s;
    }

    .sales-page .btn-primary {
        border: 1px solid var(--gold-color, #c9a227);
        background: var(--gold-color, #c9a227);
        color: #fff;
    }

    .sales-page .btn-primary:hover {
        border-color: #ad8719;
        background: #ad8719;
    }

    .sales-page .btn-secondary {
        border: 1px solid #dedbd3;
        background: #fff;
        color: #49443a;
    }

    .sales-page .btn-secondary:hover {
        background: #f8f6f0;
        border-color: #c9c2b3;
    }

    /* Invoice information */
    .sales-page .invoice-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .sales-page .info-box {
        min-width: 0;
        padding: 18px;
        border: 1px solid #eee9de;
        border-radius: 11px;
        background: #fdfcf9;
    }

    .sales-page .info-box span {
        display: block;
        margin-bottom: 9px;
        color: #858078;
        font-size: 12px;
        font-weight: 500;
    }

    .sales-page .info-box strong {
        display: block;
        overflow-wrap: anywhere;
        color: #302d27;
        font-size: 14px;
        font-weight: 700;
    }

    /* Return / Exchange selector */
    .sales-page .return-type-buttons {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    .sales-page .return-type {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border: 1px solid #e1ddd3;
        border-radius: 8px;
        background: #fff;
        color: #5f5a50;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sales-page .return-type:hover {
        border-color: var(--gold-color, #c9a227);
        background: #fcf9f0;
    }

    .sales-page .return-type.active {
        border-color: var(--gold-color, #c9a227);
        background: var(--gold-color, #c9a227);
        color: #fff;
        box-shadow: 0 3px 8px rgba(201, 162, 39, 0.18);
    }

    /* Product table */
    .sales-page .table-wrapper {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #eeeae2;
        border-radius: 10px;
    }

    .sales-page .sales-table {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
    }

    .sales-page .sales-table th {
        padding: 15px 14px;
        border-bottom: 1px solid #e9e4d9;
        background: #faf8f2;
        color: #6e6759;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.45px;
        text-align: left;
        white-space: nowrap;
    }

    .sales-page .sales-table td {
        padding: 16px 14px;
        border-bottom: 1px solid #f0ede7;
        color: #514d45;
        font-size: 13px;
        vertical-align: middle;
    }

    .sales-page .sales-table tbody tr:last-child td {
        border-bottom: none;
    }

    .sales-page .sales-table tbody tr {
        transition: background 0.15s ease;
    }

    .sales-page .sales-table tbody tr:hover {
        background: #fdfbf6;
    }

    .sales-page .sales-table td strong {
        color: #302d27;
        font-weight: 600;
    }

    .sales-page .sales-table input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--gold-color, #c9a227);
        cursor: pointer;
    }

    .sales-page .return-qty {
        box-sizing: border-box;
        width: 76px;
        min-height: 36px;
        padding: 7px 9px;
        border: 1px solid #dedbd3;
        border-radius: 7px;
        outline: none;
        background: #fff;
        color: #302d27;
        font-size: 13px;
    }

    .sales-page .return-qty:focus {
        border-color: var(--gold-color, #c9a227);
        box-shadow: 0 0 0 2px rgba(201, 162, 39, 0.12);
    }

    .sales-page .return-amount {
        color: #6d5514;
        font-weight: 700;
    }

    /* Form fields */
    .sales-page .return-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-top: 24px;
    }

    .sales-page .form-group {
        min-width: 0;
    }

    .sales-page .form-group label {
        display: block;
        margin-bottom: 9px;
        color: #49443a;
        font-size: 13px;
        font-weight: 700;
    }

    .sales-page .form-group select,
    .sales-page .form-group textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 12px 13px;
        border: 1px solid #dedbd3;
        border-radius: 9px;
        outline: none;
        background: #fff;
        color: #39362f;
        font: inherit;
        font-size: 13px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .sales-page .form-group select {
        min-height: 44px;
    }

    .sales-page .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }

    .sales-page .form-group select:focus,
    .sales-page .form-group textarea:focus {
        border-color: var(--gold-color, #c9a227);
        box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.12);
    }

    /* Refund summary */
    .sales-page .refund-summary {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 42px;
        margin-top: 26px;
        padding: 22px 24px;
        border: 1px solid #eee5cc;
        border-radius: 12px;
        background: linear-gradient(110deg, #fcfaf4, #f7f0dc);
    }

    .sales-page .refund-summary>div {
        min-width: 110px;
    }

    .sales-page .refund-summary span {
        display: block;
        margin-bottom: 8px;
        color: #827965;
        font-size: 12px;
    }

    .sales-page .refund-summary strong {
        color: #302d27;
        font-size: 22px;
        font-weight: 750;
    }

    /* Bottom actions */
    .sales-page .return-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 24px;
    }

    .sales-page .return-actions .btn-primary,
    .sales-page .return-actions .btn-secondary {
        min-height: 44px;
    }

    /* Tablet */
    @media (max-width: 1000px) {
        .sales-page {
            padding: 24px 20px 32px;
        }

        .sales-page .invoice-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sales-page .sales-card {
            padding: 20px;
        }
    }

    /* Mobile */
    @media (max-width: 600px) {
        .sales-page {
            padding: 18px 12px 28px;
        }

        .sales-page .page-header {
            align-items: flex-start;
            flex-direction: column;
            margin-bottom: 22px;
        }

        .sales-page .page-header h1 {
            font-size: 23px;
        }

        .sales-page .sales-card {
            padding: 16px;
            border-radius: 11px;
        }

        .sales-page .invoice-search {
            flex-direction: column;
        }

        .sales-page .invoice-search .btn-primary {
            width: 100%;
        }

        .sales-page .invoice-info-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .sales-page .info-box {
            padding: 14px;
        }

        .sales-page .card-title {
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 10px;
        }

        .sales-page .return-type-buttons {
            width: 100%;
            margin-left: 0;
        }

        .sales-page .return-type {
            flex: 1;
            padding: 10px 8px;
        }

        .sales-page .return-form {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .sales-page .refund-summary {
            justify-content: space-between;
            gap: 14px;
            padding: 18px 14px;
        }

        .sales-page .refund-summary>div {
            min-width: 0;
        }

        .sales-page .refund-summary strong {
            font-size: 19px;
        }

        .sales-page .return-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .sales-page .return-actions .btn-primary,
        .sales-page .return-actions .btn-secondary {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>


<?php
include __DIR__ . '/../includes/footer.php';
?>