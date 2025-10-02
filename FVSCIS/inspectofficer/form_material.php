<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

$Officer   = Officer::find_by_id($session->user_id());
$request   = InspectionRequest::find_by_id($_GET["request"] ?? null);
if (!$request) {
  // กันกรณีไม่มีคำขอ
  header('Location: form_inspect_list.php');
  exit;
}

$request_id = $request->id;
$form_type  = (int)($request->inspection_form_type ?? 1); // 1=ทั่วไป, 2=EU

// โหลด checklist “ไม่ผ่าน” ของหมวด 2 จากฐานข้อมูล (ใช้โครงสร้างเดิม)
$fail_items = InspectionFailItem::find_by_section(2);

// ====== หมวด 2: หัวข้อแบบทั่วไป (type=1) ======
$inspection_items_type1 = [
  '2_1' => '2.1 วัสดุ อุปกรณ์และเครื่องมือที่ใช้ทำความสะอาดแล้วต้องมีที่เก็บอย่างเหมาะสม...',
  '2_2' => '2.2 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่มีผิวเรียบ ไม่มีรอยแตก...',
  '2_3' => '2.3 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องออกแบบให้เหมาะสมกับการใช้งานและสะดวกในการรักษาความสะอาด',
  '2_4' => '2.4 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องล้างทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด (น้ำประปา)',
  '2_5' => '2.5 ภาชนะที่บรรจุสัตว์น้ำมีสภาพแข็งแรง น้ำหนักเบา และสามารถรับน้ำหนักได้ในกรณีที่ต้องวางซ้อนกัน เพื่อป้องกันไม่ให้ภาชนะกดทับสัตว์น้ำ',
  '2_6' => '2.6 ภาชนะที่บรรจุสัตว์น้ำควรมีรูหรือช่องระบายน้ำได้ดี เช่น ภาชนะพลาสติก',
];

// ====== หมวด 2: หัวข้อแบบ EU (type=2) ======
$inspection_items_type2 = [
  '2_1' => '2.1 วัสดุ อุปกรณ์และเครื่องมือที่ล้างทำความสะอาดแล้วต้องมีที่เก็บอย่างเหมาะสม สามารถป้องกันไม่ให้เกิดการปนเปื้อน โดยเฉพาะพื้นผิวที่ต้องสัมผัสสัตว์น้ำ',
  '2_2' => '2.2 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่มีผิวเรียบ ไม่มีรอยแตก ทำความสะอาดง่าย ไม่ดูดซับน้ำและไม่เป็นสนิม',
  '2_3' => '2.3 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องมีความเหมาะสมกับการใช้งานและสะดวกในการรักษาความสะอาด',
  '2_4' => '2.4 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องล้างทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด',
  '2_5' => '2.5 ภาชนะที่บรรจุสัตว์น้ำมีสภาพแข็งแรง น้ำหนักเบา สามารถรับน้ำหนักได้ในกรณีที่ต้องวางซ้อนกัน เพื่อป้องกันไม่ให้ภาชนะกดทับสัตว์น้ำ สามารถป้องกันไม่ให้สัตว์น้ำปนเปื้นกับน้ำท้องเรือประมง สิ่งปฎิกูล ควัน น้ำมันเชื้อเพลิง น้ำมัน จาระบี หรือสิ่งสกปรกอื่น ๆ และห้ามนำไปใช้บรรจุสิ่งของอื่นนอกเหนือจากการเก็บรักษาสัตว์น้ำ',
  '2_6' => '2.6 ภาชนะที่บรรจุสัตว์น้ำควรมีรูหรือช่องระบายน้ำได้ดี เช่น ภาชนะพลาสติก',
];

// เลือกชุดหัวข้อที่จะใช้ตามแบบฟอร์ม
$inspection_items = ($form_type === 2) ? $inspection_items_type2 : $inspection_items_type1;

// จัดกลุ่ม checklist “ไม่ผ่าน” ของหมวด 2 (key = 2_1 ... 2_6)
$grouped_fail_items = [];
foreach ($fail_items as $item) {
  $parts = explode('_', $item->field_name); // ตัวอย่าง: fail_2_1_1 → [fail,2,1,1]
  if (count($parts) >= 3) {
    $key = $parts[1] . '_' . $parts[2];     // 2_1
    $grouped_fail_items[$key][] = $item;
  }
}

include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>
<!-- Begin Page Content -->
<div class="container-fluid">

  <h1 class="h3 mb-4 text-gray-800">
    ด้านวัสดุ อุปกรณ์ และเครื่องมือในเรือประมง (structer)
    <span class="badge bg-info ms-2">
      แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?>
    </span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary ms-2" id="btn-back">
      ← กลับไปหน้าฟอร์มตรวจสอบ
    </a>
  </h1>

  <!-- Accordion: หมวด 2 -->
  <div class="accordion" id="inspectionAccordion">
    <?php foreach ($inspection_items as $code => $title): ?>
      <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= htmlspecialchars($code) ?>">
          <button class="accordion-button collapsed bg-primary text-white" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($code) ?>"
                  aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($code) ?>">
            <?= htmlspecialchars($title) ?>
          </button>
        </h2>

        <div id="collapse<?= htmlspecialchars($code) ?>" class="accordion-collapse collapse"
             aria-labelledby="heading<?= htmlspecialchars($code) ?>" data-bs-parent="#inspectionAccordion">
          <div class="accordion-body">
            <form id="form-<?= htmlspecialchars($code) ?>" class="form-inspect" data-item-code="<?= htmlspecialchars($code) ?>">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

              <div class="mb-3">
                <?php foreach (['pass' => 'ผ่าน', 'fail' => 'ไม่ผ่าน'] as $val => $label): ?>
                  <div class="form-check mb-2">
                    <input class="form-check-input form-status-radio" type="radio"
                           name="status_<?= htmlspecialchars($code) ?>"
                           id="status_<?= htmlspecialchars($code) ?>_<?= htmlspecialchars($val) ?>"
                           value="<?= htmlspecialchars($val) ?>"
                           data-item-code="<?= htmlspecialchars($code) ?>">
                    <label class="form-check-label" for="status_<?= htmlspecialchars($code) ?>_<?= htmlspecialchars($val) ?>">
                      <?= htmlspecialchars($label) ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>

              <?php if (!empty($grouped_fail_items[$code])): ?>
                <div id="fail_group_<?= htmlspecialchars($code) ?>" class="border p-3 mb-3 bg-light" style="display:none;">
                  <?php foreach ($grouped_fail_items[$code] as $fail_item): ?>
                    <div class="form-check mb-2">
                      <input class="form-check-input checklist-item" type="checkbox"
                             id="<?= htmlspecialchars($fail_item->field_name) ?>"
                             data-code="<?= htmlspecialchars($fail_item->field_name) ?>"
                             data-item-code="<?= htmlspecialchars($code) ?>">
                      <label class="form-check-label" for="<?= htmlspecialchars($fail_item->field_name) ?>">
                        <?= htmlspecialchars($fail_item->label_text) ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="mb-3">
                <label for="remark_<?= htmlspecialchars($code) ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
                <textarea class="form-control checklist-remark"
                          id="remark_<?= htmlspecialchars($code) ?>"
                          data-code="remark_<?= htmlspecialchars($code) ?>"
                          data-item-code="<?= htmlspecialchars($code) ?>"
                          placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
              </div>

            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div><!-- /accordion -->

</div><!-- /.container-fluid -->

<?php include("../../private/shared/footerofficer.php"); ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Endpoint autosave / preload (คงชื่อเดิมของคุณ)
const autosaveUrl = 'ajax/autosave_material.php';
const loadAllUrl  = 'ajax/load_material_all.php';
</script>

<!-- JS เดิมของโปรเจ็กต์ -->
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
