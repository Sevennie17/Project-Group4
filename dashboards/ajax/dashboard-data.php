<?php

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

try {

    /* =========================
       TOTAL PRODUCTS
    ========================= */

    $stmt = $con->prepare("
        SELECT COUNT(*)
        FROM products
    ");

    $stmt->execute();

    $totalProducts = (int) $stmt->fetchColumn();


    /* =========================
       AVAILABLE INVENTORY
    ========================= */

    $stmt = $con->prepare("
        SELECT COALESCE(SUM(p.StockQuantity), 0)
        FROM products p
        INNER JOIN Product_Status s
            ON p.PStatusID = s.PStatusID
        WHERE s.PStatusName = 'Active'
    ");

    $stmt->execute();

    $availableInventory = (int) $stmt->fetchColumn();


    /* =========================
       LOW STOCK
    ========================= */

    $stmt = $con->prepare("
        SELECT COUNT(*)
        FROM products p
        INNER JOIN Product_Status s
            ON p.PStatusID = s.PStatusID
        WHERE p.StockQuantity <= 5
        AND s.PStatusName = 'Active'
    ");

    $stmt->execute();

    $lowStock = (int) $stmt->fetchColumn();


    /* =========================
       MONTHLY REVENUE
    ========================= */

    $stmt = $con->prepare("
        SELECT
            DATE_FORMAT(SaleDate, '%Y-%m') AS month,
            COALESCE(SUM(TotalAmount), 0) AS revenue
        FROM sales
        WHERE SaleDate >= DATE_SUB(
            DATE_FORMAT(CURDATE(), '%Y-%m-01'),
            INTERVAL 5 MONTH
        )
        GROUP BY DATE_FORMAT(SaleDate, '%Y-%m')
        ORDER BY month
    ");

    $stmt->execute();

    $revenueRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /* =========================
       MONTHLY SALES
    ========================= */

    $stmt = $con->prepare("
        SELECT
            DATE_FORMAT(SaleDate, '%Y-%m') AS month,
            COUNT(*) AS sales
        FROM sales
        WHERE SaleDate >= DATE_SUB(
            DATE_FORMAT(CURDATE(), '%Y-%m-01'),
            INTERVAL 5 MONTH
        )
        GROUP BY DATE_FORMAT(SaleDate, '%Y-%m')
        ORDER BY month
    ");

    $stmt->execute();

    $salesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /* =========================
       RESPONSE
    ========================= */

    echo json_encode([
        'success' => true,

        'data' => [
            'totalProducts' => $totalProducts,
            'availableInventory' => $availableInventory,
            'lowStock' => $lowStock,

            'revenue' => $revenueRows,
            'sales' => $salesRows
        ]
    ]);
} catch (PDOException $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
