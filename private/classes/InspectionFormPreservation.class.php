<?php

class InspectionFormPreservation extends DatabaseObject {
    protected static $table_name = "inspection_form_preservation";
    protected static $db_columns = [
        'id', 'request_id',
        'status_5_1', 'remark_5_1',
        'status_5_2', 'remark_5_2',
        'status_5_3', 'fail_5_3_1', 'fail_5_3_2', 'remark_5_3',
        'status_5_4', 'fail_5_4_1', 'fail_5_4_2', 'remark_5_4',
        'status_5_5', 'remark_5_5',
        'status_5_6', 'remark_5_6',
        'status_5_7', 'fail_5_7_1', 'fail_5_7_2', 'remark_5_7',
        'status_5_8', 'fail_5_8_1', 'fail_5_8_2', 'remark_5_8',
        'status_5_9', 'fail_5_9_1', 'fail_5_9_2', 'remark_5_9',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
    ];

    public $id;
    public $request_id;

    public $status_5_1;
    public $remark_5_1;

    public $status_5_2;
    public $remark_5_2;

    public $status_5_3;
    public $fail_5_3_1;
    public $fail_5_3_2;
    public $remark_5_3;

    public $status_5_4;
    public $fail_5_4_1;
    public $fail_5_4_2;
    public $remark_5_4;

    public $status_5_5;
    public $remark_5_5;

    public $status_5_6;
    public $remark_5_6;

    public $status_5_7;
    public $fail_5_7_1;
    public $fail_5_7_2;
    public $remark_5_7;

    public $status_5_8;
    public $fail_5_8_1;
    public $fail_5_8_2;
    public $remark_5_8;

    public $status_5_9;
    public $fail_5_9_1;
    public $fail_5_9_2;
    public $remark_5_9;

    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;

    // 🔍 หากมีแล้วให้คืน record นั้น, ถ้าไม่มีให้สร้างใหม่
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

        $preservation = self::find_or_create($request_id);
        $preservation->$field_name = $value;
        $preservation->save();

        // ✅ ตรวจสอบความครบถ้วนของทุกข้อ
        $valid = true;

        // ปรับจำนวนข้อให้ตรงตามคลาสของคุณ (สมมติ 5 ข้อ)
        for ($i = 1; $i <= 5; $i++) {
            $status = $preservation->{"status_5_{$i}"};  // ปรับเป็น prefix ของ preservation
            if (!$status) {
                $valid = false;
                break;
            }

            if ($status === 'fail') {
                $hasFailReason = false;

                // ตรวจ checkbox
                for ($j = 1; $j <= 4; $j++) {
                    $failField = "fail_5_{$i}_{$j}";
                    if (property_exists($preservation, $failField) && $preservation->$failField) {
                        $hasFailReason = true;
                        break;
                    }
                }

                // ตรวจ remark
                $remark = $preservation->{"remark_5_{$i}"};
                if ($remark) {
                    $hasFailReason = true;
                }

                if (!$hasFailReason) {
                    $valid = false;
                    break;
                }
            }
        }

        if ($valid) {
            $statusRow = InspectionFormStatus::find_by_request_id($request_id);
            $statusRow->form_preservation_status = 1;
            $statusRow->save();
        }

        return true;
    }

}
