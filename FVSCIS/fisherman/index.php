<?php
require_once('../../private/initialize.php');
$session->require_role(['fisherman']);

// ดึงข้อมูลชาวประมง (ปรับให้ตรงกับระบบจริง)
$fisherman = Fisherman::find_by_id($session->user_id());

// ---- ตัวอย่างการเตรียมข้อมูลสถิติ (ปรับเมธอดให้ตรงกับคลาสจริง) ----
// รวมคำขอทั้งหมดของชาวประมงคนนี้
$total_requests = InspectionRequest::count_by_fisherman($fisherman->id ?? 0);

// คำขอที่ยังอยู่ระหว่างดำเนินการ (สถานะตัวอย่าง: pending, inspecting, conditional)
$pending_requests = InspectionRequest::count_by_fisherman_and_status(
  $fisherman->id ?? 0,
  ['pending', 'inspecting', 'conditional']
);

// จำนวนใบรับรองที่ยังไม่หมดอายุ (ตัวอย่าง)
$active_certificates = FvSanitationCertificationOld::count_active_by_fisherman($fisherman->id ?? 0);

// 5 คำขอล่าสุด
$latest_requests = InspectionRequest::find_recent_by_fisherman($fisherman->id ?? 0, 5);

include("../../private/shared/headeruser.php");
include("../../private/shared/sidebaruser.php");
include("../../private/shared/topbaruser.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Heading -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      
      <p class="mb-0 text-muted">
        ยินดีต้อนรับคุณ <strong><?php echo h($fisherman->full_name ?? 'ชาวประมง'); ?></strong>
      </p>
    </div>
  </div>

  <!-- Alert แนะนำสั้นๆ -->
  <div class="alert alert-info mb-4">
    <i class="fas fa-info-circle mr-1"></i>
    หน้านี้จะแสดงภาพรวมคำขอรับการตรวจสุขอนามัยเรือ และใบรับรองของคุณ
  </div>

  <!-- Row: Statistic Cards -->
  <div class="row">

    <!-- การขอรับการตรวจทั้งหมด -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                คำขอรับการตรวจทั้งหมด
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo (int) $total_requests; ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- กำลังดำเนินการ -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card border-left-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                คำขอที่กำลังดำเนินการ
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo (int) $pending_requests; ?> เรื่อง
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-spinner fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ใบรับรองที่ยังใช้ได้ -->
    <div class="col-xl-4 col-md-6 mb-4">
      <div class="card border-left-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                ใบรับรองที่ยังไม่หมดอายุ
              </div>
              <div class="h5 mb-0 font-weight-bold text-gray-800">
                <?php echo (int) $active_certificates; ?> ใบ
              </div>
            </div>
            <div class="col-auto">
              <i class="fas fa-certificate fa-2x text-gray-300"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.row -->

  <!-- Row: Latest Requests Table -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">คำขอรับการตรวจล่าสุดของคุณ</h6>
          <a href="mystatus.php" class="btn btn-sm btn-outline-primary">
            ดูทั้งหมด
          </a>
        </div>
        <div class="card-body">
          <?php if (!empty($latest_requests)) { ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 10%;">เลขที่คำขอ</th>
                    <th style="width: 25%;">ชื่อเรือ</th>
                    <th style="width: 20%;">ท่าเรือ</th>
                    <th style="width: 15%;">สถานะ</th>
                    <th style="width: 15%;">วันที่ยื่นคำขอ</th>
                    <th style="width: 15%;">วันที่นัดตรวจ</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($latest_requests as $req) { ?>
                  <tr>
                    <td><?php echo h($req->request_code ?? $req->id); ?></td>
                    <td><?php echo h($req->vessel_name ?? '-'); ?></td>
                    <td><?php echo h($req->port_name ?? '-'); ?></td>
                    <td>
                      <?php
                        // แปลงสถานะเป็นข้อความไทยแบบสั้น (ปรับ mapping ได้)
                        $statusMap = [
                          'pending'     => 'รอดำเนินการ',
                          'inspecting'  => 'อยู่ระหว่างตรวจ',
                          'passed'      => 'ผ่าน',
                          'conditional' => 'ผ่านแบบมีเงื่อนไข',
                          'failed'      => 'ไม่ผ่าน',
                          'completed'   => 'อนุมัติแล้ว'
                        ];
                        $status = $req->status ?? 'pending';
                        echo h($statusMap[$status] ?? $status);
                      ?>
                    </td>
                    <td><?php echo h(thai_date($req->created_at ?? '')); ?></td>
                    <td><?php echo h(thai_date($req->inspect_date ?? '')); ?></td>
                  </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          <?php } else { ?>
            <p class="mb-0 text-muted">ยังไม่มีข้อมูลคำขอรับการตรวจ</p>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- /.container-fluid -->

<?php
include("../../private/shared/footeruser.php");
?>
<script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
