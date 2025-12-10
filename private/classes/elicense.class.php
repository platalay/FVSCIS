<?php

class Elicense extends DatabaseObjectEl{
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

  // เพิ่มคุณสมบัติใหม่จาก SQL ที่ปรับปรุงแล้ว
  public $ship_code;
  public $license_no;
  public $vessel_name;
  public $vessel_ton_gross;
  public $vessel_engine_power;
  public $vessel_width;
  public $vessel_length;
  public $vessel_depth;
  public $state;
  public $date_effective;
  public $date_expire;
  public $fishing_period_amount;
  public $fishing_period;
  public $vessel_size;
  public $fishing_mark;
  public $vms_status;
  public $vms_serial_no;
  public $max_catching;
  public $vessel_type;
  public $fishing_area;
  public $geargroup;
  public $geartype;

  public $tool_group = [];

  // เพิ่มคุณสมบัติใหม่สำหรับการตรวจสอบเลขบัตร
  public $id_type; // 'citizen' | 'juristic' | 'invalid'

  public function __construct($data) {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }

    // ตรวจสอบประเภทของหมายเลข 13 หลัก
    if (!empty($this->number)) {
      $this->id_type = self::check_id_type($this->number);
    }
  }

  /**
   * ตรวจสอบว่าเป็นเลขบัตรประชาชนหรือนิติบุคคล พร้อมตรวจสอบความถูกต้อง
   * @param string $id_number
   * @return string 'citizen' | 'juristic' | 'invalid'
   */
  public static function check_id_type(string $id_number): string {
    if (!preg_match('/^\d{13}$/', $id_number)) {
      return 'invalid';
    }

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
      $sum += (int)$id_number[$i] * (13 - $i);
    }
    $check_digit = (11 - ($sum % 11)) % 10;

    if ((int)$id_number[12] !== $check_digit) {
      return 'invalid';
    }

    return $id_number[0] === '0' ? 'juristic' : 'citizen';
  }

  /**
   * ค้นหาข้อมูลเจ้าของใบอนุญาตจากเลขบัตรประชาชน (ข้อมูลทั่วไป)
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

    if (!$rows) return false;

    return array_map(fn($row) => new Elicense($row), $rows);
  }

  /**
   * ค้นหาข้อมูลใบอนุญาตทั้งหมด (ข้อมูลรายละเอียดเต็ม)
   */
  public static function find_full_by_citizen_id(PDO $pdo, string $citizen_id) {
    $sql = "SELECT
              am.name AS amphur_request,
              pr.name AS province_request,
              rpn.display_name,
              rpn.number,
              rpn.phone,
              rpn.street,
              rpn.moo,
              tmn.name AS tambon_name,
              amn.name AS amphur_name,
              prn.name AS province_name,
              rpn.zip,
              fv.ship_code,
              fl.license_no,
              fl.vessel_name,
              fl.vessel_ton_gross,
              fl.vessel_engine_power,
              fl.vessel_width,
              fl.vessel_length,
              fl.vessel_depth,
              fl.state,
              fl.date_effective,
              fl.date_expire,
              fl.fishing_period_amount,
              fl.fishing_period,
              ftg1.name as geargroup,
              ft1.name as geartype,
              CASE
                WHEN fl.vessel_ton_gross > 0 AND fl.vessel_ton_gross < 10 THEN 'SS'
                WHEN fl.vessel_ton_gross >= 10 AND fl.vessel_ton_gross < 30 THEN 'S'
                WHEN fl.vessel_ton_gross >= 30 AND fl.vessel_ton_gross < 60 THEN 'M'
                WHEN fl.vessel_ton_gross >= 60 AND fl.vessel_ton_gross < 150 THEN 'L'
                WHEN fl.vessel_ton_gross >= 150 THEN 'X'
              END AS vessel_size,
              fm.name AS fishing_mark,
              CASE WHEN fvms.status::integer = 1 THEN 'เปิด' ELSE 'ปิด' END AS vms_status,
              fvms.serial_no AS vms_serial_no,
              fl.max_catching,
              fl.vessel_type,
              fl.fishing_area
            FROM public.fishing_license fl
            LEFT JOIN public.elicense_office ef ON ef.id = fl.request_office_id
            LEFT JOIN public.amphur am ON am.id = ef.amphur_id
            LEFT JOIN public.province pr ON pr.id = ef.province_id
            LEFT JOIN public.res_partner rpn ON rpn.id = fl.owner_id
            LEFT JOIN public.tambon tmn ON tmn.id = rpn.tambon_id
            LEFT JOIN public.amphur amn ON amn.id = rpn.amphur_id
            LEFT JOIN public.province prn ON prn.id = rpn.province_id
            LEFT JOIN public.fishing_vessel fv ON fv.id = fl.vessel_id
            LEFT JOIN public.fishing_marking fm ON fm.id = fl.fishing_marking_id
            LEFT JOIN public.fishing_vms fvms ON fvms.id = fv.vms_id
            LEFT JOIN public.fishing_tool_line ftl1 on ftl1.id = (select ftl.id from fishing_tool_line ftl where ftl.fishing_license_id = fl.id order by ftl.id asc offset 0 limit 1)
            LEFT JOIN public.fishing_tool_group ftg1 on ftg1.id = ftl1.fishing_tool_category_id
            LEFT JOIN public.fishing_tool ft1 on ft1.id = ftl1.fishing_tool_id
            WHERE fl.fishery_year = '2567'
              AND fl.state = 'active'
              AND fl.license_type_id = 1
              AND rpn.number = :citizen_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':citizen_id', $citizen_id);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) return false;

    return array_map(fn($row) => new Elicense($row), $rows);
  }

    public static function find_full_by_citizen_id_auto(string $citizen_id) {
        // ดึงตัวแปร global ที่เราสร้างใน initialize.php
        global $el_db;

        // กันไว้ เผื่อกรณี $el_db ยังไม่ถูกสร้าง (หรือหลุด context)
        if (!$el_db instanceof PDO) {
            $el_db = db_el_connect();  // ฟังก์ชันเดิมที่เต้ยใช้เชื่อม elicense DB
        }

        return static::find_full_by_citizen_id($el_db, $citizen_id);
    }


    public static function find_one_by_ship_code(PDO $pdo, string $ship_code, string $fishery_year = '2567') {
      $sql = "SELECT
                am.name AS amphur_request,
                pr.name AS province_request,
                rpn.age,
                rpn.nationality_id,
                rpn.display_name,
                rpn.number,
                rpn.phone,
                rpn.street,
                rpn.moo,
                tmn.name AS tambon_name,
                amn.name AS amphur_name,
                prn.name AS province_name,
                rpn.zip,
                fv.ship_code,
                fl.license_no,
                fl.vessel_name,
                fl.vessel_ton_gross,
                fl.vessel_engine_power,
                fl.vessel_width,
                fl.vessel_length,
                fl.vessel_depth,
                fl.state,
                fl.date_effective,
                fl.date_expire,
                fl.fishing_period_amount,
                fl.fishing_period,
                ftg1.name as geargroup,
                ft1.name as geartype,
                CASE
                  WHEN fl.vessel_ton_gross > 0 AND fl.vessel_ton_gross < 10 THEN 'SS'
                  WHEN fl.vessel_ton_gross >= 10 AND fl.vessel_ton_gross < 30 THEN 'S'
                  WHEN fl.vessel_ton_gross >= 30 AND fl.vessel_ton_gross < 60 THEN 'M'
                  WHEN fl.vessel_ton_gross >= 60 AND fl.vessel_ton_gross < 150 THEN 'L'
                  WHEN fl.vessel_ton_gross >= 150 THEN 'X'
                END AS vessel_size,
                fm.name AS fishing_mark,
                CASE WHEN fvms.status::integer = 1 THEN 'เปิด' ELSE 'ปิด' END AS vms_status,
                fvms.serial_no AS vms_serial_no,
                fl.max_catching,
                fl.vessel_type,
                fl.fishing_area
              FROM public.fishing_license fl
              LEFT JOIN public.elicense_office ef ON ef.id = fl.request_office_id
              LEFT JOIN public.amphur am ON am.id = ef.amphur_id
              LEFT JOIN public.province pr ON pr.id = ef.province_id
              LEFT JOIN public.res_partner rpn ON rpn.id = fl.owner_id
              LEFT JOIN public.tambon tmn ON tmn.id = rpn.tambon_id
              LEFT JOIN public.amphur amn ON amn.id = rpn.amphur_id
              LEFT JOIN public.province prn ON prn.id = rpn.province_id
              LEFT JOIN public.fishing_vessel fv ON fv.id = fl.vessel_id
              LEFT JOIN public.fishing_marking fm ON fm.id = fl.fishing_marking_id
              LEFT JOIN public.fishing_vms fvms ON fvms.id = fv.vms_id
              LEFT JOIN public.fishing_tool_line ftl1 on ftl1.id = (select ftl.id from fishing_tool_line ftl where ftl.fishing_license_id = fl.id order by ftl.id asc offset 0 limit 1)
              LEFT JOIN public.fishing_tool_group ftg1 on ftg1.id = ftl1.fishing_tool_category_id
              LEFT JOIN public.fishing_tool ft1 on ft1.id = ftl1.fishing_tool_id
              WHERE fl.fishery_year = :fishery_year
                AND fl.state = 'active'
                AND fl.license_type_id = 1
                AND fv.ship_code = :ship_code
              ORDER BY fl.date_effective DESC NULLS LAST, fl.id DESC
              LIMIT 1";

      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':fishery_year', $fishery_year);
      $stmt->bindValue(':ship_code', $ship_code);
      $stmt->execute();

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) return false;

      return new Elicense($row);
  }

      public static function find_by_ship_code(PDO $pdo, string $ship_code) {
      $sql = "SELECT
                fv.ship_code,
                fl.license_no,
                fl.vessel_name,
                fl.vessel_ton_gross,
                fl.fishing_area
              FROM public.fishing_license fl
              LEFT JOIN public.fishing_vessel fv ON fv.id = fl.vessel_id
              WHERE fl.fishery_year = '2567'
                AND fl.state = 'active'
                AND fl.license_type_id = 1
                AND fv.ship_code = :ship_code
              LIMIT 1";

      $stmt = $pdo->prepare($sql);
      $stmt->bindValue(':ship_code', $ship_code);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      return $row ? new Elicense($row) : false;
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