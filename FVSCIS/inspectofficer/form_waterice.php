<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

$Officer    = Officer::find_by_id($session->user_id());
$request    = InspectionRequest::find_by_id($_GET["request"] ?? null);
if (!$request) {
  header('Location: form_inspect.php');
  exit;
}
$request_id = $request->id;
$form_type  = (int)($request->inspection_form_type ?? 1); // 1=ทั่วไป, 2=EU

// ดึง checklist เฉพาะหมวด 4
$fail_items = InspectionFailItem::find_by_section(4);

/** ===== หมวด 4: หัวข้อแบบทั่วไป (type=1) ===== */
$inspection_items_type1 = [
  '4_1' => '4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน',
  '4_2' => '4.2 สถานที่เก็บ และภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิมและทำความสะอาดได้ง่าย',
  '4_3' => '4.3 มีการถ่ายเท ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ น้ำแข็งลงเรือ',
  '4_4' => '4.4 ภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องมีฝาปิดมิดชิด'
];

/** ===== หมวด 4: หัวข้อแบบ EU (type=2) ===== */
$inspection_items_type2 = [
  '4_1' => '4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน',
  '4_2' => '4.2 การจัดเก็บน้ำจืด น้ำใช้ และน้ำแข็งต้องมีการป้องกัน หรืออยู่ในสถานที่ที่หลีกเลี่ยงการปนเปื้อน',
  '4_3' => '4.3 มีการลำเลียง ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ',
  '4_4' => '4.4 ภาชนะที่บรรจุน้ำจืด น้ำใช้ และน้ำแข็งต้องมีฝาปิดมิดชิดเพื่อป้องกันไม่ให้เกิดการปนเปื้อน อยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิม และทำความสะอาดได้ง่าย'
];

// เลือกชุดหัวข้อที่จะใช้
$inspection_items = ($form_type === 2) ? $inspection_items_type2 : $inspection_items_type1;

// จัดกลุ่ม checklist “ไม่ผ่าน” ของหมวด 4 (key = 4_1 ... 4_4)
$grouped_fail_items = [];
foreach ($fail_items as $item) {
  $parts = explode('_', $item->field_name); // ตัวอย่าง: fail_4_1_1 → [fail,4,1,1]
  if (count($parts) >= 3) {
    $key = $parts[1] . '_' . $parts[2];     // 4_1
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
    ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ (water and ice)
    <span class="badge bg-info ms-2">แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?></span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary ms-2" id="btn-back">
      ← กลับไปหน้าฟอร์มตรวจสอบ
    </a>
  </h1>

  <!-- Accordion: หมวด 4 -->
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
                             name="<?= htmlspecialchars($fail_item->field_name) ?>"
                             data-item-code="<?= htmlspecialchars($code) ?>"
                             data-code="<?= htmlspecialchars($fail_item->field_name) ?>"
                             data-text="<?= htmlspecialchars($fail_item->label_text) ?>">
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
  // Endpoint autosave / preload สำหรับหมวด 4
  const autosaveUrl = 'ajax/autosave_waterice.php';
  const loadAllUrl  = 'ajax/load_waterice_all.php';
</script>
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
