<?php
require_once('../private/initialize.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta name="description" content="" />
    <meta name="author" content="" />

    <title>FVSCIS Login S2</title>

    <!-- Custom fonts for this template-->
    <link
      href="vendor/fontawesome-free/css/all.min.css"
      rel="stylesheet"
      type="text/css"
    />
    <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet" />
  </head>

  
    <body class="d-flex align-items-center justify-content-center bg-gradient-primary" style="min-height: 100vh">
        <div class="container" style="max-width: 400px;">
          <div class="card o-hidden border-0 shadow-lg">
            <div class="card-body p-0">
              <div class="row">
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <button type="button" class="p-0 border-0 bg-transparent" style="box-shadow: none;" data-bs-toggle="modal" data-bs-target="#modalAddFisherman" title="ลงทะเบียนใช้งานสำหรับชาวประมง">
  <img src="img/fisher_man.png" class="img-fluid" style="max-height: 200px;" alt="Officer">
</button>    
                
                
                </div>
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <button type="button" class="p-0 border-0 bg-transparent" style="box-shadow: none;" data-bs-toggle="modal" data-bs-target="#modalAddOfficer" title="ลงทะเบียนใช้งานสำหรับเจ้าหน้าที่กรม">
  <img src="img/officer.png" class="img-fluid" style="max-height: 200px;" alt="Officer">
</button>
</div>
              </div>
              <div class="row">
                <div class="col-lg-12 d-flex align-items-center justify-content-center">
                กรุณาเลือกประเภทผู้ใช้งาน 
                </div>
              </div>
            </div>
          </div>
        </div>


    <!-- Officer Modal -->
    <div class="modal fade" id="modalAddOfficer" tabindex="-1" aria-labelledby="modalAddOfficerLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formAddOfficer" method="POST" action="ajax/save_officer_local.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="modalAddOfficerLabel">ลงทะเบียนเจ้าหน้าที่ (บัญชีภายใน)</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>

            <div class="modal-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="username" class="form-label">ชื่อผู้ใช้</label>
                  <input type="text" class="form-control" name="officer[username]" id="usernameOfficer" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="Officer_password" class="form-label">รหัสผ่าน</label>
                  <input type="password" class="form-control" name="officer[password]" id="Officer_password" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="Officer_confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                  <input type="password" class="form-control" name="officer[confirm_password]" id="Officer_confirm_password" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="full_name" class="form-label">ชื่อ - นามสกุล</label>
                  <input type="text" class="form-control" name="officer[full_name]" id="full_name" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="position" class="form-label">ตำแหน่ง</label>
                  <input type="text" class="form-control" name="officer[position]" id="position">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">อีเมล</label>
                  <input type="email" class="form-control" name="officer[email]" id="email">
                </div>
                
                <?php
                // ส่วน HTML + PHP
                $Departmentgroups = DepartmentGroup::find_all();
                ?>
                <div class="mb-3">
                  <label for="department_group" class="form-label">เลือกกลุ่มหน่วยงาน</label>
                  <select class="form-control" name="officer[departments_group]" id="department_group" required>
                    <option value="" selected data-default>select department group</option>
                    <?php foreach ($Departmentgroups as $Departmentgroup): ?>
                      <option value="<?= $Departmentgroup->id ?>"><?= htmlspecialchars($Departmentgroup->name) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="department_id" class="form-label">เลือกหน่วยงาน</label>
                  <select class="form-control" name="officer[departments_id]" id="departments_id" required>
                    <option value="" selected data-default>select department</option>
                  </select>
                </div>
                
                <div class="col-md-6">
                  <label for="edit_usertype_id" class="form-label">สิทธิ์การใช้งาน</label>
                  <?php $UserTypes = UserType::find_all();?>
                  <select class="form-select" name="officer[usertype_id]" id="edit_usertype_id">
                      <option value="">-- กรุณาเลือกกลุ่มหน่วยงาน --</option>
                      <?php foreach($UserTypes as $UserType): 
                        if($UserType->id != 5 && $UserType->id != 1){
                      ?>

                          <option value="<?php echo $UserType->id; ?>"><?php echo $UserType->name_th; ?></option>
                      <?php 
                        }
                        endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">บันทึก</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
          </div>
        </form>
      </div>
    </div>
   


      <!-- Fisher Modal -->
      <div class="modal fade" id="modalAddFisherman" tabindex="-1" aria-labelledby="modalAddFishermanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <form id="formAddFisherman" method="POST" action="ajax/save_fisherman_local.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalAddFishermanLabel">ลงทะเบียนชาวประมง (บัญชีภายใน)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="username" class="form-label">ชื่อผู้ใช้</label>
                  <input type="text" class="form-control" name="fisherman[username]" id="usernameFisherman" required>
                </div>

                <div class="mb-3">
                  <label for="Fisherman_password" class="form-label">รหัสผ่าน</label>
                  <input type="password" class="form-control" name="fisherman[password]" id="Fisherman_password" required>
                </div>

                <div class="mb-3">
                  <label for="Fisherman_confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                  <input type="password" class="form-control" name="fisherman[confirm_password]" id="Fisherman_confirm_password" required>
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">อีเมล</label>
                  <input type="email" class="form-control" name="fisherman[email]" id="email">
                </div>

                <div class="mb-3">
                  <label for="citizen_id" class="form-label">หมายเลขบัตรประชาชน</label>
                  <input type="text" class="form-control" name="fisherman[citizen_id]" id="citizen_id" required>
                </div>
              </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              </div>
            </div>
          </form>
        </div>
      </div>



    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
          tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl)
          })
        });
      </script>    
      

      <script>
      $(document).ready(function () {
        $('#department_group').on('change', function () {
          const groupId = $(this).val();
          $('#departments_id').html('<option value="">loading...</option>');
          if (groupId) {
            $.ajax({
              url: 'ajax/get_departments_by_group.php',
              method: 'POST',
              data: { group_id: groupId },
              dataType: 'json',
              success: function (res) {
                let options = '<option value="" selected data-default>select department</option>';
                if (res.length > 0) {
                  res.forEach(function (dep) {
                    options += `<option value="${dep.id}">${dep.name}</option>`;
                  });
                }
                $('#departments_id').html(options);
              },
              error: function () {
                $('#departments_id').html('<option value="">error loading departments</option>');
              }
            });
          } else {
            $('#departments_id').html('<option value="" selected data-default>select department</option>');
          }
        });
      });
      </script>

      <script>
        $(document).ready(function () {
          $('#usernameFisherman').on('blur', function () {
            const username = $(this).val().trim();
            if (username === '') return;

            $.ajax({
              url: 'ajax/check_fisherman_username.php',
              type: 'POST',
              data: { username: username },
              dataType: 'json',
              success: function (res) {
                if (!res.available) {
                  Swal.fire({
                    icon: 'error',
                    title: 'ชื่อผู้ใช้ซ้ำ',
                    text: 'ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว กรุณาเลือกชื่ออื่น'
                  });
                  $('#username').val('').focus();
                }
              },
              error: function () {
                Swal.fire({
                  icon: 'error',
                  title: 'ข้อผิดพลาด',
                  text: 'ไม่สามารถตรวจสอบชื่อผู้ใช้ได้'
                });
              }
            });
          });
        });
      </script>

       <script>
        $(document).ready(function () {
          $('#usernameOfficer').on('blur', function () {
            const username = $(this).val().trim();
            if (username === '') return;

            $.ajax({
              url: 'ajax/check_Officer_username.php',
              type: 'POST',
              data: { username: username },
              dataType: 'json',
              success: function (res) {
                if (!res.available) {
                  Swal.fire({
                    icon: 'error',
                    title: 'ชื่อผู้ใช้ซ้ำ',
                    text: 'ชื่อผู้ใช้นี้ถูกใช้ไปแล้ว กรุณาเลือกชื่ออื่น'
                  });
                  $('#username').val('').focus();
                }
              },
              error: function () {
                Swal.fire({
                  icon: 'error',
                  title: 'ข้อผิดพลาด',
                  text: 'ไม่สามารถตรวจสอบชื่อผู้ใช้ได้'
                });
              }
            });
          });
        });
      </script>
      <script>
      $(document).ready(function () {
        $('#formAddFisherman, #formAddOfficer').on('submit', function (e) {
          const pass = $(this).find('#password').val();
          const confirm = $(this).find('#confirm_password').val();

          if (pass !== confirm) {
            e.preventDefault();
            Swal.fire({
              icon: 'warning',
              title: 'รหัสผ่านไม่ตรงกัน',
              text: 'กรุณายืนยันรหัสผ่านให้ตรงกับรหัสผ่านหลัก'
            });
            return false;
          }
        });
      });
    </script>

    <script>
        $(document).ready(function () {
          $('#formAddFisherman').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const password = $('#Fisherman_password').val();
            const confirm = $('#Fisherman_confirm_password').val();

            if (password !== confirm) {
              Swal.fire({
                icon: 'warning',
                title: 'รหัสผ่านไม่ตรงกัน',
                text: 'กรุณายืนยันรหัสผ่านให้ตรงกัน'
              });
              return;
            }

            const formData = form.serialize();
            console.log(formData)
            $.ajax({
              url: 'ajax/save_fisherman_local.php',
              type: 'POST',
              data: formData,
              dataType: 'json',
              success: function (response) {
                if (response.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'ลงทะเบียนสำเร็จ',
                    text: 'เพิ่มบัญชีเจ้าหน้าที่เรียบร้อยแล้ว กรุณารอการอนุมัติจากเจ้าหน้าที่',
                    timer: 2000,
                    showConfirmButton: false
                  }).then(() => {
                    $('#modalAddFisherman').modal('hide');
                    form.trigger('reset');
                    window.location.href = 'login.php';
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
                  title: 'ข้อผิดพลาด',
                  text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้'
                });
              }
            });
          });
        });
      </script>


        <script>
          $(document).ready(function () {
            $('#formAddOfficer').on('submit', function (e) {
              e.preventDefault();

              const pass = $('#Officer_password').val();
              const confirm = $('#Officer_confirm_password').val();

              if (pass !== confirm) {
                Swal.fire({
                  icon: 'warning',
                  title: 'รหัสผ่านไม่ตรงกัน',
                  text: 'กรุณายืนยันรหัสผ่านเจ้าหน้าที่ให้ตรงกัน'
                });
                return;
              }

              const formData = $(this).serialize();
              console.log(formData)
              $.ajax({
                url: 'ajax/save_officer_local.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (response) {
                  if (response.success) {
                    Swal.fire({
                      icon: 'success',
                      title: 'ลงทะเบียนสำเร็จ',
                      text: 'เพิ่มบัญชีเจ้าหน้าที่เรียบร้อยแล้ว กรุณารอการอนุมัติจากเจ้าหน้าที่'
                    }).then(() => {
                      $('#modalAddOfficer').modal('hide');
                      $('#formAddOfficer')[0].reset();
                      window.location.href = 'login.php';
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
                    title: 'ข้อผิดพลาด',
                    text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                  });
                }
              });
            });
          });
        </script>


  </body>
</html>
