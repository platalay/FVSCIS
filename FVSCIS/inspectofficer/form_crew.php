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

// ===== โหลดข้อมูลฟอร์มของหมวด 3 (กัน null) =====
// ถ้ามีคลาสเฉพาะหมวด 3 (เช่น InspectionFormCrew) ให้ใช้, ไม่งั้นกันล้มด้วย stdClass
if (class_exists('InspectionFormCrew')) {
  $data = InspectionFormCrew::find_or_create($request_id);
} else {
  $data = new stdClass();
}

// จัดกลุ่ม checklist “ไม่ผ่าน” ของหมวด 3 (key = 3_1 ... 3_5)
$grouped_fail_items = [];
if (!empty($fail_items) && is_iterable($fail_items)) {
  foreach ($fail_items as $item) {
    $fn = trim((string)$item->field_name); // ตัวอย่าง: fail_3_1_1
    $parts = explode('_', $fn);            // [fail,3,1,1]
    if (count($parts) >= 3 && $parts[0] === 'fail' && ctype_digit($parts[1]) && ctype_digit($parts[2])) {
      $key = $parts[1] . '_' . $parts[2];  // 3_1
      $grouped_fail_items[$key][] = $item;
    }
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
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary ms-2" id="btn-back">
      ← กลับไปหน้าฟอร์มตรวจสอบ
    </a>
  </h1>

  <!-- Accordion: หมวด 3 -->
  <div class="accordion" id="inspectionAccordion">
    <?php foreach ($inspection_items as $code => $title): ?>
      <?php
        // ฟิลด์ของข้อปัจจุบัน
        $status_field = 'status_' . $code;   // เช่น status_3_1
        $remark_field = 'remark_' . $code;   // เช่น remark_3_1

        $status_value = $data?->$status_field ?? null;
        $remark_value = $data?->$remark_field ?? '';

        $fail_list  = $grouped_fail_items[$code] ?? [];
        $fail_count = count($fail_list);

        // ควรแสดงกล่องเหตุผลไหม: ถ้าเลือก "ไม่ผ่าน" หรือมีช่องถูกติ๊กเดิม
        $should_show = ($status_value === 'fail');
        if (!$should_show && $fail_count > 0) {
          foreach ($fail_list as $fi) {
            $ff = $fi->field_name;
            if (!empty($data?->$ff)) { $should_show = true; break; }
          }
        }
      ?>
      <div class="accordion-item" id="item_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" data-code="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
        <h2 class="accordion-header" id="heading<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
          <button class="accordion-button collapsed bg-primary text-white" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                  aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
            <span class="me-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
            <?php if ($fail_count > 0): ?>
              <span class="badge bg-warning text-dark ms-2">เหตุผลไม่ผ่าน: <?= (int)$fail_count ?></span>
            <?php endif; ?>
          </button>
        </h2>

        <div id="collapse<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" class="accordion-collapse collapse"
             aria-labelledby="heading<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" data-bs-parent="#inspectionAccordion">
          <div class="accordion-body">
            <form id="form-<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" class="form-inspect" data-item-code="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id, ENT_QUOTES, 'UTF-8') ?>">

              <!-- สถานะ ผ่าน/ไม่ผ่าน -->
              <div class="mb-3 status-group" id="status_group_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>">
                <?php
                  $opts = ['pass' => 'ผ่าน', 'fail' => 'ไม่ผ่าน'];
                  foreach ($opts as $val => $label) {
                    $id = "status_{$code}_{$val}";
                    $checked = ($status_value === $val) ? ' checked' : '';
                    echo '<div class="form-check mb-2">';
                    echo   '<input class="form-check-input form-status-radio status-radio" type="radio"'
                         . ' name="' . htmlspecialchars($status_field, ENT_QUOTES, 'UTF-8') . '"'
                         . ' id="'   . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"'
                         . ' value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                         . ' data-item-code="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"'
                         . $checked
                         . '>';
                    echo   '<label class="form-check-label" for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">'
                         . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                         . '</label>';
                    echo '</div>';
                  }
                ?>
              </div>

              <!-- กล่องเหตุผลไม่ผ่าน (checkbox) -->
              <div id="fail_group_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                   class="border p-3 mb-3 bg-light"
                   style="<?= $should_show ? '' : 'display:none;' ?>">

                <?php if ($fail_count > 0): ?>
                  <?php foreach ($fail_list as $fail_item): ?>
                    <?php
                      $fail_field = trim($fail_item->field_name);         // เช่น fail_3_1_1
                      $is_checked = !empty($data?->$fail_field) ? ' checked' : '';
                      $input_id   = $fail_field;
                    ?>
                    <div class="form-check mb-2">
                      <?php
                        echo '<input class="form-check-input checklist-item" type="checkbox"'
                           .  ' id="'   . htmlspecialchars($input_id, ENT_QUOTES, 'UTF-8') . '"'
                           .  ' name="' . htmlspecialchars($fail_field, ENT_QUOTES, 'UTF-8') . '"'
                           .  $is_checked
                           .  ' data-item-code="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"'
                           .  ' data-code="' . htmlspecialchars($fail_field, ENT_QUOTES, 'UTF-8') . '"'
                           .  ' data-text="' . htmlspecialchars($fail_item->label_text, ENT_QUOTES, 'UTF-8') . '"'
                           .  '>';
                      ?>
                      <label class="form-check-label" for="<?= htmlspecialchars($input_id, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($fail_item->label_text, ENT_QUOTES, 'UTF-8') ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-muted">— ไม่มีเหตุผลไม่ผ่านกำหนดไว้สำหรับข้อนี้ —</div>
                <?php endif; ?>
              </div>

              <!-- หมายเหตุ -->
              <div class="mb-3">
                <label for="remark_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
                <textarea class="form-control checklist-remark"
                          id="remark_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                          name="<?= htmlspecialchars($remark_field, ENT_QUOTES, 'UTF-8') ?>"
                          data-code="<?= htmlspecialchars($remark_field, ENT_QUOTES, 'UTF-8') ?>"
                          data-item-code="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                          placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."><?= htmlspecialchars($remark_value, ENT_QUOTES, 'UTF-8') ?></textarea>
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

  // แสดง/ซ่อนกล่องเหตุผลเมื่อเลือกสถานะ
  document.addEventListener('change', function(e){
    if (e.target.matches('.status-radio')) {
      const code = e.target.dataset.itemCode;
      const group = document.getElementById('fail_group_' + code);
      if (!group) return;
      group.style.display = (e.target.value === 'fail') ? '' : 'none';
    }
  });

  // เปิดกล่องเหตุผลอัตโนมัติถ้าค่าเดิมเป็น fail
  window.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.status-radio:checked').forEach(r => {
      const code = r.dataset.itemCode;
      if (r.value === 'fail') {
        const group = document.getElementById('fail_group_' + code);
        if (group) group.style.display = '';
      }
    });
  });
</script>
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
