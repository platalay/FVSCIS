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

                // ✅ หลังจาก save แล้ว เรียกฟังก์ชัน check_complete
                self::check_complete($request_id);

                return true;
            }

            return false;
        }

        public static function check_complete($request_id) {
            $record = self::find_or_create($request_id);
            $is_complete = true;

            // 1️⃣ วนหาทุก property ที่เป็น status_1_*
            foreach (get_object_vars($record) as $property => $value) {
                if (preg_match('/^status_1_\d+$/', $property)) {
                    $code = substr($property, 7); // เอาเลขเช่น 1,2,3,4,.. จาก status_1_3

                    $status = $record->$property;

                    if (!$status) {
                        $is_complete = false;
                        break;
                    }

                    if ($status === 'fail') {
                        $has_reason = false;
                        $has_any_checkbox = false;

                        // วนหา fail_1_{code}_{j}
                        foreach (get_object_vars($record) as $fail_prop => $fail_value) {
                            if (preg_match("/^fail_1_{$code}_\d+$/", $fail_prop)) {
                                $has_any_checkbox = true;

                                if (!empty($fail_value)) {
                                    $has_reason = true;
                                    break;
                                }
                            }
                        }

                        // remark_1_{code}
                        $remark_field = "remark_1_{$code}";
                        $remark = $record->$remark_field ?? '';

                        if (!empty($remark)) {
                            $has_reason = true;
                        }

                        if ($has_any_checkbox && !$has_reason) {
                            $is_complete = false;
                            break;
                        }
                    }
                }
            }

            // 2️⃣ อัปเดต InspectionFormStatus
            $status_record = InspectionFormStatus::find_by_request_id($request_id);
            if ($status_record) {
                $status_record->form_structure_status = $is_complete ? 1 : 0;
                $status_record->save();
            }

            return $is_complete;
        }

        public static function find_by_request_id($request_id) {
            $escaped = self::$database->escape_string($request_id);

            $sql = "SELECT * FROM " . static::$table_name;
            $sql .= " WHERE request_id = '{$escaped}'";
            ////error_log($sql);
            $result = static::find_by_sql($sql);
            return !empty($result) ? array_shift($result) : null;
        }


}
