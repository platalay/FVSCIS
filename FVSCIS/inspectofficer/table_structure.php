<?php
$data = InspectionFormStructure::find_by_request_id($request->id);

$check = '✔';
$cross = '✖';
$pending = '⏳';

$fail_list = [
    '1_1' => ['fail_1_1_1', 'fail_1_1_2'], // สมมติ checklist ของข้อ 1.1
    '1_2' => ['fail_1_2_1'],
    '1_3' => ['fail_1_3_1'],
    '1_4' => ['fail_1_4_1'],
    '1_5' => ['fail_1_5_1'],
    '1_6' => ['fail_1_6_1'],
    '1_7' => ['fail_1_7_1'],
];

// รายการข้อความ
$inspection_items = [
    '1_1' => '๑.๑ ห้องเก็บรักษาสัตว์น้ำต้องอยู่ในสภาพที่สะอาด มีขนาดเหมาะสมเพียงพอ ในกรณีมีขอรับหนังสือรับรอง (สธ.๓๓ ฉบับชั่วคราว) ต้องมีเครื่องเย็นหรือมีน้ำแข็งในเรือประมง',
    '1_2' => '๑.๒ มีโครงสร้างอย่างเหมาะสมโดยมีส่วนหนึ่งเป็นช่องเก็บมุมเอียงต่ำสุด เพื่อให้ง่ายต่อการทำความสะอาด',
    '1_3' => '๑.๓ พื้นปฏิบัติงานและพื้นที่เก็บรักษาสัตว์น้ำออกแบบอย่างเหมาะสม ถูกสุขลักษณะต่อการเก็บรักษาในเรือประมง โดยต้องแยกจากส่วนที่อยู่อาศัยของลูกเรือ และที่พักอาศัยของลูกเรืออย่างชัดเจน',
    '1_4' => '๑.๔ พื้นที่เพียงพอและเหมาะสมสำหรับการรับวัตถุดิบ: การเตรียม; การขนถ่ายสัตว์น้ำ; มีทางระบายน้ำที่ง่ายต่อการล้างทำความสะอาด หรือระบายน้ำทิ้งได้สะดวก และพื้นผิวที่ง่ายต่อการล้างทำความสะอาด',
    '1_5' => '๑.๕ มีราวจับหรือราวพยุงสำหรับลูกเรือ และห้องเก็บรักษาสัตว์น้ำทำจากวัสดุที่สะอาด สามารถทำความสะอาดได้ง่าย สะดวกสบายต่อการใช้งาน',
    '1_6' => '๑.๖ มีพื้นที่เพียงพอสำหรับแยกของเหม็นและไม่เป็นพิษ ทุกส่วนที่สัมผัสกับสัตว์น้ำต้องทำจากวัสดุที่ไม่เป็นพิษต่อสัตว์น้ำ มีพื้นผิวเรียบ ไม่มีสีลอก ไม่มีเศษวัสดุ',
    '1_7' => '๑.๗ มีการแยกของเสียจากสัตว์น้ำ เศษอาหาร และเศษสัตว์น้ำออกจากพื้นที่จัดเก็บสัตว์น้ำ แยกออกจากบริเวณพื้นที่ปฏิบัติงาน',
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

          // ถ้าไม่ผ่าน เช็ค fail_list
          if ($data->$status_field == 'fail') {
              if (!empty($fail_list[$code])) {
                  foreach ($fail_list[$code] as $fail_field) {
                      if (!empty($data->$fail_field)) {
                          $fail_texts[] = htmlspecialchars($data->$fail_field);
                      }
                  }
              }
              // เพิ่ม remark ด้วย
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
