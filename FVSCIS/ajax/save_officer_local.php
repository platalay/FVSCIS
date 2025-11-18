<?php
require_once('../../private/initialize.php');
header('Content-Type: application/json');

try {
    // 1. รับข้อมูลจากแบบฟอร์ม (POST) ที่ส่งมาจาก modal registration
    if (!isset($_POST['officer']) || !is_array($_POST['officer'])) {
        throw new Exception("ไม่มีข้อมูลที่ส่งมา");  // No data received
    }
    $data = $_POST['officer'];

    // 2. ดึงค่าและทำความสะอาดข้อมูลที่จำเป็น
    $username        = trim($data['username'] ?? '');
    $password        = trim($data['password'] ?? '');
    $confirm_password = trim($data['confirm_password'] ?? '');
    $full_name       = trim($data['full_name'] ?? '');
    $position        = trim($data['position'] ?? '');
    $email           = trim($data['email'] ?? '');
    $line_id         = trim($data['line_id'] ?? '');
    $facebook_id     = trim($data['facebook_id'] ?? '');
    $google_id       = trim($data['google_id'] ?? '');
    $departments_id  = trim($data['departments_id'] ?? '');
    $usertype_id     = trim($data['usertype_id'] ?? '');

    // กำจัดอักขระที่ไม่ปลอดภัยออก (เช่น แท็ก HTML) เพื่อความปลอดภัย
    $username    = strip_tags($username);
    $full_name   = strip_tags($full_name);
    $position    = strip_tags($position);
    $email       = filter_var($email, FILTER_SANITIZE_EMAIL);
    $line_id     = strip_tags($line_id);
    $facebook_id = strip_tags($facebook_id);
    $google_id   = strip_tags($google_id);
    // แปลงรหัสแผนกและประเภทผู้ใช้เป็นตัวเลข (หากว่างจะได้ 0)
    $departments_id = ($departments_id === '' ? 0 : (int)$departments_id);
    $usertype_id    = ($usertype_id === '' ? 0 : (int)$usertype_id);

    // 3. ตรวจสอบข้อมูลที่จำเป็นว่าห้ามว่าง
    if ($username === '' || $password === '' || $confirm_password === '' || 
        $full_name === '' || $departments_id === 0 || $usertype_id === 0) {
        throw new Exception("กรุณากรอกข้อมูลให้ครบทุกช่องที่จำเป็น ". $departments_id);  // Please fill all required fields
    }

    // 4. ตรวจสอบว่ารหัสผ่านและยืนยันรหัสผ่านตรงกัน
    if ($password !== $confirm_password) {
        throw new Exception("รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน");  // Password and confirm password do not match
    }

    // 5. ตรวจสอบชื่อผู้ใช้ซ้ำในระบบหรือไม่
    $existing = Officer::find_by_username($username);
    if ($existing) {
        throw new Exception("ชื่อผู้ใช้นี้ถูกใช้งานแล้ว");  // This username is already taken
    }

    // 6. สร้างออบเจ็กต์ Officer ใหม่และกำหนดค่าต่าง ๆ
    $officer = new Officer();
    $officer->username      = $username;
    // แฮชรหัสผ่านด้วยอัลกอริทึมที่ปลอดภัย
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if (!$hashedPassword) {  // ตรวจสอบเผื่อกรณี hashing ไม่สำเร็จ
        throw new Exception("เกิดข้อผิดพลาดในการเข้ารหัสรหัสผ่าน");  // Error occurred while hashing the password
    }
    $officer->password      = $hashedPassword;
    $officer->full_name     = $full_name;
    $officer->position      = $position;
    $officer->email         = $email;
    $officer->line_id       = $line_id;
    $officer->facebook_id   = $facebook_id;
    $officer->google_id     = $google_id;
    $officer->departments_id = $departments_id;
    $officer->usertype_id    = $usertype_id;
    // กำหนดข้อมูลการสร้าง record
    $officer->created_by    = $_SESSION['user_id'] ?? 0;              // ผู้สร้าง (ควรได้จาก session ผู้ใช้งานปัจจุบัน)
    $officer->created_at    = date('Y-m-d H:i:s'); // วันที่เวลาที่สร้าง

    // 7. บันทึกข้อมูล Officer ใหม่ลงฐานข้อมูล
    if ($officer->save()) {
        $admins = Officer::find_admins();  
        if (!empty($admins)) {
            foreach ($admins as $admin) {
                $msg = "มีคำขอสมัครเจ้าหน้าที่ใหม่จาก "
                     . ($officer->full_name ?? $officer->username ?? 'ไม่ทราบชื่อ');

                     $log = new InspectionLog();
                            $log->inspection_request_id = 0;
                            $log->action_id             = 1;
                            $log->note                  = $msg
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
        
        // หากบันทึกสำเร็จ ตอบกลับ JSON success
        echo json_encode(["success" => true]);
    } else {
        // หากบันทึกไม่สำเร็จ (เช่น เกิดข้อผิดพลาดของฐานข้อมูล)
        throw new Exception("ไม่สามารถบันทึกข้อมูลเจ้าหน้าที่ได้");  // Unable to save officer data
    }

} catch (Exception $e) {
    // จัดการข้อผิดพลาดและส่งออกเป็น JSON
    $error = ["success" => false, "message" => $e->getMessage()];
    echo json_encode($error);
}
