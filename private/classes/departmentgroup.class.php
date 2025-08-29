<?php
class DepartmentGroup extends DatabaseObject {

    static protected $table_name = "department_groups";
    static protected $db_columns = ['id', 'name', 'note', 'officer_id', 'responsible_unit'];

    public $id;
    public $name;
    public $note;
    public $officer_id;
    public $responsible_unit;

    public function __construct($args=[]) 
    {
        $this->name = $args['name'] ?? '';
        $this->note = $args['note'] ?? '';
        $this->officer_id = $args['officer_id'] ?? null;
        $this->responsible_unit = $args['responsible_unit'] ?? null;
    }

    public static function find_one_by_officer_id($officer_id) {
        $officer_id = self::$database->escape_string($officer_id);
        $sql = "SELECT * FROM " . static::$table_name;
        $sql .= " WHERE officer_id = '{$officer_id}'";
        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : null;
    }

    /**
    * คืนชื่อกลุ่มหน่วยงานจาก id (ไม่พบคืน null)
    */
    public static function get_name_by_id(int|string $id): ?string {
        if ($id === null || $id === '') { 
            return null; 
        }

        // ถ้ามี find_by_id ในฐานคลาส ใช้ให้สม่ำเสมอ
        if (method_exists(get_called_class(), 'find_by_id')) {
            $grp = static::find_by_id((int)$id);
            return $grp ? $grp->name : null;
        }

        // กรณีไม่มี find_by_id ให้ fallback เป็น SQL
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE id = '" . self::$database->escape_string((string)$id) . "' ";
        $sql .= "LIMIT 1";
        $result = static::find_by_sql($sql);
        $grp = !empty($result) ? array_shift($result) : null;

        return $grp ? $grp->name : null;
    }

}
?>