<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']); // เจ้าหน้าที่
header('Content-Type: application/json');

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

  $form_type   = (int)($data['inspection_form_type'] ?? 1); // 1/2
  $cold_flag   = (int)($data['cold_room_flag'] ?? 0);       // 0/1
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
    echo json_encode(['success'=>false,'message'=>'มีคำขอที่ยังไม่จบของเรือลำนี้อยู่แล้ว']); exit;
  }

  // บันทึกคำขอ
  $req = new InspectionRequest();
  $req->ship_code           = $ship_code;
  $req->vessel_name         = $vessel_name;
  $req->owner_name          = $owner_name;
  $req->contact_phone       = $phone;

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

  $req->inspect_date_start  = ($confirmed ?: null); // กรณี manual ใช้วันนัดเดียว
  $req->inspect_date_end    = ($confirmed ?: null);
  $req->confirmed_inspect_date = ($confirmed ?: null);

  $req->inspection_form_type = in_array($form_type,[1,2],true) ? $form_type : 1;
  $req->cold_room_flag       = $cold_flag ? 1 : 0;
  $req->is_manual_case       = 1;                 // สำคัญ
  $req->confirm_agreement    = 1;
  $req->is_confirm          = 1;
  $req->status     = InspectionRequest::STATUS_INSPECTING;
  $req->created_by = $session->user_id() ?? 0;
  $req->created_at = date('Y-m-d H:i:s');

  if (!$req->save()) {
    $err = is_array($req->errors ?? null) ? implode(', ', $req->errors) : ($req->errors ?? '');
    throw new Exception('บันทึกคำขอไม่สำเร็จ' . ($err ? " ({$err})" : ''));
  }
  FvSanitationCertificationOld::mark_pending($ship_code);
  // อัปโหลดรูปแนบ (เฉพาะรูป)
  if (!empty($_FILES['attachments'])) {
    $types = $_POST['attachment_types'] ?? [];
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
      if (!is_dir(dirname($abs))) { mkdir(dirname($abs), 0775, true); }
      if (!move_uploaded_file($tmp,$abs)) continue;
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

  // log + แจ้งเตือน (ย่อ)
  $action = LogAction::find_by_code('request_created_by_officer');
  if ($action) {
    $log = new InspectionLog();
        $log->inspection_request_id = $req->id;
        $log->action_id             = $action->id;
        $log->note                  = "เจ้าหน้าที่บันทึกคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
        $log->save();
  }
  $message = "เจ้าหน้าที่บันทึกคำขอตรวจเรือแทนชาวประมง เรือ".$req->vessel_name;
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

    /*$officers = Officer::find_by_department_id($req->department_id);
    foreach ($officers as $officer) {
    Notification::mark_action_taken($officer->id, 'inspectofficer', $req->id, [2,3]);
    }
    Notification::mark_action_taken($session->user_id(), 'fisherman', $req->id, 7);*/

  echo json_encode(['success'=>true,'message'=>'บันทึกคำขอเรียบร้อยแล้ว']);
  exit;

} catch(Throwable $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
  exit;
}
