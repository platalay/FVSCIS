<?php
require_once('../../private/initialize.php');
$session->require_role(['headquarter']);

// ดึงคำขอทั้งหมด (จะสั่ง ORDER ตาม created_at ก็ได้)

$requests = InspectionRequest::find_all();

// ดึงหน่วยงานทั้งหมดไว้ map id -> name (ลดการ query ซ้ำ ๆ)
$departments = Department::find_all();
$department_map = [];
foreach($departments as $dept) {
    $department_map[$dept->id] = $dept;
}

include("../../private/shared/headerheadquarter.php");
include("../../private/shared/sidebarheadquarter.php");
include("../../private/shared/topbarheadquarter.php");
?>

<div class="container-fluid">

  <!-- หัวข้อหน้า -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">คำขอรับรองสุขอนามัยเรือ</h1>
      <div class="text-muted small">
        ผู้ดูแลระบบสามารถดูคำขอทั้งหมดในระบบ พร้อมสถานะและหน่วยตรวจที่รับผิดชอบ
      </div>
    </div>
  </div>

  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
      <h6 class="m-0 font-weight-bold text-primary">รายการคำขอทั้งหมด</h6>
      <!-- ตรงนี้อนาคตจะใส่ filter ตามสถานะ / หน่วยงานได้ -->
    </div>
    <div class="card-body">

      <div class="table-responsive">
        <table class="table table-hover table-sm" id="dataTable">
          <thead class="thead-light">
            <tr>
              <th class="text-center">เลขที่คำขอ</th>
              <th>ชื่อเรือ</th>
              <th>เจ้าของเรือ</th>
              <th class="text-center">สถานะ</th>
              <th>หน่วยตรวจ</th>
              <th class="text-center">วันที่ยื่นขอ</th>
              <th>วันที่นัดตรวจ</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($requests)) { ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                ยังไม่มีคำขอรับรองสุขอนามัยเรือในระบบ
              </td>
            </tr>
          <?php } else { ?>
            <?php foreach($requests as $req) { ?>

              <?php
                // หา department ที่รับผิดชอบจาก map
                $dept_name = '-';
                if(!empty($req->department_id) && isset($department_map[$req->department_id])) {
                    $dept = $department_map[$req->department_id];
                    // ปรับ field ตามจริง เช่น $dept->name / $dept->department_name
                    $dept_name = $dept->name ?? $dept->department_name ?? ('หน่วย #' . $dept->id);
                }

                // กำหนดสี badge ตาม status
                $badge_class = 'badge-secondary';
                switch($req->status) {
                  case 'pending':
                    $badge_class = 'badge-warning';
                    break;
                  case 'inspecting':
                    $badge_class = 'badge-info';
                    break;
                  case 'passed':
                  case 'conditional':
                  case 'completed':
                    $badge_class = 'badge-success';
                    break;
                  case 'failed':
                    $badge_class = 'badge-danger';
                    break;
                }
              ?>

              <tr>
                <td class="text-center">
                  <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ภายใน foreach ของ Officer -->
                                        
                                        <button class="btn btn-primary btn-sm" title="แก้ไขคำขอ"
                                                style="width: 35px; height: 35px;"
                                                onclick="editInspectionRequest(<?= $req->request_code ?>)">
                                            <i class="fas fa-edit text-white"></i>
                                        </button>
                  </div>
                </td>
                <td><?php echo h($req->vessel_name ?? '-'); ?></td>
                <td><?php echo h($req->owner_name ?? '-'); ?></td>

                <td class="text-center">
                  <span class="badge <?php echo $badge_class; ?>">
                    <?php echo InspectionRequest::status_text($req->status); ?>
                  </span>
                </td>

                <td><?php echo h($dept_name); ?></td>

                <td class="text-center">
                  <?php echo !empty($req->created_at)
                    ? date('d/m/Y', strtotime($req->created_at))
                    : '-'; ?>
                </td>
                <td class="text-center">
                  <?php echo !empty($req->confirmed_inspect_date)
                    ? date('d/m/Y', strtotime($req->confirmed_inspect_date))
                    : '-'; ?>
                </td>
              </tr>

            <?php } // endforeach ?>
          <?php } // endif ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<?php
include("../../private/shared/footerheadquarter.php");
?>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
