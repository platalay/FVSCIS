<?php

class InspectionFormMaterial extends DatabaseObject {
    protected static $table_name = "inspection_form_material";
    protected static $db_columns = [
        'id', 'request_id',
        'status_2_1', 'fail_2_1_1', 'fail_2_1_2', 'remark_2_1',
        'status_2_2', 'fail_2_2_1', 'remark_2_2',
        'status_2_3', 'fail_2_3_1', 'remark_2_3',
        'status_2_4', 'fail_2_4_1', 'fail_2_4_2', 'fail_2_4_3', 'remark_2_4',
        'status_2_5', 'fail_2_5_1', 'remark_2_5',
        'status_2_6', 'fail_2_6_1', 'fail_2_6_2', 'remark_2_6',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
    ];

     

    public $id;
    public $request_id;

    public $status_2_1;
    public $fail_2_1_1;
    public $fail_2_1_2;
    public $remark_2_1;

    public $status_2_2;
    public $fail_2_2_1;
    public $remark_2_2;

    public $status_2_3;
    public $fail_2_3_1;
    public $remark_2_3;

    public $status_2_4;
    public $fail_2_4_1;
    public $fail_2_4_2;
    public $fail_2_4_3;
    public $remark_2_4;

    public $status_2_5;
    public $fail_2_5_1;
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
                $status_record->form_material_status = $is_complete ? 1 : 0;
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
            '2_1' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 205],
                'fail' => ['x' => 150, 'y' => 205],
                'fails' => ['fail_2_1_1', 'fail_2_1_2'],
            ],
            '2_2' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 223],
                'fail' => ['x' => 150, 'y' => 223],
                'fails' => ['fail_2_2_1'],
            ],
            '2_3' => [
                'page' => 1,
                'pass' => ['x' => 133, 'y' => 240],
                'fail' => ['x' => 150, 'y' => 240],
                'fails' => ['fail_2_3_1'],
            ],
            '2_4' => [
                'page' => 2,
                'pass' => ['x' => 133, 'y' => 58],
                'fail' => ['x' => 150, 'y' => 58],
                'fails' => ['fail_2_4_1','fail_2_4_2','fail_2_4_3'],
            ],
            '2_5' => [
                'page' => 2,
                'pass' => ['x' => 133, 'y' => 75],
                'fail' => ['x' => 150, 'y' => 75],
                'fails' => ['fail_2_5_1'],
            ],
            '2_6' => [
                'page' => 2,
                'pass' => ['x' => 133, 'y' => 90],
                'fail' => ['x' => 150, 'y' => 90],
                'fails' => ['fail_2_6_1','fail_2_6_2'],
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
