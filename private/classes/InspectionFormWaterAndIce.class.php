<?php

class InspectionFormWaterAndIce extends DatabaseObject {
    protected static $table_name = "inspection_form_water_and_ice";
    protected static $db_columns = [
        'id', 'request_id',
        'status_4_1', 'fail_4_1_1', 'fail_4_1_2', 'fail_4_1_3', 'fail_4_1_4', 'remark_4_1',
        'status_4_2', 'remark_4_2',
        'status_4_3', 'fail_4_3_1', 'fail_4_3_2', 'remark_4_3',
        'status_4_4', 'remark_4_4',
        'created_at', 'updated_at'
    ];

    public $id;
    public $request_id;

    public $status_4_1;
    public $fail_4_1_1;
    public $fail_4_1_2;
    public $fail_4_1_3;
    public $fail_4_1_4;
    public $remark_4_1;

    public $status_4_2;
    public $remark_4_2;

    public $status_4_3;
    public $fail_4_3_1;
    public $fail_4_3_2;
    public $remark_4_3;

    public $status_4_4;
    public $remark_4_4;

    public $created_at;
    public $updated_at;

    // 🔍 ดึงหรือสร้างใหม่หากไม่มี
    public static function find_or_create($request_id) {
        $request_id = self::$database->escape_string($request_id);
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '{$request_id}'";
        $sql .= " ORDER BY id DESC LIMIT 1";
        $existing = static::find_by_sql($sql);
        if (!empty($existing)) {
            return array_shift($existing);
        }

        $new = new self();
        $new->request_id = $request_id;
        $new->save();
        return $new;
    }

    // ✅ autosave สำหรับบันทึกอัตโนมัติทีละช่อง
    public static function autosave($request_id, $field, $value) {
        $record = self::find_or_create($request_id);
        if (property_exists($record, $field)) {
            $record->$field = $value;
            return $record->save();
        }
        return false;
    }
}
