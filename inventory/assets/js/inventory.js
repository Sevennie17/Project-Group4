document.addEventListener("DOMContentLoaded", function () {
  console.log("Inventory Dashboard Loaded");
});
console.log("INVENTORY JS LOADED");
/* =========================================================
   STOCK IN JAVASCRIPT
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const stockDate =
        document.getElementById("stockDate");

    const productSelect =
        document.getElementById("productSelect");

    const quantityInput =
        document.getElementById("quantity");

    const unitCostInput =
        document.getElementById("unitCost");

    const addProductBtn =
        document.getElementById("addProductBtn");

    const tableBody =
        document.getElementById("productTableBody");

    const totalItems =
        document.getElementById("totalItems");

    const totalQuantity =
        document.getElementById("totalQuantity");

    const grandTotal =
        document.getElementById("grandTotal");

    const cancelBtn =
        document.getElementById("cancelBtn");

    const saveStockBtn =
        document.getElementById("saveStockBtn");


    /* =====================================================
       CHECK STOCK IN PAGE
    ===================================================== */

    if (!productSelect || !tableBody) {
        return;
    }


    /* =====================================================
       SET TODAY
    ===================================================== */

    if (stockDate) {

        const today = new Date();

        const year =
            today.getFullYear();

        const month =
            String(
                today.getMonth() + 1
            ).padStart(2, "0");

        const day =
            String(
                today.getDate()
            ).padStart(2, "0");

        stockDate.value =
            `${year}-${month}-${day}`;
    }


    /* =====================================================
       PRODUCT CHANGE
    ===================================================== */

    productSelect.addEventListener(
        "change",
        function () {

            const selectedOption =
                this.options[
                    this.selectedIndex
                ];

            const cost =
                selectedOption.getAttribute(
                    "data-cost"
                );

            if (cost) {

                unitCostInput.value =
                    parseFloat(cost)
                    .toFixed(2);

            } else {

                unitCostInput.value =
                    "0.00";
            }
        }
    );


    /* =====================================================
       ADD PRODUCT
    ===================================================== */

    addProductBtn.addEventListener(
        "click",
        function () {

            const selectedOption =
                productSelect.options[
                    productSelect.selectedIndex
                ];

            const productName =
                productSelect.value;

            const code =
                selectedOption.getAttribute(
                    "data-code"
                );

            const quantity =
                parseInt(
                    quantityInput.value
                );

            const unitCost =
                parseFloat(
                    unitCostInput.value
                );


            /* Product validation */

            if (!productName) {

                alert(
                    "Please select a product."
                );

                return;
            }


            /* Quantity validation */

            if (
                isNaN(quantity) ||
                quantity <= 0
            ) {

                alert(
                    "Please enter a valid quantity."
                );

                return;
            }


            /* Cost validation */

            if (
                isNaN(unitCost) ||
                unitCost < 0
            ) {

                alert(
                    "Please enter a valid unit cost."
                );

                return;
            }


            /* Total */

            const total =
                quantity * unitCost;


            /* Create row */

            const row =
                document.createElement("tr");


            row.dataset.product =
                productName;

            row.dataset.code =
                code;

            row.dataset.quantity =
                quantity;

            row.dataset.cost =
                unitCost;


            row.innerHTML = `

                <td></td>

                <td>

                    <div class="product-name-cell">

                        <div class="product-icon">

                            <i class="fa-solid fa-gem"></i>

                        </div>

                        <span>
                            ${productName}
                        </span>

                    </div>

                </td>

                <td>
                    ${code}
                </td>

                <td>
                    ${quantity}
                </td>

                <td>
                    $${unitCost.toFixed(2)}
                </td>

                <td class="total-cell">
                    $${total.toFixed(2)}
                </td>

                <td>

                    <button
                        type="button"
                        class="delete-product"
                    >

                        <i class="fa-solid fa-trash-can"></i>

                    </button>

                </td>
            `;


            tableBody.appendChild(row);


            resetStockForm();

            updateStockNumbers();

            updateStockSummary();

        }
    );


    /* =====================================================
       DELETE PRODUCT
    ===================================================== */

    tableBody.addEventListener(
        "click",
        function (event) {

            const deleteButton =
                event.target.closest(
                    ".delete-product"
                );

            if (!deleteButton) {
                return;
            }


            const row =
                deleteButton.closest("tr");


            if (row) {

                row.remove();

                updateStockNumbers();

                updateStockSummary();
            }

        }
    );


    /* =====================================================
       UPDATE ROW NUMBER
    ===================================================== */

    function updateStockNumbers() {

        const rows =
            tableBody.querySelectorAll("tr");


        rows.forEach(
            function (row, index) {

                if (row.children[0]) {

                    row.children[0]
                        .textContent =
                        index + 1;
                }

            }
        );

    }


    /* =====================================================
       UPDATE SUMMARY
    ===================================================== */

    function updateStockSummary() {

        const rows =
            tableBody.querySelectorAll("tr");


        let totalQty = 0;

        let totalAmount = 0;


        rows.forEach(
            function (row) {

                const quantity =
                    parseInt(
                        row.dataset.quantity
                    ) || 0;

                const cost =
                    parseFloat(
                        row.dataset.cost
                    ) || 0;


                totalQty += quantity;

                totalAmount +=
                    quantity * cost;

            }
        );


        totalItems.textContent =
            rows.length;


        totalQuantity.textContent =
            totalQty;


        grandTotal.textContent =
            "$" +
            totalAmount.toLocaleString(
                "en-US",
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );

    }


    /* =====================================================
       RESET PRODUCT FORM
    ===================================================== */

    function resetStockForm() {

        productSelect.value =
            "";

        quantityInput.value =
            "1";

        unitCostInput.value =
            "0.00";

    }


    /* =====================================================
       CANCEL
    ===================================================== */

    if (cancelBtn) {

        cancelBtn.addEventListener(
            "click",
            function () {

                const confirmCancel =
                    confirm(
                        "Are you sure you want to cancel?"
                    );


                if (confirmCancel) {

                    window.location.href =
                        "/Jewelry_Shop/inventory/";

                }

            }
        );

    }


    /* =====================================================
       SAVE STOCK IN
    ===================================================== */
    if (saveStockBtn) {

    saveStockBtn.addEventListener("click", async function () {

        const rows = tableBody.querySelectorAll("tr");

        // Check products
        if (rows.length === 0) {
            alert("Please add at least one product.");
            return;
        }

        // Check supplier
        const supplier = document.getElementById("supplier");

        if (!supplier || !supplier.value) {
            alert("Please select a supplier.");
            return;
        }

        // Prepare Stock In data
        const products = [];

        rows.forEach(function (row) {

            products.push({
                productName: row.dataset.product,
                productCode: row.dataset.code,
                quantity: parseInt(row.dataset.quantity),
                unitCost: parseFloat(row.dataset.cost)
            });

        });

        const stockDate =
            document.getElementById("stockDate")?.value || "";

        const reference =
            document.getElementById("reference")?.value || "";

        try {

            const response = await fetch(
                "/Jewelry_Shop/inventory/ajax/stock-in.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        stockDate: stockDate,
                        supplier: supplier.value,
                        reference: reference,
                        products: products
                    })
                }
            );

            const result = await response.json();

            if (result.success) {

                alert("Stock In saved successfully!");

               
            } else {

                alert(
                    result.message ||
                    "Failed to save Stock In."
                );

            }

        } catch (error) {

            console.error("Stock In Error:", error);

            alert(
                "Cannot connect to the server."
            );

        }

    });

}
   
    /* =====================================================
       INITIAL SUMMARY
    ===================================================== */

    updateStockNumbers();

    updateStockSummary();

});


/* =========================================================
   STOCK OUT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const productSelect = document.getElementById("stockOutProduct");
    const availableStock = document.getElementById("availableStock");
    const quantityInput = document.getElementById("outQuantity");
    const addBtn = document.getElementById("addStockOutBtn");
    const tableBody = document.getElementById("stockOutTableBody");

    const totalItems = document.getElementById("stockOutTotalItems");
    const totalQuantity = document.getElementById("stockOutTotalQuantity");
    const grandTotal = document.getElementById("stockOutGrandTotal");

    const cancelBtn = document.getElementById("cancelStockOutBtn");
    const saveBtn = document.getElementById("saveStockOutBtn");
    const dateInput = document.getElementById("stockOutDate");


    /* -----------------------------------------
       STOP IF NOT STOCK OUT PAGE
    ----------------------------------------- */

    if (!productSelect || !tableBody) {
        return;
    }


    /* -----------------------------------------
       SET TODAY
    ----------------------------------------- */

    if (dateInput) {

        const today = new Date();

        const year = today.getFullYear();

        const month = String(
            today.getMonth() + 1
        ).padStart(2, "0");

        const day = String(
            today.getDate()
        ).padStart(2, "0");

        dateInput.value =
            `${year}-${month}-${day}`;
    }


    /* -----------------------------------------
       PRODUCT SELECT
    ----------------------------------------- */

    productSelect.addEventListener("change", function () {

        const option =
            this.options[this.selectedIndex];

        if (!option || !option.value) {

            availableStock.value = 0;

            return;
        }

        const stock =
            parseInt(
                option.dataset.stock || 0
            );

        availableStock.value = stock;

        quantityInput.max = stock;

    });


    /* -----------------------------------------
       UPDATE SUMMARY
    ----------------------------------------- */

    function updateStockOutSummary() {

        const rows =
            tableBody.querySelectorAll("tr");

        let items = 0;

        let quantity = 0;

        let total = 0;


        rows.forEach(function (row) {

            const qtyInput =
                row.querySelector(
                    ".stock-out-qty-input"
                );

            const price =
                parseFloat(
                    row.dataset.price || 0
                );

            if (!qtyInput) {
                return;
            }

            const qty =
                parseInt(
                    qtyInput.value || 0
                );

            const rowTotal =
                qty * price;


            const totalCell =
                row.querySelector(
                    ".stock-out-total"
                );

            if (totalCell) {

                totalCell.textContent =
                    "$" + rowTotal.toFixed(2);

            }


            items++;

            quantity += qty;

            total += rowTotal;

        });


        if (totalItems) {
            totalItems.textContent = items;
        }

        if (totalQuantity) {
            totalQuantity.textContent = quantity;
        }

        if (grandTotal) {
            grandTotal.textContent =
                "$" + total.toFixed(2);
        }

    }


    /* -----------------------------------------
       UPDATE ROW NUMBERS
    ----------------------------------------- */

    function updateRowNumbers() {

        const rows =
            tableBody.querySelectorAll("tr");

        rows.forEach(function (row, index) {

            row.children[0].textContent =
                index + 1;

        });

    }


    /* -----------------------------------------
       DELETE PRODUCT
    ----------------------------------------- */

    function attachDeleteEvents() {

        const deleteButtons =
            tableBody.querySelectorAll(
                ".stock-out-delete"
            );

        deleteButtons.forEach(function (button) {

            button.onclick = function () {

                const row =
                    this.closest("tr");

                if (row) {

                    row.remove();

                    updateRowNumbers();

                    updateStockOutSummary();

                }

            };

        });

    }


    /* -----------------------------------------
       QUANTITY CHANGE
    ----------------------------------------- */

    function attachQuantityEvents() {

        const quantityInputs =
            tableBody.querySelectorAll(
                ".stock-out-qty-input"
            );

        quantityInputs.forEach(function (input) {

            input.addEventListener(
                "input",
                function () {

                    const row =
                        this.closest("tr");

                    const stock =
                        parseInt(
                            row.dataset.stock || 0
                        );

                    let qty =
                        parseInt(
                            this.value || 0
                        );


                    if (qty < 1) {
                        qty = 1;
                    }


                    if (qty > stock) {

                        qty = stock;

                        alert(
                            "Quantity cannot exceed available stock."
                        );

                    }


                    this.value = qty;

                    updateStockOutSummary();

                }
            );

        });

    }


    /* -----------------------------------------
       ADD PRODUCT
    ----------------------------------------- */

    if (addBtn) {

        addBtn.addEventListener(
            "click",
            function () {

                const option =
                    productSelect.options[
                        productSelect.selectedIndex
                    ];


                if (!option || !option.value) {

                    alert(
                        "Please select a product."
                    );

                    return;
                }


                const product =
                    option.value;

                const code =
                    option.dataset.code || "";

                const stock =
                    parseInt(
                        option.dataset.stock || 0
                    );

                const price =
                    parseFloat(
                        option.dataset.price || 0
                    );

                let quantity =
                    parseInt(
                        quantityInput.value || 0
                    );


                if (quantity < 1) {

                    alert(
                        "Please enter a valid quantity."
                    );

                    return;
                }


                if (quantity > stock) {

                    alert(
                        "Quantity cannot exceed available stock."
                    );

                    return;
                }


                /* Check duplicate */

                const existingRows =
                    tableBody.querySelectorAll("tr");

                let duplicate = false;


                existingRows.forEach(function (row) {

                    if (
                        row.dataset.product ===
                        product
                    ) {
                        duplicate = true;
                    }

                });


                if (duplicate) {

                    alert(
                        "This product is already in the list."
                    );

                    return;
                }


                const rowTotal =
                    quantity * price;


                const row =
                    document.createElement("tr");


                row.dataset.product =
                    product;

                row.dataset.code =
                    code;

                row.dataset.stock =
                    stock;

                row.dataset.quantity =
                    quantity;

                row.dataset.price =
                    price;


                row.innerHTML = `

                    <td></td>

                    <td>
                        <div class="stock-out-product-name">

                            <div class="stock-out-product-icon">

                                <i class="fa-solid fa-gem"></i>

                            </div>

                            <span>
                                ${product}
                            </span>

                        </div>
                    </td>

                    <td>
                        ${code}
                    </td>

                    <td>
                        ${stock}
                    </td>

                    <td>

                        <input
                            type="number"
                            class="stock-out-qty-input"
                            value="${quantity}"
                            min="1"
                            max="${stock}"
                        >

                    </td>

                    <td>
                        $${price.toFixed(2)}
                    </td>

                    <td class="stock-out-total">

                        $${rowTotal.toFixed(2)}

                    </td>

                    <td>

                        <button
                            type="button"
                            class="stock-out-delete"
                        >

                            <i class="fa-solid fa-trash-can"></i>

                        </button>

                    </td>

                `;


                tableBody.appendChild(row);


                productSelect.value = "";

                availableStock.value = 0;

                quantityInput.value = 1;


                updateRowNumbers();

                attachDeleteEvents();

                attachQuantityEvents();

                updateStockOutSummary();

            }
        );

    }


    /* -----------------------------------------
       CANCEL
    ----------------------------------------- */

    if (cancelBtn) {

        cancelBtn.addEventListener(
            "click",
            function () {

                window.location.href =
                    "/Jewelry_Shop/inventory/";

            }
        );

    }


    /* -----------------------------------------
       SAVE
    ----------------------------------------- */

    if (saveBtn) {

        saveBtn.addEventListener(
            "click",
            function () {

                const rows =
                    tableBody.querySelectorAll("tr");

                const customer =
                    document.getElementById(
                        "customer"
                    );

                const reason =
                    document.getElementById(
                        "outReason"
                    );


                if (rows.length === 0) {

                    alert(
                        "Please add at least one product."
                    );

                    return;
                }


                if (
                    customer &&
                    customer.value === ""
                ) {

                    alert(
                        "Please select a customer / recipient."
                    );

                    customer.focus();

                    return;
                }


                if (
                    reason &&
                    reason.value === ""
                ) {

                    alert(
                        "Please select a reason."
                    );

                    reason.focus();

                    return;
                }


                alert(
                    "Stock Out saved successfully!"
                );

            }
        );

    }


    /* -----------------------------------------
       INITIAL EVENTS
    ----------------------------------------- */

    attachDeleteEvents();

    attachQuantityEvents();

    updateStockOutSummary();

});


/* =========================================================
   INVENTORY ADJUSTMENT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const adjustmentProduct =
        document.getElementById("adjustmentProduct");

    const currentStock =
        document.getElementById("adjustmentCurrentStock");

    const adjustmentType =
        document.getElementById("adjustmentType");

    const adjustmentQuantity =
        document.getElementById("adjustmentQuantity");

    const addAdjustmentBtn =
        document.getElementById("addAdjustmentBtn");

    const tableBody =
        document.getElementById("adjustmentTableBody");


    /* =====================================================
       SET TODAY DATE
    ====================================================== */

    const adjustmentDate =
        document.getElementById("adjustmentDate");

    if (adjustmentDate) {

        const today =
            new Date().toISOString().split("T")[0];

        adjustmentDate.value = today;

    }


    /* =====================================================
       PRODUCT CHANGE
    ====================================================== */

    if (adjustmentProduct) {

        adjustmentProduct.addEventListener("change", function () {

            const option =
                this.options[this.selectedIndex];

            const stock =
                option.getAttribute("data-stock");

            currentStock.value =
                stock ? stock : 0;

        });

    }


    /* =====================================================
       ADD PRODUCT
    ====================================================== */

    if (addAdjustmentBtn) {

        addAdjustmentBtn.addEventListener("click", function () {

            const selectedOption =
                adjustmentProduct.options[
                    adjustmentProduct.selectedIndex
                ];

            if (!adjustmentProduct.value) {

                alert("Please select a product.");

                return;

            }


            const product =
                adjustmentProduct.value;

            const code =
                selectedOption.getAttribute("data-code");

            const stock =
                parseInt(
                    selectedOption.getAttribute("data-stock")
                ) || 0;

            const type =
                adjustmentType.value;

            const quantity =
                parseInt(adjustmentQuantity.value) || 0;


            if (quantity <= 0) {

                alert("Please enter a valid quantity.");

                return;

            }


            /* Prevent negative stock */

            if (
                type === "decrease" &&
                quantity > stock
            ) {

                alert(
                    "Adjustment quantity cannot be greater than current stock."
                );

                return;

            }


            const newStock =
                type === "increase"
                    ? stock + quantity
                    : stock - quantity;


            const row =
                document.createElement("tr");


            row.setAttribute(
                "data-product",
                product
            );

            row.setAttribute(
                "data-code",
                code
            );

            row.setAttribute(
                "data-stock",
                stock
            );

            row.setAttribute(
                "data-type",
                type
            );

            row.setAttribute(
                "data-quantity",
                quantity
            );


            const rowNumber =
                tableBody.children.length + 1;


            row.innerHTML = `

                <td>${rowNumber}</td>

                <td>

                    <div class="adjustment-product-name">

                        <div class="adjustment-product-icon">

                            <i class="fa-solid fa-gem"></i>

                        </div>

                        <span>${product}</span>

                    </div>

                </td>

                <td>${code}</td>

                <td>${stock}</td>

                <td>

                    <span class="adjustment-type ${type}">

                        <i class="fa-solid ${
                            type === "increase"
                                ? "fa-arrow-up"
                                : "fa-arrow-down"
                        }"></i>

                        ${
                            type === "increase"
                                ? "Increase (+)"
                                : "Decrease (-)"
                        }

                    </span>

                </td>

                <td>${quantity}</td>

                <td>${newStock}</td>

                <td>Manual adjustment</td>

                <td>

                    <button
                        type="button"
                        class="adjustment-delete"
                    >

                        <i class="fa-solid fa-trash-can"></i>

                    </button>

                </td>

            `;


            tableBody.appendChild(row);


            /* Reset */

            adjustmentProduct.value = "";

            currentStock.value = "0";

            adjustmentQuantity.value = "1";


            updateAdjustmentSummary();

        });

    }


    /* =====================================================
       DELETE PRODUCT
    ====================================================== */

    if (tableBody) {

        tableBody.addEventListener("click", function (event) {

            const deleteButton =
                event.target.closest(
                    ".adjustment-delete"
                );

            if (!deleteButton) return;


            const row =
                deleteButton.closest("tr");

            if (row) {

                row.remove();

                updateAdjustmentRows();

                updateAdjustmentSummary();

            }

        });

    }


    /* =====================================================
       UPDATE ROW NUMBERS
    ====================================================== */

    function updateAdjustmentRows() {

        const rows =
            tableBody.querySelectorAll("tr");

        rows.forEach(function (row, index) {

            row.children[0].textContent =
                index + 1;

        });

    }


    /* =====================================================
       UPDATE SUMMARY
    ====================================================== */

    function updateAdjustmentSummary() {

        const rows =
            tableBody.querySelectorAll("tr");


        let totalItems = 0;

        let totalIncrease = 0;

        let totalDecrease = 0;


        rows.forEach(function (row) {

            totalItems++;


            const type =
                row.getAttribute("data-type");

            const quantity =
                parseInt(
                    row.getAttribute("data-quantity")
                ) || 0;


            if (type === "increase") {

                totalIncrease += quantity;

            } else {

                totalDecrease += quantity;

            }

        });


        const netChange =
            totalIncrease - totalDecrease;


        const totalItemsElement =
            document.getElementById(
                "adjustmentTotalItems"
            );

        const totalIncreaseElement =
            document.getElementById(
                "adjustmentTotalIncrease"
            );

        const totalDecreaseElement =
            document.getElementById(
                "adjustmentTotalDecrease"
            );

        const netChangeElement =
            document.getElementById(
                "adjustmentNetChange"
            );


        if (totalItemsElement) {

            totalItemsElement.textContent =
                totalItems;

        }

        if (totalIncreaseElement) {

            totalIncreaseElement.textContent =
                totalIncrease;

        }

        if (totalDecreaseElement) {

            totalDecreaseElement.textContent =
                totalDecrease;

        }

        if (netChangeElement) {

            netChangeElement.textContent =
                netChange >= 0
                    ? "+" + netChange
                    : netChange;

        }

    }


    /* =====================================================
       CANCEL
    ====================================================== */

    const cancelAdjustmentBtn =
        document.getElementById(
            "cancelAdjustmentBtn"
        );

    if (cancelAdjustmentBtn) {

        cancelAdjustmentBtn.addEventListener(
            "click",
            function () {

                window.location.href =
                    "/Jewelry_Shop/inventory/";

            }
        );

    }


    /* =====================================================
       SAVE
    ====================================================== */

    const saveAdjustmentBtn =
        document.getElementById(
            "saveAdjustmentBtn"
        );

    if (saveAdjustmentBtn) {

        saveAdjustmentBtn.addEventListener(
            "click",
            function () {

                const rows =
                    tableBody.querySelectorAll("tr");


                if (rows.length === 0) {

                    alert(
                        "Please add at least one product."
                    );

                    return;

                }


                alert(
                    "Adjustment saved successfully!"
                );

            }
        );

    }


    /* Initial summary */

    updateAdjustmentSummary();

});

/* =========================================================
   BARCODE MANAGEMENT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const productSelect =
        document.getElementById("barcodeProduct");

    const generateBtn =
        document.getElementById("generateBarcodeBtn");

    const searchInput =
        document.getElementById("barcodeSearchInput");

    const searchBtn =
        document.getElementById("barcodeSearchBtn");

    const tableSearch =
        document.getElementById("barcodeTableSearch");

    const quantityInput =
        document.getElementById("barcodeQuantity");


    /* =====================================================
       PRODUCT ICON
    ===================================================== */

    function changeProductIcon(productName) {

        const icon =
            document.getElementById("productImageIcon");

        if (!icon) return;

        icon.className = "fa-solid";

        const name =
            productName.toLowerCase();


        if (name.includes("necklace")) {

            icon.classList.add("fa-link");

        }

        else if (name.includes("ring")) {

            icon.classList.add("fa-ring");

        }

        else if (name.includes("earring")) {

            icon.classList.add("fa-gem");

        }

        else if (name.includes("bracelet")) {

            icon.classList.add("fa-circle-notch");

        }

        else if (name.includes("pendant")) {

            icon.classList.add("fa-gem");

        }

        else {

            icon.classList.add("fa-gem");

        }

    }


    /* =====================================================
       GENERATE BARCODE NUMBER
    ===================================================== */

    function generateBarcodeNumber() {

        let number = "";

        for (let i = 0; i < 13; i++) {

            number +=
                Math.floor(
                    Math.random() * 10
                );

        }

        return number;

    }


    /* =====================================================
       UPDATE PRODUCT
    ===================================================== */

    function updateProduct() {

        if (!productSelect) return;

        const option =
            productSelect.options[
                productSelect.selectedIndex
            ];


        if (!option || !option.value) return;


        const product =
            option.value;

        const code =
            option.dataset.code || "";

        const category =
            option.dataset.category || "";

        const brand =
            option.dataset.brand || "";

        const purity =
            option.dataset.purity || "";

        const weight =
            option.dataset.weight || "0.00";

        const price =
            option.dataset.price || "0.00";

        const stock =
            option.dataset.stock || "0";


        /* Details */

        document.getElementById(
            "detailProductName"
        ).textContent = product;


        document.getElementById(
            "detailProductCode"
        ).textContent = code;


        document.getElementById(
            "detailCategory"
        ).textContent = category;


        document.getElementById(
            "detailBrand"
        ).textContent = brand;


        document.getElementById(
            "detailStock"
        ).textContent = stock + " pcs";


        document.getElementById(
            "detailPrice"
        ).textContent =
            "$" +
            parseFloat(price).toFixed(2);


        document.getElementById(
            "detailWeight"
        ).textContent =
            weight + " g";


        /* Status */

        const status =
            document.getElementById(
                "detailStatus"
            );


        if (status) {

            if (parseInt(stock) <= 5) {

                status.textContent =
                    "Low Stock";

                status.className =
                    "barcode-status-low";

            } else {

                status.textContent =
                    "In Stock";

                status.className =
                    "barcode-status-good";

            }

        }


        /* Preview */

        document.getElementById(
            "previewProductName"
        ).textContent = product;


        document.getElementById(
            "previewWeight"
        ).textContent =
            weight + " g";


        document.getElementById(
            "previewPrice"
        ).textContent =
            "$" +
            parseFloat(price).toFixed(2);


        /* Barcode */

        const barcode =
            generateBarcodeNumber();


        document.getElementById(
            "detailBarcode"
        ).textContent =
            barcode;


        document.getElementById(
            "previewBarcodeNumber"
        ).textContent =
            barcode;


        /* Icon */

        changeProductIcon(product);

    }


    /* =====================================================
       PRODUCT CHANGE
    ===================================================== */

    if (productSelect) {

        productSelect.addEventListener(
            "change",
            updateProduct
        );

    }


    /* =====================================================
       GENERATE
    ===================================================== */

    if (generateBtn) {

        generateBtn.addEventListener(
            "click",
            function () {

                if (
                    !productSelect ||
                    !productSelect.value
                ) {

                    alert(
                        "Please select a product first."
                    );

                    return;

                }


                updateProduct();


                alert(
                    "Barcode generated successfully!"
                );

            }
        );

    }


    /* =====================================================
       SEARCH
    ===================================================== */

    function searchBarcode() {

        if (!searchInput) return;


        const keyword =
            searchInput.value
                .trim()
                .toLowerCase();


        if (!keyword) {

            alert(
                "Please enter a barcode."
            );

            return;

        }


        const rows =
            document.querySelectorAll(
                "#barcodeTableBody tr"
            );


        let found = false;


        rows.forEach(function (row) {

            const text =
                row.textContent
                    .toLowerCase();


            if (
                text.includes(keyword)
            ) {

                row.style.display = "";

                row.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

                found = true;

            }

            else {

                row.style.display = "none";

            }

        });


        if (!found) {

            alert(
                "Barcode or product not found."
            );


            rows.forEach(function (row) {

                row.style.display = "";

            });

        }

    }


    if (searchBtn) {

        searchBtn.addEventListener(
            "click",
            searchBarcode
        );

    }


    if (searchInput) {

        searchInput.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    searchBarcode();

                }

            }
        );

    }


    /* =====================================================
       TABLE SEARCH
    ===================================================== */

    if (tableSearch) {

        tableSearch.addEventListener(
            "input",
            function () {

                const keyword =
                    this.value
                        .trim()
                        .toLowerCase();


                const rows =
                    document.querySelectorAll(
                        "#barcodeTableBody tr"
                    );


                rows.forEach(function (row) {

                    const text =
                        row.textContent
                            .toLowerCase();


                    if (
                        text.includes(keyword)
                    ) {

                        row.style.display = "";

                    }

                    else {

                        row.style.display =
                            "none";

                    }

                });

            }
        );

    }


    /* =====================================================
       PRINT
    ===================================================== */

    function printBarcode() {

        const preview =
            document.querySelector(
                ".barcode-preview-box"
            );


        if (!preview) return;


        const printWindow =
            window.open(
                "",
                "_blank",
                "width=500,height=600"
            );


        printWindow.document.write(`
            <!DOCTYPE html>

            <html>

            <head>

                <title>
                    ANAJAK Jewelry Barcode
                </title>

                <style>

                    body {
                        margin: 0;
                        padding: 30px;
                        display: flex;
                        justify-content: center;
                        font-family: Arial, sans-serif;
                    }

                    .barcode-preview-box {
                        width: 320px;
                        padding: 18px;
                        text-align: center;
                        border: 1px solid #ddd;
                    }

                    .barcode-preview-brand {
                        font-weight: bold;
                        font-size: 16px;
                    }

                    .barcode-visual {
                        height: 60px;
                        margin: 15px auto;
                        display: flex;
                        justify-content: center;
                        gap: 3px;
                    }

                    .barcode-visual span {
                        width: 3px;
                        height: 100%;
                        background: #111;
                    }

                    .barcode-visual span:nth-child(2n) {
                        width: 1px;
                    }

                    .barcode-visual span:nth-child(3n) {
                        width: 5px;
                    }

                    .preview-barcode-number {
                        font-family: monospace;
                        font-weight: bold;
                    }

                </style>

            </head>

            <body>

                ${preview.outerHTML}

            </body>

            </html>
        `);


        printWindow.document.close();

        printWindow.focus();


        setTimeout(function () {

            printWindow.print();

            printWindow.close();

        }, 300);

    }


    /* Preview Print */

    const previewPrint =
        document.getElementById(
            "previewPrintBtn"
        );


    if (previewPrint) {

        previewPrint.addEventListener(
            "click",
            printBarcode
        );

    }


    /* Top Print */

    const topPrint =
        document.getElementById(
            "topPrintBarcodeBtn"
        );


    if (topPrint) {

        topPrint.addEventListener(
            "click",
            printBarcode
        );

    }


    /* =====================================================
       TABLE PRINT
    ===================================================== */

    document.querySelectorAll(
        ".table-print"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                printBarcode();

            }
        );

    });


    /* =====================================================
       DELETE
    ===================================================== */

    document.querySelectorAll(
        ".table-delete"
    ).forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const row =
                    this.closest("tr");


                if (!row) return;


                const product =
                    row.children[1]
                        .textContent
                        .trim();


                const answer =
                    confirm(
                        "Delete barcode for " +
                        product +
                        "?"
                    );


                if (answer) {

                    row.remove();

                }

            }
        );

    });


    /* =====================================================
       EDIT
    ===================================================== */

    const editBtn =
        document.getElementById(
            "editBarcodeBtn"
        );


    if (editBtn) {

        editBtn.addEventListener(
            "click",
            function () {

                if (productSelect) {

                    productSelect.focus();

                }

            }
        );

    }


    /* =====================================================
       HISTORY
    ===================================================== */

    const historyBtn =
        document.getElementById(
            "historyBarcodeBtn"
        );


    if (historyBtn) {

        historyBtn.addEventListener(
            "click",
            function () {

                alert(
                    "Barcode history will be connected to database."
                );

            }
        );

    }


    /* =====================================================
       DOWNLOAD
    ===================================================== */

    const downloadBtn =
        document.getElementById(
            "downloadBarcodeBtn"
        );


    if (downloadBtn) {

        downloadBtn.addEventListener(
            "click",
            function () {

                const barcode =
                    document.getElementById(
                        "detailBarcode"
                    ).textContent;


                const text =
                    "ANAJAK JEWELRY\n" +
                    "Barcode: " +
                    barcode;


                const blob =
                    new Blob(
                        [text],
                        {
                            type: "text/plain"
                        }
                    );


                const url =
                    URL.createObjectURL(
                        blob
                    );


                const link =
                    document.createElement(
                        "a"
                    );


                link.href = url;

                link.download =
                    barcode +
                    ".txt";


                document.body.appendChild(
                    link
                );

                link.click();

                document.body.removeChild(
                    link
                );


                URL.revokeObjectURL(
                    url
                );

            }
        );

    }


    /* =====================================================
       INITIAL PRODUCT
    ===================================================== */

    if (productSelect) {

        updateProduct();

    }

});




/* =========================================================
   GOLD WEIGHT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const product =
        document.getElementById("goldProduct");

    const productCode =
        document.getElementById("goldProductCode");

    const purity =
        document.getElementById("goldPurity");

    const currentWeight =
        document.getElementById("goldCurrentWeight");

    const newWeight =
        document.getElementById("goldNewWeight");

    const adjustment =
        document.getElementById("goldAdjustmentType");

    const notes =
        document.getElementById("goldNotes");

    const saveBtn =
        document.getElementById("saveGoldWeightBtn");

    const cancelBtn =
        document.getElementById("goldCancelBtn");

    const search =
        document.getElementById("goldHistorySearch");

    const historyBody =
        document.getElementById("goldHistoryBody");

    const exportBtn =
        document.getElementById("goldExportBtn");


    /* =====================================================
       STOP IF GOLD WEIGHT PAGE NOT FOUND
    ====================================================== */

    if (!product) {
        return;
    }


    /* =====================================================
       PRODUCT CHANGE
    ====================================================== */

    product.addEventListener("change", function () {

        const selected =
            product.options[
                product.selectedIndex
            ];


        if (!selected.value) {

            productCode.value = "";

            currentWeight.value =
                "0.00 g";

            purity.value =
                "18K";

            return;

        }


        productCode.value =
            selected.dataset.code || "";


        currentWeight.value =
            (selected.dataset.weight || "0.00")
            + " g";


        purity.value =
            selected.dataset.purity || "18K";

    });


    /* =====================================================
       SAVE
    ====================================================== */

    if (saveBtn) {

        saveBtn.addEventListener(
            "click",
            function () {


                if (!product.value) {

                    alert(
                        "Please select a product."
                    );

                    product.focus();

                    return;

                }


                if (
                    !newWeight.value ||
                    Number(newWeight.value) < 0
                ) {

                    alert(
                        "Please enter the new gold weight."
                    );

                    newWeight.focus();

                    return;

                }


                if (!adjustment.value) {

                    alert(
                        "Please select an adjustment type."
                    );

                    adjustment.focus();

                    return;

                }


                const oldValue =
                    parseFloat(
                        currentWeight.value
                    ) || 0;


                const newValue =
                    parseFloat(
                        newWeight.value
                    ) || 0;


                const change =
                    newValue - oldValue;


                const changeText =
                    (
                        change >= 0
                            ? "+"
                            : ""
                    )
                    +
                    change.toFixed(2)
                    +
                    " g";


                alert(
                    "Gold Weight saved successfully!\n\n"
                    +
                    "Product: "
                    +
                    product.value
                    +
                    "\n"
                    +
                    "Product Code: "
                    +
                    productCode.value
                    +
                    "\n"
                    +
                    "Purity: "
                    +
                    purity.value
                    +
                    "\n"
                    +
                    "New Weight: "
                    +
                    newValue.toFixed(2)
                    +
                    " g"
                    +
                    "\n"
                    +
                    "Change: "
                    +
                    changeText
                );

            }
        );

    }


    /* =====================================================
       CANCEL
    ====================================================== */

    if (cancelBtn) {

        cancelBtn.addEventListener(
            "click",
            function () {

                product.value = "";

                productCode.value = "";

                currentWeight.value =
                    "0.00 g";

                purity.value =
                    "18K";

                newWeight.value = "";

                adjustment.value = "";

                notes.value = "";

            }
        );

    }


    /* =====================================================
       SEARCH HISTORY
    ====================================================== */

    if (search && historyBody) {

        search.addEventListener(
            "input",
            function () {

                const keyword =
                    search.value
                        .toLowerCase()
                        .trim();


                const rows =
                    historyBody.querySelectorAll(
                        "tr"
                    );


                rows.forEach(
                    function (row) {

                        const text =
                            row.textContent
                                .toLowerCase();


                        if (
                            text.includes(keyword)
                        ) {

                            row.style.display =
                                "";

                        } else {

                            row.style.display =
                                "none";

                        }

                    }
                );

            }
        );

    }


    /* =====================================================
       VIEW / EDIT / DELETE
    ====================================================== */

    if (historyBody) {

        historyBody.addEventListener(
            "click",
            function (event) {

                const button =
                    event.target.closest(
                        ".gold-action"
                    );


                if (!button) {
                    return;
                }


                const row =
                    button.closest("tr");


                const productName =
                    row
                        ?.querySelector(
                            ".product-name-cell span"
                        )
                        ?.textContent
                        .trim()
                    ||
                    "Product";


                /* VIEW */

                if (
                    button.classList.contains(
                        "view"
                    )
                ) {

                    alert(
                        "Viewing gold weight history for:\n"
                        +
                        productName
                    );

                }


                /* EDIT */

                if (
                    button.classList.contains(
                        "edit"
                    )
                ) {

                    alert(
                        "Edit gold weight:\n"
                        +
                        productName
                    );

                }


                /* DELETE */

                if (
                    button.classList.contains(
                        "delete"
                    )
                ) {

                    const confirmed =
                        confirm(
                            "Delete this gold weight history record?"
                        );


                    if (confirmed) {

                        row.remove();

                    }

                }

            }
        );

    }


    /* =====================================================
       EXPORT CSV
    ====================================================== */

    if (exportBtn && historyBody) {

        exportBtn.addEventListener(
            "click",
            function () {

                const rows =
                    [
                        ...historyBody
                            .querySelectorAll("tr")
                    ]
                    .filter(
                        row =>
                            row.style.display !==
                            "none"
                    )
                    .map(
                        row => {

                            return [
                                ...row
                                    .querySelectorAll(
                                        "td"
                                    )
                            ]
                            .slice(0, 9)
                            .map(
                                cell =>
                                    '"'
                                    +
                                    cell.innerText
                                        .replaceAll(
                                            '"',
                                            '""'
                                        )
                                        .trim()
                                    +
                                    '"'
                            )
                            .join(",");

                        }
                    );


                const csv = [

                    "No,Product,Code,Purity,Old Weight,New Weight,Change,Adjusted By,Date & Time",

                    ...rows

                ].join("\n");


                const blob =
                    new Blob(
                        [csv],
                        {
                            type:
                                "text/csv;charset=utf-8;"
                        }
                    );


                const url =
                    URL.createObjectURL(
                        blob
                    );


                const link =
                    document.createElement(
                        "a"
                    );


                link.href = url;

                link.download =
                    "gold-weight-history.csv";


                document.body.appendChild(
                    link
                );


                link.click();


                document.body.removeChild(
                    link
                );


                URL.revokeObjectURL(
                    url
                );

            }
        );

    }

});

/* =========================================================
   GOLD WEIGHT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const product =
        document.getElementById("goldProduct");

    if (!product) {
        return;
    }


    const productCode =
        document.getElementById("goldProductCode");

    const purity =
        document.getElementById("goldPurity");

    const currentWeight =
        document.getElementById("goldCurrentWeight");

    const newWeight =
        document.getElementById("goldNewWeight");

    const adjustment =
        document.getElementById("goldAdjustment");

    const notes =
        document.getElementById("goldNotes");

    const saveBtn =
        document.getElementById("saveGoldWeightBtn");

    const cancelBtn =
        document.getElementById("goldCancelBtn");

    const search =
        document.getElementById("goldHistorySearch");

    const historyBody =
        document.getElementById("goldHistoryBody");


    /* PRODUCT */

    product.addEventListener("change", function () {

        const selected =
            product.options[
                product.selectedIndex
            ];


        if (!selected.value) {

            productCode.value = "";

            purity.value = "18K";

            currentWeight.value =
                "0.00 g";

            return;
        }


        productCode.value =
            selected.dataset.code || "";


        purity.value =
            selected.dataset.purity || "18K";


        currentWeight.value =
            (
                selected.dataset.weight
                || "0.00"
            )
            +
            " g";

    });


    /* SAVE */

    if (saveBtn) {

        saveBtn.addEventListener(
            "click",
            function () {

                if (!product.value) {

                    alert(
                        "Please select a product."
                    );

                    product.focus();

                    return;
                }


                if (
                    !newWeight.value ||
                    Number(newWeight.value) < 0
                ) {

                    alert(
                        "Please enter the new gold weight."
                    );

                    newWeight.focus();

                    return;
                }


                if (!adjustment.value) {

                    alert(
                        "Please select adjustment type."
                    );

                    adjustment.focus();

                    return;
                }


                alert(
                    "Gold Weight saved successfully!"
                    +
                    "\n\nProduct: "
                    +
                    product.value
                    +
                    "\nProduct Code: "
                    +
                    productCode.value
                    +
                    "\nPurity: "
                    +
                    purity.value
                    +
                    "\nNew Weight: "
                    +
                    newWeight.value
                    +
                    " g"
                );

            }
        );

    }


    /* CANCEL */

    if (cancelBtn) {

        cancelBtn.addEventListener(
            "click",
            function () {

                product.value = "";

                productCode.value = "";

                purity.value = "18K";

                currentWeight.value =
                    "0.00 g";

                newWeight.value = "";

                adjustment.value = "";

                notes.value = "";

            }
        );

    }


    /* SEARCH */

    if (search && historyBody) {

        search.addEventListener(
            "input",
            function () {

                const keyword =
                    search.value
                        .toLowerCase()
                        .trim();


                const rows =
                    historyBody.querySelectorAll(
                        "tr"
                    );


                rows.forEach(
                    function (row) {

                        const text =
                            row.textContent
                                .toLowerCase();


                        row.style.display =
                            text.includes(keyword)
                                ? ""
                                : "none";

                    }
                );

            }
        );

    }


    /* VIEW / EDIT / DELETE */

    if (historyBody) {

        historyBody.addEventListener(
            "click",
            function (event) {

                const button =
                    event.target.closest(
                        ".gold-history-btn"
                    );


                if (!button) {
                    return;
                }


                const row =
                    button.closest("tr");


                if (
                    button.classList.contains(
                        "view"
                    )
                ) {

                    alert(
                        "View Gold Weight History"
                    );

                }


                if (
                    button.classList.contains(
                        "edit"
                    )
                ) {

                    alert(
                        "Edit Gold Weight"
                    );

                }


                if (
                    button.classList.contains(
                        "delete"
                    )
                ) {

                    if (
                        confirm(
                            "Are you sure you want to delete this record?"
                        )
                    ) {

                        row.remove();

                    }

                }

            }
        );

    }

});

/* =========================================================
   STOCK IN - ADD PRODUCT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const productSelect =
        document.getElementById("productSelect");

    const quantityInput =
        document.getElementById("quantity");

    const unitCostInput =
        document.getElementById("unitCost");

    const addProductBtn =
        document.getElementById("addProductBtn");

    const productTableBody =
        document.getElementById("productTableBody");

    const totalItems =
        document.getElementById("totalItems");

    const totalQuantity =
        document.getElementById("totalQuantity");

    const grandTotal =
        document.getElementById("grandTotal");

    const cancelBtn =
        document.getElementById("cancelBtn");


    /* =====================================================
       CHECK STOCK IN PAGE
    ====================================================== */

    if (
        !productSelect ||
        !quantityInput ||
        !unitCostInput ||
        !addProductBtn ||
        !productTableBody
    ) {
        return;
    }


    /* =====================================================
       PRODUCT ICON
    ====================================================== */

    function getProductIcon(productName) {

        const name =
            productName.toLowerCase();


        if (name.includes("ring")) {
            return "fa-ring";
        }

        if (name.includes("necklace")) {
            return "fa-link";
        }

        if (name.includes("bracelet")) {
            return "fa-circle-notch";
        }

        if (
            name.includes("earring") ||
            name.includes("pendant")
        ) {
            return "fa-gem";
        }

        return "fa-gem";
    }


    /* =====================================================
       UPDATE SUMMARY
    ====================================================== */

    function updateStockSummary() {

        const rows =
            productTableBody.querySelectorAll("tr");


        let itemCount = 0;

        let quantityTotal = 0;

        let moneyTotal = 0;


        rows.forEach(function (row) {

            const quantity =
                Number(
                    row.dataset.quantity || 0
                );

            const cost =
                Number(
                    row.dataset.cost || 0
                );


            itemCount++;

            quantityTotal += quantity;

            moneyTotal +=
                quantity * cost;

        });


        if (totalItems) {

            totalItems.textContent =
                itemCount;

        }


        if (totalQuantity) {

            totalQuantity.textContent =
                quantityTotal;

        }


        if (grandTotal) {

            grandTotal.textContent =
                "$" +
                moneyTotal.toFixed(2);

        }


        updateRowNumbers();

    }


    /* =====================================================
       UPDATE ROW NUMBERS
    ====================================================== */

    function updateRowNumbers() {

        const rows =
            productTableBody.querySelectorAll("tr");


        rows.forEach(function (row, index) {

            const numberCell =
                row.querySelector("td:first-child");


            if (numberCell) {

                numberCell.textContent =
                    index + 1;

            }

        });

    }


    /* =====================================================
       ADD PRODUCT
    ====================================================== */

    addProductBtn.addEventListener(
        "click",
        function () {

            const selectedOption =
                productSelect.options[
                    productSelect.selectedIndex
                ];


            /* PRODUCT CHECK */

            if (
                !selectedOption ||
                !selectedOption.value
            ) {

                alert(
                    "Please select a product."
                );

                productSelect.focus();

                return;

            }


            /* QUANTITY */

            const quantity =
                Number(
                    quantityInput.value
                );


            if (
                !quantity ||
                quantity < 1
            ) {

                alert(
                    "Please enter a valid quantity."
                );

                quantityInput.focus();

                return;

            }


            /* UNIT COST */

            const unitCost =
                Number(
                    unitCostInput.value
                );


            if (
                isNaN(unitCost) ||
                unitCost < 0
            ) {

                alert(
                    "Please enter a valid unit cost."
                );

                unitCostInput.focus();

                return;

            }


            const productName =
                selectedOption.value;


            const productCode =
                selectedOption.dataset.code
                || "";


            /* =================================================
               CHECK IF PRODUCT ALREADY EXISTS
            ================================================= */

            const existingRows =
                productTableBody.querySelectorAll(
                    "tr"
                );


            let existingRow = null;


            existingRows.forEach(function (row) {

                if (
                    row.dataset.product ===
                    productName
                ) {

                    existingRow = row;

                }

            });


            /* =================================================
               IF EXISTING → ADD QUANTITY
            ================================================= */

            if (existingRow) {

                const oldQuantity =
                    Number(
                        existingRow.dataset.quantity
                        || 0
                    );


                const newQuantity =
                    oldQuantity + quantity;


                existingRow.dataset.quantity =
                    newQuantity;


                existingRow.dataset.cost =
                    unitCost;


                const cells =
                    existingRow.querySelectorAll(
                        "td"
                    );


                /* Quantity */

                cells[3].textContent =
                    newQuantity;


                /* Unit Cost */

                cells[4].textContent =
                    "$" +
                    unitCost.toFixed(2);


                /* Total */

                cells[5].textContent =
                    "$" +
                    (
                        newQuantity *
                        unitCost
                    ).toFixed(2);


                updateStockSummary();

                resetProductForm();

                return;

            }


            /* =================================================
               CREATE NEW ROW
            ================================================= */

            const row =
                document.createElement("tr");


            row.dataset.product =
                productName;


            row.dataset.code =
                productCode;


            row.dataset.quantity =
                quantity;


            row.dataset.cost =
                unitCost;


            const total =
                quantity * unitCost;


            const icon =
                getProductIcon(
                    productName
                );


            row.innerHTML = `

                <td></td>

                <td>

                    <div class="product-name-cell">

                        <div class="product-icon">

                            <i class="fa-solid ${icon}"></i>

                        </div>

                        <span>
                            ${productName}
                        </span>

                    </div>

                </td>

                <td>
                    ${productCode}
                </td>

                <td>
                    ${quantity}
                </td>

                <td>
                    $${unitCost.toFixed(2)}
                </td>

                <td class="total-cell">
                    $${total.toFixed(2)}
                </td>

                <td>

                    <button
                        type="button"
                        class="delete-product"
                        title="Remove Product"
                    >

                        <i class="fa-solid fa-trash-can"></i>

                    </button>

                </td>

            `;


            productTableBody.appendChild(
                row
            );


            updateStockSummary();


            resetProductForm();

        }
    );


    /* =====================================================
       DELETE PRODUCT
    ====================================================== */

    productTableBody.addEventListener(
        "click",
        function (event) {

            const deleteBtn =
                event.target.closest(
                    ".delete-product"
                );


            if (!deleteBtn) {
                return;
            }


            const row =
                deleteBtn.closest("tr");


            if (!row) {
                return;
            }


            const productName =
                row.dataset.product
                || "this product";


            const confirmDelete =
                confirm(
                    "Remove " +
                    productName +
                    " from Stock In?"
                );


            if (!confirmDelete) {
                return;
            }


            row.remove();


            updateStockSummary();

        }
    );


    /* =====================================================
       RESET PRODUCT INPUT
    ====================================================== */

    function resetProductForm() {

        productSelect.value = "";

        quantityInput.value = 1;

        unitCostInput.value = "0.00";

    }


    /* =====================================================
       CANCEL
    ====================================================== */

    if (cancelBtn) {

        cancelBtn.addEventListener(
            "click",
            function () {

                const rows =
                    productTableBody.querySelectorAll(
                        "tr"
                    );


                if (rows.length > 0) {

                    const answer =
                        confirm(
                            "Clear all Stock In products?"
                        );


                    if (!answer) {
                        return;
                    }

                }


                productTableBody.innerHTML =
                    "";


                resetProductForm();


                updateStockSummary();

            }
        );

    }


    /* =====================================================
       INITIAL SUMMARY
    ====================================================== */

    updateStockSummary();

});