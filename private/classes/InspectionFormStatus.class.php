<?php

class InspectionFormStatus extends DatabaseObject {
    protected static $table_name = 'inspection_form_status';
    protected static $db_columns = [
        'id', 'vessel_id', 'inspection_date', 'inspector_id',
        'form_structure_status', 'form_material_status', 'form_crew_status',
        'form_water_ice_status', 'form_preservation_status',
        'document_number', 'department_code',
        'locked_by', 'locked_at',
        'new_id', 'parent_id',
        'is_active', 'created_at', 'updated_at'
    ];

    public $id;
    public $vessel_id;
    public $inspection_date;
    public $inspector_id;

    public $form_structure_status = 0;
    public $form_material_status = 0;
    public $form_crew_status = 0;
    public $form_water_ice_status = 0;
    public $form_preservation_status = 0;

    public $document_number;
    public $department_code;

    public $locked_by;
    public $locked_at;

    public $new_id;
    public $parent_id;
    public $is_active = 1;

    public $created_at;
    public $updated_at;

    // ✅ สร้างเลขเอกสาร (escape string + query manual)
    public static function generate_document_number($department_code) {
        $year = date('Y');
        $escaped_code = self::$database->escape_string($department_code);

        $sql = "SELECT COUNT(*) as count FROM " . static::$table_name;
        $sql .= " WHERE YEAR(created_at) = '{$year}'";
        $sql .= " AND department_code = '{$escaped_code}'";
        $sql .= " AND parent_id IS NULL";  // ✅ นับเฉพาะต้นฉบับเท่านั้น
        $result = self::$database->query($sql);
        $running = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $department_code_two = str_pad($department_code, 2, '0', STR_PAD_LEFT);
        return "efvscis-{$year}-{$department_code_two}-{$running}";
    }

    public static function generate_uuid_v4() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, // เวอร์ชัน 4
            mt_rand(0, 0x3fff) | 0x8000, // variant
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // ✅ ดึงรายการที่ผู้ใช้เป็นคนตรวจ (escape user_id)
    public static function find_by_user_id($user_id) {
        $escaped_id = self::$database->escape_string($user_id);

        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE inspector_id = '{$escaped_id}'";
        $sql .= " ORDER BY inspection_date DESC";

        return static::find_by_sql($sql);
    }

    public static function find_or_create($vessel_id, $inspection_date, $inspector_id, $department_code, $request_id) {
        $vessel_id = self::$database->escape_string($vessel_id);
        $inspection_date = self::$database->escape_string($inspection_date);
        $inspector_id = self::$database->escape_string($inspector_id);
        $department_code = self::$database->escape_string($department_code);

        // 1. ค้นหา record เดิมที่ยัง active ของ user นี้
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE vessel_id = '{$vessel_id}'";
        $sql .= " AND inspection_date = '{$inspection_date}'";
        $sql .= " AND inspector_id = '{$inspector_id}'";
        $sql .= " AND is_active = 1";
        $sql .= " ORDER BY id DESC LIMIT 1";

        $existing = static::find_by_sql($sql);
        if (!empty($existing)) {
            return array_shift($existing);
        }

        // 2. ยังไม่มี → สร้างใหม่
        $new = new self();
        $new->vessel_id = $vessel_id;
        $new->inspection_date = $inspection_date;
        $new->inspector_id = $inspector_id;
        $new->department_code = $department_code;
        $new->document_number = self::generate_document_number($department_code);
        $new->locked_by = $inspector_id;
        $new->locked_at = date('Y-m-d H:i:s');
        $new->create_at = date('Y-m-d H:i:s');
        $new->document_token = self::generate_uuid_v4();
        $new->is_active = 1;
        $new->create();

        // ✅ เพิ่มบันทึก log
        $log = new InspectionLog();
        $log->inspection_request_id = null;
        $log->action_id = 7; // รหัสสำหรับ action เช่น "สร้างแบบฟอร์ม"
        $log->old_value = null;
        $log->new_value = "form_status_id: " . $new->id;
        $log->note = "สร้างฟอร์มการตรวจเรือใหม่อัตโนมัติ";
        $log->performed_by = $session->user_id();
        $log->performed_at = date('Y-m-d H:i:s');
        $log->target_department_id = null;
        $log->target_usertype_id = null;
        $log->target_officer_id =  $session->user_id();
        $log->save();
        return $new;
    }

    // ✅ ฟังก์ชันเพิ่มเองตามแนวของคุณ
    public static function find_active_by_vessel($vessel_id) {
        $escaped = self::$database->escape_string($vessel_id);

        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE vessel_id = '{$escaped}'";
        $sql .= " AND is_active = 1";
        $sql .= " ORDER BY inspection_date DESC";
        $sql .= " LIMIT 1";

        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }
}

?>
