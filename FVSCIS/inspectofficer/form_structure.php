<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
$request_id = $request->id;
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านโครงสร้างของเรือประมง (structer)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<!-- ข้อ 1 -->
<!-- ข้อ 1.1 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading1_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_1"
            aria-expanded="false" aria-controls="collapse1_1">
      1.1 ห้องเก็บรักษาสัตว์น้ำอยู่ในสภาพดี สะอาด มีขนาดเหมาะสมเพียงพอ
    </button>
  </h2>
  <div id="collapse1_1" class="accordion-collapse collapse" aria-labelledby="heading1_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- ผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_1" id="status_1_1_pass" value="pass" data-item-code="1_1">
            <label class="form-check-label" for="status_1_1_pass">ผ่าน</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_1" id="status_1_1_fail" value="fail" data-item-code="1_1">
            <label class="form-check-label" for="status_1_1_fail">ไม่ผ่าน</label>
          </div>
        </div>

        <!-- Checklist (แสดงเมื่อไม่ผ่าน) -->
        <div id="fail_group_1_1" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_1_1" name="fail_1_1_1">
            <label class="form-check-label" for="fail_1_1_1">
              ผนังห้องผุกร่อน ชำรุด มีสนิม ผนังห้องด้านบนสภาพปรก
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_1_2" name="fail_1_1_2">
            <label class="form-check-label" for="fail_1_1_2">
              ห้องมีขนาดไม่เพียงพอ เช่น มีสัตว์น้ำกองอยู่นอกห้อง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_1_3" name="fail_1_1_3">
            <label class="form-check-label" for="fail_1_1_3">
              พบเศษซากสัตว์น้ำที่ค้างในห้อง
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_1" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_2"
            aria-expanded="false" aria-controls="collapse1_2">
      1.2 มีโครงสร้างอย่างเหมาะสมโดยมีซอกมุมน้อยที่สุด เพื่อให้ง่ายต่อการรักษาความสะอาด
    </button>
  </h2>
  <div id="collapse1_2" class="accordion-collapse collapse" aria-labelledby="heading1_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- ผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_2" id="status_1_2_pass"
                   value="pass" data-item-code="1_2">
            <label class="form-check-label" for="status_1_2_pass" data-item-code="1_2">
              ผ่าน - ไม่มีมุมอับ ไม่มีสิ่งของขวาง
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_2" id="status_1_2_fail"
                   value="fail" data-item-code="1_2">
            <label class="form-check-label" for="status_1_2_fail" data-item-code="1_2">
              ไม่ผ่าน - มีมุมอับ และพื้นที่สกปรก
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control"
                    id="remark_1_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading1_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_3"
            aria-expanded="false" aria-controls="collapse1_3">
      1.3 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำออกแบบอย่างเหมาะสม ถูกสุขลักษณะ...
    </button>
  </h2>
  <div id="collapse1_3" class="accordion-collapse collapse" aria-labelledby="heading1_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_3" id="status_1_3_pass" value="pass" data-item-code="1_3">
            <label class="form-check-label" for="status_1_3_pass">
              ผ่าน - โครงสร้างเรือ การออกแบบมีความเหมาะสม แบ่งสัดส่วนชัดเจน
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_3" id="status_1_3_fail" value="fail" data-item-code="1_3">
            <label class="form-check-label" for="status_1_3_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist -->
        <div id="fail_group_1_3" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_3_1" name="fail_1_3_1">
            <label class="form-check-label" for="fail_1_3_1">
              มีคราบน้ำมันปนเปื้อนจากเครื่องยนต์ หรือเครื่องจักรช่วยทำการประมง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_3_2" name="fail_1_3_2">
            <label class="form-check-label" for="fail_1_3_2">
              วางสิ่งของ เช่น เครื่องจากวาน รอก ฯลฯ ปะปน ไม่เหมาะสม
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_3" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_4"
            aria-expanded="false" aria-controls="collapse1_4">
      1.4 มีพื้นที่เพียงพอและเหมาะสมสำหรับรับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำ...
    </button>
  </h2>
  <div id="collapse1_4" class="accordion-collapse collapse" aria-labelledby="heading1_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_4" id="status_1_4_pass" value="pass" data-item-code="1_4">
            <label class="form-check-label" for="status_1_4_pass">
              ผ่าน - มีพื้นที่รับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำเพียงพอและเหมาะสม
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_4" id="status_1_4_fail" value="fail" data-item-code="1_4">
            <label class="form-check-label" for="status_1_4_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist -->
        <div id="fail_group_1_4" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_4_1" name="fail_1_4_1">
            <label class="form-check-label" for="fail_1_4_1">
              สัตว์น้ำและเครื่องมือวางปะปนกัน หรือวางทับซ้อนกัน ไม่เป็นระเบียบ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_4_2" name="fail_1_4_2">
            <label class="form-check-label" for="fail_1_4_2">
              การขนถ่ายที่ไม่ถูกสุขลักษณะ เช่น การเหยียบบนสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_4" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->

<!-- ข้อ 5 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_5">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_5"
            aria-expanded="false" aria-controls="collapse1_5">
      1.5 พื้นที่ของเรือที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำทำจากวัสดุคงทน ผิวเรียบ ทำความสะอาดง่าย
    </button>
  </h2>
  <div id="collapse1_5" class="accordion-collapse collapse" aria-labelledby="heading1_5" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-5">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_5" id="status_1_5_pass" value="pass" data-item-code="1_5">
            <label class="form-check-label" for="status_1_5_pass">
              ผ่าน - พื้นที่ผิวเรียบ เกลี้ยง ไม่มีเสียหาย และกรณีทาสี สีไม่หลุดล่อน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_5" id="status_1_5_fail" value="fail" data-item-code="1_5">
            <label class="form-check-label" for="status_1_5_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist -->
        <div id="fail_group_1_5" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_5_1" name="fail_1_5_1">
            <label class="form-check-label" for="fail_1_5_1">
              พื้นผิวไม่เรียบ เป็นร่อง ยาแนวหลุดล่อน เป็นฝุ่นขุย
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_5_2" name="fail_1_5_2">
            <label class="form-check-label" for="fail_1_5_2">
              สีหลุดล่อน มีสนิม ปูนร่วง ทาสีไม่เหมาะกับบรรจุสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_5" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_5" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 5 -->

<!-- ข้อ 6 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_6">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_6"
            aria-expanded="false" aria-controls="collapse1_6">
      1.6 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำต้องทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด...
    </button>
  </h2>
  <div id="collapse1_6" class="accordion-collapse collapse" aria-labelledby="heading1_6" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-6">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_6" id="status_1_6_pass" value="pass" data-item-code="1_6">
            <label class="form-check-label" for="status_1_6_pass">
              ผ่าน - พื้นที่สะอาด ทำความสะอาดด้วยน้ำสะอาด และไม่มีแมลงหรือสัตว์อื่น ๆ บนเรือ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_6" id="status_1_6_fail" value="fail" data-item-code="1_6">
            <label class="form-check-label" for="status_1_6_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist -->
        <div id="fail_group_1_6" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_6_1" name="fail_1_6_1">
            <label class="form-check-label" for="fail_1_6_1">
              พื้นที่สกปรก ไม่ได้ทำความสะอาดหลังใช้งาน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_6_2" name="fail_1_6_2">
            <label class="form-check-label" for="fail_1_6_2">
              พบสุนัข แมว หนู จิ้งจก แมลง หรือสัตว์อื่น ๆ ในเรือหรือห้องเก็บสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_6" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_6" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 6 -->

<!-- ข้อ 7 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_7">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_7"
            aria-expanded="false" aria-controls="collapse1_7">
      1.7 จัดพื้นที่บริเวณเฉพาะสำหรับเก็บขยะ เศษอาหาร และเศษสัตว์น้ำที่เหลือให้เป็นสัดส่วนแยกออกจากบริเวณพื้นที่ปฏิบัติงาน
    </button>
  </h2>
  <div id="collapse1_7" class="accordion-collapse collapse" aria-labelledby="heading1_7" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-7">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_7" id="status_1_7_pass" value="pass" data-item-code="1_7">
            <label class="form-check-label" for="status_1_7_pass">
              ผ่าน - มีการจัดพื้นที่ขยะเป็นสัดส่วน มีภาชนะเก็บขยะ และไม่พบเศษอาหารในพื้นที่ปฏิบัติงาน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_7" id="status_1_7_fail" value="fail" data-item-code="1_7">
            <label class="form-check-label" for="status_1_7_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist -->
        <div id="fail_group_1_7" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_7_1" name="fail_1_7_1">
            <label class="form-check-label" for="fail_1_7_1">
              ไม่มีถัง/ภาชนะใส่ขยะที่ชัดเจน เช่น ถุงพลาสติก ขวดแก้ว กระป๋อง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_7_2" name="fail_1_7_2">
            <label class="form-check-label" for="fail_1_7_2">
              พบเศษอาหาร เช่น ข้าว เศษปลา ฯลฯ ไม่อยู่ในพื้นที่จัดเก็บ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="fail_1_7_3" name="fail_1_7_3">
            <label class="form-check-label" for="fail_1_7_3">
              พบเศษสัตว์น้ำที่เหลืออยู่บนพื้นที่ปฏิบัติงาน
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_7" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_1_7" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 7 -->

</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>

<script>
$(document).ready(function () {
  $('.form-status-radio').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const itemCode = $(this).data('item-code'); // เช่น 1_1, 1_2
    const targetSection = '#fail_group_' + itemCode;

    if (isFail) {
      $(targetSection).slideDown();
    } else {
      $(targetSection).slideUp();
    }
  });
});
</script>



<script>
$(document).ready(function () {
  // ✅ autosave radio ทุกข้อ
  $('input[type="radio"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('name');
    const value = $(this).val();

    // 👉 toggle checklist group ถ้ามี
    const groupId = '#fail_group_' + field.replace('status_', '');
    if ($(groupId).length) {
      $(groupId).toggle(value === 'fail');
    }

    autosave(requestId, field, value);
  });

  // ✅ autosave checkbox ทุกข้อ
  $('input[type="checkbox"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).is(':checked') ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ autosave textarea ทุกข้อ
  $('textarea').on('input', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // 🔁 core autosave
  function autosave(requestId, field, value) {
    $.ajax({
      url: 'ajax/autosave_structure.php',
      method: 'POST',
      data: {
        request_id: requestId,
        field: field,
        value: value
      },
      success: function (res) {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }
});
</script>


<script>
  $(document).ready(function () {
    const requestId = <?= json_encode($request_id) ?>;

    $.post('ajax/load_structure_all.php', { request_id: requestId }, function (res) {
      if (!res.success) return;

      const data = res.data;
      console.log(data);

      // 🧠 วนทุก field ที่ได้มา
      for (const [key, value] of Object.entries(data)) {
        if (value === null || value === "") continue;

        // ✅ radio (status_1_x)
        if (key.startsWith('status_')) {
          $(`input[name="${key}"][value="${value}"]`).prop('checked', true);

          // ถ้าเป็น fail → แสดงกล่อง checklist
          if (value === 'fail') {
            const code = key.replace('status_', '');
            $(`#fail_group_${code}`).show();
          }
        }

        // ✅ checkbox (fail_1_x_x)
        else if (key.startsWith('fail_') && value == '1') {
          $(`input[id="${key}"]`).prop('checked', true);
        }

        // ✅ textarea (remark_1_x)
        else if (key.startsWith('remark_')) {
          $(`#${key}`).val(value);
        }
      }
    }, 'json');
  });
</script>


<script>
$(document).ready(function () {

  // ✅ แก้ปัญหาเมื่อเลือก "ผ่าน" ต้อง uncheck checkbox ทั้งหมดใน fail group
  $('input[type="radio"].form-status-radio').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const itemCode = $(this).data('item-code'); // เช่น 2_1, 2_4
    const field = $(this).attr('name'); // เช่น status_2_1
    const value = $(this).val(); // pass / fail
    const failGroup = $('#fail_group_' + itemCode);

    // 👉 toggle group
    if (value === 'fail') {
      failGroup.slideDown();
    } else {
      failGroup.slideUp();

      // ✅ ยกเลิก checkbox ทั้งหมดในกลุ่ม และ autosave = 0
      failGroup.find('input[type="checkbox"]').each(function () {
        if ($(this).is(':checked')) {
          $(this).prop('checked', false);
          const checkboxId = $(this).attr('id');
          autosave(requestId, checkboxId, 0);
        }
      });
    }

    autosave(requestId, field, value);
  });

  // ✅ autosave checkbox ทุกข้อ
  $('input[type="checkbox"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).is(':checked') ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ autosave textarea ทุกข้อ
  $('textarea').on('input', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // 🔁 autosave core
  function autosave(requestId, field, value) {
    $.ajax({
      url: 'ajax/autosave_structure.php',
      method: 'POST',
      data: {
        request_id: requestId,
        field: field,
        value: value
      },
      success: function () {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }

});
</script>
<?
include("../../private/shared/footerall.php");
?>