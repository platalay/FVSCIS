<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

$Officer = Officer::find_by_id($session->user_id());
if(!$Officer) { redirect_to('../login.php'); }

$department_id = $Officer->departments_id ?? null;
error_log("department_id = ". $department_id);
// ====== ดึงสถิติหลักของหน่วย ======
$total_requests     = InspectionRequest::count_by_department($department_id);
$pending_requests   = InspectionRequest::count_by_department_and_status($department_id, 'pending');
$inspecting_requests= InspectionRequest::count_by_department_and_status($department_id, 'inspecting');
$completed_requests = InspectionRequest::count_by_department_and_status($department_id, 'completed'); // หรือ 'passed'/'failed' รวมกันตามที่ใช้จริง

// ====== ดึงคำขอที่รอดำเนินการของหน่วย (ล่าสุด 10 รายการ) ======
$pending_list = InspectionRequest::find_recent_by_department_and_status(
    $department_id,
    ['pending', 'inspecting'], // ปรับตามสถานะที่ถือว่า "รอดำเนินการ"
    10
);

// ====== ดึงภารกิจของเจ้าหน้าที่วันนี้ ======
$today = date('Y-m-d');
$my_today_tasks = InspectionRequest::find_today_tasks_by_officer($department_id, $today);

include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Heading -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">หน้าผู้ตรวจประเมิน</h1>
      <div class="text-muted small mt-1">
        หน่วยงาน: <?php echo h($Officer->department_name ?? ''); ?>
        (รหัสหน่วย: <?php echo h($department_id); ?>)
      </div>
    </div>
  </div>

  <!-- Row: Summary Cards -->
  <div class="row">

    <!-- คำขอทั้งหมดในหน่วย -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                คำขอทั้งหมดในหน่วย
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_requests); ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- รอดำเนินการ -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                รอดำเนินการ
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($pending_requests); ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- อยู่ระหว่างตรวจ -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                อยู่ระหว่างตรวจ
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($inspecting_requests); ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-search-location fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- เสร็จสิ้นแล้ว -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                ตรวจเสร็จสิ้นแล้ว
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($completed_requests); ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-check-circle fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Row: Pending in Department + My Tasks Today -->
  <div class="row">

    <!-- คำขอที่รอดำเนินการในหน่วยของท่าน -->
    <div class="col-lg-8 mb-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">คำขอที่รอดำเนินการในหน่วยของท่าน</h6>
          <a href="request_list.php" class="small">ดูทั้งหมด</a>
        </div>
        <div class="card-body">
          <?php if (empty($pending_list)) { ?>
            <div class="text-muted small">ยังไม่มีคำขอที่รอดำเนินการในหน่วยของท่าน</div>
          <?php } else { ?>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>เลขที่คำขอ</th>
                    <th>ทะเบียนเรือ</th>
                    <th>ชื่อเรือ</th>
                    <th>วันที่ยื่น</th>
                    <th>วันที่นัดตรวจ</th>
                    <th>สถานะ</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_list as $req) : ?>
                    <tr>
                      <td><?php echo h($req->request_number ?? $req->id); ?></td>
                      <td><?php echo h($req->ship_code); ?></td>
                      <td><?php echo h($req->vessel_name); ?></td>
                      <td><?php echo h(thai_date($req->created_at)); ?></td>
                      <td>
                        <?php
                          echo (!empty($req->confirmed_inspect_date) && $req->confirmed_inspect_date != '0000-00-00'
                            ? h(thai_date($req->confirmed_inspect_date))
                            : '-'
                          );
                        ?>
                      </td>
                      <td><?php echo h($req->status_label()); ?></td>
                      <td class="text-right">
                        <a href="request_detail.php?id=<?php echo h($req->id); ?>" class="btn btn-sm btn-outline-primary">
                          จัดการ
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

    <!-- ภารกิจของฉันวันนี้ -->
    <div class="col-lg-4 mb-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-info">ภารกิจของฉันวันนี้</h6>
        </div>
        <div class="card-body">
          <?php if (empty($my_today_tasks)) { ?>
            <div class="text-muted small">วันนี้ยังไม่มีภารกิจตรวจเรือที่ได้รับมอบหมาย</div>
          <?php } else { ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($my_today_tasks as $task) : ?>
                <li class="list-group-item px-0">
                  <div class="font-weight-bold">
                    <?php echo h($task->ship_code); ?> - <?php echo h($task->vessel_name); ?>
                  </div>
                  <div class="small text-muted">
                    นัดตรวจ: <?php echo h(display_datetime($task->inspect_date)); ?><br>
                    สถานที่: <?php echo h($task->port_name ?? '-'); ?>
                  </div>
                  <div class="mt-1">
                    <a href="request_detail.php?id=<?php echo h($task->id); ?>" class="btn btn-sm btn-outline-secondary">
                      เปิดดูคำขอ
                    </a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php } ?>
        </div>
      </div>
    </div>

  </div>

</div>

<?php
include("../../private/shared/footerofficer.php");
?>
<script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
