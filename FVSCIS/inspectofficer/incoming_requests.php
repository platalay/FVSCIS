<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
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
                                        <th>หมายเลขทะเบียนเรือ</th>
                                        <th>ชื่อเรือ</th>
                                        <th>ช่วงเวลาขอตรวจ</th>
                                        <th></th>
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
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalRequestDetail"
                                                        onclick="loadRequestDetail(<?= h($req->id) ?>)">
                                                    <i class="fas fa-search"></i>
                                                </button>

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
                                            <td><?= thai_date($req->inspect_date_start). " ถึงวันที่ ".thai_date($req->inspect_date_end) ?></td>
                                            <td></td>
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
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    </div>
                    <!-- modalRequestDetail -->   
                    <div class="modal fade" id="modalRequestDetail" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <form id="confirmInspectionForm">
                            <div class="modal-header">
                            <h5 class="modal-title">รายละเอียดคำขอตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="modalRequestBody">
                            <!-- 📌 JS จะโหลดข้อมูลมาใส่ตรงนี้ -->
                            </div>
                            <div class="modal-footer">
                            <input type="hidden" name="request_id" id="confirm_request_id">
                            <input type="hidden" name="original_confirmed_date" id="original_confirmed_date">
                            <div class="row g-2 align-items-center mb-3">
                            <div class="col-auto">
                                <label for="confirmed_date" class="col-form-label text-secondary fw-semibold">
                                กรุณาเลือกวันนัดตรวจ
                                </label>
                            </div>
                            <div class="col">
                                <input type="date" id="confirmed_date" name="confirmed_date" class="form-control" required>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary" id="btnConfirmDate">
                                ยืนยัน
                                </button>
                            </div>
                            </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- modalRequestDetail -->                               
                               
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
                                <p><strong>ชื่อเรือ:</strong> ${req.vessel_name || '-'} <strong>   ทะเบียนเรือ:</strong> ${req.ship_code || '-'}</p>
                                <p><strong>ชื่อเจ้าของเรือ:</strong> ${req.owner_name || '-'} <strong>   หมายเลขติดต่อ:</strong> ${req.contact_phone || '-'} </p>
                                <p><strong>ช่วงวันที่ขอตรวจ:</strong> ${req.inspect_date_start} ถึง ${req.inspect_date_end}</p>
                                <p><strong>ชื่อท่าเทียบเรือประมงที่ใช้สำหรับการตรวจ:</strong> ${req.port_name || '-'} <strong>   ทะเบียนท่าเทียบเรือประมง:</strong> ${req.port_license_no || '-'} </p>
                                <p><strong>ตำบล:</strong> ${req.port_tambon || '-'} <strong>  อำเภอ:</strong> ${req.port_amphur || '-'} <strong>  จังหวัด:</strong> ${req.port_province || '-'}</p>
                                <p><strong>สถานะ:</strong> ${statusMap[req.status] || 'ไม่ทราบ'}`;
                                

                            if (req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00') {
                                const displayDate = new Date(req.confirmed_inspect_date).toLocaleDateString('th-TH', {
                                    day: '2-digit',
                                    month: '2-digit',
                                    year: 'numeric'
                                });
                                html += `<br><span class="text-success"><i class="fas fa-calendar-check"></i> มีการนัดหมายวันที่ ${displayDate} แล้ว</span>`;
                            } else {
                                html += `<br><span class="text-danger"><i class="fas fa-exclamation-circle"></i> ยังไม่มีการนัดหมายวันตรวจ</span>`;
                            }

                            html += `</p>`;
                            if (req.is_confirm == 1){
                                html += `<p><span class="text-success"><i class="fas fa-calendar-check"></i> ผู้ขอตรวจยืนยันวันนัดตรวจแล้ว</span></p>`;
                                $('#btnConfirmDate').prop('disabled', true);
                            } else {
                                html += `<p><span class="text-danger"><i class="fas fa-calendar-check"></i> ยังไม่มีการยืนยันวันนัดตรวจ</span></p>`;
                            }

                            $('#modalRequestBody').html(html);

                            // ✅ รอ DOM render ก่อนค่อย set ค่าใน input
                            setTimeout(() => {
                                $('#confirm_request_id').val(req.id);
                                $('input[name="confirmed_date"]').attr('min', req.inspect_date_start);
                                $('input[name="confirmed_date"]').val(
                                    req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00'
                                        ? req.confirmed_inspect_date
                                        : ''
                                );
                                $('input[name="original_confirmed_date"]').val(
                                    req.confirmed_inspect_date && req.confirmed_inspect_date !== '0000-00-00'
                                        ? req.confirmed_inspect_date
                                        : ''
                                );
                            }, 100); // ให้เวลา render 100 มิลลิวินาที
                        },
                        error: function () {
                            Swal.fire('ผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
                        }
                    });
                }
                </script>


            <script>
            $(document).ready(function () {
            // กันเลือกวันย้อนหลัง (ถ้าอยากบังคับ)
            const today = new Date().toISOString().split('T')[0];
            $('#confirmed_date').attr('min', today);

            $('#btnConfirmDate').on('click', function () {
                const $form = $('#confirmInspectionForm');
                const $date = $('#confirmed_date');
                const val = $date.val();

                // 1) ตรวจว่ามีค่ามั้ย
                if (!val) {
                // ให้ browser แสดง bubble เตือน และโฟกัสช่อง
                $date[0].reportValidity();
                $date.addClass('is-invalid');                 // (ถ้าใช้ Bootstrap)
                return;
                } else {
                $date.removeClass('is-invalid');
                }

                // 2) (ทางเลือก) ตรวจ format YYYY-MM-DD เผื่อ browser แปลก ๆ
                if (!/^\d{4}-\d{2}-\d{2}$/.test(val)) {
                Swal.fire({ icon: 'warning', title: 'รูปแบบวันที่ไม่ถูกต้อง', text: 'กรุณาเลือกวันที่จากปฏิทินอีกครั้ง' });
                $date.focus();
                return;
                }

                // 3) (ทางเลือก) ไม่ให้ย้อนหลัง
                if (val < today) {
                Swal.fire({ icon: 'warning', title: 'เลือกวันย้อนหลังไม่ได้', text: 'กรุณาเลือกตั้งแต่วันนี้เป็นต้นไป' });
                $date.focus();
                return;
                }

                // ✅ ผ่านแล้ว ค่อยส่ง AJAX
                const formData = $form.serialize();
                const $btn = $('#btnConfirmDate').prop('disabled', true);

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
                        showConfirmButton: false
                    }).then(() => location.reload());
                    } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: response.message });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'การเชื่อมต่อล้มเหลว', text: 'ไม่สามารถติดต่อ server ได้' });
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
                });
            });
            });
            </script>

<?php 
include("../../private/shared/footerall.php");
?>