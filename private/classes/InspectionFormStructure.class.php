<?php

class InspectionFormStructure extends DatabaseObject {
    protected static $table_name = "inspection_form_structure";
    protected static $db_columns = [
        'id', 'request_id',
        'status_1_1', 'fail_1_1_1', 'fail_1_1_2', 'fail_1_1_3', 'fail_1_1_4', 'fail_1_1_5', 'remark_1_1',
        'status_1_2', 'fail_1_2_1', 'remark_1_2',
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
    public $fail_1_1_4;
    public $fail_1_1_5;
    public $remark_1_1;

    public $status_1_2;
    public $fail_1_2_1;
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

    public function __construct($args = []) {
        $this->merge_attributes($args);
    }

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

        // 1) ดึง record (หรือสร้างใหม่ถ้าไม่มี)
        $record = self::find_or_create($request_id);

        // 2) กันกรณี field ไม่ใช่คอลัมน์จริง → ป้องกัน save ล้ม
        if (!in_array($field, static::$db_columns)) {
            error_log("autosave error: Field '{$field}' not found in table " . static::$table_name);
            return false;
        }

        // 3) sanitize ค่า checkbox ให้เป็น 0/1
        if (preg_match('/^fail_\d+_\d+_\d+$/', $field)) {
            $value = ($value == 1 ? 1 : 0);
        }

        // 4) อัปเดตค่า
        $record->$field = $value;

        // 5) save แล้วตรวจว่าการ save สำเร็จ
        $saved = $record->save();
        if ($saved) {

            // 6) เรียกฟังก์ชันตรวจความสมบูรณ์ฟอร์ม (เช่น set completed/not completed)
            if (method_exists(get_called_class(), 'check_complete')) {
                self::check_complete($request_id);
            }

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
            //error_log($sql);
            $result = static::find_by_sql($sql);
            return !empty($result) ? array_shift($result) : null;
        }

        public static $statusMap = [
            '1_1' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 72],
                'fail' => ['x' => 150, 'y' => 72],
                'fails' => ['fail_1_1_1', 'fail_1_1_2', 'fail_1_1_3', 'fail_1_1_4'],
            ],
            '1_2' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 85],
                'fail' => ['x' => 150, 'y' => 85],
                'fails' => ['fail_1_2_1'],
            ],
            '1_3' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 100],
                'fail' => ['x' => 150, 'y' => 100],
                'fails' => ['fail_1_3_1', 'fail_1_3_2'],
            ],
            '1_4' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 120],
                'fail' => ['x' => 150, 'y' => 120],
                'fails' => ['fail_1_4_1', 'fail_1_4_2'],
            ],
            '1_5' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 145],
                'fail' => ['x' => 150, 'y' => 145],
                'fails' => ['fail_1_5_1', 'fail_1_5_2', 'fail_1_5_3'],
            ],
            '1_6' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 160],
                'fail' => ['x' => 150, 'y' => 160],
                'fails' => ['fail_1_6_1', 'fail_1_6_2'],
            ],
            '1_7' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 180],
                'fail' => ['x' => 150, 'y' => 180],
                'fails' => ['fail_1_7_1', 'fail_1_7_2', 'fail_1_7_3', 'fail_1_7_4'],
            ],
        ];

    /**
     * นับจำนวนประเด็นบกพร่องของข้อหนึ่ง ๆ
     * - นับ fail_1_x_y ที่เป็น 1
     * - ถ้ามี remark_1_x → +1
     */
    public static function countFails($form, string $section): int
    {
        $count = 0;

        $map = self::$statusMap[$section] ?? null;
        if (!$map) {
            return 0;
        }

        foreach ($map['fails'] ?? [] as $field) {
            if (!empty($form->$field) && (int)$form->$field === 1) {
                $count++;
            }
        }

        $remarkField = "remark_{$section}";
        if (!empty($form->$remarkField)) {
            $count++;
        }

        return $count;
    }



        /**
        * วาด X ที่ช่อง pass/fail ตามค่า status
        * $status = 'pass' หรือ 'fail'
        */
        public static function drawStatus(\FPDF $pdf, string $status, array $pos, string $mark = 'X'): void
        {
            $xy = ($status === 'pass') ? $pos['pass'] : $pos['fail'];
            $mark = ($status === 'pass') ? '/' : 'X';
            $pdf->SetXY($xy['x'], $xy['y']);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->Cell(4, 4, $mark, 0, 0, 'C');
        }

        /**
        * helper สำหรับ text "บกพร่อง x ประเด็น / รายละเอียดใน สร.2-4"
        */
        public static function buildFailSummaryText(int $count): string
        {
            if ($count <= 0) {
                return '';
            }
            return "บกพร่อง {$count} ประเด็น";
        }

}
