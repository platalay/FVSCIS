<?php
$data = InspectionFormPreservation::find_by_request_id($request->id);

$check = '✔';
$cross = '✖';

// ดึง checklist เฉพาะหมวด 5
$fail_items = InspectionFailItem::find_by_section(5);

// จัดกลุ่ม checklist
$grouped_fail_items = [];
foreach ($fail_items as $item) {
    $parts = explode('_', $item->field_name); // fail_5_3_1 → [fail, 5, 3, 1]
    $key = $parts[1] . '_' . $parts[2];
    $grouped_fail_items[$key][] = $item;
}

// รายการข้อสอบถามในหมวด 5
$inspection_items = [
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
              // checklist ที่ถูกติ๊ก
              if (!empty($grouped_fail_items[$code])) {
                  foreach ($grouped_fail_items[$code] as $fail_item) {
                      $fail_field = $fail_item->field_name;
                      if (!empty($data->$fail_field)) {
                          $fail_texts[] = htmlspecialchars($fail_item->label_text);
                      }
                  }
              }

              // หมายเหตุ
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
