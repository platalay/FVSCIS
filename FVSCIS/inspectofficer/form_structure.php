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

$section = 1; // หมวดที่ 1: โครงสร้างเรือ (structure)

// ===== โหลดข้อมูลแบบฟอร์ม (กัน null) =====
if (class_exists('InspectionFormStructure')) {
  $data = InspectionFormStructure::find_or_create($request_id);
} else {
  $data = new stdClass();
}

// ===== ดึงหัวข้อของหมวด 1 จาก inspection_main_items ตามประเภทฟอร์ม =====
// form_type: 1 = ทั่วไป, 2 = EU
$main_items = InspectionMainItem::find_by_section_and_category($section, $form_type);

// เตรียม map สำหรับใช้ใน template เดิม
$inspection_items   = []; // ['1_1' => 'หัวข้อ...', ...]
$main_item_ids      = []; // [id1, id2, ...]
$id_to_section_code = []; // [id => '1_1']

if (!empty($main_items)) {
    foreach ($main_items as $mi) {
        $code = trim((string)$mi->section_code); // เช่น "1_1"
        if ($code === '') { continue; }

        $inspection_items[$code] = $mi->title_th;
        $main_item_ids[]         = (int)$mi->id;
        $id_to_section_code[(int)$mi->id] = $code;
    }
}

// ===== ดึง fail items ของทุก main_item ในหมวดนี้ =====
$grouped_fail_items = []; // key = section_code (เช่น "1_1"), value = array of fail_items

if (!empty($main_item_ids)) {
    $fail_items = InspectionFailItem::find_by_main_item_ids($main_item_ids);

    if (!empty($fail_items)) {
        foreach ($fail_items as $fi) {
            $mid = (int)$fi->main_item_id;
            if (!isset($id_to_section_code[$mid])) {
                continue;
            }
            $code = $id_to_section_code[$mid]; // "1_1", "1_2", ...

            // เก็บแบบ group ตาม section_code เพื่อใช้กับ template เดิม
            $grouped_fail_items[$code][] = $fi;
        }
    }
}
?>

<!-- Begin Page Content -->
<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">
    ด้านโครงสร้างของเรือประมง (structure)
    <span class="badge bg-info ms-2">
  แบบที่ <?= ($form_type === 2 ? '2 (EU)' : '1') ?>
</span>

<?php if ($request->license_status === 'normal'): ?>
  <span class="badge bg-success ms-2 mb-1">
    มีใบอนุญาตทำการประมง
  </span>
<?php else: ?>
  <span class="badge bg-danger ms-2 mb-1">
    ไม่มีใบอนุญาตทำการประมง
  </span>
<?php endif; ?>

    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary ms-2" id="btn-back">← กลับไปหน้าฟอร์มตรวจสอบ</a>
  </h1>

  <div class="accordion" id="inspectionAccordion">
    <?php foreach ($inspection_items as $code => $title): ?>
      <?php
        // status/remark field ของข้อปัจจุบัน
        $status_field = 'status_' . $code;     // เช่น status_1_4
        $remark_field = 'remark_' . $code;     // เช่น remark_1_4

        $status_value = $data?->$status_field ?? null;
        $remark_value = $data?->$remark_field ?? '';

        $fail_list   = $grouped_fail_items[$code] ?? [];
        $fail_count  = count($fail_list);

        // ควรแสดงกล่องเหตุผลไหม: ถ้าเลือก "ไม่ผ่าน" หรือมีช่องถูกติ๊กเดิม
        $should_show = ($status_value === 'fail');
        if (!$should_show && $fail_count > 0) {
          foreach ($fail_list as $fi) {
            // สร้างชื่อฟิลด์จาก code + fail_code → fail_1_4_1
            $ff = 'fail_' . $code . '_' . $fi->fail_code;
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

              <!-- กล่องเหตุผลไม่ผ่าน (checkbox ตามฟิลด์) -->
              <div id="fail_group_<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                   class="border p-3 mb-3 bg-light"
                   style="<?= $should_show ? '' : 'display:none;' ?>">

                <?php if ($fail_count > 0): ?>
                <?php foreach ($fail_list as $fail_item): ?>
                  <?php
                    // $code เช่น "1_1", "2_4"
                    // $fail_item->fail_code ตอนนี้ใน DB อาจเป็น "1" หรือ "fail_1_1_1" ก็ได้

                    $raw_code   = trim((string)$fail_item->fail_code);
                    $basePrefix = 'fail_' . $code . '_'; // เช่น "fail_1_1_"

                    if (strpos($raw_code, 'fail_') === 0) {
                        // กรณีใน DB เก็บ full field name แล้ว เช่น "fail_1_1_1"
                        $fail_field = $raw_code;
                    } else {
                        // กรณีใน DB เก็บเป็นเลขลำดับ เช่น "1", "2"
                        $fail_field = $basePrefix . $raw_code;  // → fail_1_1_1
                    }

                    $is_checked = !empty($data?->$fail_field) ? ' checked' : '';
                    $input_id   = $fail_field;
                  ?>
                  <div class="form-check mb-2">
                    <input class="form-check-input checklist-item"
                          type="checkbox"
                          id="<?= htmlspecialchars($input_id, ENT_QUOTES, 'UTF-8') ?>"
                          name="<?= htmlspecialchars($fail_field, ENT_QUOTES, 'UTF-8') ?>"
                          <?= $is_checked ?>
                          data-item-code="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>"
                          data-code="<?= htmlspecialchars($fail_field, ENT_QUOTES, 'UTF-8') ?>"
                          data-text="<?= htmlspecialchars($fail_item->label_text, ENT_QUOTES, 'UTF-8') ?>"
                    >
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
  // Endpoint autosave / preload (หมวด 1)
  const autosaveUrl = 'ajax/autosave_structure.php';
  const loadAllUrl  = 'ajax/load_structure_all.php';

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

