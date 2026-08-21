<?php
// ajax/change_Officer_password.php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');

try {
    // ให้เฉพาะ admin ใช้ได้
    $session->require_role(['admin']);

    $id = $_POST['id'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    $id = (int)$id;
    $new_password = trim($new_password);

    if ($id <= 0 || $new_password === '') {
        throw new Exception('ข้อมูลไม่ถูกต้อง');
    }

    if (mb_strlen($new_password) < 6) {
        throw new Exception('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
    }

    $Officer = Officer::find_by_id($id);
    if (!$Officer) {
        throw new Exception('ไม่พบผู้ใช้งาน');
    }

    // เข้ารหัสรหัสผ่านใหม่
    $Officer->password = password_hash($new_password, PASSWORD_DEFAULT);

    if ($Officer->save()) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        throw new Exception('บันทึกข้อมูลไม่สำเร็จ');
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
