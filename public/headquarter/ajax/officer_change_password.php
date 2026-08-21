<?php
require_once('../../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

try {
  $session->require_role(['headquarter']);

  $current = $_POST['current_password'] ?? '';
  $new     = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if(!$current || !$new || !$confirm){
    throw new Exception('กรอกข้อมูลให้ครบถ้วน');
  }
  if(strlen($new) < 8){
    throw new Exception('รหัสผ่านใหม่ควรมีอย่างน้อย 8 ตัวอักษร');
  }
  if($new !== $confirm){
    throw new Exception('รหัสผ่านใหม่และยืนยันไม่ตรงกัน');
  }

  $officer = Officer::find_by_id($session->user_id());
  if(!$officer){ throw new Exception('ไม่พบบัญชีผู้ใช้'); }

  if(!password_verify($current, $officer->password)){
    throw new Exception('รหัสผ่านปัจจุบันไม่ถูกต้อง');
  }

  $officer->password = password_hash($new, PASSWORD_DEFAULT);
  if(!$officer->save()){
    throw new Exception('บันทึกรหัสผ่านใหม่ไม่สำเร็จ');
  }

  echo json_encode(['success'=>true,'message'=>'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว']);
} catch(Throwable $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
