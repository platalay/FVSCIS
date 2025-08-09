<?php
class Tambon extends DatabaseObject {

    static protected $table_name = 'tambon';
    static protected $db_columns = [
        'id', 'create_uid', 'create_date', 'name',
        'amphur_id', 'write_uid', 'write_date', 'name_eng'
    ];

    public $id;
    public $create_uid;
    public $create_date;
    public $name;
    public $amphur_id;
    public $write_uid;
    public $write_date;
    public $name_eng;

    public function __construct($args=[]) {
        $this->create_uid = $args['create_uid'] ?? '';
        $this->create_date = $args['create_date'] ?? '';
        $this->name = $args['name'] ?? '';
        $this->amphur_id = $args['amphur_id'] ?? '';
        $this->write_uid = $args['write_uid'] ?? '';
        $this->write_date = $args['write_date'] ?? '';
        $this->name_eng = $args['name_eng'] ?? '';
    }

    static public function find_by_amphur_id($amphur_id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE amphur_id='" . self::$database->escape_string($amphur_id) . "'";
        $obj_array = static::find_by_sql($sql);
        return is_array($obj_array) ? $obj_array : [];
    }
}
?>
