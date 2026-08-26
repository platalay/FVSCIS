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

    // Fix: department scope must reflect the authenticated officer, not a client-controlled hidden field.
    // This preserves the intended authorization rule: certificate.evaluation_agency == officer.departments_id.
    $currentOfficer = Officer::find_by_id($session->user_id());
    if ($currentOfficer && isset($currentOfficer->departments_id) && $currentOfficer->departments_id !== null && $currentOfficer->departments_id !== '') {
        $attrs['evaluation_agency'] = (int)$currentOfficer->departments_id;
    }

    // map สถานะ
    $attrs['status'] = ($attrs['certificate_status'] == 'ไม่ผ่าน') ? 'fail' : 'active';

    if (!isset($attrs['type']) || $attrs['type'] === '' || $attrs['type'] === null) {
        $attrs['type'] = 0; // ค่า default ของตาราง (tinyint)
    }

    // -----------------------------
    // 1.1) Single-Active Guard (เฉพาะกรณีใบใหม่จะเป็น active) — ต้องเช็คก่อน insert เสมอ
    // -----------------------------
    $ship_code_for_check = trim((string)($attrs['ship_code'] ?? ''));
    $old_to_deactivate_id = null;

    if ($attrs['status'] === 'active' && $ship_code_for_check !== '') {
        $existing_active = FvSanitationCertificationOld::find_all_active_unexpired_by_ship_code($ship_code_for_check);
        $existing_count   = count($existing_active);

        // พบ active+ยังไม่หมดอายุมากกว่า 1 ใบ -> data inconsistency ห้าม insert ต่อโดยเด็ดขาด
        if ($existing_count > 1) {
            if (method_exists($database, 'rollback')) { $database->rollback(); } else { $database->autocommit(true); }
            echo json_encode([
                'success'       => false,
                'inconsistency' => true,
                'message'       => 'พบใบรับรองที่ยังใช้งานอยู่มากกว่า 1 รายการ กรุณาตรวจสอบข้อมูลก่อนบันทึกใบรับรองใหม่',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // พบ active+ยังไม่หมดอายุ 1 ใบ -> ต้องได้รับการยืนยันจากเจ้าหน้าที่ก่อนเท่านั้น
        if ($existing_count === 1) {
            $confirmed = !empty($_POST['confirm_replace_active']);
            if (!$confirmed) {
                $old = $existing_active[0];
                if (method_exists($database, 'rollback')) { $database->rollback(); } else { $database->autocommit(true); }
                echo json_encode([
                    'success'             => false,
                    'need_confirmation'   => true,
                    'existing_certificate' => [
                        'id'                 => $old->id,
                        'certificate_number' => $old->certificate_number,
                        'effective_date'     => $old->effective_date,
                        'expiration_date'    => $old->expiration_date,
                    ],
                    'message' => 'พบใบรับรองเดิมที่ยังไม่หมดอายุ กรุณายืนยันก่อนบันทึกใบรับรองใหม่',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // ยืนยันแล้ว -> จำ id ของใบเดิมไว้ ปิดเป็น inactive หลัง insert ใบใหม่สำเร็จ
            $old_to_deactivate_id = (int)$existing_active[0]->id;
        }
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
    } elseif ($old_to_deactivate_id !== null) {
        // Single-Active Rule: ใบใหม่ที่เพิ่ง insert สำเร็จ = active อยู่แล้ว (ตามค่า status ที่ส่งเข้ามา)
        // ปิดใบเดิมที่ active อยู่ก่อนหน้าเป็น inactive เท่านั้น ไม่ใช้ mark_active() เลือกใบจากวันหมดอายุไกลที่สุด
        FvSanitationCertificationOld::deactivate_other_active($cert->ship_code, (int)$cert->id);
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
                if (!InspectionLog::create_manual_certificate_audit(
                    'fvscis_attachment_added',
                    $cert_id,
                    'เพิ่มไฟล์แนบใบรับรอง Manual',
                    null,
                    [
                        'attachment_id' => (int)$att->id,
                        'attachment_type' => $att->attachment_type,
                        'file_name' => $att->file_name,
                    ]
                )) {
                    throw new Exception('บันทึกประวัติไฟล์แนบไม่สำเร็จ');
                }
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
    $audit_values = [
        'vessel_name' => $cert->vessel_name,
        'ship_code' => $cert->ship_code,
        'certificate_number' => $cert->certificate_number,
        'effective_date' => $cert->effective_date,
        'expiration_date' => $cert->expiration_date,
        'certificate_status' => $cert->certificate_status,
        'status' => $cert->status,
    ];
    if (!InspectionLog::create_manual_certificate_audit(
        'fvscis_created_by_officer',
        $cert->id,
        "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ " . $cert->vessel_name,
        null,
        $audit_values
    )) {
        throw new Exception('บันทึกประวัติใบรับรองไม่สำเร็จ');
    }

    $created_action = LogAction::find_by_code('fvscis_created_by_officer');
    $message  = "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ " . $cert->vessel_name;
    $officers = Officer::find_by_department_id($cert->evaluation_agency);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $cert->id,
            $created_action->id,
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

