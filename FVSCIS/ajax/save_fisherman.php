<?php
require_once('../../private/initialize.php');

header('Content-Type: application/json; charset=utf-8');

$tmp = $_SESSION['social_tmp'] ?? [];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fisherman']['citizen_id'])) {

    $citizen_id  = trim($_POST['fisherman']['citizen_id']);
    $email       = $_POST['fisherman']['email'] ?? null;
    $username    = $_POST['fisherman']['username'] ?? null;
    $google_id   = $_POST['fisherman']['google_id'] ?? null;
    $facebook_id = $_POST['fisherman']['facebook_id'] ?? null;
    $line_id     = $_POST['fisherman']['line_id'] ?? null;

    // เติมจาก session ชั่วคราวถ้าไม่มีใน POST
    if (!$email)     $email    = $tmp['email'] ?? null;
    if (!$username)  $username = $tmp['username'] ?? null;
    if (!$google_id) $google_id = $tmp['google_id'] ?? null;
    if (!$facebook_id) $facebook_id = $tmp['facebook_id'] ?? null;
    if (!$line_id)   $line_id = $tmp['line_id'] ?? null;

    try {
        // ตรวจเลขบัตรในระบบ Elicense ก่อน
        $Elicenses = Elicense::find_by_id_number($el_db, $citizen_id);
        if (empty($Elicenses)) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบหมายเลขบัตรในระบบ Elicense']);
            exit;
        }

        // ✅ ตรวจว่าเคยสมัครแล้วหรือยัง
        $existing = Fisherman::find_by_citizen_id($citizen_id);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => 'คุณสมัครใช้ระบบแล้ว']);
            exit;
        }

        // ✅ ตรวจ username ซ้ำ
        if (!empty($username)) {
            $dup_user = Fisherman::find_by_username($username);
            if ($dup_user) {
                echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้ถูกใช้แล้ว กรุณาเลือกชื่ออื่น']);
                exit;
            }
        }

        // ✅ ตรวจ email ซ้ำ (ถ้ามี unique)
        if (!empty($email)) {
            $dup_email = Fisherman::find_by_email($email);
            if ($dup_email) {
                echo json_encode(['success' => false, 'message' => 'อีเมลนี้ถูกใช้แล้ว กรุณาใช้อีเมลอื่น']);
                exit;
            }
        }

        // ✅ สร้างและบันทึกข้อมูลใหม่
        $fisherman = new Fisherman();
        $fisherman->citizen_id   = $citizen_id;
        $fisherman->email        = $email;
        $fisherman->username     = $username;
        $fisherman->google_id    = $google_id;
        $fisherman->facebook_id  = $facebook_id;
        $fisherman->line_id      = $line_id;
        $fisherman->full_name    = $Elicenses[0]->display_name ?? null;
        $fisherman->created_at   = date('Y-m-d H:i:s');
        $fisherman->created_by   = $tmp['user_id_tmp'] ?? 0;

        if ($fisherman->save()) {
            unset($_SESSION['social_tmp']); // ล้าง session ชั่วคราวหลังบันทึกสำเร็จ
            $response['success'] = true;
            $response['message'] = 'บันทึกข้อมูลสำเร็จ';
        } else {
            $response['message'] = 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูลอีกครั้ง';
        }

    } catch (Throwable $e) {
        $error_message = $e->getMessage();

        if (strpos($error_message, "for key 'citizen_id'") !== false || strpos($error_message, 'for key `citizen_id`') !== false) {
            $response['message'] = 'คุณสมัครใช้ระบบแล้ว';
        } elseif (strpos($error_message, "for key 'username'") !== false || strpos($error_message, 'for key `username`') !== false) {
            $response['message'] = 'ชื่อผู้ใช้นี้ถูกใช้แล้ว กรุณาเลือกชื่ออื่น';
        } elseif (strpos($error_message, "for key 'email'") !== false || strpos($error_message, 'for key `email`') !== false) {
            $response['message'] = 'อีเมลนี้ถูกใช้แล้ว กรุณาใช้อีเมลอื่น';
        } elseif (strpos($error_message, 'Duplicate entry') !== false) {
            $response['message'] = 'ข้อมูลซ้ำกับที่มีอยู่ในระบบ กรุณาตรวจสอบอีกครั้ง';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $error_message;
        }
    }
} else {
    $response['message'] = 'ข้อมูลไม่ครบถ้วน';
}

echo json_encode($response);

