<?php
require_once('../../private/initialize.php');
include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
<h3 class="h3 mb-2 text-gray-800">ข้อมูลผู้ใช้งานกลุ่มเจ้าหน้าที่</h3>
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr style="font-size: 14px;">
                                        <th>รหัสเจ้าหน้าที่</th>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>รหัสผ่าน</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>ตำแหน่ง</th>
                                        <th>อีเมล</th>
                                        <th>รหัสบัญชี Google</th>
                                        <th>รหัสบัญชี Facebook</th>
                                        <th>รหัสบัญชี LINE</th>
                                        <th>สถานะใช้งาน</th>
                                        <th>สถานะการอนุมัติ</th>
                                        <th>ผู้ที่อนุมัติ</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>โทเคนสำหรับล็อกอิน</th>
                                        <th>วันหมดอายุของโทเคน</th>
                                        <th>ผู้สร้างข้อมูล</th>
                                        <th>ผู้แก้ไขข้อมูลล่าสุด</th>
                                        <th>วันที่สร้างข้อมูล</th>
                                        <th>วันที่แก้ไขข้อมูลล่าสุด</th>
                                        <th>รหัสหน่วยงานที่สังกัด</th>
                                        <th>รหัสประเภทผู้ใช้งาน</th>

                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr style="font-size: 14px;">
                                        <th>รหัสเจ้าหน้าที่</th>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>รหัสผ่าน</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>ตำแหน่ง</th>
                                        <th>อีเมล</th>
                                        <th>รหัสบัญชี Google</th>
                                        <th>รหัสบัญชี Facebook</th>
                                        <th>รหัสบัญชี LINE</th>
                                        <th>สถานะใช้งาน</th>
                                        <th>สถานะการอนุมัติ</th>
                                        <th>ผู้ที่อนุมัติ</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>โทเคนสำหรับล็อกอิน</th>
                                        <th>วันหมดอายุของโทเคน</th>
                                        <th>ผู้สร้างข้อมูล</th>
                                        <th>ผู้แก้ไขข้อมูลล่าสุด</th>
                                        <th>วันที่สร้างข้อมูล</th>
                                        <th>วันที่แก้ไขข้อมูลล่าสุด</th>
                                        <th>รหัสหน่วยงานที่สังกัด</th>
                                        <th>รหัสประเภทผู้ใช้งาน</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php  
                                    $Officers = Officer::find_all();
                                    foreach($Officers as $Officer) { ?> 
                                    <tr style="font-size: 14px;">
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   
                                    <?php echo h($Officer->id); ?>
                                    
                                    <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ภายใน foreach ของ Officer -->
                                        <button class="btn btn-primary btn-sm" title="แก้ไข"
                                                style="width: 35px; height: 35px;"
                                                onclick="editOfficer(<?php echo $Officer->id; ?>)">
                                            <i class="fas fa-edit text-white"></i>
                                        </button>


                                        <!-- ปุ่ม Delete -->
                                        <button class="btn btn-danger btn-sm" onclick="deleteOfficer(<?php echo $Officer->id; ?>)" title="ลบ" style="width: 35px; height: 35px;">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </div>
                                    </td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->username); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->password); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->full_name); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->position); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->email); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->google_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->facebook_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->line_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->is_active); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->is_approved); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->approved_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->approved_at); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->login_token); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->token_expiry); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->created_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->updated_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->created_at); ?></td>
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($Officer->updated_at); ?></td>
                                    <?php $department = Department::find_by_id($Officer->departments_id);?>    
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($department->name); ?></td>
                                    <?php $UserType = UserType::find_by_id($Officer->usertype_id);?>    
                                    <td class="text-center align-middle" style="color: <?php echo ((int)$Officer->is_approved === 1) ? '#2e7d32' : '#e65100'; ?>;">   <?php echo h($UserType->name_th); ?></td>
                                    </tr> <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Edit Officer Modal -->
                    <div class="modal fade" id="EditOfficerModal" tabindex="-1" aria-labelledby="EditOfficerModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="EditOfficerModalLabel">แก้ไขข้อมูลเจ้าหน้าที่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <form id="EditOfficerForm" action="update_officer.php" method="post">
                            <input type="hidden" name="Officer[id]" id="edit_id">

                            <div class="row g-3">
                                <div class="col-md-6">
                                <label for="edit_username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" name="Officer[username]" id="edit_username" required>
                                </div>
                                <div class="col-md-6">
                                <label for="edit_password" class="form-label">รหัสผ่าน</label>
                                <input type="text" class="form-control" name="Officer[password]" id="edit_password">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_full_name" class="form-label">ชื่อ - นามสกุล</label>
                                <input type="text" class="form-control" name="Officer[full_name]" id="edit_full_name">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_position" class="form-label">ตำแหน่ง</label>
                                <input type="text" class="form-control" name="Officer[position]" id="edit_position">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="Officer[email]" id="edit_email">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_google_id" class="form-label">Google ID</label>
                                <input type="text" class="form-control" name="Officer[google_id]" id="edit_google_id">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_facebook_id" class="form-label">Facebook ID</label>
                                <input type="text" class="form-control" name="Officer[facebook_id]" id="edit_facebook_id">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_line_id" class="form-label">LINE ID</label>
                                <input type="text" class="form-control" name="Officer[line_id]" id="edit_line_id">
                                </div>
                                <div class="col-md-6">
                                <label for="edit_is_active" class="form-label">สถานะใช้งาน</label>
                                <select class="form-select" name="Officer[is_active]" id="edit_is_active">
                                    <option value="1">ใช้งาน</option>
                                    <option value="0">ไม่ใช้งาน</option>
                                </select>
                                </div>
                                <div class="col-md-6">
                                <label for="edit_is_approved" class="form-label">สถานะการอนุมัติ</label>
                                <select class="form-select" name="Officer[is_approved]" id="edit_is_approved">
                                    <option value="1">อนุมัติแล้ว</option>
                                    <option value="0">รออนุมัติ</option>
                                </select>
                                </div>
                                <div class="col-md-6">
                                <label for="edit_departments_id" class="form-label">หน่วยงาน</label>
                                <?php $departments = Department::find_all();?>
                                <select name="Officer[departments_id]" id="edit_departments_id" class="form-select">
                                <option value="">-- กรุณาเลือกกลุ่มหน่วยงาน --</option>
                                <?php foreach($departments as $group): ?>
                                    <option value="<?php echo $group->id; ?>"><?php echo $group->name; ?></option>
                                <?php endforeach; ?>
                                </select>
                                </div>
                                <div class="col-md-6">
                                <label for="edit_usertype_id" class="form-label">สิทธิ์การใช้งาน</label>
                                <?php $UserTypes = UserType::find_all();?>
                                <select class="form-select" name="Officer[usertype_id]" id="edit_usertype_id">
                                    <option value="">-- กรุณาเลือกกลุ่มหน่วยงาน --</option>
                                    <?php foreach($UserTypes as $UserType): ?>
                                        <option value="<?php echo $UserType->id; ?>"><?php echo $UserType->name_th; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                            </div>
                            </form>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" form="EditOfficerForm" class="btn btn-success">บันทึกการแก้ไข</button>
                        </div>
                        </div>
                    </div>
                    </div>

                <!-- /.container-fluid -->
</div>
<?php
include("../../private/shared/footeradmin.php");
?>
<!-- Page level plugins -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

                                        
            <script>
        $(document).ready(function () {
            if (!$.fn.DataTable) {
                console.error("DataTables plugin not loaded");
                return;
            }

            var table = $('#dataTable').DataTable();

            $('#topSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            $('#EditOfficerForm').on('submit', function (e) {
                e.preventDefault();

                const form = $(this);
                const formData = form.serializeArray();

                submitOfficer(formData);
            });

            function submitOfficer(formData) {
                $.ajax({
                    url: 'ajax/update_officer.php',
                    type: 'POST',
                    data: $.param(formData),
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // ตรวจว่าเป็นข้อความเตือน overwrite หรือไม่
                            if (response.message.includes('กลุ่มนี้มีผู้อนุมัติอยู่แล้ว')) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'พบผู้อนุมัติซ้ำ',
                                    text: response.message,
                                    showCancelButton: true,
                                    confirmButtonText: 'แทนที่',
                                    cancelButtonText: 'ยกเลิก'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // เพิ่ม force=1 แล้วส่งอีกรอบ
                                        formData.push({ name: 'force', value: '1' });
                                        submitOfficer(formData);
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ไม่สามารถบันทึกข้อมูลได้',
                                    text: response.message
                                });
                            }
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: xhr.responseText
                        });
                    }
                });
            }
        });
        </script>

        
        <script>
        function editOfficer(id) {
                $.ajax({
                    url: 'ajax/get_officer.php',
                    type: 'GET',
                    data: { id: id },
                    dataType: 'json',
                    success: function(data) {
                        // เติมค่าลงฟอร์มใน modal
                        $('#edit_id').val(data.id);
                        $('#edit_username').val(data.username);
                        $('#edit_password').val(data.password);
                        $('#edit_full_name').val(data.full_name);
                        $('#edit_position').val(data.position);
                        $('#edit_email').val(data.email);
                        $('#edit_google_id').val(data.google_id);
                        $('#edit_facebook_id').val(data.facebook_id);
                        $('#edit_line_id').val(data.line_id);
                        $('#edit_is_active').val(data.is_active);
                        $('#edit_is_approved').val(data.is_approved);
                        // ✅ ตั้งค่าค่า dropdown ให้เลือก option ที่ถูกต้อง
                        $('#edit_departments_id').val(data.departments_id).trigger('change');
                        $('#edit_usertype_id').val(data.usertype_id).trigger('change');

                        // เปิด modal
                        const editModal = new bootstrap.Modal(document.getElementById('EditOfficerModal'));
                        editModal.show();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถโหลดข้อมูลได้',
                            text: 'เกิดข้อผิดพลาด: ' + xhr.responseText
                        });
                    }
                });
            }

            function deleteOfficer(id) {
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: 'การลบเจ้าหน้าที่นี้จะไม่สามารถย้อนกลับได้',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'ajax/delete_officer.php',
                            type: 'POST',
                            data: { id: id },
                            dataType: 'json',
                            success: function (res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'ลบข้อมูลสำเร็จ',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'ไม่สามารถลบได้',
                                        text: res.message
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ข้อผิดพลาด',
                                    text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                                });
                            }
                        });
                    }
                });
            }
        </script>

<?php
include("../../private/shared/footerall.php");
?>

