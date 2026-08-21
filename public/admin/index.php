<?php
require_once('../../private/initialize.php');
$session->require_role(['admin']);

// ดึงสถิติเบื้องต้น
$total_fisherman      = Fisherman::count_all();          // จำนวนชาวประมงทั้งหมด
$total_officer        = Officer::count_all();            // จำนวนเจ้าหน้าที่ทั้งหมด
$total_users          = $total_fisherman + $total_officer;

$total_requests       = InspectionRequest::count_all();  // จำนวนคำขอทั้งหมด
$pending_requests     = InspectionRequest::count_by_status('pending');    // รอดำเนินการ
$inspecting_requests  = InspectionRequest::count_by_status('inspecting'); // อยู่ระหว่างตรวจ;
$completed_requests   = InspectionRequest::count_by_status('completed');  // อนุมัติแล้ว;

// คำขอตรวจล่าสุด (เช่น 10 รายการ)
$recent_requests = InspectionRequest::find_recent_for_admin(10);

// ผู้ใช้ที่สมัครล่าสุด (เอาเฉพาะ Fisherman ก็ได้ หรือจะรวม Officer ด้วยก็ได้)
$recent_fishermen = Fisherman::find_recent(5);
$recent_officers  = Officer::find_recent(5);

include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- หัวข้อหลัก -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">หน้าผู้ดูแลระบบ (Admin)</h1>
      <div class="text-muted small">
        ภาพรวมการใช้งานระบบสารสนเทศเพื่อการรับรองสุขอนามัยเรือประมง
      </div>
    </div>
  </div>

  <!-- แถวบน: สถิติโดยรวม -->
  <div class="row">

    <!-- จำนวนผู้ใช้งานทั้งหมด -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                ผู้ใช้งานทั้งหมด (ชาวประมง + เจ้าหน้าที่)
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_users); ?> คน
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-users fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- จำนวนชาวประมง -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                ชาวประมงที่ลงทะเบียน
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_fisherman); ?> คน
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-ship fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- เจ้าหน้าที่ -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                เจ้าหน้าที่กรมประมง
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_officer); ?> คน
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-user-tie fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- คำขอทั้งหมด -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                คำขอรับรองสุขอนามัยเรือทั้งหมด
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_requests); ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-file-alt fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- แถวสอง: สถานะคำขอ & รายการล่าสุด -->
  <div class="row">

    <!-- การกระจายสถานะคำขอแบบ card -->
    <div class="col-xl-4 col-lg-5 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">สถานะคำขอรับรองสุขอนามัยเรือ</h6>
        </div>
        <div class="card-body">
          <div class="mb-2 small text-muted">จำนวนคำขอแยกตามสถานะ</div>

          <div class="d-flex justify-content-between mb-2">
            <span>รอดำเนินการ</span>
            <span class="font-weight-bold text-warning">
              <?php echo number_format($pending_requests); ?> เรื่อง
            </span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span>อยู่ระหว่างตรวจ</span>
            <span class="font-weight-bold text-info">
              <?php echo number_format($inspecting_requests); ?> เรื่อง
            </span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span>อนุมัติแล้ว</span>
            <span class="font-weight-bold text-success">
              <?php echo number_format($completed_requests); ?> เรื่อง
            </span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span>ใบรับรองทั้งหมด</span>
            <span class="font-weight-bold text-success">
              <?php echo number_format($completed_requests); ?> เรื่อง
            </span>
          </div>

          <div class="text-right mt-3">
            <a href="inspection_requests.php" class="small">ดูรายละเอียดคำขอทั้งหมด &rarr;</a>
          </div>
        </div>
      </div>
    </div>

    <!-- คำขอล่าสุด -->
    <div class="col-xl-8 col-lg-7 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">คำขอรับรองสุขอนามัยเรือล่าสุด</h6>
          <a href="inspection_requests.php" class="small">ดูทั้งหมด</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="thead-light">
                <tr>
                  <th>เลขที่คำขอ</th>
                  <th>ชื่อเรือ</th>
                  <th>เจ้าของเรือ</th>
                  <th>สถานะ</th>
                  <th>วันที่ยื่น</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($recent_requests)) { ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      ยังไม่มีคำขอในระบบ
                    </td>
                  </tr>
                <?php } else { ?>
                  <?php foreach($recent_requests as $req) { ?>
                    <tr>
                      <td><?php echo h($req->request_code ?? $req->id); ?></td>
                      <td><?php echo h($req->vessel_name ?? '-'); ?></td>
                      <td><?php echo h($req->owner_name ?? '-'); ?></td>
                      <td>
                        <span class="badge badge-pill
                          <?php
                            switch($req->status) {
                              case 'pending':    echo 'badge-warning'; break;
                              case 'inspecting': echo 'badge-info';    break;
                              case 'completed':  echo 'badge-success'; break;
                              default:           echo 'badge-secondary';
                            }
                          ?>">
                          <?php echo InspectionRequest::status_text($req->status); ?>
                        </span>
                      </td>
                      <td><?php echo date('d/m/Y', strtotime($req->created_at)); ?></td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- แถวสาม: ผู้ใช้ล่าสุด -->
  <div class="row">

    <div class="col-lg-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">ชาวประมงที่สมัครล่าสุด</h6>
          <a href="fisherman.php" class="small">จัดการผู้ใช้งาน</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>ชื่อ–สกุล</th>
                  <th>ชื่อผู้ใช้งาน</th>
                  <th>วันที่สมัคร</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($recent_fishermen)) { ?>
                  <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>
                <?php } else { ?>
                  <?php foreach($recent_fishermen as $f) { ?>
                    <tr>
                      <td><?php echo h($f->full_name ?? '-'); ?></td>
                      <td><?php echo h($f->username ?? '-'); ?></td>
                      <td><?php echo date('d/m/Y', strtotime($f->created_at)); ?></td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow h-100">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">เจ้าหน้าที่ที่เพิ่มล่าสุด</h6>
          <a href="officer.php" class="small">จัดการเจ้าหน้าที่</a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="thead-light">
                <tr>
                  <th>ชื่อ–สกุล</th>
                  <th>ชื่อผู้ใช้งาน</th>
                  <th>หน่วยงาน</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($recent_officers)) { ?>
                  <tr><td colspan="3" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>
                <?php } else { ?>
                  <?php foreach($recent_officers as $o) { ?>
                    <tr>
                      <td><?php echo h($o->full_name ?? '-'); ?></td>
                      <td><?php echo h($o->username ?? '-'); ?></td>
                      <?php $department = Department::get_name_by_id($o->departments_id);?>
                      <td><?php echo $department ?></td>
                    </tr>
                  <?php } ?>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>
<!-- /.container-fluid -->

<?php
include("../../private/shared/footeradmin.php");
?>
<script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
