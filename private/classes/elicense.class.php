<?php

class Elicense {
  public $display_name;
  public $age;
  public $nationality_id;
  public $number;
  public $phone;
  public $street;
  public $moo;
  public $tambon_id;
  public $tambon_name;
  public $amphur_id;
  public $amphur_name;
  public $province_id;
  public $province_name;
  public $zip;

  public function __construct($data) {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }

  /**
   * ค้นหาข้อมูลใบอนุญาตจากเลขบัตรประชาชน
   * @param PDO $pdo - การเชื่อมต่อ PDO
   * @param string $id_number - เลขบัตรประชาชน
   * @return Elicense[]|false
   */
  public static function find_by_id_number(PDO $pdo, string $id_number) {
    $sql = "SELECT
              rpn.display_name,
              rpn.age,
              rpn.nationality_id,
              rpn.number,
              rpn.phone,
              rpn.street,
              rpn.moo,
              rpn.tambon_id,
              tmn.name AS tambon_name,
              ef.amphur_id,
              am.name AS amphur_name,
              ef.province_id,
              pr.name AS province_name,
              rpn.zip
            FROM public.fishing_license fl
            LEFT JOIN public.elicense_office ef ON ef.id = fl.request_office_id
            LEFT JOIN public.res_partner rpn ON rpn.id = fl.owner_id
            LEFT JOIN public.tambon tmn ON tmn.id = rpn.tambon_id
            LEFT JOIN public.amphur am ON am.id = ef.amphur_id
            LEFT JOIN public.province pr ON pr.id = ef.province_id
            LEFT JOIN public.fishing_vessel fv ON fv.id = fl.vessel_id
            WHERE fl.fishery_year = '2567'
              AND fl.state = 'active'
              AND fl.license_type_id = 1
              AND rpn.number = :id_number";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id_number', $id_number);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
      return false;
    }

    $results = [];
    foreach ($rows as $row) {
      $results[] = new Elicense($row);
    }
    return $results;
  }
}

//ตัวอย่างการใช้
/*
require_once 'Elicense.php';

// สมมุติว่าคุณมี $pdo แล้ว
$id_number = '3810100181501';
$records = Elicense::find_by_id_number($pdo, $id_number);

if ($records === false) {
  echo "ไม่พบข้อมูล";
} else {
  foreach ($records as $person) {
    echo "ชื่อ: $person->display_name<br>";
    echo "อายุ: $person->age<br>";
    echo "จังหวัด: $person->province_name<br><hr>";
  }
}
*/