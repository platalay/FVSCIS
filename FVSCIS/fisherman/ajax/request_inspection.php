<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json');

try {
    if (!isset($_POST['request'])) {
        throw new Exception("ไม่พบข้อมูลที่ส่งมา");
    }

    $data = $_POST['request'];

    // ✳️ ดึงค่าจำเป็น
    $ship_code      = trim($data['ship_code'] ?? '');
    $vessel_name    = trim($data['vessel_name'] ?? '');
    $contact_phone  = trim($data['contact_phone'] ?? '');
    $department_id  = trim($data['department_id'] ?? '');
    $province_id    = trim($data['port_province_id'] ?? '');
    $amphur_id      = trim($data['port_amphur_id'] ?? '');
    $tambon_id      = trim($data['port_tambon_id'] ?? '');
    $port_license   = trim($data['port_license_no'] ?? '');
    $inspect_start  = trim($data['inspect_date_start'] ?? '');
    $inspect_end    = trim($data['inspect_date_end'] ?? '');
    $agree          = isset($data['confirm_agreement']) ? 1 : 0;

    // ✅ รองรับรูปแบบฟอร์ม: 1=ทั่วไป, 2=EU (checkbox)
    $inspection_form_type = (int)($_POST['request']['inspection_form_type'] ?? 1);
    $inspection_form_type = ($inspection_form_type === 2) ? 2 : 1; // บังคับ 1/2
    $cold_room_flag_raw = $_POST['request']['cold_room_flag'] ?? '0';
    $cold_room_flag = ($cold_room_flag_raw === '1') ? 1 : 0; // บังคับ 0/1
    // ✅ ตรวจข้อมูลขั้นต่ำ
    if ($ship_code === '' || $contact_phone === '' || $department_id === '') {
        throw new Exception("กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    // ✅ ตรวจเบอร์โทร (ตัวเลข 9–10 หลัก แบบหยืดหยุ่น)
    if (!preg_match('/^\d{9,10}$/', $contact_phone)) {
        throw new Exception("กรุณากรอกหมายเลขโทรศัพท์ให้ถูกต้อง (9–10 หลัก)");
    }

    // ✅ ตรวจวันที่ (ถ้ามีการกรอกครบคู่)
    if ($inspect_start !== '' && $inspect_end !== '') {
        $ds = strtotime($inspect_start);
        $de = strtotime($inspect_end);
        if ($ds === false || $de === false) {
            throw new Exception("รูปแบบวันที่ไม่ถูกต้อง");
        }
        if ($ds > $de) {
            throw new Exception("วันที่เริ่มต้องไม่เกินวันที่สิ้นสุด");
        }
    }

    // ⚙️ ดึงข้อมูลอ้างอิงเรือ/ท่าเรือ
    $VesselData = Elicense::find_one_by_ship_code($el_db, $ship_code);
    $Portdata   = ElicensePort::find_one_by_license_no($el_db, $port_license);

    // ✅ ตรวจคำขอค้างอยู่ของเรือลำนี้
    $existing_request = InspectionRequest::find_active_by_ship($ship_code);
    if ($existing_request) {
        echo json_encode([
            'success' => false,
            'message' => 'คุณมีคำขอตรวจเรือที่ยังไม่เสร็จ กรุณายกเลิกหรือรอผลก่อน',
        ]);
        exit;
    }

    // ✅ บันทึกคำขอ
    $request = new InspectionRequest();
    $request->ship_code           = $ship_code;
    $request->vessel_name         = $vessel_name;
    $request->vessel_mark         = $VesselData->fishing_mark ?? null;
    $request->license_number      = $VesselData->license_no   ?? null;
    $request->license_status      = "normal";
    $request->gear_type           = $VesselData->geartype     ?? null;
    $request->owner_name          = $VesselData->display_name ?? null;

    // group/data owner จากหน่วยงาน
    $result = Department::get_department_group_id($department_id);
    $request->department_id       = $department_id;
    $request->department_group_id = $result->parent_department ?? null;
    $request->data_owner_id       = $result->data_owner_id     ?? null;

    // พื้นที่/ท่าเทียบเรือ
    $request->port_province_id    = $province_id;
    $request->port_amphur_id      = $amphur_id;
    $request->port_tambon_id      = $tambon_id;
    $request->port_license_no     = $port_license;
    $request->port_name           = $Portdata->port_name ?? null;

    // วันที่ตรวจที่ต้องการ
    $request->inspect_date_start  = $inspect_start ?: null;
    $request->inspect_date_end    = $inspect_end   ?: null;

    // คำยินยอม + ผู้สร้าง + สถานะ
    $request->confirm_agreement       = $agree;
    $request->created_by              = $session->user_id() ?? 0;
    $request->created_at              = date('Y-m-d H:i:s');
    $request->status                  = InspectionRequest::STATUS_PENDING;
    $request->confirmed_inspect_date  = null;
    $request->contact_phone = $contact_phone;
    // 🌟 สำคัญ: เก็บรูปแบบฟอร์ม (1/2)
    $request->inspection_form_type = $inspection_form_type;
    $request->cold_room_flag = $cold_room_flag;
    $request->is_manual_case = 0;

    if (!$request->save()) {
        $err = is_array($request->errors ?? null) ? implode(', ', $request->errors) : ($request->errors ?? '');
        throw new Exception("ไม่สามารถบันทึกคำขอได้" . ($err ? " ({$err})" : ''));
    }


    $log = new InspectionLog();
    $log->inspection_request_id = $request->id;
    $log->action_id             = 2;
    $log->note                  = ($inspection_form_type === 2)
                                  ? 'ชาวประมงยื่นคำขอ: ตรวจสุขอนามัยเพื่อขอใบรับรอง EU'
                                  : 'ชาวประมงยื่นคำขอ: ตรวจสุขอนามัยแบบทั่วไป';
    $log->save();
    
    // 🔔 Notification → เจ้าหน้าที่หน่วยงานที่เลือก
    $officers = Officer::find_by_department_id($department_id);
    $notif_title = ($inspection_form_type === 2)
        ? "มีคำขอ 'ตรวจ EU Export' ใหม่จากชาวประมง เรือ {$request->vessel_name}"
        : "มีคำขอตรวจสุขอนามัยเรือใหม่จากชาวประมง เรือ {$request->vessel_name}";

    foreach ($officers as $officer) {
        Notification::create_notification(
            $officer->id,
            'inspectofficer',
            $request->id,
            2,
            $notif_title,
            'warning'
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'บันทึกคำขอเรียบร้อยแล้ว',
    ]);
    exit;

} catch (Exception $ex) {
    echo json_encode([
        'success' => false,
        'message' => $ex->getMessage(),
    ]);
    exit;
}
?>

