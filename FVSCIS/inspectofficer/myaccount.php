<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
if(!$Officer) { redirect_to('../login.php'); }
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>
<!-- Begin Page Content -->
<div class="container-fluid">

  <h1 class="h3 mb-4 text-gray-800">จัดการบัญชีผู้ใช้งาน (เจ้าหน้าที่)</h1>

  <div class="row">
    <!-- ข้อมูลบัญชี -->
    <div class="col-lg-4">
      <div class="card shadow mb-4">
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">ข้อมูลบัญชีผู้ใช้งาน</h6>
        </div>
        <div class="card-body">

          <!-- รูปโปรไฟล์ -->
          <div class="text-center mb-3">
            <?php
            $default_image = '../img/default-user.svg';
            $picture = $session->user_picture;

            // ถ้าเป็น URL (เริ่มด้วย http หรือ https)
            if (!empty($picture) && preg_match('/^https?:\/\//', $picture)) {
                $profile_image = $picture;
            }
            // ถ้าเป็นไฟล์ในระบบ
            else if (!empty($picture)) {
                $path = '../uploads/profile/' . basename($picture);
                $profile_image = file_exists($path) ? $path : $default_image;
            }
            // ถ้าไม่มีรูปเลย
            else {
                $profile_image = $default_image;
            }
            ?>
            <img id="profile_image_tag"
                 src="<?php echo $profile_image; ?>"
                 class="img-profile rounded-circle"
                 style="width:120px;height:120px;object-fit:cover;">

            <button type="button"
                    class="btn btn-sm btn-outline-primary d-block mx-auto mt-2"
                    data-bs-toggle="modal"
                    data-bs-target="#profileImageModal">
              เปลี่ยนรูปโปรไฟล์
            </button>
          </div>

          <div class="form-group">
            <label>ชื่อผู้ใช้งาน (Username)</label>
            <input type="text" class="form-control" value="<?php echo h($Officer->username); ?>" disabled>
          </div>

          <div class="form-group">
            <label>ชื่อ-นามสกุล</label>
            <input type="text" class="form-control" value="<?php echo h($Officer->full_name ?? ''); ?>" disabled>
          </div>

          <div class="form-group">
            <label>ตำแหน่ง</label>
            <input type="text" class="form-control" value="<?php echo h($Officer->position ?? ''); ?>" disabled>
          </div>

          <div class="form-group mb-0">
            <label>อีเมล</label>
            <input type="text" class="form-control" id="email" value="<?php echo h($Officer->email ?? ''); ?>" disabled>
          </div>

        </div>
      </div>
    </div>

    <!-- #region <div class="col-lg-8"> -->
    <div class="col-lg-8">
      <!-- #region เปลี่ยนรหัสผ่าน -->
      <div class="card shadow mb-4">  
        <div class="card-header py-3">
          <h6 class="m-0 font-weight-bold text-primary">เปลี่ยนรหัสผ่าน</h6>
        </div>
        <div class="card-body">

          <form id="form-change-password" autocomplete="off">
            <div class="form-group">
              <label>รหัสผ่านปัจจุบัน</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>

            <div class="form-group">
              <label>รหัสผ่านใหม่</label>
              <input type="password" name="new_password" class="form-control" required>
              <small class="form-text text-muted">
                รหัสผ่านควรมีอย่างน้อย 8 ตัวอักษร และประกอบด้วยตัวอักษรและตัวเลข
              </small>
            </div>

            <div class="form-group">
              <label>ยืนยันรหัสผ่านใหม่</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">บันทึกรหัสผ่านใหม่</button>
          </form>

        </div>
      </div>
      <!-- #endregion เปลี่ยนรหัสผ่าน -->

      <!-- #region กล่องเปลี่ยนอีเมล -->
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">เปลี่ยนอีเมล</h6>
        </div>
        <div class="card-body">

          <!-- แสดงอีเมลปัจจุบัน -->
          <div class="mb-3">
            <label class="font-weight-bold">อีเมลปัจจุบัน</label>
            <div>
              <span id="current-email-text">
                <?= h($Officer->email ?? '-'); ?>
              </span>
            </div>
            <small class="form-text text-muted">
              อีเมลนี้ใช้สำหรับกู้รหัสผ่านและรับการแจ้งเตือนจากระบบ
            </small>
          </div>

          <form id="form-change-email" autocomplete="off">
            <div class="form-group">
              <label>อีเมลใหม่</label>
              <input type="email" name="new_email" class="form-control" required>
              <small class="form-text text-muted">
                กรุณาระบุอีเมลที่คุณใช้งานจริง และตรวจสอบให้ถูกต้องก่อนบันทึก
              </small>
            </div>

            <div class="form-group">
              <label>รหัสผ่านปัจจุบัน</label>
              <input type="password" name="current_password" class="form-control" required>
              <small class="form-text text-muted">
                ใช้ยืนยันตัวตนก่อนการเปลี่ยนอีเมล
              </small>
            </div>

            <div id="change-email-alert" class="mt-2" style="display:none;"></div>

            <button type="submit" id="btn-change-email" class="btn btn-primary">
              บันทึกอีเมลใหม่
            </button>
          </form>

        </div>
      </div>
      <!-- #endregion กล่องเปลี่ยนอีเมล -->
    </div>
    <!-- #endregion <div class="col-lg-8"> --> 


  </div>

</div>
<!-- /.container-fluid -->

<!-- #region Modal เปลี่ยนรูปโปรไฟล์ -->
<div class="modal fade" id="profileImageModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="form-upload-profile" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">เปลี่ยนรูปโปรไฟล์</h5>
          <button class="close" type="button" data-bs-dismiss="modal"><span>×</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group mb-0">
            <label>เลือกรูปภาพ (ไฟล์ .jpg, .png ขนาดไม่เกิน 2 MB)</label>
            <input type="file" name="profile_image" class="form-control-file" accept=".jpg,.jpeg,.png" required>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">ยกเลิก</button>
          <button class="btn btn-primary" type="submit">อัปโหลด</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- #endregion กล่องเปลี่ยนโปรไฟล์ -->

<?php
include("../../private/shared/footerofficer.php");
?>
<script src="../js/fvscis.js"></script>  
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/fvscis.js"></script>

<!-- ✅ JavaScript AJAX -->
<script>
$(function(){

  // เปลี่ยนรหัสผ่าน (Officer)
  $('#form-change-password').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: 'ajax/officer_change_password.php',
      method: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function(res){
        if(res.success){
          Swal.fire({icon:'success',title:'สำเร็จ',text:res.message,timer:2000,showConfirmButton:false});
          $('#form-change-password')[0].reset();
        } else {
          Swal.fire({icon:'error',title:'ไม่สำเร็จ',text:res.message});
        }
      },
      error: ()=> Swal.fire('ผิดพลาด','ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้','error')
    });
  });

  // อัปโหลดรูปโปรไฟล์ (Officer)
  $('#form-upload-profile').on('submit', function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
      url: 'ajax/officer_upload_profile.php',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res){
        if(res.success){
          Swal.fire({icon:'success',title:'สำเร็จ',text:res.message,timer:2000,showConfirmButton:false});
          $('#profileImageModal').modal('hide');
          if(res.new_image){
            $('#profile_image_tag').attr('src', res.new_image + '?t=' + new Date().getTime());
            // ถ้า topbar ของคุณมี id ภาพผู้ใช้
            $('#show_user_picture').attr('src', res.new_image + '?t=' + new Date().getTime());
          }
          $('#form-upload-profile')[0].reset();
        } else {
          Swal.fire({icon:'error',title:'ไม่สำเร็จ',text:res.message});
        }
      },
      error: ()=> Swal.fire('ผิดพลาด','ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้','error')
    });
  });

});
// #region เปลี่ยน email
$(function () {

  $('#form-change-email').on('submit', function (e) {
    e.preventDefault();

    const $form  = $(this);
    const $btn   = $('#btn-change-email');

    // กันกดซ้ำ
    $btn.prop('disabled', true).text('กำลังบันทึก...');

    $.ajax({
      url: 'ajax/ajax_change_email.php', // path ตามโครงของคุณ
      type: 'POST',
      data: $form.serialize(),
      dataType: 'json'
    })
    .done(function (res) {

      if (res.success) {

        // อัปเดตอีเมลที่แสดงด้านบน (ทั้งสองจุด)
        if (res.new_email) {
          $('#current-email-text').text(res.new_email); // ใน card
          $('#email').text(res.new_email);              // ที่ไหนก็ตามที่ใช้ #email
        }

        // ล้างฟิลด์อินพุต
        $form.find('input[name="new_email"]').val('');
        $form.find('input[name="current_password"]').val('');

        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: res.message || 'เปลี่ยนอีเมลเรียบร้อยแล้ว',
          confirmButtonText: 'ตกลง'
        });

      } else {

        Swal.fire({
          icon: 'error',
          title: 'ไม่สามารถเปลี่ยนอีเมลได้',
          text: res.message || 'กรุณาลองใหม่อีกครั้ง',
          confirmButtonText: 'ตกลง'
        });

      }
    })
    .fail(function () {

      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
        confirmButtonText: 'ตกลง'
      });

    })
    .always(function () {
      $btn.prop('disabled', false).text('บันทึกอีเมลใหม่');
    });
  });

});
//#endregion

</script>

<?php
include("../../private/shared/footerall.php");
?>