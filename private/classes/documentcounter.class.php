<?php

class DocumentCounter extends DatabaseObject
{
    static protected $table_name = "document_counters";
    static protected $db_columns = ['id', 'doc_type', 'year', 'running', 'updated_at'];

    public $id;
    public $doc_type;
    public $year;
    public $running;
    public $updated_at;

    public function __construct($args = [])
    {
        $this->id        = $args['id']        ?? null;
        $this->doc_type  = $args['doc_type']  ?? null;
        $this->year      = $args['year']      ?? null;
        $this->running   = $args['running']   ?? 0;
        $this->updated_at= $args['updated_at']?? null;
    }

    /**
     * ออกเลขเอกสารอิงปีจาก effective_date (Y-m-d)
     * แยกเลขตาม doc_type (เช่น SR1, SR3)
     * คืน [$doc_code, $running, $year]
     */
    public static function next_code_by_effective(string $doc_type, string $effective_date_ymd)
    {
        $db = static::$database;

        // 1) ตรวจ/แปลงวันที่ -> ปี
        $effective_date_ymd = substr(trim($effective_date_ymd), 0, 10);
        $dt = DateTime::createFromFormat('Y-m-d', $effective_date_ymd);
        if (!$dt) {
            throw new Exception("Invalid effective_date format: expect Y-m-d");
        }
        $year = (int)$dt->format('Y');

        // 2) เตรียม doc_type
        $doc_type = strtoupper(trim($doc_type));
        if ($doc_type === '') {
            throw new Exception("Invalid doc_type");
        }

        $year_int     = (int)$year;
        $doc_type_sql = "'" . $db->real_escape_string($doc_type) . "'";

        // 3) อัปเดตตัวนับแบบอะตอมิก: UPDATE ก่อน ถ้าไม่มีค่อย INSERT = 1
        $running = null;

        // UPDATE: running = running + 1 พร้อมตั้ง LAST_INSERT_ID
        $sqlUpdate = "
            UPDATE " . static::$table_name . "
            SET running = LAST_INSERT_ID(running + 1),
                updated_at = NOW()
            WHERE year = {$year_int} AND doc_type = {$doc_type_sql}
        ";

        if (!$db->query($sqlUpdate)) {
            throw new Exception("Counter update failed: " . $db->error);
        }

        if ($db->affected_rows > 0) {
            // มีแถวเดิม -> อ่านค่าที่เพิ่งบวก
            $r2    = $db->query("SELECT LAST_INSERT_ID() AS running");
            $row2  = $r2->fetch_assoc();
            $running = (int)$row2['running'];
        } else {
            // ไม่มีแถว -> INSERT แถวแรก = 1
            $sqlInsert = "
                INSERT INTO " . static::$table_name . " (doc_type, year, running, updated_at)
                VALUES ({$doc_type_sql}, {$year_int}, 1, NOW())
            ";
            if (!$db->query($sqlInsert)) {
                // กัน race: ถ้าโดน 1062 (unique key ซ้ำ) ให้ UPDATE อีกรอบแล้วอ่านค่า
                if ((int)$db->errno === 1062) {
                    if (!$db->query($sqlUpdate)) {
                        throw new Exception("Counter retry update failed: " . $db->error);
                    }
                    $r2    = $db->query("SELECT LAST_INSERT_ID() AS running");
                    $row2  = $r2->fetch_assoc();
                    $running = (int)$row2['running'];
                } else {
                    throw new Exception("Counter insert failed: " . $db->error);
                }
            } else {
                // แถวแรกของปี/doc_type นี้
                $running = 1;
            }
        }

        // 4) ประกอบรหัสเอกสาร
        $run_str  = str_pad($running, 5, '0', STR_PAD_LEFT);
        // ตัวอย่าง format: efvscis-2025-SR1-00001
        $doc_code = "efvscis-{$year_int}-{$doc_type}-{$run_str}";

        return [$doc_code, $running, $year_int];
    }
}
