<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$Officer = Officer::find_by_id($session->user_id());
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
                                    // ✅ ดึงเฉพาะคำขอที่ department_id ตรงกับเจ้าหน้าที่
                                    $DepartmentgroupObj = DepartmentGroup::find_one_by_officer_id($Officer->id);
                                    $requests = InspectionRequest::find_by_department_group_id($DepartmentgroupObj->id); 

                                    if (empty($requests)) :
                                    ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">ยังไม่มีคำขอตรวจเรือที่รับผิดชอบ</td>
                                        </tr>
                                    <?php
                                    else:
                                        foreach ($requests as $req) :
                                    ?>
                                        <tr style="font-size: 14px;">
                                            <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- ปุ่มดูรายละเอียด -->
                                                <button type="button" title="อนุมัติ" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalApproveRequest"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>)">
                                                    <i class="fas fa-file-signature"></i>
                                                </button>
                                            </div>
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

                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    </div>
                    <!-- modalApproveRequest -->
                    <div class="modal fade" id="modalApproveRequest" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <form id="approveInspectionForm">
                            <div class="modal-header">
                            <h5 class="modal-title">อนุมัติคำขอตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="modalApproveBody">
                            <!-- ✅ รายละเอียดคำขอจะโหลดมาใส่ตรงนี้โดย JS -->
                            </div>
                            <div class="modal-footer flex-column align-items-stretch">
                            <input type="hidden" name="request_id" id="approve_request_id">

                            <div class="mb-3">
                                <label for="approval_note" class="form-label">หมายเหตุ (ถ้ามี)</label>
                                <textarea class="form-control" name="approval_note" id="approval_note" rows="2"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100" id="btnApproveRequest">
                                ✅ อนุมัติคำขอและออกใบรับรอง
                            </button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /modalApproveRequest -->
                              
                               
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
                function loadRequestDetail(id) {
                    $.ajax({
                        url: 'ajax/get_request_detail.php',
                        method: 'GET',
                        data: { id: id },
                        dataType: 'json',
                        success: function (data) {
                            if (!data.success) {
                                Swal.fire('ผิดพลาด', data.message, 'error');
                                return;
                            }

                            const req = data.request;
                            const statusMap = {
                                pending: 'รอดำเนินการ',
                                cancelled: 'ยกเลิก',
                                inspecting: 'อยู่ระหว่างตรวจ',
                                passed: 'ผ่านการตรวจ',
                                failed: 'ไม่ผ่านการตรวจ',
                                conditional: 'ผ่านแบบมีเงื่อนไข',
                                completed: 'อนุมัติ'
                            };

                            let html = `
                                <p><strong>ชื่อเรือ:</strong> ${req.ship_name || '-'}</p>
                                <p><strong>ทะเบียนเรือ:</strong> ${req.ship_code || '-'}</p>
                                <p><strong>ช่วงวันที่ขอตรวจ:</strong> ${req.inspect_date_start} ถึง ${req.inspect_date_end}</p>
                                <p><strong>ใบอนุญาตท่า:</strong> ${req.port_license_no || '-'}</p>
                                <p><strong>สถานะ:</strong> ${statusMap[req.status] || 'ไม่ทราบ'}</p>
                            `;

                            const rawDate = req.actual_inspect_date ? req.actual_inspect_date.split(' ')[0] : null;
                            let displayDate = 'ไม่พบวันที่';

                            if (rawDate  && rawDate !== '0000-00-00') {
                                const parsed = new Date(rawDate);
                                if (!isNaN(parsed)) {
                                    displayDate = parsed.toLocaleDateString('th-TH', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    });
                                }
                            }

                            html += `<p><strong>วันที่ตรวจแล้วเสร็จ:</strong> <span class="text-success"><i class="fas fa-calendar-check"></i> ${displayDate}</span></p>`;

                            // วันที่อนุมัติ
                            const rawApproveDate = req.approved_at ? req.approved_at.split(' ')[0] : null;
                            let displayApproveDate = 'ยังไม่อนุมัติ';

                            if (rawApproveDate && rawApproveDate !== '0000-00-00') {
                                const parsed = new Date(rawApproveDate);
                                if (!isNaN(parsed)) {
                                    displayApproveDate = parsed.toLocaleDateString('th-TH', {
                                        day: '2-digit',
                                        month: '2-digit',
                                        year: 'numeric'
                                    });
                                }
                            }
                            html += `<p><strong>วันที่อนุมัติ:</strong> <span class="text-primary"><i class="fas fa-user-check"></i> ${displayApproveDate}</span></p>`;

                            $('#modalApproveBody').html(html);
                            $('#approve_request_id').val(req.id);
                            // ✅ ปิดปุ่มอนุมัติถ้าสถานะเป็น completed
                            if (req.status === 'completed') {
                                $('#btnApproveRequest').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');
                            } else {
                                $('#btnApproveRequest').prop('disabled', false).addClass('btn-success').removeClass('btn-secondary');
                            }

                        },
                        error: function () {
                            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                        }
                    });
                }
                </script>


                <script>
                            $('#approveInspectionForm').on('submit', function (e) {
                    e.preventDefault();

                    const formData = {
                        request_id: $('#approve_request_id').val(),
                        approval_note: $('#approval_note').val()
                    };

                    $.ajax({
                        url: 'ajax/approve_request.php',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (res) {
                            if (res.success) {
                                Swal.fire('สำเร็จ', 'อนุมัติคำขอและออกใบรับรองเรียบร้อยแล้ว', 'success')
                                    .then(() => {
                                        location.reload(); // หรือจะปิด modal แล้วรีเฟรชเฉพาะตารางก็ได้
                                    });
                            } else {
                                Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถอนุมัติได้', 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์', 'error');
                        }
                    });
                });
                </script>

<?php 
include("../../private/shared/footerall.php");
?>