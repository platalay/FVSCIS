<?php

class InspectionMainItem extends DatabaseObject
{
    protected static $table_name = "inspection_main_items";

    protected static $db_columns = [
        'id',
        'section_code',
        'category',
        'title_th',
        'has_fail_items',
        'order_no',
        'created_at',
    ];

    public $id;
    public $section_code;
    public $category        = 1;  // 1 = ทั่วไป, 2 = EU, 3 = อื่นๆในอนาคต
    public $title_th;
    public $has_fail_items  = 0;  // 0 = ไม่มี fail ย่อย, 1 = มี fail ย่อย
    public $order_no        = 1;
    public $created_at;

    public function __construct($args = [])
    {
        $this->section_code   = $args['section_code']   ?? '';
        $this->category       = $args['category']       ?? 1;
        $this->title_th       = $args['title_th']       ?? '';
        $this->has_fail_items = $args['has_fail_items'] ?? 0;
        $this->order_no       = $args['order_no']       ?? 1;
        $this->created_at     = $args['created_at']     ?? $this->created_at;
    }

    /**
     * หา Main Item ตามรหัสหัวข้อ เช่น 1_1, 5_7
     * ถ้าระบุ category จะกรองตามประเภทด้วย (1 = ทั่วไป, 2 = EU)
     */
    public static function find_by_section_code($section_code, $category = null)
    {
        $db   = static::$database;
        $code = $db->escape_string($section_code);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE section_code = '{$code}'";

        if ($category !== null) {
            $cat = (int)$category;
            $sql .= " AND category = {$cat}";
        }

        $sql .= " ORDER BY order_no, id LIMIT 1";

        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }

    /**
     * ดึงรายการทั้งหมดของ category นั้นๆ
     * เช่น category = 1 → หมวดทั่วไป, category = 2 → หมวด EU
     */
    public static function find_all_by_category($category)
    {
        $cat = (int)$category;

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE category = {$cat}";
        $sql .= " ORDER BY section_code, order_no, id";

        return static::find_by_sql($sql);
    }

    /**
     * ดึงรายการทั้งหมดของ section เดียวกัน (เช่น 1_1 ทั้งทั่วไป + EU)
     */
    public static function find_all_by_section_code($section_code)
    {
        $db   = static::$database;
        $code = $db->escape_string($section_code);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE section_code = '{$code}'";
        $sql .= " ORDER BY category, order_no, id";

        return static::find_by_sql($sql);
    }

    /**
     * helper เล็กๆ ไว้เช็คว่าข้อนี้มี fail ย่อยไหม
     */
    public function has_fails()
    {
        return (int)$this->has_fail_items === 1;
    }

    /**
     * ใหม่: ดึงหัวข้อของ "หมวด" ที่ต้องการ เช่น section=1 (ได้ 1_1, 1_2, ...), 
     * กรองตาม category (1 = ทั่วไป, 2 = EU)
     */
    public static function find_by_section_and_category(int $section, int $category = 1)
    {
        $db = static::$database;
        $prefix = $db->escape_string($section . '_'); // เช่น "1_"

        $cat = (int)$category;

        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE section_code LIKE '{$prefix}%' ";
        $sql .= "AND category = {$cat} ";
        $sql .= "ORDER BY order_no, id";

        return static::find_by_sql($sql);
    }
}
