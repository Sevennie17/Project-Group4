(function () {
  "use strict";

  const currency = new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  });

  const dateFormat = new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
  });

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, function (character) {
      return {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
      }[character];
    });
  }

  function parseSaleDate(value) {
    if (typeof value !== "string" || value.trim() === "") {
      return null;
    }

    const trimmed = value.trim();
    let match = trimmed.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
    if (match) {
      const date = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
      return Number.isNaN(date.getTime()) ? null : date;
    }

    match = trimmed.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (match) {
      const date = new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
      return Number.isNaN(date.getTime()) ? null : date;
    }

    return null;
  }

  function readSales() {
    const rawHistory = localStorage.getItem("salesHistory");
    let sales = [];

    if (rawHistory !== null) {
      sales = JSON.parse(rawHistory);
      if (!Array.isArray(sales)) {
        throw new Error("Sales history has an invalid format.");
      }
    }

    const currentInvoice = localStorage.getItem("currentInvoice");
    if (currentInvoice !== null) {
      const invoice = JSON.parse(currentInvoice);
      if (
        invoice &&
        typeof invoice === "object" &&
        String(invoice.status || "").toLowerCase() === "paid" &&
        !sales.some((sale) => sale.invoiceNumber === invoice.invoiceNumber)
      ) {
        sales.push(invoice);
      }
    }

    return sales.filter(function (sale) {
      return (
        sale &&
        typeof sale === "object" &&
        typeof sale.invoiceNumber === "string" &&
        Number.isFinite(Number(sale.total)) &&
        parseSaleDate(sale.date) !== null
      );
    });
  }

  function filterSales(sales, period) {
    if (period === "all") {
      return sales;
    }

    const days = Number(period);
    const startDate = new Date();
    startDate.setHours(0, 0, 0, 0);
    startDate.setDate(startDate.getDate() - days + 1);

    return sales.filter(function (sale) {
      const saleDate = parseSaleDate(sale.date);
      return saleDate >= startDate && saleDate <= new Date();
    });
  }

  function renderChart(sales, period) {
    const chart = document.getElementById("sales-chart");
    const useMonths = period === "all";
    const useWeeks = !useMonths && Number(period) > 14;
    const bucketCount = useMonths ? 12 : useWeeks ? Math.ceil(Number(period) / 7) : Number(period);
    const buckets = [];
    const now = new Date();

    for (let offset = bucketCount - 1; offset >= 0; offset -= 1) {
      const date = new Date(now);
      if (useMonths) {
        date.setDate(1);
        date.setMonth(date.getMonth() - offset);
      } else if (useWeeks) {
        date.setDate(date.getDate() - offset * 7);
        date.setHours(0, 0, 0, 0);
        const weekday = (date.getDay() + 6) % 7;
        date.setDate(date.getDate() - weekday);
      } else {
        date.setDate(date.getDate() - offset);
        date.setHours(0, 0, 0, 0);
      }

      buckets.push({
        date,
        amount: 0,
        label: useMonths
          ? new Intl.DateTimeFormat("en-US", { month: "short", year: "2-digit" }).format(date)
          : dateFormat.format(date),
      });
    }

    sales.forEach(function (sale) {
      const saleDate = parseSaleDate(sale.date);
      let bucketDate = new Date(saleDate);
      bucketDate.setHours(0, 0, 0, 0);
      if (useMonths) {
        bucketDate.setDate(1);
      } else if (useWeeks) {
        const weekday = (bucketDate.getDay() + 6) % 7;
        bucketDate.setDate(bucketDate.getDate() - weekday);
      }

      const bucket = buckets.find(
        (item) => item.date.getTime() === bucketDate.getTime(),
      );
      if (bucket) {
        bucket.amount += Number(sale.total);
      }
    });

    const maxAmount = Math.max(...buckets.map((bucket) => bucket.amount), 0);
    if (maxAmount === 0) {
      chart.innerHTML = '<div class="reports-chart-empty">Sales activity will appear here after completed payments.</div>';
      return;
    }

    chart.innerHTML = buckets
      .map(function (bucket) {
        const height = bucket.amount > 0 ? Math.max((bucket.amount / maxAmount) * 100, 4) : 2;
        return `
          <div class="reports-chart-column" title="${escapeHtml(bucket.label)}: ${currency.format(bucket.amount)}">
            <div class="reports-chart-bar-wrap">
              <div class="reports-chart-bar" style="height:${height}%"></div>
            </div>
            <span class="reports-chart-label">${escapeHtml(bucket.label)}</span>
          </div>`;
      })
      .join("");
  }

  function renderPayments(sales) {
    const container = document.getElementById("payment-breakdown");
    const totals = new Map();

    sales.forEach(function (sale) {
      const method = typeof sale.payment === "string" && sale.payment.trim()
        ? sale.payment.trim()
        : "Other";
      totals.set(method, (totals.get(method) || 0) + Number(sale.total));
    });

    const methods = Array.from(totals.entries()).sort((a, b) => b[1] - a[1]);
    const total = methods.reduce((sum, method) => sum + method[1], 0);

    if (methods.length === 0) {
      container.innerHTML = '<div class="reports-chart-empty">Payment breakdown will appear after completed sales.</div>';
      return;
    }

    container.innerHTML = methods
      .map(function ([method, amount]) {
        const percentage = total > 0 ? (amount / total) * 100 : 0;
        return `
          <div class="reports-payment-row">
            <div class="reports-payment-label">
              <span>${escapeHtml(method)}</span>
              <strong>${currency.format(amount)}</strong>
            </div>
            <div class="reports-payment-track">
              <div class="reports-payment-fill" style="width:${percentage}%"></div>
            </div>
          </div>`;
      })
      .join("");
  }

  function renderProducts(sales) {
    const body = document.getElementById("top-products");
    const products = new Map();

    sales.forEach(function (sale) {
      if (!Array.isArray(sale.items)) {
        return;
      }

      sale.items.forEach(function (item) {
        const name = typeof item.name === "string" && item.name.trim()
          ? item.name.trim()
          : "Unnamed product";
        const current = products.get(name) || { quantity: 0, amount: 0 };
        current.quantity += Math.max(Number(item.quantity) || 0, 0);
        current.amount += Number(item.itemTotal) || (Number(item.price) || 0) * (Number(item.quantity) || 0);
        products.set(name, current);
      });
    });

    const ranked = Array.from(products.entries())
      .sort((a, b) => b[1].quantity - a[1].quantity)
      .slice(0, 5);

    body.innerHTML = ranked.length
      ? ranked.map(([name, values]) => `
          <tr>
            <td>${escapeHtml(name)}</td>
            <td>${values.quantity}</td>
            <td>${currency.format(values.amount)}</td>
          </tr>`).join("")
      : '<tr><td colspan="3" class="reports-empty">No product sales in this period.</td></tr>';
  }

  function renderRecentSales(sales) {
    const body = document.getElementById("recent-sales");
    const recentSales = [...sales]
      .sort((a, b) => parseSaleDate(b.date) - parseSaleDate(a.date))
      .slice(0, 5);

    body.innerHTML = recentSales.length
      ? recentSales.map((sale) => `
          <tr>
            <td class="reports-invoice">${escapeHtml(sale.invoiceNumber)}</td>
            <td>${escapeHtml(sale.customer || "Walk-in Customer")}</td>
            <td>${currency.format(Number(sale.total))}</td>
          </tr>`).join("")
      : '<tr><td colspan="3" class="reports-empty">No completed sales saved in this browser.</td></tr>';
  }

  function renderReport() {
    const period = document.getElementById("report-period").value;
    const sales = filterSales(readSales(), period);
    const revenue = sales.reduce((sum, sale) => sum + Number(sale.total), 0);
    const itemCount = sales.reduce(
      (sum, sale) =>
        sum +
        (Array.isArray(sale.items)
          ? sale.items.reduce((count, item) => count + (Number(item.quantity) || 0), 0)
          : 0),
      0,
    );

    document.getElementById("report-revenue").textContent = currency.format(revenue);
    document.getElementById("report-count").textContent = String(sales.length);
    document.getElementById("report-average").textContent = currency.format(
      sales.length > 0 ? revenue / sales.length : 0,
    );
    document.getElementById("report-items").textContent = String(itemCount);

    renderChart(sales, period);
    renderPayments(sales);
    renderProducts(sales);
    renderRecentSales(sales);
  }

  function getReportData() {
    const sales = filterSales(readSales(), document.getElementById("report-period").value);
    const revenue = sales.reduce((sum, sale) => sum + Number(sale.total), 0);
    const itemCount = sales.reduce(
      (sum, sale) =>
        sum +
        (Array.isArray(sale.items)
          ? sale.items.reduce((count, item) => count + (Number(item.quantity) || 0), 0)
          : 0),
      0,
    );

    return {
      sales,
      summary: {
        totalSales: revenue,
        transactions: sales.length,
        averageSale: sales.length > 0 ? revenue / sales.length : 0,
        itemsSold: itemCount,
      },
      period: document.getElementById("report-period").value,
    };
  }

  function downloadFile(content, type, filename) {
    const url = URL.createObjectURL(new Blob([content], { type }));
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 1000);
  }

  function exportCsv(sales) {
    const rows = [
      ["Invoice", "Date", "Customer", "Payment method", "Total", "Items"],
      ...sales.map((sale) => [
        sale.invoiceNumber,
        sale.date,
        sale.customer || "Walk-in Customer",
        sale.payment || "Other",
        Number(sale.total).toFixed(2),
        Array.isArray(sale.items)
          ? sale.items.map((item) => `${item.name || "Unnamed product"} x${Number(item.quantity) || 0}`).join("; ")
          : "",
      ]),
    ];
    const csv = rows
      .map((row) => row.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(","))
      .join("\r\n");
    downloadFile(`\uFEFF${csv}`, "text/csv;charset=utf-8", "jewelry-sales-report.csv");
  }

  function exportJson(reportData) {
    const output = {
      report: "Jewelry Shop Sales",
      generatedAt: new Date().toISOString(),
      dateRange: reportData.period,
      summary: reportData.summary,
      transactions: reportData.sales,
    };
    downloadFile(
      JSON.stringify(output, null, 2),
      "application/json;charset=utf-8",
      "jewelry-sales-report.json",
    );
  }

  function closeExportMenu() {
    const button = document.getElementById("export-report");
    const options = document.getElementById("export-options");
    options.hidden = true;
    button.setAttribute("aria-expanded", "false");
  }

  function handleExport(format) {
    try {
      const reportData = getReportData();
      if (format === "csv") {
        exportCsv(reportData.sales);
      } else if (format === "json") {
        exportJson(reportData);
      } else if (format === "pdf") {
        window.print();
      }
    } catch (error) {
      console.error("Unable to export sales report:", error);
      window.alert("The report could not be exported. Check browser storage and try again.");
    }
    closeExportMenu();
  }

  document.getElementById("report-period").addEventListener("change", renderReport);
  const exportButton = document.getElementById("export-report");
  const exportOptions = document.getElementById("export-options");

  exportButton.addEventListener("click", function () {
    const isExpanded = exportButton.getAttribute("aria-expanded") === "true";
    exportOptions.hidden = isExpanded;
    exportButton.setAttribute("aria-expanded", String(!isExpanded));
  });

  exportOptions.querySelectorAll("[data-export-format]").forEach(function (option) {
    option.addEventListener("click", function () {
      handleExport(option.dataset.exportFormat);
    });
  });

  document.addEventListener("click", function (event) {
    if (!event.target.closest(".reports-export-menu")) {
      closeExportMenu();
    }
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      closeExportMenu();
      exportButton.focus();
    }
  });

  try {
    renderReport();
  } catch (error) {
    console.error("Unable to load sales report:", error);
    document.getElementById("report-revenue").textContent = "Unavailable";
    document.getElementById("report-count").textContent = "—";
    document.getElementById("report-average").textContent = "—";
    document.getElementById("report-items").textContent = "—";
    document.getElementById("sales-chart").innerHTML =
      '<div class="reports-chart-empty">Sales report data could not be read. Check browser storage and try again.</div>';
    document.getElementById("payment-breakdown").innerHTML =
      '<div class="reports-chart-empty">Payment report data could not be read.</div>';
  }
})();
