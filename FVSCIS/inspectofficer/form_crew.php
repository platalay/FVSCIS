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
  <h1 class="h3 mb-4 text-gray-800">ด้านบุคลากรประจำเรือ (crew)
  <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<!-- ข้อ 1 -->
  <div class="accordion-item">
  <h2 class="accordion-header" id="heading3_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse3_1"
            aria-expanded="false" aria-controls="collapse3_1">
      3.1 บุคลากรที่ปฏิบัติงานในเรือต้องมีสุขภาพดี...
    </button>
  </h2>
  <div id="collapse3_1" class="accordion-collapse collapse" aria-labelledby="heading3_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-3-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_1" id="status_3_1_pass" value="pass">
            <label class="form-check-label" for="status_3_1_pass">
              ผ่าน - ไม่มีบุคลากรสุขภาพไม่แข็งแรง / มีใบรับรอง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_1" id="status_3_1_fail" value="fail">
            <label class="form-check-label" for="status_3_1_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist เงื่อนไขไม่ผ่าน -->
        <div id="fail_group_3_1" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_3_1_1">
            <label class="form-check-label" for="fail_3_1_1">
              ไม่มีใบตรวจสุขภาพของกระทรวงแรงงานสำหรับคนต่างชาติ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_3_1_2">
            <label class="form-check-label" for="fail_3_1_2">
              ไม่มีใบรับรองแพทย์ (คนไทย)
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_3_1_3">
            <label class="form-check-label" for="fail_3_1_3">
              มีแผลเปิดหรือแผลติดเชื้อ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_3_1_4">
            <label class="form-check-label" for="fail_3_1_4">
              มีอาการไอ จาม มีน้ำมูก ฯลฯ หรือแสดงอาการของโรค
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_3_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_3_1"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading3_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse3_2"
            aria-expanded="false" aria-controls="collapse3_2">
      3.2 ผ่านการฝึกอบรมเรื่องสุขอนามัยที่ควรปฏิบัติในเรือประมง
    </button>
  </h2>
  <div id="collapse3_2" class="accordion-collapse collapse" aria-labelledby="heading3_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-3-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_2" id="status_3_2_pass" value="pass">
            <label class="form-check-label" for="status_3_2_pass">
              ผ่าน - ผู้ควบคุมเรือได้รับการอบรมเรื่องสุขอนามัยจากหน่วยงานของรัฐหรือสุขอนามัยในเรือประมง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_2" id="status_3_2_fail" value="fail">
            <label class="form-check-label" for="status_3_2_fail">
              ไม่ผ่าน - ผู้ควบคุมเรือไม่ผ่านการอบรมหรือไม่มีประวัติผ่านการอบรม
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_3_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_3_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading3_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse3_3"
            aria-expanded="false" aria-controls="collapse3_3">
      3.3 ล้างมือให้สะอาดทั้งก่อนและหลังการปฏิบัติงานทุกครั้ง รวมทั้งในระหว่างการปฏิบัติงานตามความเหมาะสมและทุกครั้งหลังการใช้สุขา
    </button>
  </h2>
  <div id="collapse3_3" class="accordion-collapse collapse" aria-labelledby="heading3_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-3-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_3" id="status_3_3_pass" value="pass">
            <label class="form-check-label" for="status_3_3_pass">
              ผ่าน - จากการสอบถามพบว่า ล้างมือหลังใช้สุขา
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_3" id="status_3_3_fail" value="fail">
            <label class="form-check-label" for="status_3_3_fail">
              ไม่ผ่าน - ไม่พบอุปกรณ์หรือจุดล้างมือที่สะอาดพร้อมใช้งาน เช่น ไม่มีสบู่ น้ำสะอาด หรือภาชนะรองน้ำในบริเวณปฏิบัติงาน
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_3_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_3_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading3_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse3_4"
            aria-expanded="false" aria-controls="collapse3_4">
      3.4 เสื้อผ้าที่ใส่ทำงานต้องสะอาด และเหมาะสมกับการปฏิบัติงาน
    </button>
  </h2>
  <div id="collapse3_4" class="accordion-collapse collapse" aria-labelledby="heading3_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-3-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_4" id="status_3_4_pass" value="pass">
            <label class="form-check-label" for="status_3_4_pass">
              ผ่าน - ใส่เสื้อผ้าสะอาด และสวมรองเท้าป้องกันเท้าไม่ให้สัมผัสกับสัตว์น้ำโดยตรง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_4" id="status_3_4_fail" value="fail">
            <label class="form-check-label" for="status_3_4_fail">
              ไม่ผ่าน - ไม่ใส่เสื้อ กางเกง หรือไม่สวมรองเท้าป้องกันเท้าไม่ให้สัมผัสกับสัตว์น้ำโดยตรง
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_3_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_3_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->

<!-- ข้อ 5 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading3_5">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse3_5"
            aria-expanded="false" aria-controls="collapse3_5">
      3.5 ไม่รับประทานอาหารหรือสูบบุหรี่ไม่ไอหรือจามใส่สัตว์น้ำขณะปฏิบัติงาน
    </button>
  </h2>
  <div id="collapse3_5" class="accordion-collapse collapse" aria-labelledby="heading3_5" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-3-5">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_5" id="status_3_5_pass" value="pass">
            <label class="form-check-label" for="status_3_5_pass">
              ผ่าน - ไม่สูบบุหรี่ในพื้นที่ปฏิบัติงาน ไม่ไอหรือจามใส่สัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_3_5" id="status_3_5_fail" value="fail">
            <label class="form-check-label" for="status_3_5_fail">
              ไม่ผ่าน - พบว่ามีบุคลากรทานอาหาร กินหมาก ถ่มน้ำลาย หรือสูบบุหรี่ขณะปฏิบัติงาน
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_3_5" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_3_5"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 5 -->



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
      url: 'ajax/autosave_crew.php',
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
    $.post('ajax/load_crew_all.php', { request_id: requestId }, function (res) {
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