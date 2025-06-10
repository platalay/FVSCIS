<?php
require_once('../../../private/initialize.php');

// รับข้อมูลจากฟอร์ม
$args = $_POST['DepartmentGroup'] ?? [];
$id = $args['id'] ?? null;

// ป้องกันการสร้าง object พร้อม id = 0
if (!empty($id) && is_numeric($id) && $id > 0) {
    // แก้ไขข้อมูลเดิม
    $DepartmentGroup = DepartmentGroup::find_by_id($id);
    if (!$DepartmentGroup) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงาน']);
        exit;
    }

    $DepartmentGroup->merge_attributes($args);
} else {
    // เพิ่มใหม่ (ลบ id ออกเผื่อเผลอส่งมา)
    unset($args['id']);
    $DepartmentGroup = new DepartmentGroup($args);
}

// บันทึกข้อมูล
$result = $DepartmentGroup->save();

// ส่งผลลัพธ์กลับ
if ($result === true) {
    echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่สามารถบันทึกข้อมูลได้',
        'errors' => $DepartmentGroup->errors ?? []
    ]);
}

