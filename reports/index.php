<?php
$page_css = '/Jewelry-app/reports/reports.css';
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();
include __DIR__ . '/../includes/header.php';
?>

<div class="reports-page">
    <header class="reports-header">
        <div>
            <span class="reports-eyebrow"><i class="fa-solid fa-chart-line"></i> STORE INSIGHTS</span>
            <h1>Reports</h1>
            <p>Understand sales performance, popular products, and payment trends.</p>
        </div>
        <div class="reports-header-actions">
            <label class="reports-period-control" for="report-period">
                <i class="fa-regular fa-calendar"></i>
                <select id="report-period" aria-label="Report date range">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="all">All time</option>
                </select>
            </label>
            <div class="reports-export-menu">
                <button
                    class="reports-export-button"
                    id="export-report"
                    type="button"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="export-options"
                >
                    <i class="fa-solid fa-download"></i>
                    Export report
                    <i class="fa-solid fa-chevron-down reports-export-chevron"></i>
                </button>
                <div class="reports-export-options" id="export-options" role="menu" hidden>
                    <button type="button" role="menuitem" data-export-format="csv">
                        <i class="fa-solid fa-file-csv"></i>
                        <span>CSV spreadsheet</span>
                    </button>
                    <button type="button" role="menuitem" data-export-format="json">
                        <i class="fa-solid fa-file-code"></i>
                        <span>JSON data</span>
                    </button>
                    <button type="button" role="menuitem" data-export-format="pdf">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Print / Save as PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="reports-data-note" role="note">
        <i class="fa-solid fa-circle-info"></i>
        Sales reports are based on completed sales saved in this browser. They are not yet shared across devices or users.
    </div>

    <section class="reports-kpis" aria-label="Sales summary">
        <article class="reports-kpi">
            <div class="reports-kpi-icon reports-kpi-icon-gold"><i class="fa-solid fa-sack-dollar"></i></div>
            <div>
                <span>Total sales</span>
                <strong id="report-revenue">$0.00</strong>
                <small id="report-revenue-caption">For selected period</small>
            </div>
        </article>
        <article class="reports-kpi">
            <div class="reports-kpi-icon reports-kpi-icon-green"><i class="fa-solid fa-receipt"></i></div>
            <div>
                <span>Transactions</span>
                <strong id="report-count">0</strong>
                <small>Completed payments</small>
            </div>
        </article>
        <article class="reports-kpi">
            <div class="reports-kpi-icon reports-kpi-icon-blue"><i class="fa-solid fa-basket-shopping"></i></div>
            <div>
                <span>Average sale</span>
                <strong id="report-average">$0.00</strong>
                <small>Revenue per transaction</small>
            </div>
        </article>
        <article class="reports-kpi">
            <div class="reports-kpi-icon reports-kpi-icon-rose"><i class="fa-solid fa-gem"></i></div>
            <div>
                <span>Items sold</span>
                <strong id="report-items">0</strong>
                <small>Units in selected period</small>
            </div>
        </article>
    </section>

    <section class="reports-main-grid">
        <article class="reports-card reports-sales-card">
            <div class="reports-card-header">
                <div>
                    <h2>Sales over time</h2>
                    <p>Daily, weekly, or monthly revenue trend</p>
                </div>
                <span class="reports-card-icon"><i class="fa-solid fa-chart-column"></i></span>
            </div>
            <div class="reports-chart" id="sales-chart" role="img" aria-label="Sales revenue chart"></div>
        </article>

        <article class="reports-card reports-payments-card">
            <div class="reports-card-header">
                <div>
                    <h2>Payment methods</h2>
                    <p>Completed sales by payment type</p>
                </div>
                <span class="reports-card-icon"><i class="fa-solid fa-credit-card"></i></span>
            </div>
            <div class="reports-payment-list" id="payment-breakdown"></div>
        </article>
    </section>

    <section class="reports-bottom-grid">
        <article class="reports-card">
            <div class="reports-card-header">
                <div>
                    <h2>Top-selling items</h2>
                    <p>Ranked by units sold</p>
                </div>
                <span class="reports-card-icon"><i class="fa-solid fa-ranking-star"></i></span>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Product</th><th>Units</th><th>Sales</th></tr>
                    </thead>
                    <tbody id="top-products">
                        <tr><td colspan="3" class="reports-empty">No completed sales in this period.</td></tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="reports-card reports-recent-card">
            <div class="reports-card-header">
                <div>
                    <h2>Recent transactions</h2>
                    <p>Latest completed payments</p>
                </div>
                <span class="reports-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            </div>
            <div class="reports-table-wrap">
                <table class="reports-table">
                    <thead>
                        <tr><th>Invoice</th><th>Customer</th><th>Total</th></tr>
                    </thead>
                    <tbody id="recent-sales">
                        <tr><td colspan="3" class="reports-empty">No completed sales saved in this browser.</td></tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</div>

<script src="<?php echo htmlspecialchars(app_base_path() . '/reports/reports.js', ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
