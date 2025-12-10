<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

if (!$session->is_logged_in()) {
  echo json_encode(['unread_count' => 0, 'notifications' => []]);
  exit;
}

$user_id = $session->user_id();
$user_role = $session->role;

$notifications = Notification::recent_unread_notifications($user_id, $user_role);
$unread = Notification::unread_count($user_id, $user_role);

$data = [];
foreach ($notifications as $n) {

  $shipcode = null;

  if (!empty($n->inspection_request_id)) {
      $req = InspectionRequest::find_by_id($n->inspection_request_id);

      if ($req && !empty($req->ship_code)) {
          $shipcode = $req->ship_code;
      }
  }

  if (!empty($shipcode)) {
      $link = 'mystatus.php?shipcode=' . urlencode($shipcode);
  } else {
      // กรณี request ถูกลบไปแล้ว หรือไม่มี shipcode
      $link = '#';
  }

  $created_at = !empty($n->created_at) ? strtotime($n->created_at) : time();

  $data[] = [
    'message' => htmlspecialchars($n->message),
    'type'    => $n->notification_type,
    'time'    => date("d/m/Y H:i", $created_at),
    'link'    => $link
  ];
}

echo json_encode(['unread_count' => $unread, 'notifications' => $data]);
exit;
?>