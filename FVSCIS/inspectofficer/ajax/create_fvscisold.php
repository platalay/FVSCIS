<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {

    if (empty($_POST['FvSanitationCertificationOld'])) {
        throw new Exception('ไม่มีข้อมูลฟอร์ม');
    }

    // -----------------------------
    // 1) เตรียมข้อมูลฟอร์ม
    // -----------------------------
    $attrs = $_POST['FvSanitationCertificationOld'];

    // ✔ ตั้งค่าที่จำเป็น (เพราะ DB ไม่ยอม NULL)
    if (!isset($attrs['status']) || $attrs['status'] === '' || $attrs['status'] === null) {
        $attrs['status'] = 'active';   // ค่า default ของตาราง
    }

    if (!isset($attrs['type']) || $attrs['type'] === '' || $attrs['type'] === null) {
        $attrs['type'] = 0;           // ค่า default ของตาราง (tinyint)
    }

    // -----------------------------
    // 2) สร้าง record ใหม่
    // -----------------------------
    $cert = new FvSanitationCertificationOld($attrs);

    if (!$cert->save()) {
        global $database;
        throw new Exception('บันทึกข้อมูลใบรับรองไม่สำเร็จ: ' . ($database->error ?? 'ไม่ทราบสาเหตุ'));
    }
    FvSanitationCertificationOld::mark_pending($obj->ship_code);

    $cert_id = $cert->id;

    // -----------------------------
    // 3) บันทึกไฟล์แนบ
    // -----------------------------
    $files_saved = 0;

    if (!empty($_FILES['attachments']['name'][0])) {

        $types = $_POST['attachment_type'] ?? [];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        foreach ($_FILES['attachments']['name'] as $i => $origName) {

            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp_name = $_FILES['attachments']['tmp_name'][$i];
            $mime = $finfo->file($tmp_name) ?: 'application/octet-stream';
            $size = (int)$_FILES['attachments']['size'][$i];
            // สร้างชื่อใหม่ป้องกันชื่อซ้ำ
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $safeName = uniqid('att_') . '.' . $ext;

            // ที่เก็บไฟล์
            $upload_dir = PROJECT_PATH . '/FVSCIS/uploads/certificationold/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_path = $upload_dir . $safeName;
            
            if (!move_uploaded_file($tmp_name, $target_path)) {
                continue;
            }

            // type จากฟอร์ม
            $type = $types[$i] ?? '';

            // บันทึกลงฐานข้อมูล
            $att = new FvCertificateAttachment([
                'certificate_id'  => $cert_id,
                'file_name'       => $origName,
                'stored_name'     => $safeName,
                'file_type'  => $mime,
                'file_size'  => $size,
                'attachment_type' => $type,
                'file_path'       => 'uploads/certificationold/' . $safeName,
            ]);

            if ($att->save()) {
                $files_saved++;
            }
        }
    }

    // log + แจ้งเตือน (ย่อ)
  $action = LogAction::find_by_code('fvscis_created_by_officer');
  if ($action) {
    $log = new InspectionLog();
        $log->inspection_request_id = $cert->id;
        $log->action_id             = $action->id;
        $log->note                  = "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ ".$cert->vessel_name;
        $log->save();
  }
  $message = "เจ้าหน้าที่บันทึกผลตรวจจากเอกสารของเรือ ".$cert->vessel_name;
  $officers = Officer::find_by_department_id($cert->evaluation_agency);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $cert->id,
            $action->id,
            $message,
            'warning'
        );
    }
    // -----------------------------
    // 4) ส่งผลลัพธ์กลับ
    // -----------------------------
    echo json_encode([
        'success'     => true,
        'cert_id'     => $cert_id,
        'files_saved' => $files_saved
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
