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


    <!-- Officer Modal -->
<div class="modal fade" id="OfficerModal" tabindex="-1" aria-labelledby="OfficerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="OfficerModalLabel">สมัครสมาชิก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <form id="OfficerForm">
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
        <button type="submit" form="OfficerForm" class="btn btn-primary">สมัครสมาชิก</button>
      </div>
      
    </div>
  </div>
</div>    


<!-- Fisher Modal -->
<div class="modal fade" id="FishermanModal" tabindex="-1" aria-labelledby="FishermanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="FishermanModalLabel">สมัครสมาชิก</h5>
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

  </body>
</html>
