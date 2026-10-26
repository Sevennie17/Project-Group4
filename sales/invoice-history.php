<?php
require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

require_once __DIR__ . '/../config/database.php';

$pdo = $pdo ?? ($con ?? null);

if (!$pdo instanceof PDO) {
    exit('Database connection is not available.');
}

$page_css = app_base_path() . '/sales/assets/css/invoice-history.css';

// Search
$search = trim($_GET['q'] ?? '');

// Pagination
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

try {
    // Count matching invoices
    $countSql = "
        SELECT COUNT(*)
        FROM sales s
        LEFT JOIN customers c
            ON c.CustomerID = s.CustomerID
        WHERE (
            s.SaleID LIKE :searchID
            OR COALESCE(c.CustomerName, 'Walk-in Customer')
                LIKE :searchName
            OR COALESCE(s.CustomerID, '') LIKE :searchCustomer
        )
    ";

    $countStmt = $pdo->prepare($countSql);

    $searchValue = '%' . $search . '%';

    $countStmt->execute([
        'searchID' => $searchValue,
        'searchName' => $searchValue,
        'searchCustomer' => $searchValue
    ]);

    $totalInvoices = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($totalInvoices / $perPage));

    // Keep requested page within range
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    // Load invoices
    $sql = "
        SELECT
            s.SaleID,
            s.SaleDate,
            s.CustomerID,
            s.TotalAmount,
            s.PaymentMethod,
            s.Status,
            COALESCE(c.CustomerName, 'Walk-in Customer')
                AS CustomerName
        FROM sales s
        LEFT JOIN customers c
            ON c.CustomerID = s.CustomerID
        WHERE (
            s.SaleID LIKE :searchID
            OR COALESCE(c.CustomerName, 'Walk-in Customer')
                LIKE :searchName
            OR COALESCE(s.CustomerID, '') LIKE :searchCustomer
        )
        ORDER BY s.SaleDate DESC, s.SaleID DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':searchID', $searchValue, PDO::PARAM_STR);
    $stmt->bindValue(':searchName', $searchValue, PDO::PARAM_STR);
    $stmt->bindValue(':searchCustomer', $searchValue, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Invoice history error: ' . $e->getMessage());
    exit('Unable to load invoice history. Please check the database configuration.');
}

function invoiceEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function invoiceMoney($amount): string
{
    return '$' . number_format((float) $amount, 2);
}

$startNumber = $totalInvoices > 0 ? $offset + 1 : 0;
$endNumber = min($offset + $perPage, $totalInvoices);

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .invoice-history-page {
        padding: 24px;
        max-width: 1500px;
        margin: 0 auto;
    }

    .invoice-history-page .page-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 22px;
    }

    .invoice-history-page h1 {
        font-size: 25px;
        margin: 0 0 7px;
        color: var(--text-color);
    }

    .invoice-history-page .subtitle {
        color: var(--text-muted);
        margin: 0;
        font-size: 14px;
    }

    .invoice-history-page .history-card {
        background: var(--primary-color);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .invoice-history-page .history-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        padding: 18px;
        border-bottom: 1px solid var(--border-color);
    }

    .invoice-history-page .search-form {
        display: flex;
        flex: 1;
        max-width: 560px;
        gap: 8px;
    }

    .invoice-history-page .search-form input {
        flex: 1;
        min-width: 0;
        padding: 11px 13px;
        border: 1px solid var(--border-color);
        border-radius: 7px;
        outline: none;
        background: var(--primary-color);
        color: var(--text-color);
    }

    .invoice-history-page .search-form input:focus {
        border-color: var(--gold-color);
    }

    .invoice-history-page .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        padding: 10px 13px;
        border: 1px solid var(--border-color);
        border-radius: 7px;
        background: var(--primary-color);
        color: var(--text-color);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
    }

    .invoice-history-page .btn-gold {
        background: var(--gold-color);
        border-color: var(--gold-color);
        color: #fff;
    }

    .invoice-history-page .btn:hover {
        opacity: .88;
    }

    .invoice-history-page .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .invoice-history-page table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    .invoice-history-page th,
    .invoice-history-page td {
        padding: 14px 16px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
        vertical-align: middle;
    }

    .invoice-history-page th {
        background: var(--hover-color);
        color: var(--text-color);
        font-weight: 700;
        white-space: nowrap;
    }

    .invoice-history-page tbody tr:hover {
        background: var(--secondary-color);
    }

    .invoice-history-page .invoice-id {
        color: var(--text-color);
        font-weight: 700;
    }

    .invoice-history-page .customer-cell {
        min-width: 150px;
    }

    .invoice-history-page .customer-id {
        display: block;
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 11px;
    }

    .invoice-history-page .amount {
        font-weight: 700;
        white-space: nowrap;
    }

    .invoice-history-page .status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        background: var(--hover-color);
        color: var(--text-color);
        font-size: 11px;
        font-weight: 700;
    }

    .invoice-history-page .actions {
        display: flex;
        gap: 6px;
    }

    .invoice-history-page .actions .btn {
        padding: 8px 10px;
    }

    .invoice-history-page .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        padding: 16px 18px;
    }

    .invoice-history-page .muted {
        color: var(--text-muted);
        font-size: 12px;
    }

    .invoice-history-page .pagination {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .invoice-history-page .pagination .current {
        background: var(--gold-color);
        color: #fff;
        border-color: var(--gold-color);
    }

    .invoice-history-page .empty-state {
        padding: 45px 15px;
        text-align: center;
        color: var(--text-muted);
    }

    .invoice-history-page .empty-state i {
        font-size: 32px;
        color: var(--gold-color);
        margin-bottom: 12px;
    }

    @media (max-width: 650px) {
        .invoice-history-page {
            padding: 14px;
        }

        .invoice-history-page .history-toolbar {
            align-items: stretch;
        }

        .invoice-history-page .search-form {
            max-width: none;
            flex-wrap: wrap;
        }

        .invoice-history-page .search-form input {
            flex-basis: 100%;
        }

        .invoice-history-page .page-heading h1 {
            font-size: 22px;
        }
    }
</style>

<div class="invoice-history-page">

    <div class="page-heading">
        <div>
            <h1>
                <i class="fa-solid fa-file-invoice-dollar"></i>
                Invoice History
            </h1>
            <p class="subtitle">
                Search and view previous sales invoices.
            </p>
        </div>

        <a href="index.php" class="btn btn-gold">
            <i class="fa-solid fa-plus"></i>
            New Sale
        </a>
    </div>

    <div class="history-card">

        <div class="history-toolbar">

            <form method="GET" action="invoice-history.php"
                class="search-form">

                <input
                    type="search"
                    name="q"
                    value="<?= invoiceEscape($search) ?>"
                    placeholder="Search invoice number, customer name or ID..."
                    aria-label="Search invoices">

                <button type="submit" class="btn btn-gold">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Search
                </button>

                <?php if ($search !== ''): ?>
                    <a href="invoice-history.php" class="btn">
                        Clear
                    </a>
                <?php endif; ?>

            </form>

            <span class="muted">
                <?= number_format($totalInvoices) ?> invoice(s) found
            </span>

        </div>

        <?php if (empty($invoices)): ?>

            <div class="empty-state">
                <i class="fa-solid fa-file-circle-question"></i>
                <h3>No Invoices Found</h3>
                <p>
                    No invoices match your search.
                    Try another invoice number or customer name.
                </p>
            </div>

        <?php else: ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                            $saleID = (string) $invoice['SaleID'];
                            $saleIDUrl = rawurlencode($saleID);
                            ?>

                            <tr>
                                <td>
                                    <span class="invoice-id">
                                        <?= invoiceEscape($saleID) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= invoiceEscape(
                                        date(
                                            'd/m/Y H:i',
                                            strtotime($invoice['SaleDate'])
                                        )
                                    ) ?>
                                </td>

                                <td class="customer-cell">
                                    <?= invoiceEscape($invoice['CustomerName']) ?>

                                    <?php if (!empty($invoice['CustomerID'])): ?>
                                        <span class="customer-id">
                                            ID: <?= invoiceEscape($invoice['CustomerID']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="amount">
                                    <?= invoiceMoney($invoice['TotalAmount']) ?>
                                </td>

                                <td>
                                    <?= invoiceEscape($invoice['PaymentMethod'] ?: '-') ?>
                                </td>

                                <td>
                                    <span class="status">
                                        <?= invoiceEscape($invoice['Status'] ?: 'Unknown') ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="actions">

                                        <a
                                            class="btn"
                                            href="invoice.php?sale_id=<?= $saleIDUrl ?>"
                                            title="View Invoice">
                                            <i class="fa-solid fa-file-invoice"></i>
                                            Invoice
                                        </a>

                                        <a
                                            class="btn"
                                            href="receipt.php?sale_id=<?= $saleIDUrl ?>"
                                            title="View Receipt">
                                            <i class="fa-solid fa-receipt"></i>
                                            Receipt
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-footer">

                <span class="muted">
                    Showing <?= number_format($startNumber) ?>
                    - <?= number_format($endNumber) ?>
                    of <?= number_format($totalInvoices) ?> invoices
                </span>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Invoice pages">

                        <?php if ($page > 1): ?>
                            <a
                                class="btn"
                                href="?<?= http_build_query([
                                            'q' => $search,
                                            'page' => $page - 1
                                        ]) ?>">
                                <i class="fa-solid fa-chevron-left"></i>
                                Previous
                            </a>
                        <?php endif; ?>

                        <?php
                        $firstPage = max(1, $page - 2);
                        $lastPage = min($totalPages, $page + 2);
                        ?>

                        <?php for ($p = $firstPage; $p <= $lastPage; $p++): ?>
                            <a
                                class="btn <?= $p === $page ? 'current' : '' ?>"
                                href="?<?= http_build_query([
                                            'q' => $search,
                                            'page' => $p
                                        ]) ?>"
                                <?= $p === $page ? 'aria-current="page"' : '' ?>>
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a
                                class="btn"
                                href="?<?= http_build_query([
                                            'q' => $search,
                                            'page' => $page + 1
                                        ]) ?>">
                                Next
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>

                    </nav>
                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>