<?php

class UserType extends DatabaseObject {
    protected static $table_name = "user_types";
    protected static $db_columns = ['id', 'code', 'name_th', 'name_en'];

    public $id;
    public $code;
    public $name_th;
    public $name_en;

    public function __construct($args = []) {
        $this->code = $args['code'] ?? '';
        $this->name_th = $args['name_th'] ?? '';
        $this->name_en = $args['name_en'] ?? '';
    }
}
