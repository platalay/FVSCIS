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

    // ===== แนบไฟล์ใหม่ (append) =====
    $certificate_id = $obj->id;
    $files_saved  = 0;
    $files_failed = 0;
    $file_errors  = [];
    $files_seen   = 0;

    $ini_upload_max = ini_get('upload_max_filesize');
    $ini_post_max   = ini_get('post_max_size');
    $ini_max_files  = ini_get('max_file_uploads');

    $upload_err_msg = [
        UPLOAD_ERR_OK         => 'OK',
        UPLOAD_ERR_INI_SIZE   => 'ไฟล์เกิน upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE  => 'ไฟล์เกิน MAX_FILE_SIZE จากฟอร์ม',
        UPLOAD_ERR_PARTIAL    => 'อัปโหลดมาไม่ครบ',
        UPLOAD_ERR_NO_FILE    => 'ไม่ได้เลือกไฟล์',
        UPLOAD_ERR_NO_TMP_DIR => 'ไม่มีโฟลเดอร์ชั่วคราว',
        UPLOAD_ERR_CANT_WRITE => 'เขียนไฟล์ลงดิสก์ไม่ได้',
        UPLOAD_ERR_EXTENSION  => 'ส่วนขยาย PHP หยุดการอัปโหลด',
    ];

    // 👉 อ่านประเภทเอกสารของไฟล์ใหม่จากฟอร์ม (มาจาก dropdown ในหน้า Edit)
    $attachment_types_new = $_POST['attachment_type_new'] ?? [];
    if (!is_array($attachment_types_new)) {
        $attachment_types_new = [];
    }

    if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
        $allow_ext = ['jpg','jpeg','png','gif','webp','pdf'];

        // current user id
        $currentUserId = null;
        if (isset($session)) {
            if (is_object($session) && method_exists($session, 'user_id')) {
                $currentUserId = $session->user_id();
            } elseif (isset($session->user_id)) {
                $currentUserId = $session->user_id;
            }
        }

        foreach ($_FILES['attachments']['name'] as $i => $name) {
            $files_seen++;

            $file = [
                'name'     => $_FILES['attachments']['name'][$i],
                'type'     => $_FILES['attachments']['type'][$i] ?? '',
                'tmp_name' => $_FILES['attachments']['tmp_name'][$i] ?? '',
                'error'    => $_FILES['attachments']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $_FILES['attachments']['size'][$i] ?? 0,
            ];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    $files_failed++;
                    $file_errors[] = "{$file['name']}: " . ($upload_err_msg[$file['error']] ?? "error={$file['error']}");
                }
                continue;
            }

            // ตรวจนามสกุล
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allow_ext, true)) {
                $files_failed++;
                $file_errors[] = "ไม่รองรับ: {$file['name']}";
                continue;
            }

            // 👉 ดึงประเภทเอกสารตาม index เดียวกับไฟล์
            $attachment_type = null;
            if (array_key_exists($i, $attachment_types_new)) {
                $attachment_type = trim((string)$attachment_types_new[$i]);
                if ($attachment_type === '') {
                    $attachment_type = null;
                }
            }

            //error_log("DEBUG: saving file {$file['name']} for cert_id={$certificate_id}, type={$attachment_type}");

            // เรียกเมธอดบันทึก (รองรับพารามิเตอร์ที่ 4 = attachment_type)
            $ok = FvCertificateAttachment::create_from_upload(
                $certificate_id,
                $file,
                $currentUserId,
                $attachment_type
            );

            if ($ok) {
                $files_saved++;
            } else {
                $files_failed++;
                $file_errors[] = "อัปโหลดไม่สำเร็จ: {$file['name']}";
            }
        }
    }

    $action = LogAction::find_by_code('fvscis_updated_by_officer');
    if ($action) {
        $log = new InspectionLog();
            $log->inspection_request_id = $obj->id;
            $log->action_id             = $action->id;
            $log->note                  = "เจ้าหน้าที่แก้ไขผลตรวจจากเอกสารของเรือ ".$obj->vessel_name;
            $log->save();
    }
    $message = "เจ้าหน้าที่แก้ไขผลตรวจจากเอกสารของเรือ ".$obj->vessel_name;
    $officers = Officer::find_by_department_id($obj->evaluation_agency);
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $obj->id,
                $action->id,
                $message,
                'warning'
            );
        }
        $action1 = LogAction::find_by_code('request_created_by_officer');
        
        /*$officers = Officer::find_by_department_id($obj->evaluation_agency);
        foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $obj->id, [2,3]);
        }*/
        Notification::mark_action_taken($session->user_id(), 'inspectofficer', $obj->id, $action1->id);

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
    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
