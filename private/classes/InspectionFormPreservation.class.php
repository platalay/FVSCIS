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

    public $status_5_1; public $remark_5_1;
    public $status_5_2; public $remark_5_2;

    public $status_5_3; public $fail_5_3_1; public $fail_5_3_2; public $remark_5_3;
    public $status_5_4; public $fail_5_4_1; public $fail_5_4_2; public $remark_5_4;

    public $status_5_5; public $remark_5_5;
    public $status_5_6; public $remark_5_6;

    public $status_5_7; public $fail_5_7_1; public $fail_5_7_2; public $remark_5_7;
    public $status_5_8; public $fail_5_8_1; public $fail_5_8_2; public $remark_5_8;
    public $status_5_9; public $fail_5_9_1; public $fail_5_9_2; public $remark_5_9;

    public $created_at; public $updated_at; public $created_by; public $updated_by; public $created_ip; public $updated_ip;

    // หากมีแล้วให้คืน record นั้น, ถ้าไม่มีให้สร้างใหม่
    public static function find_or_create($request_id) {
        $raw_id   = $request_id; // เก็บค่าเดิมไว้ใช้บันทึก
        $escaped  = self::$database->escape_string($raw_id);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '{$escaped}'";
        $sql .= " ORDER BY id DESC LIMIT 1";
        $existing = static::find_by_sql($sql);

        if (!empty($existing)) {
            return array_shift($existing);
        }

        $new_record = new self();
        $new_record->request_id = $raw_id; // บันทึกค่าดิบ ปล่อยให้ save() จัดการ escape เอง
        $new_record->save();
        return $new_record;
    }

    // autosave ฟิลด์เดี่ยวแบบอัตโนมัติ
    public static function autosave($request_id, $field, $value) {
        $record = self::find_or_create($request_id);

        if (property_exists($record, $field)) {
            $record->$field = $value;
            $record->save();

            // อัปเดตสถานะความครบถ้วน
            self::check_complete($request_id);

            return true;
        }
        return false;
    }

        public static function check_complete($request_id) {
            $record = self::find_or_create($request_id);
            $is_complete = true;

            // 1) อ่าน cold_room_flag จากคำขอ
            $req  = InspectionRequest::find_by_id($request_id);
            $cold = ((int)($req->cold_room_flag ?? 0) === 1);

            // 2) รองรับทั้งกรณี ORM เก็บใน attributes() หรือเป็น public properties
            $vars = method_exists($record, 'attributes') ? $record->attributes() : get_object_vars($record);

            // 3) วนเฉพาะฟิลด์ status_5_*
            foreach ($vars as $property => $value) {
                if (!preg_match('/^status_5_\d+$/', $property)) continue;

                // $code เช่น "5_8", "5_09", "5_10"
                $code = substr($property, 7);

                // ❗ ถ้าไม่ใช่เรือห้องเย็น → ข้าม 5.8 และ 5.9 (เผื่อรูปแบบมีศูนย์นำหน้า)
                if (!$cold && preg_match('/^5_0?(8|9)$/', $code)) {
                    continue;
                }

                $status = $record->$property ?? null;
                $status = is_string($status) ? trim($status) : $status;

                if ($status !== 'pass' && $status !== 'fail') {
                    $is_complete = false; // ยังไม่ได้เลือก
                    break;
                }

                if ($status === 'fail') {
                    $hasAnyCheckbox = false;
                    $reasonProvided = false;

                    // หา fail_{5_x}_{j} ให้ตรงกับ code จริง (รองรับ 5_8 และ 5_09)
                    $pattern = '/^fail_' . preg_quote($code, '/') . '_\d+$/';

                    foreach ($vars as $fail_prop => $fail_value) {
                        if (preg_match($pattern, $fail_prop)) {
                            $hasAnyCheckbox = true;
                            if (!empty($fail_value)) {
                                $reasonProvided = true;
                                break;
                            }
                        }
                    }

                    // remark ของข้อปัจจุบัน: remark_{5_x}
                    $remark_field = 'remark_' . $code;
                    $remark = $record->$remark_field ?? '';
                    if (trim((string)$remark) !== '') {
                        $reasonProvided = true;
                    }

                    // มี checkbox แต่ยังไม่มีเหตุผลทั้ง checkbox/remark → ไม่ครบ
                    if ($hasAnyCheckbox && !$reasonProvided) {
                        $is_complete = false;
                        break;
                    }
                }
            }

            // 4) อัปเดตสถานะความครบถ้วนของหมวด 5
            $status_record = InspectionFormStatus::find_by_request_id($request_id);
            if ($status_record) {
                $status_record->form_preservation_status = $is_complete ? 1 : 0;
                $status_record->save();
            }

            return $is_complete;
        }



    public static function find_by_request_id($request_id) {
        $escaped = self::$database->escape_string($request_id);
        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '{$escaped}'";
        // //error_log($sql);
        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }
}