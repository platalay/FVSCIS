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
  <h1 class="h3 mb-4 text-gray-800">ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง (structer)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  

<!-- ข้อ 2.1 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_1"
            aria-expanded="false" aria-controls="collapse2_1">
      2.1 วัสดุ อุปกรณ์และเครื่องมือที่ใช้ทำความสะอาดแล้วต้องมีที่เก็บอย่างเหมาะสม...
    </button>
  </h2>
  <div id="collapse2_1" class="accordion-collapse collapse" aria-labelledby="heading2_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_1" id="status_2_1_pass" value="pass"
                   data-item-code="2_1">
            <label class="form-check-label" for="status_2_1_pass">
              ผ่าน - วัสดุ อุปกรณ์และเครื่องมือมีที่เก็บเหมาะสม แยกส่วน และสะอาด
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_1" id="status_2_1_fail" value="fail"
                   data-item-code="2_1">
            <label class="form-check-label" for="status_2_1_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_2_1" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_1_1"
                   data-code="fail_2_1_1"
                   data-item-code="2_1">
            <label class="form-check-label" for="fail_2_1_1">
              ไม่มีภาชนะเก็บอุปกรณ์เฉพาะ / วางอุปกรณ์ปะปนกับพื้นที่อื่น
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_1_2"
                   data-code="fail_2_1_2"
                   data-item-code="2_1">
            <label class="form-check-label" for="fail_2_1_2">
              พบการเก็บเครื่องมือที่ไม่สะอาดในพื้นที่สัมผัสสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_1"
                    data-code="remark_2_1"
                    data-item-code="2_1"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- จบข้อ 2.1 -->


<!-- ข้อ 2 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_2"
            aria-expanded="false" aria-controls="collapse2_2">
      2.2 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่มีผิวเรียบ ไม่มีรอยแตก...
    </button>
  </h2>
  <div id="collapse2_2" class="accordion-collapse collapse" aria-labelledby="heading2_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_2" id="status_2_2_pass" value="pass"
                   data-item-code="2_2">
            <label class="form-check-label" for="status_2_2_pass">
              ผ่าน - อุปกรณ์และเครื่องมือที่สัมผัสสัตว์น้ำอยู่ในสภาพสมบูรณ์ ไม่ชำรุด ไม่มีรอยแตกหัก และไม่มีสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_2" id="status_2_2_fail" value="fail"
                   data-item-code="2_2">
            <label class="form-check-label" for="status_2_2_fail">
              ไม่ผ่าน - พบเครื่องมือชำรุด/มีสนิม เช่น กระบะ ตะกร้า ลังโฟม ฯลฯ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_2"
                    data-code="remark_2_2"
                    data-item-code="2_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_3"
            aria-expanded="false" aria-controls="collapse2_3">
      2.3 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องออกแบบให้เหมาะสมกับการใช้งานและสะดวกในการรักษาความสะอาด
    </button>
  </h2>
  <div id="collapse2_3" class="accordion-collapse collapse" aria-labelledby="heading2_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_3" id="status_2_3_pass" value="pass"
                   data-item-code="2_3">
            <label class="form-check-label" for="status_2_3_pass">
              ผ่าน - ใช้เครื่องมือที่เหมาะสมกับการปฏิบัติงาน และทำความสะอาดได้ง่าย
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_3" id="status_2_3_fail" value="fail"
                   data-item-code="2_3">
            <label class="form-check-label" for="status_2_3_fail">
              ไม่ผ่าน - ใช้วัสดุ อุปกรณ์ และเครื่องมือผิดประเภท เช่น ใช้พลั่วตักน้ำแข็งไปตักปลา
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_3"
                    data-code="remark_2_3"
                    data-item-code="2_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_4"
            aria-expanded="false" aria-controls="collapse2_4">
      2.4 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องล้างทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด (น้ำประปา)
    </button>
  </h2>
  <div id="collapse2_4" class="accordion-collapse collapse" aria-labelledby="heading2_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_4" id="status_2_4_pass" value="pass"
                   data-item-code="2_4">
            <label class="form-check-label" for="status_2_4_pass">
              ผ่าน - อุปกรณ์และเครื่องมือมีล้างสะอาดพร้อมใช้งาน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_4" id="status_2_4_fail" value="fail"
                   data-item-code="2_4">
            <label class="form-check-label" for="status_2_4_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_2_4" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_4_1"
                   data-code="fail_2_4_1"
                   data-item-code="2_4"
                   data-text="ไม่ผ่าน - ใช้น้ำไม่สะอาดล้างทำความสะอาดอุปกรณ์และเครื่องมือ">
            <label class="form-check-label" for="chk_fail_2_4_1">
              ใช้น้ำไม่สะอาดล้างทำความสะอาดอุปกรณ์และเครื่องมือ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_4_2"
                   data-code="fail_2_4_2"
                   data-item-code="2_4"
                   data-text="ไม่ผ่าน - พบเศษสัตว์น้ำในอ่างล้างภาชนะทำความสะอาดเรือ">
            <label class="form-check-label" for="chk_fail_2_4_2">
              พบเศษสัตว์น้ำในอ่างล้างภาชนะทำความสะอาดเรือ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_4_3"
                   data-code="fail_2_4_3"
                   data-item-code="2_4"
                   data-text="ไม่ผ่าน - อุปกรณ์ไม่ได้ล้างทำความสะอาดตามหลักเกณฑ์ของคราบสกปรก">
            <label class="form-check-label" for="chk_fail_2_4_3">
              อุปกรณ์ไม่ได้ล้างทำความสะอาดตามหลักเกณฑ์ของคราบสกปรก
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_4"
                    data-code="remark_2_4"
                    data-item-code="2_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->

<!-- ข้อ 5 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_5">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_5"
            aria-expanded="false" aria-controls="collapse2_5">
      2.5 ภาชนะที่บรรจุสัตว์น้ำมีสภาพแข็งแรง น้ำหนักเบา และสามารถรับน้ำหนักได้ในกรณีที่ต้องวางซ้อนกัน เพื่อป้องกันไม่ให้ภาชนะกดทับสัตว์น้ำ
    </button>
  </h2>
  <div id="collapse2_5" class="accordion-collapse collapse" aria-labelledby="heading2_5" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-5">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_5" id="status_2_5_pass" value="pass"
                   data-item-code="2_5">
            <label class="form-check-label" for="status_2_5_pass">
              ผ่าน - ภาชนะบรรจุสัตว์น้ำแข็งแรง และไม่ก่อให้เกิดการกดทับสัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_5" id="status_2_5_fail" value="fail"
                   data-item-code="2_5">
            <label class="form-check-label" for="status_2_5_fail">
              ไม่ผ่าน - ภาชนะบรรจุสัตว์น้ำไม่แข็งแรง พลาสติกบาง แตกหัก อยู่ในสภาพที่ไม่เหมาะกับการใช้งานและวางซ้อนกัน
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_5" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_5"
                    data-code="remark_2_5"
                    data-item-code="2_5"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 5 -->

<!-- ข้อ 6 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading2_6">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse2_6"
            aria-expanded="false" aria-controls="collapse2_6">
      2.6 ภาชนะที่บรรจุสัตว์น้ำควรมีรูหรือช่องระบายน้ำได้ดี เช่น ภาชนะพลาสติก
    </button>
  </h2>
  <div id="collapse2_6" class="accordion-collapse collapse" aria-labelledby="heading2_6" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-2-6">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_6" id="status_2_6_pass" value="pass"
                   data-item-code="2_6">
            <label class="form-check-label" for="status_2_6_pass">
              ผ่าน - มีรูระบายน้ำหรือใช้ภาชนะที่ไม่อุ้มน้ำ เช่น ถังเขียว 80 ลิตร
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_2_6" id="status_2_6_fail" value="fail"
                   data-item-code="2_6">
            <label class="form-check-label" for="status_2_6_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_2_6" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_6_1"
                   data-code="fail_2_6_1"
                   data-item-code="2_6"
                   data-text="ไม่ผ่าน - คอแห้งภาชนะไม่มีรูระบายน้ำ">
            <label class="form-check-label" for="chk_fail_2_6_1">
              คอแห้งภาชนะไม่มีรูระบายน้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_2_6_2"
                   data-code="fail_2_6_2"
                   data-item-code="2_6"
                   data-text="ไม่ผ่าน - ภาชนะที่มีสัตว์น้ำอยู่มีลักษณะอุ้มน้ำ/อุ้มน้ำขัง">
            <label class="form-check-label" for="chk_fail_2_6_2">
              ภาชนะที่มีสัตว์น้ำอยู่มีลักษณะอุ้มน้ำ/อุ้มน้ำขัง
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_2_6" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_2_6"
                    data-code="remark_2_6"
                    data-item-code="2_6"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 6 -->



</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>

<script>
$(document).ready(function () {
  const requestId = "<?= $request_id ?>";

  // ✅ โหลดข้อมูลเมื่อเริ่มต้น
  loadMaterialData(requestId);

  // ✅ กด radio แล้ว toggle กล่อง fail + autosave + clear checkbox ถ้าเลือก "ผ่าน"
  $('input[type="radio"]').on('change', function () {
    const field = $(this).attr('name'); // เช่น status_2_1
    const value = $(this).val();        // pass หรือ fail
    const itemCode = field.replace('status_', ''); // เช่น 2_1
    const failGroup = $('#fail_group_' + itemCode);

    if (value === 'fail') {
      failGroup.slideDown();
    } else {
      failGroup.slideUp();

      // เคลียร์ checkbox ทุกอันในกลุ่ม
      failGroup.find('input[type="checkbox"]').each(function () {
        if (this.checked) {
          this.checked = false;
          autosave(requestId, this.id, 0);
        }
      });
    }

    autosave(requestId, field, value);
  });

  // ✅ checkbox → autosave
  $('input[type="checkbox"]').on('change', function () {
    const field = this.id;
    const value = this.checked ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ textarea → autosave
  $('textarea').on('input', function () {
    const field = this.id;
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // ✅ ฟังก์ชัน autosave กลาง
  function autosave(requestId, field, value) {
    $.ajax({
      url: 'ajax/autosave_material.php',
      method: 'POST',
      data: { request_id: requestId, field: field, value: value },
      success: function () {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }

  // ✅ โหลดข้อมูลเดิมกลับเข้า form
  function loadMaterialData(requestId) {
    $.post('ajax/load_material_all.php', { request_id: requestId }, function (res) {
      if (res.success) {
        const data = res.data;
        for (let field in data) {
          const value = data[field];

          // Radio
          if (field.startsWith('status_')) {
            $(`input[name="${field}"][value="${value}"]`).prop('checked', true).trigger('change');
          }

          // Checkbox
          if (field.startsWith('fail_') && value === "1") {
            $(`#${field}`).prop('checked', true);
          }

          // Textarea
          if (field.startsWith('remark_')) {
            $(`#${field}`).val(value);
          }
        }
      } else {
        alert('โหลดข้อมูลไม่สำเร็จ: ' + res.message);
      }
    }, 'json');
  }
});
</script>


<?
include("../../private/shared/footerall.php");
?>