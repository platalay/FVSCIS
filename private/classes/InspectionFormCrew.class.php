<?php

class InspectionFormCrew extends DatabaseObject {
    protected static $table_name = "inspection_form_crew";
    protected static $db_columns = [
        'id',
        'request_id',
        'status_3_1', 'fail_3_1_1', 'fail_3_1_2', 'fail_3_1_3', 'fail_3_1_4', 'remark_3_1',
        'status_3_2', 'fail_3_2_1', 'remark_3_2',
        'status_3_3', 'fail_3_3_1', 'remark_3_3',
        'status_3_4', 'fail_3_4_1', 'remark_3_4',
        'status_3_5', 'fail_3_5_1', 'remark_3_5',
        'created_at', 'updated_at'
    ];

    public $id;
    public $request_id;
    public $status_3_1;
    public $fail_3_1_1 = 0;
    public $fail_3_1_2 = 0;
    public $fail_3_1_3 = 0;
    public $fail_3_1_4 = 0;
    public $remark_3_1;

    public $status_3_2;
    public $fail_3_2_1 = 0;
    public $remark_3_2;

    public $status_3_3;
    public $fail_3_3_1 = 0;
    public $remark_3_3;

    public $status_3_4;
    public $fail_3_4_1 = 0;
    public $remark_3_4;

    public $status_3_5;
    public $fail_3_5_1 = 0;
    public $remark_3_5;

    public $created_at;
    public $updated_at;

    // สร้างหรือหา record เดิมเพื่อใช้สำหรับ auto save
    public static function find_or_create($request_id) {
        $request_id = self::$database->escape_string($request_id);

        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '{$request_id}'";
        $sql .= " ORDER BY id DESC LIMIT 1";

        $existing = static::find_by_sql($sql);
        if (!empty($existing)) {
            return array_shift($existing);
        }

        // ยังไม่มี → สร้างใหม่
        $new = new self();
        $new->request_id = $request_id;
        $new->status_3_1 = 'pass';
        $new->status_3_2 = 'pass';
        $new->status_3_3 = 'pass';
        $new->status_3_4 = 'pass';
        $new->status_3_5 = 'pass';
        $new->save();
        return $new;
    }

    // ฟังก์ชันที่ใช้สำหรับ autosave update field ใด field หนึ่ง
    public static function autosave($request_id, $field_name, $value) {
        $allowed_fields = array_flip(static::$db_columns);

        if (!isset($allowed_fields[$field_name])) {
            throw new Exception("Field '$field_name' is not allowed for update.");
        }

        $crew = self::find_or_create($request_id);
        $crew->$field_name = $value;
        return $crew->save();
    }
}
