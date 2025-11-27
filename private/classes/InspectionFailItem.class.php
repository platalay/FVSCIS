<?php

class InspectionFailItem extends DatabaseObject
{
    protected static $table_name = "inspection_fail_items";

    protected static $db_columns = [
        'id',
        'main_item_id',
        'fail_code',
        'label_text',
        'order_no',
        'created_at'
    ];

    public $id;
    public $main_item_id;
    public $fail_code;
    public $label_text;
    public $order_no = 1;
    public $created_at;

    public function __construct($args = [])
    {
        $this->main_item_id = $args['main_item_id'] ?? null;
        $this->fail_code    = $args['fail_code']    ?? '';
        $this->label_text   = $args['label_text']   ?? '';
        $this->order_no     = $args['order_no']     ?? 1;
        $this->created_at   = $args['created_at']   ?? $this->created_at;
    }

    /** ดึง fail items ทั้งหมด ของ main_item_id เดียว */
    public static function find_by_main_item_id($main_item_id)
    {
        $id = (int)$main_item_id;

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE main_item_id = {$id}";
        $sql .= " ORDER BY order_no, id";

        return static::find_by_sql($sql);
    }

    /** ดึง fail item รายการเดียวจาก fail_code (ถ้าใช้เดี่ยวๆ) */
    public static function find_by_fail_code($fail_code)
    {
        $db   = static::$database;
        $code = $db->escape_string($fail_code);

        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE fail_code = '{$code}'";
        $sql .= " LIMIT 1";

        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }

    /**
     * ใหม่: ดึง fail items สำหรับ main_item_id หลายตัวในครั้งเดียว
     */
    public static function find_by_main_item_ids(array $ids = [])
    {
        if (empty($ids)) { return []; }

        $ids = array_map('intval', $ids);
        $id_list = join(',', $ids);

        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE main_item_id IN ({$id_list}) ";
        $sql .= "ORDER BY main_item_id ASC, order_no ASC, id ASC";

        return static::find_by_sql($sql);
    }
}

