<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
try {
    if (empty($_POST['FvSanitationCertificationOld'])) {
        throw new Exception('ไม่มีข้อมูลฟอร์ม');
    }

    $attrs = $_POST['FvSanitationCertificationOld'];
    $id = (int)($attrs['id'] ?? 0);
    if ($id <= 0) throw new Exception('ไม่พบ id');

    /** @var FvSanitationCertificationOld|null $obj */
    $obj = FvSanitationCertificationOld::find_by_id($id);
    if(!$obj) throw new Exception('ไม่พบรายการ');

    // Fix: never trust a client-submitted evaluation_agency value for manual certificate edits.
    // The certificate scope must remain aligned with the authenticated officer's department.
    $currentOfficer = Officer::find_by_id($session->user_id());
    if ($currentOfficer && isset($currentOfficer->departments_id) && $currentOfficer->departments_id !== null && $currentOfficer->departments_id !== '') {
        $attrs['evaluation_agency'] = (int)$currentOfficer->departments_id;
    }

    $audit_fields = [
        'vessel_name', 'ship_code', 'certificate_number', 'request_date',
        'signature_date', 'effective_date', 'expiration_date', 'status',
        'vessel_status', 'evaluation_agency', 'signing_unit', 'temporary_reason',
        'responsible_unit', 'certificate_status', 'remark', 'license_status',
        'license_number', 'gear_type', 'owner_name', 'vessel_mark'
    ];
    $old_values = [];
    foreach ($audit_fields as $field) {
        $old_values[$field] = $obj->$field ?? null;
    }

    // Backend Guard: แก้ไขได้เฉพาะ record ที่เป็น working record จริง (status=active และยังไม่หมดอายุ) เท่านั้น
    // record อื่น (active-แต่หมดอายุ/inactive/fail/pending) ถือเป็นประวัติ ห้ามแก้ไข/ห้ามแนบไฟล์เพิ่มผ่าน endpoint นี้
    if (!FvSanitationCertificationOld::is_active_working($obj->status, $obj->expiration_date)) {
        throw new Exception('รายการนี้ไม่ใช่ใบรับรองที่ใช้งานอยู่ในปัจจุบัน และไม่สามารถแก้ไขหรือลบได้');
    }

    $database->begin_transaction();

    unset($attrs['id']);

    // อัปเดตฟิลด์หลัก
    if(method_exists($obj, 'merge_attributes')){
        $obj->merge_attributes($attrs);
    } else {
        foreach (FvSanitationCertificationOld::$db_columns as $col) {
            if($col === 'id') continue;
            if(array_key_exists($col, $attrs)){
                $val = is_string($attrs[$col]) ? trim($attrs[$col]) : $attrs[$col];
                $obj->$col = ($val === '') ? null : $val;
            }
        }
    }

    if(!$obj->save()){
        throw new Exception('บันทึกไม่สำเร็จ');
    }

    $new_values = [];
    foreach ($audit_fields as $field) {
        $new_values[$field] = $obj->$field ?? null;
    }
    $changed_old = [];
    $changed_new = [];
    foreach ($audit_fields as $field) {
        if ((string)($old_values[$field] ?? '') !== (string)($new_values[$field] ?? '')) {
            $changed_old[$field] = $old_values[$field];
            $changed_new[$field] = $new_values[$field];
        }
    }

    // ===== แนบไฟล์ใหม่ (append) - SHORT =====
    $certificate_id = $obj->id;
    $files_saved  = 0;
    $files_failed = 0;
    $file_errors  = [];
    $files_seen   = 0;

    $attachment_types_new = $_POST['attachment_type_new'] ?? [];
    if (!is_array($attachment_types_new)) $attachment_types_new = [];

    $upload_err_msg = [
    UPLOAD_ERR_OK=>'OK',UPLOAD_ERR_INI_SIZE=>'ไฟล์เกิน upload_max_filesize',UPLOAD_ERR_FORM_SIZE=>'ไฟล์เกิน MAX_FILE_SIZE',
    UPLOAD_ERR_PARTIAL=>'อัปโหลดมาไม่ครบ',UPLOAD_ERR_NO_FILE=>'ไม่ได้เลือกไฟล์',UPLOAD_ERR_NO_TMP_DIR=>'ไม่มีโฟลเดอร์ชั่วคราว',
    UPLOAD_ERR_CANT_WRITE=>'เขียนไฟล์ลงดิสก์ไม่ได้',UPLOAD_ERR_EXTENSION=>'ส่วนขยาย PHP หยุดการอัปโหลด',
    ];

    if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {

    $allow_mime = ['image/jpeg','image/png','image/gif','image/webp','application/pdf'];
    $mimeToExt  = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp','application/pdf'=>'pdf'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $currentUserId = (isset($session) && is_object($session) && method_exists($session,'user_id')) ? $session->user_id() : 0;

    // === folder: /uploads/certificationold/YYYY/CERT_00012345/ ===
    $year = date('Y');
    $certFolder = 'CERT_' . str_pad((string)$certificate_id, 8, '0', STR_PAD_LEFT);
    $baseRelDir = "/uploads/certificationold/{$year}/{$certFolder}";
    $baseAbsDir = rtrim(PUBLIC_PATH, '/\\') . $baseRelDir;

    if (!is_dir($baseAbsDir)) mkdir($baseAbsDir, 0775, true);

    foreach ($_FILES['attachments']['name'] as $i => $origName) {
        $files_seen++;

        $err = $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
        if ($err !== UPLOAD_ERR_OK) {
        if ($err !== UPLOAD_ERR_NO_FILE) {
            $files_failed++;
            $file_errors[] = "{$origName}: " . ($upload_err_msg[$err] ?? "error={$err}");
        }
        continue;
        }

        $tmp  = $_FILES['attachments']['tmp_name'][$i] ?? '';
        $size = (int)($_FILES['attachments']['size'][$i] ?? 0);

        $mime = $finfo->file($tmp) ?: 'application/octet-stream';
        if (!in_array($mime, $allow_mime, true)) {
        $files_failed++; $file_errors[] = "ไม่รองรับชนิดไฟล์: {$origName}";
        continue;
        }

        $ext = $mimeToExt[$mime] ?? 'bin';
        $stored = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $abs = $baseAbsDir . '/' . $stored;
        $rel = $baseRelDir . '/' . $stored;

        if (!move_uploaded_file($tmp, $abs)) {
        $files_failed++; $file_errors[] = "อัปโหลดไม่สำเร็จ: {$origName}";
        continue;
        }

        $attachment_type = isset($attachment_types_new[$i]) && trim($attachment_types_new[$i]) !== ''
        ? trim((string)$attachment_types_new[$i]) : null;

        $att = new FvCertificateAttachment([
        'certificate_id'  => $certificate_id,
        'file_name'       => $origName,
        'stored_name'     => $stored,
        'file_type'       => $mime,
        'file_size'       => $size,
        'attachment_type' => $attachment_type,
        'file_path'       => $rel,
        'created_by'      => $currentUserId ?? 0,
        ]);

        if ($att->save()) $files_saved++;
        else { @unlink($abs); $files_failed++; $file_errors[] = "บันทึก DB ไม่สำเร็จ: {$origName}"; }

        if ($att->id > 0 && !InspectionLog::create_manual_certificate_audit(
            'fvscis_attachment_added',
            $certificate_id,
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
    }
    }


    if ($changed_old) {
        if (!InspectionLog::create_manual_certificate_audit(
            'fvscis_updated_by_officer',
            $obj->id,
            "เจ้าหน้าที่แก้ไขผลตรวจจากเอกสารของเรือ " . $obj->vessel_name,
            $changed_old,
            $changed_new
        )) {
            throw new Exception('บันทึกประวัติการแก้ไขไม่สำเร็จ');
        }
    }
    $has_audit_event = !empty($changed_old) || $files_saved > 0;
    $message = "เจ้าหน้าที่แก้ไขผลตรวจจากเอกสารของเรือ ".$obj->vessel_name;
    $officers = Officer::find_by_department_id($obj->evaluation_agency);
    $updated_action = LogAction::find_by_code('fvscis_updated_by_officer');
    if ($has_audit_event && !$updated_action) {
        throw new Exception('ไม่พบ action สำหรับประวัติการแก้ไข');
    }
    if ($has_audit_event) {
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $obj->id,
                $updated_action->id,
                $message,
                'warning'
            );
        }
        $action1 = LogAction::find_by_code('request_created_by_officer');
        
        /*$officers = Officer::find_by_department_id($obj->evaluation_agency);
        foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $obj->id, [2,3]);
        }*/
        if ($action1) {
            Notification::mark_action_taken($session->user_id(), 'inspectofficer', $obj->id, $action1->id);
        }
    }

    $database->commit();

        $ini_upload_max = ini_get('upload_max_filesize') ?: '';
        $ini_post_max   = ini_get('post_max_size') ?: '';
        $ini_max_files  = ini_get('max_file_uploads') ?: '';

        if (ob_get_length()) { ob_clean(); }

    echo json_encode([
        'success'        => true,
        'certificate_id' => $certificate_id,
        'files_saved'    => $files_saved,
        'files_failed'   => $files_failed,
        'errors'         => $file_errors,
        'debug'          => "files_seen={$files_seen}; upload_max_filesize={$ini_upload_max}; post_max_size={$ini_post_max}; max_file_uploads={$ini_max_files}"
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    if (isset($database)) {
        @$database->rollback();
    }
    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
