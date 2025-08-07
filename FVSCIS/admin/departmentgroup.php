<?php
require_once('../../private/initialize.php');
include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
<h2 class="h3 mb-2 text-gray-800">Tables</h2>
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <!-- ปุ่ม Add -->
                                <button class="btn btn-success mb-3" onclick="addDepartmentgroup()">
                                    <i class="fas fa-plus"></i> เพิ่มข้อมูล
                                </button>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr style="font-size: 14px;">
                                        <th></th>
                                        <th></th>
                                        <th>ลำดับ</th>
                                        <th>ชื่อหน่วยงาน (กลุ่ม)</th>
                                        <th>หมายเหตุ</th>
                                        <th>ผู้มีอำนาจลงนาม</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr style="font-size: 14px;">
                                        <th></th>
                                        <th></th>
                                        <th>ลำดับ</th>
                                        <th>ชื่อหน่วยงาน (กลุ่ม)</th>
                                        <th>หมายเหตุ</th>
                                        <th>ผู้มีอำนาจลงนาม</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php  
                                    $DepartmentGroups = DepartmentGroup::find_all();
                                    foreach($DepartmentGroups as $DepartmentGroup) { ?> 
                                    <tr style="font-size: 14px;">
                                   <td class="text-center align-middle">
                                    <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ปุ่ม Edit -->
                                        <button class="btn btn-primary btn-sm" title="แก้ไข"
                                                style="width: 30px; height: 30px;"
                                                onclick="editDepartmentgroup(<?php echo $DepartmentGroup->id; ?>)">
                                        <i class="fas fa-edit text-white"></i>
                                        </button>
                                    </div>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ปุ่ม Delete -->
                                        <button class="btn btn-danger btn-sm" title="ลบ"
                                                onclick="confirmDeleteDepartmentGroup(<?php echo $DepartmentGroup->id; ?>)"
                                                style="width: 30px; height: 30px;">
                                        <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </div>
                                    </td>
                                    <td class="text-center align-middle">    
                                        <?php echo h($DepartmentGroup->id); ?>
                                    </td>
                                    <td><?php echo h($DepartmentGroup->name); ?></td>
                                    <td><?php echo h($DepartmentGroup->note); ?></td>
                                    <?php
                                    $signer = Officer::find_by_id($DepartmentGroup->officer_id);
                                    ?>
                                    <td><?= h($signer->full_name ?? '-') ?></td>
                                    </tr> <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <!-- /.container-fluid -->
                 <!-- Modal: Department Group -->
                    <div class="modal fade" id="departmentGroupModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <form id="departmentGroupForm">
                            <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="departmentGroupModalLabel">เพิ่มกลุ่มหน่วยงาน</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                            <input type="hidden" name="DepartmentGroup[id]" id="dg-id" />

                            <div class="mb-3">
                                <label for="dg-name" class="form-label">ชื่อกลุ่มหน่วยงาน</label>
                                <input type="text" class="form-control" name="DepartmentGroup[name]" id="dg-name" required>
                            </div>

                            <div class="mb-3">
                                <label for="dg-note" class="form-label">หมายเหตุ</label>
                                <textarea class="form-control" name="DepartmentGroup[note]" id="dg-note"></textarea>
                            </div>
                            <div class="mb-3">
                            <label for="dg-officer_id" class="form-label">ผู้มีอำนาจลงนาม</label>
                            <select class="form-select" name="DepartmentGroup[officer_id]" id="dg-officer_id">
                                <option value="">-- เลือกเจ้าหน้าที่ --</option>
                                <?php foreach (Officer::find_all_select_options_by_usertype() as $officer): ?>
                                <option value="<?= h($officer['id']) ?>"><?= h($officer['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            </div>
                            <div class="modal-footer">
                            <button type="submit" class="btn btn-success">บันทึก</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- Modal: Department Group -->

                    <!-- Delete Confirmation Modal -->
                    <div class="modal fade" id="deleteDepartmentGroupModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">ยืนยันการลบ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลกลุ่มหน่วยงานนี้?</p>
                            <input type="hidden" id="delete-departmentgroup-id" />
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="button" class="btn btn-danger" onclick="deleteDepartmentGroup()">ลบ</button>
                        </div>
                        </div>
                    </div>
                    </div>
                    <!-- Delete Confirmation Modal -->                    
</div>

  
<?php include("../../private/shared/footeradmin.php"); ?>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <script>
            $(document).ready(function () {
                // ✅ เริ่มต้น DataTable
                var table = $('#dataTable').DataTable();

                // ✅ ช่องค้นหาด้านบน
                $('#topSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
            });
            </script>  

            <script>
  // เปิด Modal แบบเพิ่มใหม่
  function addDepartmentgroup() {
    $('#departmentGroupModalLabel').text('เพิ่มกลุ่มหน่วยงาน');
    $('#departmentGroupForm')[0].reset();
    $('#dg-id').val('');
    $('#departmentGroupModal').modal('show');
  }


        // ลบ
        function confirmDeleteDepartmentGroup(id) {
        $('#delete-departmentgroup-id').val(id);
        $('#deleteDepartmentGroupModal').modal('show');
        }

        function deleteDepartmentGroup() {
        const id = $('#delete-departmentgroup-id').val();

        $.ajax({
            url: 'ajax/delete_department_group.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (res) {
            if (res.success) {
                Swal.fire({
                icon: 'success',
                title: 'ลบข้อมูลสำเร็จ',
                timer: 1500,
                showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('เกิดข้อผิดพลาด', res.message, 'error');
            }
            },
            error: function () {
            Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');
            }
        });
        }


  // เปิด Modal แบบแก้ไข
  function editDepartmentgroup(id) {
    $.ajax({
      url: 'ajax/get_department_group.php',
      type: 'GET',
      data: { id: id },
      dataType: 'json',
      success: function (data) {
        if (!data) {
          Swal.fire('เกิดข้อผิดพลาด', 'ไม่พบข้อมูลหน่วยงาน', 'error');
          return;
        }

        $('#departmentGroupModalLabel').text('แก้ไขกลุ่มหน่วยงาน');
        $('#dg-id').val(data.id);
        $('#dg-name').val(data.name);
        $('#dg-note').val(data.note);
        $('#dg-officer_id').val(data.officer_id);
        $('#departmentGroupModal').modal('show');
      },
      error: function () {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error');
      }
    });
  }

  // บันทึกข้อมูล Add/Edit
  $('#departmentGroupForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
      url: 'ajax/save_department_group.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (res) {
  console.log(res); // ✅ ดูผลลัพธ์จาก PHP (รวม errors ถ้ามี)

        if (res.success) {
                Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
                }).then(() => {
                location.reload();
                });
            } else {
                // ✅ แสดงข้อความผิดพลาดจาก PHP ถ้ามีรายละเอียด
                Swal.fire('เกิดข้อผิดพลาด', res.message + 
                (res.errors ? '\n' + res.errors.join('\n') : ''), 'error');
            }
            },
      error: function () {
        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
      }
    });
  });
</script>

<?php include("../../private/shared/footerall.php"); ?>