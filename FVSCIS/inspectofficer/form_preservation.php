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

// ดึง checklist เฉพาะหมวด 5
$fail_items = InspectionFailItem::find_by_section(5);

/** ===== หมวด 5: หัวข้อแบบทั่วไป (type=1) ===== */
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

/** ===== หมวด 5: หัวข้อแบบ EU (type=2) ===== */
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

// จัดกลุ่ม checklist “ไม่ผ่าน”
$grouped_fail_items = [];
foreach ($fail_items as $item) {
  $parts = explode('_', $item->field_name); // fail_5_3_1 → [fail,5,3,1]
  if (count($parts) >= 3) {
    $key = $parts[1] . '_' . $parts[2];     // 5_1, 5_2, ...
    $grouped_fail_items[$key][] = $item;
  }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">
    ด้านการจัดการและการเก็บรักษาสัตว์น้ำ (preservation)
    <span class="badge bg-info ms-2">แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?></span>
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary ms-2" id="btn-back">← กลับไปหน้าฟอร์มตรวจสอบ</a>
  </h1>

  <!-- สวิตช์: เรือห้องเย็น -->
  <div class="mb-3 p-3 border rounded bg-light d-flex align-items-center justify-content-between">
    <div>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="coldRoomSwitch">
        <label class="form-check-label" for="coldRoomSwitch">
          เรือห้องเย็น (มีห้องเย็น/ระบบทำความเย็น ต้องตรวจข้อ 5.8–5.9)
        </label>
      </div>
      <small class="text-muted">ปิดสวิตช์ = ไม่ต้องตรวจข้อ 5.8 และ 5.9</small>
    </div>
  </div>

  <div class="accordion" id="inspectionAccordion">
    <?php foreach ($inspection_items as $code => $title): ?>
      <div class="accordion-item" id="item_<?= htmlspecialchars($code) ?>" data-code="<?= htmlspecialchars($code) ?>">
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

              <!-- กลุ่มสถานะ ผ่าน/ไม่ผ่าน -->
              <div class="mb-3 status-group" id="status_group_<?= htmlspecialchars($code) ?>">
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

              <!-- กลุ่ม checklist ไม่ผ่าน -->
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

              <!-- หมายเหตุ -->
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
  // Endpoint autosave / preload สำหรับหมวด 5
  const autosaveUrl = 'ajax/autosave_preservation.php';
  const loadAllUrl  = 'ajax/load_preservation_all.php';

  // โค้ดสวิตช์เงื่อนไข 5.8–5.9
  (function() {
    const conditionalCodes = ['5_8','5_9'];
    const switchEl = document.getElementById('coldRoomSwitch');

    // เก็บสถานะไว้ใน localStorage ต่อ request_id (กันหายหลังรีเฟรช)
    const storeKey = 'coldroom_flag_req_' + <?= json_encode((string)$request_id) ?>;
    const getFlag  = () => localStorage.getItem(storeKey) === '1';
    const setFlag  = (v) => localStorage.setItem(storeKey, v ? '1' : '0');

    function showItem(code, show) {
      const item = document.getElementById('item_' + code);
      const statusGroup = document.getElementById('status_group_' + code);
      const failGroup = document.getElementById('fail_group_' + code);
      if (!item) return;
      item.style.display = show ? '' : 'none';

      // ถ้าปิด ให้เคลียร์ค่าที่เลือกแล้ว และกระตุ้น change เพื่อให้ autosave เคลียร์ตาม
      if (!show) {
        const form = document.getElementById('form-' + code);
        if (form) {
          form.querySelectorAll('input[type=radio]').forEach(r => {
            if (r.checked) { r.checked = false; r.dispatchEvent(new Event('change', {bubbles:true})); }
          });
          form.querySelectorAll('.checklist-item').forEach(ch => {
            if (ch.checked) { ch.checked = false; ch.dispatchEvent(new Event('change', {bubbles:true})); }
          });
          if (failGroup) failGroup.style.display = 'none';
        }
      } else {
        // เปิดให้ตรวจ: แสดงกลุ่มสถานะ (fail group จะแสดงเองหากเลือก "ไม่ผ่าน")
        if (statusGroup) statusGroup.style.display = '';
      }
    }

    function applyFlag(flag) {
      conditionalCodes.forEach(code => showItem(code, flag));
    }

    // init
    const initFlag = getFlag();
    switchEl.checked = initFlag;
    applyFlag(initFlag);

    // on change
    switchEl.addEventListener('change', () => {
      const flag = switchEl.checked;
      setFlag(flag);
      applyFlag(flag);

      // (ออปชัน) แจ้งเซิร์ฟเวอร์เก็บค่านี้ไว้ หาก backend รองรับ
      try {
        const fd = new FormData();
        fd.append('request_id', <?= json_encode((string)$request_id) ?>);
        fd.append('cold_room_flag', flag ? '1' : '0');
        fetch(autosaveUrl, { method: 'POST', body: fd }); // เซิร์ฟเวอร์จะรับ/หรือมองข้ามก็ได้
      } catch(e) {}
    });
  })();
</script>
<script src="../js/fvscis.js"></script>
<script src="../js/checkform.js"></script>

<?php include("../../private/shared/footerall.php"); ?>
