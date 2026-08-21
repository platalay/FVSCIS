<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
    global $database;

    // -----------------------------
    // 0) เริ่มต้น Transaction
    // -----------------------------
    if (method_exists($database, 'begin_transaction')) {
        $database->begin_transaction();
    } else {
        // กรณีใช้ mysqli ปกติ
        $database->autocommit(false);
    }

    if (empty($_POST['FvSanitationCertificationOld'])) {
        throw new Exception('ไม่มีข้อมูลฟอร์ม');
    }

    // -----------------------------
    // 1) เตรียมข้อมูลฟอร์ม
    // -----------------------------
    $attrs = $_POST['FvSanitationCertificationOld'];

    // map สถานะ
    $attrs['status'] = ($attrs['certificate_status'] == 'ไม่ผ่าน') ? 'fail' : 'active';

    if (!isset($attrs['type']) || $attrs['type'] === '' || $attrs['type'] === null) {
        $attrs['type'] = 0; // ค่า default ของตาราง (tinyint)
    }

    // -----------------------------
    // 2) สร้าง record ใหม่
    // -----------------------------
    $cert = new FvSanitationCertificationOld($attrs);

    if (!$cert->save()) {
        throw new Exception('บันทึกข้อมูลใบรับรองไม่สำเร็จ: ' . ($database->error ?? 'ไม่ทราบสาเหตุ'));
    }

    // อัปเดตสถานะรวมของเรือ
    if ($attrs['status'] == 'fail') {
        FvSanitationCertificationOld::mark_fail($cert->ship_code);
    } else {
        FvSanitationCertificationOld::mark_active($cert->ship_code);
    }

    $cert_id = $cert->id;

    // -----------------------------
    // 3) บันทึกไฟล์แนบ
    // -----------------------------
    $files_saved = 0;

    if (!empty($_FILES['attachments']['name'][0])) {

        $types = $_POST['attachment_type'] ?? [];
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        // (ถ้าคุณอยากจำกัดชนิดไฟล์ค่อยเพิ่ม allowed ได้ภายหลัง)
        // ตัวอย่าง: $allowed = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];

        // === folder: /uploads/certificationold/YYYY/CERT_00012345/ ===
        $year      = date('Y');
        $certFolder = 'CERT_' . str_pad((string)$cert_id, 8, '0', STR_PAD_LEFT);

        $baseRelDir = "/uploads/certificationold/{$year}/{$certFolder}";
        $baseAbsDir = rtrim(PUBLIC_PATH, '/\\') . $baseRelDir;

        if (!is_dir($baseAbsDir)) {
            mkdir($baseAbsDir, 0775, true);
        }

        // map mime -> ext กันกรณี ext จากชื่อไฟล์ไม่ตรง/ไม่มี ext
        $mimeToExt = [
            'image/jpeg'       => 'jpg',
            'image/png'        => 'png',
            'image/gif'        => 'gif',
            'image/webp'       => 'webp',
            'application/pdf'  => 'pdf',
        ];

        foreach ($_FILES['attachments']['name'] as $i => $origName) {

            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp_name = $_FILES['attachments']['tmp_name'][$i];
            $mime     = $finfo->file($tmp_name) ?: 'application/octet-stream';
            $size     = (int)$_FILES['attachments']['size'][$i];

            // ext จากชื่อไฟล์ (อาจว่าง/เพี้ยน)
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            // ถ้า ext ว่าง/ไม่ชัวร์ ให้ใช้จาก mime แทน
            if ($ext === '' || !in_array($ext, ['jpg','jpeg','png','gif','webp','pdf'], true)) {
                $ext = $mimeToExt[$mime] ?? 'bin';
            }
            if ($ext === 'jpeg') $ext = 'jpg';

            // ตั้งชื่อไฟล์ใหม่กันชน
            $safeName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            $target_path = $baseAbsDir . '/' . $safeName;
            if (!move_uploaded_file($tmp_name, $target_path)) {
                continue;
            }

            $type = $types[$i] ?? '';

            // บันทึกลงฐานข้อมูล (file_path เก็บเป็น /uploads/... ให้สอดคล้อง)
            $att = new FvCertificateAttachment([
                'certificate_id'  => $cert_id,
                'file_name'       => $origName,
                'stored_name'     => $safeName,
                'file_type'       => $mime,
                'file_size'       => $size,
                'attachment_type' => $type,
                'file_path'       => $baseRelDir . '/' . $safeName,
            ]);

            if ($att->save()) {
                $files_saved++;
            } else {
                // ถ้า save ไม่ได้ จะปล่อยไฟล์ค้างไหม?
                // ถ้าไม่อยากให้ค้าง: @unlink($target_path);
                continue;
            }
        }
    }


    // -----------------------------
    // 4) log + แจ้งเตือน (ย่อ)
    // -----------------------------
    $action = LogAction::find_by_code('fvscis_created_by_officer');
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id = $cert->id;
        $log->action_id             = $action->id;
        $log->note                  = "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ " . $cert->vessel_name;
        $log->save();
    }

    $message  = "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ " . $cert->vessel_name;
    $officers = Officer::find_by_department_id($cert->evaluation_agency);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $cert->id,
            $action->id ?? null,
            $message,
            'warning'
        );
    }

    // -----------------------------
    // 5) COMMIT ถ้าทุกอย่างผ่าน
    // -----------------------------
    if (method_exists($database, 'commit')) {
        $database->commit();
    } else {
        $database->autocommit(true);
    }

    echo json_encode([
        'success'     => true,
        'cert_id'     => $cert_id,
        'files_saved' => $files_saved,
    ]);
    exit;

} catch (Throwable $e) {

    // -----------------------------
    // Rollback ถ้าเริ่ม Transaction ไปแล้ว
    // -----------------------------
    if (isset($database)) {
        if (method_exists($database, 'rollback')) {
            $database->rollback();
        } else {
            $database->rollback();
            $database->autocommit(true);
        }
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
    exit;
}

