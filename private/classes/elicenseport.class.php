<?php

class ElicensePort {
  public $license_no;
  public $port_name;
  public $port_province_id;
  public $port_amphur_id;
  public $port_tambon_id;

  public function __construct($data) {
    foreach ($data as $key => $value) {
      if (property_exists($this, $key)) {
        $this->$key = $value;
      }
    }
  }

  /**
   * ค้นหาท่าเรือที่เกี่ยวข้องกับใบอนุญาต
   * @param PDO $pdo - การเชื่อมต่อฐานข้อมูล
   * @param string $license_no - เลขที่ใบอนุญาต
   * @return ElicensePort[]|false
   */
  public static function find_by_license_no(PDO $pdo, string $license_no) {
    $sql = "SELECT license_no, port_name, port_province_id, port_amphur_id, port_tambon_id
            FROM public.elicense_license_port
            WHERE state = 'active' AND license_no = :license_no";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':license_no', $license_no);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) return false;

    return array_map(fn($row) => new ElicensePort($row), $rows);
  }

    public static function find_one_by_license_no(PDO $pdo, string $license_no): ?ElicensePort {
      $sql = "
          SELECT license_no, port_name, port_province_id, port_amphur_id, port_tambon_id
          FROM public.elicense_license_port
          WHERE state = 'active' AND license_no = :license_no
          LIMIT 1
      ";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':license_no' => $license_no]);

      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$row) return null;

      return new ElicensePort($row); // ตรวจว่าคอนสตรัคเตอร์รับ array ได้
  }
  
  public static function find_by_tambon(PDO $pdo, int $tambon_id) {
    $sql = "SELECT license_no, port_name, port_province_id, port_amphur_id, port_tambon_id
            FROM public.elicense_license_port
            WHERE state = 'active'
                AND port_tambon_id = :tambon_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tambon_id', $tambon_id, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) return [];

    return array_map(fn($row) => new ElicensePort($row), $rows);
    }
}
