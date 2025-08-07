<?php
require_once('../../private/initialize.php');
include("../../private/shared/headeradmin.php");
include("../../private/shared/sidebaradmin.php");
include("../../private/shared/topbaradmin.php");
$Departments = Department::find_all();
?>

<!-- Begin Page Content -->
<div class="container-fluid">
<h2 class="h3 mb-2 text-gray-800">Tables</h2>
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <!-- ปุ่ม Add -->
                                <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus"></i> เพิ่มข้อมูล
                                </button>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr style="font-size: 14px;">
                                        <th>ลำดับ</th>
                                        <th>ชื่อหน่วยงาน</th>
                                        <th>หน่วยงานหลัก</th>
                                        <th>หน่วยงานดูแลข้อมูล</th>
                                        <th>เลขที่</th>
                                        <th>อาคาร</th>
                                        <th>ซอย</th>
                                        <th>หมู่ที่</th>
                                        <th>ถนน</th>
                                        <th>แขวง/ตำบล</th>
                                        <th>อำเภอ/เขต</th>
                                        <th>จังหวัด</th>
                                        <th>รหัสไปรษณีย์</th>
                                        <th>เบอร์โทรศัพท์</th>
                                        <th>เบอร์โทรสาร</th>
                                        <th>email</th>
                                        <th>หมายเหตุ</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr style="font-size: 14px;">
                                        <th>ลำดับ</th>
                                        <th>ชื่อหน่วยงาน</th>
                                        <th>หน่วยงานหลัก</th>
                                        <th>หน่วยงานดูแลข้อมูล</th>
                                        <th>เลขที่</th>
                                        <th>อาคาร</th>
                                        <th>ซอย</th>
                                        <th>หมู่ที่</th>
                                        <th>ถนน</th>
                                        <th>แขวง/ตำบล</th>
                                        <th>อำเภอ/เขต</th>
                                        <th>จังหวัด</th>
                                        <th>รหัสไปรษณีย์</th>
                                        <th>เบอร์โทรศัพท์</th>
                                        <th>เบอร์โทรสาร</th>
                                        <th>email</th>
                                        <th>หมายเหตุ</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php  
                                    $Departments = Department::find_all();
                                    foreach($Departments as $Department) { ?> 
                                    <tr style="font-size: 14px;">
                                   <td class="text-center align-middle">
                                    <?php echo h($Department->id); ?>
                                    <div class="d-flex flex-column align-items-center mt-2" style="gap: 10px;">
                                        <!-- ปุ่ม Edit -->
                                        <button class="btn btn-primary btn-sm" title="แก้ไข"
                                                style="width: 35px; height: 35px;"
                                                onclick="editDepartment(<?php echo $Department->id; ?>)">
                                        <i class="fas fa-edit text-white"></i>
                                        </button>

                                        <!-- ปุ่ม Delete -->
                                        <button class="btn btn-danger btn-sm" onclick="deleteDepartment(<?php echo $Department->id; ?>)" title="ลบ" style="width: 35px; height: 35px;">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </div>
                                    </td>
                                    <td><?php echo h($Department->name); ?></td>
                                    <td><?php 
                                    $department_groups = DepartmentGroup::find_by_id($Department->parent_department);
                                    if ($department_groups) {
                                        echo h($department_groups->name);
                                    } else {
                                        echo '-'; 
                                    }
                                    ?></td>
                                    <td><?php 
                                    $department_groups = DepartmentGroup::find_by_id($Department->data_owner_id);
                                    if ($department_groups) {
                                        echo h($department_groups->name);
                                    } else {
                                        echo '-';
                                    }
                                    ?></td>
                                    <td><?php echo h($Department->address_no); ?></td>
                                    <td><?php echo h($Department->building); ?></td>
                                    <td><?php echo h($Department->alley); ?></td>
                                    <td><?php echo h($Department->village_no); ?></td>
                                    <td><?php echo h($Department->road); ?></td>
                                    <td>
                                    <?php
                                        $tumbon = Tambon::find_by_id($Department->subdistrict);
                                        if ($tumbon) {
                                            echo h($tumbon->name);
                                        } else {
                                            echo 'error';
                                        }
                                    ?>
                                    </td>
                                    <td>
                                    <?php
                                        $Amphur = Amphur::find_by_id($Department->district);
                                        if ($Amphur) {
                                            echo h($Amphur->name);
                                        } else {
                                            echo 'error';
                                        }
                                    ?>
                                    </td>
                                    <td>
                                    <?php
                                        $Province = Province::find_by_id($Department->province);
                                        if ($Province) {
                                            echo h($Province->name);
                                        } else {
                                            echo 'error';
                                        }
                                    ?>
                                    </td>
                                    <td><?php echo h($Department->postal_code); ?></td>
                                    <td><?php echo h($Department->phone); ?></td>
                                    <td><?php echo h($Department->fax); ?></td>
                                    <td><?php echo h($Department->email); ?></td>
                                    <td><?php echo h($Department->note); ?></td>
                                    </tr> <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <!-- Modal: เพิ่มหน่วยงาน -->
                    <?php
                    require_once('../../private/initialize.php');
                    $provinces = Province::find_all_coastal();
                    $department_groups = DepartmentGroup::find_all();
                    ?>

                    <!-- Modal: เพิ่มหน่วยงาน -->
                    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                        <form id="addForm" action="add_department.php" method="post">
                            <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">เพิ่มหน่วยงานใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>
                            <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                <div class="mb-3"><label>ชื่อหน่วยงาน</label><input type="text" class="form-control" name="department[name]" required></div>
                                <div class="mb-3">
                                    <label for="parent_department_id">กลุ่มหน่วยงาน</label>
                                    <select name="department[parent_department]" class="form-select">
                                    <option value="">-- กรุณาเลือกกลุ่ม --</option>
                                    <?php foreach($department_groups as $group): ?>
                                        <option value="<?php echo h($group->id); ?>"><?php echo h($group->name); ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3"><label>หน่วยงานดูแลข้อมูล</label>
                                <select name="department[data_owner_id]" id="edit-data_owner_id" class="form-select">
                                    <option value="">-- เลือกหน่วยงานดูแลข้อมูล --</option>
                                    <option value="2">ศูนย์วิจัยและพัฒนาประมงทะเลระยอง</option>
                                    <option value="4">ศูนย์วิจัยและพัฒนาประมงทะเลสมุทรปราการ</option>
                                    <option value="5">ศูนย์วิจัยและพัฒนาประมงทะเลชุมพร</option>
                                    <option value="6">ศูนย์วิจัยและพัฒนาประมงทะเลสงขลา</option>
                                    <option value="7">ศูนย์วิจัยและพัฒนาประมงทะเลภูเก็ต</option>
                                    <option value="8">ศูนย์วิจัยและพัฒนาประมงทะเลระนอง</option>
                                    <option value="9">ศูนย์วิจัยและพัฒนาประมงทะเลสตูล</option>
                                    <option value="33">ศูนย์วิจัยและพัฒนาประมงทะเลนราธิวาส</option>
                                </select>
                                </div>
                                <div class="mb-3"><label>เลขที่</label><input type="text" class="form-control" name="department[address_no]"></div>
                                <div class="mb-3"><label>อาคาร</label><input type="text" class="form-control" name="department[building]"></div>
                                <div class="mb-3"><label>ซอย</label><input type="text" class="form-control" name="department[alley]"></div>
                                <div class="mb-3"><label>หมู่ที่</label><input type="text" class="form-control" name="department[village_no]"></div>
                                <div class="mb-3"><label>ถนน</label><input type="text" class="form-control" name="department[road]"></div>
                                
                                </div>
                                
                                <div class="col-md-6">
                                <div class="mb-3">
                                    <label>จังหวัด</label>
                                    <select name="department[province]" id="province" class="form-select">
                                    <option value="">-- เลือกจังหวัด --</option>
                                    <?php foreach($provinces as $province): ?>
                                        <option value="<?php echo h($province->id); ?>"><?php echo h($province->name); ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>อำเภอ/เขต</label>
                                    <select name="department[district]" id="district" class="form-select">
                                    <option value="">-- เลือกอำเภอ --</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>แขวง/ตำบล</label>
                                    <select name="department[subdistrict]" id="subdistrict" class="form-select">
                                    <option value="">-- เลือกตำบล --</option>
                                    </select>
                                </div>
                                <div class="mb-3"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" name="department[postal_code]"></div>
                                <div class="mb-3"><label>เบอร์โทรศัพท์</label><input type="text" class="form-control" name="department[phone]"></div>
                                <div class="mb-3"><label>เบอร์โทรสาร</label><input type="text" class="form-control" name="department[fax]"></div>
                                <div class="mb-3"><label>อีเมล</label><input type="email" class="form-control" name="department[email]"></div>
                                <div class="mb-3"><label>หมายเหตุ</label><textarea class="form-control" name="department[note]" rows="2"></textarea></div>
                                </div>
                            </div>
                            </div>
                            <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    <form id="editForm" action="update_department.php" method="post">
                        <div class="modal-header">
                        <h5 class="modal-title">แก้ไขข้อมูลหน่วยงาน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>
                        <div class="modal-body">
                        <input type="hidden" name="department[id]" id="edit-id">
                        <div class="row">
                            <div class="col-md-6">
                            <div class="mb-3"><label>ชื่อหน่วยงาน</label><input type="text" class="form-control" name="department[name]" id="edit-name"></div>
                            <div class="mb-3">
                            <label for="edit-department_group_id">กลุ่มหน่วยงาน</label>
                            <select name="department[parent_department]" id="edit-department_group_id" class="form-select">
                                <option value="">-- กรุณาเลือกกลุ่ม --</option>
                                <!-- คุณสามารถเติม option จากฐานข้อมูลได้ที่นี่ -->
                                <!-- ตัวอย่าง -->
                                <!-- <option value="1">กลุ่มตรวจสอบแหล่งประมง</option> -->
                                <!-- <option value="2">กลุ่มวิจัยและพัฒนา</option> -->
                            </select>
                            </div>
                            <div class="mb-3"><label>หน่วยงานดูแลข้อมูล</label>
                                <select name="department[data_owner_id]" id="edit-data_owner_id" class="form-select">
                                <option value="">-- เลือกหน่วยงานดูแลข้อมูล --</option>
                                <option value="2">ศูนย์วิจัยและพัฒนาประมงทะเลระยอง</option>
                                <option value="4">ศูนย์วิจัยและพัฒนาประมงทะเลสมุทรปราการ</option>
                                <option value="5">ศูนย์วิจัยและพัฒนาประมงทะเลชุมพร</option>
                                <option value="6">ศูนย์วิจัยและพัฒนาประมงทะเลสงขลา</option>
                                <option value="7">ศูนย์วิจัยและพัฒนาประมงทะเลภูเก็ต</option>
                                <option value="8">ศูนย์วิจัยและพัฒนาประมงทะเลระนอง</option>
                                <option value="9">ศูนย์วิจัยและพัฒนาประมงทะเลสตูล</option>
                                <option value="33">ศูนย์วิจัยและพัฒนาประมงทะเลนราธิวาส</option>
                            </select>
                            </div>
                            <div class="mb-3"><label>เลขที่</label><input type="text" class="form-control" name="department[address_no]" id="edit-address_no"></div>
                            <div class="mb-3"><label>อาคาร</label><input type="text" class="form-control" name="department[building]" id="edit-building"></div>
                            <div class="mb-3"><label>ซอย</label><input type="text" class="form-control" name="department[alley]" id="edit-alley"></div>
                            <div class="mb-3"><label>หมู่ที่</label><input type="text" class="form-control" name="department[village_no]" id="edit-village_no"></div>
                            <div class="mb-3"><label>ถนน</label><input type="text" class="form-control" name="department[road]" id="edit-road"></div> 
                            </div>
                            <div class="col-md-6">
                            <!-- จังหวัด -->
                            <div class="mb-3">
                            <label>จังหวัด</label>
                            <select name="department[province]" id="edit-province" class="form-select">
                                <option value="">-- เลือกจังหวัด --</option>
                                <?php foreach (Province::find_all_coastal() as $province): ?>
                                <?php ////error_log("province id={$province->id} name={$province->name}"); ?>
                                <option value="<?php echo h($province->id); ?>"><?php echo h($province->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            </div>

                            <!-- อำเภอ -->
                            <div class="mb-3">
                            <label>อำเภอ/เขต</label>
                            <select name="department[district]" id="edit-district" class="form-select">
                                <option value="">-- เลือกอำเภอ --</option>
                            </select>
                            </div>

                            <!-- ตำบล -->
                            <div class="mb-3">
                            <label>แขวง/ตำบล</label>
                            <select name="department[subdistrict]" id="edit-subdistrict" class="form-select">
                                <option value="">-- เลือกตำบล --</option>
                            </select>
                            </div>
                            <div class="mb-3"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" name="department[postal_code]" id="edit-postal_code"></div>
                            <div class="mb-3"><label>เบอร์โทรศัพท์</label><input type="text" class="form-control" name="department[phone]" id="edit-phone"></div>
                            <div class="mb-3"><label>เบอร์โทรสาร</label><input type="text" class="form-control" name="department[fax]" id="edit-fax"></div>
                            <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="department[email]" id="edit-email"></div>
                            <div class="mb-3"><label>หมายเหตุ</label><textarea class="form-control" name="department[note]" id="edit-note"></textarea></div>
                            
                        </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                        </div>
                    </form>
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
                // ✅ เริ่มต้น DataTable
                var table = $('#dataTable').DataTable();
                $('#topSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });

                $('#editForm').on('submit', function (e) {
                    e.preventDefault();

                    $.ajax({
                        url: 'ajax/update_department.php',
                        type: 'POST',
                        data: $(this).serialize(), // ใช้ name="department[...]" ได้เลย
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'บันทึกข้อมูลสำเร็จ',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'เกิดข้อผิดพลาด',
                                    text: result.message
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'ไม่สามารถติดต่อเซิร์ฟเวอร์',
                                text: error
                            });
                        }
                    });
                });


            });

            function deleteDepartment(id) {
                Swal.fire({
                    title: 'คุณแน่ใจหรือไม่?',
                    text: 'การลบหน่วยงานนี้จะไม่สามารถย้อนกลับได้',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'ajax/delete_department.php',
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
            
            function editDepartment(id) {
                $.ajax({
                    url: 'ajax/get_department.php',
                    type: 'GET',
                    data: { id: id },
                    dataType: 'json',
                    success: function (data) {
                        $('#edit-id').val(data.id);
                        $('#edit-name').val(data.name);
                        $('#edit-address_no').val(data.address_no);
                        $('#edit-building').val(data.building);
                        $('#edit-alley').val(data.alley);
                        $('#edit-village_no').val(data.village_no);
                        $('#edit-road').val(data.road);
                        $('#edit-postal_code').val(data.postal_code);
                        $('#edit-phone').val(data.phone);
                        $('#edit-fax').val(data.fax);
                        $('#edit-email').val(data.email);
                        $('#edit-note').val(data.note);
                        $('#edit-data_owner_id').val(data.data_owner_id);
                        // โหลดจังหวัด
                        $.ajax({
                            url: 'ajax/get_provinces.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function (provinces) {
                                const $provinceSelect = $('#edit-province');
                                $provinceSelect.empty();
                                $provinceSelect.append('<option value="">-- เลือกจังหวัด --</option>');

                                $.each(provinces, function (index, province) {
                                    $provinceSelect.append(
                                        '<option value="' + province.id + '"' +
                                        (province.id == data.province ? ' selected' : '') +
                                        '>' + province.name + '</option>'
                                    );
                                });

                                // โหลดอำเภอ
                                $.ajax({
                                    url: 'ajax/get_districts.php',
                                    type: 'GET',
                                    data: { province_id: data.province },
                                    dataType: 'html',
                                    success: function (districtOptions) {
                                        const $districtSelect = $('#edit-district');
                                        $districtSelect.html(districtOptions);
                                        $districtSelect.val(data.district);

                                        // โหลดตำบล
                                        $.ajax({
                                            url: 'ajax/get_subdistricts.php',
                                            type: 'GET',
                                            data: { district_id: data.district },
                                            dataType: 'html',
                                            success: function (subdistrictOptions) {
                                                const $subdistrictSelect = $('#edit-subdistrict');
                                                $subdistrictSelect.html(subdistrictOptions);
                                                $subdistrictSelect.val(data.subdistrict);
                                            }
                                        });
                                    }
                                });
                            }
                        });

                        // โหลดกลุ่มหน่วยงาน
                        $.ajax({
                            url: 'ajax/get_department_groups.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function (groups) {
                                const $select = $('#edit-department_group_id');
                                $select.empty();
                                $select.append('<option value="">-- กรุณาเลือกกลุ่ม --</option>');

                                $.each(groups, function (index, group) {
                                    $select.append(
                                        '<option value="' + group.id + '"' +
                                        (group.id == data.parent_department ? ' selected' : '') +
                                        '>' + group.name + '</option>'
                                    );
                                });
                            },
                            error: function () {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'โหลดกลุ่มหน่วยงานไม่สำเร็จ',
                                    text: 'ไม่สามารถดึงข้อมูลกลุ่มหน่วยงานได้'
                                });
                            }
                        });

                        $('#editModal').modal('show');
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถโหลดข้อมูล',
                            text: 'เกิดข้อผิดพลาดขณะเรียกข้อมูลหน่วยงาน'
                        });
                    }
                });
            }
        </script>
       
       <script>
        $(document).ready(function() {
        $('#province').on('change', function () {
            var provinceID = $(this).val();
            $('#district').html('<option value="">-- รอสักครู่ --</option>');
            $('#subdistrict').html('<option value="">-- เลือกตำบล --</option>');

            if (provinceID !== '') {
            $.ajax({
                url: 'ajax/get_districts.php',
                type: 'GET',
                data: { province_id: provinceID },
                success: function (response) {
                $('#district').html(response);
                }
            });
            }
        });


        

        $('#addForm').on('submit', function (e) {
                e.preventDefault(); // ป้องกันฟอร์มส่งตามปกติ

                const formData = $(this).serialize(); // เก็บข้อมูลฟอร์มทั้งหมด

                $.ajax({
                    url: 'ajax/save_department.php', // ✅ ใช้ไฟล์เดียวกับ update
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'เพิ่มหน่วยงานสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload(); // หรือโหลดตารางใหม่
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
                            text: 'โปรดลองอีกครั้งภายหลัง'
                        });
                    }
                });
            });

        $('#district').on('change', function () {
            var districtID = $(this).val();
            $('#subdistrict').html('<option value="">-- รอสักครู่ --</option>');

            if (districtID !== '') {
            $.ajax({
                url: 'ajax/get_subdistricts.php',
                type: 'GET',
                data: { district_id: districtID },
                success: function (response) {
                $('#subdistrict').html(response);
                }
            });
            }
        });

        $('#edit-province').on('change', function () {
            const provinceId = $(this).val();
            $.get('ajax/get_districts.php', { province_id: provinceId }, function (data) {
                $('#edit-district').html(data);
                $('#edit-subdistrict').html('<option value="">-- เลือกตำบล --</option>');
            });
        });

        $('#edit-district').on('change', function () {
            const districtId = $(this).val();
            $.get('ajax/get_subdistricts.php', { district_id: districtId }, function (data) {
                $('#edit-subdistrict').html(data);
            });
        });

        });
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addModal = document.getElementById('addModal');
            addModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('addForm').reset();

                // เคลียร์ dropdown อำเภอและตำบล (เหลือเฉพาะตัวเลือกเริ่มต้น)
                document.querySelector('[name="district"]').innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
                document.querySelector('[name="subdistrict"]').innerHTML = '<option value="">-- เลือกตำบล --</option>';
            });
        });
        </script>

<?php
include("../../private/shared/footerall.php");
?>

