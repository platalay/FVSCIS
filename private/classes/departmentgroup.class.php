<?php
class DepartmentGroup extends DatabaseObject {

    static protected $table_name = "department_groups";
    static protected $db_columns = ['id', 'name', 'note'];

    public $id;
    public $name;
    public $note;


    public function __construct($args=[]) 
    {
        $this->name = $args['name'] ?? '';
        $this->note = $args['note'] ?? '';
    }
   
}
?>