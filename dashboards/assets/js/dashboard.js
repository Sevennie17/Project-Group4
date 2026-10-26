document.addEventListener("DOMContentLoaded", function () {
  console.log("Dashboard JS Loaded");

  fetch("/Jewelry-app/dashboards/ajax/dashboard-data.php")
    .then((response) => {
      console.log("API Status:", response.status);

      if (!response.ok) {
        throw new Error("Dashboard API error: " + response.status);
      }

      return response.json();
    })

    .then((result) => {
      console.log("Dashboard API:", result);

      if (!result.success) {
        console.error(result.message);
        return;
      }

      /* =========================
         STATISTICS
      ========================= */

      const totalProducts = document.getElementById("totalProducts");
      const availableInventory = document.getElementById("availableInventory");
      const lowStock = document.getElementById("lowStock");

      if (totalProducts) {
        totalProducts.textContent = result.data.totalProducts;
      }

      if (availableInventory) {
        availableInventory.textContent = result.data.availableInventory;
      }

      if (lowStock) {
        lowStock.textContent = result.data.lowStock;
      }

      /* =========================
         RAW DATA
      ========================= */

      const revenueRows = result.data.revenue || [];
      const salesRows = result.data.sales || [];

      /* =========================
         CREATE LAST 6 MONTHS
         INCLUDING ZERO MONTHS
      ========================= */

      const months = [];

      const today = new Date();

      for (let i = 5; i >= 0; i--) {
        const date = new Date(today.getFullYear(), today.getMonth() - i, 1);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");

        months.push(`${year}-${month}`);
      }

      /* =========================
         DISPLAY LABELS
      ========================= */

      const monthLabels = months.map((month) => {
        const [year, monthNumber] = month.split("-");

        const date = new Date(Number(year), Number(monthNumber) - 1, 1);

        return date.toLocaleDateString("en-US", {
          month: "short",
          year: "numeric",
        });
      });

      /* =========================
         PREPARE REVENUE DATA
      ========================= */

      const revenueData = months.map((month) => {
        const row = revenueRows.find((item) => item.month === month);

        return row ? Number(row.revenue) : 0;
      });

      /* =========================
         PREPARE SALES DATA
      ========================= */

      const salesData = months.map((month) => {
        const row = salesRows.find((item) => item.month === month);

        return row ? Number(row.sales) : 0;
      });

      /* =========================
         REVENUE CHART
      ========================= */

      const revenueCanvas = document.getElementById("revenueChart");

      if (revenueCanvas && typeof Chart !== "undefined") {
        new Chart(revenueCanvas, {
          type: "line",

          data: {
            labels: monthLabels,

            datasets: [
              {
                label: "Revenue",

                data: revenueData,

                borderColor: "#c9a227",

                backgroundColor: "rgba(201, 162, 39, 0.12)",

                borderWidth: 2,

                fill: true,

                tension: 0.35,

                pointRadius: 4,

                pointHoverRadius: 6,

                pointBackgroundColor: "#c9a227",

                pointBorderColor: "#ffffff",

                pointBorderWidth: 2,
              },
            ],
          },

          options: {
            responsive: true,

            maintainAspectRatio: false,

            interaction: {
              intersect: false,

              mode: "index",
            },

            plugins: {
              legend: {
                display: true,

                position: "top",

                labels: {
                  usePointStyle: true,

                  padding: 18,
                },
              },

              tooltip: {
                callbacks: {
                  label: function (context) {
                    const value = Number(context.raw || 0);

                    return (
                      " Revenue: $" +
                      value.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                      })
                    );
                  },
                },
              },
            },

            scales: {
              x: {
                grid: {
                  display: false,
                },

                ticks: {
                  color: "#777",

                  font: {
                    size: 12,
                  },
                },
              },

              y: {
                beginAtZero: true,

                ticks: {
                  color: "#777",

                  font: {
                    size: 12,
                  },
                },

                grid: {
                  color: "#eeeeee",
                },
              },
            },
          },
        });
      }

      /* =========================
         SALES CHART
      ========================= */

      const salesCanvas = document.getElementById("salesChart");

      if (salesCanvas && typeof Chart !== "undefined") {
        new Chart(salesCanvas, {
          type: "line",

          data: {
            labels: monthLabels,

            datasets: [
              {
                label: "Sales",

                data: salesData,

                borderColor: "#c9a227",

                backgroundColor: "rgba(201, 162, 39, 0.10)",

                borderWidth: 3,

                fill: true,

                tension: 0.35,

                pointRadius: 5,

                pointHoverRadius: 7,

                pointBackgroundColor: "#c9a227",

                pointBorderColor: "#ffffff",

                pointBorderWidth: 2,
              },
            ],
          },

          options: {
            responsive: true,

            maintainAspectRatio: false,

            interaction: {
              intersect: false,

              mode: "index",
            },

            plugins: {
              legend: {
                display: true,

                position: "top",

                labels: {
                  usePointStyle: true,

                  padding: 18,
                },
              },

              tooltip: {
                callbacks: {
                  label: function (context) {
                    return " Sales: " + context.raw;
                  },
                },
              },
            },

            scales: {
              x: {
                grid: {
                  display: false,
                },

                ticks: {
                  color: "#777",

                  font: {
                    size: 12,
                  },
                },
              },

              y: {
                beginAtZero: true,

                ticks: {
                  precision: 0,

                  color: "#777",

                  font: {
                    size: 12,
                  },
                },

                grid: {
                  color: "#eeeeee",
                },
              },
            },
          },
        });
      }
    })

    .catch((error) => {
      console.error("Dashboard Error:", error);
    });
});
