<?php

class FvSanitationCertificationOld extends DatabaseObject
{
    protected static $table_name = "fv_sanitation_certification_old";
    protected static $db_columns = [
        'id', 'vessel_name', 'ship_code', 'vessel_mark', 'license_number',
        'gear_type', 'owner_name', 'certificate_number',
        'request_date', 'signature_date', 'effective_date', 'expiration_date',
        'vessel_status', 'evaluation_agency', 'temporary_reason', 'responsible_unit', 'certificate_status', 'remark'
    ];

    public $id;
    public $vessel_name;
    public $ship_code;
    public $vessel_mark;
    public $license_number;
    public $gear_type;
    public $owner_name;
    public $certificate_number;
    public $request_date;
    public $signature_date;
    public $effective_date;
    public $expiration_date;
    public $vessel_status;
    public $evaluation_agency;
    public $temporary_reason;
    public $responsible_unit;
    public $certificate_status;
    public $remark;

    // ✅ หา record ด้วยทะเบียนเรือ
    public static function find_by_ship_code($ship_code)
    {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE ship_code = '" . self::$database->escape_string($ship_code) . "' ";
        $sql .= "LIMIT 1";
        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : null;
    }
}