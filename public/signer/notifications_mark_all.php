<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);

$user_id   = $session->user_id();
$user_role = $session->user_role ?? 'signer';

Notification::mark_all_as_read($user_id, $user_role);

// เสร็จแล้ว redirect กลับ
redirect_to('notifications.php');
