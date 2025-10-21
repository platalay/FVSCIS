<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');

try {
    if (empty($_POST['FvSanitationCertificationOld'])) {
        throw new Exception('ไม่มีข้อมูลฟอร์ม');
    }
    // Debug payload (ดูได้ใน php_error_log)

    $attrs = $_POST['FvSanitationCertificationOld'];

    // บันทึกใบรับรอง (เหมือนเดิมที่ใช้งานได้)
    $obj = new FvSanitationCertificationOld($attrs);
    $obj->type = 0;

    if (!$obj->save()) {
        throw new Exception('บันทึกไม่สำเร็จ');
    }

    // ===== แนบไฟล์หลายไฟล์ (ใหม่) =====
    $certificate_id = $obj->id ?? null;
    $files_saved = 0; 
    $files_failed = 0; 
    $file_errors = [];

    if (!empty($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        // นามสกุลที่อนุญาต
        $allow_ext = ['jpg','jpeg','png','gif','webp','pdf'];

        // รองรับทั้ง method user_id() และ property user_id
        $currentUserId = null;
        if (isset($session)) {
            if (is_object($session) && method_exists($session, 'user_id')) $currentUserId = $session->user_id();
            elseif (isset($session->user_id)) $currentUserId = $session->user_id;
        }

        foreach ($_FILES['attachments']['name'] as $i => $name) {
            $file = [
                'name'     => $_FILES['attachments']['name'][$i],
                'type'     => $_FILES['attachments']['type'][$i] ?? '',
                'tmp_name' => $_FILES['attachments']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['attachments']['size'][$i] ?? 0,
            ];

            // ตรวจนามสกุล
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allow_ext, true)) {
                $files_failed++; 
                $file_errors[] = "ไฟล์ {$file['name']} ไม่รองรับ";
                continue;
            }

            // บันทึกไฟล์ (โฟลเดอร์ /uploads/certificationold/ ถูกกำหนดในคลาส FvCertificateAttachment)
            $ok = FvCertificateAttachment::create_from_upload($certificate_id, $file, $currentUserId);
            if ($ok) $files_saved++; else { $files_failed++; $file_errors[] = "อัปโหลดไฟล์ {$file['name']} ไม่สำเร็จ"; }
        }
    }

    echo json_encode([
        'success'        => true,
        'certificate_id' => $certificate_id,
        'files_saved'    => $files_saved,
        'files_failed'   => $files_failed,
        'errors'         => $file_errors
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
