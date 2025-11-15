<?php
require_once('../../private/initialize.php');
$Signer = Officer::find_by_id($session->user_id());
if(!$Signer) { redirect_to('../login.php'); }

// ====== หา DepartmentGroup ที่อยู่ภายใต้การลงนามของ signer คนนี้ ======
$groups = DepartmentGroup::find_by_officer_id($Signer->id);
$group_ids = [];
$group_names = [];

foreach ($groups as $g) {
    $group_ids[]   = (int)$g->id;
    $group_names[] = $g->name;
}

// ถ้าไม่มี group เลย กัน error ไว้
if (empty($group_ids)) {
    $group_ids = [0]; // ให้ query ว่าง
}

// ====== สถิติคำขอภายใต้สังกัด (ทุกหน่วยงานใน group เหล่านี้) ======
$total_requests      = InspectionRequest::count_by_department_groups($group_ids);
$pending_requests    = InspectionRequest::count_by_department_groups($group_ids, 'pending');
$inspecting_requests = InspectionRequest::count_by_department_groups($group_ids, 'inspecting');
$completed_requests  = InspectionRequest::count_by_department_groups($group_ids, 'completed'); 
// หรือปรับตามสถานะจริง เช่น passed/failed/conditional ฯลฯ

// ====== รายการคำขอที่รอดำเนินการในสังกัด (ล่าสุด 10 รายการ) ======
$pending_list = InspectionRequest::find_recent_by_department_groups_and_status(
    $group_ids,
    ['pending', 'inspecting'],
    10
);

// ====== เตรียมชื่อหน่วยงานรับเรื่องจาก department_id ======
$departments = Department::find_all();
$dept_map = [];
foreach ($departments as $d) {
    $dept_map[$d->id] = $d;
}

include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarsigner.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <!-- Heading -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">หน้าผู้ลงนามอนุมัติ</h1>
      <div class="text-muted small mt-1">
        สังกัด:
        <?php echo h(implode(' , ', $group_names) ?: 'ไม่พบสังกัดที่ผูกกับผู้ใช้งาน'); ?>
      </div>
    </div>
  </div>

  <!-- Row: Summary Cards -->
  <div class="row">

    <!-- คำขอทั้งหมดในสังกัด -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                คำขอทั้งหมดในสังกัด
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

    <!-- ตรวจเสร็จสิ้นแล้ว -->
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

  <!-- Row: Pending in Groups -->
  <div class="row">

    <div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">คำขอที่รอดำเนินการในสังกัดของท่าน</h6>
          <a href="inspection_requests.php" class="small">ดูทั้งหมด</a>
        </div>
        <div class="card-body">
          <?php if (empty($pending_list)) { ?>
            <div class="text-muted small">ยังไม่มีคำขอที่รอดำเนินการในสังกัดของท่าน</div>
          <?php } else { ?>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>เลขที่คำขอ</th>
                    <th>ทะเบียนเรือ</th>
                    <th>ชื่อเรือ</th>
                    <th>หน่วยงานที่รับเรื่อง</th>
                    <th>วันที่ยื่น</th>
                    <th>วันที่นัดตรวจ</th>
                    <th>สถานะ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_list as $req) : ?>
                    <?php
                      $dept_name = '-';
                      if (!empty($req->department_id) && isset($dept_map[$req->department_id])) {
                          $dept = $dept_map[$req->department_id];
                          $dept_name = $dept->name ?? $dept->department_name ?? ('หน่วย #' . $dept->id);
                      }
                    ?>
                    <tr>
                      <td><?php echo h($req->request_number ?? $req->id); ?></td>
                      <td><?php echo h($req->ship_code); ?></td>
                      <td><?php echo h($req->vessel_name); ?></td>
                      <td><?php echo h($dept_name); ?></td>
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
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>

  </div>
</div><!-- End Page Content -->

<?php
include("../../private/shared/footerofficer.php");
include("../../private/shared/footerall.php");
?>