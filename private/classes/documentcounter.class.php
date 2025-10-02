<?php
class DocumentCounter extends DatabaseObject {

    static protected $table_name  = "document_counters";
    static protected $db_columns  = ['year', 'department_group_id', 'running', 'updated_at'];

    public $year;
    public $department_group_id;
    public $running;
    public $updated_at;

    public function __construct($args = []) {
        $this->year                = $args['year'] ?? null;
        $this->department_group_id = $args['department_group_id'] ?? null;
        $this->running             = $args['running'] ?? 0;
        $this->updated_at          = $args['updated_at'] ?? null;
    }

    /**
     * ออกเลขเอกสารอิงปีจาก effective_date (Y-m-d)
     * คืน [$doc_code, $running, $year]
     */
    public static function next_code_by_effective($department_group_id, string $effective_date_ymd) {
        $db  = static::$database;

        // 1) ตรวจ/แปลงวันที่
        $effective_date_ymd = substr(trim($effective_date_ymd), 0, 10);
        $dt = DateTime::createFromFormat('Y-m-d', $effective_date_ymd);
        if (!$dt) throw new Exception("Invalid effective_date format: expect Y-m-d");
        $year = (int)$dt->format('Y');

        // 2) ค่าเบื้องต้น
        $dgid = (int)$department_group_id;
        if ($dgid <= 0) throw new Exception("Invalid department_group_id");

        // 3) หารหัสหน่วย (มี fallback)
        $dept_code_two = str_pad((string)$dgid, 2, '0', STR_PAD_LEFT); // fallback ก่อน
        if ($res = $db->query("SELECT code FROM department_groups WHERE id = {$dgid} LIMIT 1")) {
            if ($res->num_rows === 1) {
                $row  = $res->fetch_assoc();
                $code = trim((string)($row['code'] ?? ''));
                if ($code !== '') {
                    $dept_code_two = ctype_digit($code) ? str_pad($code, 2, '0', STR_PAD_LEFT) : $code;
                }
            }
        }

        // 4) อัปเดตตัวนับแบบอะตอมิก: UPDATE ก่อน ถ้าไม่มีค่อย INSERT=1
        $running = null;

        // UPDATE: running = running + 1 พร้อมตั้ง LAST_INSERT_ID
        $sqlUpdate = "
            UPDATE " . static::$table_name . "
            SET running = LAST_INSERT_ID(running + 1)
            WHERE year = {$year} AND department_group_id = {$dgid}
        ";
        if (!$db->query($sqlUpdate)) {
            throw new Exception("Counter update failed: " . $db->error);
        }

        if ($db->affected_rows > 0) {
            // มีแถวเดิม -> อ่านค่าที่เพิ่งบวก
            $r2 = $db->query("SELECT LAST_INSERT_ID() AS running");
            $row2 = $r2->fetch_assoc();
            $running = (int)$row2['running'];
        } else {
            // ไม่มีแถว -> INSERT แถวแรก = 1
            $sqlInsert = "
                INSERT INTO " . static::$table_name . " (year, department_group_id, running)
                VALUES ({$year}, {$dgid}, 1)
            ";
            if (!$db->query($sqlInsert)) {
                // กัน race: ถ้าโดน 1062 (มีคนแทรกก่อนหน้า) ให้ UPDATE อีกรอบแล้วอ่านค่า
                if ((int)$db->errno === 1062) {
                    if (!$db->query($sqlUpdate)) {
                        throw new Exception("Counter retry update failed: " . $db->error);
                    }
                    $r2 = $db->query("SELECT LAST_INSERT_ID() AS running");
                    $row2 = $r2->fetch_assoc();
                    $running = (int)$row2['running'];
                } else {
                    throw new Exception("Counter insert failed: " . $db->error);
                }
            } else {
                // แถวแรกของปี/หน่วยนี้
                $running = 1;
            }
        }

        // 5) ประกอบรหัสเอกสาร
        $run_str  = str_pad($running, 5, '0', STR_PAD_LEFT);
        $doc_code = "efvscis-{$year}-{$dept_code_two}-{$run_str}";

        return [$doc_code, $running, $year];
    }
}
