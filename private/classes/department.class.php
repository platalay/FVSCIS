<?php
class Department extends DatabaseObject {

    static protected $table_name = "departments";
    static protected $db_columns = ['id', 'name', 'parent_department', 'address_no', 'building', 'alley', 'village_no', 'road', 'subdistrict', 'district', 'province', 'postal_code', 'phone', 'fax', 'email', 'note'];

    public $id;
    public $name;
    public $parent_department;
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
        }
    
    static public function find_by_department_group_id($department_group_id) {
            $sql = "SELECT * FROM " . static::$table_name . " ";
            $sql .= "WHERE parent_department='" . self::$database->escape_string($department_group_id) . "'";
            $obj_array = static::find_by_sql($sql);
            if(!empty($obj_array)) {
              return array_shift($obj_array);
            } else {
              return false;
            }
        } 
    
}
?>