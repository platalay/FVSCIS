<?php
class Province extends DatabaseObject {

    static protected $table_name = 'province';
    static protected $db_columns = [
        'id', 'create_uid', 'code', 'create_date', 'name',
        'write_uid', 'write_date', 'name_eng', 'comp_code', 'cost_center_code'
    ];

    public $id;
    public $create_uid;
    public $code;
    public $create_date;
    public $name;
    public $write_uid;
    public $write_date;
    public $name_eng;
    public $comp_code;
    public $cost_center_code;

    public function __construct($args=[]) {
        $this->create_uid = $args['create_uid'] ?? '';
        $this->code = $args['code'] ?? '';
        $this->create_date = $args['create_date'] ?? '';
        $this->name = $args['name'] ?? '';
        $this->write_uid = $args['write_uid'] ?? '';
        $this->write_date = $args['write_date'] ?? '';
        $this->name_eng = $args['name_eng'] ?? '';
        $this->comp_code = $args['comp_code'] ?? '';
        $this->cost_center_code = $args['cost_center_code'] ?? '';
    }

    static public function find_by_code($code) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE code='" . self::$database->escape_string($code) . "' LIMIT 1";
        $obj_array = static::find_by_sql($sql);
        return !empty($obj_array) ? array_shift($obj_array) : false;
    }
}
?>
