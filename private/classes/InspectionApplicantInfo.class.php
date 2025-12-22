<?php

class InspectionApplicantInfo extends DatabaseObject
{
    protected static $table_name = "inspection_applicant_info";

    protected static $db_columns = [
        'id',
        'request_id',

        'applicant_name',
        'applicant_age',
        'applicant_nationality',
        'applicant_phone',

        'applicant_address_no',
        'applicant_moo',

        'applicant_province_id',
        'applicant_province',
        'applicant_amphoe_id',
        'applicant_amphoe',
        'applicant_tambon_id',
        'applicant_tambon',

        'is_juristic',

        'juristic_name',
        'juristic_office',
        'juristic_address_no',
        'juristic_moo',

        'juristic_province_id',
        'juristic_province',
        'juristic_amphoe_id',
        'juristic_amphoe',
        'juristic_tambon_id',
        'juristic_tambon',

        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'created_ip',
        'updated_ip',

        'form1_doc_number',
        'document_token',
        'form1_locked',
        'form1_locked_at',
        'form1_locked_by',
        'written_at',
        'written_date'
    ];

    public $id;
    public $request_id;

    public $applicant_name;
    public $applicant_age;
    public $applicant_nationality;
    public $applicant_phone;

    public $applicant_address_no;
    public $applicant_moo;

    public $applicant_province_id;
    public $applicant_province;
    public $applicant_amphoe_id;
    public $applicant_amphoe;
    public $applicant_tambon_id;
    public $applicant_tambon;

    public $is_juristic;

    public $juristic_name;
    public $juristic_office;
    public $juristic_address_no;
    public $juristic_moo;

    public $juristic_province_id;
    public $juristic_province;
    public $juristic_amphoe_id;
    public $juristic_amphoe;
    public $juristic_tambon_id;
    public $juristic_tambon;

    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    public $created_ip;
    public $updated_ip;

    public $form1_doc_number;
    public $document_token;
    public $form1_locked;
    public $form1_locked_at;
    public $form1_locked_by;
    public $written_at;
    public $written_date;

    public function __construct($args = [])
    {
        $this->request_id = $args['request_id'] ?? '';

        $this->applicant_name        = $args['applicant_name']        ?? '';
        $this->applicant_age         = $args['applicant_age']         ?? null;
        $this->applicant_nationality = $args['applicant_nationality'] ?? '';
        $this->applicant_phone       = $args['applicant_phone']       ?? '';

        $this->applicant_address_no  = $args['applicant_address_no']  ?? '';
        $this->applicant_moo         = $args['applicant_moo']         ?? '';

        $this->applicant_province_id = $args['applicant_province_id'] ?? null;
        $this->applicant_province    = $args['applicant_province']    ?? '';
        $this->applicant_amphoe_id   = $args['applicant_amphoe_id']   ?? null;
        $this->applicant_amphoe      = $args['applicant_amphoe']      ?? '';
        $this->applicant_tambon_id   = $args['applicant_tambon_id']   ?? null;
        $this->applicant_tambon      = $args['applicant_tambon']      ?? '';

        $this->is_juristic  = $args['is_juristic'] ?? 0;

        $this->juristic_name        = $args['juristic_name']        ?? '';
        $this->juristic_office      = $args['juristic_office']      ?? '';
        $this->juristic_address_no  = $args['juristic_address_no']  ?? '';
        $this->juristic_moo         = $args['juristic_moo']         ?? '';

        $this->juristic_province_id = $args['juristic_province_id'] ?? null;
        $this->juristic_province    = $args['juristic_province']    ?? '';
        $this->juristic_amphoe_id   = $args['juristic_amphoe_id']   ?? null;
        $this->juristic_amphoe      = $args['juristic_amphoe']      ?? '';
        $this->juristic_tambon_id   = $args['juristic_tambon_id']   ?? null;
        $this->juristic_tambon      = $args['juristic_tambon']      ?? '';

        $this->created_at  = $args['created_at']  ?? '';
        $this->updated_at  = $args['updated_at']  ?? '';
        $this->created_by  = $args['created_by']  ?? null;
        $this->updated_by  = $args['updated_by']  ?? null;
        $this->created_ip  = $args['created_ip']  ?? '';
        $this->updated_ip  = $args['updated_ip']  ?? '';
        
        $this->form1_doc_number  = $args['form1_doc_number']  ?? '';
        $this->document_token  = $args['document_token']  ?? '';
        $this->form1_locked  = $args['form1_locked']  ?? '';
        $this->form1_locked_at  = $args['form1_locked_at']  ?? '';
        $this->form1_locked_by  = $args['form1_locked_by']  ?? '';

        $this->written_at  = $args['written_at']  ?? '';
        $this->written_date  = $args['written_date']  ?? '';
    }

    public static function generate_uuid_v4() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, // เวอร์ชัน 4
            mt_rand(0, 0x3fff) | 0x8000, // variant
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    protected function validate()
    {
        $this->errors = [];

        if (is_blank($this->request_id)) {
            $this->errors[] = "ไม่พบ request_id";
        }

        // ถ้าเป็นบุคคลธรรมดา ต้องมีชื่อผู้ยื่น
        if ((int)$this->is_juristic === 0 && is_blank($this->applicant_name)) {
            $this->errors[] = "กรุณาระบุชื่อผู้ยื่นคำขอ";
        }

        // ถ้าเป็นนิติบุคคล ต้องมีชื่อนิติบุคคล
        if ((int)$this->is_juristic === 1 && is_blank($this->juristic_name)) {
            $this->errors[] = "กรุณาระบุชื่อนิติบุคคล";
        }

        return $this->errors;
    }

    public static function find_by_request_id($request_id)
    {
        $request_id = self::$database->escape_string($request_id);

        $sql  = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE request_id = '{$request_id}' ";
        $sql .= "LIMIT 1";

        $obj_array = static::find_by_sql($sql);
        if (!empty($obj_array)) {
            return array_shift($obj_array);
        }
        return null;
    }

    public static function find_or_initialize_by_request_id($request_id)
    {
        $record = static::find_by_request_id($request_id);
        if ($record) {
            return $record;
        }

        return new static(['request_id' => $request_id]);
    }

    //ลบ record โดย request_id
    public static function delete_by_request_id($request_id)
    {
        $request_id = self::$database->escape_string($request_id);

        $sql = "DELETE FROM " . static::$table_name . " ";
        $sql .= "WHERE request_id = '{$request_id}' ";
        $sql .= "LIMIT 1";

        return self::$database->query($sql);
    }

    public static function find_by_form1_doc_number(string $docnumber)
        {
            global $database;
            $docnumber = trim($docnumber);
            $sql = "SELECT * FROM " . static::$table_name . " ";
            $sql .= "WHERE form1_doc_number = '" . $database->escape_string($docnumber) . "' ";
            $sql .= "LIMIT 1";
            $obj_array = static::find_by_sql($sql);
            return !empty($obj_array) ? array_shift($obj_array) : null;
        }

        public static function extract_doc_running_number(?string $docnumber): ?int
        {
            $docnumber = trim((string)$docnumber);
            if ($docnumber === '') {
                return null;
            }

            // จับเลขท้ายสุดของสตริง (เช่น 00002)
            if (preg_match('/(\d+)\s*$/', $docnumber, $m)) {
                return (int)$m[1]; // (int) จะตัด 0 นำหน้าให้เอง
            }

            return null;
        }

        /**
        * convenience: ดึงเลขจาก form1_doc_number ของ record นี้
        */
        public function form1_running_number(): ?int
        {
            return self::extract_doc_running_number($this->form1_doc_number);
        }

}
