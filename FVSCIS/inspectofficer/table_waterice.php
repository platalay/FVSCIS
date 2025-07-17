<?php
$data = InspectionFormWaterAndIce::find_by_request_id($request->id);

$check = '✔';
$cross = '✖';

// ดึง checklist เฉพาะหมวด 4
$fail_items = InspectionFailItem::find_by_section(4);

// จัดกลุ่ม checklist ของหมวด 4
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_4_1_1 → [fail, 4, 1, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// หัวข้อหมวด 4
$inspection_items = [
    '4_1' => '4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน',
    '4_2' => '4.2 สถานที่เก็บ และภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิมและทำความสะอาดได้ง่าย',
    '4_3' => '4.3 มีการถ่ายเท ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ น้ำแข็งลงเรือ',
    '4_4' => '4.4 ภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องมีฝาปิดมิดชิด'
];
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

  <?php foreach ($inspection_items as $code => $desc): ?>
    <tr>
      <td><?= htmlspecialchars($desc) ?></td>

      <td class="text-center">
        <?php
          $status_field = 'status_' . $code;
          echo ($data->$status_field == 'pass') ? $check : $cross;
        ?>
      </td>

      <td>
        <?php
          $fail_texts = [];

          if ($data->$status_field == 'fail') {
              // ดึง checklist ที่ถูกติ๊ก
              if (!empty($grouped_fail_items[$code])) {
                  foreach ($grouped_fail_items[$code] as $fail_item) {
                      $fail_field = $fail_item->field_name;
                      if (!empty($data->$fail_field)) {
                          $fail_texts[] = htmlspecialchars($fail_item->label_text);
                      }
                  }
              }

              // เพิ่มหมายเหตุถ้ามี
              $remark_field = 'remark_' . $code;
              if (!empty($data->$remark_field)) {
                  $fail_texts[] = 'หมายเหตุ: ' . htmlspecialchars($data->$remark_field);
              }
          }

          echo !empty($fail_texts) ? implode('<br>', $fail_texts) : '-';
        ?>
      </td>
    </tr>
  <?php endforeach; ?>

  </tbody>
</table>
