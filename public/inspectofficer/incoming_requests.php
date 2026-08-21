<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
$Department = Department::find_by_id($Officer->departments_id);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <!-- ปุ่ม Add -->
                                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalManualCase" id="btnOpenManualCase">
                                <i class="fas fa-plus"></i> สร้างคำขอตรวจเรือแทนชาวประมง
                                </button>
                            </h6>
                        </div>
                        <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr style="font-size: 14px;">
                                        <th>ดำเนินการ</th>
                                        <th>หมายเลขทะเบียนเรือ</th>
                                        <th>ชื่อเรือ</th>
                                         <th>ท่าเรือที่นัดตรวจ</th>
                                        <th>ช่วงเวลาขอตรวจ</th>
                                        <th>วันนัดตรวจ</th>
                                        <th>ประเภทคำขอ</th>
                                        <th>วันที่ยื่นคำขอ</th>
                                        <th>วันที่นัดตรวจเรือ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                // ✅ ดึงเฉพาะคำขอที่ department_id ตรงกับเจ้าหน้าที่
                                $requests = InspectionRequest::find_by_department_id($Officer->departments_id); 

                                if (empty($requests)) :
                                ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">ยังไม่มีคำขอตรวจเรือที่รับผิดชอบ</td>
                                    </tr>
                                <?php
                                else:
                                    foreach ($requests as $req) :

                                        // ==========================
                                        // กำหนดสีแถว + ข้อความแสดงผล
                                        // ==========================
                                        $trClass     = '';
                                        $displayText = '';

                                        $status    = $req->status;
                                        $hasDate   = !empty($req->confirmed_inspect_date) && $req->confirmed_inspect_date !== '0000-00-00';
                                        $isConfirm = (int)($req->is_confirm ?? 0);
                                        $dateText  = $hasDate ? thai_date($req->confirmed_inspect_date) : '';

                                        // 1) PENDING: ยังไม่ได้นัดตรวจ
                                        if ($status === InspectionRequest::STATUS_PENDING && !$hasDate) {
                                            $trClass     = 'tr-not-scheduled';           // เทา
                                            $displayText = 'ยังไม่ได้นัดตรวจ';
                                        }
                                        // 2) PENDING: นัดแล้ว แต่ผู้ยื่นยังไม่ยืนยัน
                                        else if ($status === InspectionRequest::STATUS_PENDING && $hasDate && $isConfirm === 0) {
                                            $trClass     = 'tr-wait-confirm';            // เหลือง
                                            $displayText = 'นัดตรวจแล้ว (' . $dateText . ')';
                                        }
                                        // 3) PENDING: นัดแล้ว และผู้ยื่นยืนยันแล้ว
                                        else if ($status === InspectionRequest::STATUS_PENDING && $hasDate && $isConfirm === 1) {
                                            $trClass     = 'tr-pending-confirmed';       // เขียวอ่อน
                                            $displayText = 'ยืนยันวันตรวจแล้ว (' . $dateText . ')';
                                        }
                                        // 4) อยู่ระหว่างตรวจ / จัดทำผลตรวจ / ส่งอนุมัติ
                                        else if ($status === InspectionRequest::STATUS_INSPECTING || $status === InspectionRequest::STATUS_PASSED) {
                                            $trClass     = 'tr-inspecting';              // ฟ้าอ่อน
                                            $displayText = 'อยู่ระหว่างตรวจ / จัดทำผลตรวจ';
                                        }
                                        // 5) กระบวนการเสร็จสิ้นแล้ว (ผลตรวจสรุปแล้ว)
                                        else if (
                                            $status === InspectionRequest::STATUS_FAILED ||
                                            $status === InspectionRequest::STATUS_CONDITIONAL ||
                                            $status === InspectionRequest::STATUS_COMPLETED
                                        ) {
                                            $trClass     = 'tr-completed';               // เขียว (จบกระบวนการแล้ว)
                                            $displayText = 'ตรวจเสร็จสิ้นแล้ว';
                                        }
                                        // 6) ยกเลิกคำขอ
                                        else if ($status === InspectionRequest::STATUS_CANCELLED) {
                                            $trClass     = 'tr-cancelled';               // แดงอ่อน
                                            $displayText = 'ยกเลิกคำขอ';
                                        }
                                        // fallback กันเหนียว
                                        else {
                                            $trClass     = '';
                                            $displayText = $displayText ?: '-';
                                        }
                                ?>
                                    <tr style="font-size: 14px;" class="<?= $trClass ?>">
                                        <td data-order="0">
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- ปุ่มดูรายละเอียด -->
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalRequestDetail"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="right"                                                        
                                                        title="รายละเอียดคำขอ"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>)">
                                                    <i class="fas fa-search"></i>
                                                </button>

                                                <!-- ปุ่มพิมพ์ สร.1 -->
                                                <button class="btn btn-sm btn-outline-primary btn-print-form1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="right"
                                                        title="พิมพ์ สร.๑"
                                                        data-id="<?= h($req->id) ?>">
                                                  <i class="fas fa-print"></i>
                                                </button>

                                                <!-- ปุ่มไฟล์แนบ -->
                                                <?php
                                                $attCount = InspectionAttachment::count_by_request_id($req->id);
                                                if ($attCount > 0): ?>
                                                <button class="btn btn-sm btn-warning btn-attachments"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="right"                                                        title="ไฟล์แนบ (<?= $attCount ?>)"
                                                        data-id="<?= $req->id ?>">
                                                    <i class="fas fa-paperclip"></i>
                                                </button>
                                                <?php endif; ?> 

                                                <!-- แก้ไขคำขอ / ลบคำขอ (เฉพาะคนที่สร้าง) -->
                                                <?php if ((int)$session->user_id() === (int)$req->created_by) { ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-warning btn-edit-manual"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="right"                                                            title="แก้ไขคำขอ"
                                                            data-id="<?= h($req->id); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-danger btn-delete-request"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="right"                                                            title="ลบคำขอ"
                                                            data-id="<?= h($req->id); ?>">
                                                        <i class="bi bi-trash"></i> 
                                                    </button>
                                                <?php } ?>

                                                <!-- ปุ่มฟอร์มตรวจ -->
<?php
$isConfirmed = ((int)$req->is_confirm === 1);
$form_status = null;

if ($isConfirmed) {
    $form_status = InspectionFormStatus::find_by_request_id($req->id);
}
$isPending = ($req->status === 'pending');
$isInspecting = ($req->status === 'inspecting');
$isStarted = ($form_status !== null);
$isPass    = ($req->status === 'passed');
$isFailed = ($req->status === 'failed');
$isCondition = ($req->status === 'conditional');
$isComplete = ($req->is_complete == 1);
?>

<?php if (!$isConfirmed && $isPending): ?>

    <!-- ยังไม่ยืนยันวันตรวจ -->
    <button class="btn btn-secondary btn-sm" title="ยังไม่สามารถกรอกฟอร์มตรวจได้" disabled>
        <i class="fas fa-file-signature"></i>
    </button>

<?php elseif ($isConfirmed && $isInspecting): ?>

    <!-- ยืนยันแล้ว แต่ยังไม่เริ่มตรวจ -->
    <button
        type="button"
        class="btn btn-info btn-sm btn-start-inspect"
        data-id="<?= h($req->id) ?>"
        data-department="<?= h($req->department_id) ?>"
        title="เริ่มตรวจเรือ">
        <i class="fas fa-file-signature"></i>
    </button>

<?php elseif($isConfirmed && $isFailed): ?>

    <!-- ตรวจเสร็จแล้ว (ผ่าน/ไม่ผ่าน) → เปิด PDF -->
    <a
        href="generate_pdf.php?token=<?= h($form_status->document_token) ?>"
        target="_blank"
        class="btn btn-danger btn-sm"
        title="ผลการตรวจไม่ผ่าน">
        <i class="fas fa-exclamation-triangle"></i>
    </a>

<?php elseif($isConfirmed && $isPass): ?>

    <!-- ตรวจเสร็จแล้ว (ผ่าน/ไม่ผ่าน) → เปิด PDF -->
    <a
        href="generate_pdf.php?token=<?= h($form_status->document_token) ?>"
        target="_blank"
        class="btn btn-success btn-sm"
        title="ผลการตรวจผ่าน">
        <i class="fas fa-file-alt"></i>
    </a>
<?php elseif($isConfirmed && $isCondition): ?>

    <!-- ตรวจเสร็จแล้ว (ผ่าน/ไม่ผ่าน) → เปิด PDF -->
    <a
        href="generate_pdf.php?token=<?= h($form_status->document_token) ?>"
        target="_blank"
        class="btn btn-info btn-sm"
        title="ผลการตรวจผ่าน">
        <i class="fas fa-file-alt"></i>
    </a>    
<?php endif; ?>
<?php if($isConfirmed && $isComplete && $isFailed): ?>

    <!-- ตรวจเสร็จแล้ว (ผ่าน/ไม่ผ่าน) → เปิด PDF -->
    <a
        href="generate_pdf.php?token=<?= h($form_status->document_token) ?>"
        target="_blank"
        class="btn btn-danger btn-sm"
        title="หนังสือ สร.3">
        <i class="fas fa-file-alt"></i>
    </a>
<?php elseif($isConfirmed && $isComplete && $isPass || $isConfirmed && $isComplete && $isCondition ): ?>

    <!-- ตรวจเสร็จแล้ว (ผ่าน/ไม่ผ่าน) → เปิด PDF -->
    <a
        href="generate_pdf.php?token=<?= h($form_status->document_token) ?>"
        target="_blank"
        class="btn btn-success btn-sm"
        title="หนังสือ สร.3">
        <i class="fas fa-file-alt"></i>
    </a>

<?php endif; ?>

                                            </div>
                                        </td>

                                        <td><?= h($req->ship_code) ?></td>
                                        <td><?= h($req->vessel_name) ?></td>
                                        <td><?= h($req->port_name) ?></td>
                                        <td><?= thai_date($req->inspect_date_start) . " ถึงวันที่ " . thai_date($req->inspect_date_end) ?></td>
                                        
                                        <td><?= $displayText ?></td>

                                        <td class="text-center">
                                            <?php
                                                $icons = [];

                                                // ① ตรวจแบบ EU หรือทั่วไป
                                                if ($req->inspection_form_type == 2) {
                                                    $icons[] = '<i class="fas fa-globe-europe eu" title="ตรวจเพื่อ EU Export"></i>';
                                                } else {
                                                    $icons[] = '<i class="fas fa-ship normal" title="ตรวจทั่วไป (แบบที่ 1)"></i>';
                                                }

                                                // ② ใครเป็นคนยื่น
                                                if ($req->is_manual_case == 1) {
                                                    $icons[] = '<i class="fas fa-user-tie officer" title="เจ้าหน้าที่สร้างคำขอ"></i>';
                                                } else {
                                                    $icons[] = '<i class="fas fa-user user" title="ผู้ประกอบการยื่นเอง"></i>';
                                                }

                                                // ③ ห้องเย็นหรือไม่
                                                if ($req->cold_room_flag == 1) {
                                                    $icons[] = '<i class="fas fa-snowflake cold" title="เรือห้องเย็น"></i>';
                                                } else {
                                                    $icons[] = '<i class="fas fa-thermometer-half warm" title="เรือทั่วไป (ไม่มีห้องเย็น)"></i>';
                                                }

                                                echo '<span class="req-type-pill">' . implode(' ', $icons) . '</span>';
                                            ?>
                                        </td>

                                        <td><?= thai_date($req->created_at) ?></td>
                                        <td><?= thai_date($req->confirmed_inspect_date) ?></td>
                                        <td>
                                            <?php
                                            switch ($req->status) {
                                                case InspectionRequest::STATUS_PENDING:
                                                    echo '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_INSPECTING:
                                                    echo '<span class="badge bg-primary">อยู่ระหว่างตรวจ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_PASSED:
                                                    echo '<span class="badge bg-success">ผ่านการตรวจ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_FAILED:
                                                    echo '<span class="badge bg-danger">ไม่ผ่านการตรวจ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_CONDITIONAL:
                                                    echo '<span class="badge bg-info text-dark">ผ่านแบบมีเงื่อนไข</span>';
                                                    break;
                                                case InspectionRequest::STATUS_COMPLETED:
                                                    echo '<a href="certificate_preview.php?id=' . h($req->id) . '" target="_blank" class="badge bg-success text-decoration-none">
                                                                <i class="fas fa-file-image"></i> อนุมัติ
                                                          </a>';
                                                    break;
                                                case InspectionRequest::STATUS_CANCELLED:
                                                    echo '<span class="badge bg-secondary">ยกเลิก</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-dark">ไม่ทราบ</span>';
                                            }
                                            ?>
                                            <button class="btn btn-link p-0 text-muted btn-log"
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
                                                    title="ดูประวัติ"
                                                    data-request-id="<?= h($req->id) ?>"
                                                    data-vessel="<?= h($req->vessel_name); ?>">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>

                            </table>
                        </div>
                    </div>


                    </div>
                    <?php include(__DIR__ . '/modal/modalRequestDetail.php'); ?>
                    <?php include(__DIR__ . '/modal/modal_request_manual_case.php'); ?>  
                    <?php include(__DIR__ . '/modal/modal_attachment.php'); ?>  
                    <?php include(__DIR__ . '/modal/modal_edit_manual_case.php'); ?>  
                    <?php include("modal/logmodal.php"); ?>
                    <?php include("modal/fvs01modal.php"); ?>                    
                               
</div><!-- <div class="container-fluid"> -->

  
<?php include("../../private/shared/footerofficer.php"); ?>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../js/fvscis.js"></script>    
    <script>
        // ======================= CONSTANTS ร่วม =======================
        const ATTACH_TYPES = [
        { value: 'ทะเบียนเรือ',         label: 'ทะเบียนเรือ' },
    { value: 'ใบอนุญาตทำการประมง', label: 'ใบอนุญาตทำการประมง' },
    { value: 'ใบอนุญาตใช้เรือ', label: 'ใบอนุญาตใช้เรือ' },
    { value: 'บัตรประชาชนผู้ยื่น',         label: 'บัตรประชาชนผู้ยื่น' },
    { value: 'หนังสือมอบอำนาจ',     label: 'หนังสือมอบอำนาจ' },
    { value: 'สำเนาบัตรประชาชนผู้มอบอำนาจ',         label: 'สำเนาบัตรประชาชนผู้มอบอำนาจ' },
    { value: 'บัตรประจำตัวตัวแทนนิติบุคคล', label: 'บัตรประจำตัวตัวแทนนิติบุคคล' },
    { value: 'ใบรับรอง สร.3 ฉบับเก่า',        label: 'ใบรับรอง สร.3 ฉบับเก่า' },
        ];

        // ใช้ DataTransfer แยกกันระหว่าง สร้าง และ แก้ไข
        let dtCreate = new DataTransfer();
        let dtEdit   = new DataTransfer();
        </script>
    
           <script>
// ======================== 1) โหลดรายละเอียดคำขอ (ดูรายละเอียด + confirm วันที่) ========================
function loadRequestDetail(id) {
  $.ajax({
    url: 'ajax/get_request_detail.php',
    method: 'GET',
    data: { id },
    dataType: 'json',
    success: function (data) {
      if (!data.success) {
        Swal.fire('ผิดพลาด', data.message, 'error')
        return
      }

      const req = data.request
      const statusMap = {
        pending: 'รอดำเนินการ',
        cancelled: 'ยกเลิก',
        inspecting: 'อยู่ระหว่างตรวจ',
        passed: 'ผ่านการตรวจ',
        failed: 'ไม่ผ่านการตรวจ',
        conditional: 'ผ่านแบบมีเงื่อนไข',
        completed: 'อนุมัติ',
      }

      let html = `
        <p>
          <strong>ชื่อเรือ:</strong> ${req.vessel_name || '-'}
          <strong>   ทะเบียนเรือ:</strong> ${req.ship_code || '-'}
        </p>
        <p>
          <strong>ชื่อเจ้าของเรือ:</strong> ${req.owner_name || '-'}
          <strong>   หมายเลขติดต่อ:</strong> ${req.contact_phone || '-'}
        </p>
        <p>
          <strong>ช่วงวันที่ขอตรวจ:</strong> ${req.inspect_date_start} ถึง ${req.inspect_date_end}
        </p>
        <p>
          <strong>ชื่อท่าเทียบเรือประมงที่ใช้สำหรับการตรวจ:</strong> ${req.port_name || '-'}
          <strong>   ทะเบียนท่าเทียบเรือประมง:</strong> ${req.port_license_no || '-'}
        </p>
        <p>
          <strong>ตำบล:</strong> ${req.port_tambon || '-'}
          <strong>  อำเภอ:</strong> ${req.port_amphur || '-'}
          <strong>  จังหวัด:</strong> ${req.port_province || '-'}
        </p>
        <p>
          <strong>สถานะ:</strong> ${statusMap[req.status] || 'ไม่ทราบ'}
      `

      if (req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00') {
        const displayDate = new Date(req.confirmed_inspect_date).toLocaleDateString('th-TH', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        })
        html += `<br><span class="text-success"><i class="fas fa-calendar-check"></i> มีการนัดหมายวันที่ ${displayDate} แล้ว</span>`
        $('#btnConfirmDate').prop('disabled', false);
      } else {
        html += `<br><span class="text-danger"><i class="fas fa-exclamation-circle"></i> ยังไม่มีการนัดหมายวันตรวจ</span>`
        $('#btnConfirmDate').prop('disabled', false);
      }

      html += `</p>`

      if (req.is_confirm == 1 && req.confirmed_inspect_date) {
            html += `<p><span class="text-success">
              <i class="fas fa-calendar-check"></i>
              ยืนยันวันนัดตรวจแล้ว (${req.confirmed_inspect_date})
            </span></p>`;
          } else {
            html += `<p><span class="text-danger">
              <i class="fas fa-calendar-times"></i>
              ยังไม่มีการยืนยันวันนัดตรวจ
            </span></p>`;
          }

      if (req.is_start_inspect == 1) {
          // เริ่มตรวจแล้ว = ล็อกทุกคน
          $('#btnConfirmDate').prop('disabled', true);
        } else {
          // ยังไม่เริ่มตรวจ = ให้ดูตามสิทธิ์
          $('#btnConfirmDate').prop('disabled', false);
        }

      $('#modalRequestBody').html(html)

      // set ค่าในฟอร์มยืนยันวันตรวจ
      setTimeout(() => {
        $('#confirm_request_id').val(req.id)
        $('input[name="confirmed_date"]').attr('min', req.inspect_date_start)
        $('input[name="confirmed_date"]').val(
          req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00'
            ? req.confirmed_inspect_date
            : ''
        )
        $('input[name="original_confirmed_date"]').val(
          req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00'
            ? req.confirmed_inspect_date
            : ''
        )
      }, 100)
    },
    error: function () {
      Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error')
    },
  })
}
</script>

<script>
// ======================== 2) เปิด modal แก้ไขคำขอ manual case ========================
$(document).on('click', '.btn-edit-manual', function () {
  const id = $(this).data('id')
  if (!id) return

  const $form = $('#formEditManualCase')
  if ($form.length && $form[0]) {
    $form[0].reset()
  }
  $('#manualSelectedFilesEdit').empty()

  // เคลียร์ select + flag + checkbox
  $('#edit-port_tambon_id').val('')
  $('#edit-port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>')
  $('#edit-eu-cert-checkbox').prop('checked', false)
  $('#edit-cold-room-checkbox').prop('checked', false)
  $('#edit-inspection-form-type').val('1')
  $('#edit-cold-room-flag').val('0')

  $('#edit-request-id').val(id)

  const today = new Date().toISOString().split('T')[0]
  $('#edit-confirmed_inspect_date').attr('min', today).val('')

  $('#modalEditManualCase').modal('show')

  $.ajax({
    url: 'ajax/get_manual_request_detail_for_update.php',
    type: 'GET',
    dataType: 'json',
    data: { id },
    success: function (res) {
      if (!res || !res.success || !res.request) {
        Swal.fire('ผิดพลาด', res?.message || 'ไม่พบข้อมูลคำขอ', 'error')
        return
      }

      const r = res.request || {}
      $('#edit-contact-phone').val(r.contact_phone || '')

      // เซ็ตค่าเดิมให้ฟิลด์ต่าง ๆ
      $('#edit-ship-code').val(r.ship_code || '');
      $('#edit-vessel-name').val(r.vessel_name || '');
      $('#edit-owner-name').val(r.owner_name || '');
      $('#edit-vessel-mark').val(r.vessel_mark || '');
      $('#edit-license-number').val(r.license_number || '');
      $('#edit-gear-type').val(r.gear_type || '');

      // กำหนดสิทธิ์แก้ไขตาม license_status
      const licenseStatus = r.license_status || 'none'; // กันกรณีไม่มีค่า

      if (licenseStatus === 'normal') {
        // มี license ปกติ → แก้ไขไม่ได้ทั้งหมด
        $('#edit-ship-code')
          .prop('readonly', true);
        $('#edit-vessel-name')
          .prop('readonly', true);
        $('#edit-owner-name')
          .prop('readonly', true);
        $('#edit-vessel-mark')
          .prop('readonly', true);
        $('#edit-license-number')
          .prop('readonly', true);
        $('#edit-gear-type')
          .prop('readonly', true);

        // ปิดปุ่มค้นหา (ไม่จำเป็นให้แก้แล้ว)
        $('#btnLookupShipEdit').prop('disabled', true);

      } else if (licenseStatus === 'none') {
        // ยังไม่มี license ใน eLicense
        // ทะเบียนเรือ / mark / license / gear → แก้ไม่ได้
        $('#edit-ship-code')
          .prop('readonly', true);
        $('#edit-vessel-mark')
          .prop('readonly', true);
        $('#edit-license-number')
          .prop('readonly', true);
        $('#edit-gear-type')
          .prop('readonly', true);

        // ชื่อเรือ / ชื่อเจ้าของ → แก้ได้
        $('#edit-vessel-name')
          .prop('readonly', false);
        $('#edit-owner-name')
          .prop('readonly', false);

        // ปุ่มค้นหา → ใช้งานได้ เผื่ออนาคตมี eLicense
        $('#btnLookupShipEdit').prop('disabled', false);

      } else {
        // กรณีอื่น ๆ (กันเหนียว) → ทุกอย่างแก้ได้ + ค้นหาได้
        $('#edit-ship-code')
          .prop('readonly', false);
        $('#edit-vessel-name')
          .prop('readonly', false);
        $('#edit-owner-name')
          .prop('readonly', false);
        $('#edit-vessel-mark')
          .prop('readonly', false);
        $('#edit-license-number')
          .prop('readonly', false);
        $('#edit-gear-type')
          .prop('readonly', false);

        $('#btnLookupShipEdit').prop('disabled', false);
      }


      

      const minDate =
        r.inspect_date_start && r.inspect_date_start !== '0000-00-00'
          ? r.inspect_date_start
          : today
      $('#edit-confirmed_inspect_date').attr('min', minDate)

      if (r.confirmed_inspect_date && r.confirmed_inspect_date !== '0000-00-00') {
        $('#edit-confirmed_inspect_date').val(r.confirmed_inspect_date)
      } else {
        $('#edit-confirmed_inspect_date').val('')
      }

      const formType = r.inspection_form_type || '1'
      $('#edit-inspection-form-type').val(formType)
      $('#edit-eu-cert-checkbox').prop('checked', formType == '2')

      const coldFlag = r.cold_room_flag || '0'
      $('#edit-cold-room-flag').val(coldFlag)
      $('#edit-cold-room-checkbox').prop('checked', coldFlag == '1')

      if (r.port_tambon_id) {
        $('#edit-port_tambon_id').val(r.port_tambon_id)
        $.ajax({
          url: 'ajax/get_ports_by_tambon.php',
          type: 'GET',
          data: { tambon_id: r.port_tambon_id },
          dataType: 'html',
          success: function (html) {
            $('#edit-port_license_no').html(html)
            if (r.port_license_no) {
              $('#edit-port_license_no').val(r.port_license_no)
            }
          },
          error: function () {
            $('#edit-port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>')
          },
        })
      }
      const files = Array.isArray(r.attachments) ? r.attachments : []
        renderExistingManualAttachments(files)
    },
    error: function () {
      Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error')
    },
  })
})

function renderEditAttachments(files) {
  const $wrap = $('#editFilePreview')
  $wrap.empty()

  if (!files.length) {
    $wrap.append('<div class="text-muted small">ยังไม่มีไฟล์แนบ</div>')
    return
  }

  files.forEach(function (a) {
    const url = a.url_enc || a.url || ''
    const isImg = !!a.is_image

    if (!url) return

    const thumb = isImg
      ? `<img src="${url}" alt="${a.name || ''}"
               class="img-thumbnail w-100"
               style="height:120px; object-fit:cover;">`
      : `<div class="border rounded p-2 text-center">
           <i class="bi bi-file-earmark"></i>
         </div>`

    $wrap.append(`
      <div class="col-6 col-md-3 mb-2" data-attach-id="${a.id}">
        <div class="file-card shadow-sm p-1">
          <a href="${url}" target="_blank" title="เปิดดูไฟล์">
            ${thumb}
          </a>
          <div class="small text-truncate mt-1" title="${a.name || ''}">
            ${a.name || ''}
          </div>
          <div class="text-muted small">
            ${a.attachment_type ? a.attachment_type : ''}
          </div>
        </div>
      </div>
    `)
  })
}

</script>

<script>
// ======================== 3) Tooltip + ยืนยันวันนัดตรวจ ========================
$(function () {
  // init tooltip จาก title
  function initTooltips(scope = document) {
    scope.querySelectorAll('[title]').forEach((el) => {
      bootstrap.Tooltip.getOrCreateInstance(el, {
        trigger: 'hover',
        container: 'body',
      })
    })
  }
  initTooltips()

  // ถ้ามี DataTables ให้เปลี่ยน #yourTableId เป็น id จริง
  $('#yourTableId').on('draw.dt', function () {
    initTooltips(this)
  })

  // hide tooltip เมื่อคลิก element นั้น
  document.addEventListener('click', (e) => {
    const el = e.target.closest('[title]')
    if (!el) return
    const t = bootstrap.Tooltip.getInstance(el)
    if (t) t.hide()
  })

  // hide tooltip เมื่อมี modal เปิด
  document.addEventListener('show.bs.modal', () => {
    document.querySelectorAll('[title]').forEach((el) => {
      const t = bootstrap.Tooltip.getInstance(el)
      if (t) t.hide()
    })
  })

  // ===== ยืนยันวันนัดตรวจ =====
  const today = new Date().toISOString().split('T')[0]
  $('#confirmed_date').attr('min', today)

  $('#btnConfirmDate').on('click', function () {
    const $form = $('#confirmInspectionForm')
    const $date = $('#confirmed_date')
    const val = $date.val()

    if (!val) {
      $date[0].reportValidity()
      $date.addClass('is-invalid')
      return
    } else {
      $date.removeClass('is-invalid')
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(val)) {
      Swal.fire({
        icon: 'warning',
        title: 'รูปแบบวันที่ไม่ถูกต้อง',
        text: 'กรุณาเลือกวันที่จากปฏิทินอีกครั้ง',
      })
      $date.focus()
      return
    }

    if (val < today) {
      Swal.fire({
        icon: 'warning',
        title: 'เลือกวันย้อนหลังไม่ได้',
        text: 'กรุณาเลือกตั้งแต่วันนี้เป็นต้นไป',
      })
      $date.focus()
      return
    }

    const formData = $form.serialize()
    const $btn = $('#btnConfirmDate').prop('disabled', true)

    $.ajax({
      url: 'ajax/confirm_inspect_date.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: response.message,
            timer: 1800,
            showConfirmButton: false,
          }).then(() => location.reload())
        } else {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: response.message })
        }
      },
      error: function () {
        Swal.fire({
          icon: 'error',
          title: 'การเชื่อมต่อล้มเหลว',
          text: 'ไม่สามารถติดต่อ server ได้',
        })
      },
      complete: function () {
        $btn.prop('disabled', false)
      },
    })
  })
})
</script>




<script>
// ======================== 5) เปลี่ยนตำบล -> โหลดท่าเรือ (ฟอร์ม manual case) ========================
$(function () {
  $('#port_tambon_id').on('change', function () {
    const tambonId = $(this).val()
    $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>')

    if (tambonId) {
      $.ajax({
        url: 'ajax/get_ports_by_tambon.php',
        type: 'GET',
        data: { tambon_id: tambonId },
        dataType: 'html',
        success: function (html) {
          $('#port_license_no').html(html)
        },
      })
    }
  })
})
</script>

<script>
// ======================== 6) sync checkbox -> hidden + submit ฟอร์มสร้าง manual case ========================
;(function () {
  const euCbx = document.getElementById('eu_cert_checkbox')
  const euType = document.getElementById('inspection_form_type')
  const coldCbx = document.getElementById('cold_room_checkbox')
  const coldFlg = document.getElementById('cold_room_flag')
  const d = document.getElementById('confirmed_inspect_date')

  if (euCbx && euType) {
    euCbx.addEventListener('change', () => {
      euType.value = euCbx.checked ? '2' : '1'
    })
  }
  if (coldCbx && coldFlg) {
    coldCbx.addEventListener('change', () => {
      coldFlg.value = coldCbx.checked ? '1' : '0'
    })
  }
  if (d) {
    d.min = new Date().toISOString().split('T')[0]
  }

  $(document).on('submit', '#formManualCase', function (e) {
  e.preventDefault();
  const $btn = $(this).find('button[type="submit"]').prop('disabled', true);
  const fd   = new FormData(this);

  // (ไฟล์จริงที่ส่งไปจะมาจาก input.files = dtCreate.files อยู่แล้ว)

  $.ajax({
    url: 'ajax/request_inspection.php',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    dataType: 'json',
  })
    .done(res => {
      if (res.success) {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: res.message || 'บันทึกคำขอเรียบร้อย',
          timer: 1600,
          showConfirmButton: false,
        }).then(() => location.reload());
      } else {
        Swal.fire('ไม่สำเร็จ', res.message || 'บันทึกไม่สำเร็จ', 'error');
      }
    })
    .fail(xhr => {
      Swal.fire('ผิดพลาด', 'ติดต่อเซิร์ฟเวอร์ไม่ได้', 'error');
      console.error(xhr.responseText);
    })
    .always(() => $btn.prop('disabled', false));
});
})()
</script>

<script>
// ======================== 7) modal ดูไฟล์แนบ (photo gallery) ========================
;(function () {
  $(document).on('click', '.btn-attachments', function () {
    const reqId = $(this).data('id')
    if (!reqId) return

    $('#photoModalReqId').text('')
    $('#photoGrid').empty()
    $('#photoEmpty').addClass('d-none').text('กำลังโหลด...')
    $('#photoPreviewWrap').addClass('d-none')
    $('#photoPreviewImg').attr('src', '')

    $('#modalPhotoAttachments').modal('show')

    const pDetail = $.ajax({
      url: 'ajax/get_request_detail.php',
      method: 'GET',
      data: { id: reqId },
      dataType: 'json',
    })

    const pAttach = $.ajax({
      url: 'ajax/get_request_attachments.php',
      method: 'GET',
      data: { id: reqId },
      dataType: 'json',
    })

    $.when(pDetail, pAttach)
      .done(function (detailRes, attachRes) {
        const detail = detailRes[0]
        const attach = attachRes[0]

        let vesselName = ''
        let shipCode = ''
        if (detail && detail.success && detail.request) {
          vesselName = detail.request.vessel_name || ''
          shipCode = detail.request.ship_code || ''
        }

        let photos = []
        if (attach && attach.success && Array.isArray(attach.attachments)) {
          photos = attach.attachments
            .filter((a) => a.is_image)
            .map((p) => ({
              ...p,
              _url: p.url_enc ? p.url_enc : encodeURI(p.url || ''),
            }))
        } else {
          $('#photoEmpty').removeClass('d-none').text('ไม่สามารถโหลดไฟล์แนบได้')
        }

        renderPhotoGrid(photos)

        const parts = []
        if (vesselName) parts.push(`ชื่อเรือ ${vesselName}`)
        if (shipCode) parts.push(`ทะเบียน ${shipCode}`)
        const leftText = parts.length ? parts.join(' • ') : `คำขอ #${reqId}`
        const rightText = `— ${photos.length} รูป`
        $('#photoModalReqId').text(`${leftText} ${rightText}`)
      })
      .fail(function () {
        $('#photoEmpty').removeClass('d-none').text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้')
      })
  })

  function renderPhotoGrid(photos) {
    const $grid = $('#photoGrid')
    const $empty = $('#photoEmpty')
    const $pvW = $('#photoPreviewWrap')
    const $pv = $('#photoPreviewImg')

    const imgs = photos.filter((p) => p.is_image)
    if (!imgs.length) {
      $empty.removeClass('d-none').text('ยังไม่มีรูปภาพแนบ')
      return
    }
    $empty.addClass('d-none')

    const valid = imgs.filter((p) => p.exists !== false)

    let html = ''
    valid.forEach((p) => {
      const u = p.url_enc || encodeURI(p.url)
      html += `
        <div class="border rounded p-1 shadow-sm" style="width:140px;">
          <a href="${u}" class="photo-thumb" data-url="${u}">
            <img src="${u}" alt="${p.name}" class="img-thumbnail w-100" style="height:120px; object-fit:cover;">
          </a>
          <div class="small text-truncate mt-1" title="${p.name}">${p.name}</div>
          <div class="text-muted small">
                    ${p.attachment_type ? p.attachment_type : ''}
                </div>
        </div>`
    })
    $grid.html(html)

    if (valid.length) {
      $pv.attr('src', valid[0].url_enc || encodeURI(valid[0].url))
      $pvW.removeClass('d-none')
    }

    $grid.off('click', 'a.photo-thumb').on('click', 'a.photo-thumb', function (e) {
      e.preventDefault()
      $pv.attr('src', $(this).data('url'))
      $pvW.removeClass('d-none')
    })
  }
})()
</script>

<script>
// ======================= 2) E-LICENSE LOOKUP (สร้างคำขอ) =======================
;(function () {
  function setBusyManual(isBusy) {
    $('#btnLookupShipManual').prop('disabled', isBusy);
    $('#btnManualText').toggleClass('d-none', isBusy);
    $('#btnManualSpin').toggleClass('d-none', !isBusy);
  }

  function applyElicenseFoundState(v) {
    const $licenseStatus = $('#manual-license-status');

    // เซ็ตค่าจาก eLicense
    $('#manual-vessel-name').val(v.vessel_name || '');
    $('#manual-owner-name').val(v.display_name || '');
    $('#manual-vessel-mark').val(v.fishing_mark || '');
    $('#manual-license-number').val(v.license_no || '');
    $('#manual-gear-type').val(v.geartype || '');

    // โชว์เฉพาะฟิลด์ที่เป็น eLicense-only
    $('.elicense-only').removeClass('d-none');

    // ฟิลด์ที่มาจากกลาง → ไม่ให้แก้
    $('#manual-vessel-name').prop('readonly', true);
    $('#manual-owner-name').prop('readonly', true);
    $('#manual-vessel-mark').prop('readonly', true);
    $('#manual-license-number').prop('readonly', true);
    $('#manual-gear-type').prop('readonly', true);

    // ระบุสถานะมีใบอนุญาต
    $licenseStatus.val('normal');

    Swal.fire({
      icon: 'success',
      title: 'ดึงข้อมูลจาก eLicense สำเร็จ',
      timer: 900,
      showConfirmButton: false,
    });
  }

  function applyElicenseNotFoundState() {
    const $licenseStatus = $('#manual-license-status');

    // เคลียร์ field ที่ควรต้องมาจากฐานกลางเท่านั้น
    $('#manual-vessel-mark').val('');
    $('#manual-license-number').val('');
    $('#manual-gear-type').val('');

    // ซ่อนฟิลด์ eLicense-only (เช่น mark / license / gear)
    $('.elicense-only').addClass('d-none');

    // ชื่อเรือ / ชื่อเจ้าของ → ให้แก้ได้ (กรณีพิมพ์เอง)
    $('#manual-vessel-name').prop('readonly', false);
    $('#manual-owner-name').prop('readonly', false);

    // สถานะไม่มีใบอนุญาต
    $licenseStatus.val('none');

    Swal.fire({
      icon: 'warning',
      title: 'ไม่พบข้อมูลใน eLicense',
      text: 'ระบบจะถือว่าเรือยังไม่มีใบอนุญาตทำการประมง',
    });
  }

  function lookupShipManual() {
    const shipCode = ($('#manual-ship-code').val() || '').trim();
    if (!shipCode) {
      Swal.fire({ icon: 'warning', title: 'กรุณากรอกทะเบียนเรือ' });
      return;
    }

    setBusyManual(true);

    $.ajax({
      url: 'ajax/get_elicense_by_ship_code.php',
      type: 'POST',
      dataType: 'json',
      data: { ship_code: shipCode },
    })
      .done(function (res) {
        if (res && res.success && res.data) {
          // ─── พบใน eLicense ──────────────────────
          applyElicenseFoundState(res.data);
        } else {
          // ─── ไม่พบใน eLicense ────────────────────
          applyElicenseNotFoundState();
        }
      })
      .fail(function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อ eLicense ไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      })
      .always(function () {
        setBusyManual(false);
      });
  }

  // คลิกปุ่มค้นหา
  $(document).on('click', '#btnLookupShipManual', lookupShipManual);

  // กด Enter ที่ช่องทะเบียนเรือ
  $(document).on('keydown', '#manual-ship-code', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      lookupShipManual();
    }
  });
})();
</script>

<script>
// ======================= 1) OPEN MODAL "สร้างคำขอ" =======================
$(document).ready(function () {
  $('#btnOpenManualCase').on('click', function () {
    const $form = $('#formManualCase')[0];
    if ($form) $form.reset();

    // clear DataTransfer + preview
    dtCreate = new DataTransfer();
    $('#attachments').val('');
    $('#filePreview').empty();

    // reset selects/checkbox/hidden
    $('#port_tambon_id').val('');
    $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
    $('#inspection_form_type').val('1');
    $('#cold_room_flag').val('0');
    $('#eu_cert_checkbox').prop('checked', false);
    $('#cold_room_checkbox').prop('checked', false);
  });

  $(document).on('click', '.btn-log', function () {
            const requestId = $(this).data('request-id');
             const vessel = $(this).data('vessel');
            $.ajax({
                url: 'ajax/get_request_logs.php',
                method: 'GET',
                dataType: 'json',
                data: { id: requestId },
                success: function (resp) {
                    if (!resp.success) {
                        if (window.Swal) {
                            Swal.fire('ผิดพลาด', resp.message || 'ไม่สามารถโหลดประวัติได้', 'error');
                        } else {
                            alert(resp.message || 'ไม่สามารถโหลดประวัติได้');
                        }
                        return;
                    }

                    const logs = resp.logs || [];
                    let html = '';

                    if (logs.length === 0) {
                        html = `<tr><td colspan="4" class="text-center text-muted">ยังไม่มีประวัติการดำเนินการ</td></tr>`;
                    } else {
                        logs.forEach(function (log) {
                            html += `
                                <tr>
                                    <td>${log.time}</td>
                                    <td>${log.action}</td>
                                    <td>${log.actor}</td>
                                    <td>${log.note || '-'}</td>
                                </tr>`;
                        });
                    }
                    $('#modalVesselName').text(vessel); 
                    $('#logModalBody').html(html);
                    $('#logModal').modal('show');
                },
                error: function () {
                    if (window.Swal) {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    } else {
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    }
                }
            });
        });
});
</script>



 <script>
// ===================== Edit Manual Modal: ไฟล์เดิม + ไฟล์ใหม่ =====================
;(function () {
  function bytesFmt(n) {
    if (n < 1024) return n + ' B'
    if (n < 1048576) return (n / 1024).toFixed(1) + ' KB'
    return (n / 1048576).toFixed(1) + ' MB'
  }

  function isImgFile(f) {
    return (
      /^image\//i.test(f.type || '') ||
      /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(f.name || '')
    )
  }

  // ---------- 1) แสดงไฟล์เดิมของคำขอ (ใช้ attachments จาก PHP) ----------
  window.renderExistingManualAttachments = function (attachments) {
    const $modal = $('#modalEditManualCase')
    const $wrap = $modal.find('#manualExistingFiles').empty()

    if (!attachments || !attachments.length) {
      $wrap.append('<div class="text-muted small">ยังไม่มีไฟล์แนบเดิม</div>')
      return
    }

    attachments.forEach((a) => {
      const url = a.url_enc || a.url
      if (!url) return

      const isImg = !!a.is_image
      const thumbInner = isImg
        ? `<img src="${url}" alt="${a.name || ''}">`
        : `<div class="icon-pdf">PDF</div>`

      $wrap.append(`
        <div class="col-6 col-md-3" data-attach-id="${a.id}">
          <div class="file-card shadow-sm">

            <div class="thumb-wrap">
              <button type="button"
                      class="btn btn-sm btn-danger btn-del-existing-manual"
                      data-id="${a.id}" title="ลบไฟล์เดิม">
                <i class="bi bi-x-lg"></i>
              </button>

              <a href="${url}" target="_blank" title="เปิดดูไฟล์เดิม">
                ${thumbInner}
              </a>
            </div>

            <div class="file-name mt-2 text-truncate" title="${a.name || ''}">
              ${a.name || ''}
            </div>

            <div class="text-muted small">
              ${a.attachment_type ? a.attachment_type : ''}
            </div>
          </div>
        </div>
      `)
    })
  }

  // ---------- 2) sync input[type=file] สำหรับไฟล์ใหม่ ----------
  function syncInputFilesManual() {
    const $modal = $('#modalEditManualCase')
    const $input = $modal.find('#manualAttachmentsEdit')
    const selected = $input.data('selected') || []
    const dt = new DataTransfer()
    selected.forEach((f) => dt.items.add(f))
    if ($input[0]) $input[0].files = dt.files
  }

  // ---------- 3) แสดง preview ไฟล์ใหม่ ----------
  function renderSelectedPreviewManual() {
    const $modal = $('#modalEditManualCase')
    const $input = $modal.find('#manualAttachmentsEdit')
    const $list = $modal.find('#manualSelectedFilesEdit')
    const selected = $input.data('selected') || []

    if (!selected.length) {
      $list.empty()
      return
    }

    let html = ''
    selected.forEach((f, idx) => {
      const isImg = isImgFile(f)
      const src = isImg ? URL.createObjectURL(f) : ''

      html += `
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 shadow-sm file-card">

            <div class="thumb-wrap">
              <button type="button"
                      class="btn btn-sm btn-danger btn-remove-new-manual"
                      data-idx="${idx}"
                      title="เอาไฟล์นี้ออก">
                <i class="bi bi-x-lg"></i>
              </button>

              ${
                isImg
                  ? `<img src="${src}" alt="${f.name}">`
                  : `<div class="icon-pdf">PDF</div>`
              }
            </div>

            <div class="file-name mt-2 text-truncate" title="${f.name}">
              ${f.name}
            </div>
            <div class="text-muted small">${bytesFmt(f.size || 0)}</div>

            <select class="form-select form-select-sm mt-1"
                    name="attachment_type_new[]">
              <option value="ทะเบียนเรือ">ทะเบียนเรือ</option>
              <option value="ใบอนุญาตทำการประมง">ใบอนุญาตทำการประมง</option>
              <option value="ใบอนุญาตใช้เรือ">ใบอนุญาตใช้เรือ</option>
              <option value="บัตรประชาชนผู้ยื่น">บัตรประชาชนผู้ยื่น</option>
              <option value="หนังสือมอบอำนาจ">หนังสือมอบอำนาจ</option>
              <option value="สำเนาบัตรประชาชนผู้มอบอำนาจ">สำเนาบัตรประชาชนผู้มอบอำนาจ</option>
              <option value="บัตรประจำตัวตัวแทนนิติบุคคล">บัตรประจำตัวตัวแทนนิติบุคคล</option>
              <option value="ใบรับรอง สร.3 ฉบับเก่า">ใบรับรอง สร.3 ฉบับเก่า</option>
            </select>

          </div>
        </div>
      `
    })

    $list.html(html)
  }

  // ---------- 4) event: เลือกไฟล์ใหม่ ----------
  $('#modalEditManualCase')
    .off('change.manualAttachEdit')
    .on('change.manualAttachEdit', '#manualAttachmentsEdit', function () {
      const $modal = $('#modalEditManualCase')
      const $input = $modal.find('#manualAttachmentsEdit')

      let selected = $input.data('selected') || []
      const files = Array.from(this.files || [])

      files.forEach((f) => {
        if (!selected.some((x) => x.name === f.name && x.size === f.size)) {
          selected.push(f)
        }
      })

      $input.data('selected', selected)
      syncInputFilesManual()
      renderSelectedPreviewManual()
    })
    // ---------- 5) event: เอาไฟล์ใหม่ออก ----------
    .off('click.removeNewManual')
    .on('click.removeNewManual', '.btn-remove-new-manual', function () {
      const $modal = $('#modalEditManualCase')
      const $input = $modal.find('#manualAttachmentsEdit')
      let selected = $input.data('selected') || []
      const idx = +$(this).data('idx')

      if (idx >= 0) {
        selected.splice(idx, 1)
      }
      $input.data('selected', selected)
      syncInputFilesManual()
      renderSelectedPreviewManual()
    })

  // ---------- 6) ลบไฟล์เดิมของ manual request ----------
  $(document).on('click', '.btn-del-existing-manual', function () {
    const attachId = $(this).data('id')
    if (!attachId) return

    const $btn = $(this)

    Swal.fire({
      title: 'ยืนยันการลบไฟล์?',
      text: 'ไฟล์นี้จะถูกลบออกจากระบบถาวร',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'ลบไฟล์',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
    }).then((result) => {
      if (!result.isConfirmed) return

      $btn.prop('disabled', true)

      $.post(
        'ajax/manual_request_attachment_delete.php',
        { attachment_id: attachId },
        function (res) {
          if (res && res.success) {
            $(`[data-attach-id="${attachId}"]`).remove()

            Swal.fire({
              icon: 'success',
              title: 'ลบไฟล์เรียบร้อย',
              timer: 900,
              showConfirmButton: false,
            })
          } else {
            Swal.fire({
              icon: 'error',
              title: 'ลบไม่สำเร็จ',
              text: res?.message || 'เกิดข้อผิดพลาด',
            })
          }
        },
        'json'
      )
        .fail(() => {
          Swal.fire({
            icon: 'error',
            title: 'เชื่อมต่อไม่ได้',
            text: 'โปรดลองใหม่อีกครั้ง',
          })
        })
        .always(() => {
          $btn.prop('disabled', false)
        })
    })
  })

  // ---------- 7) รีเซ็ตเมื่อปิด modal ----------
  $('#modalEditManualCase').on('hidden.bs.modal', function () {
    const $modal = $('#modalEditManualCase')
    const $input = $modal.find('#manualAttachmentsEdit')
    $input.val('').removeData('selected')
    $modal.find('#manualSelectedFilesEdit').empty()
    $modal.find('#manualExistingFiles').empty()
  })
})()
</script>

<script>
// =================== บันทึกแก้ไขคำขอ Manual (Update) ===================
;(function () {

  $(document).on('submit', '#formEditManualCase', function (e) {
    e.preventDefault();

    const $form = $(this);
    const formEl = this;

    // สร้าง FormData จากฟอร์มทั้งชุด (รวม request[...] + attachments[])
    const fd = new FormData(formEl);

    const $btn = $form.find('button[type="submit"]');
    $btn.prop('disabled', true);

    $.ajax({
      url: 'ajax/update_manual_request.php', // ไฟล์ PHP ที่เราเขียนไว้
      method: 'POST',
      data: fd,
      processData: false,   // สำคัญ: ห้ามให้ jQuery แปลง FormData
      contentType: false,   // สำคัญ: ให้ browser จัด multipart เอง
      dataType: 'json',
    })
      .done(function (res) {
        if (res && res.success) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: res.message || 'อัปเดตคำขอเรียบร้อยแล้ว',
            timer: 1200,
            showConfirmButton: false,
          })

          // ปิด modal
          $('#modalEditManualCase').modal('hide')

          // ถ้ามี DataTable อยู่ (เช่น window.requestTable) ให้ reload เฉพาะตาราง
          if (window.requestTable && typeof window.requestTable.ajax === 'object') {
            window.requestTable.ajax.reload(null, false)
          } else {
            // ถ้าไม่มีอะไรก็ reload หน้าไปเลย
            setTimeout(function () {
              location.reload()
            }, 800)
          }
        } else {
          Swal.fire(
            'ผิดพลาด',
            (res && res.message) ? res.message : 'ไม่สามารถบันทึกการแก้ไขได้',
            'error'
          )
        }
      })
      .fail(function () {
        Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error')
      })
      .always(function () {
        $btn.prop('disabled', false)
      })
  })

})()
</script>

<script>
$(document).on('click', '.btn-delete-request', function () {

    const reqId = $(this).data('id');
    if (!reqId) return;

    Swal.fire({
        title: 'ยืนยันการลบคำขอ?',
        text: 'คำขอและไฟล์ที่เกี่ยวข้องทั้งหมดจะถูกลบถาวร',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ลบคำขอ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((res) => {

        if (!res.isConfirmed) return;

        $.ajax({
            url: 'ajax/delete_manual_request.php',
            type: 'POST',
            data: { id: reqId },
            dataType: 'json',

            success: function (resp) {
                if (resp && resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบเรียบร้อย',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    if (window.requestTable) {
                        window.requestTable.ajax.reload(null, false);
                    } else {
                        setTimeout(() => location.reload(), 600);
                    }

                } else {
                    Swal.fire('ผิดพลาด', resp.message || 'ลบไม่สำเร็จ', 'error');
                }
            },

            error: function () {
                Swal.fire('ผิดพลาด', 'เชื่อมต่อเซิร์ฟเวอร์ไม่ได้', 'error');
            }
        });

    });

});
</script>

<script>
$(function () {

  // -------------------------------
  // toggle block บุคคล / นิติบุคคล
  // -------------------------------
  function toggleApplicantBlocks(isJuristic) {
    isJuristic = String(isJuristic);

    $('#block_person').removeClass('d-none');

    if (isJuristic === '1') {
      $('#block_juristic').removeClass('d-none');
      $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุลผู้แทน/ผู้รับมอบอำนาจ)');
    } else {
      $('#block_juristic').addClass('d-none');
      $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');
    }
  }

  // tooltip
  $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip();


  // ======================================================
  // กดปุ่มพิมพ์ สร.1
  // ======================================================
  $(document).on('click', '.btn-print-form1', function (e) {
    e.preventDefault();

    const requestId = $(this).data('id');
    $('#request_id').val(requestId);

    // เช็คก่อนว่ามี lock ไหม
    $.getJSON('ajax/ajax_get_applicant_info.php', { request_id: requestId })
      .done(function (res) {

        // ====== ถ้าไม่ success เช่น ไม่มี record → ให้กรอกข้อมูลใหม่ ======
        if (!res.success) {
          openApplicantModalForNew();
          return;
        }

        const d = res.data;

        // ====== ถ้าเอกสารถูกล็อกแล้ว → พิมพ์เลย ======
        if (d.form1_locked == 1) {

          if (d.print_url) {
            window.open(d.print_url, '_blank');
          } else {
            // fallback URL
            window.open('print_form1.php?request_id=' + requestId, '_blank');
          }

          return; // ไม่ต้องเปิด modal
        }

        // ====== ถ้ายังไม่ locked → เปิด modal เพื่อกรอกข้อมูล ======
        openApplicantModalFill(d);

      })
      .fail(function () {
        alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
      });
  });


  // ===========================
  // ฟังก์ชันเปิด modal ใหม่
  // ===========================
  function openApplicantModalForNew() {
    $('#form_applicant')[0].reset();
    $('#block_juristic').addClass('d-none');
    $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');
    $('#modalApplicant').modal('show');
  }

  // ===========================
  // ฟังก์ชันเปิด modal พร้อมข้อมูล
  // ===========================
  function openApplicantModalFill(d) {

    // reset
    $('#form_applicant')[0].reset();
    $('#block_juristic').addClass('d-none');
    $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');

    // filled
    $('#written_at').val(d.written_at || '');
    $('#written_date_text').val(d.written_date_text || '');
    $('#written_date').val(d.written_date || '');

    const isJuristic = d.is_juristic || 0;
    $('#is_juristic_' + isJuristic).prop('checked', true);
    toggleApplicantBlocks(isJuristic);

    $('#applicant_name').val(d.applicant_name || '');
    $('#applicant_age').val(d.applicant_age || '');
    $('#applicant_nationality').val(d.applicant_nationality || '');
    $('#applicant_address_no').val(d.applicant_address_no || '');
    $('#applicant_moo').val(d.applicant_moo || '');
    $('#applicant_tambon').val(d.applicant_tambon || '');
    $('#applicant_amphoe').val(d.applicant_amphoe || '');
    $('#applicant_province').val(d.applicant_province || '');

    // โทรศัพท์ไม่ให้แก้ไข
    $('#applicant_phone').val(d.applicant_phone || '').prop('readonly', true);

    $('#juristic_name').val(d.juristic_name || '');
    $('#juristic_office').val(d.juristic_office || '');
    $('#juristic_address_no').val(d.juristic_address_no || '');
    $('#juristic_moo').val(d.juristic_moo || '');
    $('#juristic_tambon').val(d.juristic_tambon || '');
    $('#juristic_amphoe').val(d.juristic_amphoe || '');
    $('#juristic_province').val(d.juristic_province || '');

    // เปิด modal
    $('#modalApplicant').modal('show');
  }

  // function openApplicantModalFill(d) {

  //   // reset
  //   $('#form_applicant')[0].reset();
  //   $('#block_juristic').addClass('d-none');
  //   $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');

  //   // filled
  //   $('#written_at').val(d.written_at || '');
  //   $('#written_date_text').val(d.written_date_text || '');
  //   $('#written_date').val(d.written_date || '');

  //   const isJuristic     = d.is_juristic || 0;
  //   const licenseStatus  = d.license_status || '';     // เช่น 'normal', 'none', 0
  //   const isManualCase   = Number(d.is_manual_case || 0) === 1;

  //   // reset disable radio ทุกครั้งก่อน
  //   $('#is_juristic_0, #is_juristic_1').prop('disabled', false);

  //   // ติ๊กค่าเดิมก่อน
  //   $('#is_juristic_' + isJuristic).prop('checked', true);
  //   toggleApplicantBlocks(isJuristic);

  //   // 🧠 กำหนดว่าจะล็อกไหม
  //   // ล็อกเฉพาะกรณีไม่ใช่ manual case และ license_status = normal
  //   const shouldLockApplicantType =
  //       !isManualCase && String(licenseStatus) === 'normal';

  //   if (shouldLockApplicantType) {
  //     // 🔒 ล็อกไม่ให้เลือกเปลี่ยน บุคคลธรรมดา / นิติบุคคล
  //     $('#is_juristic_0, #is_juristic_1').prop('disabled', true);
  //   }
  //   // ถ้า license_status เป็น 'none' หรือ '0' หรือเป็น manual_case
  //   // เราไม่เข้า if ข้างบน → ปล่อยให้เลือกได้ตามปกติ

  //   $('#applicant_name').val(d.applicant_name || '');
  //   $('#applicant_age').val(d.applicant_age || '');
  //   $('#applicant_nationality').val(d.applicant_nationality || '');
  //   $('#applicant_address_no').val(d.applicant_address_no || '');
  //   $('#applicant_moo').val(d.applicant_moo || '');
  //   $('#applicant_tambon').val(d.applicant_tambon || '');
  //   $('#applicant_amphoe').val(d.applicant_amphoe || '');
  //   $('#applicant_province').val(d.applicant_province || '');

  //   // โทรศัพท์ไม่ให้แก้ไข
  //   $('#applicant_phone').val(d.applicant_phone || '').prop('readonly', true);

  //   $('#juristic_name').val(d.juristic_name || '');
  //   $('#juristic_office').val(d.juristic_office || '');
  //   $('#juristic_address_no').val(d.juristic_address_no || '');
  //   $('#juristic_moo').val(d.juristic_moo || '');
  //   $('#juristic_tambon').val(d.juristic_tambon || '');
  //   $('#juristic_amphoe').val(d.juristic_amphoe || '');
  //   $('#juristic_province').val(d.juristic_province || '');

  //   // เปิด modal
  //   $('#modalApplicant').modal('show');
  // }

  // ===========================
  // submit → save → lock → print
  // ===========================
  $('#form_applicant').on('submit', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn  = $form.find('button[type="submit"]');
    const old   = $btn.html();

    $btn.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin"></i> บันทึก...');

    $.post('ajax/ajax_save_applicant_form1.php', $form.serialize(), function (res) {

      if (!res.success) {
        alert(res.message || 'บันทึกไม่สำเร็จ');
        $btn.prop('disabled', false).html(old);
        return;
      }

      $('#modalApplicant').modal('hide');

      // เปิด PDF
      if (res.print_url) {
        window.open(res.print_url, '_blank');
      }

      $btn.prop('disabled', false).html(old);

    }, 'json').fail(function () {
      alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
      $btn.prop('disabled', false).html(old);
    });
  });


  // radio change
  $('input[name="is_juristic"]').on('change', function () {
    toggleApplicantBlocks($(this).val());
  });

});
</script>
           
<script>
$(document).ready(function () {

    let table;

    // ถ้าเคยถูก init เป็น DataTable แล้ว (เช่น จาก dataTables-demo.js)
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        table = $('#dataTable').DataTable();   // ดึง instance เดิม
    } else {
        table = $('#dataTable').DataTable({
            // ใส่ options ของคุณถ้ามี เช่น paging, ordering ฯลฯ
        });
    }

    // ----- ส่วนค้นหาด้วย shipcode -----
    const params   = new URLSearchParams(window.location.search);
    const shipcode = params.get('shipcode');

    if (shipcode) {
        // column(3) = คอลัมน์ที่คุณอยากให้ search
        table.column(1).search(shipcode).draw();
    }
});
</script>

<script>
// ======================= PREVIEW ไฟล์ (สร้างคำขอ) =======================
;(function () {
  const input   = document.getElementById('attachments');
  const preview = document.getElementById('filePreview');
  if (!input || !preview) return;

  function renderCreateList(oldTypes = []) {
  console.log('renderCreateList: files =', dtCreate.files.length);

  preview.innerHTML = '';
  const row = document.createElement('div');
  row.className = 'row g-3';
  preview.appendChild(row);

  for (let i = 0; i < dtCreate.files.length; i++) {
    const f   = dtCreate.files[i];

    // ✅ ให้ใช้ col เหมือน modal edit
    const col = document.createElement('div');
    col.className = 'col-6 col-md-3';

    // ✅ ใช้ .file-card เหมือน modal edit
    const card = document.createElement('div');
    card.className = 'border rounded p-2 shadow-sm file-card';

    // ---------- ส่วนรูป + ปุ่มลบ (แบบเดียวกับ renderSelectedPreviewManual) ----------
    const thumbWrap = document.createElement('div');
    thumbWrap.className = 'thumb-wrap';

    // ปุ่มกากบาท
    const btnDel = document.createElement('button');
    btnDel.type = 'button';
    // ใช้ class เดียวกับ modal edit เพื่อให้ CSS เดิมใช้ได้เลย
    btnDel.className = 'btn btn-sm btn-danger btn-remove-new-manual';
    btnDel.dataset.idx = i;
    btnDel.title = 'เอาไฟล์นี้ออก';
    btnDel.innerHTML = '<i class="bi bi-x-lg"></i>';

    btnDel.addEventListener('click', () => {
      const currentTypes = [];
      preview.querySelectorAll('select.attach-type')
             .forEach(sel => currentTypes.push(sel.value));

      const ndt = new DataTransfer();
      for (let j = 0; j < dtCreate.files.length; j++) {
        if (j !== i) ndt.items.add(dtCreate.files[j]);
      }
      dtCreate = ndt;
      input.files = dtCreate.files;

      const newTypes = currentTypes.filter((t, idx) => idx !== i);
      renderCreateList(newTypes);
    });

    thumbWrap.appendChild(btnDel);

    // รูป / PDF icon
    const isImg = f.type.startsWith('image/');
    if (isImg) {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(f);
      img.alt = f.name;
      img.style.width = '100%';
      img.style.height = '120px';
      img.style.objectFit = 'cover';
      thumbWrap.appendChild(img);
    } else {
      const pdfDiv = document.createElement('div');
      pdfDiv.className = 'icon-pdf';
      pdfDiv.textContent = 'PDF';
      thumbWrap.appendChild(pdfDiv);
    }

    card.appendChild(thumbWrap);

    // ---------- รายละเอียดไฟล์ ----------
    const infoName = document.createElement('div');
    infoName.className = 'file-name mt-2 text-truncate';
    infoName.title = f.name;
    infoName.textContent = f.name;

    const infoSize = document.createElement('div');
    infoSize.className = 'text-muted small';
    const sizeKB = (f.size / 1024).toFixed(1);
    infoSize.textContent = `${sizeKB} KB`;

    // ---------- dropdown ประเภทเอกสาร ----------
    const sel = document.createElement('select');
    sel.className = 'form-select form-select-sm mt-1 attach-type';
    sel.name = 'attachment_types[]';

    ATTACH_TYPES.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.value;
      opt.textContent = t.label;
      sel.appendChild(opt);
    });

    const defaultType = oldTypes[i] || ATTACH_TYPES[0].value;
    sel.value = defaultType;

    // ประกอบการ์ด
    card.appendChild(infoName);
    card.appendChild(infoSize);
    card.appendChild(sel);

    col.appendChild(card);
    row.appendChild(col);
  }
}




  input.addEventListener('change', e => {
    const maxSize  = 10 * 1024 * 1024;
    const allowExt = ['jpg','jpeg','png','gif','webp','pdf'];

    const currentTypes = [];
    preview.querySelectorAll('select.attach-type')
           .forEach(sel => currentTypes.push(sel.value));

    for (const f of e.target.files) {
      const ext = f.name.split('.').pop().toLowerCase();
      if (!allowExt.includes(ext)) {
        alert(`${f.name}: ชนิดไฟล์ไม่อนุญาต`);
        continue;
      }
      if (f.size > maxSize) {
        alert(`${f.name}: ไฟล์ใหญ่เกิน 10MB`);
        continue;
      }
      dtCreate.items.add(f);
      currentTypes.push(ATTACH_TYPES[0].value);
    }

    input.value = '';
    input.files = dtCreate.files;
    renderCreateList(currentTypes);
  });

  // ถ้าอยากมีฟังก์ชัน reset ตอนเปิด modal สร้างคำขอ
  window.resetManualAttachments = function () {
    dtCreate = new DataTransfer();
    input.value = '';
    preview.innerHTML = '';
  };
})();
</script>

<script>
$(document).on('click', '.btn-start-inspect', function () {
    const requestId = $(this).data('id');
    const departmentId = $(this).data('department');

    Swal.fire({
        title: 'เริ่มการตรวจเรือ?',
        text: 'หากกดเริ่ม ระบบจะถือว่าเริ่มการตรวจในวันนี้',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'เริ่มตรวจ',
        cancelButtonText: 'ยังไม่ตรวจ',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ แค่พาเข้า form
            window.location.href =
                'form_inspect.php?id=' + requestId +
                '&department_id=' + departmentId;
        }
        // ❌ Cancel = ไม่ต้องทำอะไร
    });
});
</script>

<?php 
include("../../private/shared/footerall.php");
?>