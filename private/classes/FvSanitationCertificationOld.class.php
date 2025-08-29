<?php

class FvSanitationCertificationOld extends DatabaseObject
{
    protected static $table_name = "fv_sanitation_certification_old";
    protected static $db_columns = [
        'id', 'vessel_name', 'ship_code', 'vessel_mark', 'license_number',
        'gear_type', 'owner_name', 'certificate_number',
        'request_date', 'signature_date', 'effective_date', 'expiration_date',
        'vessel_status', 'evaluation_agency', 'signing_unit', 'temporary_reason', 'responsible_unit', 'certificate_status', 'remark'
    ];

    public $id;
    public $vessel_name;
    public $ship_code;
    public $vessel_mark;
    public $license_number;
    public $gear_type;
    public $owner_name;
    public $certificate_number;
    public $request_date;
    public $signature_date;
    public $effective_date;
    public $expiration_date;
    public $vessel_status;
    public $evaluation_agency;//Department
    public $signing_unit;//DepartmentGroup
    public $temporary_reason;
    public $responsible_unit;//DepartmentGroup
    public $certificate_status;
    public $remark;

    // ✅ หา record ด้วยทะเบียนเรือ
    public static function find_by_ship_code($ship_code)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '" . self::$database->escape_string($ship_code) . "' ";
        $sql .= "LIMIT 1";
        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : null;
    }

    public function __construct($args = [])
    {
        foreach (static::$db_columns as $col) {
            if ($col === 'id') continue;                       // ไม่ให้แก้ id
            if (!property_exists($this, $col)) continue;       // กัน key แปลกปลอม
            $val = $args[$col] ?? ($this->$col ?? null);
            // แปลงค่าว่างให้เป็น null กัน '0000-00-00'
            if (is_string($val)) $val = trim($val);
            $this->$col = ($val === '') ? null : $val;
        }
    }

    
    public static function find_one_by_ship_code($ship_code)
    {
        return static::find_by_ship_code($ship_code);
    }

    public static function find_all_by_signing_unit($signing_unit)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE signing_unit = '" . self::$database->escape_string($signing_unit) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }

    public static function find_all_by_evaluation_agency($evaluation_agency)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE evaluation_agency = '" . self::$database->escape_string($evaluation_agency) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }

    public static function find_all_by_responsible_unit($responsible_unit)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE responsible_unit = '" . self::$database->escape_string($responsible_unit) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }
}