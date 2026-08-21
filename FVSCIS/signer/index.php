<?php
require_once('../../private/initialize.php');
$Signer = Officer::find_by_id($session->user_id());
if(!$Signer) { redirect_to('../login.php'); }
$department_id =$Signer->departments_id;
$department = Department::find_by_id($department_id);
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
$complete_list = InspectionRequest::find_recent_by_department_groups_and_status(
    $group_ids,
    ['passed','failed'],
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
include("../../private/shared/topbarsigner.php");
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

   <?php
// ---------- กลุ่มซ้าย: นับตามหน่วยประเมิน ----------
$eva_inactive = FvSanitationCertificationOld::count_by_status_evaluation_agency('inactive', $department_id);
$eva_pending  = FvSanitationCertificationOld::count_by_status_evaluation_agency('pending',  $department_id);
$eva_fail     = FvSanitationCertificationOld::count_by_status_evaluation_agency('fail',     $department_id);
$eva_active   = FvSanitationCertificationOld::count_by_status_evaluation_agency('active',   $department_id);

// ---------- กลุ่มขวา: นับตามความรับผิดชอบ ----------
$res_inactive = FvSanitationCertificationOld::count_by_status_responsible_unit('inactive', $department_id);
$res_pending  = FvSanitationCertificationOld::count_by_status_responsible_unit('pending',  $department_id);
$res_fail     = FvSanitationCertificationOld::count_by_status_responsible_unit('fail',     $department_id);
$res_active   = FvSanitationCertificationOld::count_by_status_responsible_unit('active',   $department_id);
?>

<div class="row mb-4">

    <!-- ================= กลุ่มซ้าย ================= -->
    <div class="col-xl-6 col-lg-12">
        <h6 class="text-muted mb-2 border-bottom pb-1">
            จำนวนเรือที่อนุมัติตามสถานะ (หน่วยประเมิน)
        </h6>

        <div class="row">

            <!-- inactive -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card" 
                     style="border-left:4px solid rgba(108,117,125,0.4);background:rgba(108,117,125,0.10);">
                    <div class="text-xs font-weight-bold text-secondary mb-1">เรือไม่ ACTIVE</div>
                    <div class="h5 font-weight-bold"><?= number_format($eva_inactive); ?> ลำ</div>
                    <i class="fas fa-ship icon text-secondary"></i>
                </div>
            </div>

            <!-- pending -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #f7c948;background:rgba(247,201,72,0.18);">
                    <div class="text-xs font-weight-bold" style="color:#B68B00;">อยู่ระหว่างยื่นตรวจ</div>
                    <div class="h5 font-weight-bold"><?= number_format($eva_pending); ?> ลำ</div>
                    <i class="fas fa-clock icon" style="color:#B68B00;"></i>
                </div>
            </div>

            <!-- fail -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #e35d6a;background:rgba(227,93,106,0.20);">
                    <div class="text-xs font-weight-bold text-danger">ตรวจไม่ผ่าน</div>
                    <div class="h5 font-weight-bold"><?= number_format($eva_fail); ?> ลำ</div>
                    <i class="fas fa-times-circle icon text-danger"></i>
                </div>
            </div>

            <!-- active -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #4caf91;background:rgba(76,175,145,0.18);">
                    <div class="text-xs font-weight-bold" style="color:#2d7a65;">ได้รับ สร.3</div>
                    <div class="h5 font-weight-bold"><?= number_format($eva_active); ?> ลำ</div>
                    <i class="fas fa-check-circle icon" style="color:#2d7a65;"></i>
                </div>
            </div>

        </div><!-- row -->
    </div><!-- col -->

    <!-- ================= กลุ่มขวา ================= -->
    <div class="col-xl-6 col-lg-12">
        <?php if (($department->id >= 1 && $department->id <= 9)) { ?>

        <h6 class="text-muted mb-2 border-bottom pb-1 text-right">
            จำนวนข้อมูลเรือในความรับผิดชอบ (ตามสถานะ)
        </h6>

        <div class="row">

            <!-- inactive -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid rgba(108,117,125,0.4);background:rgba(108,117,125,0.10);">
                    <div class="text-xs font-weight-bold text-secondary">เรือไม่ ACTIVE</div>
                    <div class="h5 font-weight-bold"><?= number_format($res_inactive); ?> ลำ</div>
                    <i class="fas fa-ship icon text-secondary"></i>
                </div>
            </div>

            <!-- pending -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #f7c948;background:rgba(247,201,72,0.18);">
                    <div class="text-xs font-weight-bold" style="color:#B68B00;">อยู่ระหว่างยื่นตรวจ</div>
                    <div class="h5 font-weight-bold"><?= number_format($res_pending); ?> ลำ</div>
                    <i class="fas fa-clock icon" style="color:#B68B00;"></i>
                </div>
            </div>

            <!-- fail -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #e35d6a;background:rgba(227,93,106,0.20);">
                    <div class="text-xs font-weight-bold text-danger">ตรวจไม่ผ่าน</div>
                    <div class="h5 font-weight-bold"><?= number_format($res_fail); ?> ลำ</div>
                    <i class="fas fa-times-circle icon text-danger"></i>
                </div>
            </div>

            <!-- active -->
            <div class="col-6 col-md-3 mb-3">
                <div class="dashboard-card"
                     style="border-left:4px solid #4caf91;background:rgba(76,175,145,0.18);">
                    <div class="text-xs font-weight-bold" style="color:#2d7a65;">ได้รับ สร.3</div>
                    <div class="h5 font-weight-bold"><?= number_format($res_active); ?> ลำ</div>
                    <i class="fas fa-check-circle icon" style="color:#2d7a65;"></i>
                </div>
            </div>

        </div><!-- row -->
        <?php } ?>
    </div><!-- col -->

</div><!-- row -->


  <!-- Row: Pending in Department + My Tasks Today -->
  <div class="row">

    <!-- คำขอที่รอดำเนินการในหน่วยของท่าน -->
    <div class="col-lg-8 mb-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">คำขอที่รอดำเนินการในหน่วยของท่าน</h6>
          <a href="incoming_requests.php" class="small">ดูทั้งหมด</a>
        </div>
        <div class="card-body">
          <?php if (empty($complete_list)) { ?>
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
                  <?php foreach ($complete_list as $req) : ?>
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
                        <a href="incoming_requests.php?shipcode=<?= urlencode(h($req->ship_code)); ?>" class="btn btn-sm btn-outline-primary">
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
          <h6 class="m-0 font-weight-bold text-info">ภารกิจตรวจเรือวันนี้ของหน่วย</h6>
        </div>
        <div class="card-body">
          <?php if (empty($my_today_tasks)) { ?>
            <div class="text-muted small">วันนี้หน่วยงานไม่มีภารกิจตรวจเรือ</div>
          <?php } else { ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($my_today_tasks as $task) : ?>
                <li class="list-group-item px-0">
                  <div class="font-weight-bold">
                    <?php echo h($task->ship_code); ?> - <?php echo h($task->vessel_name); ?>
                  </div>
                  <div class="small text-muted">
                    นัดตรวจ: <?php echo h(thai_date($task->confirmed_inspect_date)); ?><br>
                    สถานที่: <?php echo h($task->port_name ?? '-'); ?>
                  </div>
                  <div class="mt-1">
                    <a href="incoming_requests.php?shipcode=<?= urlencode(h($task->ship_code)); ?>" 
                      class="btn btn-sm btn-outline-secondary">
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
