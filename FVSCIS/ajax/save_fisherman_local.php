<?php
require_once('../../private/initialize.php');

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

// ตรวจสอบว่าเป็น POST และมีข้อมูลที่จำเป็น
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fisherman'])) {
    $data = $_POST['fisherman'];

    $citizen_id        = trim($data['citizen_id'] ?? '');
    $username          = trim($data['username'] ?? '');
    $password_raw      = $data['password'] ?? '';
    $confirm_password  = $data['confirm_password'] ?? '';
    $email             = trim($data['email'] ?? '');
    $google_id         = $data['google_id'] ?? null;
    $facebook_id       = $data['facebook_id'] ?? null;
    $line_id           = $data['line_id'] ?? null;

    if ($citizen_id === '' || $username === '' || $password_raw === '' || $confirm_password === '') {
        $response['message'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
        echo json_encode($response);
        exit;
    }

    if ($password_raw !== $confirm_password) {
        $response['message'] = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
        echo json_encode($response);
        exit;
    }

    try {
        // ตรวจสอบว่า username ซ้ำหรือไม่
        if (Fisherman::find_by_username($username)) {
            $response['message'] = 'ชื่อผู้ใช้นี้ถูกใช้งานแล้ว กรุณาเลือกชื่ออื่น';
            echo json_encode($response);
            exit;
        }

        // ตรวจสอบว่า citizen_id ซ้ำหรือไม่
        if (Fisherman::find_by_citizen_id($citizen_id)) {
            $response['message'] = 'หมายเลขบัตรประชาชนนี้ถูกลงทะเบียนแล้ว';
            echo json_encode($response);
            exit;
        }

        // ตรวจสอบหมายเลขบัตรในระบบ Elicense
        $Elicenses = Elicense::find_by_id_number($el_db, $citizen_id);

        if (!empty($Elicenses)) {
            $fisherman = new Fisherman();
            $fisherman->citizen_id   = $citizen_id;
            $fisherman->username     = $username;
            $fisherman->password     = password_hash($password_raw, PASSWORD_DEFAULT); // เข้ารหัสรหัสผ่าน
            $fisherman->email        = $email;
            $fisherman->google_id    = $google_id;
            $fisherman->facebook_id  = $facebook_id;
            $fisherman->line_id      = $line_id;
            $fisherman->full_name    = $Elicenses[0]->display_name;
            $fisherman->created_at   = date('Y-m-d H:i:s');
            $fisherman->created_by   = $_SESSION['user_id'] ?? 0;

            if ($fisherman->save()) {
                $response['success'] = true;
            } else {
                $response['message'] = 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบอีกครั้ง';
            }
        } else {
            $response['message'] = 'ไม่พบหมายเลขบัตรในระบบ Elicense';
        }
    } catch (Throwable $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $response['message'] = 'ข้อมูลซ้ำ กรุณาเปลี่ยนข้อมูลแล้วลองใหม่';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'ข้อมูลไม่ครบถ้วน';
}

echo json_encode($response);

