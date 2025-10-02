<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

$Officer = Officer::find_by_id($session->user_id());

// โหลดคำขอ
$request = InspectionRequest::find_by_id($_GET["request"] ?? null);
if (!$request) {
  header('Location: form_inspect.php');
  exit;
}
$request_id = $request->id;
$form_type  = (int)($request->inspection_form_type ?? 1); // 1=ทั่วไป, 2=EU

// โหลด checklist “ไม่ผ่าน” ของหมวด 3
$fail_items = InspectionFailItem::find_by_section(3);

/** ===== หมวด 3: หัวข้อแบบทั่วไป (type=1) ===== */
$inspection_items_type1 = [
  '3_1' => '3.1 บุคลากรที่ปฏิบัติงานในเรือต้องมีสุขภาพดี...',
  '3_2' => '3.2 ผ่านการฝึกอบรมเรื่องสุขอนามัยที่ควรปฏิบัติในเรือประมง',
  '3_3' => '3.3 ล้างมือให้สะอาดทั้งก่อนและหลังการปฏิบัติงานทุกครั้ง รวมทั้งในระหว่างการปฏิบัติงานตามความเหมาะสมและทุกครั้งหลังการใช้สุขา',
  '3_4' => '3.4 เสื้อผ้าที่ใส่ทำงานต้องสะอาด และเหมาะสมกับการปฏิบัติงาน',
  '3_5' => '3.5 ไม่รับประทานอาหารหรือสูบบุหรี่ไม่ไอหรือจามใส่สัตว์น้ำขณะปฏิบัติงาน'
];

/** ===== หมวด 3: หัวข้อแบบ EU (type=2) ===== */
$inspection_items_type2 = [
  '3_1' => '3.1 บุคคลากรที่ปฎิบัติงานในเรือประมงต้องมีสุขภาพดี ไม่เป็นโรคติดต่อร้ายแรง ไม่เป็นพาหะของโรคทางเดินอาหาร และไม่มีแผลเปิดหรือแผลติดเชื้อ',
  '3_2' => '3.2 ผ่านการฝึกอบรมหรือได้รับการถ่ายทอดความรู้เรื่องสุขอนามัยที่ควรปฏิบัติในเรือประมง',
  '3_3' => '3.3 ล้างมือให้สะอาดทั้งก่อน ระหว่าง และหลังการปฏิบัติงานทุกครั้ง ตามความเหมาะสม และทุกครั้งหลังการใช้สุขา',
  '3_4' => '3.4 เสื้อผ้าที่ใส่ทำงานต้องสะอาด และเหมาะสมกับการปฏิบัติงาน',
  '3_5' => '3.5 ไม่รับประทานอาหารหรือสูบบุหรี่ และไม่ไอหรือจามใส่สัตว์น้ำขณะปฏิบัติงาน'
];

// เลือกชุดหัวข้อที่จะใช้
$inspection_items = ($form_type === 2) ? $inspection_items_type2 : $inspection_items_type1;

// จัดกลุ่ม checklist “ไม่ผ่าน” ของหมวด 3 (key = 3_1 ... 3_5)
$grouped_fail_items = [];
foreach ($fail_items as $item) {
  $parts = explode('_', $item->field_name); // ตัวอย่าง: fail_3_1_1 → [fail,3,1,1]
  if (count($parts) >= 3) {
    $key = $parts[1] . '_' . $parts[2];     // 3_1
    $grouped_fail_items[$key][] = $item;
  }
}

// includes
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">
    ด้านบุคลากรประจำเรือ (crew)
    <span class="badge bg-info ms-2">แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?></span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary ms-2" id="btn-back">
      ← กลับไปหน้าฟอร์มตรวจสอบ
    </a>
  </h1>

  <!-- Accordion: หมวด 3 -->
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
  // Endpoint autosave / preload สำหรับหมวด 3
  const autosaveUrl = 'ajax/autosave_crew.php';
  const loadAllUrl  = 'ajax/load_crew_all.php';
</script>
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
