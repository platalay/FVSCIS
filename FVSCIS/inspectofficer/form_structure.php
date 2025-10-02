<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

$Officer   = Officer::find_by_id($session->user_id());
$request   = InspectionRequest::find_by_id($_GET["request"] ?? null);
if (!$request) {
  header('Location: form_inspect.php');
  exit;
}
$request_id = $request->id;
$form_type  = (int)($request->inspection_form_type ?? 1); // 1=ทั่วไป, 2=EU

// โหลด checklist “ไม่ผ่าน” ของหมวด 1
$fail_items = InspectionFailItem::find_by_section(1);

/** ===== หมวด 1: หัวข้อแบบทั่วไป (type=1) ===== */
$inspection_items_type1 = [
  '1_1' => '1.1 ห้องเก็บรักษาสัตว์น้ำอยู่ในสภาพดี สะอาด มีขนาดเหมาะสมเพียงพอ',
  '1_2' => '1.2 มีโครงสร้างอย่างเหมาะสมโดยมีซอกมุมน้อยที่สุด เพื่อให้ง่ายต่อการรักษาความสะอาด',
  '1_3' => '1.3 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำออกแบบอย่างเหมาะสม ถูกสุขลักษณะ...',
  '1_4' => '1.4 มีพื้นที่เพียงพอและเหมาะสมสำหรับรับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำ...',
  '1_5' => '1.5 พื้นที่ของเรือที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำทำจากวัสดุคงทน ผิวเรียบ ทำความสะอาดง่าย',
  '1_6' => '1.6 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำต้องทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด...',
  '1_7' => '1.7 จัดพื้นที่บริเวณเฉพาะสำหรับเก็บขยะ เศษอาหาร และเศษสัตว์น้ำที่เหลือให้เป็นสัดส่วนแยกออกจากบริเวณพื้นที่ปฏิบัติงาน'
];

/** ===== หมวด 1: หัวข้อแบบ EU (type=2) ===== */
$inspection_items_type2 = [
  '1_1' => '1.1 ห้องเก็บรักษาสัตว์น้ำต้องรักษาความสะอาด มีการบำรุงรักษาให้อยู่ในสภาพดี มีขนาดเหมาะสมเพียงพอ และป้องกันไม่ให้สัตว์น้ำปนเปื้อนกับน้ำท้องเรือประมง สิ่งปฎิกูล ควัน น้ำมันเชื้อเพลิง น้ำมันจาระบี หรือสิ่งสกปรกอื่นๆ ในกรณีขอรับหนังสือรับรอง สร.3 EU ฉบับชั่วคราว ต้องมีเครื่องยนต์พร้อมใช้งานอยู่ในเรือประมง',
  '1_2' => '1.2 มีโครงสร้างอย่างเหมาะสมโดยมีส่วนที่เป็นซอกมุมน้อยที่สุด เพื่อให้ง่ายต่อการรักษาความสะอาด',
  '1_3' => '1.3 พื้นที่ปฎิบัติงานออกแบบอย่างเหมาะสม ถูกสุขลักษณะและไม่ก่อให้เกิดการปนเปื้อนจากน้ำท้องเรืประมง สิ่งปฏิกูล ควัน น้ำมันเชื้อเพลิง น้ำมัน จาระบี หรือสิ่งสกปรกอื่นๆ ไปยังสัตว์น้ำโดยต้องแยกจากส่วนที่เป็นเครื่องยนต์ของเรือประมง และที่พักอาศัยของลูกเรือประมงอย่างชัดเจน',
  '1_4' => '1.4 มีพื้นที่เพียงพอและเหมาะสมสำหรับการรับวัตถุดิบ การคัดเลือก การขนถ่ายสัตว์น้ำ และเก็บรักษาสัตว์น้ำ ต้องรักษาความสะอาด มีการบำรุงรักษาให้อยู่ในสภาพดี และป้องกันไม่ให้ปนเปื้อนกับน้ำท้องเรือประมง สิ่งปฏิกูล ควัน น้ำมันเชื้อเพลิง น้ำมัน จาระบี หรือสิ่งสกปรกอื่นๆ ไปยังสัตว์น้ำ',
  '1_5' => '1.5 พื้นที่ผิวของเรือบริเวณที่ปฏิบัติงาน และห้องเก็บรักษาสัตว์น้ำ ทำจากวัสดุผิวเรียบ ที่สามารถทำความสะอาดได้ง่าย กรณีทาสีต้องดูแลไม่ให้สีหลุดร่อน สีที่ใช้ต้องคงทนและไม่เป็นพิษ',
  '1_6' => '1.6 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำ ต้องทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด ต้องไม่มีสัตว์เลี้ยงในเรือประมงและมีการควบคุม ป้องกัน กำจัดหนู แมลงสาบ และสัตว์อื่นที่เป็นพาหะนำโรค',
  '1_7' => '1.7 จัดพื้นที่บริเวณเฉพาะสำหรับเก็บขยะ เศษอาหาร และเศษสัตว์น้ำที่เหลือให้เป็นสัดส่วน มีฝาปิดมิดชิด แยกออกจากบริเวณพื้นที่ปฏิบัติงาน'
];

// เลือกชุดหัวข้อที่จะใช้
$inspection_items = ($form_type === 2) ? $inspection_items_type2 : $inspection_items_type1;

// จัดกลุ่ม checklist “ไม่ผ่าน” ของหมวด 1 (key = 1_1 ... 1_7)
$grouped_fail_items = [];
foreach ($fail_items as $item) {
  $parts = explode('_', $item->field_name); // ตัวอย่าง: fail_1_1_1 → [fail,1,1,1]
  if (count($parts) >= 3) {
    $key = $parts[1] . '_' . $parts[2];     // 1_1
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
    ด้านโครงสร้างของเรือประมง (structer)
    <span class="badge bg-info ms-2">แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?></span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary ms-2" id="btn-back">
      ← กลับไปหน้าฟอร์มตรวจสอบ
    </a>
  </h1>

  <!-- Accordion: หมวด 1 -->
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
                  <div class="form-check form-check-inline">
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
                <label for="remark_<?= htmlspecialchars($code) ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
                <textarea class="form-control" id="remark_<?= htmlspecialchars($code) ?>"
                          name="remark_<?= htmlspecialchars($code) ?>"
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
  // Endpoint autosave / preload สำหรับหมวด 1
  const autosaveUrl = 'ajax/autosave_structure.php';
  const loadAllUrl  = 'ajax/load_structure_all.php';
</script>
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
