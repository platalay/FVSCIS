<?php

class InspectionFormStructure extends DatabaseObject {
    protected static $table_name = "inspection_form_structure";
    protected static $db_columns = [
        'id', 'request_id',
        'status_1_1', 'fail_1_1_1', 'fail_1_1_2', 'fail_1_1_3', 'remark_1_1',
        'status_1_2', 'remark_1_2',
        'status_1_3', 'fail_1_3_1', 'fail_1_3_2','remark_1_3',
        'status_1_4', 'fail_1_4_1', 'fail_1_4_2', 'remark_1_4',
        'status_1_5', 'fail_1_5_1', 'fail_1_5_2', 'fail_1_5_3', 'remark_1_5',
        'status_1_6', 'fail_1_6_1', 'fail_1_6_2', 'remark_1_6',
        'status_1_7', 'fail_1_7_1', 'fail_1_7_2', 'fail_1_7_3', 'fail_1_7_4', 'remark_1_7',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
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
    public $fail_1_3_1;
    public $fail_1_3_2;
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
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;

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
                $record->save();

                // ✅ ตรวจสอบความครบถ้วน
                $is_complete = true;

                for ($i = 1; $i <= 7; $i++) {
                    $status = $record->{"status_1_{$i}"};
                    if (!$status) {
                        $is_complete = false;
                        break;
                    }

                    if ($status === 'fail') {
                        $has_reason = false;

                        // ✅ ตรวจ checkbox
                        for ($j = 1; $j <= 4; $j++) {
                            $fail_field = "fail_1_{$i}_{$j}";
                            if (property_exists($record, $fail_field) && $record->$fail_field) {
                                $has_reason = true;
                                break;
                            }
                        }

                        // ✅ ตรวจ remark
                        $remark = $record->{"remark_1_{$i}"};
                        if (!empty($remark)) {
                            $has_reason = true;
                        }

                        if (!$has_reason) {
                            $is_complete = false;
                            break;
                        }
                    }
                }

                // 🔧 อัปเดตฟิลด์ใน InspectionFormStatus
                $status_record = InspectionFormStatus::find_by_request_id($request_id);
                if ($status_record) {
                    $status_record->form_structure_status = $is_complete ? 1 : 0;
                    $status_record->save();
                }

                return true;
            }

            return false;
        }


}
