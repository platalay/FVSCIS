<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json; charset=utf-8');

try {

    // -------------------------------
    // 1) รับค่า attachment_id
    // -------------------------------
    $idParam = $_POST['attachment_id'] ?? '';
    if ($idParam === '' || !ctype_digit((string)$idParam)) {
        throw new Exception('missing or invalid attachment id');
    }
    $attach_id = (int)$idParam;

    // -------------------------------
    // 2) ดึงข้อมูลไฟล์
    // -------------------------------
    /** @var InspectionAttachment|null $att */
    $att = InspectionAttachment::find_by_id($attach_id);
    if (!$att) {
        throw new Exception('attachment not found');
    }

    // -------------------------------
    // 3) ลบไฟล์จริงบน server
    // -------------------------------
    $docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $pubPath = rtrim(str_replace('\\','/', defined('PUBLIC_PATH') ? PUBLIC_PATH : $docRoot), '/');

    // path ที่บันทึกไว้ เช่น '/uploads/request/xxx.jpg'
    $filePath = (string)$att->file_path;

    // ถ้าไม่ใช่ URL (เช่น https://) จะพยายามลบไฟล์
    if (!preg_match('~^https?://~i', $filePath)) {

        // realpath กัน directory traversal
        $abs = realpath($pubPath . '/' . ltrim($filePath,'/'));
        $uploadsBase = realpath($pubPath . '/uploads');

        // ต้องอยู่ในโฟลเดอร์ uploads เท่านั้น
        if (
            $abs &&
            $uploadsBase &&
            str_starts_with($abs, $uploadsBase) &&
            is_file($abs)
        ) {
            @unlink($abs); // ซ่อน warning ถ้าไฟล์ไม่มีแล้ว
        }
    }

    // -------------------------------
    // 4) ลบจากฐานข้อมูล
    // -------------------------------
    if (!$att->delete()) {
        throw new Exception('delete db failed');
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
