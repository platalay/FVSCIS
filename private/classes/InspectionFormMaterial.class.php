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
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
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
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;




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

    public static function autosave($request_id, $field_name, $value) {
        $allowed_fields = array_flip(static::$db_columns);

        if (!isset($allowed_fields[$field_name])) {
            throw new Exception("Field '$field_name' is not allowed for update.");
        }

        $material = self::find_or_create($request_id);
        $material->$field_name = $value;
        $material->save();

        // ✅ ตรวจสอบความครบถ้วนของทุก status
        $valid = true;
        for ($i = 1; $i <= 6; $i++) {
            $status = $material->{"status_2_{$i}"};
            if (!$status) {
                $valid = false; break;
            }

            if ($status === 'fail') {
                $hasFailReason = false;

                // ตรวจ checkbox
                for ($j = 1; $j <= 4; $j++) {
                    $failField = "fail_2_{$i}_{$j}";
                    if (property_exists($material, $failField) && $material->$failField) {
                        $hasFailReason = true; break;
                    }
                }

                // ตรวจ remark
                $remark = $material->{"remark_2_{$i}"};
                if ($remark) {
                    $hasFailReason = true;
                }

                if (!$hasFailReason) {
                    $valid = false; break;
                }
            }
        }

        if ($valid) {
            $statusRow = InspectionFormStatus::find_by_request_id($request_id);
            $statusRow->form_material_status = 1;
            $statusRow->save();
        }

        return true;
    }


}
