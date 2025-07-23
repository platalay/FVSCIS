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
}
?>