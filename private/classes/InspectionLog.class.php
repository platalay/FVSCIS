<?php

class InspectionLog extends DatabaseObject {
    protected static $table_name = "inspection_logs";
    protected static $db_columns = [
        'id',
        'inspection_request_id',
        'action_id',
        'old_value',
        'new_value',
        'note',
        'performed_by',
        'performed_at',
        'target_department_id',
        'target_usertype_id',
        'target_officer_id'
    ];

    public $id;
    public $inspection_request_id;
    public $action_id;
    public $old_value;
    public $new_value;
    public $note;
    public $performed_by;
    public $performed_at;
    public $target_department_id;
    public $target_usertype_id;
    public $target_officer_id;
    public function action_description_th() {
        $action = LogAction::find_by_id($this->action_id);
        return $action ? $action->description_th : '';
    }

    public function performed_date_formatted() {
        return date("d/m/Y H:i", strtotime($this->performed_at));
    }

    public function who_should_see_this_log() {
        $parts = [];
        if ($this->target_officer_id) {
            $parts[] = "เจ้าหน้าที่รหัส {$this->target_officer_id}";
        }
        if ($this->target_usertype_id) {
            $parts[] = "user type {$this->target_usertype_id}";
        }
        if ($this->target_department_id) {
            $parts[] = "หน่วยงานรหัส {$this->target_department_id}";
        }
        return implode(', ', $parts);
    }
}
?>
