<?php
require_once('../../private/initialize.php');
include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
<h3 class="h3 mb-2 text-gray-800">ข้อมูลผู้ใช้งานกลุ่มชาวประมง</h3>
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr style="font-size: 14px;">
                                        <th>ลำดับผู้ใช้งาน</th>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>ชื่อเต็ม</th>
                                        <th>อีเมล</th>
                                        <th>Google ID</th>
                                        <th>Facebook ID</th>
                                        <th>Line ID</th>
                                        <th>หมายเลขบัตรประชาชน</th>
                                        <th>เปิดใช้งาน</th>
                                        <th>อนุมัติแล้ว</th>
                                        <th>อนุมัติโดย</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>Token เข้าระบบ</th>
                                        <th>วันหมดอายุ Token</th>
                                        <th>สร้างโดย</th>
                                        <th>แก้ไขโดย</th>
                                        <th>วันที่สร้าง</th>
                                        <th>วันที่แก้ไข</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr style="font-size: 14px;">
                                        <th>ลำดับผู้ใช้งาน</th>
                                        <th>ชื่อผู้ใช้</th>
                                        <th>ชื่อเต็ม</th>
                                        <th>อีเมล</th>
                                        <th>Google ID</th>
                                        <th>Facebook ID</th>
                                        <th>Line ID</th>
                                        <th>หมายเลขบัตรประชาชน</th>
                                        <th>เปิดใช้งาน</th>
                                        <th>อนุมัติแล้ว</th>
                                        <th>อนุมัติโดย</th>
                                        <th>วันที่อนุมัติ</th>
                                        <th>Token เข้าระบบ</th>
                                        <th>วันหมดอายุ Token</th>
                                        <th>สร้างโดย</th>
                                        <th>แก้ไขโดย</th>
                                        <th>วันที่สร้าง</th>
                                        <th>วันที่แก้ไข</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php  
                                    
                                    $Fishermans = Fisherman::find_all();             
                                    foreach($Fishermans as $Fisherman) { 
                                        $color = ((int)$Fisherman->is_approved === 1) ? '#2e7d32' : '#e65100';
                                        ?> 
                                    <tr style="font-size: 14px;">
                                    <td class="text-center align-middle" style="color: <?= $color; ?>">   
                                    <?php echo h($Fisherman->id); ?>
                                    
                                    <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ภายใน foreach ของ Officer -->
                                        
                                        <button class="btn btn-primary btn-sm" title="แก้ไข"
                                                style="width: 35px; height: 35px;"
                                                onclick="editFisherman(<?= $Fisherman->id ?>)">
                                            <i class="fas fa-edit text-white"></i>
                                        </button>

                                        <!-- ปุ่มเปลี่ยนรหัสผ่านใหม่ -->
                                        <button class="btn btn-warning btn-sm btn-change-pass"
                                                data-id="<?php echo $Fisherman->id; ?>"
                                                data-username="<?php echo h($Fisherman->username); ?>">
                                            <i class="fas fa-key"></i>
                                        </button>

                                        <!-- ปุ่ม Delete -->
                                        <button class="btn btn-danger btn-sm" onclick="deleteFisherman(<?php echo $Fisherman->id; ?>)" title="ลบ" style="width: 35px; height: 35px;">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </div>
                                    </td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->username); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->full_name); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->email); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->google_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->facebook_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->line_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->citizen_id); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->is_active); ?></td>
                                    <td class="text-center align-middle"
                                        style="color: <?= $color; ?>">
                                    
                                        <?php echo ((int)$Fisherman->is_approved === 1) ? 'อนุมัติ' : 'รออนุมัติ'; ?>
                                    </td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->approved_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->approved_at); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->login_token); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->token_expiry); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->created_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->updated_by); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->created_at); ?></td>
                                    <td class="text-center align-middle" style="color: <?= $color; ?>"><?php echo h($Fisherman->updated_at); ?></td>
                                    </tr> <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Edit fisherman Modal -->
                    <div class="modal fade" id="modalEditFisherman" tabindex="-1" aria-labelledby="modalEditFishermanLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="formEditFisherman">
                        <div class="modal-content">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalEditFishermanLabel">แก้ไขข้อมูลชาวประมง</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>
                            <div class="modal-body">
                            <input type="hidden" name="fisherman[id]" id="edit-id">

                            <div class="mb-3">
                                <label for="edit-full_name" class="form-label">ชื่อเต็ม</label>
                                <input type="text" class="form-control" name="fisherman[full_name]" id="edit-full_name" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit-email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="fisherman[email]" id="edit-email">
                            </div>

                            <div class="mb-3">
                                <label for="edit-citizen_id" class="form-label">หมายเลขบัตรประชาชน</label>
                                <input type="text" class="form-control" name="fisherman[citizen_id]" id="edit-citizen_id" required>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="fisherman[is_active]" id="edit-is_active" value="1">
                                <label class="form-check-label" for="edit-is_active">เปิดใช้งาน</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" name="fisherman[is_approved]" id="edit-is_approved" value="1">
                                <label class="form-check-label" for="edit-is_approved">อนุมัติแล้ว</label>
                            </div>

                            <input type="hidden" name="fisherman[approved_by]" id="edit-approved_by">
                            <input type="hidden" name="fisherman[approved_at]" id="edit-approved_at">
                            <input type="hidden" name="fisherman[updated_by]" id="edit-updated_by">
                            <input type="hidden" name="fisherman[updated_at]" id="edit-updated_at">
                            </div>
                            <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            </div>
                        </div>
                        </form>
                    </div>
                    </div>
                    <!-- Edit fisherman Modal -->


                    <!-- Edit password Modal -->
                    <div class="modal fade" id="changePassModal" tabindex="-1" role="dialog" aria-labelledby="changePassLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form id="changePassForm" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="changePassLabel">เปลี่ยนรหัสผ่านผู้ใช้งาน</h5>
                            <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <p>
                            ผู้ใช้งาน: <strong id="chgUserName"></strong>
                            </p>

                            <input type="hidden" name="id" id="chgFishermanId">

                            <div class="form-group">
                            <label for="newPassword">รหัสผ่านใหม่</label>
                            <input type="password" name="new_password" id="newPassword" class="form-control" required>
                            </div>

                            <div class="form-group">
                            <label for="confirmPassword">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" id="confirmPassword" class="form-control" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">ยกเลิก</button>
                            <button class="btn btn-primary" type="submit">บันทึก</button>
                        </div>
                        </form>
                    </div>
                    </div>
                    <!-- Edit password Modal -->

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
    <script src="../js/fvscis.js"></script>
                                        
            <script>
            // กดปุ่มรูปกุญแจ -> เปิด modal
            $(document).on('click', '.btn-change-pass', function () {
                const id = $(this).data('id');
                const username = $(this).data('username');

                $('#chgFishermanId').val(id);
                $('#chgUserName').text(username);
                $('#newPassword').val('');
                $('#confirmPassword').val('');

                $('#changePassModal').modal('show');
            });

            // submit ฟอร์มเปลี่ยนรหัสผ่าน
            $('#changePassForm').on('submit', function (e) {
                e.preventDefault();

                const pass1 = $('#newPassword').val().trim();
                const pass2 = $('#confirmPassword').val().trim();

                if (!pass1 || !pass2) {
                    Swal.fire('แจ้งเตือน', 'กรุณากรอกรหัสผ่านให้ครบ', 'warning');
                    return;
                }

                if (pass1 !== pass2) {
                    Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน', 'warning');
                    return;
                }

                if (pass1.length < 6) {
                    Swal.fire('แจ้งเตือน', 'กรุณากำหนดรหัสผ่านอย่างน้อย 6 ตัวอักษร', 'warning');
                    return;
                }

                $.ajax({
                    url: 'ajax/change_fisherman_password.php',
                    method: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(), // จะส่ง id + new_password
                    success: function (res) {
                        if (res.success) {
                            $('#changePassModal').modal('hide');
                            Swal.fire('สำเร็จ', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว', 'success');
                        } else {
                            Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });



            $(document).ready(function () {
                if (!$.fn.DataTable) {
                    console.error("DataTables plugin not loaded");
                    return;
                }

                var table = $('#dataTable').DataTable();

                // ✅ ช่องค้นหาด้านบน
                $('#topSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
                
                $('#formEditFisherman').on('submit', function (e) {
                    e.preventDefault();
                    const formData = $(this).serialize();

                    $.ajax({
                        url: 'ajax/update_fisherman.php',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function (response) {
                        if (response.success) {
                            Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ',
                            timer: 1500,
                            showConfirmButton: false
                            }).then(() => {
                            $('#modalEditFisherman').modal('hide');
                            location.reload();
                            });
                        } else {
                            Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถบันทึกได้',
                            text: response.message
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
                    }); // ← ปิด )
                    }); // ← ปิดทั้งหมด

            });
        </script>
        
        <script>
        function editFisherman(id) {
            $.ajax({
                url: 'ajax/get_fisherman.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(fisherman) {
                // วางค่าลงในฟอร์ม
                $('#edit-id').val(fisherman.id);
                $('#edit-full_name').val(fisherman.full_name);
                $('#edit-email').val(fisherman.email);
                $('#edit-citizen_id').val(fisherman.citizen_id);

                $('#edit-is_active').prop('checked', fisherman.is_active == 1);
                $('#edit-is_approved').prop('checked', fisherman.is_approved == 1);

                $('#edit-approved_by').val(fisherman.approved_by);
                $('#edit-approved_at').val(fisherman.approved_at);
                $('#edit-updated_by').val(fisherman.updated_by); // หรือใช้ session user_id ฝั่ง JS ถ้ามี
                $('#edit-updated_at').val(new Date().toISOString().slice(0, 19).replace('T', ' '));

                // แสดง modal
                $('#modalEditFisherman').modal('show');
                },
                error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถโหลดข้อมูลชาวประมงได้'
                });
                }
            });
            }


            function deleteFisherman(id) {
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
                            url: 'ajax/delete_fisherman.php',
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

