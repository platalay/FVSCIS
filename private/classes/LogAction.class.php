<?php
class LogAction extends DatabaseObject {
    protected static $table_name = "log_actions";
    protected static $db_columns = ['id', 'code', 'description_th', 'description_en', 'category', 'is_visible'];

    public $id;
    public $code;
    public $description_th;
    public $description_en;
    public $category;
    public $is_visible = true;


    public static function find_by_code($code) {
        $code = self::$database->escape_string($code);
        $sql = "SELECT * FROM " . static::$table_name . " WHERE code = '{$code}' LIMIT 1";
        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }
}
?>
