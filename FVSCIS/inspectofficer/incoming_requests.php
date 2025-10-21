<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
$Department = Department::find_by_id($Officer->departments_id);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">

                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <!-- ปุ่ม Add -->
                                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalManualCase">
                                <i class="fas fa-plus"></i> เพิ่มข้อมูล
                                </button>
                            </h6>
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
                                        <th>ประเภทคำขอ</th>
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
                                            <td data-order="0">
                                            <div class="d-flex align-items-center gap-1">
                                                <!-- ปุ่มดูรายละเอียด -->
                                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#modalRequestDetail"
                                                        title="รายละเอียดคำขอ"
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
                                                <?php
                                                $attCount = InspectionAttachment::count_by_request_id($req->id);
                                                if ($attCount > 0): ?>
                                                <button class="btn btn-sm btn-warning btn-attachments"
                                                        title="ไฟล์แนบ (<?= $attCount ?>)"
                                                        data-id="<?= $req->id ?>">
                                                    <i class="fas fa-paperclip"></i>
                                                </button>
                                                <?php endif; ?>    
                                                
                                            </div>
                                            </td>


                                            <td><?= h($req->ship_code) ?></td>
                                            <td><?= h($req->vessel_name) ?></td>
                                            <td><?= thai_date($req->inspect_date_start). " ถึงวันที่ ".thai_date($req->inspect_date_end) ?></td>
                                            <td class="text-center">
                                            <?php
                                                $icons = [];

                                                // ① ตรวจแบบ EU หรือทั่วไป
                                                if ($req->inspection_form_type == 2) {
                                                $icons[] = '<i class="fas fa-globe-europe eu" title="ตรวจเพื่อ EU Export"></i>';
                                                } else {
                                                $icons[] = '<i class="fas fa-ship normal" title="ตรวจทั่วไป (แบบที่ 1)"></i>';
                                                }

                                                // ② ใครเป็นคนยื่น
                                                if ($req->is_manual_case == 1) {
                                                $icons[] = '<i class="fas fa-user-tie officer" title="เจ้าหน้าที่สร้างคำขอ"></i>';
                                                } else {
                                                $icons[] = '<i class="fas fa-user user" title="ผู้ประกอบการยื่นเอง"></i>';
                                                }

                                                // ③ ห้องเย็นหรือไม่
                                                if ($req->cold_room_flag == 1) {
                                                $icons[] = '<i class="fas fa-snowflake cold" title="เรือห้องเย็น"></i>';
                                                } else {
                                                $icons[] = '<i class="fas fa-thermometer-half warm" title="เรือทั่วไป (ไม่มีห้องเย็น)"></i>';
                                                }

                                                echo '<span class="req-type-pill">' . implode(' ', $icons) . '</span>';
                                            ?>
                                            </td>


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
                    <?php include(__DIR__ . '/modal/modalRequestDetail.php'); ?>
                    <?php include(__DIR__ . '/modal/modal_manual_case.php'); ?>  
                    <?php include(__DIR__ . '/modal/modal_attachment.php'); ?>                         
                               
</div><!-- <div class="container-fluid"> -->

  
<?php include("../../private/shared/footerofficer.php"); ?>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
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
            
            // ---------- INIT (ไม่เขียนทับ data-bs-toggle เดิม) ----------
            function initTooltips(scope = document) {
            scope.querySelectorAll('[title]').forEach(el => {
                // สร้าง/ดึง instance โดยไม่ไป set attribute ใด ๆ เพิ่ม
                bootstrap.Tooltip.getOrCreateInstance(el, {
                trigger: 'hover',     // ไม่ใช้ focus => ลดโอกาสค้าง
                container: 'body'
                });
            });
            }
            initTooltips();

            // ถ้าใช้ DataTables / มีการ redraw ให้เรียก initTooltips อีกครั้งเฉพาะพื้นที่ตาราง
            $('#yourTableId').on('draw.dt', function () {
            initTooltips(this);
            });

            // ---------- HIDE เมื่อคลิกปุ่ม (ไม่ขัดขวางคลิก) ----------
            document.addEventListener('click', (e) => {
            const el = e.target.closest('[title]');
            if (!el) return;
            const t = bootstrap.Tooltip.getInstance(el);
            if (t) t.hide();
            // ไม่ preventDefault / ไม่ stopPropagation -> ปุ่มยังทำงานปกติ
            });

            // ---------- HIDE เมื่อมี modal เปิด ----------
            document.addEventListener('show.bs.modal', () => {
            document.querySelectorAll('[title]').forEach(el => {
                const t = bootstrap.Tooltip.getInstance(el);
                if (t) t.hide();
            });
            });


            
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

            <script>
                // พรีวิวไฟล์ + ลบได้ก่อนส่ง
                (function(){
                const input = document.getElementById('attachments');
                const preview = document.getElementById('filePreview');

                // เก็บไฟล์ที่เลือกไว้ในออบเจ็กต์ DataTransfer เพื่อให้ "ลบแล้วอัปเดต input" ได้
                const dt = new DataTransfer();

                function renderList(){
                    preview.innerHTML = '';
                    for (let i = 0; i < dt.files.length; i++) {
                    const f = dt.files[i];
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center border rounded p-2 mb-2 gap-2';

                    // ไอคอน/thumbnail
                    const thumb = document.createElement('div');
                    thumb.style.width = '48px'; thumb.style.height = '48px';
                    thumb.className = 'd-flex align-items-center justify-content-center border rounded';
                    // แสดงภาพถ้าเป็นรูป
                    if (f.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(f);
                        img.style.width = '100%'; img.style.height = '100%'; img.style.objectFit = 'cover';
                        thumb.appendChild(img);
                    } else {
                        thumb.textContent = f.name.split('.').pop().toUpperCase(); // แสดงนามสกุลไฟล์
                        thumb.style.fontSize = '12px';
                    }

                    // รายละเอียดไฟล์
                    const info = document.createElement('div');
                    info.className = 'flex-grow-1';
                    info.innerHTML = `<div class="fw-semibold">${f.name}</div>
                                        <div class="text-muted" style="font-size:12px">
                                        ${(f.size/1024).toFixed(0)} KB • ${f.type || 'unknown'}
                                        </div>`;

                    // ปุ่มลบ
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'btn btn-sm btn-outline-danger';
                    btn.innerHTML = '<i class="fas fa-trash"></i>';
                    btn.onclick = () => {
                        // ลบรายการที่ i ออกจาก DataTransfer แล้วรีเฟรช
                        const ndt = new DataTransfer();
                        for (let j = 0; j < dt.files.length; j++) {
                        if (j !== i) ndt.items.add(dt.files[j]);
                        }
                        // คัดลอกกลับ
                        dt.items.clear();
                        for (let k = 0; k < ndt.files.length; k++) dt.items.add(ndt.files[k]);
                        input.files = dt.files;
                        renderList();
                    };

                    row.appendChild(thumb);
                    row.appendChild(info);
                    row.appendChild(btn);
                    preview.appendChild(row);
                    }
                }

                // เมื่อเลือกไฟล์ใหม่ ให้ merge เข้ากับ dt (รองรับเลือกหลายรอบ)
                input.addEventListener('change', (e) => {
                    const maxSize = 10 * 1024 * 1024; // 10MB/ไฟล์
                    const allow = ['pdf','jpg','jpeg','png','doc','docx'];

                    for (const f of e.target.files) {
                    const ext = f.name.split('.').pop().toLowerCase();
                    if (!allow.includes(ext)) { alert(`${f.name}: ชนิดไฟล์ไม่อนุญาต`); continue; }
                    if (f.size > maxSize) { alert(`${f.name}: ไฟล์ใหญ่เกิน 10MB`); continue; }
                    dt.items.add(f);
                    }
                    input.value = ''; // เคลียร์ input เดิม เพื่อให้เลือกซ้ำได้
                    input.files = dt.files;
                    renderList();
                });
                })();
                </script>

                <script>
                $(document).ready(function () {
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

                <script>
                    // ตั้งค่า checkbox -> hidden
                    (function(){
                    const euCbx   = document.getElementById('eu_cert_checkbox');
                    const euType  = document.getElementById('inspection_form_type'); // hidden
                    const coldCbx = document.getElementById('cold_room_checkbox');
                    const coldFlg = document.getElementById('cold_room_flag');       // hidden

                    if(euCbx){
                        euCbx.addEventListener('change', ()=> euType.value = euCbx.checked ? '2' : '1');
                    }
                    if(coldCbx){
                        coldCbx.addEventListener('change', ()=> coldFlg.value = coldCbx.checked ? '1' : '0');
                    }

                    // กันย้อนหลังให้ช่องวันที่
                    const d = document.getElementById('confirmed_inspect_date');
                    if(d) d.min = new Date().toISOString().split('T')[0];
                    })();

                    // ส่งฟอร์มด้วย AJAX
                    $(document).on('submit', '#formManualCase', function(e){
                    e.preventDefault();

                    const $btn = $(this).find('button[type="submit"]').prop('disabled', true);
                    const fd   = new FormData(this); // เก็บทุก input ที่มี name รวมถึงไฟล์ attachments[]

                    // ตรวจไฟล์ภาพฝั่ง client (กันพลาด)
                    const files = $('#attachments')[0]?.files || [];
                    const allow = ['image/jpeg','image/png','image/gif','image/webp'];
                    for(let i=0;i<files.length;i++){
                        if(!allow.includes(files[i].type)){ 
                        Swal.fire('ไฟล์ไม่ถูกต้อง','อนุญาตเฉพาะรูปภาพ JPG/PNG/GIF/WebP','warning');
                        $btn.prop('disabled', false);
                        return;
                        }
                        if(files[i].size > 10*1024*1024){
                        Swal.fire('ไฟล์ใหญ่เกินไป','จำกัดไม่เกิน 10MB ต่อไฟล์','warning');
                        $btn.prop('disabled', false);
                        return;
                        }
                    }

                    $.ajax({
                        url: 'ajax/create_manual_request_by_officer.php',
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    }).done(res => {
                        if(res.success){
                        Swal.fire({icon:'success', title:'สำเร็จ', text:res.message, timer:1600, showConfirmButton:false})
                            .then(()=> location.reload());
                        }else{
                        Swal.fire('ไม่สำเร็จ', res.message || 'บันทึกไม่สำเร็จ', 'error');
                        }
                    }).fail(xhr => {
                        Swal.fire('ผิดพลาด', 'ติดต่อเซิร์ฟเวอร์ไม่ได้', 'error');
                        console.error(xhr.responseText);
                    }).always(() => $btn.prop('disabled', false));
                    });
                    </script>

                    
                    <script>
                    // 📎 เมื่อคลิกปุ่มไฟล์แนบ (เวอร์ชันใหม่)
                    $(document).on('click', '.btn-attachments', function () {
                    const reqId = $(this).data('id');
                    if (!reqId) return;

                    // เคลียร์ UI เดิม
                    $('#photoModalReqId').text('');          // เราจะใส่ "ชื่อเรือ (ทะเบียน ...) — N รูป" ทีหลัง
                    $('#photoGrid').empty();
                    $('#photoEmpty').addClass('d-none').text('กำลังโหลด...');
                    $('#photoPreviewWrap').addClass('d-none');
                    $('#photoPreviewImg').attr('src', '');

                    // เปิดโมดัลก่อน (ให้ผู้ใช้เห็นว่าเริ่มทำงานแล้ว)
                    $('#modalPhotoAttachments').modal('show');

                    // สร้าง Promise สองตัว (ดึงรายละเอียดคำขอ + ไฟล์แนบ)
                    const pDetail = $.ajax({
                        url: 'ajax/get_request_detail.php',
                        method: 'GET',
                        data: { id: reqId },
                        dataType: 'json'
                    });

                    const pAttach = $.ajax({
                        url: 'ajax/get_request_attachments.php',
                        method: 'GET',
                        data: { id: reqId },
                        dataType: 'json'
                    });

                    // รอทั้งสองอย่างเสร็จ แล้วค่อยอัปเดตหัวข้อ + แสดงรูป
                    $.when(pDetail, pAttach).done(function (detailRes, attachRes) {
                        // jQuery.when คืนค่าเป็น array [data, statusText, jqXHR]
                        const detail = detailRes[0];
                        const attach = attachRes[0];

                        // ----- 1) เตรียมชื่อเรือ/ทะเบียน -----
                        let vesselName = '';
                        let shipCode   = '';
                        if (detail && detail.success && detail.request) {
                        vesselName = detail.request.vessel_name || '';
                        shipCode   = detail.request.ship_code   || '';
                        }

                        // ----- 2) เรนเดอร์รูป -----
                        let photos = [];
                        if (attach && attach.success && Array.isArray(attach.attachments)) {
                        photos = attach.attachments.filter(a => a.is_image);
                        // ใช้ url ที่ encode แล้ว ถ้า API ส่งมา
                        photos = photos.map(p => ({
                            ...p,
                            _url: p.url_enc ? p.url_enc : (encodeURI(p.url || ''))
                        }));
                        } else {
                        $('#photoEmpty').removeClass('d-none').text('ไม่สามารถโหลดไฟล์แนบได้');
                        }
                        renderPhotoGrid(photos);

                        // ----- 3) ตั้งหัวโมดัล: "ชื่อเรือ (ทะเบียน xxx) — N รูป" -----
                        const parts = [];
                        if (vesselName) parts.push(`ชื่อเรือ ${vesselName}`);
                        if (shipCode)   parts.push(`ทะเบียน ${shipCode}`);
                        const leftText = parts.length ? parts.join(' • ') : `คำขอ #${reqId}`;
                        const rightText = `— ${photos.length} รูป`;
                        $('#photoModalReqId').text(`${leftText} ${rightText}`);

                    }).fail(function () {
                        $('#photoEmpty').removeClass('d-none').text('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                    });
                    });


                    function renderPhotoGrid(photos) {
                    const $grid = $('#photoGrid'), $empty = $('#photoEmpty');
                    const $pvW = $('#photoPreviewWrap'), $pv = $('#photoPreviewImg');

                    const imgs = photos.filter(p => p.is_image);
                    if (!imgs.length) { $empty.removeClass('d-none').text('ยังไม่มีรูปภาพแนบ'); return; }
                    $empty.addClass('d-none');

                    // ใช้ url_enc ถ้ามี (กันชื่อไทย/ช่องว่าง), และข้ามไฟล์ที่ exists=false
                    const valid = imgs.filter(p => p.exists !== false);

                    let html = '';
                    valid.forEach(p => {
                        const u = p.url_enc || encodeURI(p.url);
                        html += `
                        <div class="border rounded p-1 shadow-sm" style="width:140px;">
                            <a href="${u}" class="photo-thumb" data-url="${u}">
                            <img src="${u}" alt="${p.name}" class="img-thumbnail w-100" style="height:120px; object-fit:cover;">
                            </a>
                            <div class="small text-truncate mt-1" title="${p.name}">${p.name}</div>
                        </div>`;
                    });
                    $grid.html(html);

                    if (valid.length) {
                        $pv.attr('src', valid[0].url_enc || encodeURI(valid[0].url));
                        $pvW.removeClass('d-none');
                    }

                    $grid.off('click','a.photo-thumb').on('click','a.photo-thumb', function(e){
                        e.preventDefault();
                        $pv.attr('src', $(this).data('url'));
                        $pvW.removeClass('d-none');
                    });
                    }
                    </script>
                
<?php 
include("../../private/shared/footerall.php");
?>