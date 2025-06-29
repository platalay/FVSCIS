<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["id"]);
$form = InspectionFormStatus::find_or_create(
    $request->ship_code,
    $request->confirmed_inspect_date,
    $session->user_id(),        // หรือ current inspector
    $request->department_id,   // หรือดึงจาก officer info
    $request->id
);
$check = '✅ ';
?>

<!-- Begin Page Content -->
<div class="container-fluid">
 <div class="card o-hidden border-0 shadow-lg">
            <div class="card-body p-0">
            <div class="row">
            
            <!-- โครงสร้างเรือ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center">
    <a href="form_structure.php?request=<?php echo $request->id; ?>"
       style="padding: 0; border: 0; background: transparent; text-decoration: none; display: inline-block; transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านโครงสร้างของเรือประมง">
        <img src="../img/boat.png" style="max-height: 100px; display: block;" alt="โครงสร้างเรือ">
        <div style="margin-top: 8px; font-size: 14px; color: #000;">
            <?= $form->form_structure_status == 1 ? $check : '' ?>โครงสร้างเรือ
        </div>
    </a>
</div>

<!-- วัสดุอุปกรณ์ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center">
    <a href="form_material.php?request=<?php echo $request->id; ?>"
       style="padding: 0; border: 0; background: transparent; text-decoration: none; display: inline-block; transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง">
        <img src="../img/material.png" style="max-height: 200px; display: block;" alt="วัสดุอุปกรณ์">
        <div style="margin-top: 8px; font-size: 14px; color: #000;">
            <?= $form->form_material_status == 1 ? $check : '' ?>วัสดุและอุปกรณ์
        </div>
    </a>
</div>

<!-- บุคลากร -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center">
    <a href="form_crew.php?request=<?php echo $request->id; ?>"
       style="padding: 0; border: 0; background: transparent; text-decoration: none; display: inline-block; transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านบุคลากรประจำเรือ">
        <img src="../img/fisher_man.png" style="max-height: 150px; display: block;" alt="บุคลากร">
        <div style="margin-top: 8px; font-size: 14px; color: #000;">
            <?= $form->form_crew_status == 1 ? $check : '' ?>บุคลากรบนเรือ
        </div>
    </a>
</div>

<!-- น้ำจืดและน้ำแข็ง -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center">
    <a href="form_waterice.php?request=<?php echo $request->id; ?>"
       style="padding: 0; border: 0; background: transparent; text-decoration: none; display: inline-block; transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ">
        <img src="../img/waterandice.png" style="max-height: 150px; display: block;" alt="น้ำและน้ำแข็ง">
        <div style="margin-top: 8px; font-size: 14px; color: #000;">
            <?= $form->form_water_ice_status == 1 ? $check : '' ?>น้ำจืดและน้ำแข็ง
        </div>
    </a>
</div>

<!-- การเก็บรักษาสัตว์น้ำ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center">
    <a href="form_preservation.php?request=<?php echo $request->id; ?>"
       style="padding: 0; border: 0; background: transparent; text-decoration: none; display: inline-block; transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านการเก็บรักษาสัตว์น้ำ">
        <img src="../img/preservation.png" style="max-height: 150px; display: block;" alt="การเก็บรักษา">
        <div style="margin-top: 8px; font-size: 14px; color: #000;">
            <?= $form->form_preservation_status == 1 ? $check : '' ?>การเก็บรักษาสัตว์น้ำ
        </div>
    </a>
</div>



                <div class="col-lg-2 d-flex align-items-center justify-content-center">
                  <?php if (
                      $form->form_structure_status &&
                      $form->form_material_status &&
                      $form->form_crew_status &&
                      $form->form_water_ice_status &&
                      $form->form_preservation_status &&
                      !$form->document_locked
                  ): ?>
                      <div class="text-center mt-1">
                          <a href="generate_pdf.php?token=<?= h($form->document_token); ?>"
                            class="btn btn-primary btn-lg">
                            <img src="../img/pdf.png" style="max-height: 100px; display: block;" alt="pdf">
                          </a>
                      </div>
                  <?php else: ?>
                      <div class="text-center mt-1">
                          <button class="btn btn-secondary btn-lg" disabled>
                              <img src="../img/pdf.png" style="max-height: 100px; display: block;" alt="pdf">
                          </button>
                      </div>
                  <?php endif; ?>
                </div>


              </div>
            </div>
          </div>
</div>

<?php
include("../../private/shared/footerofficer.php");
?>
 <script>
        document.addEventListener('DOMContentLoaded', function () {
          var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
          tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl)
          })
        });
      </script> 
<?php
include("../../private/shared/footerall.php");
?>