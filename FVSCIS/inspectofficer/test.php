<?php
require_once('../../private/initialize.php');
$user_id = $session->user_id();
$user_role = $session->role; // เช่น 'officer'
echo $user_id." ทดสอบ <br/>";
echo $user_role." ทดสอบ <br/>";
echo Notification::unread_count($user_id, $user_role); // ทดลองแสดงดู
?>