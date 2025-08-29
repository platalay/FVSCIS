<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');

try {
    if (empty($_POST['FvSanitationCertificationOld'])) {
        throw new Exception('ไม่มีข้อมูลฟอร์ม');
    }

    $attrs = $_POST['FvSanitationCertificationOld'];

    // ถ้าคลาสคุณมีเมธอด sanitize/merge_attributes ใช้ได้เลย
    $obj = new FvSanitationCertificationOld($attrs);

    // ตัวอย่าง: ถ้าคลาสคุณใช้ $db_columns ตามที่ให้มา save() จะ insert อัตโนมัติ
    if ($obj->save()) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        throw new Exception('บันทึกไม่สำเร็จ');
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
