<?php
require_once('../private/initialize.php');

// สมมุติว่าคุณมี $pdo แล้ว
$id_number = '3810100315167';
$records = Elicense::find_by_id_number($el_db, $id_number);

if ($records === false) {
  echo "ไม่พบข้อมูล";
} else {
  foreach ($records as $person) {
    echo "ชื่อ: $person->display_name<br>";
    echo "อายุ: $person->age<br>";
    echo "จังหวัด: $person->province_name<br><hr>";
  }
}