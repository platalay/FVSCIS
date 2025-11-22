<?php
declare(strict_types=1);

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {

    // -------------------------------
    // 1) ตรวจ POST
    // -------------------------------
    if (empty($_POST['request'])) {
        throw new Exception('ไม่พบข้อมูลคำขอ');
    }

    $data = $_POST['request'];
    $req_id = (int)($data['id'] ?? 0);

    if (!$req_id) {
        throw new Exception('ไม่พบรหัสคำขอ');
    }

    /** @var InspectionRequest|null $req */
    $req = InspectionRequest::find_by_id($req_id);
    if (!$req) {
        throw new Exception('ไม่พบคำขอในระบบ');
    }

    // -------------------------------
    // 2) รับค่า input
    // -------------------------------
    $ship_code   = trim($data['ship_code'] ?? '');
    $vessel_name = trim($data['vessel_name'] ?? '');
    $vessel_mark = trim($data['vessel_mark'] ?? '');
    $license_number = trim($data['license_number'] ?? '');
    $gear_type = trim($data['gear_type'] ?? '');
    $owner_name  = trim($data['owner_name'] ?? '');
    $dept_id     = (int)($data['department_id'] ?? 0);
    $phone       = preg_replace('/\D/', '', $data['contact_phone'] ?? '');
    $prov_id     = (int)($data['port_province_id'] ?? 0);
    $amphur_id   = (int)($data['port_amphur_id'] ?? 0);
    $tambon_id   = (int)($data['port_tambon_id'] ?? 0);
    $port_no     = trim($data['port_license_no'] ?? '');
    $confirmed   = trim($data['confirmed_inspect_date'] ?? '');

    $form_type   = (int)($data['inspection_form_type'] ?? 1);
    $cold_flag   = (int)($data['cold_room_flag'] ?? 0);

    // -------------------------------
    // 3) ตรวจข้อมูลจำเป็น
    // -------------------------------
    if ($ship_code==='' || $vessel_name==='' || $owner_name==='' || !$dept_id || !$tambon_id || $port_no==='') {
        throw new Exception('กรุณากรอกข้อมูลบังคับให้ครบ');
    }
    if (!preg_match('/^\d{9,10}$/', $phone)) {
        throw new Exception('หมายเลขโทรศัพท์ไม่ถูกต้อง (ต้องเป็นตัวเลข 9–10 หลัก)');
    }
    if ($confirmed !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $confirmed)) {
        throw new Exception('รูปแบบวันที่นัดตรวจไม่ถูกต้อง');
    }

    // -------------------------------
    // 4) ดึงข้อมูล eLicense เพิ่ม
    // -------------------------------
    $VesselData = Elicense::find_one_by_ship_code($el_db, $ship_code);
    $Portdata   = ElicensePort::find_one_by_license_no($el_db, $port_no);

    // -------------------------------
    // 5) กันไม่ให้สร้างคำขอซ้ำแต่ให้ "อัปเดตอันเดิม"
    // -------------------------------
    $active = InspectionRequest::find_active_by_ship($ship_code);
    if ($active && $active->id != $req_id) {
        throw new Exception('มีคำขอที่ยังไม่จบของเรือลำนี้อยู่แล้ว');
    }

    // -------------------------------
    // 6) อัปเดตคำขอ
    // -------------------------------
    $req->ship_code           = $ship_code;
    $req->vessel_name         = $vessel_name;
    $req->owner_name          = $owner_name;
    $req->contact_phone       = $phone;

    $req->vessel_mark = !empty($VesselData->fishing_mark)
    ? $VesselData->fishing_mark
    : $vessel_mark;

    $req->license_number = !empty($VesselData->license_no)
    ? $VesselData->license_no
    : $license_number;

    $req->gear_type = !empty($VesselData->geartype)
    ? $VesselData->geartype
    : $gear_type;

    $groupInfo = Department::get_department_group_id($dept_id);
    $req->department_id       = $dept_id;
    $req->department_group_id = $groupInfo->parent_department ?? null;
    $req->data_owner_id       = $groupInfo->data_owner_id     ?? null;

    $req->port_province_id    = $prov_id;
    $req->port_amphur_id      = $amphur_id;
    $req->port_tambon_id      = $tambon_id;
    $req->port_license_no     = $port_no;
    $req->port_name           = $Portdata->port_name ?? null;

    $req->inspect_date_start  = ($confirmed ?: null);
    $req->inspect_date_end    = ($confirmed ?: null);
    $req->confirmed_inspect_date = ($confirmed ?: null);

    $req->inspection_form_type = in_array($form_type, [1,2], true) ? $form_type : 1;
    $req->cold_room_flag       = $cold_flag ? 1 : 0;

    // manual case flag
    $req->is_manual_case       = 1;

    if (!$req->save()) {
        $err = is_array($req->errors ?? null) ? implode(', ', $req->errors) : ($req->errors ?? '');
        throw new Exception('บันทึกคำขอไม่สำเร็จ' . ($err ? " ({$err})" : ''));
    }

    // -------------------------------
    // 7) อัปโหลดไฟล์ใหม่ (ไม่ยุ่งกับไฟล์เดิม)
    // -------------------------------
    if (!empty($_FILES['attachments'])) {
        $types = $_POST['attachment_type_new'] ?? [];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $cnt = count($_FILES['attachments']['name']);

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

            if (!is_dir(dirname($abs))) {
                mkdir(dirname($abs), 0775, true);
            }

            if (!move_uploaded_file($tmp, $abs)) continue;

            $type = $types[$i] ?? '';

            $att = new InspectionAttachment([
                'request_id' => $req->id,
                'attachment_type' => $type,
                'file_path'  => $rel,
                'file_name'  => $name,
                'file_type'  => $mime,
                'file_size'  => $size,
                'created_by' => $session->user_id() ?? 0
            ]);
            $att->save();
        }
    }

    // -------------------------------
    // 8) บันทึก log
    // -------------------------------
    // log + แจ้งเตือน (ย่อ)

    $action = LogAction::find_by_code('request_updated_by_officer');
    if ($action) {
        $log = new InspectionLog();
            $log->inspection_request_id = $req->id;
            $log->action_id             = $action->id;
            $log->note                  = "เจ้าหน้าที่ปรับปรุงคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
            $log->save();
    }
    $message = "เจ้าหน้าที่ปรับปรุงคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
    $officers = Officer::find_by_department_id($req->department_id);
        foreach ($officers as $officer) {
            Notification::create_notification(
                $officer->id,
                'inspectofficer',
                $req->id,
                $action->id,
                $message,
                'warning'
            );
        }
        $action1 = LogAction::find_by_code('request_created_by_officer');
        
        /*$officers = Officer::find_by_department_id($req->department_id);
        foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $req->id, [2,3]);
        }*/
        Notification::mark_action_taken($session->user_id(), 'inspectofficer', $req->id, $action1->id);

    echo json_encode(['success'=>true,'message'=>'อัปเดตคำขอเรียบร้อยแล้ว']);
    exit;

} catch(Throwable $e){
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    exit;
}
