<?php

/**
 * FVSCIS Database / Application Configuration Loader
 *
 * ค่าที่แตกต่างกันในแต่ละเครื่อง เช่น
 * - BASE_URL
 * - MySQL port
 * - Database username/password
 * - External database credentials
 * - LINE / Google / Facebook credentials
 *
 * จะเก็บไว้ใน config.local.php
 *
 * IMPORTANT:
 * private/config.local.php ต้องไม่ถูก commit ขึ้น Git
 */

$config_file = __DIR__ . '/config.local.php';

if (!file_exists($config_file)) {
    die('Configuration error: private/config.local.php not found.');
}

$config = require $config_file;

if (!is_array($config)) {
    die('Configuration error: private/config.local.php must return an array.');
}

foreach ($config as $key => $value) {
    if (!defined($key)) {
        define($key, $value);
    }
}

// ตรวจสอบค่าหลักที่ FVSCIS จำเป็นต้องใช้
$required_config = [
    'BASE_URL',

    'DB_SERVER',
    'DB_PORT',
    'DB_USER',
    'DB_PASS',
    'DB_NAME',

    'DB_SERVER_FI',
    'DB_PORT_FI',
    'DB_USER_FI',
    'DB_PASS_FI',
    'DB_NAME_FI',

    'DB_SERVER_EL',
    'DB_PORT_EL',
    'DB_USER_EL',
    'DB_PASS_EL',
    'DB_NAME_EL',
];

foreach ($required_config as $key) {
    if (!defined($key)) {
        die('Configuration error: missing ' . $key . ' in private/config.local.php');
    }
}
?>