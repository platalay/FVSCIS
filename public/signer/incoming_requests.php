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
                                        <th>ช่วงเวลาขอตรวจ</th>
                                        <th>วันที่ตรวจแล้วเสร็จ</th>
                                        <th>วันที่ยื่นคำขอ</th>
                                        <th>สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                // ✅ ดึงเฉพาะคำขอที่ department_group ตรงกับเจ้าหน้าที่
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

                                        // ==========================
                                        // กำหนดสีแถว (เหมือนตัวอย่าง)
                                        // ==========================
                                        $trClass   = '';
                                        $status    = $req->status;
                                        $hasDate   = !empty($req->confirmed_inspect_date) && $req->confirmed_inspect_date !== '0000-00-00';
                                        $isConfirm = (int)($req->is_confirm ?? 0);

                                        if ($status === InspectionRequest::STATUS_CONDITIONAL) {
                                            $trClass = 'tr-inspecting';    //ฟ้าอ่อน  
                                        }
                                        else if (
                                            $status === InspectionRequest::STATUS_COMPLETED
                                        ) {
                                            $trClass = 'tr-completed';              // เขียว (จบกระบวนการ)
                                        }
                                        else if ($status === InspectionRequest::STATUS_FAILED) {
                                            $trClass = 'tr-cancelled';              // แดงอ่อน
                                        }
                                        else if ($status === InspectionRequest::STATUS_PASSED) {
                                            $trClass = 'tr-pending-confirmed';              // เขียวอ่อน
                                        }
                                        
                                ?>
                                    <tr style="font-size: 14px;" class="<?= $trClass ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                            <?php if ($req->status === InspectionRequest::STATUS_FAILED): ?>
                                                <!-- ยืนยันผลไม่ผ่าน -->
                                                <button type="button" class="btn btn-danger btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalConfirmFail"
                                                        title="ยืนยันผลตรวจไม่ผ่าน"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>, 'fail')">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                </button>
                                            <?php else: ?>
                                                <!-- อนุมัติ -->
                                                <button type="button" class="btn btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalApproveRequest"
                                                        title="อนุมัติผลตรวจผ่าน"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>, 'approve')">
                                                <i class="fas fa-clipboard-check"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php 
                                                        $form = InspectionFormStatus::find_by_request_id($req->id);                                               
                                                        ?>
                                                        <a
                                                            href="generate_pdf.php?token=<?= h($form->document_token); ?>"
                                                            target="_blank"
                                                            class="btn btn-success btn-sm"
                                                            title="ฟอร์มตรวจ">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                            </div>
                                        </td>

                                        <td><?= h($req->ship_code) ?></td>
                                        <td><?= h($req->vessel_name) ?></td>
                                        <td><?= thai_date($req->inspect_date_start) . " ถึงวันที่ " . thai_date($req->inspect_date_end) ?></td>
                                        <td><?= thai_date($req->submitted_at) ?></td>
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
                                                    echo '<span class="badge bg-info text-dark">รออนุมัติ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_FAILED:
                                                    echo '<span class="badge bg-danger">ไม่ผ่านการตรวจ</span>';
                                                    break;
                                                case InspectionRequest::STATUS_CONDITIONAL:
                                                    echo '<span class="badge bg-info text-dark">ผ่านแบบมีเงื่อนไข</span>';
                                                    break;
                                                case InspectionRequest::STATUS_COMPLETED:
                                                    echo '<a href="certificate_preview.php?id=' . h($req->id) . '" target="_blank"
                                                            class="badge bg-success text-decoration-none">
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
                    <?php
                    include("modal/approve_request_modal.php");
                    include("modal/confirm_fail_request_modal.php");  
                    include("modal/logmodal.php");      
                    ?>
                               
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
    $(document).ready(function () {
    if (!$.fn.DataTable.isDataTable('#dataTable')) return;

    const table = $('#dataTable').DataTable();

    const params   = new URLSearchParams(window.location.search);
    const shipcode = params.get('shipcode');

    if (shipcode) {
        table.column(1).search(shipcode).draw();
    }
    });
    </script>

            <script>
                function loadRequestDetail(id, mode = 'approve') {
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

                    if (rawDate && rawDate !== '0000-00-00') {
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

                    if (mode === 'fail') {
                        $('#modalConfirmFailBody').html(html);
                        $('#fail_request_id').val(req.id);

                        // ปิดปุ่มถ้าไม่ใช่ failed
                        $('#btnConfirmFail').prop('disabled', req.status !== 'failed');

                        // (ไม่บังคับ) เคลียร์ค่าฟอร์ม
                        $('#effective_date_fail').val('');
                        $('#approval_note_fail').val('');
                    } else {
                        $('#modalApproveBody').html(html);
                        $('#approve_request_id').val(req.id);

                        // ปิดปุ่มอนุมัติถ้า completed
                        if (req.status === 'completed') {
                        $('#btnApproveRequest')
                            .prop('disabled', true)
                            .addClass('btn-secondary')
                            .removeClass('btn-success');
                        } else {
                        $('#btnApproveRequest')
                            .prop('disabled', false)
                            .addClass('btn-success')
                            .removeClass('btn-secondary');
                        }

                        // (ไม่บังคับ) เคลียร์ค่าฟอร์ม
                        $('#effective_date_approve').val('');
                        $('#approval_note_approve').val('');
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
                        effective_date: $('#effective_date_approve').val(),
                        temporary_reason: $('#temporary_reason').val(),
                        approval_note: $('#approval_note_approve').val()
                    };

                    $.ajax({
                        url: 'ajax/approve_request.php',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (res) {
                            if (res.success) {
                                Swal.fire(
                                    'สำเร็จ',
                                    'อนุมัติคำขอและออกใบรับรองเรียบร้อยแล้ว',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'ผิดพลาด',
                                    res.message || 'ไม่สามารถอนุมัติได้',
                                    'error'
                                );
                            }
                        },
                        error: function () {
                            Swal.fire(
                                'ผิดพลาด',
                                'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์',
                                'error'
                            );
                        }
                    });
                });
                </script>


                <script>
                $('#confirmFailForm').on('submit', function (e) {
                    e.preventDefault();

                    const formData = {
                        request_id: $('#fail_request_id').val(),
                        effective_date: $('#effective_date_fail').val(),
                        approval_note: $('#approval_note_fail').val()
                    };

                    $.ajax({
                        url: 'ajax/confirm_fail.php',
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (res) {
                            if (res.success) {
                                Swal.fire(
                                    'สำเร็จ',
                                    'ยืนยันผลไม่ผ่านเรียบร้อยแล้ว',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'ผิดพลาด',
                                    res.message || 'ไม่สามารถยืนยันผลไม่ผ่านได้',
                                    'error'
                                );
                            }
                        },
                        error: function () {
                            Swal.fire(
                                'ผิดพลาด',
                                'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์',
                                'error'
                            );
                        }
                    });
                });
                </script>


    <script>
    $(document).ready(function () {
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

<?php 
include("../../private/shared/footerall.php");
?>