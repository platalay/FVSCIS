<?php

class InspectionFormMaterial extends DatabaseObject {
    protected static $table_name = "inspection_form_material";
    protected static $db_columns = [
        'id', 'request_id',
        'status_2_1', 'fail_2_1_1', 'fail_2_1_2', 'fail_2_1_3', 'remark_2_1',
        'status_2_2', 'remark_2_2',
        'status_2_3', 'remark_2_3',
        'status_2_4', 'fail_2_4_1', 'fail_2_4_2', 'fail_2_4_3', 'remark_2_4',
        'status_2_5', 'remark_2_5',
        'status_2_6', 'fail_2_6_1', 'fail_2_6_2', 'remark_2_6',
        'created_at', 'updated_at'
    ];

    public $id;
    public $request_id;

    public $status_2_1;
    public $fail_2_1_1;
    public $fail_2_1_2;
    public $fail_2_1_3;
    public $remark_2_1;

    public $status_2_2;
    public $remark_2_2;

    public $status_2_3;
    public $remark_2_3;

    public $status_2_4;
    public $fail_2_4_1;
    public $fail_2_4_2;
    public $fail_2_4_3;
    public $remark_2_4;

    public $status_2_5;
    public $remark_2_5;

    public $status_2_6;
    public $fail_2_6_1;
    public $fail_2_6_2;
    public $remark_2_6;

    public $created_at;
    public $updated_at;

    public static function find_or_create($request_id) {
        $request_id = self::$database->escape_string($request_id);
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '{$request_id}'";
        $sql .= " ORDER BY id DESC LIMIT 1";
        $existing = static::find_by_sql($sql);
        if (!empty($existing)) {
            return array_shift($existing);
        }

        $new_record = new self();
        $new_record->request_id = $request_id;
        $new_record->save();
        return $new_record;
    }

    public static function autosave($request_id, $field, $value) {
        $record = self::find_or_create($request_id);
        if (property_exists($record, $field)) {
            $record->$field = $value;
            $record->save();

            // 🔄 ตรวจสอบความครบถ้วนของ status_1_1 ถึง status_1_7
            $all_status_fields = [
                'status_2_1', 'status_2_2', 'status_2_3', 'status_2_4',
                'status_2_5', 'status_2_6'
            ];

            $is_complete = true;
            foreach ($all_status_fields as $status_field) {
                if (empty($record->$status_field)) {
                    $is_complete = false;
                    break;
                }
            }

            // 🔧 อัปเดตฟิลด์ใน InspectionFormStatus
            $status_record = InspectionFormStatus::find_by_request_id($request_id);
            if ($status_record) {
                $status_record->form_material_status = $is_complete ? 1 : 0;
                $status_record->save();
            }

            return true;
        }
        return false;
    }
}
