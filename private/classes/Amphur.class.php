<?php
class Amphur extends DatabaseObject {

    static protected $table_name = 'amphur';
    static protected $db_columns = [
        'id', 'create_uid', 'code', 'create_date', 'name',
        'write_uid', 'write_date', 'province_id', 'name_eng'
    ];

    public $id;
    public $create_uid;
    public $code;
    public $create_date;
    public $name;
    public $write_uid;
    public $write_date;
    public $province_id;
    public $name_eng;

    public function __construct($args=[]) {
        $this->create_uid = $args['create_uid'] ?? '';
        $this->code = $args['code'] ?? '';
        $this->create_date = $args['create_date'] ?? '';
        $this->name = $args['name'] ?? '';
        $this->write_uid = $args['write_uid'] ?? '';
        $this->write_date = $args['write_date'] ?? '';
        $this->province_id = $args['province_id'] ?? '';
        $this->name_eng = $args['name_eng'] ?? '';
    }

    static public function find_by_province_id($province_id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE province_id='" . self::$database->escape_string($province_id) . "'";
        $obj_array = static::find_by_sql($sql);
        if (!empty($obj_array)) {
            return $obj_array;
        } else {
            return false;
        }
    }
}
?>
