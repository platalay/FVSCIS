<?php
class Notification extends DatabaseObject {
    protected static $table_name = "notifications";
    protected static $db_columns = [
        'id',
        'user_id',
        'user_role',
        'inspection_request_id',
        'action_id',
        'message',
        'notification_type',
        'is_read',
        'action_taken',
        'created_at', 'updated_at',
        'created_by', 'updated_by',
        'created_ip', 'updated_ip'
    ];

    public $id;
    public $user_id;
    public $user_role;
    // 0 = system / admin notification ที่ไม่ผูกกับคำขอ
    public $inspection_request_id = 0;
    public $action_id;
    public $message;
    public $notification_type = 'info';
    public $is_read = 0;        // ใช้ 0/1 ให้ตรงกับ tinyint
    public $action_taken = 0;   // 0/1

    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;

    /**
     * สร้าง notification ใหม่
     * - ถ้าเป็น noti admin ที่ไม่ผูกกับคำขอ ให้ส่ง $inspection_request_id = 0
     */
    public static function create_notification($user_id, $user_role, $inspection_request_id, $action_id, $message, $notification_type = 'info') {
        $notification = new self;

        $notification->user_id               = $user_id;
        $notification->user_role             = $user_role;                  // เช่น $session->role
        $notification->inspection_request_id = $inspection_request_id ?? 0; // ถ้า null ให้ใช้ 0
        $notification->action_id             = $action_id;
        $notification->message               = $message;
        $notification->notification_type     = $notification_type;
        $notification->is_read               = 0;
        $notification->action_taken          = 0;

        return $notification->save();
    }

    public static function unread_count($user_id, $user_role) {
        $user_id   = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);

        $sql = "SELECT COUNT(*) FROM " . static::$table_name .
               " WHERE user_id = '{$user_id}'
                 AND user_role = '{$user_role}'
                 AND is_read = 0";
        return static::count_by_sql($sql);
    }

    public static function recent_notifications($user_id, $user_role, $limit = 10) {
        $user_id   = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $limit     = (int)$limit;

        $sql = "SELECT * FROM " . static::$table_name .
               " WHERE user_id = '{$user_id}'
                 AND user_role = '{$user_role}'
               ORDER BY created_at DESC
               LIMIT {$limit}";
        return static::find_by_sql($sql);
    }

    public static function recent_unread_notifications($user_id, $user_role, $limit = 10) {
        $user_id   = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $limit     = (int)$limit;

        $sql = "SELECT * FROM " . static::$table_name .
               " WHERE user_id = '{$user_id}'
                 AND user_role = '{$user_role}'
                 AND is_read = 0
               ORDER BY created_at DESC
               LIMIT {$limit}";
        return static::find_by_sql($sql);
    }

    public static function mark_all_as_read($user_id, $user_role) {
        $user_id   = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);

        $sql = "UPDATE " . static::$table_name .
               " SET is_read = 1
                 WHERE user_id = '{$user_id}'
                   AND user_role = '{$user_role}'";
        return self::$database->query($sql);
    }

    /**
     * ใช้ในกรณีที่ notification ต้องมี action ตอบสนอง
     * เช่น ชาวประมงกด "ยืนยันวันตรวจ" แล้วให้ mark ว่าทำ action แล้ว
     * ถ้าไม่ใช้สามารถไม่เรียกฟังก์ชันนี้ได้
     */
    public static function mark_action_taken($user_id, $user_role, $inspection_request_id, $action_ids = null) {
        $user_id   = self::$database->escape_string($user_id);
        $user_role = self::$database->escape_string($user_role);
        $inspection_request_id = (int)$inspection_request_id;

        $sql = "UPDATE " . static::$table_name . "
                SET action_taken = 1,
                    is_read = 1
                WHERE user_id = '{$user_id}'
                AND user_role = '{$user_role}'
                AND inspection_request_id = {$inspection_request_id}";

        if (!is_null($action_ids)) {
            if (!is_array($action_ids)) {
                $action_ids = [(int)$action_ids];
            }
            $list = implode(',', array_map('intval', $action_ids));
            $sql .= " AND action_id IN ({$list})";
        }

        return self::$database->query($sql);
    }

    // ========= helper เดิมของคุณ =========

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
}
?>

