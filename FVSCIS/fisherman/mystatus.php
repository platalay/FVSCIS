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
                                                <?php if ($req->status === 'pending'): ?>
                                                    <button class="btn btn-primary btn-sm btn-confirm-date" 
                                                            data-id="<?= h($req->id) ?>" 
                                                            title="ยืนยันวันตรวจ">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </button>
                                                <?php endif; ?>
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
                    <!-- modalConfirmInspection date-->
                    <div class="modal fade" id="modalConfirmInspection" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <form id="confirmInspectionForm">
                            <div class="modal-header">
                            <h5 class="modal-title">ยืนยันวันตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                            <p><strong>วันที่นัดตรวจ:</strong> <span id="confirmedDateDisplay" class="text-primary"></span></p>
                            <input type="hidden" name="request_id" id="confirm_request_id">
                            <input type="hidden" name="original_confirmed_date" id="original_confirmed_date">
                            </div>
                            <div class="modal-footer">
                            <button id="btnSubmitConfirm" type="submit" class="btn btn-success">
                                ยืนยันเข้ารับการตรวจ
                            </button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div><!-- modalConfirmInspection date-->                   
                <!-- /.container-fluid -->                  
</div><!-- <div class="container-fluid"> -->

  
<?php include("../../private/shared/footeruser.php"); ?>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
                    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="../js/fvscis.js"></script>  
     
    <script>
    $(document).ready(function () {

        // เปิด modal และโหลดข้อมูล
        $('.btn-confirm-date').on('click', function () {
            const requestId = $(this).data('id');

            $.ajax({
                url: 'ajax/get_request_detail.php',
                method: 'POST',
                data: { request_id: requestId },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        const confirmedDate = res.data.confirmed_inspect_date;

                        $('#confirm_request_id').val(res.data.id);
                        $('#original_confirmed_date').val(confirmedDate);
                        $('#confirmedDateDisplay').text(confirmedDate && confirmedDate !== '0000-00-00' ? confirmedDate : 'ยังไม่กำหนด');

                        // ตรวจสอบและตั้งค่าปุ่มยืนยัน
                        if (!confirmedDate || confirmedDate === '0000-00-00') {
                            $('#btnSubmitConfirm')
                                .prop('disabled', true)
                                .addClass('btn-secondary')
                                .removeClass('btn-success')
                                .attr('title', 'ยังไม่มีการกำหนดวันนัดตรวจ');
                        } else {
                            $('#btnSubmitConfirm')
                                .prop('disabled', false)
                                .addClass('btn-success')
                                .removeClass('btn-secondary')
                                .removeAttr('title');
                        }

                        $('#modalConfirmInspection').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: res.message
                        });
                    }
                }
            });
        });

        // ส่งฟอร์มยืนยันวันตรวจ
        $('#confirmInspectionForm').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();

            $.post('ajax/confirm_by_fisherman.php', formData, function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: 'ยืนยันวันตรวจเรียบร้อยแล้ว',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        $('#modalConfirmInspection').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: res.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            }, 'json');
        });

    });
</script>


<?php include("../../private/shared/footerall.php"); ?>