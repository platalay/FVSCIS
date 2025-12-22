<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);

// ดึงคำขอทั้งหมด (จะสั่ง ORDER ตาม created_at ก็ได้)

$requests = InspectionRequest::find_all();

// ดึงหน่วยงานทั้งหมดไว้ map id -> name (ลดการ query ซ้ำ ๆ)
$departments = Department::find_all();
$department_map = [];
foreach($departments as $dept) {
    $department_map[$dept->id] = $dept;
}

include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarsigner.php");
include("../../private/shared/topbarofficer.php");
?>

<div class="container-fluid">

  <!-- หัวข้อหน้า -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-0 text-gray-800">คำขอรับรองสุขอนามัยเรือ</h1>
      <div class="text-muted small">
        ผู้อนุมัติสามารถดูคำขอในระบบเฉพาะหน่วยในสังกัด พร้อมสถานะและหน่วยตรวจที่รับผิดชอบ
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
              <th class="d-none"></th>
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
                <td colspan="7" class="text-center text-muted py-4">
                  ยังไม่มีคำขอรับรองสุขอนามัยเรือในระบบ
                </td>
              </tr>
            <?php } else { ?>
              <?php foreach($requests as $req) { ?>

                <?php
                  // ==========================
                  // หน่วยตรวจ (ของเดิม)
                  // ==========================
                  $dept_name = '-';
                  if(!empty($req->department_id) && isset($department_map[$req->department_id])) {
                      $dept = $department_map[$req->department_id];
                      $dept_name = $dept->name ?? $dept->department_name ?? ('หน่วย #' . $dept->id);
                  }

                  // ==========================
                  // สีแถว + badge (ยึดแนวเดียวกับอีกตาราง)
                  // ==========================
                  $trClass     = '';
                  $status      = $req->status;
                  $hasDate     = !empty($req->confirmed_inspect_date) && $req->confirmed_inspect_date !== '0000-00-00';
                  $isConfirm   = (int)($req->is_confirm ?? 0);

                  // 1) PENDING: ยังไม่ได้นัดตรวจ
                  if ($status === InspectionRequest::STATUS_PENDING && !$hasDate) {
                      $trClass = 'tr-not-scheduled';
                  }
                  // 2) PENDING: นัดแล้ว แต่ผู้ยื่นยังไม่ยืนยัน
                  else if ($status === InspectionRequest::STATUS_PENDING && $hasDate && $isConfirm === 0) {
                      $trClass = 'tr-wait-confirm';
                  }
                  // 3) PENDING: นัดแล้ว และผู้ยื่นยืนยันแล้ว
                  else if ($status === InspectionRequest::STATUS_PENDING && $hasDate && $isConfirm === 1) {
                      $trClass = 'tr-pending-confirmed';
                  }
                  // 4) อยู่ระหว่างตรวจ / ส่งอนุมัติ (inspecting + passed)
                  else if ($status === InspectionRequest::STATUS_INSPECTING || $status === InspectionRequest::STATUS_PASSED) {
                      $trClass = 'tr-inspecting';
                  }
                  // 5) กระบวนการเสร็จสิ้นแล้ว (failed/conditional/completed)
                  else if (
                      $status === InspectionRequest::STATUS_FAILED ||
                      $status === InspectionRequest::STATUS_CONDITIONAL ||
                      $status === InspectionRequest::STATUS_COMPLETED
                  ) {
                      $trClass = 'tr-completed';
                  }
                  // 6) ยกเลิก
                  else if ($status === InspectionRequest::STATUS_CANCELLED) {
                      $trClass = 'tr-cancelled';
                  }

                  // badge ตาม status (แบบ bootstrap 4 ใช้ badge-xxx)
                  $badge_class = 'badge-secondary';
                  if ($status === InspectionRequest::STATUS_PENDING)         $badge_class = 'badge-warning';
                  else if ($status === InspectionRequest::STATUS_INSPECTING) $badge_class = 'badge-primary';
                  else if ($status === InspectionRequest::STATUS_PASSED)     $badge_class = 'badge-info';
                  else if ($status === InspectionRequest::STATUS_FAILED)     $badge_class = 'badge-danger';
                  else if ($status === InspectionRequest::STATUS_CONDITIONAL)$badge_class = 'badge-info';
                  else if ($status === InspectionRequest::STATUS_COMPLETED)  $badge_class = 'badge-success';
                  else if ($status === InspectionRequest::STATUS_CANCELLED)  $badge_class = 'badge-secondary';
                ?>

                <tr class="<?= h($trClass) ?>">
                  <td class="d-none"></td>
                  <td class="text-center">
                    <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                      <button class="btn btn-primary btn-sm" title="แก้ไขคำขอ"
                              style="width: 35px; height: 35px;"
                              onclick="editInspectionRequest(<?= (int)$req->request_code ?>)">
                          <i class="fas fa-edit text-white"></i>
                      </button>
                    </div>
                  </td>

                  <td><?= h($req->vessel_name ?? '-') ?></td>
                  <td><?= h($req->owner_name ?? '-') ?></td>

                  <td class="text-center">
                    <span class="badge <?= h($badge_class) ?>">
                      <?= h(InspectionRequest::status_text($status)) ?>
                    </span>
                  </td>

                  <td><?= h($dept_name) ?></td>

                  <td class="text-center">
                    <?= !empty($req->created_at) ? thai_date_safe($req->created_at) : '-' ?>
                  </td>

                  <td class="text-center">
                    <?= !empty($req->confirmed_inspect_date) ? thai_date_safe($req->confirmed_inspect_date) : '-' ?>
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
include("../../private/shared/footerofficer.php");
?>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/fvscis.js"></script>
<?php
include("../../private/shared/footerall.php");
?>
