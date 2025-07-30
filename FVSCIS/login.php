<?php
require_once('../private/initialize.php');
if (isset($_GET['logout'])) {
    session_destroy();
}
if ($session->is_logged_in()) {
  Session::redirect_by_role($session->role);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>FVSCIS login</title>

  <!-- SB Admin 2 Styles -->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="css/sb-admin-2.min.css" rel="stylesheet" />

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" />

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>

<body class="bg-gradient-primary">
  <div class="container">
    <!-- Outer Row -->
    <div class="row justify-content-center">
      <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">ระบบสารสนเทศเพื่อการรับรองสุขอนามัยเรือประมง</h1>
                  </div>

                  <form class="user" action="logincheck.php" method="post">
                    <div class="form-group">
                      <input type="text" class="form-control form-control-user" name="username" placeholder="ชื่อผู้ใช้งาน" required />
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control form-control-user" name="password" placeholder="รหัสผ่าน" required />
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small">
                        <input type="checkbox" class="custom-control-input" id="customCheck" name="remember_me"/>
                        <label class="custom-control-label" for="customCheck">จดจำฉันไว้ในระบบ</label>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-user btn-block">เข้าสู่ระบบ</button>
                    <hr />
                    <a href="logingoogle.php" class="btn btn-google btn-user btn-block">
                      <i class="fab fa-google fa-fw"></i> เข้าสู่ระบบด้วย Google
                    </a>
                    <a href="loginfb.php" class="btn btn-facebook btn-user btn-block">
                      <i class="fab fa-facebook-f fa-fw"></i> เข้าสู่ระบบด้วย Facebook
                    </a>
                    <a href="loginline.php" class="btn btn-line btn-user btn-block">
                      <i class="fab fa-line fa-fw"></i> เข้าสู่ระบบด้วย LINE
                    </a>
                  </form>

                  <hr />
                  <div class="text-center">
                    <a class="small" href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">ลืมรหัสผ่าน?</a>
                  </div>
                  <div class="text-center">
                    <a class="small" href="logins3.php">ลงทะเบียนเข้าใช้ระบบ!</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Forgot Password Modal -->
      <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <form id="forgotPasswordForm" method="post">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="forgotPasswordModalLabel">ลืมรหัสผ่าน</h5>
              <button class="close" type="button" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <div class="modal-body">
              <div class="form-group mb-3">
                <label for="user_type">คุณเป็นใคร?</label>
                <select class="form-control" name="user_type" id="user_type" required>
                  <option value="">-- กรุณาเลือกประเภทผู้ใช้ --</option>
                  <option value="officer">เจ้าหน้าที่</option>
                  <option value="fisherman">ชาวประมง</option>
                </select>
              </div>

              <div class="form-group mb-3 d-none" id="officer_email_group">
                <label for="email">อีเมลที่ใช้ลงทะเบียน</label>
                <input type="email" name="email" class="form-control" placeholder="example@email.com">
              </div>

              <div class="form-group mb-3 d-none" id="fisherman_id_group">
                <label for="citizen_id">หมายเลขบัตรประจำตัวประชาชน</label>
                <input type="text" name="citizen_id" class="form-control" maxlength="13" placeholder="1234567890123">
              </div>
              <small class="form-text text-muted">
                หากคุณลืมชื่อผู้ใช้งาน กรุณาติดต่อเจ้าหน้าที่เพื่อขอความช่วยเหลือ
              </small>
              <div id="forgot-password-result" class="text-center mt-3"></div>

            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" class="btn btn-primary">ขอรหัสผ่านใหม่</button>
            </div>
          </div>
        </form>
      </div>
    </div>


  </div>

  <!-- JavaScript -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <!--<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>-->
  <!-- Bootstrap 5 JS Bundle (รวม Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>


  <!-- SweetAlert2 Alert Trigger -->
  <?php if ($session->message()) : ?>
  <script>
    Swal.fire({
      icon: 'warning',
      title: 'แจ้งเตือน',
      text: <?= json_encode($session->message()) ?>,
      confirmButtonText: 'ตกลง'
    });
  </script>
  <?php $session->clear_message(); ?>
  <?php endif; ?>
  <script>
document.addEventListener("DOMContentLoaded", function () {
  const userTypeSelect = document.getElementById('user_type');
  const officerGroup = document.getElementById('officer_email_group');
  const fishermanGroup = document.getElementById('fisherman_id_group');
  const form = document.getElementById('forgotPasswordForm');
  const resultBox = document.getElementById('forgot-password-result');

  // แสดง/ซ่อนฟิลด์ตามประเภทผู้ใช้
  if (userTypeSelect) {
    userTypeSelect.addEventListener('change', function () {
      const type = this.value;
      officerGroup.classList.toggle('d-none', type !== 'officer');
      fishermanGroup.classList.toggle('d-none', type !== 'fisherman');
    });
  }

  // จัดการ submit form
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      resultBox.innerHTML = '⏳ กำลังดำเนินการ...';
      resultBox.className = 'text-muted';

      fetch('forgot_password_process.php', {
        method: 'POST',
        body: new FormData(form)
      })
      .then(res => res.json())
      .then(data => {
        resultBox.innerHTML = data.message;
        resultBox.className = data.status === 'success'
          ? 'alert alert-success'
          : 'alert alert-danger';
      })
      .catch(() => {
        resultBox.innerHTML = 'เกิดข้อผิดพลาด กรุณาลองใหม่';
        resultBox.className = 'alert alert-danger';
      });
    });
  }
});
</script>



</body>
</html>

