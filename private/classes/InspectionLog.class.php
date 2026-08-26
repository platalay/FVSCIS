<?php

class InspectionLog extends DatabaseObject {
    protected static $table_name = "inspection_logs";
    protected static $db_columns = [
        'id',
        'inspection_request_id',
        'entity_type',
        'entity_id',
        'action_id',
        'note',
        'old_values',
        'new_values',
        'created_at', 'updated_at', 'created_by', 'actor_role', 'created_ip', 'updated_ip'
    ];

    public $id;
    public $inspection_request_id;
    public $entity_type;
    public $entity_id;
    public $action_id;
    public $note;
    public $old_values;
    public $new_values;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;
    public $actor_role;
    public $action_code;
    public $action_name;

    public static function create_manual_certificate_audit($action_code, $certificate_id, $note, $old_values = null, $new_values = null) {
        $action = LogAction::find_by_code($action_code);
        if (!$action) {
            return false;
        }

        $session = $GLOBALS['session'] ?? null;
        $log = new self();
        $log->inspection_request_id = 0;
        $log->entity_type = 'manual_certificate';
        $log->entity_id = (int)$certificate_id;
        $log->action_id = $action->id;
        $log->note = $note;
        $log->old_values = self::encode_audit_values($old_values);
        $log->new_values = self::encode_audit_values($new_values);
        $log->actor_role = ($session && isset($session->role)) ? $session->role : null;

        return $log->save();
    }

    public static function encode_audit_values($values) {
        if ($values === null || $values === []) {
            return null;
        }

        return json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function find_manual_certificate_audit($certificate_id) {
        $certificate_id = (int)$certificate_id;
        if ($certificate_id <= 0) {
            return [];
        }

        $sql = "SELECT il.*, la.code AS action_code, la.description_th AS action_name
                FROM " . static::$table_name . " il
                LEFT JOIN log_actions la ON il.action_id = la.id
                WHERE il.entity_type = 'manual_certificate'
                  AND il.entity_id = {$certificate_id}
                ORDER BY il.created_at ASC, il.id ASC";
        return static::find_by_sql($sql);
    }
    
}
?>
