<?php
require_once('../../private/initialize.php');
$session->require_role(['admin']);

$user_id   = $session->user_id();
$user_role = $session->user_role ?? 'admin';

Notification::mark_all_as_read($user_id, $user_role);

// เสร็จแล้ว redirect กลับ
redirect_to('notifications.php');
