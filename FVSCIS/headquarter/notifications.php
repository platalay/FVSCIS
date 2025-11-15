<?php
require_once('../../private/initialize.php');
$session->require_role(['headquarter']);

$user_id   = $session->user_id();
$user_role = $session->user_role ?? 'headquarter';

// ดึงการแจ้งเตือนล่าสุด (เช่น 50 รายการ)
$notifications = Notification::recent_notifications($user_id, $user_role, 50);

include("../../private/shared/headerheadquarter.php");
include("../../private/shared/sidebarheadquarter.php");
include("../../private/shared/topbarheadquarter.php");

/**
 * helper แปลง type → bootstrap badge
 */
function notification_badge_class($type) {
  switch ($type) {
    case 'success': return 'badge-success';
    case 'warning': return 'badge-warning';
    case 'danger':  return 'badge-danger';
    default:        return 'badge-info';
  }
}

/**
 * helper แปลง reference_type → URL
 */
function notification_link($n) {
  if (empty($n->reference_type) || empty($n->reference_id)) {
    return '#';
  }
  switch ($n->reference_type) {
    case 'inspection_request':
      return 'request_show.php?id=' . urlencode($n->reference_id);
    case 'certificate_old':
      return 'certificate_old_show.php?id=' . urlencode($n->reference_id);
    // เพิ่ม case ตามชนิดอื่นๆ ได้
    default:
      return '#';
  }
}

?>

<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1 text-gray-800">การแจ้งเตือนของฉัน</h1>
      <p class="mb-0 text-muted">ดูการแจ้งเตือนทั้งหมดเกี่ยวกับคำขอรับการตรวจและใบรับรองของคุณ</p>
    </div>

    <?php if (!empty($notifications)) { ?>
      <form method="post" action="notifications_mark_all.php">
        <button type="submit" class="btn btn-sm btn-outline-secondary">
          ทำเครื่องหมายว่าอ่านทั้งหมดแล้ว
        </button>
      </form>
    <?php } ?>
  </div>

  <?php if (empty($notifications)) { ?>

    <div class="alert alert-light border">
      ยังไม่มีการแจ้งเตือนในขณะนี้
    </div>

  <?php } else { ?>

    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">รายการการแจ้งเตือนล่าสุด</h6>
      </div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">

          <?php foreach ($notifications as $n) : 
            $is_unread = ($n->is_read == 0);
            $badge_class = notification_badge_class($n->notification_type);
            $link = notification_link($n);
          ?>
            <div class="list-group-item d-flex justify-content-between align-items-start <?php echo $is_unread ? 'bg-light' : ''; ?>">
              
              <div class="mr-3">
                <span class="badge <?php echo $badge_class; ?> mr-2">
                  <?php echo h($n->notification_type); ?>
                </span>
                <span class="<?php echo $is_unread ? 'font-weight-bold' : ''; ?>">
                  <?php echo h($n->message); ?>
                </span>
                <div class="small text-muted mt-1">
                  <?php echo h(thai_date($n->created_at)); ?>
                  <?php if ($n->action_taken) { ?>
                    · <span class="text-success">ดำเนินการแล้ว</span>
                  <?php } ?>
                </div>
              </div>

              <?php if ($is_unread) { ?>
                <span class="badge badge-primary">ยังไม่อ่าน</span>
              <?php } else { ?>
                <span class="badge badge-secondary">อ่านแล้ว</span>
              <?php } ?>
              </div>
          <?php endforeach; ?>

        </div>
      </div>
    </div>

  <?php } ?>

</div>

<?php
include("../../private/shared/footerheadquarter.php");
?>
<script src="../js/fvscis.js"></script> 
<?php
include("../../private/shared/footerall.php");
?>
