<?php

session_start();

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = trim($_POST['id'] ?? '');

if ($id === '') {
    header('Location: index.php?error=invalid_id');
    exit;
}

try {

    /*
     * Get order first
     * We need DesignImage so we can remove the uploaded image too.
     */
    $order = db_fetch_one(
        "
        SELECT id, CustomOrderID, DesignImage
        FROM custom_orders
        WHERE id = ? OR CustomOrderID = ?
        ",
        [$id, $id]
    );

    if (!$order) {
        header('Location: index.php?error=order_not_found');
        exit;
    }

    /*
     * Delete order
     */
    $stmt = $con->prepare("
        DELETE FROM custom_orders
        WHERE id = ? OR CustomOrderID = ?
    ");

    $stmt->execute([
        $id,
        $id
    ]);

    /*
     * Delete design image from uploads folder
     * only if the database delete succeeded.
     */
    if (
        $stmt->rowCount() > 0 &&
        !empty($order['DesignImage'])
    ) {

        $imagePath = __DIR__ . '/' . $order['DesignImage'];

        if (is_file($imagePath)) {
            @unlink($imagePath);
        }
    }

    header('Location: index.php?success=deleted');
    exit;
} catch (PDOException $e) {

    header('Location: index.php?error=delete_failed');
    exit;
}
