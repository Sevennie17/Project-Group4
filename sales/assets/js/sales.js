/* ========================================
   SALES / POS JAVASCRIPT
======================================== */

let cart = [];

const TAX_RATE = 0.08;

/* ========================================
   ADD TO CART
======================================== */

function addToCart(button) {
  const product = button.closest(".product-item");

  const name = product.dataset.name;
  const sku = product.dataset.sku;
  const category = product.dataset.category;
  const price = parseFloat(product.dataset.price);
  const stock = parseInt(product.dataset.stock);

  const existingProduct = cart.find((item) => item.sku === sku);

  if (existingProduct) {
    if (existingProduct.quantity >= stock) {
      alert("Not enough stock available.");
      return;
    }

    existingProduct.quantity++;
  } else {
    const imageElement = product.querySelector(".product-image img");

    const image = imageElement ? imageElement.getAttribute("src") : "";

    cart.push({
      name: name,
      sku: sku,
      category: category,
      price: price,
      stock: stock,
      image: image,
      quantity: 1,
    });
  }

  renderCart();
  calculateTotal();
}

/* ========================================
   RENDER CART
======================================== */

function renderCart() {
  const cartContainer = document.getElementById("cartItems");
  const cartCount = document.getElementById("cartCount");

  if (!cartContainer) return;

  cartContainer.innerHTML = "";

  if (cart.length === 0) {
    cartContainer.innerHTML = `
            <div class="empty-cart">

                <i class="fa-solid fa-cart-shopping"></i>

                <h3>Your cart is empty</h3>

                <p>
                    Add products from the catalog.
                </p>

            </div>
        `;

    cartCount.textContent = "(0 items)";

    return;
  }

  let totalItems = 0;

  cart.forEach((item, index) => {
    totalItems += item.quantity;

    const itemTotal = item.price * item.quantity;

    const cartItem = document.createElement("div");

    cartItem.className = "cart-item";

    cartItem.innerHTML = `

            <div class="cart-item-image">
                ${
                  item.image
                    ? `<img src="${item.image}" alt="${item.name}">`
                    : `<i class="fa-regular fa-gem"></i>`
                }
            </div>


            <div class="cart-item-info">

                <h4>
                    ${item.name}
                </h4>

                <small>
                    ${item.sku}
                </small>


                <div class="quantity-control">

                    <button
                        onclick="decreaseQuantity(${index})"
                    >
                        <i class="fa-solid fa-minus"></i>
                    </button>


                    <span>
                        ${item.quantity}
                    </span>


                    <button
                        onclick="increaseQuantity(${index})"
                    >
                        <i class="fa-solid fa-plus"></i>
                    </button>

                </div>

            </div>


            <div>

                <div class="cart-item-price">
                    $${itemTotal.toFixed(2)}
                </div>


                <button
                    class="remove-cart-btn"
                    onclick="removeFromCart(${index})"
                    style="
                        border:none;
                        background:none;
                        color:#dc3545;
                        cursor:pointer;
                        margin-top:8px;
                    "
                >
                    <i class="fa-solid fa-trash"></i>
                </button>

            </div>

        `;

    cartContainer.appendChild(cartItem);
  });

  cartCount.textContent = `(${totalItems} ${totalItems === 1 ? "item" : "items"})`;
}

/* ========================================
   INCREASE QUANTITY
======================================== */

function increaseQuantity(index) {
  const item = cart[index];

  if (item.quantity >= item.stock) {
    alert("Not enough stock available.");

    return;
  }

  item.quantity++;

  renderCart();
  calculateTotal();
}

/* ========================================
   DECREASE QUANTITY
======================================== */

function decreaseQuantity(index) {
  const item = cart[index];

  if (item.quantity > 1) {
    item.quantity--;
  } else {
    cart.splice(index, 1);
  }

  renderCart();
  calculateTotal();
}

/* ========================================
   REMOVE FROM CART
======================================== */

function removeFromCart(index) {
  cart.splice(index, 1);

  renderCart();
  calculateTotal();
}

/* ========================================
   CLEAR CART
======================================== */

const clearCartBtn = document.getElementById("clearCartBtn");

if (clearCartBtn) {
  clearCartBtn.addEventListener("click", function () {
    if (cart.length === 0) {
      return;
    }

    const confirmClear = confirm("Are you sure you want to clear the cart?");

    if (confirmClear) {
      cart = [];

      renderCart();
      calculateTotal();
    }
  });
}

/* ========================================
   CALCULATE SUBTOTAL
======================================== */

function calculateSubtotal() {
  let subtotal = 0;

  cart.forEach((item) => {
    subtotal += item.price * item.quantity;
  });

  return subtotal;
}

/* ========================================
   CALCULATE TOTAL
======================================== */

function calculateTotal() {
  const subtotal = calculateSubtotal();

  /* --------------------------------
       Manual Discount
    -------------------------------- */

  const discountInput = document.getElementById("discount");

  const discountType = document.getElementById("discountType");

  let discount = 0;

  if (discountInput) {
    const discountValue = parseFloat(discountInput.value) || 0;

    if (discountType && discountType.value === "percent") {
      discount = subtotal * (discountValue / 100);
    } else {
      discount = discountValue;
    }
  }

  /* --------------------------------
       Membership Discount
    -------------------------------- */

  const membership = document.getElementById("membership");

  let membershipDiscount = 0;

  if (membership) {
    const membershipRate = parseFloat(membership.value) || 0;

    membershipDiscount = (subtotal - discount) * (membershipRate / 100);
  }

  /* --------------------------------
       Amount Before Tax
    -------------------------------- */

  const taxableAmount = Math.max(subtotal - discount - membershipDiscount, 0);

  /* --------------------------------
       Tax
    -------------------------------- */

  const tax = taxableAmount * TAX_RATE;

  /* --------------------------------
       Final Total
    -------------------------------- */

  const total = taxableAmount + tax;

  /* --------------------------------
       Update UI
    -------------------------------- */

  updateAmount("subtotal", subtotal);

  updateAmount("membershipDiscount", membershipDiscount, true);

  updateAmount("tax", tax);

  updateAmount("total", total);

  /* Discount display */

  const discountDisplay = document.getElementById("discount");
}

/* ========================================
   UPDATE MONEY DISPLAY
======================================== */

function updateAmount(elementId, amount, negative = false) {
  const element = document.getElementById(elementId);

  if (!element) return;

  if (negative && amount > 0) {
    element.textContent = `-$${amount.toFixed(2)}`;
  } else if (negative) {
    element.textContent = "-$0.00";
  } else {
    element.textContent = `$${amount.toFixed(2)}`;
  }
}

/* ========================================
   DISCOUNT EVENT
======================================== */

const discountInput = document.getElementById("discount");

const discountType = document.getElementById("discountType");

if (discountInput) {
  discountInput.addEventListener("input", calculateTotal);
}

if (discountType) {
  discountType.addEventListener("change", calculateTotal);
}

/* ========================================
   MEMBERSHIP EVENT
======================================== */

const membership = document.getElementById("membership");

if (membership) {
  membership.addEventListener("change", calculateTotal);
}

/* ========================================
   SEARCH PRODUCTS
======================================== */

const productSearch = document.getElementById("productSearch");

if (productSearch) {
  productSearch.addEventListener("input", filterProducts);
}

/* ========================================
   CATEGORY FILTER
======================================== */

const categoryFilter = document.getElementById("categoryFilter");

if (categoryFilter) {
  categoryFilter.addEventListener("change", filterProducts);
}

/* ========================================
   FILTER PRODUCTS
======================================== */

function filterProducts() {
  const searchValue = productSearch
    ? productSearch.value.toLowerCase().trim()
    : "";

  const selectedCategory = categoryFilter ? categoryFilter.value : "";

  const products = document.querySelectorAll(".product-item");

  products.forEach((product) => {
    const name = product.dataset.name.toLowerCase();

    const sku = product.dataset.sku.toLowerCase();

    const category = product.dataset.category;

    const matchesSearch =
      name.includes(searchValue) || sku.includes(searchValue);

    const matchesCategory =
      selectedCategory === "" || category === selectedCategory;

    if (matchesSearch && matchesCategory) {
      product.style.display = "";
    } else {
      product.style.display = "none";
    }
  });
}

/* ========================================
   CATEGORY BUTTONS
======================================== */

const categoryButtons = document.querySelectorAll(".category-btn");

categoryButtons.forEach((button) => {
  button.addEventListener("click", function () {
    categoryButtons.forEach((btn) => {
      btn.classList.remove("active");
    });

    this.classList.add("active");

    const category = this.dataset.category;

    if (categoryFilter) {
      categoryFilter.value = category;
    }

    filterProducts();
  });
});

/* ========================================
   PAYMENT METHOD
======================================== */

const paymentButtons = document.querySelectorAll(".payment-btn");

paymentButtons.forEach((button) => {
  button.addEventListener("click", function () {
    paymentButtons.forEach((btn) => {
      btn.classList.remove("active");
    });

    this.classList.add("active");
  });
});

/* ========================================
   NEW SALE
======================================== */

const newSaleBtn = document.getElementById("newSaleBtn");

if (newSaleBtn) {
  newSaleBtn.addEventListener("click", function () {
    // Confirm if current sale has products
    if (cart.length > 0) {
      const confirmNewSale = confirm(
        "Start a new sale? Current cart will be cleared.",
      );

      if (!confirmNewSale) {
        return;
      }
    }

    // Clear cart
    cart = [];

    // Reset cart UI
    renderCart();

    // Reset discount
    if (discountInput) {
      discountInput.value = 0;
    }

    if (discountType) {
      discountType.value = "percent";
    }

    // Reset membership
    if (membership) {
      membership.value = "0";
    }

    // Reset customer
    const customerSelect = document.getElementById("customerSelect");

    if (customerSelect) {
      customerSelect.value = "";
    }

    // Reset payment method to Cash
    const paymentButtons = document.querySelectorAll(".payment-btn");

    paymentButtons.forEach((button) => {
      button.classList.remove("active");
    });

    const cashButton = document.querySelector(
      '.payment-btn[data-payment="cash"]',
    );

    if (cashButton) {
      cashButton.classList.add("active");
    }

    // Recalculate total
    calculateTotal();

    // Show confirmation
    alert("New sale started successfully.");
  });
}

/* ========================================
   GENERATE INVOICE - PREVIEW ONLY
======================================== */

const generateInvoiceBtn = document.getElementById("generateInvoiceBtn");

if (generateInvoiceBtn) {
  generateInvoiceBtn.addEventListener("click", function (event) {
    event.preventDefault();

    if (cart.length === 0) {
      alert("Please add at least one product to the cart.");
      return;
    }

    // Customer
    const customerSelect = document.getElementById("customerSelect");

    let customerName = "Walk-in Customer";
    let customerID = "";

    if (customerSelect && customerSelect.value !== "") {
      customerID = customerSelect.value;

      customerName = customerSelect.options[customerSelect.selectedIndex].text;
    }

    // Discount
    const discountInput = document.getElementById("discount");
    const discountType = document.getElementById("discountType");

    const discountValue = Math.max(0, parseFloat(discountInput?.value) || 0);

    const subtotal = calculateSubtotal();

    let discount = 0;

    if (discountType?.value === "percent") {
      discount = subtotal * (discountValue / 100);
    } else {
      discount = discountValue;
    }

    discount = Math.min(Math.max(discount, 0), subtotal);

    // Membership
    const membershipSelect = document.getElementById("membership");
    const membershipRate = Math.max(
      0,
      parseFloat(membershipSelect?.value) || 0,
    );

    const eligibleAmount = Math.max(subtotal - discount, 0);

    let membershipDiscount = 0;

    if (eligibleAmount >= (currentMembership?.minimumSpend || 0)) {
      membershipDiscount = eligibleAmount * (membershipRate / 100);
    }

    const taxableAmount = Math.max(eligibleAmount - membershipDiscount, 0);

    const tax = taxableAmount * TAX_RATE;
    const total = taxableAmount + tax;

    // Payment method
    const activePayment = document.querySelector(".payment-btn.active");

    let paymentMethod = "Cash";

    if (activePayment) {
      const paymentLabel = activePayment.querySelector("span");

      if (paymentLabel) {
        paymentMethod = paymentLabel.textContent.trim();
      } else {
        paymentMethod = activePayment.dataset.payment || "Cash";
      }
    }

    // Create a temporary invoice preview.
    // This does NOT save a sale or change stock.
    const invoiceNumber = "PREVIEW-" + Date.now();

    const invoiceData = {
      invoiceNumber,
      saleID: "",
      customerID,
      date: new Date().toLocaleDateString("en-GB"),
      customer: customerName,
      payment: paymentMethod,
      membership: membershipRate,
      discount: Number(discount.toFixed(2)),
      membershipDiscount: Number(membershipDiscount.toFixed(2)),
      subtotal: Number(subtotal.toFixed(2)),
      tax: Number(tax.toFixed(2)),
      total: Number(total.toFixed(2)),
      status: "Preview",
      items: cart.map((item) => ({
        name: item.name,
        sku: item.sku,
        quantity: item.quantity,
        price: item.price,
        itemTotal: Number((item.price * item.quantity).toFixed(2)),
      })),
    };

    try {
      localStorage.setItem("currentInvoice", JSON.stringify(invoiceData));

      window.location.href = "invoice.php";
    } catch (error) {
      console.error("Generate Invoice error:", error);

      alert("Unable to prepare the invoice preview.");
    }
  });
}

/* ========================================
   COMPLETE PAYMENT
======================================== */

const completePaymentBtn = document.getElementById("completePaymentBtn");

function saveCompletedSale(invoice) {
  try {
    const savedHistory = localStorage.getItem("salesHistory");
    const history = savedHistory === null ? [] : JSON.parse(savedHistory);

    if (!Array.isArray(history)) {
      throw new Error("Sales history has an invalid format.");
    }

    const updatedHistory = history.filter(
      (sale) => sale && sale.invoiceNumber !== invoice.invoiceNumber,
    );
    updatedHistory.push(invoice);
    localStorage.setItem("salesHistory", JSON.stringify(updatedHistory));
    return true;
  } catch (error) {
    console.error("Unable to save completed sale to report history:", error);
    return false;
  }
}

if (completePaymentBtn) {
  completePaymentBtn.addEventListener("click", async function () {
    /* Check cart */
    if (cart.length === 0) {
      alert("Please add at least one product to the cart.");

      return;
    }

    /* Get payment method */
    const activePayment = document.querySelector(".payment-btn.active");

    let paymentMethod = "Cash";

    if (activePayment) {
      paymentMethod = activePayment.querySelector("span").textContent.trim();
    }

    /* Get customer */
    const customerSelect = document.getElementById("customerSelect");

    let customerName = "Walk-in Customer";

    if (customerSelect && customerSelect.value !== "") {
      customerName = customerSelect.options[customerSelect.selectedIndex].text;
    }

    /* Calculate total */
    const subtotal = calculateSubtotal();

    const discountInput = document.getElementById("discount");

    const discountType = document.getElementById("discountType");

    let discountValue = 0;

    if (discountInput) {
      discountValue = parseFloat(discountInput.value) || 0;
    }

    let discount = 0;

    if (discountType && discountType.value === "percent") {
      discount = subtotal * (discountValue / 100);
    } else {
      discount = discountValue;
    }

    /* Membership */
    const membership = document.getElementById("membership");

    let membershipRate = 0;

    if (membership) {
      membershipRate = parseFloat(membership.value) || 0;
    }

    const membershipDiscount =
      Math.max(subtotal - discount, 0) * (membershipRate / 100);

    /* Tax */
    const taxableAmount = Math.max(subtotal - discount - membershipDiscount, 0);

    const tax = taxableAmount * TAX_RATE;

    /* Final total */
    const total = taxableAmount + tax;

    /* Confirm payment */
    const confirmed = confirm(
      `Complete payment of $${total.toFixed(2)} using ${paymentMethod}?`,
    );

    if (!confirmed) {
      return;
    }

    /* Create invoice number */
    const invoiceNumber = "INV-" + Date.now();

    /* Prepare payment data */
    const formData = new FormData();

    formData.append("invoiceNumber", invoiceNumber);

    // Customer ID
    const sentCustomerID = customerSelect ? customerSelect.value : "";

    alert("CustomerID sent to database = [" + sentCustomerID + "]");

    formData.append("customerID", sentCustomerID);

    // Payment method
    formData.append("paymentMethod", paymentMethod);

    // Cart items
    formData.append(
      "items",
      JSON.stringify(
        cart.map((item) => ({
          sku: item.sku,
          quantity: item.quantity,
        })),
      ),
    );

    // Discount
    formData.append("discountValue", discountValue.toString());

    formData.append(
      "discountType",
      discountType ? discountType.value : "percent",
    );

    // Membership
    formData.append("membershipRate", membershipRate.toString());

    try {
      /* Send payment to PHP */
      const response = await fetch("ajax/payment.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      /* Check result */
      if (!result.success) {
        alert(result.message || "Payment failed.");

        return;
      }

      /* Create invoice data */

      /* Create invoice data using PHP-calculated amounts */
      const serverSale = result.data;

      if (!serverSale) {
        alert(
          "Payment was saved, but the server did not return sale details. Please check the database before retrying.",
        );
        return;
      }

      const invoiceData = {
        invoiceNumber: serverSale.invoiceNumber || invoiceNumber,
        saleID: serverSale.saleID,
        date: new Date().toLocaleDateString("en-GB"),
        customer: customerName,
        payment: serverSale.paymentMethod || paymentMethod,
        membership: membershipRate,
        discount: Number(serverSale.discount) || 0,
        membershipDiscount: Number(serverSale.membershipDiscount) || 0,
        subtotal: Number(serverSale.subtotal) || 0,
        tax: Number(serverSale.tax) || 0,
        total: Number(serverSale.total) || 0,
        status: "Paid",
        items: cart.map((item) => ({
          name: item.name,
          sku: item.sku,
          quantity: item.quantity,
          price: item.price,
          itemTotal: item.price * item.quantity,
        })),
      };

      /* Save invoice */
      localStorage.setItem("currentInvoice", JSON.stringify(invoiceData));

      /* Save payment status */
      localStorage.setItem("paymentStatus", "Paid");

      const reportHistorySaved = saveCompletedSale(invoiceData);

      /* Success */
      alert(
        reportHistorySaved
          ? "Payment completed successfully!"
          : "Payment completed, but the sales report history could not be updated.",
      );

      /* Go to receipt */
      window.location.href = `receipt.php?sale_id=${encodeURIComponent(serverSale.saleID)}`;
    } catch (error) {
      console.error("Payment Error:", error);

      alert("Unable to process payment.");
    }
  });
}

/* ========================================
   INITIALIZE
======================================== */

renderCart();
calculateTotal();

/* ========================================
   NEW CUSTOMER
======================================== */

const newCustomerBtn = document.getElementById("newCustomerBtn");

const customerModal = document.getElementById("customerModal");

const closeCustomerModal = document.getElementById("closeCustomerModal");

const cancelCustomerBtn = document.getElementById("cancelCustomerBtn");

const customerModalOverlay = document.getElementById("customerModalOverlay");

const newCustomerForm = document.getElementById("newCustomerForm");

const customerSelect = document.getElementById("customerSelect");

/* ========================================
   LOAD CUSTOMER MEMBERSHIP FROM DATABASE
======================================== */

const membershipSelect = document.getElementById("membership");

let currentMembership = {
  rate: 0,
  minimumSpend: 0,
  name: "No Membership",
};

async function loadCustomerMembership(customerID) {
  if (!membershipSelect) return;

  if (!customerID) {
    currentMembership = {
      rate: 0,
      minimumSpend: 0,
      name: "No Membership",
    };

    membershipSelect.innerHTML =
      '<option value="0">Select a customer first</option>';
    membershipSelect.disabled = true;
    calculateTotal();
    return;
  }

  membershipSelect.disabled = true;
  membershipSelect.innerHTML =
    '<option value="0">Loading membership...</option>';

  try {
    const response = await fetch(
      `ajax/membership.php?customer_id=${encodeURIComponent(customerID)}`,
      {
        headers: { Accept: "application/json" },
      },
    );

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || "Unable to load membership.");
    }

    const customer = result.customer;

    currentMembership = {
      rate: Number(customer.DiscountRate) || 0,
      minimumSpend: Number(customer.MinimumSpend) || 0,
      name: customer.MembershipName || "No Membership",
    };

    membershipSelect.innerHTML = "";

    const option = document.createElement("option");
    option.value = String(currentMembership.rate);
    option.textContent = `${currentMembership.name} - ${currentMembership.rate}%`;

    membershipSelect.appendChild(option);
    membershipSelect.value = String(currentMembership.rate);
    membershipSelect.disabled = false;

    calculateTotal();
  } catch (error) {
    currentMembership = {
      rate: 0,
      minimumSpend: 0,
      name: "No Membership",
    };

    membershipSelect.innerHTML =
      '<option value="0">Membership unavailable</option>';
    membershipSelect.disabled = true;

    calculateTotal();
    console.error("Membership loading error:", error);
    alert(error.message || "Unable to load membership.");
  }
}

/* Listen for customer changes */
if (customerSelect) {
  customerSelect.addEventListener("change", function () {
    loadCustomerMembership(this.value);
  });

  if (customerSelect.value) {
    loadCustomerMembership(customerSelect.value);
  }
}

/* =========================
   OPEN MODAL
========================= */

if (newCustomerBtn && customerModal) {
  newCustomerBtn.addEventListener("click", function () {
    customerModal.classList.add("show");

    const nameInput = document.getElementById("newCustomerName");

    if (nameInput) {
      setTimeout(() => nameInput.focus(), 100);
    }
  });
}

/* =========================
   CLOSE MODAL
========================= */

function closeCustomerModalBox() {
  if (customerModal) {
    customerModal.classList.remove("show");
  }
}

/* =========================
   CLOSE EVENTS
========================= */

if (closeCustomerModal) {
  closeCustomerModal.addEventListener("click", closeCustomerModalBox);
}

if (cancelCustomerBtn) {
  cancelCustomerBtn.addEventListener("click", closeCustomerModalBox);
}

if (customerModalOverlay) {
  customerModalOverlay.addEventListener("click", closeCustomerModalBox);
}

/* =========================
   SAVE CUSTOMER
========================= */

if (newCustomerForm) {
  newCustomerForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const saveButton = document.getElementById("saveCustomerBtn");

    const formData = new FormData(newCustomerForm);

    /* Disable button */

    if (saveButton) {
      saveButton.disabled = true;

      saveButton.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    }

    try {
      const response = await fetch(
        "/Jewelry-app/sales/ajax/create_customer.php",
        {
          method: "POST",
          body: formData,
        },
      );

      const responseText = await response.text();

      console.log("CREATE CUSTOMER STATUS:", response.status);
      console.log("CREATE CUSTOMER RESPONSE:", responseText);

      let result;

      try {
        result = JSON.parse(responseText);
      } catch (error) {
        alert("Create Customer Error:\n\n" + responseText);
        return;
      }

      if (!result.success) {
        alert(result.message || "Unable to create customer.");

        return;
      }

      /* =========================
                   ADD NEW CUSTOMER TO SELECT
                ========================= */

      if (customerSelect) {
        const option = document.createElement("option");

        option.value = result.customer.CustomerID;

        option.textContent = result.customer.CustomerName;

        customerSelect.appendChild(option);

        /* Automatically select */

        customerSelect.value = result.customer.CustomerID;
      }

      /* =========================
                   RESET FORM
                ========================= */

      newCustomerForm.reset();

      /* =========================
                   CLOSE MODAL
                ========================= */

      closeCustomerModalBox();

      /* =========================
                   SUCCESS
                ========================= */

      alert(
        "Customer created successfully!\n\n" +
          "Customer ID: " +
          result.customer.CustomerID,
      );
    } catch (error) {
      console.error("Create Customer Error:", error);

      alert("Unable to create customer.");
    } finally {
      if (saveButton) {
        saveButton.disabled = false;

        saveButton.innerHTML =
          '<i class="fa-solid fa-check"></i> Save Customer';
      }
    }
  });
}
