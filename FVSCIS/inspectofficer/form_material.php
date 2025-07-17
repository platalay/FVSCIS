<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
$request_id = $request->id;
$fail_items = InspectionFailItem::find_by_section(2);
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง (structer)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary" id="btn-back">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  

<?php
// จัดกลุ่ม checklist ของหมวด 2
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_2_1_1 → [fail, 2, 1, 1]
    $key = $parts[1] . '_' . $parts[2];      // 2_1
    $grouped_fail_items[$key][] = $item;
}

// หัวข้อหมวด 2
$inspection_items = [
    '2_1' => '2.1 วัสดุ อุปกรณ์และเครื่องมือที่ใช้ทำความสะอาดแล้วต้องมีที่เก็บอย่างเหมาะสม...',
    '2_2' => '2.2 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่มีผิวเรียบ ไม่มีรอยแตก...',
    '2_3' => '2.3 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องออกแบบให้เหมาะสมกับการใช้งานและสะดวกในการรักษาความสะอาด',
    '2_4' => '2.4 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องล้างทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด (น้ำประปา)',
    '2_5' => '2.5 ภาชนะที่บรรจุสัตว์น้ำมีสภาพแข็งแรง น้ำหนักเบา และสามารถรับน้ำหนักได้ในกรณีที่ต้องวางซ้อนกัน เพื่อป้องกันไม่ให้ภาชนะกดทับสัตว์น้ำ',
    '2_6' => '2.6 ภาชนะที่บรรจุสัตว์น้ำควรมีรูหรือช่องระบายน้ำได้ดี เช่น ภาชนะพลาสติก',
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
                   data-code="<?= htmlspecialchars($fail_item->field_name) ?>"
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
    const autosaveUrl = 'ajax/autosave_material.php';
    const loadAllUrl  = 'ajax/load_material_all.php';
    </script>
    <script src="../js/checkform.js"></script>


<?
include("../../private/shared/footerall.php");
?>