<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
$department = Department::find_by_id($Officer->departments_id);
$departmentgroup = DepartmentGroup::find_by_id($department->parent_department);
$evaluation_agency = $department->name;
$signing_unit = $departmentgroup->name;
$ownerobj = DepartmentGroup::find_by_id($departmentgroup->responsible_unit);
$responsible_unit = $ownerobj->name;
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
                                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalFvscisOldAdd">
                                <i class="fas fa-plus"></i> เพิ่มข้อมูล
                                </button>
                                


                            </h6>
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
                                    // $DepartmentgroupObj = DepartmentGroup::find_one_by_officer_id($Officer->id);
                                    $FvSanitationCertificationOlds = FvSanitationCertificationOld::find_all_by_evaluation_agency($Officer->departments_id); 

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
                                                <button type="button" title="ดูข้อมูลเก่า"
                                                        class="btn btn-primary btn-sm btn-edit-fvscisold"
                                                        data-id="<?= h($req->id) ?>">
                                                <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" title="ลบข้อมูลเก่า"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="deleteOldCertification(<?= h($req->id) ?>, this)">
                                                <i class="fas fa-trash"></i>
                                                </button>

                                            </td>
                                            <td><?= h($req->vessel_name) ?></td>
                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= thai_date($req->request_date) ?></td>
                                            <td><?= thai_date($req->effective_date) ?></td>
                                            <td><?= thai_date($req->expiration_date) ?></td>
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

                    <!-- Modal: Add FvSanitationCertificationOld -->
                    <div class="modal fade" id="modalFvscisOldAdd" tabindex="-1" aria-labelledby="modalFvscisOldAddLabel" aria-hidden="true">
                    <!-- ✅ เลื่อนเฉพาะ body และเต็มจอเมื่อจอเล็ก -->
                    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
                        <div class="modal-content">
                        <form id="form-fvscisold-add" autocomplete="off">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalFvscisOldAddLabel">เพิ่มข้อมูลใบรับรอง(manual)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>

                            <!-- ✅ เผื่อ fallback ใส่ style overflow:auto ด้วย (ไม่รบกวน CSS ข้างบน) -->
                            <div class="modal-body" style="overflow-y:auto;">
                            <div class="row g-3">

                                <!-- 2 : ship_code + ปุ่มค้นหา -->
                                <div class="col-md-3">
                                <label class="form-label">รหัสเรือ</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="FvSanitationCertificationOld[ship_code]" id="fv-ship-code" required>
                                    <button class="btn btn-outline-secondary" type="button" id="btnLookupShip">
                                    <span class="d-inline" id="btnText">ค้นหา</span>
                                    <span class="spinner-border spinner-border-sm d-none" id="btnSpin" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                </div>

                                <!-- 1 -->
                                <div class="col-md-3">
                                <label class="form-label">ชื่อเรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[vessel_name]" id="fv-vessel-name" required>
                                </div>

                                <!-- 3 -->
                                <div class="col-md-3">
                                <label class="form-label">หมายเลข/สัญลักษณ์เรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[vessel_mark]" id="fv-vessel-mark">
                                </div>

                                <!-- 4 -->
                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบอนุญาต</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[license_number]" id="fv-license-number">
                                </div>

                                <!-- 5 -->
                                <div class="col-md-3">
                                <label class="form-label">ชนิดเครื่องมือทำการประมง</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[gear_type]" id="fv-gear-type">
                                </div>

                                <!-- 6 -->
                                <div class="col-md-3">
                                <label class="form-label">ชื่อเจ้าของเรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[owner_name]" id="fv-owner-name">
                                </div>

                                <!-- 7 -->
                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบรับรอง</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[certificate_number]" required>
                                </div>

                                <!-- วันที่ -->
                                <div class="col-md-3">
                                <label class="form-label">วันที่ยื่นคำขอ</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[request_date]">
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันที่ลงนาม</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[signature_date]">
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันที่มีผล</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[effective_date]" required>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันหมดอายุ</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[expiration_date]" required>
                                </div>

                                <!-- ซ่อนค่าอ้างอิง -->
                                <input type="hidden" name="FvSanitationCertificationOld[evaluation_agency]" value="<?= h($Officer->departments_id ?? '') ?>">
                                <input type="hidden" name="FvSanitationCertificationOld[signing_unit]"        value="<?= h($department->parent_department ?? '') ?>">
                                <input type="hidden" name="FvSanitationCertificationOld[responsible_unit]"   value="<?= h($departmentgroup->responsible_unit ?? '') ?>">

                                <!-- สถานะ -->
                                <div class="col-md-3">
                                <label class="form-label">สถานะใบรับรอง (certificate_status)</label>
                                <select class="form-select" name="FvSanitationCertificationOld[certificate_status]" required>
                                    <option value="" selected disabled>-- เลือกสถานะ --</option>
                                    <option value="สร. 3">สร. 3</option>
                                    <option value="สร. 3 ชั่วคราว">สร. 3 ชั่วคราว</option>
                                    <option value="ไม่ผ่าน">ไม่ผ่าน</option>
                                </select>
                                </div>

                                <!-- อื่น ๆ -->
                                <div class="col-md-6">
                                <label class="form-label">เหตุผลชั่วคราว</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[temporary_reason]">
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">หมายเหตุ (remark)</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[remark]">
                                </div>

                                <!-- แสดงผลในโมดัล -->
                                <?php
                                $eval = $evaluation_agency ?? '';
                                $sign = $signing_unit ?? '';
                                $resp = $responsible_unit ?? '';
                                ?>
                                <div class="col-12">
                                <div class="border rounded p-3 mb-2 bg-light">
                                    <div class="row g-2 small">
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยประเมิน</div>
                                        <div><?= $eval !== '' ? h($eval) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยลงนาม</div>
                                        <div><?= $sign !== '' ? h($sign) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยงานรับผิดชอบ</div>
                                        <div><?= $resp !== '' ? h($resp) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    </div>
                                </div>
                                </div>

                            </div><!-- /.row -->
                            </div><!-- /.modal-body -->

                            <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /Modal -->   
                     
                    <!-- Modal: Edit FvSanitationCertificationOld -->
                    <div class="modal fade" id="modalFvscisOldEdit" tabindex="-1"
                        aria-labelledby="modalFvscisOldEditLabel" aria-hidden="true">
                    <!-- เลื่อนเฉพาะ body + fullscreen เมื่อ lg-down -->
                    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
                        <div class="modal-content">
                        <form id="form-fvscisold-edit" autocomplete="off">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalFvscisOldEditLabel">แก้ไขข้อมูลใบรับรอง (เก่า)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>

                            <div class="modal-body">
                            <input type="hidden" name="FvSanitationCertificationOld[id]" id="edit-id">

                            <div class="row g-3">
                                <div class="col-md-3">
                                <label class="form-label">รหัสเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[ship_code]" id="edit-ship-code" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_name]" id="edit-vessel-name" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">หมายเลข/สัญลักษณ์เรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_mark]" id="edit-vessel-mark">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบอนุญาต</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[license_number]" id="edit-license-number">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชนิดเครื่องมือทำการประมง</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[gear_type]" id="edit-gear-type">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเจ้าของเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[owner_name]" id="edit-owner-name">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบรับรอง</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[certificate_number]" id="edit-certificate-number" required>
                                </div>

                                <!-- วันที่ -->
                                <div class="col-md-3">
                                <label class="form-label">วันที่ยื่นคำขอ</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[request_date]" id="edit-request-date">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันที่ลงนาม</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[signature_date]" id="edit-signature-date">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันที่มีผล</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[effective_date]" id="edit-effective-date" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันหมดอายุ</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[expiration_date]" id="edit-expiration-date" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">สถานะใบรับรอง</label>
                                <select class="form-select"
                                        name="FvSanitationCertificationOld[certificate_status]" id="edit-certificate-status" required>
                                    <option value="" disabled>-- เลือกสถานะ --</option>
                                    <option value="สร. 3">สร. 3</option>
                                    <option value="สร. 3 ชั่วคราว">สร. 3 ชั่วคราว</option>
                                    <option value="ไม่ผ่าน">ไม่ผ่าน</option>
                                </select>
                                </div>

                                <!-- Hidden (หน่วยงาน จาก record เดิม) -->
                                <input type="hidden" name="FvSanitationCertificationOld[evaluation_agency]" id="edit-evaluation-agency">
                                <input type="hidden" name="FvSanitationCertificationOld[signing_unit]" id="edit-signing-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[responsible_unit]" id="edit-responsible-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[type]" id="edit-type" value="0">

                                <!-- สรุปหน่วยงาน -->
                                <?php
                                $eval  = $evaluation_agency  ?? '';
                                $sign  = $signing_unit       ?? '';
                                $resp  = $responsible_unit   ?? '';
                                ?>
                                <div class="col-12">
                                <div class="border rounded p-3 mb-2 bg-light">
                                    <div class="row g-2 small">
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยประเมิน</div>
                                        <div><?= $eval !== '' ? h($eval) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยลงนาม</div>
                                        <div><?= $sign !== '' ? h($sign) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยงานรับผิดชอบ</div>
                                        <div><?= $resp !== '' ? h($resp) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    </div>
                                </div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">เหตุผลชั่วคราว</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[temporary_reason]" id="edit-temporary-reason">
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">หมายเหตุ (remark)</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[remark]" id="edit-remark">
                                </div>
                            </div><!-- /.row -->
                            </div><!-- /.modal-body -->

                            <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /Modal -->
                               
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
    $(function () {
    // ใช้อินสแตนซ์เดิมถ้ามีอยู่แล้ว ไม่รีอินิท
    var table = $.fn.dataTable.isDataTable('#dataTable')
        ? $('#dataTable').DataTable()
        : $('#dataTable').DataTable({
            language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)"
            }
        });

    // สร้าง badge แสดงจำนวนไว้ข้างช่องค้นหา
    var $filter = $('#dataTable_wrapper .dataTables_filter');
    if ($('#totalCount').length === 0) {
        $filter.prepend('<span id="totalCount" class="badge bg-info me-2 mb-2 mb-md-0"></span>');
    }

    function updateCount() {
        var info = table.page.info(); // {recordsTotal, recordsDisplay, ...}
        var total = info.recordsTotal;
        var display = info.recordsDisplay;

        var text = 'ทั้งหมด ' + total.toLocaleString('th-TH') + ' รายการ';
        if (display !== total) {
        text += ' (กำลังแสดง ' + display.toLocaleString('th-TH') + ')';
        }
        $('#totalCount').text(text);
    }

    updateCount();
    table.on('draw.dt', updateCount);
    });
    </script>
 
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


        // ajax search ship code
        <script>
        (function() {
        function setBusy(isBusy) {
            $('#btnLookupShip').prop('disabled', isBusy);
            $('#btnText').toggleClass('d-none', isBusy);
            $('#btnSpin').toggleClass('d-none', !isBusy);
        }

        function fillFromElicense(data) {
            // map ค่าไปยังฟิลด์ในฟอร์ม
            $('#fv-vessel-name').val(data.vessel_name || '');
            $('#fv-license-number').val(data.license_no || '');
            $('#fv-owner-name').val(data.display_name || '');
            $('#fv-gear-type').val(data.geartype || '');
            $('#fv-vessel-mark').val(data.fishing_mark || '');
        }

        function lookupShip() {
            const shipCode = ($('#fv-ship-code').val() || '').trim();
            if (!shipCode) {
            Swal.fire({ icon: 'warning', title: 'กรุณากรอกรหัสเรือ (ship_code)' });
            return;
            }
            setBusy(true);

            $.ajax({
            url: 'ajax/get_elicense_by_ship_code.php',
            type: 'POST',
            dataType: 'json',
            data: { ship_code: shipCode }, // จะส่ง fishery_year เพิ่มก็ได้ เช่น 2567
            success: function(res) {
                if (res && res.success && res.data) {
                fillFromElicense(res.data);
                Swal.fire({ icon: 'success', title: 'ดึงข้อมูลสำเร็จ', timer: 900, showConfirmButton: false });
                } else {
                Swal.fire({ icon: 'error', title: 'ไม่พบข้อมูล', text: res?.message || 'ตรวจสอบรหัสเรืออีกครั้ง' });
                }
            },
            error: function(xhr) {
                Swal.fire({ icon: 'error', title: 'เชื่อมต่อไม่ได้', text: xhr.responseText || 'โปรดลองใหม่' });
            },
            complete: function() { setBusy(false); }
            });
        }

        // คลิกปุ่มค้นหา
        $(document).on('click', '#btnLookupShip', lookupShip);

        // กด Enter ในช่อง ship_code ให้ค้นหา
        $(document).on('keydown', '#fv-ship-code', function(e) {
            if (e.key === 'Enter') {
            e.preventDefault();
            lookupShip();
            }
        });
        })();
        </script>



        // ajax เพิ่มข้อมูล
        <script>
        // ส่งฟอร์มด้วย AJAX
        $('#form-fvscisold-add').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/create_fvscisold.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1200, showConfirmButton: false })
                .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: res.message || 'กรุณาลองใหม่' });
            }
            },
            error: function(xhr) {
            Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: xhr.responseText || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์' });
            }
        });
        });
        </script>

        // update
        <script>
        (function(){
        function fillAgencyDisplay(evalTxt, signTxt, respTxt){
            $('#show-eval').text(evalTxt || 'ไม่ระบุ').toggleClass('text-muted', !evalTxt);
            $('#show-sign').text(signTxt || 'ไม่ระบุ').toggleClass('text-muted', !signTxt);
            $('#show-resp').text(respTxt || 'ไม่ระบุ').toggleClass('text-muted', !respTxt);
        }

        // เปิดโมดัลแก้ไข + โหลดข้อมูลจาก find_by_id()
        $(document).on('click', '.btn-edit-fvscisold', function(){
            const id = $(this).data('id');
            if(!id) return;

            // เคลียร์ฟอร์ม
            $('#form-fvscisold-edit')[0].reset();
            $('#edit-id').val(id);

            $.ajax({
            url: 'ajax/get_fvscisold.php',
            type: 'POST',
            dataType: 'json',
            data: { id: id },
            success: function(res){
                if(res && res.success && res.data){
                const d = res.data;

                // กำหนดค่า field จาก record ที่ได้มา (ทุกฟิลด์)
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
                //fillAgencyDisplay(d.evaluation_agency, d.signing_unit, d.responsible_unit);

                new bootstrap.Modal(document.getElementById('modalFvscisOldEdit')).show();
                }else{
                Swal.fire({icon:'error', title:'ไม่พบข้อมูล', text: res?.message || ''});
                }
            },
            error: function(xhr){
                Swal.fire({icon:'error', title:'เชื่อมต่อไม่ได้', text: xhr.responseText || 'โปรดลองใหม่'});
            }
            });
        });

        // Submit อัปเดต
        $('#form-fvscisold-edit').on('submit', function(e){
            e.preventDefault();
            const $btn = $(this).find('button[type=submit]').prop('disabled', true);

            $.ajax({
            url: 'ajax/update_fvscisold.php',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(res){
                if(res.success){
                Swal.fire({icon:'success', title:'บันทึกการแก้ไขแล้ว', timer:900, showConfirmButton:false})
                    .then(()=> location.reload()); // หรืออัปเดตแถว DataTable แทน
                }else{
                Swal.fire({icon:'error', title:'แก้ไขไม่สำเร็จ', text: res.message || ''});
                }
            },
            error: function(xhr){
                Swal.fire({icon:'error', title:'เชื่อมต่อไม่ได้', text: xhr.responseText || 'โปรดลองใหม่'});
            },
            complete: function(){ $btn.prop('disabled', false); }
            });
        });
        })();
        </script>

        <script>
            function deleteOldCertification(id, btn){
            if(!id) return;

            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: 'ลบแล้วไม่สามารถกู้คืนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก'
            }).then(function(result){
                if(!result.isConfirmed) return;

                // กันกดซ้ำ
                const $btn = $(btn).prop('disabled', true);

                $.ajax({
                url: 'ajax/delete_fvscisold.php',
                type: 'POST',
                dataType: 'json',
                data: { id: id }, // ถ้ามี CSRF ให้ส่ง token มาด้วย
                success: function(res){
                    if(res && res.success){
                    // เอาแถวออกจากตาราง (รองรับทั้ง DataTable และ table ธรรมดา)
                    const $tr = $($btn).closest('tr');
                    const dt = $.fn.DataTable && $('#dataTable').data('DataTable') || $('#dataTable').DataTable?.();
                    if(dt){
                        dt.row($tr).remove().draw(false);
                    }else{
                        $tr.remove();
                    }
                    Swal.fire({icon:'success', title:'ลบแล้ว', timer:900, showConfirmButton:false});
                    }else{
                    $btn.prop('disabled', false);
                    Swal.fire({icon:'error', title:'ลบไม่สำเร็จ', text: res?.message || ''});
                    }
                },
                error: function(xhr){
                    $btn.prop('disabled', false);
                    Swal.fire({icon:'error', title:'เชื่อมต่อไม่ได้', text: xhr.responseText || 'โปรดลองใหม่'});
                }
                });
            });
            }
            </script>

                                          

<?php 
include("../../private/shared/footerall.php");
?>