<?php
require_once('../../private/initialize.php');
$session->require_role(['headquarter']);
$Officer = Officer::find_by_id($session->user_id());
// เตรียมข้อมูลจำนวนนับตาม responsible_unit
$units = [];
for ($i = 1; $i <= 9; $i++) {
    $group = DepartmentGroup::find_by_id($i);
    if ($group) {
        $units[] = [
            'id'    => $i,
            'name'  => $group->name,
            'count' => FvSanitationCertificationOld::count_active_by_responsible_unit($i),
        ];
    }
}

// นับรวมทั้งหมด (ทุก responsible_unit)
$total_active = FvSanitationCertificationOld::count_active_by_responsible_unit();
// คำขอตรวจล่าสุด (เช่น 10 รายการ)
$recent_requests = InspectionRequest::find_recent_for_admin(10);

$total_requests       = InspectionRequest::count_all();  // จำนวนคำขอทั้งหมด
$pending_requests     = InspectionRequest::count_by_status('pending');    // รอดำเนินการ
$inspecting_requests  = InspectionRequest::count_by_status('inspecting'); // อยู่ระหว่างตรวจ;
$completed_requests   = InspectionRequest::count_by_status('completed');  // อนุมัติแล้ว;

include("../../private/shared/headerheadquarter.php");
include("../../private/shared/sidebarheadquarter.php");
include("../../private/shared/topbarheadquarter.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">ภาพรวมผู้ดูแลระบบส่วนกลาง</h1>
      <div class="text-muted small">
        ดูข้อมูลใบรับรองสุขอนามัยเรือทั้งหมด แยกตามหน่วยรับผิดชอบ
      </div>
    </div>
  </div>

  <!-- แถวการ์ดสรุป -->
  <div class="row">

    <?php foreach($units as $unit): ?>
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">

              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                  <?php echo h($unit['name']); ?>
                </div>
                <div class="h4 mb-0 font-weight-bold text-gray-800">
                  <?php echo number_format($unit['count']); ?> ฉบับ
                </div>
              </div>

              <div class="col-auto">
                <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
              </div>

            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- กล่องรวมทั้งหมด -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">

            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                รวมทั้งหมด (ทุกหน่วยรับผิดชอบ)
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_active); ?> ฉบับ
              </div>
            </div>

            <div class="col-auto">
              <i class="fas fa-list-ul fa-2x text-gray-300"></i>
            </div>

          </div>
        </div>
      </div>
    </div>
    <!-- กล่องคำขอตรวจทั้งระบบ -->
    <div class="col-xl-3 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">

            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                คำขอตรวจทั้งหมด
              </div>
              <div class="h4 mb-0 font-weight-bold text-gray-800">
                <?php echo number_format($total_requests); ?> ฉบับ
              </div>
            </div>

            <div class="col-auto">
              <i class="fas fa-list-ul fa-2x text-gray-300"></i>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
  <!-- แถวการ์ดสรุป -->
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


  <!-- ตรงนี้เต้ยจะใส่ตารางรายละเอียด / กราฟ / อื่น ๆ ต่อภายหลังก็ได้ -->


</div><!-- container-fluid -->

<?php
include("../../private/shared/footerheadquarter.php");
?>
<script src="../js/fvscis.js"></script>  
<?php
include("../../private/shared/footerall.php");
?>