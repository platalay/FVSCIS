<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());
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
$cross = '❌ ';
$pending = '⏳';
?>

<!-- Begin Page Content -->
<div class="container-fluid  p-3">
 <div class="card o-hidden border-0 shadow-lg">
            <div class="card-body p-0">
            <div class="row">
            
            <!-- โครงสร้างเรือ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
    <a href="form_structure.php?request=<?= $request->id ?>"
       class="text-decoration-none"
       style="transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านโครงสร้างของเรือประมง">
        <img src="../img/boat.png"
             class="img-fluid"
             style="max-height: 100px;"
             alt="โครงสร้างเรือ">
        <div class="mt-2" style="font-size: 14px; color: #000;">
            <?= $form->form_structure_status == 1 ? $check : '' ?>โครงสร้างเรือ
        </div>
    </a>
</div>


<!-- วัสดุอุปกรณ์ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
    <a href="form_material.php?request=<?= $request->id ?>"
       class="text-decoration-none"
       style="transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง">
        <img src="../img/material.png"
             class="img-fluid"
             style="max-height: 200px;"
             alt="วัสดุอุปกรณ์">
        <div class="mt-2" style="font-size: 14px; color: #000;">
            <?= $form->form_material_status == 1 ? $check : '' ?>วัสดุและอุปกรณ์
        </div>
    </a>
</div>


<!-- บุคลากร -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
    <a href="form_crew.php?request=<?= $request->id ?>"
       class="text-decoration-none"
       style="transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านบุคลากรประจำเรือ">
        <img src="../img/fisher_man.png"
             class="img-fluid"
             style="max-height: 150px;"
             alt="บุคลากร">
        <div class="mt-2" style="font-size: 14px; color: #000;">
            <?= $form->form_crew_status == 1 ? $check : '' ?>บุคลากรบนเรือ
        </div>
    </a>
</div>


<!-- น้ำจืดและน้ำแข็ง -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
    <a href="form_waterice.php?request=<?= $request->id ?>"
       class="text-decoration-none"
       style="transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ">
        <img src="../img/waterandice.png"
             class="img-fluid"
             style="max-height: 150px;"
             alt="น้ำและน้ำแข็ง">
        <div class="mt-2" style="font-size: 14px; color: #000;">
            <?= $form->form_water_ice_status == 1 ? $check : '' ?>น้ำจืดและน้ำแข็ง
        </div>
    </a>
</div>


<!-- การเก็บรักษาสัตว์น้ำ -->
<div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
    <a href="form_preservation.php?request=<?= $request->id ?>"
       class="text-decoration-none"
       style="transition: transform 0.2s;"
       onmouseover="this.style.transform='scale(1.05)';"
       onmouseout="this.style.transform='scale(1)';"
       title="ด้านการเก็บรักษาสัตว์น้ำ">
        <img src="../img/preservation.png"
             class="img-fluid"
             style="max-height: 150px;"
             alt="การเก็บรักษา">
        <div class="mt-2" style="font-size: 14px; color: #000;">
            <?= $form->form_preservation_status == 1 ? $check : '' ?>การเก็บรักษาสัตว์น้ำ
        </div>
    </a>
</div>




                <div class="col-lg-2 d-flex flex-column align-items-center justify-content-center text-center  p-3">
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
                        class="btn btn-outline-danger btn-lg"
                        style="transition: transform 0.2s;"
                        onmouseover="this.style.transform='scale(1.05)';"
                        onmouseout="this.style.transform='scale(1)';"
                        target="_blank"
                        title="ดูใบรับรอง PDF">
                            <img src="../img/pdf.png"
                                class="img-fluid"
                                style="max-height: 100px;"
                                alt="เปิดใบรับรอง PDF">
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center mt-1">
                        <button class="btn btn-secondary btn-lg" disabled
                                title="กรอกข้อมูลให้ครบก่อน">
                            <img src="../img/pdf.png"
                                class="img-fluid"
                                style="max-height: 100px;"
                                alt="PDF">
                        </button>
                        <div class="mt-2" style="font-size: 14px; color: #000;">
                            สร้างเอกสารผลตรวจ
                        </div>
                    </div>
                <?php endif; ?>
            </div>



            </div>
            </div>
            </div>



                <?php
                $result = InspectionEvaluation::check_vessel_pass($request->id, 'commercial');

                if (
                    $form->form_structure_status &&
                    $form->form_material_status &&
                    $form->form_crew_status &&
                    $form->form_water_ice_status &&
                    $form->form_preservation_status &&
                    !$form->document_locked
                ):
                ?>

                <div class="mt-4">
                    <div class="card o-hidden border-0 shadow-lg">
                        <div class="card-body p-4 text-center">
                            <?php if ($result === true): ?>
                                <div class="alert alert-success fs-5">
                                  เรือที่ได้รับใบอนุญาต  ✅ ผ่านเกณฑ์ ได้รับหนังสือรับรอง
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger fs-5">
                                  เรือที่ได้รับใบอนุญาต  ❌ <?= htmlspecialchars($result) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php endif; ?>

                <!--<?php
                $result = InspectionEvaluation::check_vessel_pass($request->id, 'non_permitted');

                if (
                    $form->form_structure_status &&
                    $form->form_material_status &&
                    $form->form_crew_status &&
                    $form->form_water_ice_status &&
                    $form->form_preservation_status &&
                    !$form->document_locked
                ):
                ?>
                
                <div class="mt-4">
                    <div class="card o-hidden border-0 shadow-lg">
                        <div class="card-body p-4 text-center">
                            <?php if ($result === true): ?>
                                <div class="alert alert-success fs-5">
                                   เรือที่ยังไม่ได้รับใบอนุญาต ✅ ผ่านเกณฑ์ ได้รับหนังสือรับรอง
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger fs-5">
                                   เรือที่ยังไม่ได้รับใบอนุญาต ❌ <?= htmlspecialchars($result) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php endif; ?>
                -->
            <div class="accordion mt-4" id="inspectionAccordion1">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    ผลการประเมินหมวด 1: ด้านโครงสร้างของเรือประมง
                </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#inspectionAccordion1">
                <div class="accordion-body">
                    <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4">
                        <div class="row">
                        <?php include("table_structure.php"); ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>



            <div class="accordion mt-4" id="inspectionAccordion2">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    ผลการประเมินหมวด 2: ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง
                </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#inspectionAccordion2">
                <div class="accordion-body">
                    <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4">
                        <div class="row">
                        <?php include("table_material.php"); ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <div class="accordion mt-4" id="inspectionAccordion3">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    ผลการประเมินหมวด 3 ด้านบุคลากรประจำเรือ
                </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#inspectionAccordion3">
                <div class="accordion-body">
                    <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4">
                        <div class="row">
                        <?php include("table_crew.php"); ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>

            <div class="accordion mt-4" id="inspectionAccordion4">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                    ผลการประเมินหมวด 4: ด้านน้ำจืดและน้ำแข็ง
                </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#inspectionAccordion4">
                <div class="accordion-body">
                    <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4">
                        <div class="row">
                        <?php include("table_waterice.php"); ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>


            <div class="accordion mt-4" id="inspectionAccordion5">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    ผลการประเมินหมวด 5: ด้านการเก็บรักษาสัตว์น้ำ
                </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#inspectionAccordion5">
                <div class="accordion-body">
                    <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-4">
                        <div class="row">
                        <?php include("table_preservation.php"); ?>
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>




            
</div>

<?php
include("../../private/shared/footerofficer.php");
?>
<!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/fvscis.js"></script> 
    <script src="../js/checkform.js"></script>                            
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