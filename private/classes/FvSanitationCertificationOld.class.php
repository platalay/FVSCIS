<?php

class FvSanitationCertificationOld extends DatabaseObject
{
    protected static $table_name = "fv_sanitation_certification_old";
    protected static $db_columns = [
        'id', 'vessel_name', 'ship_code', 'fisherman_id', 'vessel_mark', 'license_number', 'license_status',
        'gear_type', 'owner_name', 'certificate_number',
        'request_date', 'signature_date', 'effective_date', 'expiration_date', 'status',
        'vessel_status', 'evaluation_agency', 'signing_unit', 'temporary_reason', 'responsible_unit', 'certificate_status', 'remark', 'type'
    ];

    public $id;
    public $vessel_name;
    public $ship_code;
    public $fisherman_id;
    public $vessel_mark;
    public $license_number;
    public $license_status;
    public $gear_type;
    public $owner_name;
    public $certificate_number;
    public $request_date;
    public $signature_date;
    public $effective_date;
    public $expiration_date;
    public $status;
    public $vessel_status;
    public $evaluation_agency;//Department
    public $signing_unit;//DepartmentGroup
    public $temporary_reason;
    public $responsible_unit;//DepartmentGroup
    public $certificate_status;
    public $remark;
    public $type;

    // ✅ หา record ด้วยทะเบียนเรือ
    public static function find_by_ship_code($ship_code)
    {
        $sc = self::$database->escape_string(trim($ship_code));

        // เอาเรคคอร์ดที่มีวันหมดอายุจริง และเรียงใหม่สุดก่อน
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '{$sc}' ";
        $sql .= "  AND expiration_date IS NOT NULL ";
        $sql .= "  AND expiration_date <> '0000-00-00' ";
        $sql .= "ORDER BY expiration_date DESC, effective_date DESC, signature_date DESC, id DESC ";
        $sql .= "LIMIT 1";

        $result_array = static::find_by_sql($sql);
        if (!empty($result_array)) {
            return array_shift($result_array);
        }

        // fallback: ถ้าไม่มีวันหมดอายุที่ใช้ได้เลย ให้คืนเรคคอร์ดล่าสุดตาม id
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '{$sc}' ";
        $sql .= "ORDER BY id DESC ";
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
    public static function count_active_by_fisherman($fisherman_id) {
        $fisherman_id = self::$database->escape_string($fisherman_id);

        // สมมติว่าถือว่า "ยังใช้ได้" = expire_at >= TODAY และ status = 'active'
        $sql  = "SELECT COUNT(*) AS cnt ";
        $sql .= "FROM " . static::$table_name . " ";
        $sql .= "WHERE fisherman_id = '{$fisherman_id}' ";
        $sql .= "AND expiration_date >= CURDATE() ";
        $sql .= "AND status = 'active'";

        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

     public static function link_to_fisherman_by_citizen(Fisherman $fisherman) {

        if (empty($fisherman->citizen_id)) {
            return;
        }
        error_log("fisherman->citizen_id = " . $fisherman->citizen_id);
        $vessels = Elicense::find_full_by_citizen_id_auto($fisherman->citizen_id);
        if (empty($vessels)) {
            return;
        }

        $ship_codes = [];
        foreach ($vessels as $vessel) {
            if (!empty($vessel->ship_code)) {
                $ship_codes[] = $vessel->ship_code;
                error_log("vessel->ship_code = " . $vessel->ship_code);
            }
        }

        $ship_codes = array_values(array_unique($ship_codes));

        if (empty($ship_codes)) {
            return;
        }

        $escaped = [];
        foreach ($ship_codes as $code) {
            $escaped[] = "'" . self::$database->escape_string($code) . "'";
        }
        $list = implode(',', $escaped);

        $fisherman_id = (int)$fisherman->id;

        $sql  = "UPDATE " . static::$table_name . " ";
        $sql .= "SET fisherman_id = {$fisherman_id} ";
        $sql .= "WHERE ship_code IN ({$list}) ";
        $sql .= "AND (fisherman_id IS NULL OR fisherman_id = 0)";

        self::$database->query($sql);
    }    

    public static function count_active_by_responsible_unit($unit_id = null) {
        global $database;

        $sql = "SELECT COUNT(*) AS cnt FROM " . static::$table_name .
            " WHERE status = 'active'";

        if ($unit_id !== null) {
            $sql .= " AND responsible_unit = '" . $database->escape_string($unit_id) . "'";
        }

        $result = $database->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['cnt'];
    }


}