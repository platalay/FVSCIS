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
                                        <th>ชื่อเรือ</th>
                                        <th>ช่วงวันที่ขอตรวจ</th>
                                        <th>วันนัดตรวจยืนยันแล้ว</th>
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
                                            $row_class = '';
                                            switch ($req->status) {
                                                case InspectionRequest::STATUS_COMPLETED:
                                                    $row_class = 'table-success';  // เขียว
                                                    break;
                                                case InspectionRequest::STATUS_CANCELLED:
                                                    $row_class = 'table-danger';  // แดง
                                                    break;
                                            }

                                    ?>
                                        <tr class="<?= $row_class ?>" style="font-size: 14px;">
                                            <td class="text-center">
                                                <?php
                                                $status = $req->status ?? '';
                                                $has_confirmed_date = !empty($req->confirmed_inspect_date);
                                                ?>

                                                <!-- ปุ่มดูรายละเอียด (ใช้ได้ทุกสถานะ) -->
                                                <button type="button"
                                                        class="btn btn-sm btn-info mb-1"
                                                        title="ดูรายละเอียดคำขอ"
                                                        onclick="openRequestDetail('<?= h($req->id) ?>');">
                                                    <i class="fas fa-search"></i>
                                                </button>

                                                <?php if ($status === 'pending' && !$has_confirmed_date): ?>
                                                    <!-- กรณียื่นแล้ว ยังไม่มีวันนัดตรวจ: แก้ไข + ยกเลิกได้ -->

                                                    <button type="button"
                                                            class="btn btn-sm btn-warning mb-1"
                                                            title="แก้ไขคำขอตรวจเรือ"
                                                            onclick="openRequestModal('<?= h($req->ship_code) ?>', 'edit', '<?= h($req->id) ?>');">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-request mb-1"
                                                            title="ยกเลิกคำขอ"
                                                            data-request-id="<?= h($req->id) ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                <?php elseif ($status === 'pending' && $has_confirmed_date): ?>
                                                    <!-- เจ้าหน้าที่กำหนดวันตรวจแล้ว แต่ชาวประมงยังไม่ยืนยัน -->

                                                    <button type="button"
                                                            class="btn btn-sm btn-success mb-1 btn-confirm-date"
                                                            title="ยืนยันวันตรวจเรือ"
                                                            data-request-id="<?= h($req->id) ?>">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </button>

                                                    <!-- ปุ่มแก้ไข (ปิดการใช้งาน) -->
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary mb-1"
                                                            title="ไม่สามารถแก้ไขข้อมูลหลักได้ เนื่องจากเจ้าหน้าที่กำหนดวันตรวจแล้ว"
                                                            disabled>
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <!-- ยังยกเลิกได้ -->
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-request mb-1"
                                                            title="ยกเลิกคำขอ"
                                                            data-request-id="<?= h($req->id) ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                <?php elseif ($status === 'inspecting'): ?>
                                                    <!-- อยู่ระหว่างตรวจ / ยืนยันวันตรวจแล้ว: ดูอย่างเดียว -->

                                                    <!-- (ดูรายละเอียดด้านบนมีแล้ว ไม่ต้องปุ่มเพิ่ม) -->

                                                <?php else: ?>
                                                    <!-- สถานะอื่น ๆ เช่น completed / cancelled / failed / conditional -->
                                                    <!-- แสดงแค่ปุ่มดูรายละเอียดด้านบน ก็เพียงพอ -->
                                                <?php endif; ?>
                                            </td>


                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= h($req->vessel_name) ?></td>
                                            <td><?= thai_date($req->inspect_date_start). " ถึงวันที่ ".thai_date($req->inspect_date_end) ?></td>
                                            <td>
                                            <?php  
                                            if($req->is_confirm){
                                                echo thai_date($req->confirmed_inspect_date);
                                            }    
                                            ?></td>
                                            <td><?= thai_date($req->created_at) ?></td>
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
                    <?php include("modal/confirmmodal.php"); ?> 
                    <?php include("modal/viewrequestmodal.php"); ?>               
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
        function openRequestDetail(requestId) {
            if (!requestId) {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่พบหมายเลขคำขอ', 'error');
                return;
            }

            $.ajax({
                url: 'ajax/get_view_request_detail.php',
                method: 'GET',
                dataType: 'json',
                data: { id: requestId },
                success: function (res) {
                    if (!res.success) {
                        Swal.fire('เกิดข้อผิดพลาด', res.message || 'ไม่สามารถดึงข้อมูลคำขอได้', 'error');
                        return;
                    }

                    const req = res.request || {};

                    // แปลงสถานะเป็น badge ภาษาไทย
                    const statusMap = {
                        pending:    '<span class="badge badge-warning">รอดำเนินการ</span>',
                        inspecting: '<span class="badge badge-primary">อยู่ระหว่างตรวจ</span>',
                        completed:  '<span class="badge badge-success">เสร็จสิ้น</span>',
                        cancelled:  '<span class="badge badge-secondary">ยกเลิกคำขอ</span>',
                        failed:     '<span class="badge badge-danger">ไม่ผ่านการตรวจ</span>',
                        conditional:'<span class="badge badge-info">ผ่านแบบมีเงื่อนไข</span>'
                    };

                    // ---------- ข้อมูลเรือ ----------
                    $('#detail-ship-code').text(req.ship_code || '-');
                    $('#detail-vessel-name').text(req.vessel_name || '-');
                    $('#detail-vessel-ton').text(req.vessel_ton_gross ? req.vessel_ton_gross + ' ตันกรอส' : '-');

                    // แปลง fishing_area เป็นคำไทยนิดนึง
                    let fishingAreaText = '-';
                    if (req.fishing_area === 'andaman') fishingAreaText = 'อันดามัน';
                    else if (req.fishing_area === 'gulf') fishingAreaText = 'อ่าวไทย';
                    else if (req.fishing_area) fishingAreaText = req.fishing_area;
                    $('#detail-fishing-area').text(fishingAreaText);

                    // ---------- ข้อมูลคำขอ ----------
                    
                    $('#detail-created-at').text(req.created_at || '-');
                    $('#detail-contact-phone').text(req.contact_phone || '-');

                    // รูปแบบการตรวจ
                    let inspectionTypeText = '-';
                    if (req.inspection_form_type === '2' || req.inspection_form_type === 2) {
                        inspectionTypeText = 'ตรวจเพื่อออกหนังสือรับรองส่งออกไปสหภาพยุโรป(แบบที่ 2)';
                    } else if (req.inspection_form_type === '1' || req.inspection_form_type === 1) {
                        inspectionTypeText = 'ตรวจทั่วไป (แบบที่ 1)';
                    }
                    $('#detail-inspection-type').text(inspectionTypeText);

                    // เรือห้องเย็น
                    let coldRoomText = (req.cold_room_flag === '1' || req.cold_room_flag === 1)
                        ? 'เป็นเรือห้องเย็น / มีระบบทำความเย็น'
                        : 'ไม่มีระบบห้องเย็น';
                    $('#detail-cold-room').text(coldRoomText);

                    // ---------- ท่าเรือและหน่วยงาน ----------
                    $('#detail-port-province').text(req.port_province_name || '-');

                    let amphurTambon = '-';
                    if (req.port_amphur_name || req.port_tambon_name) {
                        amphurTambon = (req.port_amphur_name || '') +
                                    (req.port_amphur_name && req.port_tambon_name ? ' / ' : '') +
                                    (req.port_tambon_name || '');
                    }
                    $('#detail-port-amphur-tambon').text(amphurTambon || '-');

                    $('#detail-port-name').text(req.port_name || req.port_license_no || '-');
                    $('#detail-department').text(req.department_name || '-');

                    // ---------- วันที่ ----------
                    let inspectRange = '-';
                    if (req.inspect_date_start || req.inspect_date_end) {
                        if (req.inspect_date_start === req.inspect_date_end) {
                            inspectRange = req.inspect_date_start;
                        } else {
                            inspectRange = (req.inspect_date_start || '-') +
                                        ' ถึงวันที่ ' +
                                        (req.inspect_date_end || '-');
                        }
                    }
                    $('#detail-inspect-range').text(inspectRange);

                    $('#detail-confirmed-inspect-date').text(
                        req.confirmed_inspect_date || '-'
                    );

                    // ---------- สถานะ ----------
                    $('#detail-status-badge').html(
                        statusMap[req.status] || '<span class="badge badge-light">ไม่ทราบสถานะ</span>'
                    );

                    // แสดง modal
                    $('#requestDetailModal').modal('show');
                },
                error: function () {
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }


    $(document).ready(function () {

        // เปิด modal และโหลดข้อมูล
        

        $('.btn-confirm-date').on('click', function () {
            const requestId = $(this).data('request-id');

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

        //ลบข้อมูล

        $(document).on('click', '.btn-delete-request', function (e) {
            e.preventDefault();

            const $btn = $(this);
            const id = $btn.data('request-id');
            if (!id) return;

            const ajaxDelete = () => $.ajax({
                url: 'ajax/delete_request.php',
                type: 'POST',
                dataType: 'json',
                data: { id },
                beforeSend() { $btn.prop('disabled', true).addClass('disabled'); },
            }).always(() => {
                $btn.prop('disabled', false).removeClass('disabled');
            });

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                title: 'ลบคำขอถาวร?',
                html: 'ข้อมูลที่เกี่ยวข้องทั้งหมดจะถูกลบและไม่สามารถกู้คืนได้',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบถาวร',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
                }).then((r) => {
                if (!r.isConfirmed) return;

                ajaxDelete().done((res) => {
                    if (res && res.success) {
                    Swal.fire({ icon: 'success', title: 'ลบแล้ว', timer: 1200, showConfirmButton: false });
                    loadNotificationCount();
                    const $row = $btn.closest('tr');
                    if ($.fn.DataTable && $('#dataTable').length) {
                        $('#dataTable').DataTable().row($row).remove().draw(false);
                    } else {
                        $row.remove();
                    }
                    } else {
                    Swal.fire({ icon: 'error', title: 'ลบไม่สำเร็จ', text: res?.message || '' });
                    }
                }).fail((xhr) => {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || xhr.statusText });
                });
                });
            } else {
                if (!confirm('ลบคำขอถาวร?')) return;
                ajaxDelete().done((res) => {
                if (res && res.success) {
                    const $row = $btn.closest('tr');
                    if ($.fn.DataTable && $('#dataTable').length) {
                    $('#dataTable').DataTable().row($row).remove().draw(false);
                    } else {
                    $row.remove();
                    }
                } else {
                    alert(res?.message || 'ลบไม่สำเร็จ');
                }
                }).fail(() => alert('เกิดข้อผิดพลาด'));
            }
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