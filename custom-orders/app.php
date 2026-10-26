<?php

/**
 * Custom Jewelry Orders
 * Application Helper
 * PHP 7.3 Compatible
 */

if (!defined('CUSTOM_ORDER_NAME')) {
    define('CUSTOM_ORDER_NAME', 'Custom Jewelry Order');
}

if (!defined('CUSTOM_ORDER_UPLOAD_DIR')) {
    define(
        'CUSTOM_ORDER_UPLOAD_DIR',
        __DIR__ . '/uploads/designs/'
    );
}

if (!defined('CUSTOM_ORDER_UPLOAD_URL')) {
    define(
        'CUSTOM_ORDER_UPLOAD_URL',
        'uploads/designs/'
    );
}


/* =========================
   ORDER STATUSES
========================= */

function customOrderStatuses()
{
    return array(
        'Pending',
        'Designing',
        'Manufacturing',
        'Quality Inspection',
        'Ready for Pickup',
        'Delivered',
        'Cancelled'
    );
}


/* =========================
   STATUS CLASS
========================= */

function customOrderStatusClass($status)
{
    switch ($status) {

        case 'Pending':
            return 'status-pending';

        case 'Designing':
            return 'status-designing';

        case 'Manufacturing':
            return 'status-manufacturing';

        case 'Quality Inspection':
            return 'status-quality';

        case 'Ready for Pickup':
            return 'status-ready';

        case 'Delivered':
            return 'status-delivered';

        case 'Cancelled':
            return 'status-cancelled';

        default:
            return 'status-default';
    }
}


/* =========================
   STATUS ICON
========================= */

function customOrderStatusIcon($status)
{
    switch ($status) {

        case 'Pending':
            return 'fa-clock';

        case 'Designing':
            return 'fa-pen-ruler';

        case 'Manufacturing':
            return 'fa-hammer';

        case 'Quality Inspection':
            return 'fa-magnifying-glass';

        case 'Ready for Pickup':
            return 'fa-box';

        case 'Delivered':
            return 'fa-circle-check';

        case 'Cancelled':
            return 'fa-circle-xmark';

        default:
            return 'fa-circle';
    }
}


/* =========================
   ESCAPE HTML
========================= */

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================
   GENERATE ORDER NUMBER
========================= */

function generateCustomOrderNumber($number)
{
    return 'CO-' . str_pad(
        $number,
        3,
        '0',
        STR_PAD_LEFT
    );
}


/* =========================
   CREATE UPLOAD DIRECTORY
========================= */

function createCustomOrderUploadDirectory()
{
    if (!is_dir(CUSTOM_ORDER_UPLOAD_DIR)) {

        mkdir(
            CUSTOM_ORDER_UPLOAD_DIR,
            0777,
            true
        );
    }

    return CUSTOM_ORDER_UPLOAD_DIR;
}


/* =========================
   CHECK IMAGE
========================= */

function isAllowedDesignImage($fileName)
{
    $allowed = array(
        'jpg',
        'jpeg',
        'png',
        'webp'
    );

    $extension = strtolower(
        pathinfo(
            $fileName,
            PATHINFO_EXTENSION
        )
    );

    return in_array(
        $extension,
        $allowed
    );
}


/* =========================
   FORMAT DATE
========================= */

function formatCustomOrderDate($date)
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    return date(
        'd M Y',
        $timestamp
    );
}


/* =========================
   VALIDATE STATUS
========================= */

function isValidCustomOrderStatus($status)
{
    return in_array(
        $status,
        customOrderStatuses()
    );
}


/* =========================
   STATUS BADGE
========================= */

function customOrderStatusBadge($status)
{
    $class = customOrderStatusClass($status);

    $icon = customOrderStatusIcon($status);

    return '
        <span class="status-badge ' . e($class) . '">
            <i class="fa-solid ' . e($icon) . '"></i>
            ' . e($status) . '
        </span>
    ';
}


/* =========================
   DEFAULT CUSTOM ORDER
========================= */

function defaultCustomOrder()
{
    return array(
        'id' => '',
        'customer' => '',
        'phone' => '',
        'description' => '',
        'due_date' => '',
        'delivery_date' => '',
        'goldsmith' => '',
        'design_image' => '',
        'status' => 'Pending'
    );
}