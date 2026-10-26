<?php
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($con) || !$con instanceof PDO) {
    exit('Database connection is not available.');
}

$basePath = function_exists('app_base_path')
    ? rtrim(app_base_path(), '/')
    : '/Jewelry-app';

function historyEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Search and filters
$search = trim((string)($_GET['search'] ?? ''));
$type = trim((string)($_GET['type'] ?? ''));
$dateFrom = trim((string)($_GET['date_from'] ?? ''));
$dateTo = trim((string)($_GET['date_to'] ?? ''));

$allowedTypes = ['Stock In', 'Stock Out', 'Adjustment'];

if (!in_array($type, $allowedTypes, true)) {
    $type = '';
}

$isValidDate = static function (string $date): bool {
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

    return $parsed !== false && $parsed->format('Y-m-d') === $date;
};

if ($dateFrom !== '' && !$isValidDate($dateFrom)) {
    $dateFrom = '';
}

if ($dateTo !== '' && !$isValidDate($dateTo)) {
    $dateTo = '';
}

$error = '';
$transactions = [];
$totalRecords = 0;
$totalPages = 1;
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));

try {
    // Count filtered transactions
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "(
            t.ProductID LIKE :search_id
            OR p.ProductName LIKE :search_name
            OR p.SKU LIKE :search_sku
            OR t.Reason LIKE :search_reason
            OR t.ReferenceNumber LIKE :search_reference
        )";

        $keyword = '%' . $search . '%';

        $params[':search_id'] = $keyword;
        $params[':search_name'] = $keyword;
        $params[':search_sku'] = $keyword;
        $params[':search_reason'] = $keyword;
        $params[':search_reference'] = $keyword;
    }

    if ($type !== '') {
        $where[] = 't.TransactionType = :type';
        $params[':type'] = $type;
    }

    if ($dateFrom !== '') {
        $where[] = 't.CreatedAt >= :date_from';
        $params[':date_from'] = $dateFrom . ' 00:00:00';
    }

    if ($dateTo !== '') {
        $where[] = 't.CreatedAt < :date_to';
        $params[':date_to'] = (new DateTimeImmutable($dateTo))
            ->modify('+1 day')
            ->format('Y-m-d 00:00:00');
    }

    $whereSql = $where
        ? 'WHERE ' . implode(' AND ', $where)
        : '';

    $countStmt = $con->prepare("
        SELECT COUNT(*)
        FROM inventory_transactions t
        LEFT JOIN Products p ON p.ProductID = t.ProductID
        $whereSql
    ");

    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();

    $totalPages = max(1, (int)ceil($totalRecords / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $historyStmt = $con->prepare("
        SELECT
            t.TransactionID,
            t.ProductID,
            p.ProductName,
            p.SKU,
            t.TransactionType,
            t.Quantity,
            t.PreviousStock,
            t.NewStock,
            t.Reason,
            t.ReferenceNumber,
            t.CreatedAt
        FROM inventory_transactions t
        LEFT JOIN Products p ON p.ProductID = t.ProductID
        $whereSql
        ORDER BY t.CreatedAt DESC, t.TransactionID DESC
        LIMIT :limit OFFSET :offset
    ");

    foreach ($params as $key => $value) {
        $historyStmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $historyStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $historyStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $historyStmt->execute();

    $transactions = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Inventory History error: ' . $e->getMessage());
    $error = 'Unable to load inventory history. Please check your database.';
}

// Preserve filters when changing pages
$queryParams = [
    'search' => $search,
    'type' => $type,
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
];

include __DIR__ . '/../includes/header.php';
?>

<style>
    .history-page {
        padding: 26px;
        color: #2b2b2b;
    }

    .history-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .history-heading h1 {
        margin: 0 0 8px;
        font-size: 26px;
    }

    .history-heading p {
        margin: 0;
        color: #777;
    }

    .history-back {
        color: #9b7917;
        text-decoration: none;
        font-weight: 600;
    }

    .history-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .history-stat {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 19px;
    }

    .history-stat span {
        display: block;
        color: #777;
        font-size: 13px;
    }

    .history-stat strong {
        display: block;
        margin-top: 10px;
        font-size: 25px;
    }

    .history-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 22px;
    }

    .history-card-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .history-card-heading h2 {
        font-size: 18px;
        margin: 0 0 6px;
    }

    .history-card-heading p {
        margin: 0;
        color: #777;
        font-size: 13px;
    }

    .history-filters {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
        margin-bottom: 20px;
    }

    .history-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .history-field label {
        font-size: 12px;
        font-weight: 600;
    }

    .history-field input,
    .history-field select {
        min-width: 0;
        width: 100%;
        box-sizing: border-box;
        padding: 11px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        background: #fff;
    }

    .history-button {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 7px;
        padding: 11px 15px;
        border: 0;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .history-button.primary {
        color: #fff;
        background: #c9a227;
    }

    .history-button.secondary {
        color: #444;
        background: #f3f3f3;
    }

    .history-table-wrap {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1050px;
    }

    .history-table th,
    .history-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
        font-size: 12px;
        vertical-align: middle;
    }

    .history-table th {
        background: #faf9f6;
        color: #777;
        font-weight: 600;
    }

    .history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .history-table td strong {
        font-weight: 600;
    }

    .history-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .history-badge.in {
        background: #e5f5eb;
        color: #17663b;
    }

    .history-badge.out {
        background: #fdeaea;
        color: #a32929;
    }

    .history-badge.adjust {
        background: #fff4db;
        color: #8b6500;
    }

    .history-empty {
        padding: 35px !important;
        text-align: center !important;
        color: #777;
    }

    .history-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .history-pagination {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .history-pagination a,
    .history-pagination span {
        min-width: 34px;
        padding: 8px 10px;
        border: 1px solid #e8e8e8;
        border-radius: 7px;
        text-align: center;
        text-decoration: none;
        color: #444;
        font-size: 12px;
    }

    .history-pagination .active {
        background: #c9a227;
        border-color: #c9a227;
        color: #fff;
    }

    .history-pagination .disabled {
        color: #aaa;
    }

    .history-muted {
        color: #777;
        font-size: 12px;
    }

    .history-alert {
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 18px;
        background: #fdeaea;
        color: #a32929;
    }

    @media (max-width: 1050px) {
        .history-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .history-page {
            padding: 15px;
        }

        .history-stats {
            grid-template-columns: 1fr;
        }

        .history-card {
            padding: 15px;
        }

        .history-filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="history-page">
    <div class="history-heading">
        <div>
            <h1>
                <i class="fa-solid fa-clock-rotate-left"
                    style="color:#c9a227"></i>
                Inventory History
            </h1>
            <p>Track all stock-in, stock-out and adjustment transactions.</p>
        </div>

        <a class="history-back"
            href="<?= historyEscape($basePath) ?>/inventory/index.php">
            <i class="fa-solid fa-arrow-left"></i> Inventory Dashboard
        </a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="history-alert">
            <?= historyEscape($error) ?>
        </div>
    <?php endif; ?>

    <section class="history-stats">
        <div class="history-stat">
            <span>Total Transactions</span>
            <strong><?= number_format($totalRecords) ?></strong>
        </div>

        <div class="history-stat">
            <span>Current Page</span>
            <strong><?= $page ?> / <?= $totalPages ?></strong>
        </div>

        <div class="history-stat">
            <span>Records Per Page</span>
            <strong><?= $perPage ?></strong>
        </div>
    </section>

    <section class="history-card">
        <div class="history-card-heading">
            <div>
                <h2>Transaction Records</h2>
                <p>Search, filter and review inventory movements.</p>
            </div>
        </div>

        <form method="GET" class="history-filters">
            <div class="history-field">
                <label for="search">Search</label>
                <input type="search"
                    id="search"
                    name="search"
                    value="<?= historyEscape($search) ?>"
                    placeholder="Product, SKU, reason or reference">
            </div>

            <div class="history-field">
                <label for="type">Transaction Type</label>
                <select name="type" id="type">
                    <option value="">All Types</option>
                    <?php foreach ($allowedTypes as $allowedType): ?>
                        <option value="<?= historyEscape($allowedType) ?>"
                            <?= $type === $allowedType ? 'selected' : '' ?>>
                            <?= historyEscape($allowedType) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="history-field">
                <label for="date_from">From Date</label>
                <input type="date"
                    name="date_from"
                    id="date_from"
                    value="<?= historyEscape($dateFrom) ?>">
            </div>

            <div class="history-field">
                <label for="date_to">To Date</label>
                <input type="date"
                    name="date_to"
                    id="date_to"
                    value="<?= historyEscape($dateTo) ?>">
            </div>

            <div style="display:flex;gap:7px;flex-wrap:wrap">
                <button class="history-button primary" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Filter
                </button>

                <a class="history-button secondary"
                    href="<?= historyEscape($basePath) ?>/inventory/history.php">
                    Reset
                </a>
            </div>
        </form>

        <div class="history-table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Date &amp; Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Previous Stock</th>
                        <th>New Stock</th>
                        <th>Reason</th>
                        <th>Reference</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($transactions): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <?php
                            $badgeClass = match ($transaction['TransactionType']) {
                                'Stock In' => 'in',
                                'Stock Out' => 'out',
                                default => 'adjust'
                            };
                            ?>
                            <tr>
                                <td>
                                    #<?= (int)$transaction['TransactionID'] ?>
                                </td>

                                <td>
                                    <?= historyEscape(
                                        date(
                                            'd M Y, H:i',
                                            strtotime($transaction['CreatedAt'])
                                        )
                                    ) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= historyEscape(
                                            $transaction['ProductName'] ?? 'Unknown Product'
                                        ) ?>
                                    </strong>
                                    <br>
                                    <span class="history-muted">
                                        <?= historyEscape($transaction['ProductID']) ?>
                                        <?php if (!empty($transaction['SKU'])): ?>
                                            · <?= historyEscape($transaction['SKU']) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="history-badge <?= $badgeClass ?>">
                                        <?= historyEscape($transaction['TransactionType']) ?>
                                    </span>
                                </td>

                                <td><?= number_format((int)$transaction['Quantity']) ?></td>

                                <td><?= number_format((int)$transaction['PreviousStock']) ?></td>

                                <td>
                                    <strong>
                                        <?= number_format((int)$transaction['NewStock']) ?>
                                    </strong>
                                </td>

                                <td><?= historyEscape($transaction['Reason']) ?></td>

                                <td>
                                    <?= historyEscape(
                                        $transaction['ReferenceNumber'] ?: '—'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="history-empty">
                                <i class="fa-regular fa-folder-open"
                                    style="font-size:25px;display:block;margin-bottom:10px"></i>
                                No inventory transactions found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="history-footer">
            <span class="history-muted">
                Showing
                <?= $totalRecords === 0 ? 0 : $offset + 1 ?>
                –
                <?= min($offset + $perPage, $totalRecords) ?>
                of <?= number_format($totalRecords) ?> transactions
            </span>

            <div class="history-pagination">
                <?php
                $previousParams = $queryParams;
                $previousParams['page'] = max(1, $page - 1);

                $nextParams = $queryParams;
                $nextParams['page'] = min($totalPages, $page + 1);
                ?>

                <?php if ($page > 1): ?>
                    <a href="?<?= historyEscape(http_build_query($previousParams)) ?>"
                        aria-label="Previous page">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                <?php endif; ?>

                <span class="active"><?= $page ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= historyEscape(http_build_query($nextParams)) ?>"
                        aria-label="Next page">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>