<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ (water and ice)</h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<!-- ข้อ 1 -->
  <div class="accordion-item">
  <h2 class="accordion-header" id="heading4_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_1"
            aria-expanded="false" aria-controls="collapse4_1">
      4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน
    </button>
  </h2>
  <div id="collapse4_1" class="accordion-collapse collapse" aria-labelledby="heading4_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-4_1" type="radio"
                   name="status_4_1" id="status_4_1_pass" value="pass"
                   data-item-code="4_1">
            <label class="form-check-label" for="status_4_1_pass">
              ผ่าน - ใช้น้ำแข็งที่ได้มาตรฐาน GMP หรือ อย. และใช้น้ำอุปโภคบริโภคที่สะอาด ไม่มีสี ไม่มีกลิ่น และมีปริมาณเพียงพอ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-4_1" type="radio"
                   name="status_4_1" id="status_4_1_fail" value="fail"
                   data-item-code="4_1">
            <label class="form-check-label" for="status_4_1_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_4_1" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_1_fail_1"
                   data-code="fail_1"
                   data-item-code="4_1"
                   data-text="ไม่ผ่าน - น้ำแข็งไม่มีใบรับรองมาตรฐาน GMP หรือ อย.">
            <label class="form-check-label" for="chk_4_1_fail_1">
              น้ำแข็งไม่มีใบรับรองมาตรฐาน GMP หรือ อย.
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_1_fail_2"
                   data-code="fail_2"
                   data-item-code="4_1"
                   data-text="ไม่ผ่าน - น้ำแข็งชื้น มีคราบ มีสิ่งสกปรก">
            <label class="form-check-label" for="chk_4_1_fail_2">
              น้ำแข็งชื้น มีคราบ มีสิ่งสกปรก
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_1_fail_3"
                   data-code="fail_3"
                   data-item-code="4_1"
                   data-text="ไม่ผ่าน - น้ำอุปโภคบริโภค ไม่สะอาด มีสี มีกลิ่น">
            <label class="form-check-label" for="chk_4_1_fail_3">
              น้ำอุปโภคบริโภค ไม่สะอาด มีสี มีกลิ่น
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_1_fail_4"
                   data-code="fail_4"
                   data-item-code="4_1"
                   data-text="ไม่ผ่าน - น้ำแข็งหรือน้ำอุปโภคบริโภคมีปริมาณไม่เพียงพอ">
            <label class="form-check-label" for="chk_4_1_fail_4">
              น้ำแข็งหรือน้ำอุปโภคบริโภคมีปริมาณไม่เพียงพอ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_1"
                    data-code="4_1_remark"
                    data-item-code="4_1"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_2"
            aria-expanded="false" aria-controls="collapse4_2">
      4.2 สถานที่เก็บ และภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิมและทำความสะอาดได้ง่าย
    </button>
  </h2>
  <div id="collapse4_2" class="accordion-collapse collapse" aria-labelledby="heading4_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_4_2" id="status_4_2_pass" value="pass">
            <label class="form-check-label" for="status_4_2_pass">
              ผ่าน - สถานที่เก็บและภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี ไม่มีสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_4_2" id="status_4_2_fail" value="fail">
            <label class="form-check-label" for="status_4_2_fail">
              ไม่ผ่าน - สถานที่เก็บและภาชนะที่บรรจุน้ำจืด และน้ำแข็งมีคราบสกปรกเป็นสนิม
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_4_2"
                    name="remark_4_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_3"
            aria-expanded="false" aria-controls="collapse4_3">
      4.3 มีการถ่ายเท ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ น้ำแข็งลงเรือ
    </button>
  </h2>
  <div id="collapse4_3" class="accordion-collapse collapse" aria-labelledby="heading4_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-4_3" type="radio"
                   name="status_4_3" id="status_4_3_pass" value="pass"
                   data-item-code="4_3">
            <label class="form-check-label" for="status_4_3_pass">
              ผ่าน - เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด ไม่เป็นสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-4_3" type="radio"
                   name="status_4_3" id="status_4_3_fail" value="fail"
                   data-item-code="4_3">
            <label class="form-check-label" for="status_4_3_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_4_3" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_3_fail_1"
                   data-code="fail_1"
                   data-item-code="4_3"
                   data-text="ไม่ผ่าน - เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด เป็นสนิม">
            <label class="form-check-label" for="chk_4_3_fail_1">
              เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด เป็นสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_4_3_fail_2"
                   data-code="fail_2"
                   data-item-code="4_3"
                   data-text="ไม่ผ่าน - น้ำแข็งวางกองอยู่บนพื้นก่อนลงเรือ">
            <label class="form-check-label" for="chk_4_3_fail_2">
              น้ำแข็งวางกองอยู่บนพื้นก่อนลงเรือ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_3"
                    data-code="4_3_remark"
                    data-item-code="4_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_4"
            aria-expanded="false" aria-controls="collapse4_4">
      4.4 ภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องมีฝาปิดมิดชิด
    </button>
  </h2>
  <div id="collapse4_4" class="accordion-collapse collapse" aria-labelledby="heading4_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_4_4" id="status_4_4_pass" value="pass">
            <label class="form-check-label" for="status_4_4_pass">
              ผ่าน - มีฝาปิดมิดชิด
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_4_4" id="status_4_4_fail" value="fail">
            <label class="form-check-label" for="status_4_4_fail">
              ไม่ผ่าน - ไม่มีฝาปิด หรือฝาปิดชำรุด
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control"
                    id="remark_4_4"
                    name="remark_4_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->



</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>
<script>
  $(document).ready(function () {
    $('.form-status-radio-4_1').on('change', function () {
      const isFail = $(this).val() === 'fail';
      $('#fail_group_4_1').toggle(isFail);
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('.form-status-radio-4_3').on('change', function () {
      const isFail = $(this).val() === 'fail';
      $('#fail_group_4_3').toggle(isFail);
    });
  });
</script>


<?
include("../../private/shared/footerall.php");
?>