<?php

class InspectionFormStructure extends DatabaseObject {
    protected static $table_name = "inspection_form_structure";
    protected static $db_columns = [
        'id', 'request_id',
        'status_1_1', 'fail_1_1_1', 'fail_1_1_2', 'fail_1_1_3', 'remark_1_1',
        'status_1_2', 'remark_1_2',
        'status_1_3', 'remark_1_3',
        'status_1_4', 'fail_1_4_1', 'fail_1_4_2', 'remark_1_4',
        'status_1_5', 'fail_1_5_1', 'fail_1_5_2', 'fail_1_5_3', 'remark_1_5',
        'status_1_6', 'fail_1_6_1', 'fail_1_6_2', 'remark_1_6',
        'status_1_7', 'fail_1_7_1', 'fail_1_7_2', 'fail_1_7_3', 'fail_1_7_4', 'remark_1_7',
        'created_at', 'updated_at'
    ];

    public $id;
    public $request_id;

    public $status_1_1;
    public $fail_1_1_1;
    public $fail_1_1_2;
    public $fail_1_1_3;
    public $remark_1_1;

    public $status_1_2;
    public $remark_1_2;

    public $status_1_3;
    public $remark_1_3;

    public $status_1_4;
    public $fail_1_4_1;
    public $fail_1_4_2;
    public $remark_1_4;

    public $status_1_5;
    public $fail_1_5_1;
    public $fail_1_5_2;
    public $fail_1_5_3;
    public $remark_1_5;

    public $status_1_6;
    public $fail_1_6_1;
    public $fail_1_6_2;
    public $remark_1_6;

    public $status_1_7;
    public $fail_1_7_1;
    public $fail_1_7_2;
    public $fail_1_7_3;
    public $fail_1_7_4;
    public $remark_1_7;

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

    // ✅ autosave รองรับการบันทึกฟิลด์เดี่ยวแบบอัตโนมัติ
    public static function autosave($request_id, $field, $value) {
        $record = self::find_or_create($request_id);
        if (property_exists($record, $field)) {
            $record->$field = $value;
            return $record->save();
        }
        return false;
    }
}
