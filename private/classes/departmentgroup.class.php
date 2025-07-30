<?php
class DepartmentGroup extends DatabaseObject {

    static protected $table_name = "department_groups";
    static protected $db_columns = ['id', 'name', 'note', 'officer_id'];

    public $id;
    public $name;
    public $note;
    public $officer_id;

    public function __construct($args=[]) 
    {
        $this->name = $args['name'] ?? '';
        $this->note = $args['note'] ?? '';
        $this->officer_id = $args['officer_id'] ?? null;
    }

    public static function find_one_by_officer_id($officer_id) {
        $officer_id = self::$database->escape_string($officer_id);
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE officer_id = '{$officer_id}'";
        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }

}
?>