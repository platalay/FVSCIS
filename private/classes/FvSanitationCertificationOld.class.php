<?php

class FvSanitationCertificationOld extends DatabaseObject
{
    protected static $table_name = "fv_sanitation_certification_old";
    protected static $db_columns = [
        'id', 'vessel_name', 'ship_code', 'fisherman_id', 'vessel_mark', 'license_number', 'license_status',
        'gear_type', 'owner_name', 'certificate_number',
        'request_date', 'signature_date', 'effective_date', 'expiration_date', 'status',
        'vessel_status', 'evaluation_agency', 'signing_unit', 'temporary_reason', 'responsible_unit', 'certificate_status', 'remark', 'type'
    ];

    public $id;
    public $vessel_name;
    public $ship_code;
    public $fisherman_id;
    public $vessel_mark;
    public $license_number;
    public $license_status;
    public $gear_type;
    public $owner_name;
    public $certificate_number;
    public $request_date;
    public $signature_date;
    public $effective_date;
    public $expiration_date;
    public $status;
    public $vessel_status;
    public $evaluation_agency;//Department
    public $signing_unit;//DepartmentGroup
    public $temporary_reason;
    public $responsible_unit;//DepartmentGroup
    public $certificate_status;
    public $remark;
    public $type;

    // ✅ หา record ด้วยทะเบียนเรือ
    public static function find_by_ship_code($ship_code)
    {
        $sc = self::$database->escape_string(trim($ship_code));

        // เอาเรคคอร์ดที่มีวันหมดอายุจริง และเรียงใหม่สุดก่อน
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '{$sc}' ";
        $sql .= "  AND expiration_date IS NOT NULL ";
        $sql .= "  AND expiration_date <> '0000-00-00' ";
        $sql .= "ORDER BY expiration_date DESC, effective_date DESC, signature_date DESC, id DESC ";
        $sql .= "LIMIT 1";

        $result_array = static::find_by_sql($sql);
        if (!empty($result_array)) {
            return array_shift($result_array);
        }

        // fallback: ถ้าไม่มีวันหมดอายุที่ใช้ได้เลย ให้คืนเรคคอร์ดล่าสุดตาม id
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '{$sc}' ";
        $sql .= "ORDER BY id DESC ";
        $sql .= "LIMIT 1";

        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : null;
    }


    public function __construct($args = [])
    {
        foreach (static::$db_columns as $col) {
            if ($col === 'id') continue;                       // ไม่ให้แก้ id
            if (!property_exists($this, $col)) continue;       // กัน key แปลกปลอม
            $val = $args[$col] ?? ($this->$col ?? null);
            // แปลงค่าว่างให้เป็น null กัน '0000-00-00'
            if (is_string($val)) $val = trim($val);
            $this->$col = ($val === '') ? null : $val;
        }
    }

    
    public static function find_one_by_ship_code($ship_code)
    {
        return static::find_by_ship_code($ship_code);
    }

    public static function find_all_by_signing_unit($signing_unit)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE signing_unit = '" . self::$database->escape_string($signing_unit) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }

    public static function find_all_by_evaluation_agency($evaluation_agency)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE evaluation_agency = '" . self::$database->escape_string($evaluation_agency) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }

    public static function find_all_by_responsible_unit($responsible_unit)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE responsible_unit = '" . self::$database->escape_string($responsible_unit) . "' ";
        $sql .= "ORDER BY id DESC";
        return static::find_by_sql($sql);
    }
    
    public static function link_to_fisherman_by_citizen(Fisherman $fisherman) {

        if (empty($fisherman->citizen_id)) {
            return;
        }
        //error_log("fisherman->citizen_id = " . $fisherman->citizen_id);
        $vessels = Elicense::find_full_by_citizen_id_auto($fisherman->citizen_id);
        if (empty($vessels)) {
            return;
        }

        $ship_codes = [];
        foreach ($vessels as $vessel) {
            if (!empty($vessel->ship_code)) {
                $ship_codes[] = $vessel->ship_code;
                //error_log("vessel->ship_code = " . $vessel->ship_code);
            }
        }

        $ship_codes = array_values(array_unique($ship_codes));

        if (empty($ship_codes)) {
            return;
        }

        $escaped = [];
        foreach ($ship_codes as $code) {
            $escaped[] = "'" . self::$database->escape_string($code) . "'";
        }
        $list = implode(',', $escaped);

        $fisherman_id = (int)$fisherman->id;

        $sql  = "UPDATE " . static::$table_name . " ";
        $sql .= "SET fisherman_id = {$fisherman_id} ";
        $sql .= "WHERE ship_code IN ({$list}) ";
        $sql .= "AND (fisherman_id IS NULL OR fisherman_id = 0)";

        self::$database->query($sql);
    }    



    // ======== ตระกูลนับ ================
    protected static function build_latest_join_sql(string $where = ''): string
    {
        $where = trim($where);
        $whereSql = $where !== '' ? "WHERE {$where}" : "";

        return "
            FROM (
                SELECT ship_code, MAX(id) AS last_id
                FROM " . static::$table_name . "
                {$whereSql}
                GROUP BY ship_code
            ) t
            JOIN " . static::$table_name . " o ON o.id = t.last_id
        ";
    }

    public static function count_active_by_fisherman($fisherman_id) {
        $fisherman_id = self::$database->escape_string($fisherman_id);

        // สมมติว่าถือว่า "ยังใช้ได้" = expire_at >= TODAY และ status = 'active'
        $sql  = "SELECT COUNT(*) AS cnt ";
        $sql .= "FROM " . static::$table_name . " ";
        $sql .= "WHERE fisherman_id = '{$fisherman_id}' ";
        $sql .= "AND expiration_date >= CURDATE() ";
        $sql .= "AND status = 'active'";

        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }

    public static function count_active_by_responsible_unit($unit_id = null): int
    {
        $where = "";
        if ($unit_id !== null) {
            $unit_id = self::$database->escape_string($unit_id);
            $where = "responsible_unit = '{$unit_id}'";
        }

        $sql = "SELECT COUNT(*) AS cnt "
            . static::build_latest_join_sql($where)
            . " WHERE o.status = 'active'";

        $result = self::$database->query($sql);
        if (!$result) return 0;

        $row = $result->fetch_assoc();
        return (int)($row['cnt'] ?? 0);
    }



    public static function count_distinct_shipcode_by_status(): array
    {
        $counts = [
            'active'   => 0,
            'inactive' => 0,
            'pending'  => 0,
        ];

        $sql = "
            SELECT o.status, COUNT(*) AS total
            " . static::build_latest_join_sql() . "
            WHERE o.status IN ('active', 'inactive', 'pending')
            GROUP BY o.status
        ";

        $result = self::$database->query($sql);
        if (!$result) return $counts;

        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            if (isset($counts[$status])) {
                $counts[$status] = (int)$row['total'];
            }
        }

        return $counts;
    }


    public static function count_by_status_responsible_unit($status, $responsible_unit): int
    {
        $status = self::$database->escape_string($status);
        $unit   = self::$database->escape_string($responsible_unit);

        $sql = "
            SELECT COUNT(*) AS total
            " . static::build_latest_join_sql("responsible_unit = '{$unit}'") . "
            WHERE o.status = '{$status}'
        ";

        $result = self::$database->query($sql);
        if (!$result) return 0;

        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    public static function count_by_status_evaluation_agency($status, $evaluation_agency): int
    {
        $status = self::$database->escape_string($status);
        $unit   = self::$database->escape_string($evaluation_agency);

        $sql = "
            SELECT COUNT(*) AS total
            " . static::build_latest_join_sql("evaluation_agency = '{$unit}'") . "
            WHERE o.status = '{$status}'
        ";

        $result = self::$database->query($sql);
        if (!$result) return 0;

        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

            // ====== ตระกูล mark ทั้งหลายแหล่ =======
            // ====== Helper: หา id ล่าสุดของเรือลำนี้ ======
            protected static function latest_id_by_ship_code(string $ship_code): ?int
            {
                $ship_code = self::$database->escape_string($ship_code);

                $sql = "SELECT id
                        FROM " . static::$table_name . "
                        WHERE ship_code = '{$ship_code}'
                        ORDER BY id DESC
                        LIMIT 1";

                $result = self::$database->query($sql);
                if (!$result) return null;

                $row = $result->fetch_assoc();
                return $row ? (int)$row['id'] : null;
            }

            // ====== Helper: update status เฉพาะ id ที่ระบุ ======
            protected static function update_status_by_id(int $id, string $status): int|false
            {
                $id     = self::$database->escape_string((string)$id);
                $status = self::$database->escape_string($status);

                $sql = "UPDATE " . static::$table_name . "
                        SET status = '{$status}'
                        WHERE id = '{$id}'
                        AND (status IS NULL OR status <> '{$status}')";

                $ok = self::$database->query($sql);
                if (!$ok) return false;

                return (int) self::$database->affected_rows; // 0 หรือ 1
            }

            // ====== Helper: update status เฉพาะ record ล่าสุดของ ship_code ======
            protected static function update_latest_status(string $ship_code, string $status): int|false
            {
                $latest_id = static::latest_id_by_ship_code($ship_code);
                if (!$latest_id) return false; // ไม่พบ record ล่าสุด

                return static::update_status_by_id($latest_id, $status);
            }

            // ===================================================================
            // MARK FUNCTIONS (ใหม่): กระทบเฉพาะ record ล่าสุด เพื่อให้สอดคล้อง UI
            // ===================================================================

            public static function mark_pending(string $ship_code): int|false
            {
                return static::update_latest_status($ship_code, 'pending');
            }

            public static function mark_fail(string $ship_code): int|false
            {
                return static::update_latest_status($ship_code, 'fail');
            }

            public static function mark_pass(string $ship_code): int|false
            {
                return static::update_latest_status($ship_code, 'pass');
            }

            public static function mark_inactive(string $ship_code): int|false
            {
                return static::update_latest_status($ship_code, 'inactive');
            }

            // ===================================================================
            // mark_active: เลือก "ใบที่ยังไม่หมดอายุ" ที่หมดอายุช้าที่สุด → ให้เป็น active
            // และเพื่อให้สอดคล้องหน้าโชว์ "ล่าสุดเท่านั้น" ให้เราอัปเดตเฉพาะ record ที่เลือก
            // ===================================================================

            public static function mark_active(string $ship_code): int|false
            {
                $ship_code = self::$database->escape_string($ship_code);
                $today     = date('Y-m-d');

                // 1) เลือก "ใบที่ยังไม่หมดอายุ" ที่หมดอายุช้าที่สุดของเรือลำนี้
                $sql  = "SELECT id FROM " . static::$table_name . " ";
                $sql .= "WHERE ship_code = '{$ship_code}' ";
                $sql .= "  AND expiration_date IS NOT NULL ";
                $sql .= "  AND expiration_date >= '{$today}' ";
                $sql .= "ORDER BY expiration_date DESC, id DESC ";
                $sql .= "LIMIT 1";

                $result = self::$database->query($sql);
                if (!$result) return false;

                $row = $result->fetch_assoc();

                // 2) ถ้าไม่มีใบที่ยังไม่หมดอายุเลย → set ล่าสุดเป็น inactive
                if (!$row) {
                    // ใช้ latest ตาม id (หน้าโชว์)
                    $latest_id = static::latest_id_by_ship_code($ship_code);
                    if (!$latest_id) return false;
                    return static::update_status_by_id($latest_id, 'inactive');
                }

                // 3) มีใบที่ยังไม่หมดอายุ → ใบนั้น active (อัปเดตแค่ record นั้น)
                $active_id = (int)$row['id'];
                return static::update_status_by_id($active_id, 'active');
            }

            // ===================================================================
            // reset_after_request_deleted: หลังลบ request ให้ reset สถานะใน old
            // - ถ้า request ล่าสุดของเรือลำนี้เป็น fail → mark_fail (เฉพาะล่าสุดของ old)
            // - ถ้าไม่ fail → ใช้ logic expiration_date เลือก active หรือ inactive
            // ===================================================================

            public static function reset_after_request_deleted(string $ship_code): int|false
            {
                $ship_code_esc = self::$database->escape_string($ship_code);

                // 1) เช็กสถานะล่าสุดจาก InspectionRequest ของเรือลำนี้
                $latestReq = InspectionRequest::find_latest_by_ship_code($ship_code_esc);
                $latestStatus = $latestReq->status ?? null;

                if ($latestReq && $latestStatus === 'fail') {
                    return static::mark_fail($ship_code);
                }

                // 2) ไม่ fail → ใช้ logic expiration_date
                $today = date('Y-m-d');

                $sql  = "SELECT id FROM " . static::$table_name . " ";
                $sql .= "WHERE ship_code = '{$ship_code_esc}' ";
                $sql .= "  AND expiration_date IS NOT NULL ";
                $sql .= "  AND expiration_date >= '{$today}' ";
                $sql .= "ORDER BY expiration_date DESC, id DESC ";
                $sql .= "LIMIT 1";

                $result = self::$database->query($sql);
                if (!$result) return false;

                $row = $result->fetch_assoc();

                if ($row) {
                    return static::update_status_by_id((int)$row['id'], 'active');
                }

                // 3) ไม่มีใบที่ยังไม่หมดอายุเลย → inactive ล่าสุด (ตามหน้าโชว์)
                return static::mark_inactive($ship_code);
            }


}