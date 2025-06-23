<?php
class Officer extends DatabaseObject {

    static protected $table_name = 'officer';
    static protected $db_columns = [
        'id', 'username', 'password', 'full_name', 'position', 'email', 'google_id', 'facebook_id', 'line_id',
        'is_active', 'is_approved', 'approved_by', 'approved_at', 'login_token', 'token_expiry',
        'created_by', 'updated_by', 'created_at', 'updated_at', 'departments_id', 'usertype_id'
    ];

    public $id;
    public $username;
    public $password;
    public $full_name;
    public $position;
    public $email;
    public $google_id;
    public $facebook_id;
    public $line_id;
    public $is_active = 1;
    public $is_approved = 0;
    public $approved_by;
    public $approved_at;
    public $login_token;
    public $token_expiry;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;
    public $departments_id;
    public $usertype_id;

    public function __construct($args=[]) {
        $this->username = $args['username'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->full_name = $args['full_name'] ?? '';
        $this->position = $args['position'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->google_id = $args['google_id'] ?? NULL;
        $this->facebook_id = $args['facebook_id'] ?? NULL;
        $this->line_id = $args['line_id'] ?? NULL;
        $this->is_active = $args['is_active'] ?? 1;
        $this->is_approved = $args['is_approved'] ?? 0;
        $this->approved_by = $args['approved_by'] ?? NULL;
        $this->approved_at = $args['approved_at'] ?? NULL;
        $this->login_token = $args['login_token'] ?? NULL;
        $this->token_expiry = $args['token_expiry'] ?? NULL;
        $this->created_by = $args['created_by'] ?? NULL;
        $this->updated_by = $args['updated_by'] ?? NULL;
        $this->created_at = $args['created_at'] ?? NULL;
        $this->updated_at = $args['updated_at'] ?? NULL;
        $this->departments_id = $args['departments_id'] ?? NULL;
        $this->usertype_id = $args['usertype_id'] ?? NULL;
    }

    static public function find_by_email($email) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE email = '" . self::$database->escape_string($email) . "' ";
        $sql .= "LIMIT 1";
        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    static public function find_by_username($username) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE username = '" . self::$database->escape_string($username) . "' ";
        $sql .= "LIMIT 1";
        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    static public function find_by_token($token) {
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE login_token = '" . self::$database->escape_string($token) . "' ";
        $sql .= "AND token_expiry > NOW() ";
        $sql .= "LIMIT 1";
        $result_array = static::find_by_sql($sql);
        return !empty($result_array) ? array_shift($result_array) : false;
    }

    public static function alert_and_redirect($title, $message, $redirect_url) {
        $title_escaped = htmlspecialchars($title, ENT_QUOTES);
        $message_escaped = htmlspecialchars($message, ENT_QUOTES);
        $redirect_url_escaped = htmlspecialchars($redirect_url, ENT_QUOTES);

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="th">
        <head>
            <meta charset="UTF-8">
            <title>แจ้งเตือน</title>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'info',
                    title: '{$title_escaped}',
                    text: '{$message_escaped}',
                    confirmButtonText: 'ตกลง'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '{$redirect_url_escaped}';
                    }
                });
            </script>
        </body>
        </html>
        HTML;
        exit;
    }

    static public function find_or_create_by_facebook($fb_id, $full_name, $email = null) {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE facebook_id = '" . self::$database->escape_string($fb_id) . "' LIMIT 1";
        $result = static::find_by_sql($sql);
        if (!empty($result)) return $result[0];

        $new_user = new Officer([
            'facebook_id' => $fb_id,
            'full_name' => $full_name,
            'email' => $email,
            'username' => 'facebook_'.$fb_id,
            'departments_id' => 38,
            'is_active' => 1,
            'is_approved' => 0
        ]);
        $new_user->save();
        return $new_user;
    }

    static public function find_or_create_by_google($google_id, $full_name, $email = null) {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE google_id = '" . self::$database->escape_string($google_id) . "' LIMIT 1";
        $result = static::find_by_sql($sql);
        if (!empty($result)) return $result[0];

        $new_user = new Officer([
            'google_id' => $google_id,
            'full_name' => $full_name,
            'username' => 'google_'.$google_id,
            'email' => $email,
            'departments_id' => 38,
            'is_active' => 1,
            'is_approved' => 0
        ]);
        $new_user->save();
        return $new_user;
    }

    static public function find_or_create_by_line($line_id, $full_name, $email = null) {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE line_id = '" . self::$database->escape_string($line_id) . "' LIMIT 1";
        $result = static::find_by_sql($sql);
        if (!empty($result)) return $result[0];

        $new_user = new Officer([
            'line_id' => $line_id,
            'full_name' => $full_name,
            'email' => $email,
            'username' => 'line_'.$line_id,
            'departments_id' => 38,
            'is_active' => 1,
            'is_approved' => 0
        ]);
        $new_user->save();
        return $new_user;
    }

    public function set_hashed_password($plain_password) {
        $this->password = password_hash($plain_password, PASSWORD_DEFAULT);
    }

    public function verify_password($plain_password) {
        return password_verify($plain_password, $this->password);
    }

    public function generate_login_token($days_valid = 30) {
        $this->login_token = bin2hex(random_bytes(32));
        $this->token_expiry = date('Y-m-d H:i:s', time() + ($days_valid * 86400));
        return $this->save();
    }

    public static function verify_login_token($token) {
        $sql = "SELECT * FROM " . static::$table_name . " WHERE login_token = '" . self::$database->escape_string($token) . "' AND token_expiry >= NOW() LIMIT 1";
        $result = static::find_by_sql($sql);
        return !empty($result) ? array_shift($result) : false;
    }

    public function clear_login_token() {
        $this->login_token = NULL;
        $this->token_expiry = NULL;
        return $this->save();
    }

    public function get_display_name() {
        return !empty($this->full_name) ? $this->full_name : $this->username;
    }

    static public function find_by_department_id($department_id) {
        $department_id = self::$database->escape_string($department_id);
        $sql = "SELECT * FROM " . static::$table_name . " ";
        $sql .= "WHERE departments_id = '" . $department_id . "' ";
        $sql .= "AND is_active = 1 AND is_approved = 1 ";
        $sql .= "ORDER BY full_name ASC";
        return static::find_by_sql($sql);
    }
}
