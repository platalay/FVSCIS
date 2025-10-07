<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
$Officer = Officer::find_by_id($session->user_id());

include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");

$request    = InspectionRequest::find_by_id($_GET["request"] ?? null);
if (!$request) { header('Location: form_inspect.php'); exit; }
$request_id = $request->id;
$form_type  = (int)($request->inspection_form_type ?? 1); // 1=ทั่วไป, 2=EU

// ใช้ flag จากคำขอ: 1=มีห้องเย็น/ระบบทำความเย็น, 0=ไม่มี
$is_cold_room = (int)($request->cold_room_flag ?? 0);

// ===== รายการหัวข้อหมวด 5 =====
$inspection_items_type1 = [
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

$inspection_items_type2 = [
  '5_1' => '5.1 น้ำยาทำความสะอาด น้ำยาฆ่าเชื้อ และยาฆ่าแมลง ต้องเก็บแยกในสถานที่ที่เป็นสัดส่วน ถูกสุขลักษณะ และควบคุมไม่ให้มีโอกาสปนเปื้อนในสัตว์น้ำได้',
  '5_2' => '5.2 เก็บ บรรจุสัตว์น้ำในภาชนะบรรจุที่แข็งแรง สะอาด และไม่ซ้อนทับจนทำให้สัตว์น้ำเสียหาย',
  '5_3' => '5.3 เก็บรักษาสัตว์น้ำด้วยน้ำแข็ง และต้องรักษาอุณหภูมิของสัตว์น้ำ ให้ได้ความเย็นอย่างสม่ำเสมอทั่วทั้งภาชนะเก็บสัตว์น้ำ',
  '5_4' => '5.4 เก็บรักษาสัตว์น้ำอย่างถูกสุขลักษณะ และรักษาอุณหภูมิของสัตว์น้ำให้ใกล้เคียง 0 องศาเซลเซียส สำหรับสัตว์น้ำสด',
  '5_5' => '5.5 วางหรือเก็บรักษาสัตว์น้ำในที่เหมาะสม และต้องหลีกเลี่ยงการสัมผัสความร้อนจากแสงแดด หรือความร้อนอื่น ๆ',
  '5_6' => '5.6 มีบันทึกรายละเอียดของแหล่งจับหรือแหล่งที่มาของสัตว์น้ำ พร้อมเก็บไว้เพื่อการตรวจสอบ',
  '5_7' => '5.7 ขนถ่ายสัตว์น้ำอย่างถูกสุขลักษณะ โดยหลีกเลี่ยงการใช้วัสดุอุปกรณ์ที่จะก่อให้เกิดความเสียหายแก่สัตว์น้ำ',
  '5_8' => '5.8 ห้องเย็นเก็บรักษาสัตว์น้ำต้องสามารถควบคุมอุณหภูมิไม่สูงกว่า 18 องศาเซลเซียสและติดตั้งเทอร์โมมิเตอร์หรืออุปกรณ์บันทึกอุณหภูมิ อย่างต่อเนื่องอัตโนมัติ',
  '5_9' => '5.9 กระบวนการทำความเย็นต้องมีประสิทธิภาพที่จะลดอุณหภูมิของสัตว์น้ำได้อย่างทั่วถึง และอุณหภูมิในสัตว์น้ำไม่สูงกว่า 18 องศาเซลเซียส'
];

// เลือกชุดหัวข้อ
$inspection_items = ($form_type === 2) ? $inspection_items_type2 : $inspection_items_type1;

// ถ้าไม่ใช่เรือห้องเย็น ให้ตัด 5_8, 5_9 ออกจากแบบฟอร์ม
if (!$is_cold_room) {
  unset($inspection_items['5_8'], $inspection_items['5_9']);
}

// ===== โหลดข้อมูลแบบฟอร์ม (กัน null โดยใช้ find_or_create) =====
$data = InspectionFormPreservation::find_or_create($request_id);

// ===== ดึง “เหตุผลไม่ผ่าน” จากตาราง inspection_fail_items section=5 และจัดกลุ่มตาม 5_x =====
$fail_items = InspectionFailItem::find_by_section(5);
$grouped_fail_items = [];
if (!empty($fail_items) && is_iterable($fail_items)) {
  foreach ($fail_items as $item) {
    $fn = trim((string)$item->field_name);     // เช่น fail_5_4_1
    $parts = explode('_', $fn);                // [fail,5,4,1]
    if (count($parts) >= 3 && $parts[0] === 'fail' && ctype_digit($parts[1]) && ctype_digit($parts[2])) {
      $key = $parts[1] . '_' . $parts[2];      // 5_4
      // ถ้าไม่ใช่เรือห้องเย็น ข้ามเหตุผลของ 5_8, 5_9 ไปเลย
      if (!$is_cold_room && ($key === '5_8' || $key === '5_9')) { continue; }
      $grouped_fail_items[$key][] = $item;
    }
  }
}
?>
<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">
    ด้านการจัดการและการเก็บรักษาสัตว์น้ำ (preservation)
    <span class="badge bg-info ms-2">แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?></span>
    <span class="badge bg-secondary ms-2">เรือห้องเย็น: <?= $is_cold_room ? 'ใช่' : 'ไม่ใช่' ?></span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary ms-2" id="btn-back">← กลับไปหน้าฟอร์มตรวจสอบ</a>
  </h1>

  <div class="accordion" id="inspectionAccordion">
    <?php foreach ($inspection_items as $code => $title): ?>
      <?php
        // status/remark field ของข้อปัจจุบัน
        $status_field = 'status_' . $code;     // เช่น status_5_4
        $remark_field = 'remark_' . $code;     // เช่น remark_5_4

        $status_value = $data?->$status_field ?? null;
        $remark_value = $data?->$remark_field ?? '';

        $fail_list   = $grouped_fail_items[$code] ?? [];
        $fail_count  = count($fail_list);

        // ควรแสดงกล่องเหตุผลไหม: ถ้าเลือก "ไม่ผ่าน" หรือมีช่องถูกติ๊กเดิม
        $should_show = ($status_value === 'fail');
        if (!$should_show && $fail_count > 0) {
          foreach ($fail_list as $fi) {
            $ff = $fi->field_name; if (!empty($data?->$ff)) { $should_show = true; break; }
          }
        }
      ?>

      <div class="accordion-item" id="item_<?= htmlspecialchars($code) ?>" data-code="<?= htmlspecialchars($code) ?>">
        <h2 class="accordion-header" id="heading<?= htmlspecialchars($code) ?>">
          <button class="accordion-button collapsed bg-primary text-white" type="button"
                  data-bs-toggle="collapse" data-bs-target="#collapse<?= htmlspecialchars($code) ?>"
                  aria-expanded="false" aria-controls="collapse<?= htmlspecialchars($code) ?>">
            <span class="me-2"><?= htmlspecialchars($title) ?></span>
            <?php if ($fail_count > 0): ?>
              <span class="badge bg-warning text-dark ms-2">เหตุผลไม่ผ่าน: <?= (int)$fail_count ?></span>
            <?php endif; ?>
          </button>
        </h2>

        <div id="collapse<?= htmlspecialchars($code) ?>" class="accordion-collapse collapse"
             aria-labelledby="heading<?= htmlspecialchars($code) ?>" data-bs-parent="#inspectionAccordion">
          <div class="accordion-body">

            <form id="form-<?= htmlspecialchars($code) ?>" class="form-inspect" data-item-code="<?= htmlspecialchars($code) ?>">
              <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

              <!-- สถานะ ผ่าน/ไม่ผ่าน -->
              <div class="mb-3 status-group" id="status_group_<?= htmlspecialchars($code) ?>">
                <?php
                  $opts = ['pass' => 'ผ่าน', 'fail' => 'ไม่ผ่าน'];
                  foreach ($opts as $val => $label) {
                      $id = "status_{$code}_{$val}";
                      $checked = ($status_value === $val) ? ' checked' : '';
                      echo '<div class="form-check mb-2">';
                      echo    '<input class="form-check-input form-status-radio status-radio" type="radio"'
                            . ' name="' . htmlspecialchars($status_field, ENT_QUOTES, 'UTF-8') . '"'
                            . ' id="'   . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"'
                            . ' value="' . htmlspecialchars($val, ENT_QUOTES, 'UTF-8') . '"'
                            . ' data-item-code="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '"'
                            . $checked
                            . '>';
                      echo    '<label class="form-check-label" for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">'
                            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
                            . '</label>';
                      echo '</div>';
                  }
                ?>
              </div>

              <!-- กล่องเหตุผลไม่ผ่าน (checkbox ตามฟิลด์ในคลาส) -->
              <div id="fail_group_<?= htmlspecialchars($code) ?>"
                   class="border p-3 mb-3 bg-light"
                   style="<?= $should_show ? '' : 'display:none;' ?>">

                <?php if ($fail_count > 0): ?>
                  <?php foreach ($fail_list as $fail_item): ?>
                    <?php
                      $fail_field = trim($fail_item->field_name);           // เช่น fail_5_4_1
                      $is_checked = !empty($data?->$fail_field) ? ' checked' : '';
                      $input_id   = $fail_field;                            // ใช้ชื่อเดียวกัน
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
                <label for="remark_<?= htmlspecialchars($code) ?>" class="form-label">หมายเหตุ (ถ้ามี):</label>
                <textarea class="form-control checklist-remark"
                          id="remark_<?= htmlspecialchars($code) ?>"
                          name="<?= htmlspecialchars($remark_field) ?>"
                          data-code="<?= htmlspecialchars($remark_field) ?>"
                          data-item-code="<?= htmlspecialchars($code) ?>"
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
  // Endpoint autosave / preload (ใช้ของระบบเดิม)
  const autosaveUrl = 'ajax/autosave_preservation.php';
  const loadAllUrl  = 'ajax/load_preservation_all.php';

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
