<?php
if (php_sapi_name() !== 'cli-server') {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
}
require_once('../../../private/initialize.php');
header('Content-Type: application/json'); // ✅ บอกว่าเราจะส่ง JSON

if (is_post_request()) {

    $id = $_POST['department']['id'] ?? null;
    if (!$id) {
         echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสหน่วยงาน']);
         exit;
    }

    $Department = Department::find_by_id($id);
    if (!$Department) {
         echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลหน่วยงาน']);
         exit;
    }
    
    $args = $_POST['department'];
    $Department->merge_attributes($args);
    $result = $Department->save();

    if ($result === true) {
        echo json_encode(['success' => true]);
    } else {
         $error_message = join(', ', $Department->errors ?? ['ไม่สามารถบันทึกได้']);
         echo json_encode(['success' => false, 'message' => $error_message]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'ไม่ใช่คำขอแบบ POST']);
}
?>