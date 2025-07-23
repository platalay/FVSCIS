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
  <h1 class="h3 mb-4 text-gray-800">ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ (water and ice)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary" id="btn-back">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<?php
// ดึง checklist เฉพาะหมวด 4
$fail_items = InspectionFailItem::find_by_section(4);

// จัดกลุ่ม checklist
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_4_1_1 → [fail, 4, 1, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// รายการข้อสอบถามในหมวด 4
$inspection_items = [
    '4_1' => '4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน',
    '4_2' => '4.2 สถานที่เก็บ และภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิมและทำความสะอาดได้ง่าย',
    '4_3' => '4.3 มีการถ่ายเท ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ น้ำแข็งลงเรือ',
    '4_4' => '4.4 ภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องมีฝาปิดมิดชิด'
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
                   data-item-code="<?= $code ?>"
                   data-text="<?= htmlspecialchars($fail_item->label_text) ?>">
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
    const autosaveUrl = 'ajax/autosave_waterice.php';
    const loadAllUrl  = 'ajax/load_waterice_all.php';
    </script>
    <script src="../js/fvscis.js"></script> 
    <script src="../js/checkform.js"></script>


<?
include("../../private/shared/footerall.php");
?>