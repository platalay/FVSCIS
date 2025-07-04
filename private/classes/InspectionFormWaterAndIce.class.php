<?php

class InspectionFormWaterAndIce extends DatabaseObject {
    protected static $table_name = "inspection_form_water_and_ice";
    protected static $db_columns = [
        'id', 'request_id',
        'status_4_1', 'fail_4_1_1', 'fail_4_1_2', 'fail_4_1_3', 'fail_4_1_4', 'remark_4_1',
        'status_4_2', 'remark_4_2',
        'status_4_3', 'fail_4_3_1', 'fail_4_3_2', 'remark_4_3',
        'status_4_4', 'remark_4_4',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
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

    // ✅ autosave สำหรับบันทึกอัตโนมัติทีละช่อง
     // ✅ autosave รองรับการบันทึกฟิลด์เดี่ยวแบบอัตโนมัติ
        public static function autosave($request_id, $field, $value) {
            $record = self::find_or_create($request_id);
            if (property_exists($record, $field)) {
                $record->$field = $value;
                $record->save();

                // ✅ ตรวจสอบความครบถ้วน
                $is_complete = true;

                for ($i = 1; $i <= 4; $i++) {
                    $status = $record->{"status_4_{$i}"};
                    if (!$status) {
                        $is_complete = false;
                        break;
                    }

                    if ($status === 'fail') {
                        $has_reason = false;

                        // ✅ ตรวจ checkbox
                        for ($j = 1; $j <= 4; $j++) {
                            $fail_field = "fail_4_{$i}_{$j}";
                            if (property_exists($record, $fail_field) && $record->$fail_field) {
                                $has_reason = true;
                                break;
                            }
                        }

                        // ✅ ตรวจ remark
                        $remark = $record->{"remark_4_{$i}"};
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
                    $status_record->form_water_ice_status = $is_complete ? 1 : 0;
                    $status_record->save();
                }
                return true;
            }

            return false;
        }



}
