<?php
require_once('../../private/initialize.php');
include("../../private/shared/headeruser.php");
include("../../private/shared/sidebaruser.php");
include("../../private/shared/topbaruser.php");
$fisherman=Fisherman::find_by_username($session->username);
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
                                    <th>ชื่อเรือ</th>
                                    <th>เลขที่ใบอนุญาต</th>
                                    <th>ประเภทเรือ</th>
                                    <th>ขนาดเรือ</th>
                                    <th>แรงม้า</th>
                                    <th>พื้นที่ทำการประมง</th>
                                    <th>วันทำการประมง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $my_vessels = Elicense::find_full_by_citizen_id($el_db, $fisherman->citizen_id);
                                    $index = 1;
                                    foreach ($my_vessels as $vessel) :
                                    ?>
                                    <tr style="font-size: 14px;">
                                        <td class="text-center">
                                        <!-- Trigger example: -->
                                        <form onsubmit="event.preventDefault(); openRequestModal('<?= h($vessel->ship_code) ?>');">
                                        <button type="submit" class="btn btn-success btn-sm" id="<?= h($vessel->ship_code) ?>">
                                            <i class="fas fa-clipboard-check"></i>
                                        </button>
                                        </form>

                                        </td>
                                        <td><?= h($vessel->ship_code) ?></td>
                                        <td><?= h($vessel->vessel_name) ?></td>
                                        <td><?= h($vessel->license_no) ?></td>
                                        <td><?= h($vessel->vessel_type) ?></td>
                                        <td><?= h($vessel->vessel_size) ?></td>
                                        <td><?= h($vessel->vessel_engine_power) ?> แรงม้า</td>
                                        <td><?= h($vessel->fishing_area) ?></td>
                                        <td><?= h($vessel->fishing_period) ?> (<?= h($vessel->fishing_period_amount) ?> วัน)</td>
                                    </tr>
                                    <?php endforeach; ?>
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
                                <label for="contact_phone" class="form-label">หมายเลขโทรศัพท์ที่ติดต่อได้</label>
                                <input type="text" name="contact_phone" id="contact_phone" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">หน่วยงานที่ยื่นคำขอ</label>
                                <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">-- เลือกหน่วยงาน --</option>
                                <!-- เติมด้วย PHP: Department::find_all() -->
                                </select>
                            </div>

                            <!-- วันที่ต้องการตรวจ -->
                            <div class="row mb-3">
                                <div class="col">
                                <label for="inspect_date_start" class="form-label">วันที่เริ่มต้องการตรวจ</label>
                                <input type="date" name="inspect_date_start" id="inspect_date_start" class="form-control" required>
                                </div>
                                <div class="col">
                                <label for="inspect_date_end" class="form-label">ถึงวันที่</label>
                                <input type="date" name="inspect_date_end" id="inspect_date_end" class="form-control" required>
                                </div>
                            </div>

                            <!-- เลือกจังหวัด อำเภอ ตำบล ท่าเรือ -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                <label for="port_province_id" class="form-label">จังหวัด</label>
                                <select name="port_province_id" id="port_province_id" class="form-select" required>
                                    <option value="">-- เลือกจังหวัด --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_amphur_id" class="form-label">อำเภอ</label>
                                <select name="port_amphur_id" id="port_amphur_id" class="form-select" required>
                                    <option value="">-- เลือกอำเภอ --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_tambon_id" class="form-label">ตำบล</label>
                                <select name="port_tambon_id" id="port_tambon_id" class="form-select" required>
                                    <option value="">-- เลือกตำบล --</option>
                                </select>
                                </div>
                                <div class="col-md-3">
                                <label for="port_license_no" class="form-label">ท่าเรือ</label>
                                <select name="port_license_no" id="port_license_no" class="form-select" required>
                                    <option value="">-- เลือกท่าเรือ --</option>
                                </select>
                                </div>
                            </div>

                            <!-- Checkbox ยืนยัน -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="confirm_agreement" id="confirm_agreement" required>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            
            <script>
            function openRequestModal(shipCode) {
            fetch('ajax/fetch_vessel_info.php?ship_code=' + encodeURIComponent(shipCode))
                .then(res => res.json())
                .then(data => {
                console.log(data); // DEBUG ดู response

                if (data.success === true) {  // ✅ ตรงกับ JSON จริง
                    document.getElementById('modal-ship-code').innerText = data.ship_code;
                    document.getElementById('modal-vessel-name').innerText = data.vessel_name;
                    document.getElementById('modal-vessel-ton').innerText = data.vessel_ton_gross + ' ตันกรอส';
                    document.getElementById('modal-fishing-area').innerText = data.fishing_area;
                    document.getElementById('hidden_ship_code').value = data.ship_code;

                    const modal = new bootstrap.Modal(document.getElementById('requestInspectionModal'));
                    modal.show();
                } else {
                    alert('ไม่พบข้อมูลเรือ');
                }
                })
                .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
                });
            }
            </script>
<?php include("../../private/shared/footerall.php"); ?>