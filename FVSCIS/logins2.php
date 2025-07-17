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
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
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
                <button type="button" class="p-0 border-0 bg-transparent" style="box-shadow: none;" data-bs-toggle="modal" data-bs-target="#FishermanModal" title="ลงทะเบียนใช้งานสำหรับชาวประมง">
  <img src="img/fisher_man.png" class="img-fluid" style="max-height: 200px;" alt="Officer">
</button>    
                
                
                </div>
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <button type="button" class="p-0 border-0 bg-transparent" style="box-shadow: none;" data-bs-toggle="modal" data-bs-target="#OfficerModal" title="ลงทะเบียนใช้งานสำหรับเจ้าหน้าที่กรม">
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


        <!-- Fisherman Modal -->
<div class="modal fade" id="FishermanModal" tabindex="-1" aria-labelledby="FishermanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formFisherman">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="FishermanModalLabel">ลงทะเบียนชาวประมง</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="citizen_id" class="form-label">หมายเลขบัตรประชาชน</label>
            <input type="text" class="form-control" name="fisherman[citizen_id]" id="citizen_id" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">อีเมล (ไม่บังคับหากบันทึกจะมีการแจ้งข้อมูลผ่านทาง email)</label>
            <input type="email" class="form-control" name="fisherman[email]" id="email">
          </div>
          <input type="hidden" name="fisherman[username]" value="<?= $_SESSION['username'] ?>">
          <?php
          if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
              $firstChar = strtolower(substr($_SESSION['username'], 0, 1));

              if ($firstChar === 'g') {
                  echo '<input type="hidden" name="fisherman[google_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              } elseif ($firstChar === 'l') {
                  echo '<input type="hidden" name="fisherman[line_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              } elseif ($firstChar === 'f') {
                  echo '<input type="hidden" name="fisherman[facebook_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              }
          }
          ?>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </div>
    </form>
  </div>
</div>

    <!-- Officer Modal -->
<div class="modal fade" id="OfficerModal" tabindex="-1" aria-labelledby="OfficerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="OfficerModalLabel">ลงทะเบียนเข้าใช้งานระบบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="OfficerForm">
          
          <!-- Hidden Fields -->
          <input type="hidden" name="Officer[username]" value="<?= $_SESSION['username'] ?>">
          <?php
          if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
              $firstChar = strtolower(substr($_SESSION['username'], 0, 1));

              if ($firstChar === 'g') {
                  echo '<input type="hidden" name="Officer[google_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              } elseif ($firstChar === 'l') {
                  echo '<input type="hidden" name="Officer[line_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              } elseif ($firstChar === 'f') {
                  echo '<input type="hidden" name="Officer[facebook_id]" value="' . htmlspecialchars($_SESSION['user_id'], ENT_QUOTES, 'UTF-8') . '">';
              }
          }
          ?>

          
          <div class="mb-3">
            <label for="name" class="form-label">ชื่อ - นามสกุล</label>
            <input type="text" class="form-control" name="Officer[full_name]" id="name" required>
          </div>

          <div class="mb-3">
            <label for="position" class="form-label">ตำแหน่ง</label>
            <input type="text" class="form-control" name="Officer[position]" id="position" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" class="form-control" id="email" name = "Officer[email]" value="<?= $_SESSION['email'] ?>" required>
          </div>
          <?php
          // ส่วน HTML + PHP
          $Departmentgroups = DepartmentGroup::find_all();
          ?>
          <div class="mb-3">
            <label for="department_group" class="form-label">เลือกกลุ่มหน่วยงาน</label>
            <select class="form-control" name="Officer[departments_group]" id="department_group" required>
              <option value="" selected data-default>select department group</option>
              <?php foreach ($Departmentgroups as $Departmentgroup): ?>
                <option value="<?= $Departmentgroup->id ?>"><?= htmlspecialchars($Departmentgroup->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="department_id" class="form-label">เลือกหน่วยงาน</label>
            <select class="form-control" name="Officer[departments_id]" id="department_id" required>
              <option value="" selected data-default>select department</option>
            </select>
          </div>

          

          <div class="mb-3">
            <label for="UserType" class="form-label">สิทธิ์การใช้งาน</label>
            <select class="form-control" name="Officer[usertype_id]" id="UserType"  required>
              <option value="" selected data-default>select UserType</option>  
              <option value="2">ผู้ดูแลระบบส่วนกลาง</option>
              <option value="3">เจ้าหน้าที่หน่วยงานตรวจ</option>
              <option value="4">ผู้มีอำนาจลงนาม</option>
            </select>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" form="OfficerForm" class="btn btn-primary">สมัครเข้าใช้งาน</button>
      </div>

    </div>
  </div>
</div>



<!-- Fisher Modal -->
<div class="modal fade" id="FishermanModal" tabindex="-1" aria-labelledby="FishermanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="FishermanModalLabel">สมัครเข้าใช้งานระบบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <form id="FishermanForm">
          <div class="mb-3">
            <label for="name" class="form-label">ชื่อ - นามสกุล</label>
            <input type="text" class="form-control" id="name" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" class="form-control" id="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" class="form-control" id="password" required>
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">ยืนยันรหัสผ่าน</label>
            <input type="password" class="form-control" id="confirmPassword" required>
          </div>
        </form>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" form="FishermanForm" class="btn btn-primary">สมัครสมาชิก</button>
      </div>
      
    </div>
  </div>
</div>  

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>   
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
          $('#department_id').html('<option value="">loading...</option>');
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
                $('#department_id').html(options);
              },
              error: function () {
                $('#department_id').html('<option value="">error loading departments</option>');
              }
            });
          } else {
            $('#department_id').html('<option value="" selected data-default>select department</option>');
          }
        });
      });
      </script>
      
        <script>
        $(document).ready(function() {


          $('#formFisherman').on('submit', function (e) {
            e.preventDefault();
            const formData = $(this).serialize();
            Swal.fire({
              title: 'กำลังดำเนินการ...',
              html: 'โปรดรอสักครู่',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            $.ajax({
              url: 'ajax/save_fisherman.php',
              type: 'POST',
              data: formData,
              dataType: 'json',
              success: function (response) {
                if (response.success) {
                  Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: 'บันทึกข้อมูลเรียบร้อยแล้ว กรุณารอการอนุมัติ',
                    confirmButtonText: 'ตกลง'
                  }).then(() => {
                    window.location.href = 'login.php';
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: response.message,
                    confirmButtonText: 'ปิด'
                  }).then(() => {
                    window.location.href = 'login.php';
                  });
                }
              },
              error: function () {
                Swal.fire({
                  icon: 'error',
                  title: 'ข้อผิดพลาดของระบบ',
                  text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้',
                  confirmButtonText: 'ตกลง'
                });
              }
            });
          });

          //end $('#formFisherman').on('submit', function (e) {


          $('#OfficerForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
              url: 'ajax/save_officer.php',
              type: 'POST',
              data: $(this).serialize(),
              dataType: 'json',
              success: function(response) {
                if(response.status === 'success') {
                  Swal.fire({
                    icon: 'success',
                    title: 'สมัครสมาชิกสำเร็จ',
                    text: response.message,
                    confirmButtonText: 'ตกลง'
                  }).then(() => {
                    location.href = 'login.php';
                  });
                } else {
                  Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: response.message,
                  });
                }
              },
              error: function(xhr, status, error) {
                Swal.fire({
                  icon: 'error',
                  title: 'ข้อผิดพลาดในการส่งข้อมูล',
                  text: error,
                });
              }
            });

          });

        });
        </script>

  </body>
</html>
