<?php
require_once('../../private/initialize.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $args = $_POST['Officer'] ?? [];

    $officer = new Officer($args);
    $officer->is_active = 1;
    $officer->is_approved = 0;
    $officer->created_by = $_SESSION['username'];
    $officer->created_at = date('Y-m-d H:i:s');
    $officer->updated_at = date('Y-m-d H:i:s');

    if ($officer->save()) {
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว กรุณารอการอนุมัติเข้าใช้ระบบ']);
    } else {
        $errors = join(', ', $officer->errors);
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $errors]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ไม่อนุญาตให้เข้าถึงโดยตรง']);
}
