<?php
$data = InspectionFormCrew::find_by_request_id($request->id);

$check = '✔';
$cross = '✖';
$pending = '⏳';

// ดึง checklist เฉพาะกลุ่ม 3
$fail_items = InspectionFailItem::find_by_section(3);

// จัดกลุ่ม checklist ของหมวด 3
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_3_1_1 → [fail, 3, 1, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// รายการข้อสอบถามในหมวด 3
$inspection_items = [
    '3_1' => '3.1 บุคลากรที่ปฏิบัติงานในเรือต้องมีสุขภาพดี...',
    '3_2' => '3.2 ผ่านการฝึกอบรมเรื่องสุขอนามัยที่ควรปฏิบัติในเรือประมง',
    '3_3' => '3.3 ล้างมือให้สะอาดทั้งก่อนและหลังการปฏิบัติงานทุกครั้ง รวมทั้งในระหว่างการปฏิบัติงานตามความเหมาะสมและทุกครั้งหลังการใช้สุขา',
    '3_4' => '3.4 เสื้อผ้าที่ใส่ทำงานต้องสะอาด และเหมาะสมกับการปฏิบัติงาน',
    '3_5' => '3.5 ไม่รับประทานอาหารหรือสูบบุหรี่ไม่ไอหรือจามใส่สัตว์น้ำขณะปฏิบัติงาน'
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
          echo checkStatus($data, $status_field, $check, $cross, $pending);
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
