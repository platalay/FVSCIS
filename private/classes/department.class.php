<?php
class Department extends DatabaseObject {

    static protected $table_name = "departments";
    static protected $db_columns = ['id', 'name', 'parent_department', 'data_owner_id', 'address_no', 'building', 'alley', 'village_no', 'road', 'subdistrict', 'district', 'province', 'postal_code', 'phone', 'fax', 'email', 'note', 'data_owner_id'];

    public $id;
    public $name;
    public $parent_department;
    public $data_owner_id;
    public $address_no;
    public $building;
    public $alley;
    public $village_no;
    public $road;
    public $subdistrict;
    public $district;
    public $province;
    public $postal_code;
    public $phone;
    public $fax;
    public $email;
    public $note;
    

    public function __construct($args=[]) {
        $this->name = $args['name'] ?? '';
        $this->parent_department = $args['parent_department'] ?? '';
        $this->address_no = $args['address_no'] ?? '';
        $this->building = $args['building'] ?? '';
        $this->alley = $args['alley'] ?? '';
        $this->village_no = $args['village_no'] ?? '';
        $this->road = $args['road'] ?? '';
        $this->subdistrict = $args['subdistrict'] ?? '';
        $this->district = $args['district'] ?? '';
        $this->province = $args['province'] ?? '';
        $this->postal_code = $args['postal_code'] ?? '';
        $this->phone = $args['phone'] ?? '';
        $this->fax = $args['fax'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->note = $args['note'] ?? '';
        $this->data_owner_id = $args['data_owner_id'] ?? '';
        }
    
    static public function find_by_department_group_id($department_group_id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE parent_department='" . self::$database->escape_string($department_group_id) . "'";
        return static::find_by_sql($sql);
    } 
    
    static public function find_by_province($province) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE province ='" . self::$database->escape_string($province) . "'";
        return static::find_by_sql($sql);
    } 

    static public function get_department_group_id($department_id) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE id = '" . self::$database->escape_string($department_id) . "'";
        $result = static::find_by_sql($sql);
        return !empty($result) ? $result[0] : null;
    }

    /**
    * คืนชื่อหน่วยงานจาก id
    * @param int|string $id
    * @return string|null  ชื่อหน่วยงาน หรือ null ถ้าไม่พบ
    */
    static public function get_name_by_id($id) {
        if ($id === null || $id === '') { return null; }

        // ถ้ามีเมธอด find_by_id ในฐานคลาส ให้ใช้เพื่อความสม่ำเสมอ
        if (method_exists(get_called_class(), 'find_by_id')) {
            $dept = static::find_by_id((int)$id);
            return $dept ? $dept->name : null;
        }

        // กรณีไม่มี find_by_id ให้ fallback เป็น SQL ปกติ
        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE id = '" . self::$database->escape_string((string)$id) . "' ";
        $sql .= "LIMIT 1";
        $result = static::find_by_sql($sql);
        $dept = !empty($result) ? array_shift($result) : null;

        return $dept ? $dept->name : null;
    }


}
?>