<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']); // เจ้าหน้าที่

header('Content-Type: application/json; charset=utf-8');

// ใช้ connection เดียวกับ model
global $database;
/** @var mysqli $database */
$db = $database;

try {
    if (empty($_POST['request'])) {
        throw new Exception('ไม่พบข้อมูลคำขอ');
    }
    $data = $_POST['request'];

    // ค่า input หลัก
    $ship_code   = trim($data['ship_code'] ?? '');
    $vessel_name = trim($data['vessel_name'] ?? '');
    $owner_name  = trim($data['owner_name'] ?? '');
    $dept_id     = (int)($data['department_id'] ?? 0);
    $phone       = preg_replace('/\D/','', $data['contact_phone'] ?? '');
    $prov_id     = (int)($data['port_province_id'] ?? 0);
    $amphur_id   = (int)($data['port_amphur_id'] ?? 0);
    $tambon_id   = (int)($data['port_tambon_id'] ?? 0);
    $port_no     = trim($data['port_license_no'] ?? '');
    $confirmed   = trim($data['confirmed_inspect_date'] ?? ''); // วันเดียว (นัดตรวจ)

    $form_type      = (int)($data['inspection_form_type'] ?? 1); // 1/2
    $cold_flag      = (int)($data['cold_room_flag'] ?? 0);       // 0/1
    $license_status = $data['license_status'] ?? 'none';

    if ($ship_code==='' || $vessel_name==='' || $owner_name==='' || !$dept_id || !$tambon_id || $port_no==='') {
        throw new Exception('กรุณากรอกข้อมูลบังคับให้ครบ');
    }
    if (!preg_match('/^\d{9,10}$/', $phone)) {
        throw new Exception('หมายเลขโทรศัพท์ไม่ถูกต้อง (ต้องเป็นตัวเลข 9–10 หลัก)');
    }
    if ($confirmed !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$confirmed)) {
        throw new Exception('รูปแบบวันที่นัดตรวจไม่ถูกต้อง');
    }

    // ดึงข้อมูล eLicense เพิ่มเติมถ้ามี
    $VesselData = Elicense::find_one_by_ship_code($el_db, $ship_code);
    $Portdata   = ElicensePort::find_one_by_license_no($el_db, $port_no);

    // กันคำขอค้าง
    if (InspectionRequest::find_active_by_ship($ship_code)) {
        echo json_encode([
            'success'=>false,
            'message'=>'มีคำขอที่ยังไม่จบของเรือลำนี้อยู่แล้ว'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 🔹 เริ่ม Transaction
    $db->query("START TRANSACTION");

    // --------------------------
    // 1) บันทึกคำขอ InspectionRequest
    // --------------------------
    $req = new InspectionRequest();
    $req->ship_code     = $ship_code;
    $req->vessel_name   = $vessel_name;
    $req->owner_name    = $owner_name;
    $req->contact_phone = $phone;

    // --- Logic ใบอนุญาต / เครื่องมือ / สัญลักษณ์ ---
    if ($VesselData && !empty($VesselData->license_no)) {
        // เจอใน eLicense
        $req->vessel_mark    = $VesselData->fishing_mark ?? null;
        $req->license_number = $VesselData->license_no   ?? null;
        $req->gear_type      = $VesselData->geartype     ?? null;
        $req->license_status = 'normal';
    } else {
        // ไม่เจอใน eLicense → ไม่มีใบอนุญาต
        $req->vessel_mark    = null;
        $req->license_number = null;
        $req->gear_type      = null;
        $req->license_status = 'none';
    }

    $groupInfo = Department::get_department_group_id($dept_id);
    $req->department_id       = $dept_id;
    $req->department_group_id = $groupInfo->parent_department ?? null;
    $req->data_owner_id       = $groupInfo->data_owner_id     ?? null;

    $req->port_province_id    = $prov_id;
    $req->port_amphur_id      = $amphur_id;
    $req->port_tambon_id      = $tambon_id;
    $req->port_license_no     = $port_no;
    $req->port_name           = $Portdata->port_name ?? null;

    $req->inspect_date_start      = ($confirmed ?: null); // กรณี manual ใช้วันนัดเดียว
    $req->inspect_date_end        = ($confirmed ?: null);
    $req->confirmed_inspect_date  = ($confirmed ?: null);

    $req->inspection_form_type = in_array($form_type,[1,2],true) ? $form_type : 1;
    $req->cold_room_flag       = $cold_flag ? 1 : 0;
    $req->is_manual_case       = 1;                 // สำคัญ
    $req->confirm_agreement    = 1;
    $req->is_confirm           = 1;
    $req->status               = InspectionRequest::STATUS_INSPECTING;
    $req->created_by           = $session->user_id() ?? 0;
    $req->created_at           = date('Y-m-d H:i:s');

    if (!$req->save()) {
        $err = is_array($req->errors ?? null) ? implode(', ', $req->errors) : ($req->errors ?? '');
        throw new Exception('บันทึกคำขอไม่สำเร็จ' . ($err ? " ({$err})" : ''));
    }

    // --------------------------
    // 2) ปรับใบรับรองเก่าเป็น pending
    // --------------------------
    FvSanitationCertificationOld::mark_pending($ship_code);

    // --------------------------
    // 3) บันทึกข้อมูลผู้ยื่น InspectionApplicantInfo
    // --------------------------

    // 🔹 ดึงข้อมูลชาวประมงด้วยทะเบียนเรือ (จาก eLicense)
    $fisherman = Elicense::find_one_by_ship_code($el_db, $ship_code);
    if(!$fisherman) {
        throw new Exception("ไม่พบข้อมูลชาวประมงผู้ใช้ระบบ");
    }

    // 🔹 เช็คเลข 13 หลัก ว่าเป็นนิติบุคคลหรือไม่
    $citizen_no  = trim($VesselData->number ?? $fisherman->citizen_id ?? '');
    $is_juristic = 0;
    if ($citizen_no !== '' && strlen($citizen_no) === 13 && $citizen_no[0] === '0') {
        $is_juristic = 1;
    }

    // 🔹 เตรียม InspectionApplicantInfo (1 request มี 1 record)
    $applicant = InspectionApplicantInfo::find_or_initialize_by_request_id($req->id);
    $applicant->request_id  = $req->id;
    $applicant->is_juristic = $is_juristic;

    $current_user_id = $session->user_id() ?? 0;
    $current_ip      = $_SERVER['REMOTE_ADDR'] ?? '';

    if(!$applicant->id) {
        // new record
        $applicant->created_by = $current_user_id;
        $applicant->created_ip = $current_ip;
    }
    $applicant->updated_by = $current_user_id;
    $applicant->updated_ip = $current_ip;

    if ($is_juristic === 0) {
        // 🟢 บุคคลธรรมดา → ใช้ข้อมูลจาก eLicense เป็นผู้ยื่นเลย
        $applicant->applicant_name        = $VesselData->display_name ?? $fisherman->full_name ?? '';
        $applicant->applicant_age         = $VesselData->age ?? null;

        // แปลงสัญชาติจาก eLicense (numeric → text)
        $nationalityId  = $VesselData->nationality_id ?? null;
        $nationalityText = '';

        if ($nationalityId == 259) {
            $nationalityText = 'ไทย';
        } elseif (!empty($nationalityId)) {
            $nationalityText = 'อื่นๆ';
        } else {
            $nationalityText = '';
        }
        $applicant->applicant_nationality = $nationalityText;

        $applicant->applicant_phone       = $phone;

        $applicant->applicant_address_no  = $VesselData->street ?? '';
        $applicant->applicant_moo         = $VesselData->moo ?? '';

        $applicant->applicant_province_id = $VesselData->province_id ?? null;
        $applicant->applicant_province    = $VesselData->province_name ?? '';
        $applicant->applicant_amphoe_id   = $VesselData->amphur_id ?? null;
        $applicant->applicant_amphoe      = $VesselData->amphur_name ?? '';
        $applicant->applicant_tambon_id   = $VesselData->tambon_id ?? null;
        $applicant->applicant_tambon      = $VesselData->tambon_name ?? '';

        // ฝั่ง juristic_* ปล่อยว่าง
        $applicant->juristic_name = $applicant->juristic_name ?? '';
    } else {
        $applicant->applicant_phone       = $phone;

        // 🔵 นิติบุคคล → เก็บข้อมูลบริษัทไว้ก่อน, ผู้ยื่น (คน) ให้กรอกตอนกดพิมพ์ สร.1
        $applicant->juristic_name        = $VesselData->display_name ?? '';
        $applicant->juristic_office      = ''; // ถ้ามีฟิลด์อาคาร/สำนักงานเพิ่มทีหลังได้
        $applicant->juristic_address_no  = $VesselData->street ?? '';
        $applicant->juristic_moo         = $VesselData->moo ?? '';

        $applicant->juristic_province_id = $VesselData->province_id ?? null;
        $applicant->juristic_province    = $VesselData->province_name ?? '';
        $applicant->juristic_amphoe_id   = $VesselData->amphur_id ?? null;
        $applicant->juristic_amphoe      = $VesselData->amphur_name ?? '';
        $applicant->juristic_tambon_id   = $VesselData->tambon_id ?? null;
        $applicant->juristic_tambon      = $VesselData->tambon_name ?? '';

        // ผู้ยื่นตัวบุคคลจะมาเติมทีหลังใน modal
        if (is_blank($applicant->applicant_name)) {
            $applicant->applicant_name = '';
        }
    }

    if(!$applicant->save()) {
        $err = is_array($applicant->errors ?? null) ? implode(', ', $applicant->errors) : ($applicant->errors ?? '');
        throw new Exception("ไม่สามารถบันทึกข้อมูลผู้ยื่นคำขอได้" . ($err ? " ({$err})" : ''));
    }

    // --------------------------
    // 4) อัปโหลดรูปแนบ (InspectionAttachment)
    // --------------------------
    if (!empty($_FILES['attachments'])) {
        $types   = $_POST['attachment_types'] ?? [];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $cnt     = count($_FILES['attachments']['name']);

        for ($i=0; $i<$cnt; $i++) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK) continue;
            
            $tmp  = $_FILES['attachments']['tmp_name'][$i];
            $name = $_FILES['attachments']['name'][$i];
            $mime = $finfo->file($tmp) ?: 'application/octet-stream';
            $size = (int)$_FILES['attachments']['size'][$i];

            if (!in_array($mime,$allowed,true)) continue;
            if ($size > 10*1024*1024) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $new = date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$ext;

            $rel = '/uploads/inspection/'.$new;
            $abs = PUBLIC_PATH . $rel;
            if (!is_dir(dirname($abs))) { mkdir(dirname($abs), 0775, true); }
            if (!move_uploaded_file($tmp,$abs)) continue;
            $type = $types[$i] ?? '';
            
            $att = new InspectionAttachment([
                'request_id'      => $req->id,
                'attachment_type' => $type,
                'file_path'       => $rel,
                'file_name'       => $name,
                'file_type'       => $mime,
                'file_size'       => $size,
                'created_by'      => $session->user_id() ?? 0
            ]);
            $att->save(); // ถ้า save ไม่ได้ เราไม่ throw เพื่อไม่ให้ทั้งคำขอล้ม (แล้วแต่นโยบาย)
        }
    }

    // --------------------------
    // 5) Log + Notification
    // --------------------------
    $action = LogAction::find_by_code('request_created_by_officer');
    if ($action) {
        $log = new InspectionLog();
        $log->inspection_request_id = $req->id;
        $log->action_id             = $action->id;
        $log->note                  = "เจ้าหน้าที่บันทึกคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
        if (!$log->save()) {
            throw new Exception('บันทึกประวัติคำขอไม่สำเร็จ');
        }
    }

    $message  = "เจ้าหน้าที่บันทึกคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
    $officers = Officer::find_by_department_id($req->department_id);
    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $req->id,
            $action ? $action->id : null,
            $message,
            'warning'
        );
    }

    // ✅ ทุกอย่างผ่าน → COMMIT
    $db->query("COMMIT");

    echo json_encode([
        'success'=>true,
        'message'=>'บันทึกคำขอเรียบร้อยแล้ว'
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch(Throwable $e){

    // ❌ มี error ใด ๆ → ROLLBACK
    if ($db instanceof mysqli) {
        @ $db->query("ROLLBACK");
    }

    echo json_encode([
        'success'=>false,
        'message'=>$e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
