<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
  $session->require_role(['inspectofficer']);

  if(empty($_FILES['profile_image']['name'])){
    throw new Exception('กรุณาเลือกรูปภาพ');
  }

  $file = $_FILES['profile_image'];
  if($file['error'] !== UPLOAD_ERR_OK){
    throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ (error '.$file['error'].')');
  }

  // ตรวจขนาดไม่เกิน 2MB
  if($file['size'] > 2 * 1024 * 1024){
    throw new Exception('ไฟล์ต้องมีขนาดไม่เกิน 2 MB');
  }

  // ตรวจ MIME/นามสกุลพื้นฐาน
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);

  if(!isset($allowed[$mime])){
    throw new Exception('อนุญาตเฉพาะไฟล์ JPG/PNG');
  }

  $ext = $allowed[$mime];
  $Officer = Officer::find_by_id($session->user_id());
  if(!$Officer){ throw new Exception('ไม่พบบัญชีผู้ใช้'); }

  // ตั้งชื่อไฟล์
  $newname = 'officer_' . $Officer->id . '_' . time() . '.' . $ext;

  // โฟลเดอร์เก็บรูป (ให้สิทธิ์เขียนได้)
  $upload_dir = dirname(__DIR__,2) . '/uploads/profile';
  if(!is_dir($upload_dir)) { mkdir($upload_dir, 0775, true); }

  $target = $upload_dir . '/' . $newname;
  if(!move_uploaded_file($file['tmp_name'], $target)){
    throw new Exception('บันทึกรูปบนเซิร์ฟเวอร์ไม่สำเร็จ');
  }

  // อัปเดตฐานข้อมูล
  $Officer->profile_image = $newname;
  if(!$Officer->save()){
    @unlink($target);
    throw new Exception('บันทึกข้อมูลรูปโปรไฟล์ไม่สำเร็จ');
  }

  // path สำหรับแสดงผลบนหน้าเว็บ
  $public_url = '../uploads/profile/' . $newname;

  echo json_encode([
    'success'=>true,
    'message'=>'อัปโหลดรูปโปรไฟล์เรียบร้อยแล้ว',
    'new_image'=>$public_url
  ]);
} catch(Throwable $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
