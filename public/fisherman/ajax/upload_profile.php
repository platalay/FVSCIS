<?php
require_once('../../../private/initialize.php');
$session->require_role(['fisherman']);
header('Content-Type: application/json; charset=utf-8');

try {
  $fisherman = Fisherman::find_by_id($session->user_id());
  if(!$fisherman) throw new Exception('ไม่พบผู้ใช้งาน');

  if(empty($_FILES['profile_image']['name'])) throw new Exception('กรุณาเลือกรูปภาพ');

  $file = $_FILES['profile_image'];
  if($file['error'] !== UPLOAD_ERR_OK) throw new Exception('อัปโหลดไฟล์ไม่สำเร็จ');

  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if(!in_array($ext, ['jpg','jpeg','png'])) throw new Exception('อนุญาตเฉพาะไฟล์ .jpg หรือ .png');

  if($file['size'] > 2*1024*1024) throw new Exception('ไฟล์มีขนาดเกิน 2MB');

  $newname = 'fisher_' . $fisherman->id . '_' . time() . '.' . $ext;
  $upload_dir = dirname(__DIR__,2) . '/uploads/profile/';
  if(!is_dir($upload_dir)) mkdir($upload_dir,0777,true);

  $target = $upload_dir . $newname;
  if(!move_uploaded_file($file['tmp_name'], $target)) throw new Exception('ไม่สามารถบันทึกไฟล์ได้');

  // ลบรูปเก่า (ถ้ามี)
  if(!empty($fisherman->profile_image)){
    $oldpath = $upload_dir . $fisherman->profile_image;
    if(file_exists($oldpath)) unlink($oldpath);
  }

  // อัปเดตชื่อไฟล์ในฐานข้อมูล
  $fisherman->profile_image = $newname;
  $fisherman->save();

  $new_image_path = '../uploads/profile/' . $newname;
  $session->set('user_picture', $new_image_path);
  echo json_encode(['success'=>true,'message'=>'อัปโหลดรูปโปรไฟล์เรียบร้อย','new_image'=>$new_image_path]);

} catch(Throwable $e){
  echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
