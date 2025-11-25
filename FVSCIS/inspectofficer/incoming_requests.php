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
                                            <td colspan="9" class="text-center text-muted">ยังไม่มีคำขอตรวจเรือที่รับผิดชอบ</td>
                                        </tr>
                                    <?php
                                    else:
                                        foreach ($requests as $req) :
                                    ?>
                                        <tr style="font-size: 14px;">
                                            <td data-order="0">
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- ปุ่มดูรายละเอียด -->
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalRequestDetail"
                                                        title="รายละเอียดคำขอ"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>)">
                                                    <i class="fas fa-search"></i>
                                                </button>

                                                <?php
                                                $attCount = InspectionAttachment::count_by_request_id($req->id);
                                                if ($attCount > 0): ?>
                                                <button class="btn btn-sm btn-warning btn-attachments"
                                                        title="ไฟล์แนบ (<?= $attCount ?>)"
                                                        data-id="<?= $req->id ?>">
                                                    <i class="fas fa-paperclip"></i>
                                                </button>
                                                <?php endif; ?> 

                                                <?php if((int)$session->user_id() === (int)$req->created_by) { ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-warning btn-edit-manual"
                                                        title="แก้ไขคำขอ"
                                                        data-id="<?= h($req->id); ?>">
                                                <i class="fas fa-edit"></i>
                                                </button>
                                                <?php } ?>
                                                
                                                <?php if((int)$session->user_id() === (int)$req->created_by) { ?>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger btn-delete-request"
                                                        title = "ลบคำขอ"
                                                        data-id="<?= h($req->id); ?>">
                                                    <i class="bi bi-trash"></i> 
                                                </button>
                                                <?php } ?>


                                                <!-- ปุ่มฟอร์มตรวจ -->
                                                <?php if ($req->is_confirm == 1): ?>
                                                    <a href="form_inspect.php?id=<?= h($req->id) ?>&department_id=<?= h($req->department_id) ?>"
                                                    class="btn btn-success btn-sm" title="ฟอร์มตรวจ">
                                                        <i class="fas fa-file-signature"></i>
                                                    </a>
                                                <?php else : ?>
                                                    <button class="btn btn-secondary btn-sm" title="ยังไม่สามารถกรอกฟอร์มตรวจได้" disabled>
                                                        <i class="fas fa-file-signature"></i>
                                                    </button>
                                                <?php endif; ?>
                                                   
                                                
                                            </div>
                                            </td>


                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= h($req->vessel_name) ?></td>
                                            <td><?= h($req->port_name) ?></td>
                                            <td><?= thai_date($req->inspect_date_start). " ถึงวันที่ ".thai_date($req->inspect_date_end) ?></td>
                                            <td><?= thai_date($req->confirmed_inspect_date) ?></td>
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
                                                        data-vessel="<?php echo h($req->vessel_name); ?>">
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
        { value: 'ทะเบียนเรือ',             label: 'ทะเบียนเรือ' },
        { value: 'ใบอนุญาตทำการประมง',     label: 'ใบอนุญาตทำการประมง' },
        { value: 'บัตรประชาชน',             label: 'บัตรประชาชน' },
        { value: 'หนังสือมอบอำนาจ',         label: 'หนังสือมอบอำนาจ' },
        { value: 'ผลตรวจสุขอนามัยเรือ',     label: 'ผลตรวจสุขอนามัยเรือ' },
        { value: 'ใบแจ้งผลตรวจ',             label: 'ใบแจ้งผลตรวจ' },
        { value: 'ใบรับรอง สร.3',            label: 'ใบรับรอง สร.3' },
        { value: 'อื่น ๆ',                    label: 'อื่น ๆ' },
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
      } else {
        html += `<br><span class="text-danger"><i class="fas fa-exclamation-circle"></i> ยังไม่มีการนัดหมายวันตรวจ</span>`
      }

      html += `</p>`

      if (req.is_confirm == 1) {
        html += `<p><span class="text-success"><i class="fas fa-calendar-check"></i> ผู้ขอตรวจยืนยันวันนัดตรวจแล้ว</span></p>`
        $('#btnConfirmDate').prop('disabled', true)
      } else {
        html += `<p><span class="text-danger"><i class="fas fa-calendar-check"></i> ยังไม่มีการยืนยันวันนัดตรวจ</span></p>`
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
  $('#editFilePreview').empty()

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
;(function () {
  const input   = document.getElementById('attachments')
  const preview = document.getElementById('filePreview')

  // ใช้ DataTransfer เก็บไฟล์จริงที่ส่งไปกับฟอร์ม
  const dt = new DataTransfer()

  // dropdown ประเภทเอกสาร
  const ATTACH_TYPES = [
    { value: 'ทะเบียนเรือ',         label: 'ทะเบียนเรือ' },
    { value: 'ใบอนุญาตทำการประมง', label: 'ใบอนุญาตทำการประมง' },
    { value: 'บัตรประชาชน',         label: 'บัตรประชาชน' },
    { value: 'หนังสือมอบอำนาจ',     label: 'หนังสือมอบอำนาจ' },
    { value: 'ผลตรวจสุขอนามัยเรือ', label: 'ผลตรวจสุขอนามัยเรือ' },
    { value: 'ใบแจ้งผลตรวจ',         label: 'ใบแจ้งผลตรวจ' },
    { value: 'ใบรับรอง สร.3',        label: 'ใบรับรอง สร.3' },
    { value: 'อื่น ๆ',                label: 'อื่น ๆ' },
  ]

  function renderList (oldTypes = []) {
    preview.innerHTML = ''

    const row = document.createElement('div')
    row.className = 'row g-3'
    preview.appendChild(row)

    for (let i = 0; i < dt.files.length; i++) {
      const f = dt.files[i]

      const col  = document.createElement('div')
      col.className = 'col-md-4 col-sm-6'

      const card = document.createElement('div')
      card.className = 'border rounded shadow-sm p-2 position-relative h-100 bg-white'

      // 🔴 ปุ่มลบ (มุมขวาบน เห็นชัด ๆ)
      const btnDel = document.createElement('button')
      btnDel.type = 'button'
      btnDel.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle'
      btnDel.style.zIndex = '10'
      btnDel.innerHTML = '<i class="fas fa-times"></i>'

      btnDel.addEventListener('click', function () {
        // เก็บประเภทเดิมก่อนลบ
        const currentTypes = []
        preview.querySelectorAll('select.attach-type').forEach(sel => {
          currentTypes.push(sel.value)
        })
        const newTypes = currentTypes.filter((t, idx) => idx !== i)

        // ลบไฟล์ index i ออกจาก DataTransfer
        const ndt = new DataTransfer()
        for (let j = 0; j < dt.files.length; j++) {
          if (j !== i) ndt.items.add(dt.files[j])
        }
        dt.items.clear()
        for (let k = 0; k < ndt.files.length; k++) dt.items.add(ndt.files[k])
        input.files = dt.files

        renderList(newTypes)
      })

      // ส่วนรูป (ratio 16:9)
      const ratio = document.createElement('div')
      ratio.className = 'ratio ratio-16x9 mb-2'

      const thumbBox = document.createElement('div')
      thumbBox.className =
        'w-100 h-100 d-flex align-items-center justify-content-center border rounded'
      thumbBox.style.overflow = 'hidden'

      if (f.type.startsWith('image/')) {
        const img = document.createElement('img')
        img.src = URL.createObjectURL(f)
        img.style.width = '100%'
        img.style.height = '100%'
        img.style.objectFit = 'cover'
        thumbBox.appendChild(img)
      } else {
        const ext = f.name.split('.').pop().toUpperCase()
        thumbBox.textContent = ext
        thumbBox.style.fontSize = '12px'
      }

      ratio.appendChild(thumbBox)

      // รายละเอียดไฟล์
      const info = document.createElement('div')
      info.className = 'mb-2'
      const sizeKB = (f.size / 1024).toFixed(1)
      info.innerHTML =
        `<div class="fw-semibold text-truncate" title="${f.name}">${f.name}</div>` +
        `<div class="text-muted" style="font-size:12px">${sizeKB} KB</div>`

      // dropdown ประเภทเอกสาร
      const sel = document.createElement('select')
      sel.className = 'form-select form-select-sm attach-type'
      sel.name = 'attachment_types[]'

      ATTACH_TYPES.forEach(t => {
        const opt = document.createElement('option')
        opt.value = t.value
        opt.textContent = t.label
        sel.appendChild(opt)
      })

      const defaultType = oldTypes[i] || ATTACH_TYPES[0].value
      sel.value = defaultType

      // ประกอบการ์ด
      card.appendChild(btnDel)
      card.appendChild(ratio)
      card.appendChild(info)
      card.appendChild(sel)
      col.appendChild(card)
      row.appendChild(col)
    }
  }

  // เมื่อเลือกไฟล์ใหม่
  input.addEventListener('change', e => {
    const maxSize  = 10 * 1024 * 1024 // 10MB
    const allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp']

    const currentTypes = []
    preview.querySelectorAll('select.attach-type').forEach(sel => {
      currentTypes.push(sel.value)
    })

    for (const f of e.target.files) {
      const ext = f.name.split('.').pop().toLowerCase()
      if (!allowExt.includes(ext)) {
        alert(`${f.name}: ชนิดไฟล์ไม่อนุญาต`)
        continue
      }
      if (f.size > maxSize) {
        alert(`${f.name}: ไฟล์ใหญ่เกิน 10MB`)
        continue
      }
      dt.items.add(f)
      currentTypes.push(ATTACH_TYPES[0].value)
    }

    input.value = ''
    input.files = dt.files
    renderList(currentTypes)
  })

  // 🧹 ฟังก์ชันสำหรับล้างไฟล์แนบเวลาเปิดฟอร์มใหม่
  window.resetManualAttachments = function () {
    dt.items.clear()
    input.value = ''
    preview.innerHTML = ''
  }
})()
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
    url: 'ajax/create_manual_request_by_officer.php',
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
// ======================= 3) PREVIEW ไฟล์ (สร้างคำขอ) =======================
;(function () {
  const input   = document.getElementById('attachments');
  const preview = document.getElementById('filePreview');
  if (!input || !preview) return;

  function renderCreateList(oldTypes = []) {
    preview.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'row g-3';
    preview.appendChild(row);

    for (let i = 0; i < dtCreate.files.length; i++) {
      const f   = dtCreate.files[i];
      const col = document.createElement('div');
      col.className = 'col-md-4 col-sm-6';

      const card = document.createElement('div');
      card.className = 'border rounded shadow-sm p-2 position-relative h-100 bg-white';

      // ปุ่มลบ
      const btnDel = document.createElement('button');
      btnDel.type = 'button';
      btnDel.className = 'btn btn-sm btn-link text-danger position-absolute top-0 end-0';
      btnDel.innerHTML = '<i class="fas fa-times"></i>';
      btnDel.onclick = () => {
        const currentTypes = [];
        preview.querySelectorAll('select.attach-type').forEach(sel => currentTypes.push(sel.value));

        const ndt = new DataTransfer();
        for (let j = 0; j < dtCreate.files.length; j++) {
          if (j !== i) ndt.items.add(dtCreate.files[j]);
        }
        dtCreate = ndt;
        input.files = dtCreate.files;

        const newTypes = currentTypes.filter((t, idx) => idx !== i);
        renderCreateList(newTypes);
      };

      const ratio = document.createElement('div');
      ratio.className = 'ratio ratio-16x9 mb-2';

      const box = document.createElement('div');
      box.className = 'w-100 h-100 d-flex align-items-center justify-content-center border rounded';
      box.style.overflow = 'hidden';

      if (f.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        box.appendChild(img);
      } else {
        const ext = f.name.split('.').pop().toUpperCase();
        box.textContent = ext;
        box.style.fontSize = '12px';
      }

      ratio.appendChild(box);

      const info = document.createElement('div');
      info.className = 'mb-2';
      const sizeKB = (f.size / 1024).toFixed(1);
      info.innerHTML =
        `<div class="fw-semibold text-truncate" title="${f.name}">${f.name}</div>` +
        `<div class="text-muted" style="font-size:12px">${sizeKB} KB</div>`;

      const sel = document.createElement('select');
      sel.className = 'form-select form-select-sm attach-type';
      sel.name = 'attachment_types[]';
      ATTACH_TYPES.forEach(t => {
        const opt = document.createElement('option');
        opt.value = t.value;
        opt.textContent = t.label;
        sel.appendChild(opt);
      });
      const defaultType = oldTypes[i] || ATTACH_TYPES[0].value;
      sel.value = defaultType;

      card.appendChild(btnDel);
      card.appendChild(ratio);
      card.appendChild(info);
      card.appendChild(sel);
      col.appendChild(card);
      row.appendChild(col);
    }
  }

  input.addEventListener('change', e => {
    const maxSize  = 10 * 1024 * 1024;
    const allowExt = ['jpg','jpeg','png','gif','webp','pdf'];

    const currentTypes = [];
    preview.querySelectorAll('select.attach-type').forEach(sel => currentTypes.push(sel.value));

    for (const f of e.target.files) {
      const ext = f.name.split('.').pop().toLowerCase();
      if (!allowExt.includes(ext)) { alert(`${f.name}: ชนิดไฟล์ไม่อนุญาต`); continue; }
      if (f.size > maxSize) { alert(`${f.name}: ไฟล์ใหญ่เกิน 10MB`); continue; }
      dtCreate.items.add(f);
      currentTypes.push(ATTACH_TYPES[0].value);
    }

    input.value = '';
    input.files = dtCreate.files;
    renderCreateList(currentTypes);
  });
})();
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
              <option value="บัตรประชาชน">บัตรประชาชน</option>
              <option value="หนังสือมอบอำนาจ">หนังสือมอบอำนาจ</option>
              <option value="ผลตรวจสุขอนามัยเรือ">ผลตรวจสุขอนามัยเรือ</option>
              <option value="ใบแจ้งผลตรวจ">ใบแจ้งผลตรวจ</option>
              <option value="ใบรับรอง สร.3">ใบรับรอง สร.3</option>
              <option value="อื่น ๆ">อื่น ๆ</option>
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

               
<?php 
include("../../private/shared/footerall.php");
?>