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
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary" id="btn-back">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<?php
// ดึง checklist เฉพาะหมวด 5
$fail_items = InspectionFailItem::find_by_section(5);

// จัดกลุ่ม checklist
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_5_3_1 → [fail, 5, 3, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// รายการข้อสอบถามในหมวด 5
$inspection_items = [
    '5_1' => '5.1 น้ำยาทำความสะอาด น้ำยาฆ่าเชื้อ และยาฆ่าแมลง ต้องเก็บแยกในสถานที่ที่เป็นสัดส่วน ถูกสุขลักษณะ และควบคุมไม่ให้มีโอกาสปนเปื้อนในสัตว์น้ำได้',
    '5_2' => '5.2 เก็บบรรจุสัตว์น้ำในภาชนะบรรจุที่แข็งแรง สะอาด และไม่ซ้อนทับจนทำให้สัตว์น้ำเสียหาย',
    '5_3' => '5.3 เก็บรักษาสัตว์น้ำหลังจากการจับด้วยวิธีการที่เหมาะสมโดยเร็วที่สุด...',
    '5_4' => '5.4 เก็บรักษาสัตว์น้ำอย่างถูกสุขลักษณะ และรักษาอุณหภูมิของสัตว์น้ำให้ใกล้เคียง 0 องศาเซลเซียส...',
    '5_5' => '5.5 วางหรือเก็บรักษาสัตว์น้ำในที่เหมาะสม หากเป็นการแช่เย็นหรือแช่แข็งต้องหลีกเลี่ยงการสัมผัสความร้อนจากแสงแดด หรือความร้อนอื่น ๆ',
    '5_6' => '5.6 มีบันทึกรายละเอียดของแหล่งจับหรือแหล่งที่มาของสัตว์น้ำ พร้อมเก็บไว้เพื่อการตรวจสอบ',
    '5_7' => '5.7 ขนถ่ายสัตว์น้ำอย่างถูกสุขลักษณะ โดยหลีกเลี่ยงการใช้วัสดุอุปกรณ์ที่จะก่อให้เกิดความเสียหายแก่สัตว์น้ำ',
    '5_8' => '5.8 ห้องเย็นเก็บรักษาสัตว์น้ำต้องสามารถควบคุมอุณหภูมิไม่สูงกว่า 18 องศาเซลเซียสและติดตั้งเทอร์โมมิเตอร์หรืออุปกรณ์บันทึกอุณหภูมิ อย่างต่อเนื่องอัตโนมัติ',
    '5_9' => '5.9 กระบวนการทำความเย็นต้องมีประสิทธิภาพที่จะลดอุณหภูมิของสัตว์น้ำได้อย่างทั่วถึง และอุณหภูมิในสัตว์น้ำไม่สูงกว่า 18 องศาเซลเซียส'
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
    const autosaveUrl = 'ajax/autosave_preservation.php';
    const loadAllUrl  = 'ajax/load_preservation_all.php';
    </script>
    <script src="../js/fvscis.js"></script> 
    <script src="../js/checkform.js"></script>

<?
include("../../private/shared/footerall.php");
?>