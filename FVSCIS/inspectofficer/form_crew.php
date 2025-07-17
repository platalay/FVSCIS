<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
$request_id = $request->id;
$fail_items = InspectionFailItem::find_by_section(3);
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านบุคลากรประจำเรือ (crew)
  <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary" id="btn-back">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<?php
// ดึง checklist เฉพาะกลุ่ม 3
$fail_items = InspectionFailItem::find_by_section(3);

// จัดกลุ่ม checklist
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_3_1_1 → [fail, 3, 1, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// รายการข้อสอบถามในหมวด 3
$inspection_items = [
    '3_1' => '3.1 บุคลากรที่ปฏิบัติงานในเรือต้องมีสุขภาพดี...',
    '3_2' => '3.2 ผ่านการฝึกอบรมเรื่องสุขอนามัยที่ควรปฏิบัติในเรือประมง',
    '3_3' => '3.3 ล้างมือให้สะอาดทั้งก่อนและหลังการปฏิบัติงานทุกครั้ง รวมทั้งในระหว่างการปฏิบัติงานตามความเหมาะสมและทุกครั้งหลังการใช้สุขา',
    '3_4' => '3.4 เสื้อผ้าที่ใส่ทำงานต้องสะอาด และเหมาะสมกับการปฏิบัติงาน',
    '3_5' => '3.5 ไม่รับประทานอาหารหรือสูบบุหรี่ไม่ไอหรือจามใส่สัตว์น้ำขณะปฏิบัติงาน'
];
?>

<div class="accordion" id="inspectionAccordion">
<?php foreach ($inspection_items as $code => $title): ?>
<div class="accordion-item">
  <h2 class="accordion-header" id="heading<?= $code ?>">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse<?= $code ?>"
            aria-expanded="false" aria-controls="collapse<?= $code ?>">
      <?= htmlspecialchars($title) ?>
    </button>
  </h2>
  <div id="collapse<?= $code ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $code ?>" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-<?= $code ?>" class="form-inspect" data-item-code="<?= $code ?>">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <div class="mb-3">
          <?php foreach (['pass' => 'ผ่าน', 'fail' => 'ไม่ผ่าน'] as $val => $label): ?>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_<?= $code ?>" id="status_<?= $code ?>_<?= $val ?>"
                   value="<?= $val ?>" data-item-code="<?= $code ?>">
            <label class="form-check-label" for="status_<?= $code ?>_<?= $val ?>">
              <?= $label ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($grouped_fail_items[$code])): ?>
        <div id="fail_group_<?= $code ?>" class="border p-3 mb-3 bg-light" style="display: none;">
          <?php foreach ($grouped_fail_items[$code] as $fail_item): ?>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="<?= htmlspecialchars($fail_item->field_name) ?>"
                   name="<?= htmlspecialchars($fail_item->field_name) ?>"
                   data-item-code="<?= $code ?>">
            <label class="form-check-label" for="<?= htmlspecialchars($fail_item->field_name) ?>">
              <?= htmlspecialchars($fail_item->label_text) ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="remark_<?= $code ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_<?= $code ?>"
                    data-code="remark_<?= $code ?>"
                    data-item-code="<?= $code ?>"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>



</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    const autosaveUrl = 'ajax/autosave_crew.php';
    const loadAllUrl  = 'ajax/load_crew_all.php';
    </script>
    <script src="../js/checkform.js"></script>

<?
include("../../private/shared/footerall.php");
?>