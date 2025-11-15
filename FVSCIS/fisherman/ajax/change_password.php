<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json; charset=utf-8');

try {
  if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception('Method not allowed');
  }

  $fisherman = Fisherman::find_by_id($session->user_id());
  if(!$fisherman) throw new Exception('ไม่พบผู้ใช้งาน');

  $current = trim($_POST['current_password'] ?? '');
  $new     = trim($_POST['new_password'] ?? '');
  $confirm = trim($_POST['confirm_password'] ?? '');

  if(!$fisherman->verify_password($current))
    throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
  if($new !== $confirm)
    throw new Exception('รหัสผ่านใหม่และการยืนยันไม่ตรงกัน');
  if(strlen($new) < 8)
    throw new Exception('รหัสผ่านใหม่ควรมีอย่างน้อย 8 ตัวอักษร');

  // ถ้าอยากเข้มงวดขึ้น เปิดคอมเมนต์ด้านล่างนี้
  /*
  if(!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $new)) {
    throw new Exception('รหัสผ่านควรมีทั้งตัวอักษรและตัวเลข และมีความยาวอย่างน้อย 8 ตัวอักษร');
  }
  */

  $fisherman->set_password($new);

  // เคลียร์ token remember me เพื่อความปลอดภัย
  if(property_exists($fisherman, 'login_token')) {
    $fisherman->login_token = null;
    $fisherman->token_expiry = null;
  }

  if(!$fisherman->save()) throw new Exception('บันทึกรหัสผ่านไม่สำเร็จ');

  echo json_encode(['success'=>true,'message'=>'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว']);
} catch(Throwable $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
