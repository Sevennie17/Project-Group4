console.log("PRODUCT JS IS WORKING");
/* =========================================================
   PRODUCTS JAVASCRIPT
   Product Management
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  /* =====================================================
       PRODUCT LIST ELEMENTS
    ===================================================== */

  const productSearch = document.getElementById("productSearch");

  const categoryFilter = document.getElementById("categoryFilter");

  const statusFilter = document.getElementById("statusFilter");

  const resetFilters = document.getElementById("resetFilters");

  const productTableBody = document.getElementById("productTableBody");

  const productCount = document.getElementById("productCount");

  const showingCount = document.getElementById("showingCount");

  /* =====================================================
       PRODUCT LIST
    ===================================================== */

  if (productTableBody) {
    const rows = productTableBody.querySelectorAll("tr");

    /* =================================================
           UPDATE PRODUCT COUNT
        ================================================= */

    function updateProductCount() {
      let visibleCount = 0;

      rows.forEach(function (row) {
        if (row.style.display !== "none") {
          visibleCount++;
        }
      });

      /* Product count */

      if (productCount) {
        productCount.textContent =
          visibleCount + (visibleCount === 1 ? " Product" : " Products");
      }

      /* Showing count */

      if (showingCount) {
        showingCount.textContent = visibleCount;
      }
    }

    /* =================================================
           FILTER PRODUCTS
        ================================================= */

    function filterProducts() {
      const keyword = productSearch
        ? productSearch.value.toLowerCase().trim()
        : "";

      const selectedCategory = categoryFilter
        ? categoryFilter.value.toLowerCase().trim()
        : "";

      const selectedStatus = statusFilter
        ? statusFilter.value.toLowerCase().trim()
        : "";

      rows.forEach(function (row) {
        /* -----------------------------------------
                   GET DATA FROM <tr>
                ----------------------------------------- */

        const searchText = (row.dataset.search || "").toLowerCase();

        const category = (row.dataset.category || "").toLowerCase();

        const status = (row.dataset.status || "").toLowerCase();

        /* -----------------------------------------
                   CHECK SEARCH
                ----------------------------------------- */

        const matchesSearch = keyword === "" || searchText.includes(keyword);

        /* -----------------------------------------
                   CHECK CATEGORY
                ----------------------------------------- */

        const matchesCategory =
          selectedCategory === "" || category === selectedCategory;

        /* -----------------------------------------
                   CHECK STATUS
                ----------------------------------------- */

        const matchesStatus =
          selectedStatus === "" || status === selectedStatus;

        /* -----------------------------------------
                   SHOW / HIDE ROW
                ----------------------------------------- */

        if (matchesSearch && matchesCategory && matchesStatus) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });

      /* Update count */

      updateProductCount();
    }

    /* =================================================
           SEARCH
        ================================================= */

    if (productSearch) {
      productSearch.addEventListener("input", filterProducts);
    }

    /* =================================================
           CATEGORY FILTER
        ================================================= */

    if (categoryFilter) {
      categoryFilter.addEventListener("change", filterProducts);
    }

    /* =================================================
           STATUS FILTER
        ================================================= */

    if (statusFilter) {
      statusFilter.addEventListener("change", filterProducts);
    }

    /* =================================================
           RESET FILTERS
        ================================================= */

    if (resetFilters) {
      resetFilters.addEventListener("click", function (event) {
        event.preventDefault();

        // Clear search
        if (productSearch) {
          productSearch.value = "";
        }

        // Reset category
        if (categoryFilter) {
          categoryFilter.selectedIndex = 0;
        }

        // Reset status
        if (statusFilter) {
          statusFilter.selectedIndex = 0;
        }

        // Show all products
        rows.forEach(function (row) {
          row.style.display = "";
        });

        // Update product count
        updateProductCount();
      });
    }

    /* =================================================
           DELETE CONFIRMATION
        ================================================= */

    productTableBody.addEventListener("click", function (event) {
      const deleteButton = event.target.closest(".action-btn.delete");

      if (!deleteButton) {
        return;
      }

      const confirmed = confirm(
        "Are you sure you want to delete this product?",
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });

    /* =================================================
           INITIAL COUNT
        ================================================= */

    updateProductCount();
  }

  /* =====================================================
       ADD PRODUCT - IMAGE PREVIEW
    ===================================================== */

  const productImage = document.getElementById("productImage");

  const imagePreview = document.getElementById("imagePreview");

  if (productImage && imagePreview) {
    productImage.addEventListener("change", function () {
      const file = productImage.files[0];

      if (!file) {
        imagePreview.innerHTML = `
                        <i class="fa-regular fa-image"></i>
                        <span>No image selected</span>
                    `;

        return;
      }

      /* Check image */

      if (!file.type.startsWith("image/")) {
        alert("Please select a valid image file.");

        productImage.value = "";

        return;
      }

      const reader = new FileReader();

      reader.addEventListener("load", function (event) {
        imagePreview.innerHTML = `
                            <img
                                src="${event.target.result}"
                                alt="Product Preview"
                            >
                        `;
      });

      reader.readAsDataURL(file);
    });
  }

  /* =====================================================
       ADD PRODUCT - BARCODE GENERATOR
    ===================================================== */

  const generateBarcode = document.getElementById("generateBarcode");

  const barcodeInput = document.getElementById("barcode");

  if (generateBarcode && barcodeInput) {
    function createBarcode() {
      const randomBarcode =
        "885" + Math.floor(100000000 + Math.random() * 900000000);

      barcodeInput.value = randomBarcode;
    }

    // Auto generate when Add Product page opens
    if (barcodeInput.value.trim() === "") {
      createBarcode();
    }

    // Generate another barcode when button is clicked
    generateBarcode.addEventListener("click", createBarcode);
  }

  /* =====================================================
   QR CODE GENERATOR
===================================================== */

  const generateQRCode = document.getElementById("generateQRCode");
  const qrCode = document.getElementById("qrCode");

  if (generateQRCode && qrCode) {
    generateQRCode.addEventListener("click", function () {
      qrCode.innerHTML = "";

      const productName =
        document.querySelector('[name="product_name"]')?.value || "Product";

      const sku = document.querySelector('[name="sku"]')?.value || "N/A";

      const barcode =
        document.querySelector('[name="barcode"]')?.value || "N/A";

      const qrProductName = document.getElementById("qrProductName");

      const qrProductSku = document.getElementById("qrProductSku");

      if (qrProductName) {
        qrProductName.textContent = productName;
      }

      if (qrProductSku) {
        qrProductSku.textContent = "SKU: " + sku;
      }

      const qrData =
        "Product: " + productName + "\nSKU: " + sku + "\nBarcode: " + barcode;

      new QRCode(qrCode, {
        text: qrData,
        width: 180,
        height: 180,
        correctLevel: QRCode.CorrectLevel.H,
      });
    });
  }
});
