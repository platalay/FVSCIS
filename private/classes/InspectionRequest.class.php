<?php

class InspectionRequest extends DatabaseObject {
    protected static $table_name = "inspection_requests";
    protected static $db_columns = [
        'id',
        'ship_code',
        'contact_phone',
        'department_id',
        'port_provinse_id',
        'port_amphur_id',
        'port_tambon_id',
        'port_license_no',
        'inspect_date_start',
        'inspect_date_end',
        'confirm_agreement',
        'created_by',
        'created_at'
    ];

    public $id;
    public $ship_code;
    public $contact_phone;
    public $department_id;
    public $port_province_id;
    public $port_amphur_id;
    public $port_tambon_id;
    public $port_license_no;
    public $inspect_date_start;
    public $inspect_date_end;
    public $confirm_agreement = false;
    public $created_by;
    public $created_at;

    // Optional: เพิ่ม method แปลงวันที่/แสดงชื่อจังหวัดได้ภายหลัง
}
?>
