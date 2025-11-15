<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json'); // บอกว่าเราจะส่ง JSON กลับ

if (is_post_request()) {

    $args = $_POST['department'] ?? [];
    
    // ตรวจสอบว่ามีข้อมูลบ้างหรือไม่
    if (empty($args)) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลที่ส่งมา']);
        exit;
    }
    $id = $args['id'] ?? null;
    // สร้างอ็อบเจกต์ใหม่
    $Department = new Department($args);

    // บันทึกข้อมูล
    $result = $Department->save();

    if ($result === true) {
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ']);
    } else {
        // ดึง error ออกมาเป็นข้อความ
        $error_message = join(', ', $Department->errors ?? ['ไม่สามารถบันทึกข้อมูลได้']);
        echo json_encode(['success' => false, 'message' => $error_message]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'ไม่ใช่คำขอแบบ POST']);
}
?>
