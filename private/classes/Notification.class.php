<?php
class Notification extends DatabaseObject {
    protected static $table_name = "notifications";
    protected static $db_columns = [
        'id', 'user_id', 'user_role', 'message', 'notification_type',
        'is_read', 'action_taken', 'reference_type', 'reference_id',
        'inspection_log_id', 'created_at'
    ];

    public $id;
    public $user_id;
    public $user_role;
    public $message;
    public $notification_type = 'info';
    public $is_read = false;
    public $action_taken = false;
    public $reference_type;
    public $reference_id;
    public $inspection_log_id;
    public $created_at;

    public static function create_notification($user_id, $user_role, $message, $notification_type = 'info', $reference_type = null, $reference_id = null, $log_id = null) {
        $notification = new self;
        $notification->user_id = $user_id;
        $notification->user_role = $user_role;
        $notification->message = $message;
        $notification->notification_type = $notification_type;
        $notification->reference_type = $reference_type;
        $notification->reference_id = $reference_id;
        $notification->inspection_log_id = $log_id;
        $notification->created_at = date('Y-m-d H:i:s');
        return $notification->save();
    }

    public static function unread_count($user_id, $user_role) {
        $user_id = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $sql = "SELECT COUNT(*) FROM " . static::$table_name .
               " WHERE user_id = '" . $user_id . "' AND user_role = '" . $user_role . "' AND is_read = 0";
        return static::count_by_sql($sql);
    }

    public static function recent_notifications($user_id, $user_role, $limit = 10) {
        $user_id = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $limit = (int)$limit;
        $sql = "SELECT * FROM " . static::$table_name .
               " WHERE user_id = '" . $user_id . "' AND user_role = '" . $user_role . "' ORDER BY created_at DESC LIMIT " . $limit;
        return static::find_by_sql($sql);
    }

    public static function mark_all_as_read($user_id, $user_role) {
        $user_id = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $sql = "UPDATE " . static::$table_name .
               " SET is_read = 1 WHERE user_id = '" . $user_id . "' AND user_role = '" . $user_role . "'";
        return self::$database->query($sql);
    }

    public static function mark_action_taken($user_id, $user_role, $reference_type, $reference_id) {
        $user_id = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $reference_type = self::$database->escape_string($reference_type);
        $reference_id = self::$database->escape_string($reference_id);
        $sql = "UPDATE " . static::$table_name .
               " SET action_taken = 1 WHERE user_id = '" . $user_id .
               "' AND user_role = '" . $user_role .
               "' AND reference_type = '" . $reference_type .
               "' AND reference_id = '" . $reference_id . "'";
        return self::$database->query($sql);
    }

    // 🔽 เพิ่มฟังก์ชันเสริมเพื่อให้ class ใช้งานได้
    protected static function count_by_sql($sql) {
        $result = self::$database->query($sql);
        $row = $result->fetch_array();
        return array_shift($row);
    }

    public static function find_by_sql($sql) {
        $result = self::$database->query($sql);
        $object_array = [];
        while ($record = $result->fetch_assoc()) {
            $object_array[] = static::instantiate($record);
        }
        return $object_array;
    }

    protected static function instantiate($record) {
        $object = new static;
        foreach ($record as $property => $value) {
            if (property_exists($object, $property)) {
                $object->$property = $value;
            }
        }
        return $object;
    }

    public static function mark_related_as_read($reference_type, $reference_id) {
        $reference_type = self::$database->escape_string($reference_type);
        $reference_id   = self::$database->escape_string($reference_id);

        $sql = "UPDATE " . static::$table_name .
            " SET is_read = 1 
                WHERE reference_type = '" . $reference_type . "' 
                AND reference_id = '" . $reference_id . "' 
                AND is_read = 0";
        return self::$database->query($sql);
    }
}
?>

