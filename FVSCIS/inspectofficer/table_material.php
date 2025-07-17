<?php
$data = InspectionFormMaterial::find_by_request_id($request->id);

$check = '✔';
$cross = '✖';
$fail_items = InspectionFailItem::find_by_section(2);
// จัดกลุ่ม checklist ของหมวด 2
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_2_1_1 → [fail, 2, 1, 1]
    $key = $parts[1] . '_' . $parts[2];      // eg. 2_1
    $grouped_fail_items[$key][] = $item;
}

// หัวข้อหมวด 2
$inspection_items = [
    '2_1' => '2.1 วัสดุ อุปกรณ์และเครื่องมือที่ใช้ทำความสะอาดแล้วต้องมีที่เก็บอย่างเหมาะสม...',
    '2_2' => '2.2 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่มีผิวเรียบ ไม่มีรอยแตก...',
    '2_3' => '2.3 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องออกแบบให้เหมาะสมกับการใช้งานและสะดวกในการรักษาความสะอาด',
    '2_4' => '2.4 วัสดุ อุปกรณ์และเครื่องมือทุกชนิดต้องล้างทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด (น้ำประปา)',
    '2_5' => '2.5 ภาชนะที่บรรจุสัตว์น้ำมีสภาพแข็งแรง น้ำหนักเบา และสามารถรับน้ำหนักได้ในกรณีที่ต้องวางซ้อนกัน เพื่อป้องกันไม่ให้ภาชนะกดทับสัตว์น้ำ',
    '2_6' => '2.6 ภาชนะที่บรรจุสัตว์น้ำควรมีรูหรือช่องระบายน้ำได้ดี เช่น ภาชนะพลาสติก',
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
