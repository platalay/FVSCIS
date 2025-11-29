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
                                        <th>ยื่นขอต่อหน่วยงาน</th>
                                        <th>ท่าเรือ</th>
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
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top"
                                                        title="ดูรายละเอียดคำขอ"
                                                        onclick="openRequestDetail('<?= h($req->id) ?>');">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-primary mb-1 btn-print-form1"
                                                        data-id="<?= h($req->id) ?>"
                                                        data-toggle="tooltip"
                                                        data-placement="top"
                                                        title="พิมพ์แบบ สร.1">
                                                    <i class="fas fa-print"></i>
                                                </button>

                                                <?php if ($status === 'pending' && !$has_confirmed_date): ?>
                                                    <!-- กรณียื่นแล้ว ยังไม่มีวันนัดตรวจ: แก้ไข + ยกเลิกได้ -->

                                                    <button type="button"
                                                            class="btn btn-sm btn-warning mb-1"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="แก้ไขคำขอตรวจเรือ"
                                                            
                                                            onclick="openEditInspectionModal('<?= h($req->id) ?>');">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-request mb-1"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="ยกเลิกคำขอ"
                                                            
                                                            data-request-id="<?= h($req->id) ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                <?php elseif ($status === 'pending' && $has_confirmed_date): ?>
                                                    <!-- เจ้าหน้าที่กำหนดวันตรวจแล้ว แต่ชาวประมงยังไม่ยืนยัน -->
                                                    
                                                    <button type="button"
                                                            class="btn btn-sm btn-success mb-1 btn-confirm-date"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="ยืนยันวันตรวจเรือ"
                                                            
                                                            data-request-id="<?= h($req->id) ?>">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </button>

                                                    <!-- ปุ่มแก้ไข (ปิดการใช้งาน) -->
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary mb-1"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="ไม่สามารถแก้ไขข้อมูลหลักได้ เนื่องจากเจ้าหน้าที่กำหนดวันตรวจแล้ว"
                                                            
                                                            disabled>
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <!-- ยังยกเลิกได้ -->
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-request mb-1"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
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
                                            <?php $department = Department::find_by_id($req->department_id); ?>
                                            <td><?= h($department->name) ?></td>
                                            <td><?= h($req->port_name) ?></td>
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
                                                <button class="btn btn-link p-0 text-muted btn-log"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top"
                                                        title="ดูประวัติ"
                                                        data-request-id="<?= h($req->id) ?>"
                                                        data-vessel="<?php echo h($req->vessel_name); ?>">
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
                    <?php include("modal/confirmmodal.php"); ?> 
                    <?php include("modal/viewrequestmodal.php"); ?>     
                    <?php include("modal/editrequestmodal.php"); ?> 
                    <?php include("modal/logmodal.php"); ?> 
                    <?php include("modal/fvs01modal.php"); ?>               
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

<script>

// ====================================================================
// 1) ฟังก์ชันโหลดข้อมูลจังหวัด → อำเภอ → ตำบล → ท่าเรือ
// ====================================================================
//โหลดจังหวัด
function loadEditProvinces(selected_province_id = "") {
  $.ajax({
    url: 'ajax/get_provinces.php',
    method: 'GET',
    dataType: 'json',
    success: function(res) {
      let html = '<option value="">-- เลือกจังหวัด --</option>';
      res.forEach(function(p) {
        html += `<option value="${p.id}" ${p.id == selected_province_id ? 'selected' : ''}>
                   ${p.name}
                 </option>`;
      });
      $('#edit_port_province_id').html(html);
    }
  });
}

// โหลดอำเภอ
function loadEditAmphur(province_id, selected_amphur = "") {
  $.ajax({
    url: 'ajax/get_districts_edit.php',
    method: 'GET',
    data: { province_id },
    dataType: 'json',
    success: function(res) {
      let html = '<option value="">-- เลือกอำเภอ --</option>';
      res.forEach(function(a) {
        html += `<option value="${a.id}" ${a.id == selected_amphur ? 'selected' : ''}>
                    ${a.name}
                 </option>`;
      });
      $('#edit_port_amphur_id').html(html).trigger('change');
    }
  });
}

// โหลดหน่วยงาน
function loadEditdepartment(province_id, department_id = "") {
  $.ajax({
    url: 'ajax/get_departments_by_province_edit.php',
    method: 'GET',
    data: { province_id },
    dataType: 'json',
    success: function(res) {
      let html = '<option value="">-- เลือกหน่วยงาน --</option>';
      res.forEach(function(a) {
        html += `<option value="${a.id}" ${a.id == department_id  ? 'selected' : ''}>
                    ${a.name}
                 </option>`;
      });
      $('#edit_department_id').html(html);
    }
  });
}

// โหลดตำบล
function loadEditTambon(amphur_id, selected_tambon = "") {
  $.ajax({
    url: 'ajax/get_subdistricts_edit.php',
    method: 'GET',
    data: { amphur_id },
    dataType: 'json',
    success: function(res) {
      let html = '<option value="">-- เลือกตำบล --</option>';
      res.forEach(function(t) {
        html += `<option value="${t.id}" ${t.id == selected_tambon ? 'selected' : ''}>
                    ${t.name}
                 </option>`;
      });
      $('#edit_port_tambon_id').html(html).trigger('change');
    }
  });
}

// โหลดท่าเรือ
function loadEditPort(tambon_id, selected_port = "") {
  $.ajax({
    url: 'ajax/get_ports_by_tambon_edit.php',
    method: 'GET',
    data: { tambon_id },
    dataType: 'json',
    success: function(res) {
      let html = '<option value="">-- เลือกท่าเรือ --</option>';
      res.forEach(function(p) {
        html += `<option value="${p.license_no}" ${p.license_no == selected_port ? 'selected' : ''}>
                    ${p.port_name}
                 </option>`;
      });
      $('#edit_port_license_no').html(html);
    }
  });
}


// ====================================================================
// 2) event: เมื่อเลือกจังหวัด → โหลดอำเภอ
// ====================================================================
$('#edit_port_province_id').on('change', function() {
  const province_id = $(this).val();
  $('#edit_port_amphur_id').html('<option value="">-- เลือกอำเภอ --</option>');
  $('#edit_port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
  $('#edit_port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
  if(province_id){
     loadEditAmphur(province_id);
     loadEditdepartment(province_id);
  }

});

// เมื่อเลือกอำเภอ → โหลดตำบล
$('#edit_port_amphur_id').on('change', function() {
  const amphur_id = $(this).val();
  $('#edit_port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
  $('#edit_port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
  if(amphur_id) loadEditTambon(amphur_id);
});

// เมื่อเลือกตำบล → โหลดท่าเรือ
$('#edit_port_tambon_id').on('change', function() {
  const tambon_id = $(this).val();
  $('#edit_port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
  if(tambon_id) loadEditPort(tambon_id);
});


// ====================================================================
// 3) checkbox EU / Cold Room
// ====================================================================

// EU
$('#edit_eu_cert_checkbox').on('change', function() {
  $('#edit_inspection_form_type').val(this.checked ? '2' : '1');
});

// Cold room
$('#edit_cold_room_checkbox').on('change', function() {
  $('#edit_cold_room_flag').val(this.checked ? '1' : '0');
});


// ====================================================================
// 4) ฟังก์ชันเปิด modal + preload ข้อมูล (ตัวหลัก)
// ====================================================================

function openEditInspectionModal(id) {

  $.ajax({
    url: 'ajax/get_edit_request_detail.php',
    method: 'GET',
    data: { id },
    dataType: 'json',
    success: function(res) {

      if (!res.success) {
        Swal.fire('ผิดพลาด', res.message, 'error');
        return;
      }

      const req = res.request;

      // ====== ใส่ค่าลงฟอร์ม ======

      $('#edit_request_id').val(req.id);
      $('#edit_hidden_ship_code').val(req.ship_code);
      $('#edit_hidden_vessel_name').val(req.vessel_name);

      // แสดงเรือ
      $('#edit-modal-ship-code').text(req.ship_code);
      $('#edit-modal-vessel-name').text(req.vessel_name);
      $('#edit-modal-vessel-ton').text(req.gross_ton);
      $('#edit-modal-fishing-area').text(req.fishing_area);

      // Contact
      $('#edit_contact_phone').val(req.contact_phone);

      

      // Date
      $('#edit_inspect_date_start').val(req.inspect_date_start);
      $('#edit_inspect_date_end').val(req.inspect_date_end);

      // EU form
      $('#edit_inspection_form_type').val(req.inspection_form_type);
      $('#edit_eu_cert_checkbox').prop('checked', req.inspection_form_type == 2);

      // Cold Room
      $('#edit_cold_room_flag').val(req.cold_room_flag);
      $('#edit_cold_room_checkbox').prop('checked', req.cold_room_flag == 1);

      // ====== จังหวัด / อำเภอ / ตำบล / ท่าเรือ ======
      loadEditProvinces(req.port_province_id);

      // โหลดอำเภอ → ตั้งค่า selected
      loadEditAmphur(req.port_province_id, req.port_amphur_id);


      setTimeout(function() {
      loadEditdepartment(req.port_province_id, req.department_id);
      }, 200);
      // โหลดตำบล → ตั้งค่า selected (ดีเลย์นิดเพราะ AJAX)

      setTimeout(function() {
        loadEditTambon(req.port_amphur_id, req.port_tambon_id);
      }, 200);

      // โหลดท่าเรือ → selected
      setTimeout(function() {
        loadEditPort(req.port_tambon_id, req.port_license_no);
      }, 400);

      // เปิด Modal
      $('#editInspectionModal').modal('show');
    }
  });

}

</script>

<script>
$(document).ready(function () {

  $('#editInspectionForm').on('submit', function (e) {
    e.preventDefault();

    const $form = $(this);
    const formData = $form.serialize();

    $.ajax({
      url: $form.attr('action'),     // ajax/update_inspection.php
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function (res) {
        if (!res.success) {
          Swal.fire('บันทึกไม่สำเร็จ', res.message || 'เกิดข้อผิดพลาด', 'error');
          return;
        }

        Swal.fire({
          title: 'บันทึกสำเร็จ',
          text: 'ระบบได้บันทึกการแก้ไขคำขอตรวจเรือแล้ว',
          icon: 'success',
          confirmButtonText: 'ตกลง'
        }).then(() => {
          // ปิด modal และรีโหลดหน้า/ตาราง
          $('#editInspectionModal').modal('hide');
          // ถ้าใช้ DataTable ก็ reload table ตรงนี้แทนได้
          location.reload();
        });
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', status, error);
        Swal.fire('ผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง', 'error');
      }
    });
  });

});
</script>


<script>
$(function () {

  // -------------------------------
  // toggle block บุคคล / นิติบุคคล
  // -------------------------------
  function toggleApplicantBlocks(isJuristic) {
    isJuristic = String(isJuristic);

    $('#block_person').removeClass('d-none');

    if (isJuristic === '1') {
      $('#block_juristic').removeClass('d-none');
      $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุลผู้แทน/ผู้รับมอบอำนาจ)');
    } else {
      $('#block_juristic').addClass('d-none');
      $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');
    }
  }

  // tooltip
  $('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').tooltip();


  // ======================================================
  // กดปุ่มพิมพ์ สร.1
  // ======================================================
  $(document).on('click', '.btn-print-form1', function (e) {
    e.preventDefault();

    const requestId = $(this).data('id');
    $('#request_id').val(requestId);

    // เช็คก่อนว่ามี lock ไหม
    $.getJSON('ajax/ajax_get_applicant_info.php', { request_id: requestId })
      .done(function (res) {

        // ====== ถ้าไม่ success เช่น ไม่มี record → ให้กรอกข้อมูลใหม่ ======
        if (!res.success) {
          openApplicantModalForNew();
          return;
        }

        const d = res.data;

        // ====== ถ้าเอกสารถูกล็อกแล้ว → พิมพ์เลย ======
        if (d.form1_locked == 1) {

          if (d.print_url) {
            window.open(d.print_url, '_blank');
          } else {
            // fallback URL
            window.open('print_form1.php?request_id=' + requestId, '_blank');
          }

          return; // ไม่ต้องเปิด modal
        }

        // ====== ถ้ายังไม่ locked → เปิด modal เพื่อกรอกข้อมูล ======
        openApplicantModalFill(d);

      })
      .fail(function () {
        alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
      });
  });


  // ===========================
  // ฟังก์ชันเปิด modal ใหม่
  // ===========================
  function openApplicantModalForNew() {
    $('#form_applicant')[0].reset();
    $('#block_juristic').addClass('d-none');
    $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');
    $('#modalApplicant').modal('show');
  }

  // ===========================
  // ฟังก์ชันเปิด modal พร้อมข้อมูล
  // ===========================
  function openApplicantModalFill(d) {

    // reset
    $('#form_applicant')[0].reset();
    $('#block_juristic').addClass('d-none');
    $('#label_applicant_name').text('ข้าพเจ้า (ชื่อ–สกุล)');

    // filled
    $('#written_at').val(d.written_at || '');
    $('#written_date_text').val(d.written_date_text || '');
    $('#written_date').val(d.written_date || '');

    const isJuristic = d.is_juristic || 0;
    $('#is_juristic_' + isJuristic).prop('checked', true);
    toggleApplicantBlocks(isJuristic);

    $('#applicant_name').val(d.applicant_name || '');
    $('#applicant_age').val(d.applicant_age || '');
    $('#applicant_nationality').val(d.applicant_nationality || '');
    $('#applicant_address_no').val(d.applicant_address_no || '');
    $('#applicant_moo').val(d.applicant_moo || '');
    $('#applicant_tambon').val(d.applicant_tambon || '');
    $('#applicant_amphoe').val(d.applicant_amphoe || '');
    $('#applicant_province').val(d.applicant_province || '');

    // โทรศัพท์ไม่ให้แก้ไข
    $('#applicant_phone').val(d.applicant_phone || '').prop('readonly', true);

    $('#juristic_name').val(d.juristic_name || '');
    $('#juristic_office').val(d.juristic_office || '');
    $('#juristic_address_no').val(d.juristic_address_no || '');
    $('#juristic_moo').val(d.juristic_moo || '');
    $('#juristic_tambon').val(d.juristic_tambon || '');
    $('#juristic_amphoe').val(d.juristic_amphoe || '');
    $('#juristic_province').val(d.juristic_province || '');

    // เปิด modal
    $('#modalApplicant').modal('show');
  }


  // ===========================
  // submit → save → lock → print
  // ===========================
  $('#form_applicant').on('submit', function (e) {
    e.preventDefault();

    const $form = $(this);
    const $btn  = $form.find('button[type="submit"]');
    const old   = $btn.html();

    $btn.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin"></i> บันทึก...');

    $.post('ajax/ajax_save_applicant_form1.php', $form.serialize(), function (res) {

      if (!res.success) {
        alert(res.message || 'บันทึกไม่สำเร็จ');
        $btn.prop('disabled', false).html(old);
        return;
      }

      $('#modalApplicant').modal('hide');

      // เปิด PDF
      if (res.print_url) {
        window.open(res.print_url, '_blank');
      }

      $btn.prop('disabled', false).html(old);

    }, 'json').fail(function () {
      alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
      $btn.prop('disabled', false).html(old);
    });
  });


  // radio change
  $('input[name="is_juristic"]').on('change', function () {
    toggleApplicantBlocks($(this).val());
  });

});
</script>





<?php include("../../private/shared/footerall.php"); ?>