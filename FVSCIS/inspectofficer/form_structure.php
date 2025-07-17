<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
$request_id = $request->id;
$fail_items = InspectionFailItem::find_by_section(1);
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านโครงสร้างของเรือประมง (structer)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary" id="btn-back">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<?php
// จัดกลุ่ม checklist
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_1_1_1 → [fail, 1, 1, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

$inspection_items = [
    '1_1' => '1.1 ห้องเก็บรักษาสัตว์น้ำอยู่ในสภาพดี สะอาด มีขนาดเหมาะสมเพียงพอ',
    '1_2' => '1.2 มีโครงสร้างอย่างเหมาะสมโดยมีซอกมุมน้อยที่สุด เพื่อให้ง่ายต่อการรักษาความสะอาด',
    '1_3' => '1.3 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำออกแบบอย่างเหมาะสม ถูกสุขลักษณะ...',
    '1_4' => '1.4 มีพื้นที่เพียงพอและเหมาะสมสำหรับรับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำ...',
    '1_5' => '1.5 พื้นที่ของเรือที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำทำจากวัสดุคงทน ผิวเรียบ ทำความสะอาดง่าย',
    '1_6' => '1.6 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำต้องทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด...',
    '1_7' => '1.7 จัดพื้นที่บริเวณเฉพาะสำหรับเก็บขยะ เศษอาหาร และเศษสัตว์น้ำที่เหลือให้เป็นสัดส่วนแยกออกจากบริเวณพื้นที่ปฏิบัติงาน'
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
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_<?= $code ?>" id="status_<?= $code ?>_<?= $val ?>"
                   value="<?= $val ?>" data-item-code="<?= $code ?>">
            <label class="form-check-label" for="status_<?= $code ?>_<?= $val ?>"><?= $label ?></label>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($grouped_fail_items[$code])): ?>
        <div id="fail_group_<?= $code ?>" class="border p-3 mb-3 bg-light" style="display:none;">
          <?php foreach ($grouped_fail_items[$code] as $fail_item): ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   id="<?= htmlspecialchars($fail_item->field_name) ?>"
                   name="<?= htmlspecialchars($fail_item->field_name) ?>">
            <label class="form-check-label" for="<?= htmlspecialchars($fail_item->field_name) ?>">
              <?= htmlspecialchars($fail_item->label_text) ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="remark_<?= $code ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_<?= $code ?>" name="remark_<?= $code ?>" placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
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
    const autosaveUrl = 'ajax/autosave_structure.php';
    const loadAllUrl  = 'ajax/load_structure_all.php';
    </script>
    <script src="../js/checkform.js"></script>

<?
include("../../private/shared/footerall.php");
?>