<?php
class InspectionAttachment extends DatabaseObject {

    protected static $table_name = "inspection_attachments";
    protected static $db_columns = [
        'id',
        'request_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'created_by',
        'created_at'
    ];

    public $id;
    public $request_id;
    public $file_path;
    public $file_name;
    public $file_type;
    public $file_size;
    public $created_by;
    public $created_at;

    public function __construct($args = []) {
        $this->request_id = $args['request_id'] ?? null;
        $this->file_path  = $args['file_path'] ?? '';
        $this->file_name  = $args['file_name'] ?? '';
        $this->file_type  = $args['file_type'] ?? null;
        $this->file_size  = $args['file_size'] ?? null;
        $this->created_by = $args['created_by'] ?? null;
        $this->created_at = $args['created_at'] ?? date('Y-m-d H:i:s');
    }

    /** 
     * 🔹 หาไฟล์แนบทั้งหมดของคำขอหนึ่งๆ
     */
    public static function find_by_request_id($request_id) {
        $sql  = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE request_id = '" . self::$database->escape_string($request_id) . "'";
        $sql .= " ORDER BY id ASC";
        return static::find_by_sql($sql);
    }

    /** 
     * 🔹 ลบไฟล์แนบทั้งหมดของคำขอ (ใช้ก่อนลบคำขอหลัก)
     */
    public static function delete_by_request_id($request_id) {
        $sql  = "DELETE FROM " . static::$table_name;
        $sql .= " WHERE request_id = '" . self::$database->escape_string($request_id) . "'";
        return self::$database->query($sql);
    }

    /**
     * 🔹 ลบไฟล์แนบเฉพาะรายการ (ทั้ง record และไฟล์จริง)
     */
    public function delete_with_file() {
        // ลบไฟล์จริงในโฟลเดอร์ถ้ามี
        if(!empty($this->file_path) && file_exists(PUBLIC_PATH . $this->file_path)) {
            @unlink(PUBLIC_PATH . $this->file_path);
        }
        return $this->delete();
    }

    /**
     * 🔹 บันทึกไฟล์ใหม่ (ใช้หลังอัปโหลด)
     */
    public static function create_from_upload($request_id, $file, $created_by) {
        if(empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $upload_dir  = '/uploads/inspection/';
        $target_dir  = PUBLIC_PATH . $upload_dir;
        if(!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $unique_name = uniqid('att_') . '_' . basename($file['name']);
        $target_path = $target_dir . $unique_name;
        $relative_path = $upload_dir . $unique_name;

        if(move_uploaded_file($file['tmp_name'], $target_path)) {
            $attachment = new self([
                'request_id' => $request_id,
                'file_path'  => $relative_path,
                'file_name'  => $file['name'],
                'file_type'  => $file['type'] ?? null,
                'file_size'  => $file['size'] ?? null,
                'created_by' => $created_by
            ]);
            return $attachment->save();
        }
        return false;
    }

     public static function count_by_request_id($request_id) {
        $rid = self::$database->escape_string($request_id);
        $sql = "SELECT COUNT(*) AS c FROM " . static::$table_name . " WHERE request_id = '{$rid}'";
        $res = self::$database->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            return (int)$row['c'];
        }
        return 0;
    }
}
?>
