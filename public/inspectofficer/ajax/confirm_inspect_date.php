<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json');

try {
    $request_id           = $_POST['request_id'] ?? null;
    $confirmed_date       = $_POST['confirmed_date'] ?? null;
    $original_date_hidden = $_POST['original_confirmed_date'] ?? '';

    if (!$request_id || !$confirmed_date) {
        throw new Exception("ข้อมูลไม่ครบถ้วน");
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $confirmed_date) || $confirmed_date === '0000-00-00') {
        throw new Exception("วันที่ไม่ถูกต้อง");
    }

    $request = InspectionRequest::find_by_id($request_id);
    if (!$request) throw new Exception("ไม่พบคำขอ");

    // ✅ กันแก้วันหลังเริ่มตรวจจริง
    $form_status = InspectionFormStatus::find_by_request_id($request_id);
    if ($form_status) {
        throw new Exception("เริ่มตรวจแล้ว ไม่สามารถเปลี่ยนวันนัดตรวจได้");
    }

    // optimistic lock กันข้อมูลหน้าเว็บเก่า
    $current_date = $request->confirmed_inspect_date ?? '';
    $original_date_hidden = $original_date_hidden ?? '';

    $isEmptyCurrent  = ($current_date === '' || $current_date === '0000-00-00' || is_null($current_date));
    $isEmptyOriginal = ($original_date_hidden === '' || $original_date_hidden === '0000-00-00' || is_null($original_date_hidden));

    if (!$isEmptyCurrent && $current_date !== $original_date_hidden) {
        throw new Exception("มีเจ้าหน้าที่คนอื่นแก้ไขวันนัดแล้ว กรุณารีเฟรชข้อมูลก่อนดำเนินการ");
    }

    // ถ้าวันเดิม = วันใหม่
    if (!$isEmptyCurrent && $current_date === $confirmed_date) {
        echo json_encode(['success' => true, 'message' => 'วันนัดตรวจเป็นวันเดิม ไม่มีการเปลี่ยนแปลง']);
        exit;
    }

    // เก็บวันเดิมไว้ทำ log
    $old_date = $isEmptyCurrent ? null : $current_date;
    $old_text = $old_date ? thai_date($old_date) : 'ยังไม่เคยนัด';
    $new_text = thai_date($confirmed_date);

    // ✅ ถ้าชาวประมงเคยยืนยันแล้ว → ต้องยืนยันใหม่เมื่อเจ้าหน้าที่เปลี่ยนวัน
    $need_reconfirm = ((int)$request->is_confirm === 1);

    // อัปเดตวันนัดใหม่ (เจ้าหน้าที่ "กำหนด/เสนอ" วัน)
    $request->confirmed_inspect_date = $confirmed_date;

    if ($need_reconfirm) {
        $request->is_confirm = 0; // reset ให้ชาวประมงยืนยันใหม่
        $request->status = "pending";
    }

    if (!$request->save()) {
        throw new Exception("ไม่สามารถอัปเดตวันตรวจได้");
    }

    // ✅ LOG: ใช้คำว่า "กำหนด/เสนอ" ไม่ใช้ "ยืนยัน"
    $log = new InspectionLog();
    $log->inspection_request_id = $request->id;

    // action_id: คุณใช้ 7 อยู่ ผมคงไว้ก่อน (v1.0 ไม่ต้องรื้อ)
    $log->action_id = 7;

    if ($need_reconfirm) {
        $log->note = "เจ้าหน้าที่เสนอเปลี่ยนวันตรวจเรือ จาก {$old_text} เป็น {$new_text} (รอชาวประมงยืนยันใหม่)";
    } else {
        $log->note = "เจ้าหน้าที่กำหนดวันตรวจเรือเป็นวันที่ {$new_text}";
    }
    $log->save();

    // ✅ แจ้งเตือนชาวประมง
    if ($need_reconfirm) {
        Notification::create_notification(
            $request->created_by,
            'fisherman',
            $request->id,
            7,
            "เจ้าหน้าที่เสนอเปลี่ยนวันตรวจเรือ {$request->vessel_name} จาก {$old_text} เป็น {$new_text} กรุณายืนยันวันนัดใหม่",
            'warning'
        );
    } else {
        Notification::create_notification(
            $request->created_by,
            'fisherman',
            $request->id,
            7,
            "เจ้าหน้าที่กำหนดวันตรวจเรือ {$request->vessel_name} เป็นวันที่ {$new_text} กรุณากดยืนยันวันนัด",
            'warning'
        );
    }

    // คง logic เดิมของคุณ
    $officers = Officer::find_by_department_id($request->department_id);
    foreach ($officers as $officer) {
        Notification::mark_action_taken($officer->id, 'inspectofficer', $request->id, [2,3]);
    }

    echo json_encode([
        'success' => true,
        'message' => $need_reconfirm
            ? 'เสนอเปลี่ยนวันนัดแล้ว (รอชาวประมงยืนยันใหม่)'
            : 'กำหนดวันนัดเรียบร้อยแล้ว (รอชาวประมงยืนยัน)'
    ]);

} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => $ex->getMessage()]);
}
?>
