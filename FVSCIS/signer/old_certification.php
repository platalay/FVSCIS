<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);
$Officer = Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarsigner.php");
include("../../private/shared/topbarofficer.php");

?>

<!-- Begin Page Content -->
<div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <!--<h6 class="m-0 font-weight-bold text-primary">-->
                                <!-- ปุ่ม Add -->
                                <!--<button class="btn btn-success mb-3" onclick="addDepartmentgroup()">
                                    <i class="fas fa-plus"></i> เพิ่มข้อมูล
                                </button>
                            </h6>-->
                        </div>
                        <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr style="font-size: 14px;">
                                        <th>ดำเนินการ</th>
                                        <th>ชื่อเรือ</th>
                                        <th>เลขทะเบียนเรือ</th>
                                        <th>วันที่ขอตรวจ</th>
                                        <th>วันที่บังคับใช้</th>
                                        <th>วันที่หมดอายุ</th>
                                        <th>ประเภท สร.3</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // ✅ ดึงเฉพาะคำขอที่ department_id ตรงกับเจ้าหน้าที่
                                    $DepartmentgroupObj = DepartmentGroup::find_one_by_officer_id($Officer->id);
                                    $FvSanitationCertificationOlds = FvSanitationCertificationOld::find_all_by_signing_unit($DepartmentgroupObj->id); 

                                    if (empty($FvSanitationCertificationOlds)) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">ไม่พบข้อมูล สร.3 ที่รับผิดชอบ</td>
                                        </tr>
                                    <?php
                                    else:
                                        foreach ($FvSanitationCertificationOlds as $req) :
                                    ?>
                                        <tr style="font-size: 14px;">
                                            <td>
                                                <button type="button" title="ดูข้อมูลเก่า" class="btn btn-info btn-sm"
                                                        onclick="openOldCertificationModalById(<?= h($req->id) ?>)">
                                                <i class="fas fa-search"></i>
                                                </button>
                                            </td>
                                            <td><?= h($req->vessel_name) ?></td>
                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= date('d/m/Y', strtotime($req->request_date)) ?></td>
                                            <td><?= date('d/m/Y', strtotime($req->effective_date)) ?></td>
                                            <td><?= date('d/m/Y', strtotime($req->expiration_date)) ?></td>
                                            <td><?= h($req->certificate_status) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    </div>
                    <!-- modalviewOldModal -->
                    <!-- Modal: รายละเอียดใบรับรองสุขอนามัยเรือ (ข้อมูลเก่า) -->
                    <div class="modal fade" id="oldCertificationModal" tabindex="-1" aria-labelledby="oldCertLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="oldCertLabel">รายละเอียดใบรับรองสุขอนามัยเรือ (ข้อมูลเก่า)</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Loading -->
                            <div id="oldCertLoading" class="text-center my-4" style="display:none;">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2">กำลังโหลดข้อมูล...</div>
                            </div>

                            <!-- Error -->
                            <div id="oldCertError" class="alert alert-danger" style="display:none;"></div>

                            <!-- เนื้อหา -->
                            <div id="oldCertContent" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                <label class="form-label text-muted">ชื่อเรือ</label>
                                <div id="oc_vessel_name" class="fw-semibold"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">ทะเบียนเรือ</label>
                                <div id="oc_ship_code" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หมายเลขเครื่องหมายเรือ</label>
                                <div id="oc_vessel_mark"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">เลขที่ใบอนุญาตทำการประมง</label>
                                <div id="oc_license_number"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">ชนิดเครื่องมือทำการประมง</label>
                                <div id="oc_gear_type"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">ชื่อเจ้าของเรือ</label>
                                <div id="oc_owner_name"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">เลขที่ใบรับรอง</label>
                                <div id="oc_certificate_number"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">สถานะเรือ</label>
                                <div id="oc_vessel_status"></div>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่ยื่นคำขอ</label>
                                <div id="oc_request_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่ลงนาม</label>
                                <div id="oc_signature_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่มีผล</label>
                                <div id="oc_effective_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันหมดอายุ</label>
                                <div id="oc_expiration_date"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยประเมิน</label>
                                <div id="oc_evaluation_agency"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยลงนาม</label>
                                <div id="oc_signing_unit"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยรับผิดชอบ</label>
                                <div id="oc_responsible_unit"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">สถานะใบรับรอง</label>
                                <div id="oc_certificate_status"></div>
                                </div>

                                <div class="col-12">
                                <label class="form-label text-muted">เหตุผลออกใบรับรองชั่วคราว</label>
                                <div id="oc_temporary_reason"></div>
                                </div>
                                <div class="col-12">
                                <label class="form-label text-muted">หมายเหตุ</label>
                                <div id="oc_remark"></div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                        </div>
                    </div>
                    </div>

                    <!-- /modalviewOldModal -->
                              
                               
</div><!-- <div class="container-fluid"> -->

  
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