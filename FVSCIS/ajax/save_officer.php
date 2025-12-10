<?php
require_once('../../private/initialize.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);
header('Content-Type: application/json');
$tmp = $_SESSION['social_tmp'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $args = $_POST['Officer'] ?? [];

    $officer = new Officer($args);
    $officer->is_active = 1;
    $officer->is_approved = 0;
    $officer->created_by = $tmp['user_id_tmp'] ?? 0;
    $officer->created_at = date('Y-m-d H:i:s');
    $officer->updated_at = date('Y-m-d H:i:s');
    

    if ($officer->save()) {
        $admins = Officer::find_admins();  

        if (!empty($admins)) {
            foreach ($admins as $admin) {
                $msg = "มีคำขอสมัครเจ้าหน้าที่ใหม่จาก ". ($officer->full_name ?? $officer->username ?? 'ไม่ทราบชื่อ');
                        $log = new InspectionLog();
                            $log->inspection_request_id = 0;
                            $log->action_id             = 1;
                            $log->note                  = $msg;
                            $log->save();
                Notification::create_notification(
                            $admin->id,        // user_id ของผู้รับ (admin แต่ละคน)
                            'admin',           // user_role (ฝั่ง admin ใช้คำนี้)
                            0,                  // inspection_id 0 สำหรับ งาน ที่ไม่เกี่ยวกับคำขอ
                            1,                  // log_action= register
                            $msg,              // message
                            'warning' // notification_type
                        );
            }
        }
        
        echo json_encode(['status' => 'success', 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว กรุณารอการอนุมัติเข้าใช้ระบบ']);
    } else {
        $errors = join(', ', $officer->errors);
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถบันทึกข้อมูลได้: ' . $errors]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ไม่อนุญาตให้เข้าถึงโดยตรง']);
}
