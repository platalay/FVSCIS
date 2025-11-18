<?php

class InspectionLog extends DatabaseObject {
    protected static $table_name = "inspection_logs";
    protected static $db_columns = [
        'id',
        'inspection_request_id',
        'action_id',
        'note',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'created_ip', 'updated_ip'
    ];

    public $id;
    public $inspection_request_id;
    public $action_id;
    public $note;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;
    
}
?>
