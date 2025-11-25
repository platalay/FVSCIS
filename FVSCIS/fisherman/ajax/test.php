<?php
// เปิดให้ log แน่นอน
ini_set('log_errors', 1);
ini_set('error_log', 'C:\xampp\php\logs\php_error_log');
error_reporting(E_ALL);

// ทดสอบเขียน log
//error_log("[TEST] Hello from error_log");

echo "Log sent.";
