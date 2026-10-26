<?php

require_once __DIR__ . '/../includes/auth.php';
require_authenticated_user();

$host = 'localhost';
$dbname = 'luxe_jewels_db';
$username = 'root';
$password = '';

try {

    $con = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $con->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $con->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );
} catch (PDOException $e) {

    die("Database connection failed: " .
        $e->getMessage());
}


/* =========================================
   FETCH MULTIPLE ROWS
========================================= */

function db_fetch_all($sql, $params = [])
{
    global $con;

    $stmt = $con->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/* =========================================
   FETCH ONE ROW
========================================= */

function db_fetch_one($sql, $params = [])
{
    global $con;

    $stmt = $con->prepare($sql);
    $stmt->execute($params);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
}
