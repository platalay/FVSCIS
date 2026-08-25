<?php
class FvCertificateAttachment extends DatabaseObject {

    protected static $table_name = "fv_certificate_attachments";
    protected static $db_columns = [
        'id',
        'certificate_id',
        'attachment_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'created_by',
        'created_at'
    ];

    public $id;
    public $certificate_id;
    public $attachment_type;
    public $file_path;
    public $file_name;
    public $file_type;
    public $file_size;
    public $created_by;
    public $created_at;

    public function __construct($args = []) {
        $this->certificate_id = $args['certificate_id'] ?? null;
        $this->attachment_type      = $args['attachment_type'] ?? '';
        $this->file_path      = $args['file_path'] ?? '';
        $this->file_name      = $args['file_name'] ?? '';
        $this->file_type      = $args['file_type'] ?? '';
        $this->file_size      = $args['file_size'] ?? 0;
        $this->created_by     = $args['created_by'] ?? null;
        $this->created_at     = $args['created_at'] ?? date('Y-m-d H:i:s');
    }

    public static function find_by_certificate_id($certificate_id) {
        $cid = self::$database->escape_string($certificate_id);
        $sql = "SELECT * FROM " . static::$table_name . " WHERE certificate_id = '{$cid}' ORDER BY id DESC";
        return static::find_by_sql($sql);
    }

    public static function count_by_certificate_id($certificate_id) {
        $cid = self::$database->escape_string($certificate_id);
        $sql = "SELECT COUNT(*) AS c FROM " . static::$table_name . " WHERE certificate_id = '{$cid}'";
        $result = self::$database->query($sql);
        $row = $result->fetch_assoc();
        return (int)$row['c'];
    }

    /** สร้างชื่อไฟล์ให้ปลอดภัย */
    protected static function safe_filename($name) {
        $name = basename($name);
        // คงอักษรไทย/อังกฤษ/ตัวเลข .-_  ที่เหลือแทนด้วย _
        return preg_replace('/[^\p{L}\p{N}\.\-\_]+/u', '_', $name);
    }

    /** 🆕 บันทึกไฟล์แนบใหม่ไปที่ /uploads/certificationold/ */
    public static function create_from_upload($certificate_id, $file, $created_by, $attachment_type) {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return false;

        $upload_dir = '/uploads/certificationold/';   // << ใช้โฟลเดอร์ใหม่นี้
        $target_dir = PUBLIC_PATH . $upload_dir;
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $original   = $file['name'];
        $safe_name  = self::safe_filename($original);
        $unique     = uniqid('cert_', true) . '_' . $safe_name;

        $target_path   = $target_dir . $unique;
        $relative_path = $upload_dir . $unique;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $attachment = new self([
                'certificate_id' => $certificate_id,
                'file_path'      => $relative_path,
                'file_name'      => $original,                 // เก็บชื่อเดิมเพื่อแสดงผล
                'attachment_type'      => $attachment_type,
                'file_type'      => $file['type'] ?? '',
                'file_size'      => $file['size'] ?? 0,
                'created_by'     => $created_by
            ]);
            return $attachment->save();
        }
        return false;
    }

    public function delete_with_file() {
        if (!empty($this->file_path) && file_exists(PUBLIC_PATH . $this->file_path)) {
            @unlink(PUBLIC_PATH . $this->file_path);
        }
        return $this->delete();
    }

    // ตัด path ให้เหลือแค่ '/uploads/...' เสมอ (physical/relative path เท่านั้น ไม่ใช่ URL)
    protected static function normalize_rel_path(string $file_path): string
    {
        if ($file_path === '') return '';
        $p = str_replace('\\', '/', $file_path);
        $pos = strpos($p, '/uploads/');
        if ($pos !== false) {
            $p = substr($p, $pos);
        } else {
            $p = '/' . ltrim($p, '/');
        }
        return $p;
    }

    // Canonical public URL ของไฟล์แนบ Paper Certification (ใช้ WWW_ROOT ที่คำนวณจาก SCRIPT_NAME จริงของ request
    // ใน private/initialize.php + url_for() ที่มีอยู่แล้วใน private/functions.php แทนการ diff DOCUMENT_ROOT/PUBLIC_PATH เอง
    // เพื่อไม่ให้ '/public' หลุดเข้า URL และไม่ hardcode host/BASE_URL)
    public static function public_url(string $file_path): string
    {
        $rel = static::normalize_rel_path($file_path);
        return $rel !== '' ? url_for($rel) : '';
    }

    public static function delete_by_certificate_id($certificate_id) {
        $cid = self::$database->escape_string($certificate_id);
        $sql = "DELETE FROM " . static::$table_name . " WHERE certificate_id = '{$cid}'";
        return self::$database->query($sql);
    }
}

