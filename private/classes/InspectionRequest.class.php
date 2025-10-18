<?php

class InspectionRequest extends DatabaseObject {
    const STATUS_PENDING    = 'pending';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_INSPECTING = 'inspecting';
    const STATUS_PASSED     = 'passed';
    const STATUS_FAILED     = 'failed';
    const STATUS_CONDITIONAL = 'conditional';
    const STATUS_COMPLETED  = 'completed'; 
    protected static $table_name = "inspection_requests";
    protected static $db_columns = [
        'id',
        'ship_code',
        'vessel_name',
        'vessel_mark',
        'license_number',
        'gear_type',
        'owner_name',
        'contact_phone',
        'department_id',
        'department_group_id',
        'data_owner_id',
        'port_province_id',
        'port_amphur_id',
        'port_tambon_id',
        'port_license_no',
        'port_name',
        'inspect_date_start',
        'inspect_date_end',
        'confirmed_inspect_date',
        'is_confirm',
        'confirm_agreement',
        'inspection_form_type',
        'cold_room_flag',
        'status',
        'is_manual_case',
        'is_submitted',
        'submitted_at',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip',
        'approved_by', 'approved_at', 'effective_date', 'expire_at', 'approval_note', 'approved_ip', 'actual_inspect_date'
    ];

    public $id;
    public $ship_code;
    public $vessel_name;
    public $vessel_mark;
    public $license_number;
    public $gear_type;
    public $owner_name;
    public $contact_phone;
    public $department_id;
    public $department_group_id;
    public $data_owner_id;
    public $port_province_id;
    public $port_amphur_id;
    public $port_tambon_id;
    public $port_license_no;
    public $port_name;
    public $inspect_date_start;
    public $inspect_date_end;
    public $confirmed_inspect_date;
    public $confirm_agreement = false;
    public $inspection_form_type;
    public $cold_room_flag;

    public $is_confirm = false;
    public $status;
    public $is_manual_case;
    public $is_submitted;
    public $submitted_at;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;
    public $approved_by;
    public $approved_at; 
    public $effective_date;
    public $expire_at;
    public $approval_note;
    public $approved_ip;
    public $actual_inspect_date;


    // Optional: เพิ่ม method แปลงวันที่/แสดงชื่อจังหวัดได้ภายหลัง
    public static function find_active_by_ship($ship_code) {
    $sql = "SELECT * FROM " . static::$table_name;
    $sql .= " WHERE ship_code = '" . self::$database->escape_string($ship_code) . "'";
    $sql .= " AND status NOT IN ('cancelled', 'completed')";
    $sql .= " LIMIT 1";
    $result = static::find_by_sql($sql);
    return !empty($result) ? array_shift($result) : null;
    }

    public static function find_by_created_by($user_id) 
    {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE created_by = '" . self::$database->escape_string($user_id) . "'";
        return static::find_by_sql($sql); // ส่งคืน array ของ object
    }

    public static function find_by_department_id($department_id) 
    {
        $department_id = self::$database->escape_string($department_id);
        $sql = "SELECT * FROM " . static::$table_name . " WHERE department_id = '{$department_id}' ORDER BY created_at DESC";
        return static::find_by_sql($sql);
    }

    public static function find_by_department_group_id($group_id) {
        $group_id = self::$database->escape_string($group_id);
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE department_group_id = '{$group_id}'";
        $sql .= " AND is_submitted = 1";
        $sql .= " ORDER BY created_at DESC";
        return static::find_by_sql($sql);
    }

}
?>
