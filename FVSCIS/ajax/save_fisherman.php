<?php
require_once('../../private/initialize.php');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fisherman']['citizen_id'])) {
    $citizen_id = trim($_POST['fisherman']['citizen_id']);
    $email = $_POST['fisherman']['email'] ?? null;
    $username = $_POST['fisherman']['username'] ?? null;
    $google_id = $_POST['fisherman']['google_id'] ?? null;
    $facebook_id = $_POST['fisherman']['facebook_id'] ?? null;
    $line_id = $_POST['fisherman']['line_id'] ?? null;

    try {
        $Elicenses = Elicense::find_by_id_number($el_db, $citizen_id);

        if (!empty($Elicenses)) {
            $fisherman = new Fisherman();
            $fisherman->citizen_id = $citizen_id;
            $fisherman->email = $email;
            $fisherman->create_at = date('Y-m-d H:i:s');
            $fisherman->create_by = $_SESSION['user_id'] ?? 'system';
            $fisherman->username = $username;
            $fisherman->google_id = $google_id;
            $fisherman->facebook_id = $facebook_id;
            $fisherman->line_id = $line_id;

            if ($fisherman->save()) {
                $response['success'] = true;
            } else {
                $response['message'] = 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูลอีกครั้ง';
            }
        } else {
            $response['message'] = 'ไม่พบหมายเลขบัตรในระบบ Elicense';
        }
    } catch (Throwable $e) {
        // ข้อผิดพลาดจากระบบ เช่น duplicate username, database error ฯลฯ
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $response['message'] = 'username หรือข้อมูลซ้ำกับที่มีอยู่ในระบบ กรุณาเปลี่ยนข้อมูลแล้วลองใหม่อีกครั้ง';
        } else {
            $response['message'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'ข้อมูลไม่ครบถ้วน';
}

echo json_encode($response);
