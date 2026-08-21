<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);
$Officer = Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarsigner.php");
include("../../private/shared/topbarsigner.php");

?>

<!-- Begin Page Content -->
<div class="container-fluid">

<?php
  // ✅ ดึงเฉพาะคำขอที่ signing_unit ตรงกับเจ้าหน้าที่
  $DepartmentgroupObj = DepartmentGroup::find_one_by_officer_id($Officer->id);

  // ✅ ควรมี responsible_unit เพื่อใช้ count_by_status_responsible_unit
  // ถ้าคุณมี method นับตาม signing_unit โดยตรงจะยิ่งดี
  $responsible_unit = $DepartmentgroupObj->responsible_unit ?? null;

  // กล่องสถานะ
  $cnt_inactive = $responsible_unit ? FvSanitationCertificationOld::count_by_status_responsible_unit('inactive', $responsible_unit) : 0;
  $cnt_pending  = $responsible_unit ? FvSanitationCertificationOld::count_by_status_responsible_unit('pending',  $responsible_unit) : 0;
  $cnt_fail     = $responsible_unit ? FvSanitationCertificationOld::count_by_status_responsible_unit('fail',     $responsible_unit) : 0;
  $cnt_active   = $responsible_unit ? FvSanitationCertificationOld::count_by_status_responsible_unit('active',   $responsible_unit) : 0;
?>

  <!-- DataTales Example -->
  <div class="card shadow mb-4">

    <!-- ✅ header แบบตัวอย่าง (เพิ่มกล่องสถานะ) -->
    <div class="card-header py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between">

      <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0">
        <!-- คุณจะใส่ปุ่มเพิ่มข้อมูลก็ได้ ถ้าไม่ใช้ให้คอมเมนต์ไว้เหมือนเดิม -->
        <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFvscisOldAdd">
            <i class="fas fa-plus"></i> บันทึกผลตรวจจากเอกสาร
        </button> -->
      </h6>

      <div class="d-flex flex-wrap gap-2">
        <!-- inactive -->
        <div class="p-3 rounded shadow-sm" style="background: rgba(108, 117, 125, 0.15); min-width: 120px">
          <div class="small text-secondary">เรือไม่ Active</div>
          <div class="fw-bold fs-5 text-secondary"><?= (int)$cnt_inactive ?></div>
        </div>

        <!-- pending -->
        <div class="p-3 rounded shadow-sm" style="background: rgba(247, 201, 72, 0.2); min-width: 120px">
          <div class="small" style="color:#b68b00">อยู่ระหว่างยื่นตรวจ</div>
          <div class="fw-bold fs-5" style="color:#b68b00"><?= (int)$cnt_pending ?></div>
        </div>

        <!-- fail -->
        <div class="p-3 rounded shadow-sm" style="background: rgba(227, 93, 106, 0.25); min-width: 120px">
          <div class="small text-danger">ตรวจไม่ผ่าน</div>
          <div class="fw-bold fs-5 text-danger"><?= (int)$cnt_fail ?></div>
        </div>

        <!-- active -->
        <div class="p-3 rounded shadow-sm" style="background: rgba(76, 175, 145, 0.2); min-width: 120px">
          <div class="small" style="color:#2d7a65">ได้รับ สร.3</div>
          <div class="fw-bold fs-5" style="color:#2d7a65"><?= (int)$cnt_active ?></div>
        </div>
      </div>
    </div>
    <!--/header-->

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
          <thead>
            <tr style="font-size: 14px;">
              <th class="d-none">id</th>
              <th>ดำเนินการ</th>
              <th>ชื่อเรือ</th>
              <th>เลขทะเบียนเรือ</th>
              <th>วันที่ขอตรวจ</th>
              <th>วันที่บังคับใช้</th>
              <th>วันที่หมดอายุ</th>
              <th>ประเภท สร.3</th>
              <th>สถานะ</th>
            </tr>
          </thead>

          <tbody>
            <?php
              $FvSanitationCertificationOlds = FvSanitationCertificationOld::find_all_by_signing_unit($DepartmentgroupObj->id);

              if (empty($FvSanitationCertificationOlds)) :
            ?>
              <tr>
                <td colspan="8" class="text-center text-muted">ไม่พบข้อมูล สร.3 ที่รับผิดชอบ</td>
              </tr>
            <?php
              else:
                foreach ($FvSanitationCertificationOlds as $req) :

                  // ✅ สีแถวตามสถานะ (เหมือนตัวอย่าง)
                  $rowClass = '';
                  switch ($req->status) {
                    case 'inactive': $rowClass = 'tr-not-scheduled'; break;
                    case 'pending':  $rowClass = 'tr-inspecting'; break;
                    case 'fail':     $rowClass = 'tr-cancelled'; break;
                    case 'pass':     $rowClass = 'tr-pending-confirmed'; break;
                    case 'active':   $rowClass = 'tr-completed'; break;
                    default:         $rowClass = ''; break;
                  }

                  // ✅ กันวันที่ว่าง/NULL/0000-00-00
                  $request_date    = thai_date_safe($req->request_date);
                  $effective_date  = thai_date_safe($req->effective_date);
                  $expiration_date = thai_date_safe($req->expiration_date);
                  // ✅ ประเภท สร.3 (คุณเลือก field ที่ใช้งานจริง)
                  // ถ้าคุณใช้ certification_status แล้ว ให้ใช้บรรทัดนี้
                  $certType = $req->certification_status ?? ($req->certificate_status ?? '-');
            ?>

              <tr class="<?= h($rowClass) ?>" style="font-size: 14px;">
                <td class="d-none"><?= h($req->id) ?></td>
                <td>
                  <button type="button" title="ดูข้อมูลเก่า" class="btn btn-info btn-sm"
                          onclick="openOldCertificationModalById(<?= h($req->id) ?>)">
                    <i class="fas fa-search"></i>
                  </button>
                </td>

                <td><?= h($req->vessel_name) ?></td>
                <td><?= h($req->ship_code) ?></td>
                <td><?= $request_date ?></td>
                <td><?= $effective_date ?></td>
                <td><?= $expiration_date ?></td>
                <td><?= h($certType) ?></td>

                <!-- ✅ Badge สถานะ -->
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

  <!-- modalviewOldModal -->
  <div class="modal fade" id="oldCertificationModal" tabindex="-1" aria-labelledby="oldCertLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="oldCertLabel">รายละเอียดใบรับรองสุขอนามัยเรือ (ข้อมูลเก่า)</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>

        <div class="modal-body">
          <div id="oldCertLoading" class="text-center my-4" style="display:none;">
            <div class="spinner-border" role="status"></div>
            <div class="mt-2">กำลังโหลดข้อมูล...</div>
          </div>

          <div id="oldCertError" class="alert alert-danger" style="display:none;"></div>

          <div id="oldCertContent" style="display:none;">
            <!-- (ของเดิมคุณได้ดีอยู่แล้ว ไม่แตะ) -->
            ...
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
        </div>
      </div>
    </div>
  </div>
  <!-- /modalviewOldModal -->

</div><!-- /container-fluid -->


  
<?php include("../../private/shared/footerofficer.php"); ?>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
                    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                                
    <script src="../js/fvscis.js"></script>  
    <script>
        // แปลงวันที่ (YYYY-MM-DD) -> ไทย (D MMM YYYY)
        function formatThaiDate(isoDate) {
        if (!isoDate || isoDate === '0000-00-00') return '-';
        const months = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        const d = new Date(isoDate);
        if (isNaN(d)) return isoDate;
        const dd = d.getDate();
        const mm = months[d.getMonth()];
        const yyyy = d.getFullYear() + 543;
        return `${dd} ${mm} ${yyyy}`;
        }

        function badge(text, type='secondary') {
        return `<span class="badge bg-${type}">${text || '-'}</span>`;
        }

        function statusToBadge(status) {
        if (!status) return badge('-', 'secondary');
        const s = String(status).toLowerCase();
        if (['active','ผ่าน','valid','approved'].some(k => s.includes(k))) return badge(status, 'success');
        if (['temporary','ชั่วคราว','pending','รอ'].some(k => s.includes(k))) return badge(status, 'warning');
        if (['expired','หมดอายุ','reject','ไม่ผ่าน'].some(k => s.includes(k))) return badge(status, 'danger');
        return badge(status, 'primary');
        }

        // ✅ เปิด modal ด้วย id
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
            success: function(res) {
            $('#oldCertLoading').hide();

            if (!res || !res.success) {
                $('#oldCertError').text(res && res.message ? res.message : 'ไม่พบข้อมูล').show();
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
            error: function(xhr) {
            $('#oldCertLoading').hide();
            $('#oldCertError').text('เกิดข้อผิดพลาดในการดึงข้อมูล (' + xhr.status + ')').show();
            }
        });
        }
        </script>
                                          

<?php 
include("../../private/shared/footerall.php");
?>