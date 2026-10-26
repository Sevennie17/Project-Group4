<?php

function app_base_path()
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $applicationRoot = realpath(__DIR__ . '/..');

    if (
        $documentRoot !== false
        && $applicationRoot !== false
        && strncasecmp($applicationRoot, $documentRoot, strlen($documentRoot)) === 0
    ) {
        $relativePath = substr($applicationRoot, strlen($documentRoot));
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        return '/' . trim($relativePath, '/');
    }

    return '/Jewelry-app';
}

function start_app_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => app_base_path() . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_login_page()
{
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return basename(dirname($scriptPath)) === 'usercontrole'
        && basename($scriptPath) === 'index.php';
}

function require_authenticated_user()
{
    start_app_session();

    if (is_login_page()) {
        return;
    }

    if (
        isset($_SESSION['authenticated'], $_SESSION['user']['UserID'])
        && $_SESSION['authenticated'] === true
    ) {
        return;
    }

    if (
        strpos($_SERVER['REQUEST_URI'] ?? '', '/ajax/') !== false
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
    ) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => 'Please sign in to continue.',
        ]);
        exit();
    }

    header('Location: ' . app_base_path() . '/usercontrole/index.php');
    exit();
}
