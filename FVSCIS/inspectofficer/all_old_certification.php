<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']); $Officer =
Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php"); 
$department = Department::find_by_id($Officer->departments_id); 
$departmentgroup = DepartmentGroup::find_by_id($department->parent_department);
$evaluation_agency = $department->name; 
$signing_unit = $departmentgroup->name; 
$ownerobj = DepartmentGroup::find_by_id($departmentgroup->responsible_unit);
$responsible_unit = $ownerobj->name;
$department_id_check = $department->id;
 ?>
<!-- Begin Page Content -->
<div class="container-fluid">
  <?php
                    // ดึงจำนวนแต่ละสถานะ
$cnt_inactive = FvSanitationCertificationOld::count_by_status_responsible_unit('inactive', $departmentgroup->responsible_unit);
  $cnt_pending =  FvSanitationCertificationOld::count_by_status_responsible_unit('pending',  $departmentgroup->responsible_unit); 
  $cnt_fail =  FvSanitationCertificationOld::count_by_status_responsible_unit('fail',  $departmentgroup->responsible_unit); 
  $cnt_active =  FvSanitationCertificationOld::count_by_status_responsible_unit('active',  $departmentgroup->responsible_unit); ?>
  <!-- DataTales Example -->
  <div class="card shadow mb-4">
    <div
      class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between"
    >
      <!-- ปุ่ม Add -->
      <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0">
        <button
          class="btn btn-primary"
          data-bs-toggle="modal"
          data-bs-target="#modalFvscisOldAdd"
        >
          <i class="fas fa-plus"></i> บันทึกผลตรวจจากเอกสาร
        </button>
      </h6>

      <!-- กล่องสถานะ -->
      <div class="d-flex flex-wrap gap-2">
        <!-- inactive -->
        <div
          class="p-3 rounded shadow-sm"
          style="background: rgba(108, 117, 125, 0.15); min-width: 120px"
        >
          <div class="small text-secondary">เรือไม่ Active</div>
          <div class="fw-bold fs-5 text-secondary"><?= $cnt_inactive ?></div>
        </div>

        <!-- pending -->
        <div
          class="p-3 rounded shadow-sm"
          style="background: rgba(247, 201, 72, 0.2); min-width: 120px"
        >
          <div class="small" style="color: #b68b00">อยู่ระหว่างยื่นตรวจ</div>
          <div class="fw-bold fs-5" style="color: #b68b00">
            <?= $cnt_pending ?>
          </div>
        </div>

        <!-- fail -->
        <div
          class="p-3 rounded shadow-sm"
          style="background: rgba(227, 93, 106, 0.25); min-width: 120px"
        >
          <div class="small text-danger">ตรวจไม่ผ่าน</div>
          <div class="fw-bold fs-5 text-danger"><?= $cnt_fail ?></div>
        </div>

        <!-- active -->
        <div
          class="p-3 rounded shadow-sm"
          style="background: rgba(76, 175, 145, 0.2); min-width: 120px"
        >
          <div class="small" style="color: #2d7a65">ได้รับ สร.3</div>
          <div class="fw-bold fs-5" style="color: #2d7a65">
            <?= $cnt_active ?>
          </div>
        </div>
      </div>
      <!-- กล่องสถานะ -->
    </div>
    <!--card header-->
    <div class="card-body">
      <div class="table-responsive">
        <table
          class="table table-bordered"
          id="dataTable"
          width="100%"
          cellspacing="0"
        >
          <thead>
            <tr style="font-size: 14px">
            <th class="d-none">id</th>  <!-- คอลัมน์ซ่อน -->
              <th>ดำเนินการ</th>
              <th>ชื่อเรือ</th>
              <th>เลขทะเบียนเรือ</th>
              <th>หมายเลข สร.3</th>
              <th>ประเภท สร.3</th>
              <th>วันที่บังคับใช้</th>
              <th>วันที่หมดอายุ</th>
              <th>หน่วยประเมิน</th>
              <th>สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $new_departments_id = Officer::map_departments_id($Officer->departments_id);
            $FvSanitationCertificationOlds = FvSanitationCertificationOld::find_all_by_responsible_unit($new_departments_id);
            if (empty($FvSanitationCertificationOlds)) : ?>
            <tr>
              
                ไม่พบข้อมูล สร.3 ที่รับผิดชอบ
              </td>
            </tr>
            <?php
                                else:
                                    foreach ($FvSanitationCertificationOlds as $req) :

                                        // 🎨 กำหนด class ของแถวตามสถานะ
                                        $rowClass = '';
                                        switch ($req->status) { case 'inactive':
            $rowClass = 'tr-not-scheduled'; break; case 'pending': $rowClass =
            'tr-inspecting'; break; case 'fail': $rowClass = 'tr-cancelled';
            break; case 'pass': $rowClass = 'tr-pending-confirmed'; break; case
            'active': $rowClass = 'tr-completed'; break; } ?>
            <tr class="<?= $rowClass ?>" style="font-size: 14px">
               <td class="d-none"><?= h($req->id); ?></td>
                  <td>
                      <button
                              type="button"
                              title="ดูข้อมูลเก่า"
                              class="btn btn-info btn-sm me-1 mb-1"
                              onclick="openOldCertificationModalById(<?= h($req->id) ?>)"
                          >
                              <i class="fas fa-search"></i>
                      </button>
                      <?php if($department_id_check == $req->evaluation_agency) {?>
                      <button
                          type="button"
                          title="แก้ไขข้อมูลเก่า"
                          class="btn btn-primary btn-sm btn-edit-fvscisold me-1 mb-1"
                          data-id="<?= h($req->id) ?>"
                      >
                          <i class="fas fa-edit"></i>
                      </button>
                      <?php } ?>
                      <button
                          type="button"
                          title="ลบข้อมูลเก่า"
                          class="btn btn-danger btn-sm me-1 mb-1"
                          onclick="deleteOldCertification(<?= h($req->id) ?>, this)"
                      >
                          <i class="fas fa-trash"></i>
                      </button>

                      <?php
                      $attCount = FvCertificateAttachment::count_by_certificate_id($req->id);
                      if ($attCount > 0):
                      ?>
                          <button
                              class="btn btn-sm btn-warning btn-attachments me-1 mb-1"
                              title="ไฟล์แนบ (<?= $attCount ?>)"
                              data-id="<?= $req->id ?>"
                          >
                              <i class="fas fa-paperclip"></i>
                          </button>
                      <?php endif; ?>
                  </td>

              <td><?= h($req->vessel_name) ?></td>
              <td><?= h($req->ship_code) ?></td>
              <td><?= $req->certificate_number ?></td>
              <td><?= h($req->certificate_status) ?></td>
              <td><?= date('d/m/Y', strtotime($req->effective_date)) ?></td>
              <td><?= date('d/m/Y', strtotime($req->expiration_date)) ?></td>
              <td>
                <?php 
                $department = Department::find_by_id($req->evaluation_agency);
                echo h($department->name); ?>
              </td>
              
              <!-- 🎯 Badge ตามสถานะ -->
              <td>
                <?php if ($req->status === 'active'): ?>
                <span class="badge bg-success">ใช้งานจริง</span>

                <?php elseif ($req->status === 'pending'): ?>
                <span class="badge bg-primary">กำลังตรวจ</span>

                <?php elseif ($req->status === 'fail'): ?>
                <span class="badge bg-danger">ไม่ผ่าน</span>

                <?php elseif ($req->status === 'pass'): ?>
                <span class="badge bg-info text-dark">ผ่านแล้ว</span>

                <?php elseif ($req->status === 'inactive'): ?>
                <span class="badge bg-secondary">ไม่ยื่น / หมดอายุ</span>

                <?php else: ?>
                <span class="badge bg-light text-dark">-</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php
                                    endforeach;
                                endif;
                                ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- /modalviewOldModal -->
   <?php include(__DIR__ . '/modal/modal_view_fvsanitation_old.php'); ?>
  <?php include(__DIR__ . '/modal/modal_add_fvsanitation_old.php'); ?>
  <?php include(__DIR__ . '/modal/modal_edit_fvsanitation_old.php'); ?>
  <?php include(__DIR__ . '/modal/modal_attachment.php'); ?>
</div>
<!-- <div class="container-fluid"> -->

<?php include("../../private/shared/footerofficer.php"); ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../js/fvscis.js"></script>
<script>
document.onreadystatechange = function () {
    const loader = document.getElementById('pageLoader');

    if (document.readyState === 'loading') {
        loader.style.width = '40%';
    }

    if (document.readyState === 'interactive') {
        loader.style.width = '70%';
    }

    if (document.readyState === 'complete') {
        loader.style.width = '100%';
        setTimeout(() => loader.style.opacity = '0', 400);
        setTimeout(() => loader.style.display = 'none', 800);
    }
};
</script>
<script>
// #region DataTable + Badge จำนวนรายการ
// (ยังไม่ใช้ เลยคอมเมนต์เก็บไว้)

// $(function () {
//   const table = $.fn.dataTable.isDataTable('#dataTable')
//     ? $('#dataTable').DataTable()
//     : $('#dataTable').DataTable({
//         language: {
//           search: 'ค้นหา:',
//           lengthMenu: 'แสดง _MENU_ รายการ',
//           info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
//           infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
//         },
//       });

//   const $filter = $('#dataTable_wrapper .dataTables_filter');
//   if (!$('#totalCount').length) {
//     $filter.prepend(
//       '<span id="totalCount" class="badge bg-info me-2 mb-2 mb-md-0"></span>'
//     );
//   }

//   function updateCount() {
//     const info = table.page.info();
//     const total = info.recordsTotal;
//     const display = info.recordsDisplay;

//     let text = 'ทั้งหมด ' + total.toLocaleString('th-TH') + ' รายการ';
//     if (display !== total) {
//       text += ' (กำลังแสดง ' + display.toLocaleString('th-TH') + ')';
//     }
//     $('#totalCount').text(text);
//   }

//   updateCount();
//   table.on('draw.dt', updateCount);
// });

//#endregion DataTable + Badge จำนวนรายการ

// #region Helpers: วันที่ / Badge สถานะ

function formatThaiDate(isoDate) {
  if (!isoDate || isoDate === '0000-00-00') return '-';

  const months = [
    'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
  ];

  const d = new Date(isoDate);
  if (isNaN(d)) return isoDate;

  const dd = d.getDate();
  const mm = months[d.getMonth()];
  const yyyy = d.getFullYear() + 543;

  return `${dd} ${mm} ${yyyy}`;
}

function badge(text, type = 'secondary') {
  return `<span class="badge bg-${type}">${text || '-'}</span>`;
}

function statusToBadge(status) {
  if (!status) return badge('-', 'secondary');

  const s = String(status).toLowerCase();

  if (['active', 'ผ่าน', 'valid', 'approved'].some((k) => s.includes(k))) {
    return badge(status, 'success');
  }
  if (['temporary', 'ชั่วคราว', 'pending', 'รอ'].some((k) => s.includes(k))) {
    return badge(status, 'warning');
  }
  if (['expired', 'หมดอายุ', 'reject', 'ไม่ผ่าน'].some((k) => s.includes(k))) {
    return badge(status, 'danger');
  }

  return badge(status, 'primary');
}

//#endregion Helpers: วันที่ / Badge สถานะ

// #region Modal: ดูข้อมูลใบรับรองเก่า (View Old Certification)

function openOldCertificationModalById(id) {
  $('#oldCertError').hide().text('');
  $('#oldCertContent').hide();
  $('#oldCertLoading').show();

  const modalEl = document.getElementById('oldCertificationModal');
  const bsModal = new bootstrap.Modal(modalEl);
  bsModal.show();

  $.ajax({
    url: 'ajax/get_old_certification_by_id.php',
    type: 'GET',
    dataType: 'json',
    data: { id: id },
    success(res) {
      $('#oldCertLoading').hide();

      if (!res || !res.success) {
        $('#oldCertError')
          .text(res && res.message ? res.message : 'ไม่พบข้อมูล')
          .show();
        return;
      }

      const d = res.data || {};

      $('#oc_vessel_name').text(d.vessel_name || '-');
      $('#oc_ship_code').text(d.ship_code || '-');
      $('#oc_vessel_mark').text(d.vessel_mark || '-');
      $('#oc_license_number').text(d.license_number || '-');
      $('#oc_gear_type').text(d.gear_type || '-');
      $('#oc_owner_name').text(d.owner_name || '-');
      $('#oc_certificate_number').text(d.certificate_number || '-');
      $('#oc_request_date').text(formatThaiDate(d.request_date));
      $('#oc_signature_date').text(formatThaiDate(d.signature_date));
      $('#oc_effective_date').text(formatThaiDate(d.effective_date));
      $('#oc_expiration_date').text(formatThaiDate(d.expiration_date));
      $('#oc_evaluation_agency').text(d.evaluation_agency || '-');
      $('#oc_signing_unit').text(d.signing_unit || '-');
      $('#oc_responsible_unit').text(d.responsible_unit || '-');
      $('#oc_vessel_status').html(statusToBadge(d.vessel_status));
      $('#oc_certificate_status').html(statusToBadge(d.certificate_status));
      $('#oc_temporary_reason').text(d.temporary_reason || '-');
      $('#oc_remark').text(d.remark || '-');

      $('#oldCertContent').show();
    },
    error(xhr) {
      $('#oldCertLoading').hide();
      $('#oldCertError')
        .text(`เกิดข้อผิดพลาดในการดึงข้อมูล (${xhr.status})`)
        .show();
    },
  });
}

//#endregion Modal: ดูข้อมูลใบรับรองเก่า (View Old Certification)

// #region Lookup eLicense (ADD)

;(function () {
  // เก็บ AJAX ล่าสุด และ token สำหรับกัน response เก่า
  let xhrLookup = null;
  let lastRequestToken = 0;

  function setBusy(isBusy) {
    $('#btnLookupShip').prop('disabled', isBusy);
    $('#btnText').toggleClass('d-none', isBusy);
    $('#btnSpin').toggleClass('d-none', !isBusy);
  }

  // รีเซ็ตค่าและสถานะก่อนค้นหา
  function resetBeforeLookup() {
    $('#fv-vessel-name').val('').prop('readonly', false);
    $('#fv-owner-name').val('').prop('readonly', false);
    $('#fv-vessel-mark').val('');
    $('#fv-license-number').val('');
    $('#fv-gear-type').val('');
    $('.elicense-only').addClass('d-none');
    $('#fv-license-status').val('');
  }

  // กรณีพบใน eLicense
  function applyElicenseFound(data) {
    const $status = $('#fv-license-status');

    $('#fv-vessel-name').val(data.vessel_name || '');
    $('#fv-owner-name').val(data.display_name || '');
    $('#fv-vessel-mark').val(data.fishing_mark || '');
    $('#fv-license-number').val(data.license_no || '');
    $('#fv-gear-type').val(data.geartype || '');

    $('.elicense-only').removeClass('d-none');

    // กรอกอัตโนมัติ → ล็อกแก้ไข
    $('#fv-vessel-name').prop('readonly', true);
    $('#fv-owner-name').prop('readonly', true);
    $('#fv-vessel-mark').prop('readonly', true);
    $('#fv-license-number').prop('readonly', true);
    $('#fv-gear-type').prop('readonly', true);

    $status.val('normal');

    Swal.fire({
      icon: 'success',
      title: 'ดึงข้อมูลสำเร็จ',
      timer: 900,
      showConfirmButton: false,
    });
  }

  // กรณีไม่พบใน eLicense
  function applyElicenseNotFound() {
    const $status = $('#fv-license-status');

    $('#fv-vessel-name').val('').prop('readonly', false);
    $('#fv-owner-name').val('').prop('readonly', false);
    $('#fv-vessel-mark').val('');
    $('#fv-license-number').val('');
    $('#fv-gear-type').val('');

    $('.elicense-only').addClass('d-none');
    $status.val('none');

    Swal.fire({
      icon: 'warning',
      title: 'ไม่พบข้อมูลใน eLicense',
      text: 'สามารถกรอกข้อมูลเรือด้วยตนเองได้',
    });
  }

  function lookupShip() {
    // abort AJAX เก่า (กัน response เก่า)
    if (xhrLookup && xhrLookup.readyState !== 4) {
      xhrLookup.abort();
    }

    // token ใหม่ (กัน response เก่าไม่ให้แตะ DOM)
    const requestToken = ++lastRequestToken;

    // clear ค่าเก่าก่อนค้นหา
    resetBeforeLookup();

    const shipCode = ($('#fv-ship-code').val() || '').trim();
    if (!shipCode) {
      Swal.fire({ icon: 'warning', title: 'กรุณากรอกทะเบียนเรือ' });
      return;
    }

    setBusy(true);

    xhrLookup = $.ajax({
      url: 'ajax/get_elicense_by_ship_code.php',
      type: 'POST',
      dataType: 'json',
      data: { ship_code: shipCode },
      success(res) {
        if (requestToken !== lastRequestToken) return;

        if (res && res.success && res.data) {
          applyElicenseFound(res.data);
        } else {
          applyElicenseNotFound();
        }
      },
      error(xhr) {
        if (requestToken !== lastRequestToken) return;
        if (xhr.status === 0) return; // abort

        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
      complete() {
        if (requestToken !== lastRequestToken) return;
        setBusy(false);
      },
    });
  }

  // คลิกปุ่มค้นหา
  $(document).on('click', '#btnLookupShip', lookupShip);

  // กด Enter เพื่อค้นหา
  $(document).on('keydown', '#fv-ship-code', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      lookupShip();
    }
  });
})();

//#endregion Lookup eLicense (ADD)

// #region Lookup eLicense (EDIT)

;(function () {
  let xhrLookup = null;
  let lastRequestToken = 0;

  function setBusy(isBusy) {
    $('#btnLookupShipEdit').prop('disabled', isBusy);
    $('#btnEditText').toggleClass('d-none', isBusy);
    $('#btnEditSpin').toggleClass('d-none', !isBusy);
  }

  // รีเซ็ตค่าและสถานะก่อนค้นหา (EDIT)
  function resetBeforeLookupEdit() {
    $('#edit-vessel-mark').val('');
    $('#edit-license-number').val('');
    $('#edit-gear-type').val('');
    $('.elicense-only').addClass('d-none');
    $('#edit-license-status').val('');
  }

  // กรณีพบใน eLicense (EDIT)
  function applyElicenseFoundEdit(data) {
    const $status = $('#edit-license-status');

    $('#edit-vessel-name').val(data.vessel_name || '');
    $('#edit-owner-name').val(data.display_name || '');
    $('#edit-vessel-mark').val(data.fishing_mark || '');
    $('#edit-license-number').val(data.license_no || '');
    $('#edit-gear-type').val(data.geartype || '');

    $('.elicense-only').removeClass('d-none');

    $('#edit-vessel-name').prop('readonly', true);
    $('#edit-owner-name').prop('readonly', true);
    $('#edit-vessel-mark').prop('readonly', true);
    $('#edit-license-number').prop('readonly', true);
    $('#edit-gear-type').prop('readonly', true);

    $status.val('normal');

    Swal.fire({
      icon: 'success',
      title: 'ดึงข้อมูลจาก eLicense สำเร็จ',
      timer: 900,
      showConfirmButton: false,
    });
  }

  // กรณีไม่พบใน eLicense (EDIT)
  function applyElicenseNotFoundEdit() {
    const $status = $('#edit-license-status');

    $('#edit-vessel-mark').val('');
    $('#edit-license-number').val('');
    $('#edit-gear-type').val('');

    $('.elicense-only').addClass('d-none');
    $status.val('none');

    Swal.fire({
      icon: 'warning',
      title: 'ไม่พบข้อมูลใน eLicense',
      text: 'สามารถกรอกข้อมูลเรือด้วยตนเองได้',
    });
  }

  function lookupShipEdit() {
    if (xhrLookup && xhrLookup.readyState !== 4) {
      xhrLookup.abort();
    }

    const requestToken = ++lastRequestToken;

    resetBeforeLookupEdit();

    const shipCode = ($('#edit-ship-code').val() || '').trim();
    if (!shipCode) {
      Swal.fire({ icon: 'warning', title: 'กรุณากรอกทะเบียนเรือ' });
      return;
    }

    setBusy(true);

    xhrLookup = $.ajax({
      url: 'ajax/get_elicense_by_ship_code.php',
      type: 'POST',
      dataType: 'json',
      data: { ship_code: shipCode },
      success(res) {
        if (requestToken !== lastRequestToken) return;

        if (res && res.success && res.data) {
          applyElicenseFoundEdit(res.data);
        } else {
          applyElicenseNotFoundEdit();
        }
      },
      error(xhr) {
        if (requestToken !== lastRequestToken) return;
        if (xhr.status === 0) return;

        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
      complete() {
        if (requestToken !== lastRequestToken) return;
        setBusy(false);
      },
    });
  }

  // คลิกปุ่มค้นหา (EDIT)
  $(document).on('click', '#btnLookupShipEdit', lookupShipEdit);

  // กด Enter เพื่อค้นหา (EDIT)
  $(document).on('keydown', '#edit-ship-code', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      lookupShipEdit();
    }
  });
})();

//#endregion Lookup eLicense (EDIT)

// #region Add Modal: เลือกไฟล์ + ประเภทเอกสาร

;(function () {
  function addBytesFmt(n) {
    if (n < 1024) return n + ' B';
    if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
    return (n / 1024 / 1024).toFixed(1) + ' MB';
  }

  function addIsImg(f) {
    return /^image\//i.test(f.type);
  }

  function syncInputFilesAdd(input) {
    const selected = input._selectedFiles || [];
    const dt = new DataTransfer();
    selected.forEach((f) => dt.items.add(f));
    input.files = dt.files;
  }

  function renderSelectedPreviewAdd(input, wrap) {
    const selected = input._selectedFiles || [];
    wrap.innerHTML = '';

    selected.forEach((file, idx) => {
      const col = document.createElement('div');
      col.className = 'col-6 col-md-4';
      col.dataset.idx = idx;

      const thumb = addIsImg(file)
        ? `<img src="${URL.createObjectURL(file)}"
                 style="width:100%;height:100px;object-fit:cover;border-radius:6px;">`
        : `<div class="d-flex justify-content-center align-items-center"
                 style="width:100%;height:100px;background:#f5f5f5;border-radius:6px;">
               <strong>PDF</strong>
           </div>`;

      col.innerHTML = `
        <div class="border rounded p-2 position-relative bg-light small">
          <button type="button"
                  class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-del-file">
            <i class="bi bi-x"></i>
          </button>
          ${thumb}
          <div class="mt-1 text-truncate" title="${file.name}">${file.name}</div>
          <div class="text-muted">${addBytesFmt(file.size)}</div>
          <select class="form-select form-select-sm mt-1" name="attachment_type[]">
            <option value="ทะเบียนเรือ">ทะเบียนเรือ</option>
            <option value="ใบอนุญาตทำการประมง">ใบอนุญาตทำการประมง</option>
            <option value="ใบอนุญาตใช้เรือ">ใบอนุญาตใช้เรือ</option>
            <option value="บัตรประชาชนผู้ยื่น">บัตรประชาชนผู้ยื่น</option>
            <option value="หนังสือมอบอำนาจ">หนังสือมอบอำนาจ</option>
            <option value="สำเนาบัตรประชาชนผู้มอบอำนาจ">สำเนาบัตรประชาชนผู้มอบอำนาจ</option>
            <option value="บัตรประจำตัวตัวแทนนิติบุคคล">บัตรประจำตัวตัวแทนนิติบุคคล</option>
            <option value="ใบรับรอง สร.3 ฉบับเก่า">ใบรับรอง สร.3 ฉบับเก่า</option>
            <option value="สร.1">สร.1</option>
            <option value="สร.2-1">สร.2-1</option>
            <option value="สร.2-2">สร.2-2</option>
            <option value="สร.2-3">สร.2-3</option>
            <option value="สร.2-4">สร.2-4</option>
            <option value="สร.3">สร.3</option>
          </select>
        </div>
      `;

      wrap.appendChild(col);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('certAttachments');
    const wrap = document.getElementById('selectedFiles');
    if (!input || !wrap) return;

    input.addEventListener('change', function () {
      let selected = this._selectedFiles || [];
      const newFiles = Array.from(this.files || []);

      newFiles.forEach((f) => {
        if (!selected.some((x) => x.name === f.name && x.size === f.size)) {
          selected.push(f);
        }
      });

      this._selectedFiles = selected;
      syncInputFilesAdd(this);
      renderSelectedPreviewAdd(this, wrap);
    });

    wrap.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-del-file');
      if (!btn) return;

      const box = btn.closest('[data-idx]');
      if (!box) return;

      const idx = Number(box.dataset.idx);
      let selected = input._selectedFiles || [];

      if (idx >= 0 && idx < selected.length) {
        selected.splice(idx, 1);
        input._selectedFiles = selected;
        syncInputFilesAdd(input);
        renderSelectedPreviewAdd(input, wrap);
      }
    });

    $('#modalFvscisOldAdd').on('hidden.bs.modal', function () {
      input.value = '';
      input._selectedFiles = [];
      wrap.innerHTML = '';
    });
  });
})();

//#endregion Add Modal: เลือกไฟล์ + ประเภทเอกสาร

// #region AJAX: บันทึก ADD

$(document)
  .off('submit.fvscisoldAdd')
  .on('submit.fvscisoldAdd', '#form-fvscisold-add', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type=submit]').prop('disabled', true);
    const fd = new FormData();

    $form.serializeArray().forEach((p) => fd.append(p.name, p.value));

    const input = document.getElementById('certAttachments');
    if (input?.files?.length) {
      const types = $('select[name="attachment_type[]"]')
        .map(function () {
          return $(this).val() || '';
        })
        .get();

      Array.from(input.files).forEach((f, idx) => {
        fd.append('attachments[]', f, f.name);
        fd.append('attachment_type[]', types[idx] || '');
      });
    }

    $.ajax({
      url: 'ajax/create_fvscisold.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json',
      success(res) {
        if (res?.success) {
          Swal.fire({
            icon: 'success',
            title: 'บันทึกสำเร็จ',
            timer: 1000,
            showConfirmButton: false,
          }).then(() => location.reload());
        } else {
          Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: res?.message || '',
          });
        }
      },
      error(xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
      complete() {
        $btn.prop('disabled', false);
      },
    });
  });

//#endregion AJAX: บันทึก ADD

// #region Edit Modal: ไฟล์เดิม + ไฟล์ใหม่

;(function () {
  function bytesFmt(n) {
    if (n < 1024) return n + ' B';
    if (n < 1048576) return (n / 1024).toFixed(1) + ' KB';
    return (n / 1048576).toFixed(1) + ' MB';
  }

  function isImgFile(f) {
    return (
      /^image\//i.test(f.type) ||
      /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(f.name)
    );
  }

  // -------- โหลดไฟล์เดิมของใบรับรอง (EDIT) --------
  window.renderExistingAttachments = function (certId) {
    const $modal = $('#modalFvscisOldEdit');
    const $wrap = $modal.find('#existingFiles').empty();

    $.getJSON('ajax/get_certification_attachments.php', { id: certId }, function (res) {
      if (!res || !res.success || !Array.isArray(res.attachments)) return;

      res.attachments.forEach((a) => {
        const url = a.url_enc || a.url;
        const isImg = !!a.is_image;
        const name = a.name || '';
        const typeLabel = a.attachment_type ? a.attachment_type : '';

        const thumb = isImg
          ? `<div class="thumb-wrap">
                <img src="${url}" alt="${name}"
                     class="img-thumbnail w-100"
                     style="height:120px; object-fit:cover;">
             </div>`
          : `<div class="border rounded p-2 text-center">
                <i class="bi bi-file-earmark"></i>
             </div>`;

        $wrap.append(`
          <div class="col-6 col-md-3 mb-2" data-attach-id="${a.id}">
            <div class="file-card shadow-sm p-2 position-relative">

              <button type="button"
                      class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 btn-del-existing"
                      style="z-index:10;"
                      data-id="${a.id}"
                      title="ลบไฟล์นี้ออกจากระบบ">
                <i class="bi bi-x-lg"></i>
              </button>

              <a href="${url}" target="_blank" title="เปิดดูไฟล์">
                ${thumb}
              </a>

              <div class="small text-truncate mt-1" title="${name}">
                ${name}
              </div>
              <div class="text-muted small">
                ${typeLabel}
              </div>
            </div>
          </div>
        `);


      });
    });
  };

  // เปิด modal แล้วโหลดไฟล์เดิม
  $('#modalFvscisOldEdit').on('shown.bs.modal', function () {
    const id = $('#edit-id').val();
    if (id) window.renderExistingAttachments(id);
  });

  // -------- จัดการไฟล์ใหม่ที่เลือก (EDIT) --------
  function syncInputFilesEdit() {
    const $modal = $('#modalFvscisOldEdit');
    const $input = $modal.find('#certAttachmentsEdit');
    const selected = $input.data('selected') || [];
    const dt = new DataTransfer();
    selected.forEach((f) => dt.items.add(f));
    if ($input[0]) $input[0].files = dt.files;
  }

  function renderSelectedPreviewEdit() {
    const $modal = $('#modalFvscisOldEdit');
    const $input = $modal.find('#certAttachmentsEdit');
    const $list = $modal.find('#selectedFilesEdit');
    const selected = $input.data('selected') || [];

    if (!selected.length) {
      $list.empty();
      return;
    }

    let html = '';
    selected.forEach((f, idx) => {
      const isImg = isImgFile(f);
      const src = isImg ? URL.createObjectURL(f) : '';

      html += `
        <div class="col-6 col-md-3">
          <div class="border rounded p-2 shadow-sm file-card position-relative">

            <button type="button"
                    class="btn btn-sm btn-danger btn-remove-new-edit position-absolute"
                    style="top:4px; left:4px; z-index:5;"
                    data-idx="${idx}"
                    title="เอาไฟล์นี้ออก">
              <i class="bi bi-x-lg"></i>
            </button>

            ${
              isImg
                ? `<div class="thumb-wrap">
                     <img src="${src}" alt="${f.name}">
                   </div>`
                : `<div class="icon-pdf">PDF</div>`
            }

            <div class="file-name mt-2 text-truncate" title="${f.name}">
              ${f.name}
            </div>
            <div class="text-muted small">${bytesFmt(f.size || 0)}</div>

            <select class="form-select form-select-sm mt-1" name="attachment_type_new[]">
              <option value="ทะเบียนเรือ">ทะเบียนเรือ</option>
              <option value="ใบอนุญาตทำการประมง">ใบอนุญาตทำการประมง</option>
              <option value="ใบอนุญาตใช้เรือ">ใบอนุญาตใช้เรือ</option>
              <option value="บัตรประชาชนผู้ยื่น">บัตรประชาชนผู้ยื่น</option>
              <option value="หนังสือมอบอำนาจ">หนังสือมอบอำนาจ</option>
              <option value="สำเนาบัตรประชาชนผู้มอบอำนาจ">สำเนาบัตรประชาชนผู้มอบอำนาจ</option>
              <option value="บัตรประจำตัวตัวแทนนิติบุคคล">บัตรประจำตัวตัวแทนนิติบุคคล</option>
              <option value="ใบรับรอง สร.3 ฉบับเก่า">ใบรับรอง สร.3 ฉบับเก่า</option>
              <option value="สร.1">สร.1</option>
              <option value="สร.2-1">สร.2-1</option>
              <option value="สร.2-2">สร.2-2</option>
              <option value="สร.2-3">สร.2-3</option>
              <option value="สร.2-4">สร.2-4</option>
              <option value="สร.3">สร.3</option>
            </select>
          </div>
        </div>
      `;
    });

    $list.html(html);
  }

  // เมื่อเลือกไฟล์ใหม่
  $('#modalFvscisOldEdit')
    .off('change.certAttachEdit')
    .on('change.certAttachEdit', '#certAttachmentsEdit', function () {
      const $modal = $('#modalFvscisOldEdit');
      const $input = $modal.find('#certAttachmentsEdit');

      let selected = $input.data('selected') || [];
      const files = Array.from(this.files || []);

      files.forEach((f) => {
        if (!selected.some((x) => x.name === f.name && x.size === f.size)) {
          selected.push(f);
        }
      });

      $input.data('selected', selected);
      syncInputFilesEdit();
      renderSelectedPreviewEdit();
    })
    // ลบไฟล์ใหม่ที่เพิ่งเลือก
    .off('click.removeNewEdit')
    .on('click.removeNewEdit', '.btn-remove-new-edit', function () {
      const $modal = $('#modalFvscisOldEdit');
      const $input = $modal.find('#certAttachmentsEdit');
      let selected = $input.data('selected') || [];
      const idx = +$(this).data('idx');

      if (idx >= 0) {
        selected.splice(idx, 1);
      }

      $input.data('selected', selected);
      syncInputFilesEdit();
      renderSelectedPreviewEdit();
    });

  // -------- ลบไฟล์เดิม (existing) --------
  $(document).on('click', '.btn-del-existing', function () {
    const attachId = $(this).data('id');
    if (!attachId) return;

    const $btn = $(this);

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
      if (!result.isConfirmed) return;

      $btn.prop('disabled', true);

      $.post(
        'ajax/fvscisold_attachment_delete.php',
        { attachment_id: attachId },
        function (res) {
          if (res && res.success) {
            $(`[data-attach-id="${attachId}"]`).remove();

            Swal.fire({
              icon: 'success',
              title: 'ลบไฟล์เรียบร้อย',
              timer: 900,
              showConfirmButton: false,
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'ลบไม่สำเร็จ',
              text: res?.message || 'เกิดข้อผิดพลาด',
            });
          }
        },
        'json'
      )
        .fail(() => {
          Swal.fire({
            icon: 'error',
            title: 'เชื่อมต่อไม่ได้',
            text: 'โปรดลองใหม่อีกครั้ง',
          });
        })
        .always(() => {
          $btn.prop('disabled', false);
        });
    });
  });


  // รีเซ็ตเมื่อปิดโมดัล
  $('#modalFvscisOldEdit').on('hidden.bs.modal', function () {
    const $modal = $('#modalFvscisOldEdit');
    const $input = $modal.find('#certAttachmentsEdit');
    $input.val('').removeData('selected');
    $modal.find('#selectedFilesEdit').empty();
    $modal.find('#existingFiles').empty();
  });
})();

//#endregion Edit Modal: ไฟล์เดิม + ไฟล์ใหม่

// #region AJAX: บันทึก EDIT

$(document)
  .off('submit.fvscisold')
  .on('submit.fvscisold', '#form-fvscisold-edit', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn = $form.find('button[type=submit]').prop('disabled', true);

    const fd = new FormData();
    $form.serializeArray().forEach((p) => fd.append(p.name, p.value));

    const input = document.getElementById('certAttachmentsEdit');
    if (input?.files?.length) {
      const types = $('select[name="attachment_type_new[]"]')
        .map(function () {
          return $(this).val() || '';
        })
        .get();

      Array.from(input.files).forEach((f, idx) => {
        fd.append('attachments[]', f, f.name);
        fd.append('attachment_type_new[]', types[idx] || '');
      });
    }

    $.ajax({
      url: 'ajax/update_fvscisold.php',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json',
      success(res) {
        if (res?.success) {
          const msg =
            res.files_saved > 0
              ? `บันทึกสำเร็จ + เพิ่มไฟล์ใหม่ ${res.files_saved} ไฟล์`
              : 'บันทึกสำเร็จ (ไม่มีไฟล์ใหม่)';
          Swal.fire({
            icon: 'success',
            title: msg,
            timer: 1000,
            showConfirmButton: false,
          }).then(() => location.reload());
        } else {
          Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด',
            text: res?.message || '',
          });
        }
      },
      error(xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
      complete() {
        $btn.prop('disabled', false);
      },
    });
  });

//#endregion AJAX: บันทึก EDIT

// #region เปิดโมดัลแก้ไขจากปุ่ม Edit

;(function () {
  $(document).on('click', '.btn-edit-fvscisold', function () {
    const id = $(this).data('id');
    if (!id) return;

    // reset form
    const $form = $('#form-fvscisold-edit')[0];
    if ($form) $form.reset();

    $('#edit-id').val(id);
    $('#edit-license-status').val('');

    // เปิดให้แก้ทุกช่องก่อน (เดี๋ยวค่อยล็อกตาม license_status)
    $('#edit-ship-code').prop('readonly', false);
    $('#edit-vessel-name').prop('readonly', false);
    $('#edit-owner-name').prop('readonly', false);
    $('#edit-vessel-mark').prop('readonly', false);
    $('#edit-license-number').prop('readonly', false);
    $('#edit-gear-type').prop('readonly', false);

    $('.elicense-only').addClass('d-none');

    $.ajax({
      url: 'ajax/get_fvscisold.php',
      type: 'POST',
      dataType: 'json',
      data: { id: id },
      success(res) {
        if (res && res.success && res.data) {
          const d = res.data;

          // เติมค่าพื้นฐาน
          $('#edit-ship-code').val(d.ship_code || '');
          $('#edit-vessel-name').val(d.vessel_name || '');
          $('#edit-vessel-mark').val(d.vessel_mark || '');
          $('#edit-license-number').val(d.license_number || '');
          $('#edit-gear-type').val(d.gear_type || '');
          $('#edit-owner-name').val(d.owner_name || '');
          $('#edit-certificate-number').val(d.certificate_number || '');

          $('#edit-request-date').val(d.request_date || '');
          $('#edit-signature-date').val(d.signature_date || '');
          $('#edit-effective-date').val(d.effective_date || '');
          $('#edit-expiration-date').val(d.expiration_date || '');

          $('#edit-certificate-status').val(d.certificate_status || '');
          $('#edit-evaluation-agency').val(d.evaluation_agency || '');
          $('#edit-signing-unit').val(d.signing_unit || '');
          $('#edit-responsible-unit').val(d.responsible_unit || '');
          $('#edit-temporary-reason').val(d.temporary_reason || '');
          $('#edit-remark').val(d.remark || '');

          // จัดการ license_status
          const licenseStatus = d.license_status || 'none';
          $('#edit-license-status').val(licenseStatus);

          if (licenseStatus === 'normal') {
            // มีใน eLicense
            $('.elicense-only').removeClass('d-none');

            $('#edit-ship-code').prop('readonly', true);
            $('#edit-vessel-name').prop('readonly', true);
            $('#edit-owner-name').prop('readonly', true);
            $('#edit-vessel-mark').prop('readonly', true);
            $('#edit-license-number').prop('readonly', true);
            $('#edit-gear-type').prop('readonly', true);
          } else if (licenseStatus === 'none') {
            // ไม่มีใน eLicense
            $('.elicense-only').addClass('d-none');

            $('#edit-ship-code').prop('readonly', true);
            $('#edit-vessel-name').prop('readonly', false);
            $('#edit-owner-name').prop('readonly', false);

            $('#edit-vessel-mark').prop('readonly', true);
            $('#edit-license-number').prop('readonly', true);
            $('#edit-gear-type').prop('readonly', true);
          } else {
            // ค่าอื่น ๆ
            $('.elicense-only').removeClass('d-none');
            $('#edit-ship-code').prop('readonly', false);
            $('#edit-vessel-name').prop('readonly', false);
            $('#edit-owner-name').prop('readonly', false);
            $('#edit-vessel-mark').prop('readonly', false);
            $('#edit-license-number').prop('readonly', false);
            $('#edit-gear-type').prop('readonly', false);
          }

          // โหลดไฟล์แนบเดิม
          window.renderExistingAttachments(id);

          // เปิด modal
          new bootstrap.Modal(
            document.getElementById('modalFvscisOldEdit')
          ).show();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'ไม่พบข้อมูล',
            text: res?.message || '',
          });
        }
      },
      error(xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
    });
  });
})();

//#endregion เปิดโมดัลแก้ไขจากปุ่ม Edit

// #region ลบใบรับรองเก่า (Delete Row)

function deleteOldCertification(id, btn) {
  if (!id) return;

  Swal.fire({
    title: 'ยืนยันการลบ?',
    text: 'ลบแล้วไม่สามารถกู้คืนได้',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ลบ',
    cancelButtonText: 'ยกเลิก',
  }).then((result) => {
    if (!result.isConfirmed) return;

    const $btn = $(btn).prop('disabled', true);

    $.ajax({
      url: 'ajax/delete_fvscisold.php',
      type: 'POST',
      dataType: 'json',
      data: { id: id },
      success(res) {
        if (res && res.success) {
          const $tr = $btn.closest('tr');
          const dt =
            ($.fn.DataTable && $('#dataTable').data('DataTable')) ||
            $('#dataTable').DataTable?.();
          if (dt) {
            dt.row($tr).remove().draw(false);
          } else {
            $tr.remove();
          }

          Swal.fire({
            icon: 'success',
            title: 'ลบแล้ว',
            timer: 900,
            showConfirmButton: false,
          });
        } else {
          $btn.prop('disabled', false);
          Swal.fire({
            icon: 'error',
            title: 'ลบไม่สำเร็จ',
            text: res?.message || '',
          });
        }
      },
      error(xhr) {
        $btn.prop('disabled', false);
        Swal.fire({
          icon: 'error',
          title: 'เชื่อมต่อไม่ได้',
          text: xhr.responseText || 'โปรดลองใหม่',
        });
      },
    });
  });
}

//#endregion ลบใบรับรองเก่า (Delete Row)

// #region Modal รูปไฟล์แนบ (Photo Attachments)

;(function () {
  $(document).on('click', '.btn-attachments', function () {
    const reqId = $(this).data('id');
    if (!reqId) return;

    $('#photoModalReqId').text('');
    $('#photoGrid').empty();
    $('#photoEmpty').addClass('d-none').text('กำลังโหลด...');
    $('#photoPreviewWrap').addClass('d-none');
    $('#photoPreviewImg').attr('src', '');

    $('#modalPhotoAttachments').modal('show');

    const pDetail = $.ajax({
      url: 'ajax/get_certification_detail.php',
      method: 'GET',
      data: { id: reqId },
      dataType: 'json',
    });

    const pAttach = $.ajax({
      url: 'ajax/get_certification_attachments.php',
      method: 'GET',
      data: { id: reqId },
      dataType: 'json',
    });

    $.when(pDetail, pAttach)
      .done(function (detailRes, attachRes) {
        const detail = detailRes[0];
        const attach = attachRes[0];

        let vesselName = '';
        let shipCode = '';
        if (detail && detail.success && detail.request) {
          vesselName = detail.request.vessel_name || '';
          shipCode = detail.request.ship_code || '';
        }

        let photos = [];
        if (attach && attach.success && Array.isArray(attach.attachments)) {
          photos = attach.attachments.filter((a) => a.is_image);
          photos = photos.map((p) => ({
            ...p,
            _url: p.url_enc ? p.url_enc : encodeURI(p.url || ''),
          }));
        } else {
          $('#photoEmpty')
            .removeClass('d-none')
            .text('ไม่สามารถโหลดไฟล์แนบได้');
        }

        renderPhotoGrid(photos);

        const parts = [];
        if (vesselName) parts.push(`ชื่อเรือ ${vesselName}`);
        if (shipCode) parts.push(`ทะเบียน ${shipCode}`);
        const leftText = parts.length ? parts.join(' • ') : `คำขอ #${reqId}`;
        const rightText = `— ${photos.length} รูป`;
        $('#photoModalReqId').text(`${leftText} ${rightText}`);
      })
      .fail(function () {
        $('#photoEmpty')
          .removeClass('d-none')
          .text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
      });
  });

  function renderPhotoGrid(photos) {
    const $grid = $('#photoGrid');
    const $empty = $('#photoEmpty');
    const $pvW = $('#photoPreviewWrap');
    const $pv = $('#photoPreviewImg');

    const valid = photos
      .filter((p) => p.is_image)
      .filter((p) => p.exists !== false);

    if (!valid.length) {
      $grid.empty();
      $empty.removeClass('d-none').text('ยังไม่มีรูปภาพแนบ');
      $pvW.addClass('d-none');
      return;
    }

    $empty.addClass('d-none');

    let html = '';
    valid.forEach((p) => {
      const u = p.url_enc || encodeURI(p.url || '');
      html += `
        <div class="border rounded p-1 shadow-sm me-2 mb-2" style="width:180px;">
          <a href="${u}" class="photo-thumb" data-url="${u}">
            <img src="${u}"
                 alt="${p.name}"
                 class="img-thumbnail w-100"
                 style="height:120px; object-fit:cover;">
          </a>
          <div class="small text-truncate mt-1" title="${p.name}">
            ${p.name}
          </div>
          <div class="text-muted small">
            ${p.attachment_type ? p.attachment_type : ''}
          </div>
        </div>
      `;
    });
    $grid.html(html);

    const first = valid[0];
    const firstUrl = first.url_enc || encodeURI(first.url || '');
    $pv.attr('src', firstUrl);
    $pvW.removeClass('d-none');

    $grid
      .off('click', 'a.photo-thumb')
      .on('click', 'a.photo-thumb', function (e) {
        e.preventDefault();
        const u = $(this).data('url');
        $pv.attr('src', u);
        $pvW.removeClass('d-none');
      });
  }
})();

//#endregion Modal รูปไฟล์แนบ (Photo Attachments)
</script>



<?php 
include("../../private/shared/footerall.php");
?>