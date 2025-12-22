<?php

class DatabaseObject {

  static protected $database;
  static protected $table_name = "";
  static protected $columns = [];
  public $errors = [];

  static public function set_database($database) {
    self::$database = $database;
  }

  public function __get($name) {
      if ($name === 'affected_rows') {
          return $this->connection->affected_rows;
      }
      return null;
  }
  
  static public function find_by_sql($sql) {
    $result = self::$database->query($sql);
    if(!$result) {
      exit("Database query failed.");
    }

    // results into objects
    $object_array = [];
    while($record = $result->fetch_assoc()) {
      $object_array[] = static::instantiate($record);
    }

    $result->free();

    return $object_array;
  }

  static public function find_all() {
    $sql = "SELECT * FROM " . static::$table_name;
    return static::find_by_sql($sql);
  }

  static public function count_all() {
    $sql = "SELECT COUNT(*) FROM " . static::$table_name;
    $result_set = self::$database->query($sql);
    $row = $result_set->fetch_array();
    return array_shift($row);
  }

  static public function count_one_select($col,$val) {
    $sql = "SELECT COUNT(*) FROM " . static::$table_name . " WHERE " . $col ." = '". self::$database->escape_string($val) ."' ";
    $result_set = self::$database->query($sql);
    $row = $result_set->fetch_array();
    return array_shift($row);
  }

  static public function count_two_select($colone,$valone,$coltwo,$valtwo) {
    $sql = "SELECT COUNT(*) FROM " . static::$table_name . " WHERE " . $colone ." = '". self::$database->escape_string($valone) ."' ";
    $sql .= "AND " . $coltwo ." = '". self::$database->escape_string($valtwo) ."' ";
    $result_set = self::$database->query($sql);
    $row = $result_set->fetch_array();
    return array_shift($row);
  }

  static public function count_three_select($colone,$valone,$coltwo,$valtwo,$colthree,$valthree) {
    $sql = "SELECT COUNT(*) FROM " . static::$table_name . " WHERE ";
    $sql .= $colone ." = '". self::$database->escape_string($valone) ."' ";
    $sql .= "AND " . $coltwo ." = '". self::$database->escape_string($valtwo) ."' ";
    $sql .= "AND ". $colthree ." = '". self::$database->escape_string($valthree)."' ";
    $result_set = self::$database->query($sql);
    $row = $result_set->fetch_array();
    //return $sql;
    return array_shift($row);
  }

  static public function find_by_id($id) {
    $sql = "SELECT * FROM " . static::$table_name . " ";
    $sql .= isset($id) ? "WHERE id='" . self::$database->escape_string($id) . "'" : "WHERE 1=0"; // ป้องกัน SQL ผิด
    $obj_array = static::find_by_sql($sql);
    if(!empty($obj_array)) {
      return array_shift($obj_array);
    } else {
      return false;
    }
  }

  static protected function instantiate($record) {
    $object = new static;
    // Could manually assign values to properties
    // but automatically assignment is easier and re-usable
    foreach($record as $property => $value) {
      if(property_exists($object, $property)) {
        $object->$property = $value;
      }
    }
    return $object;
  }

  protected function validate() {
    $this->errors = [];

    // Add custom validations

    return $this->errors;
  }

  

    public function save() {
      $now = date('Y-m-d H:i:s');
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
      $user_id = $GLOBALS['session']->user_id() ?? 0;

      if (isset($this->id) && $this->id > 0) {
          if (property_exists($this, 'updated_at'))  $this->updated_at = $now;
          if (property_exists($this, 'updated_by'))  $this->updated_by = $user_id;
          if (property_exists($this, 'updated_ip'))  $this->updated_ip = $ip;

          return $this->update();
      } else {
          if (property_exists($this, 'created_at'))  $this->created_at = $now;
          if (property_exists($this, 'created_by'))  $this->created_by = $user_id;
          if (property_exists($this, 'created_ip'))  $this->created_ip = $ip;

          if (property_exists($this, 'updated_at'))  $this->updated_at = $now;
          if (property_exists($this, 'updated_by'))  $this->updated_by = $user_id;
          if (property_exists($this, 'updated_ip'))  $this->updated_ip = $ip;

          return $this->create();
      }
  }




  public function merge_attributes($args=[]) {
    foreach($args as $key => $value) {
      if(property_exists($this, $key) && !is_null($value)) {
        $this->$key = $value;
      }
    }
  }

  // Properties which have database columns, excluding ID
  public function attributes() {
      $attributes = [];
      foreach (static::$db_columns as $column) {
          if ($column === 'id') continue;

          // ถ้าเป็นการ update → ข้าม created_* fields
          if (isset($this->id) && in_array($column, ['created_at', 'created_by', 'created_ip'], true)) {
              continue;
          }

          // คง null เป็น null
          $attributes[$column] = $this->$column ?? null;
      }
      return $attributes;
  }

  protected function sanitized_attributes() {
      $sanitized = [];
      foreach ($this->attributes() as $key => $value) {
          // อย่าแปลง null เป็น '' และ escape เฉพาะค่าที่ไม่ใช่ null
          $sanitized[$key] = ($value === null) ? null : self::$database->escape_string($value);
      }
      return $sanitized;
  }

  /// sql_literal() เวอร์ชันที่รองรับทั้งวันที่, boolean 0/1 และ FK ที่ว่างให้เป็น NULL
// ✅ เวอร์ชันแก้ไข: กัน 0 หายสำหรับ citizen_id/รหัส/เบอร์โทร ฯลฯ
protected static function sql_literal(string $key, $val): string {
    // วันที่/เวลา: ว่าง => NULL
    static $nullable_dates = [
        'confirmed_inspect_date','expire_at','approved_at','actual_inspect_date','submitted_at','effective_date', 'token_expiry', 'form1_locked_at',
        'written_date'
    ];
    // FK/เลขที่อาจว่างได้: ว่าง => NULL
    static $nullable_int = [
        'approved_by','department_group_id','data_owner_id',
        'target_officer_id','action_taken','type','applicant_age',
        'applicant_province_id','applicant_amphoe_id','applicant_tambon_id',
        'juristic_province_id','juristic_amphoe_id','juristic_tambon_id','form1_locked_by'
        // ถ้ามี id อื่นที่ว่างได้ ก็เติมชื่อคอลัมน์มาในลิสต์นี้ได้เลย
    ];
    // boolean 0/1: ว่าง => 0
    static $zero_default_int = ['is_confirm','is_submitted','is_read','form1_locked'];

    // 👇 รายชื่อฟิลด์ที่ต้อง "เก็บเป็นสตริงเสมอ" (ถึงจะเป็นตัวเลขก็ต้องใส่ quote)
    static $force_string_fields = [
        'citizen_id','ship_code','license_no','license_number','port_license_no',
        'contact_phone','phone','mobile','vessel_mark','vms_serial_no','document_number','zip'
    ];

    // === boolean 0/1 ===
    if (in_array($key, $zero_default_int, true)) {
        if ($val === null || $val === '') return "0";
        if (is_string($val) && in_array(strtolower($val), ['1','true','on','yes'], true)) return "1";
        if (is_numeric($val)) return (string)(int)$val;
        return "0";
    }

    // === FK/เลขที่ว่างได้ -> NULL ===
    if (in_array($key, $nullable_int, true)) {
        if ($val === null || $val === '') return "NULL";
        if (is_numeric($val)) return (string)(int)$val;
        return "NULL";
    }

    // === วันที่/เวลา ===
    if (in_array($key, $nullable_dates, true)) {
        return ($val === null || $val === '') ? "NULL" : "'" . $val . "'";
    }

    // === กฎพิเศษสำหรับฟิลด์สตริงรูปแบบรหัส/หมายเลข ===
    // 1) ถ้าคีย์อยู่ใน white-list → ใส่ quote เสมอ
    if (in_array($key, $force_string_fields, true)) {
        return "'" . ($val ?? '') . "'";
    }
    // 2) ถ้าเป็นสตริงและ "ขึ้นต้นด้วย 0 และเป็นตัวเลขล้วน" → ใส่ quote (กัน 0 หาย)
    if (is_string($val) && preg_match('/^0\d+$/', $val)) {
        return "'" . $val . "'";
    }
    // 3) ถ้าชื่อคีย์บ่งชี้ว่าเป็นรหัส/หมายเลข (code/no/phone/idcard) → ใส่ quote
    if (preg_match('/(code|_no|phone|idcard|citizen|zip)$/i', $key)) {
        return "'" . ($val ?? '') . "'";
    }

    // === ตัวเลขทั่วไป ===
    if ($val !== null && (is_int($val) || is_float($val)
        || (is_string($val) && preg_match('/^-?\d+(\.\d+)?$/', $val)))) {
        return (string)$val; // ไม่ใส่ quote เฉพาะกรณีที่ "ไม่ใช่" สตริงที่ต้องคง 0 นำหน้า
    }

    // === ค่าอื่น ๆ (สตริง) ===
    return "'" . ($val ?? '') . "'";
}




  protected function create() {
      $this->validate();
      if (!empty($this->errors)) { return false; }

      $attributes = $this->sanitized_attributes();
      $cols   = array_keys($attributes);
      $values = [];
      foreach ($attributes as $k => $v) {
          $values[] = static::sql_literal($k, $v);
      }

      $sql  = "INSERT INTO " . static::$table_name . " (";
      $sql .= join(', ', $cols) . ") VALUES (" . join(', ', $values) . ")";
      //error_log($sql);
      $result = self::$database->query($sql);
      if ($result) { $this->id = self::$database->insert_id; }
      return $result;
  }

  protected function update() {
      $this->validate();
      if (!empty($this->errors)) { return false; }

      $attributes = $this->sanitized_attributes();

      $pairs = [];
      foreach ($attributes as $k => $v) {
          $pairs[] = "{$k}=" . static::sql_literal($k, $v);
      }

      $id  = self::$database->escape_string($this->id);
      $sql = "UPDATE " . static::$table_name . " SET " . join(', ', $pairs) .
            " WHERE id='{$id}' LIMIT 1";

      return self::$database->query($sql);
  }


  public function delete() {
    $sql = "DELETE FROM " . static::$table_name . " ";
    $sql .= "WHERE id='" . self::$database->escape_string($this->id) . "' ";
    $sql .= "LIMIT 1";
    $result = self::$database->query($sql);
    return $result;

    // After deleting, the instance of the object will still
    // exist, even though the database record does not.
    // This can be useful, as in:
    //   echo $user->first_name . " was deleted.";
    // but, for example, we can't call $user->update() after
    // calling $user->delete().
  }

}

?>
