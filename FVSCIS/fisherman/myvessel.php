<?php
require_once('../../private/initialize.php');
$session->require_role(['fisherman']);
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
                                    <th>วันหมดอายุใบอนุญาติ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $my_vessels = Elicense::find_full_by_citizen_id($el_db, $fisherman->citizen_id);
                                    $index = 1;
                                    foreach ($my_vessels as $vessel) :
                                        $inspection = InspectionRequest::find_active_by_ship($vessel->ship_code);
                                        $is_pending = false;
                                        $status_text = '';

                                        if ($inspection) {
                                            if ($inspection->status !== 'completed' && $inspection->status !== 'cancelled') {
                                                $is_pending = true;
                                                $status_text = 'อยู่ระหว่างดำเนินการ ต้องรอให้ครบขั้นตอนก่อนหรือยกเลิก';
                                            }
                                        }
                                    ?>
                                    <tr style="font-size: 14px;">
                                        <td class="text-center">
                                            <form onsubmit="event.preventDefault(); <?php if (!$is_pending): ?>openRequestModal('<?= h($vessel->ship_code) ?>');<?php endif; ?>">
                                                <button 
                                                    type="submit" 
                                                    class="btn btn-sm <?= $is_pending ? 'btn-secondary' : 'btn-success' ?>" 
                                                    id="<?= h($vessel->ship_code) ?>" 
                                                    <?= $is_pending ? 'disabled' : '' ?> 
                                                    title="<?= h($status_text) ?>"
                                                >
                                                    <i class="fas fa-clipboard-check"></i>
                                                    <?php if ($is_pending): ?>
                                                        <span class="ms-1">รอดำเนินการ</span>
                                                    <?php endif; ?>
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
                                        <?php $fvscisold = FvSanitationCertificationOld::find_by_ship_code($vessel->ship_code);?>
                                        <td><?= isset($fvscisold) && isset($fvscisold->expiration_date) ? h($fvscisold->expiration_date) : '-' ?></td>
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

                            <!-- Checkbox ยืนยัน -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="request[confirm_agreement]" id="confirm_agreement" required>
                                <label class="form-check-label" for="confirm_agreement">
                                ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้องและยินยอมให้ใช้ข้อมูลนี้ในการตรวจเรือ
                                </label>
                            </div>

                            <!-- Hidden ship code -->
                            <input type="hidden" name="ship_code" id="hidden_ship_code">
                            <input type="hidden" name="request[vessel_name]" id="hidden_vessel_name">
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
    <script src="../js/fvscis.js"></script> 
            
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
                    document.getElementById('hidden_vessel_name').value = data.vessel_name;
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

           <script>
                $(document).ready(function () {
                    const allowedProvinceIds = [1, 2, 7, 8, 9, 15, 16, 25, 30, 32, 35, 49, 50, 58, 59, 60, 61, 62, 83, 12, 68, 39, 22];

                    // โหลดจังหวัดเมื่อเปิด modal
                    $('#requestInspectionModal').on('shown.bs.modal', function () {
                        $.ajax({
                            url: 'ajax/get_provinces.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function (provinces) {
                                const $provinceSelect = $('#port_province_id');
                                $provinceSelect.empty().append('<option value="">-- เลือกจังหวัด --</option>');

                                $.each(provinces, function (index, province) {
                                    if (allowedProvinceIds.includes(parseInt(province.id))) {
                                        $provinceSelect.append('<option value="' + province.id + '">' + province.name + '</option>');
                                    }
                                });

                                // รีเซต dropdown อื่น
                                $('#port_amphur_id').html('<option value="">-- เลือกอำเภอ --</option>');
                                $('#port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
                                $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
                            }
                        });
                    });

                    // ✅ เมื่อเลือกหน่วยงาน → โหลดจังหวัดและตั้งค่า
                    $('#department_id').on('change', function () {
                        const selectedOption = this.options[this.selectedIndex];
                        const provinceId = $(selectedOption).data('province-id'); // ใช้ data-province-id

                        if (!provinceId) return;

                        // โหลดรายการจังหวัดใหม่ แล้วเลือกจังหวัดที่ตรงกัน
                        $.ajax({
                            url: 'ajax/get_provinces.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function (provinces) {
                                const $provinceSelect = $('#port_province_id');
                                $provinceSelect.empty().append('<option value="">-- เลือกจังหวัด --</option>');

                                $.each(provinces, function (index, province) {
                                    if (allowedProvinceIds.includes(parseInt(province.id))) {
                                        const selected = (province.id == provinceId) ? 'selected' : '';
                                        $provinceSelect.append('<option value="' + province.id + '" ' + selected + '>' + province.name + '</option>');
                                    }
                                });

                                // Trigger change เพื่อโหลดอำเภอ
                                $provinceSelect.trigger('change');

                                // Clear อื่น ๆ
                                $('#port_amphur_id').html('<option value="">-- เลือกอำเภอ --</option>');
                                $('#port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
                                $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');
                            }
                        });
                    });

                    // เมื่อเลือกจังหวัด → โหลดอำเภอ
                    $('#port_province_id').on('change', function () {
                        const provinceId = $(this).val();
                        $('#port_amphur_id').html('<option value="">-- เลือกอำเภอ --</option>');
                        $('#port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
                        $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');

                        if (provinceId) {
                            $.ajax({
                                url: 'ajax/get_districts.php',
                                type: 'GET',
                                data: { province_id: provinceId },
                                dataType: 'html',
                                success: function (html) {
                                    $('#port_amphur_id').html(html);
                                }
                            });
                            // โหลดหน่วยงานในจังหวัด
                            $.ajax({
                                url: 'ajax/get_departments_by_province.php',
                                type: 'GET',
                                data: { province_id: provinceId },
                                dataType: 'html',
                                success: function (html) {
                                    $('#department_id').html(html);
                                }
                            });
                        }
                    });

                    // เมื่อเลือกอำเภอ → โหลดตำบล
                    $('#port_amphur_id').on('change', function () {
                        const amphurId = $(this).val();
                        $('#port_tambon_id').html('<option value="">-- เลือกตำบล --</option>');
                        $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');

                        if (amphurId) {
                            $.ajax({
                                url: 'ajax/get_subdistricts.php',
                                type: 'GET',
                                data: { district_id: amphurId },
                                dataType: 'html',
                                success: function (html) {
                                    $('#port_tambon_id').html(html);
                                }
                            });
                        }
                    });

                    // เมื่อเลือกตำบล → โหลดท่าเรือ
                    $('#port_tambon_id').on('change', function () {
                        const tambonId = $(this).val();
                        $('#port_license_no').html('<option value="">-- เลือกท่าเรือ --</option>');

                        if (tambonId) {
                            $.ajax({
                                url: 'ajax/get_ports_by_tambon.php',
                                type: 'GET',
                                data: { tambon_id: tambonId },
                                dataType: 'html',
                                success: function (html) {
                                    $('#port_license_no').html(html);
                                }
                            });
                        }
                    });
                });
                </script>


                

                 <!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startDateInput = document.getElementById('inspect_date_start');
    const endDateInput = document.getElementById('inspect_date_end');

    function setMinStartDate() {
        const today = new Date();
        today.setDate(today.getDate() + 7); // +7 วัน
        const minDateStr = today.toISOString().split('T')[0];
        startDateInput.setAttribute('min', minDateStr);

        // ตั้ง min ให้กับ endDate ด้วยค่าที่เท่ากันเบื้องต้น
        endDateInput.setAttribute('min', minDateStr);
    }

    // เมื่อผู้ใช้เปลี่ยนวันที่เริ่ม → update min ของวันที่สิ้นสุด
    startDateInput.addEventListener('change', function () {
        const selectedStart = startDateInput.value;
        if (selectedStart) {
            endDateInput.setAttribute('min', selectedStart);
        }
    });

    async function validateDates(event) {
        const startDate = new Date(startDateInput.value);
        const endDate = new Date(endDateInput.value);

        const todayPlus7 = new Date();
        todayPlus7.setHours(0, 0, 0, 0);
        todayPlus7.setDate(todayPlus7.getDate() + 7);

        startDate.setHours(0, 0, 0, 0);
        endDate.setHours(0, 0, 0, 0);

        if (startDate < todayPlus7) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'วันที่เริ่มต้องการตรวจไม่ถูกต้อง',
                text: 'กรุณาเลือกวันที่อย่างน้อย 7 วันถัดจากวันนี้',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }

        if (endDate < startDate) {
            event.preventDefault();
            await Swal.fire({
                icon: 'warning',
                title: 'ช่วงวันที่ไม่ถูกต้อง',
                text: 'วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มตรวจ',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }

        return true;
    }

    setMinStartDate();

    const form = document.getElementById('requestInspectionForm');
    form.addEventListener('submit', validateDates);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('contact_phone');

    // 1. ไม่ให้พิมพ์ตัวอักษร
    phoneInput.addEventListener('keypress', function (e) {
        const char = String.fromCharCode(e.which);
        if (!/[0-9]/.test(char)) {
            e.preventDefault();
        }
    });

    // 2. ไม่ให้ paste ตัวอักษรที่ไม่ใช่ตัวเลข
    phoneInput.addEventListener('paste', function (e) {
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        if (!/^[0-9]+$/.test(pasted)) {
            e.preventDefault();
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('requestInspectionForm');

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch('ajax/request_inspection.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: result.message,
                    confirmButtonText: 'ตกลง'
                });

                location.reload(); // ✅ reload หลังจาก alert ปิด
            } else {
                throw new Error(result.message);
            }
        } catch (err) {
            // ❌ กรณี AJAX ล้มเหลว / JSON ผิด / server error
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: err.message,
                confirmButtonText: 'ตกลง'
            }).then(() => {
                // ✅ ถ้า session หมดอายุ → ส่งกลับหน้า login
                if (err.message.includes('Session')) {
                    window.location.href = '../login.php';
                }
            });
        }
    });
});
</script>
<?php include("../../private/shared/footerall.php"); ?>