<?php
class InspectionFailItem extends DatabaseObject {
    protected static $table_name = "inspection_fail_items";
    protected static $db_columns = ['id', 'form_section', 'field_name', 'label_text', 'order_no'];

    public $id;
    public $form_section;
    public $field_name;
    public $label_text;
    public $order_no;

    public static function find_by_section($section) {
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE form_section = '" . self::$database->escape_string($section) . "'";
        $sql .= " ORDER BY order_no ASC";
        error_log(date('[Y-m-d H:i:s] ') . "SQL: " . $sql);
        return static::find_by_sql($sql);
    }
}
?>