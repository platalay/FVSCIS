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
        'license_status',
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
    public $license_status;
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

    public static function count_by_fisherman($fisherman_id) {
        $fisherman_id = self::$database->escape_string($fisherman_id);
        $sql  = "SELECT COUNT(*) AS cnt ";
        $sql .= "FROM " . static::$table_name . " ";
        $sql .= "WHERE created_by = '{$fisherman_id}'";
        
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    // 2) นับจำนวนคำขอตามสถานะ (รับได้ทั้งค่าเดียวหรือ array)
    public static function count_by_fisherman_and_status($fisherman_id, $statuses = []) {
        $fisherman_id = self::$database->escape_string($fisherman_id);

        $sql  = "SELECT COUNT(*) AS cnt ";
        $sql .= "FROM " . static::$table_name . " ";
        $sql .= "WHERE created_by = '{$fisherman_id}' ";

        if (!empty($statuses)) {
            if (!is_array($statuses)) {
                $statuses = [$statuses];
            }

            $escaped_statuses = [];
            foreach ($statuses as $st) {
                $escaped_statuses[] = "'" . self::$database->escape_string($st) . "'";
            }
            $status_list = implode(',', $escaped_statuses);
            $sql .= "AND status IN ({$status_list}) ";
        }

        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    // 3) ดึงคำขอล่าสุดของชาวประมง (limit ตามต้องการ, default 5)
    public static function find_recent_by_fisherman($fisherman_id, $limit = 5) {
        $fisherman_id = self::$database->escape_string($fisherman_id);
        $limit = (int)$limit;

        $sql  = "SELECT * ";
        $sql .= "FROM " . static::$table_name . " ";
        $sql .= "WHERE created_by = '{$fisherman_id}' ";
        $sql .= "ORDER BY created_at DESC, id DESC ";
        $sql .= "LIMIT {$limit}";

        return static::find_by_sql($sql);
    }



    // ...

    public static function count_by_department($department_id) {
        $department_id = self::$database->escape_string($department_id);
        $sql  = "SELECT COUNT(*) AS cnt FROM " . static::$table_name;
        $sql .= " WHERE department_id = '{$department_id}'";
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function count_by_department_and_status($department_id, $status) {
        $department_id = self::$database->escape_string($department_id);
        $status        = self::$database->escape_string($status);

        $sql  = "SELECT COUNT(*) AS cnt FROM " . static::$table_name;
        $sql .= " WHERE department_id = '{$department_id}'";
        $sql .= "   AND status = '{$status}'";
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function find_recent_by_department_and_status($department_id, array $statuses, $limit = 10) {
        $department_id = self::$database->escape_string($department_id);
        $limit = (int)$limit;

        $escaped_status = array_map(function($s) {
            return "'" . self::$database->escape_string($s) . "'";
        }, $statuses);
        $status_list = implode(',', $escaped_status);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE department_id = '{$department_id}'";
        $sql .= "   AND status IN ({$status_list})";
        $sql .= " ORDER BY created_at DESC";
        $sql .= " LIMIT {$limit}";
        return static::find_by_sql($sql);
    }

    public static function find_today_tasks_by_officer($department_id, $date) {
        $department_id = self::$database->escape_string($department_id);
        $date       = self::$database->escape_string($date);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE department_id = '{$department_id}'";
        $sql .= "   AND DATE(confirmed_inspect_date) = '{$date}'";
        $sql .= " ORDER BY confirmed_inspect_date ASC";
        return static::find_by_sql($sql);
    }
    public static function find_today_tasks_by_user($created_by, $date) {
        $created_by = self::$database->escape_string($created_by);
        $date       = self::$database->escape_string($date);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE created_by = '{$created_by}'";
        $sql .= "   AND DATE(confirmed_inspect_date) = '{$date}'";
        $sql .= " ORDER BY confirmed_inspect_date ASC";

        return static::find_by_sql($sql);
    }
    public function status_label() {
        $map = [
            'pending'     => 'รอดำเนินการ',
            'inspecting'  => 'อยู่ระหว่างตรวจ',
            'passed'      => 'ผ่าน',
            'failed'      => 'ไม่ผ่าน',
            'conditional' => 'ผ่านแบบมีเงื่อนไข',
            'completed'   => 'ตรวจเสร็จสิ้น',
            'cancelled'   => 'ยกเลิก',
        ];
        return $map[$this->status] ?? $this->status;
    }

    public static function status_text($status) {
        $map = [
            'pending'     => 'รอดำเนินการ',
            'cancelled'   => 'ยกเลิก',
            'inspecting'  => 'อยู่ระหว่างตรวจ',
            'passed'      => 'ผ่านตรวจ',
            'failed'      => 'ไม่ผ่านตรวจ',
            'conditional' => 'ผ่านแบบมีเงื่อนไข',
            'completed'   => 'อนุมัติ'
        ];

        return $map[$status] ?? $status;
    }

        public static function count_by_status($status) {
        $status = self::$database->escape_string($status);
        $sql = "SELECT COUNT(*) AS cnt FROM " . static::$table_name;
        $sql .= " WHERE status = '{$status}'";
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['cnt'];
        }

        public static function find_recent_for_admin($limit = 10) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " ORDER BY created_at DESC";
        $sql .= " LIMIT " . (int)$limit;
        return static::find_by_sql($sql);
        }

        public static function count_by_department_groups($group_ids = [], $status = null) {
    if (empty($group_ids)) { return 0; }

    $ids = array_map('intval', $group_ids);
    $id_list = implode(',', $ids);

    $sql = "SELECT COUNT(*) AS cnt FROM " . static::$table_name .
           " WHERE department_group_id IN ({$id_list})";

    if ($status !== null) {
        $status = self::$database->escape_string($status);
        $sql .= " AND status = '{$status}'";
    }

    $result = self::$database->query($sql);
    $row = $result->fetch_assoc();
    return (int)$row['cnt'];
}

    public static function find_recent_by_department_groups_and_status($group_ids = [], $statuses = [], $limit = 10) {
        if (empty($group_ids) || empty($statuses)) { return []; }

        $ids = array_map('intval', $group_ids);
        $id_list = implode(',', $ids);

        $status_list = array_map(function($s) {
            return "'" . self::$database->escape_string($s) . "'";
        }, $statuses);
        $status_sql = implode(',', $status_list);

        $limit = (int)$limit;

        $sql = "SELECT * FROM " . static::$table_name .
            " WHERE department_group_id IN ({$id_list})" .
            " AND status IN ({$status_sql})" .
            " ORDER BY created_at DESC" .
            " LIMIT {$limit}";

        return static::find_by_sql($sql);
    }


}
?>
