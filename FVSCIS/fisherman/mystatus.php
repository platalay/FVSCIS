<?php
require_once('../../private/initialize.php');
$session->require_role(['fisherman']);
include("../../private/shared/headeruser.php");
include("../../private/shared/sidebaruser.php");
include("../../private/shared/topbaruser.php");
$fisherman=Fisherman::find_by_id($session->user_id());
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
                                        <th>เลขทะเบียนเรือ</th>
                                        <th>ใบอนุญาตท่า</th>
                                        <th>วันที่ขอเริ่มตรวจ</th>
                                        <th>วันที่ขอเสร็จ</th>
                                        <th>วันที่สร้าง</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $requests = InspectionRequest::find_by_created_by($session->user_id()); // ✅ ต้องเป็น array
                                    if (empty($requests)) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">ยังไม่มีคำขอตรวจเรือ</td>
                                        </tr>
                                    <?php
                                    else:
                                        foreach ($requests as $req) :
                                    ?>
                                        <tr style="font-size: 14px;">
                                            <td class="text-center">
                                                <a href="view_request.php?id=<?= h($req->id) ?>" class="btn btn-info btn-sm" title="ดูรายละเอียด">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </td>
                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= h($req->port_license_no) ?></td>
                                            <td><?= h($req->inspect_date_start) ?></td>
                                            <td><?= h($req->inspect_date_end) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($req->created_at)) ?></td>
                                            <td>
                                                <?php
                                                switch ($req->status) {
                                                    case InspectionRequest::STATUS_PENDING:
                                                        echo '<span class="badge bg-warning text-dark">รอดำเนินการ</span>';
                                                        break;
                                                    case InspectionRequest::STATUS_INSPECTING:
                                                        echo '<span class="badge bg-primary">กำลังตรวจ</span>';
                                                        break;
                                                    case InspectionRequest::STATUS_COMPLETED:
                                                        echo '<span class="badge bg-success">ตรวจเสร็จแล้ว</span>';
                                                        break;
                                                    case InspectionRequest::STATUS_CANCELLED:
                                                        echo '<span class="badge bg-danger">ยกเลิก</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">ไม่ทราบ</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                    <!-- Modal: Request Inspection -->
                    <div class="modal fade" id="requestInspectionModal" tabindex="-1" aria-labelledby="requestInspectionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form id="requestInspectionForm" method="post" action="request_inspection.php">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title" id="requestInspectionModalLabel">ยื่นคำขอตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                            <!-- รายละเอียดเรือ -->
                            <div class="mb-3">
                                <strong>เลขทะเบียนเรือ:</strong> <span id="modal-ship-code"></span><br>
                                <strong>ชื่อเรือ:</strong> <span id="modal-vessel-name"></span><br>
                                <strong>ขนาดตันกรอส:</strong> <span id="modal-vessel-ton"></span> ตัน<br>
                                <strong>พื้นที่ทำการประมง:</strong> <span id="modal-fishing-area"></span>
                            </div>

                            <!-- ข้อมูลผู้ยื่น -->
                            <div class="mb-3">
                                <input type="hidden" name="request[ship_code]" id="hidden_ship_code">
                                <label for="contact_phone" class="form-label">หมายเลขโทรศัพท์ที่ติดต่อได้</label>
                                <input type="text"
                                name="request[contact_phone]"
                                id="contact_phone"
                                class="form-control"
                                required
                                maxlength="10"
                                inputmode="numeric"
                                autocomplete="tel"
                                placeholder="เช่น 0891234567">
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">หน่วยงานที่ยื่นคำขอ</label>
                                <select name="request[department_id]" id="department_id" class="form-select" required>
                                <option value="">-- เลือกหน่วยงาน --</option>
                                    <?php 
                                        $Departments = Department::find_all();
                                        foreach ($Departments as $Department): ?>
                                            <option value="<?= $Department->id ?>" data-province-id="<?= $Department->province ?>">
                                                <?= $Department->name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                            </div>

                            <!-- วันที่ต้องการตรวจ -->
                            <div class="row mb-3">
                                <div class="col">
                                <label for="inspect_date_start" class="form-label">วันที่เริ่มต้องการตรวจ</label>
                                <input type="date" name="request[inspect_date_start]" id="inspect_date_start" class="form-control" required>
                                </div>
                                <div class="col">
                                <label for="inspect_date_end" class="form-label">ถึงวันที่</label>
                                <input type="date" name="request[inspect_date_end]" id="inspect_date_end" class="form-control" required>
                                </div>
                            </div>

                            <!-- เลือกจังหวัด อำเภอ ตำบล ท่าเรือ -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                <label for="port_province_id" class="form-label">จังหวัด</label>
                                <select name="request[port_province_id]" id="port_province_id" class="form-select" required>
                                    <option value="">-- เลือกจังหวัด --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_amphur_id" class="form-label">อำเภอ</label>
                                <select name="request[port_amphur_id]" id="port_amphur_id" class="form-select" required>
                                    <option value="">-- เลือกอำเภอ --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_tambon_id" class="form-label">ตำบล</label>
                                <select name="request[port_tambon_id]" id="port_tambon_id" class="form-select" required>
                                    <option value="">-- เลือกตำบล --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_license_no" class="form-label">ท่าเรือ</label>
                                <select name="request[port_license_no]" id="port_license_no" class="form-select" required>
                                    <option value="">-- เลือกท่าเรือ --</option>
                                </select>
                                </div>
                            </div>

                            <!-- Checkbox ยืนยัน -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="request[confirm_agreement]" id="confirm_agreement" required>
                                <label class="form-check-label" for="confirm_agreement">
                                ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้องและยินยอมให้ใช้ข้อมูลนี้ในการตรวจเรือ
                                </label>
                            </div>

                            <!-- Hidden ship code -->
                            <input type="hidden" name="ship_code" id="hidden_ship_code">
                            </div>

                            <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">ยื่นคำขอ</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            </div>
                        </div>
                        </form>
                    </div>
                    </div> <!-- Modal: Request Inspection -->                   
                <!-- /.container-fluid -->                  
</div><!-- <div class="container-fluid"> -->

  
<?php include("../../private/shared/footeruser.php"); ?>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
                    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
            $(document).ready(function () {
                // ✅ เริ่มต้น DataTable
                var table = $('#dataTable').DataTable();

                // ✅ ช่องค้นหาด้านบน
                $('#topSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
            });
            </script> 
     
<?php include("../../private/shared/footerall.php"); ?>