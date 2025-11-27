<?php
// หมวด 5: การเก็บรักษา (Preservation)
$data = InspectionFormPreservation::find_by_request_id($request->id);

$check   = '✔';
$cross   = '✖';
$pending = '⏳';

// ประเภทฟอร์ม: 1 = ทั่วไป, 2 = EU
$form_type = (int)($request->inspection_form_type ?? 1);

// section 5 = ด้านการเก็บรักษา
$section = 5;

// ===== 1) ดึง main items ของหมวด 5 จาก inspection_main_items =====
$main_items = InspectionMainItem::find_by_section_and_category($section, $form_type);

$main_item_ids      = [];
$id_to_section_code = []; // map main_item_id → "5_1", "5_2", ...

if (!empty($main_items)) {
    foreach ($main_items as $mi) {
        $code = trim((string)$mi->section_code); // เช่น "5_1"
        if ($code === '') { continue; }

        $mid = (int)$mi->id;
        $main_item_ids[]          = $mid;
        $id_to_section_code[$mid] = $code;
    }
}

// ===== 2) ดึง fail items ทั้งหมดของ main_item_ids =====
$grouped_fail_items = []; // key = section_code ("5_1") => array ของ fail items

if (!empty($main_item_ids)) {
    $fail_items = InspectionFailItem::find_by_main_item_ids($main_item_ids);

    if (!empty($fail_items)) {
        foreach ($fail_items as $fi) {
            $mid = (int)$fi->main_item_id;
            if (!isset($id_to_section_code[$mid])) {
                continue;
            }
            $code = $id_to_section_code[$mid]; // "5_1", "5_2", ...

            $grouped_fail_items[$code][] = $fi;
        }
    }
}
?>

<table class="table table-bordered table-striped mt-4">
  <thead class="table-light">
    <tr>
      <th>รายการตรวจประเมิน</th>
      <th class="text-center">ผ่าน/ไม่ผ่าน</th>
      <th>ข้อบกพร่องที่พบ</th>
    </tr>
  </thead>
  <tbody>

  <?php if (!empty($main_items)): ?>
    <?php foreach ($main_items as $mi): ?>
      <?php
        $code         = $mi->section_code;        // เช่น "5_1"
        $desc         = $mi->title_th;            // ข้อความหัวข้อ
        $status_field = 'status_' . $code;        // status_5_1
        $remark_field = 'remark_' . $code;        // remark_5_1

        $status_val = $data->$status_field ?? null;
        $remark_val = $data->$remark_field ?? '';
      ?>
      <tr>
        <!-- รายการตรวจประเมิน -->
        <td><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></td>

        <!-- ผ่าน/ไม่ผ่าน -->
        <td class="text-center">
          <?php
            echo checkStatus($data, $status_field, $check, $cross, $pending);
          ?>
        </td>

        <!-- ข้อบกพร่องที่พบ -->
        <td>
          <?php
          $fail_texts = [];

          if ($status_val === 'fail') {
              // 1) checklist ที่ถูกติ๊ก
              $fails_for_code = $grouped_fail_items[$code] ?? [];

              if (!empty($fails_for_code)) {
                  foreach ($fails_for_code as $fi) {
                      // field name: fail_5_1_1, fail_5_1_2 ...
                      $fail_field = 'fail_' . $code . '_' . $fi->fail_code;
                      $checked    = !empty($data->$fail_field);

                      if ($checked) {
                          $fail_texts[] = htmlspecialchars($fi->label_text, ENT_QUOTES, 'UTF-8');
                      }
                  }
              }

              // 2) หมายเหตุ
              if (!empty($remark_val)) {
                  $fail_texts[] = 'หมายเหตุ: ' . htmlspecialchars($remark_val, ENT_QUOTES, 'UTF-8');
              }
          }

          echo !empty($fail_texts) ? implode('<br>', $fail_texts) : '-';
          ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="3" class="text-center text-muted">— ยังไม่มีรายการตรวจประเมินในหมวดนี้ —</td>
    </tr>
  <?php endif; ?>

  </tbody>
</table>
