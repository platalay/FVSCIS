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
                                    <th>ประเภท สร.3</th>
                                    <th>วันหมดอายุ สร.3</th>
                                    <th>สถานะ สร.3</th>
                                    
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $my_vessels = Elicense::find_full_by_citizen_id($el_db, $fisherman->citizen_id);
                                    $index = 1;
                                    foreach ($my_vessels as $vessel) :
                                        $inspection = InspectionRequest::find_active_by_ship($vessel->ship_code);
                                        $is_pending = false;
                                        $status_text = 'ยื่นคำขอตรวจ';
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
                                                    class="btn btn-sm <?= $is_pending ? 'btn-secondary' : 'btn-primary' ?>" 
                                                    id="<?= h($vessel->ship_code) ?>" 
                                                    <?= $is_pending ? 'disabled' : '' ?> 
                                                    data-bs-toggle="tooltip" 
                                                    data-bs-placement="top"
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
                                        
                                        
                                        <?php $fvscisold = FvSanitationCertificationOld::find_by_ship_code($vessel->ship_code);?>
                                        <td><?= isset($fvscisold) && isset($fvscisold->certificate_status) ? $fvscisold->certificate_status : '-' ?></td>
                                        <td><?= isset($fvscisold) && isset($fvscisold->expiration_date) ? thai_date($fvscisold->expiration_date) : '-' ?></td>
                                        <td>
                                          <?php 
                                          $status = isset($fvscisold) && isset($fvscisold->status) ? $fvscisold->status : null;

                                          if ($status === 'active') {
                                              echo '<span class="badge bg-success">เปิดใช้งาน</span>';
                                          } elseif ($status === 'inactive') {
                                              echo '<span class="badge bg-secondary">หมดอายุ</span>';
                                          } else {
                                              echo '<span class="badge bg-light text-dark">-</span>';
                                          }
                                          ?>
                                      </td>
                                      </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <!-- Modal: Request Inspection -->
                    <?php include("modal/requestmodal.php"); ?>

 <!-- Modal: Request Inspection -->                   
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
  if (!form) return;

  const submitBtn   = form.querySelector('button[type="submit"]');

  // ====== อ้างอิง checkbox/hidden ======
  const euCheckbox  = document.getElementById('eu_cert_checkbox');
  let   typeInput   = document.getElementById('inspection_form_type'); // hidden: 1/2

  const coldCheckbox = document.getElementById('cold_room_checkbox');
  let   coldInput    = document.getElementById('cold_room_flag');       // hidden: 0/1

  // ✅ ถ้าไม่มี hidden → สร้างให้
  if (!typeInput) {
    typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'request[inspection_form_type]';
    typeInput.id   = 'inspection_form_type';
    typeInput.value = '1';
    form.appendChild(typeInput);
  }
  if (!coldInput) {
    coldInput = document.createElement('input');
    coldInput.type = 'hidden';
    coldInput.name = 'request[cold_room_flag]';
    coldInput.id   = 'cold_room_flag';
    coldInput.value = '0';
    form.appendChild(coldInput);
  }

  // ====== ฟังก์ชันซิงก์ค่า ======
  const syncEuType = () => {
    if (!euCheckbox) return;
    typeInput.value = euCheckbox.checked ? '2' : '1'; // 1=ทั่วไป, 2=EU
  };
  const syncColdFlag = () => {
    if (!coldCheckbox) return;
    coldInput.value = coldCheckbox.checked ? '1' : '0'; // 1=มีห้องเย็น, 0=ไม่มี
  };

  // ====== ตั้งค่าเริ่มต้นจาก hidden (รองรับกรณีเปิดแก้ไข/เติมค่าเดิม) ======
  if (euCheckbox) {
    // ถ้ามีค่าใน hidden อยู่แล้ว → ติ๊ก checkbox ให้ตรงกัน
    euCheckbox.checked = (typeInput.value === '2');
    syncEuType();
    euCheckbox.addEventListener('change', syncEuType);
  }
  if (coldCheckbox) {
    coldCheckbox.checked = (coldInput.value === '1');
    syncColdFlag();
    coldCheckbox.addEventListener('change', syncColdFlag);
  }

  // ====== Helper ตรวจสอบช่วงวันที่ ======
  function validateDateRange() {
    const startEl = document.getElementById('inspect_date_start');
    const endEl   = document.getElementById('inspect_date_end');
    if (!startEl || !endEl || !startEl.value || !endEl.value) return true;
    const start = new Date(startEl.value);
    const end   = new Date(endEl.value);
    return start <= end;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    // sync ค่าอีกครั้งก่อนส่ง
    syncEuType();
    syncColdFlag();

    // ตรวจวันที่เริ่ม–สิ้นสุด
    if (!validateDateRange()) {
      Swal.fire({
        icon: 'warning',
        title: 'ช่วงวันที่ไม่ถูกต้อง',
        text: '“วันที่เริ่มต้องการตรวจ” ต้องไม่เกิน “ถึงวันที่”',
        confirmButtonText: 'ตกลง'
      });
      return;
    }

    // ป้องกันกดซ้ำ
    if (submitBtn) {
      submitBtn.disabled = true;
      const originalText = submitBtn.innerHTML;
      submitBtn.dataset.originalText = originalText;
      submitBtn.innerHTML = 'กำลังส่งคำขอ...';
    }

    try {
      // สร้าง payload
      const formData = new FormData(form);

      // ปลายทาง: ใช้ action ของฟอร์มก่อน, ถ้าไม่มีให้ fallback
      const endpoint = form.getAttribute('action') || 'ajax/request_inspection.php';

      // ส่งคำขอ
      const response = await fetch(endpoint, { method: 'POST', body: formData });

      if (!response.ok) {
        const text = await response.text().catch(() => '');
        throw new Error(text || `HTTP ${response.status} ${response.statusText}`);
      }

      let result;
      try {
        result = await response.json();
      } catch {
        const text = await response.text().catch(() => '');
        throw new Error(text || 'ไม่สามารถอ่านข้อมูลที่ส่งกลับจากเซิร์ฟเวอร์ได้');
      }

      if (result && result.success) {
        await Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: result.message || 'บันทึกคำขอเรียบร้อย',
          confirmButtonText: 'ตกลง'
        });
        location.reload();
      } else {
        throw new Error(result?.message || 'ไม่สามารถบันทึกคำขอได้');
      }
    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: err.message || 'ไม่ทราบสาเหตุ',
        confirmButtonText: 'ตกลง'
      }).then(() => {
        if ((err.message || '').includes('Session')) {
          window.location.href = '../login.php';
        }
      });
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtn.dataset.originalText || 'ยื่นคำขอ';
      }
    }
  });
});
</script>


<?php include("../../private/shared/footerall.php"); ?>