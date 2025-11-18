<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('วิธีการเรียกไม่ถูกต้อง');
    }

    // ตรวจโครง request
    $attrs = $_POST['request'] ?? null;
    if (!$attrs) {
        throw new Exception('ไม่พบข้อมูลคำขอ');
    }

    $id = $attrs['id'] ?? null;
    if (!$id) {
        throw new Exception('ไม่พบรหัสคำขอ (id)');
    }

    // โหลดคำขอเดิมจากฐานข้อมูล
    $req = InspectionRequest::find_by_id($id);
    if (!$req) {
        throw new Exception('ไม่พบคำขอนี้ในระบบ');
    }

    // ตรวจสิทธิ์ – ต้องเป็นชาวประมงเจ้าของคำขอเท่านั้น
    $fisherman = Fisherman::find_by_id($session->user_id());
    if (!$fisherman || $req->created_by != $fisherman->id) {
        throw new Exception('คุณไม่มีสิทธิ์แก้ไขคำขอนี้');
    }

    // ===========================
    //  แม็ปข้อมูลจากฟอร์มเข้า object
    // ===========================

    // ป้องกันฟิลด์ที่ไม่ควรเปลี่ยน เช่น ship_code ถ้าคุณอยากล็อกไว้
    // $req->ship_code = $req->ship_code;
    $port = ElicensePort::find_one_by_license_no($el_db, $attrs['port_license_no']);
    $req->contact_phone       = trim($attrs['contact_phone'] ?? '');
    $req->port_province_id    = $attrs['port_province_id'] ?? null;
    $req->port_amphur_id      = $attrs['port_amphur_id'] ?? null;
    $req->port_tambon_id      = $attrs['port_tambon_id'] ?? null;
    $req->port_license_no     = $port->license_no ?? null;
    $req->port_name           = $port->port_name ?? null;
    $req->department_id       = $attrs['department_id'] ?? null;

    $req->inspect_date_start  = $attrs['inspect_date_start'] ?? null;  // YYYY-MM-DD จาก <input type="date">
    $req->inspect_date_end    = $attrs['inspect_date_end'] ?? null;

    // flags จาก hidden / checkbox
    $req->inspection_form_type = (int)($attrs['inspection_form_type'] ?? 1);  // 1 = ปกติ, 2 = EU
    $req->cold_room_flag       = (int)($attrs['cold_room_flag'] ?? 0);        // 0/1

    // ===========================
    //  ตรวจสอบข้อมูลเบื้องต้น
    // ===========================
    if ($req->contact_phone === '') {
        throw new Exception('กรุณากรอกหมายเลขโทรศัพท์ที่ติดต่อได้');
    }
    if (empty($req->port_province_id) || empty($req->port_amphur_id) || empty($req->port_tambon_id) || empty($req->port_license_no)) {
        throw new Exception('กรุณาเลือกจังหวัด อำเภอ ตำบล และท่าเรือให้ครบถ้วน');
    }
    if (empty($req->department_id)) {
        throw new Exception('กรุณาเลือกหน่วยงานที่ยื่นคำขอ');
    }
    if (empty($req->inspect_date_start) || empty($req->inspect_date_end)) {
        throw new Exception('กรุณาระบุช่วงวันที่ต้องการตรวจ');
    }

    // ===========================
    //  บันทึก (update)
    // ===========================

    if (!$req->save()) {           // หรือใช้ $req->update(); แล้วแต่ class คุณ
        throw new Exception('ไม่สามารถบันทึกการแก้ไขได้');
    }

    // ✅ 2. หาค่า action_id ที่ถูกต้องจาก code
    $action = LogAction::find_by_code('edit_request');
    if (!$action) throw new Exception("ไม่พบ log action: fisher_confirm_date");

    $log = new InspectionLog();
    $log->inspection_request_id = $req->id;
    $log->action_id             = 3;
    $log->note                  = "{$vessel_name} ถูกลบคำขอโดย user_id={$deleter_user_id}";
    if (!$log->save()) {
        if ($in_tx) Database::$database->rollback();
        echo json_encode(['success' => false, 'message' => 'ลบสำเร็จ แต่บันทึก log ไม่สำเร็จ']);
        exit;
    }

    $log = new InspectionLog();
    $log->inspection_request_id = $req->id;
    $log->action_id             = 3;
    $log->note                  = "มีการปรับแก้ไขคำขอตรวจเรือ {$req->vessel_name}";
    $log->save();

    // 🔔 Notification → เจ้าหน้าที่หน่วยงานที่เลือก
    $officers = Officer::find_by_department_id($req->department_id);
    $notif_title = "มีการปรับแก้ไขคำขอตรวจเรือ ".$req->vessel_name;

    foreach ($officers as $officer) {
                Notification::create_notification(
                    $officer->id,
                    'inspectofficer',
                    $req->id,
                    3,
                    $notif_title,
                    'info'
                );        
    }
    
    
    echo json_encode([
        'success' => true,
        'message' => 'บันทึกการแก้ไขเรียบร้อยแล้ว'
    ]);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
