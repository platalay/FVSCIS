<?php

class Session {

  private $user_id;
  public $username;
  public $role;
  public $user_picture;
  private $last_login;

  public const MAX_LOGIN_AGE = 60 * 60 * 24; // 1 วัน
  public const COOKIE_EXPIRY_DAYS = 30;
  public const COOKIE_NAME = 'remember_token';

  public function __construct() {
    session_start();
    $this->check_stored_login();
    if (!$this->is_logged_in()) {
      $this->check_remember_me_cookie(); // ✅ เพิ่มตรงนี้
    }
  }


  public function get_profile_image_url(): string {
        if (!empty($this->profile_image)) {
            return $this->profile_image;
        }
        // รูป default
        return '../img/undraw_profile.svg';
    }


  public function get_user_picture(): string {
      // ถ้าใน session มีค่าแล้ว และไม่ว่าง ใช้เลย
      if (!empty($this->user_picture)) {
          return $this->user_picture;
      }

      // ถ้าไม่มี ให้โหลดจาก DB ตาม role
      $user = null;
      if ($this->role === 'fisherman') {
          $user = Fisherman::find_by_id($this->user_id);
      } else {
          $user = Officer::find_by_id($this->user_id);
      }

      if ($user && method_exists($user, 'get_profile_image_url')) {
          $this->user_picture = $user->get_profile_image_url();
          $_SESSION['user_picture'] = $this->user_picture;
          return $this->user_picture;
      }

      // fallback รูป default
      return '../img/undraw_profile.svg';
  }


  public function login($user, $role, $picture = '', $remember_me = false) {
    if ($user && $role) {
      session_regenerate_id();
      if (empty($picture) && method_exists($user, 'get_profile_image_url')) {
          $picture = $user->get_profile_image_url();
      }

      $this->user_id       = $_SESSION['user_id'] = $user->id;
      $this->username      = $_SESSION['username'] = $user->username;
      $this->role          = $_SESSION['role'] = $role;
      $this->user_picture  = $_SESSION['user_picture'] = $picture;
      $this->last_login    = $_SESSION['last_login'] = time();

      if ($remember_me) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (self::COOKIE_EXPIRY_DAYS * 24 * 60 * 60));

        // บันทึก token ลงฐานข้อมูล
        $user->login_token = $token;
        $user->token_expiry = $expiry;
        $user->save();

        // สร้าง cookie
        setcookie(self::COOKIE_NAME, "{$role}|{$token}", time() + (self::COOKIE_EXPIRY_DAYS * 24 * 60 * 60), "/");
      }
    }
    return true;
  }

  public function set($key, $value) {
      $this->$key = $value;
      $_SESSION[$key] = $value;
  }

  public function logout() {
    // ลบ token จากฐานข้อมูล
    if ($this->is_logged_in()) {
      $user = null;
      if ($this->role === 'fisherman') {
        $user = Fisherman::find_by_id($this->user_id);
      } else {
        $user = Officer::find_by_id($this->user_id);
      }

      if ($user) {
        $user->login_token = null;
        $user->token_expiry = null;
        $user->save();
      }
    }

    // ลบ cookie
    setcookie(self::COOKIE_NAME, '', time() - 3600, "/");

    unset(
      $_SESSION['user_id'],
      $_SESSION['username'],
      $_SESSION['role'],
      $_SESSION['user_picture'],
      $_SESSION['last_login']
    );
    unset(
      $this->user_id,
      $this->username,
      $this->role,
      $this->user_picture,
      $this->last_login
    );
    return true;
  }

  private function check_stored_login() {
    if (isset($_SESSION['user_id'])) {
      $this->user_id       = $_SESSION['user_id'];
      $this->username      = $_SESSION['username'] ?? null;
      $this->role          = $_SESSION['role'] ?? null;
      $this->user_picture  = $_SESSION['user_picture'] ?? null;
      $this->last_login    = $_SESSION['last_login'] ?? null;
    }
  }

  private function check_remember_me_cookie() {
    if (!isset($_COOKIE[self::COOKIE_NAME])) return;

    list($role, $token) = explode('|', $_COOKIE[self::COOKIE_NAME], 2);

    if ($role === 'fisherman') {
      $user = Fisherman::find_by_token($token);
    } else {
      $user = Officer::find_by_token($token);
    }

    if ($user && strtotime($user->token_expiry) > time()) {
      $this->login($user, $role); // รีเซ็ต session ใหม่
    } else {
      // ลบ cookie ถ้า token ไม่ valid
      setcookie(self::COOKIE_NAME, '', time() - 3600, "/");
    }
  }

  private function last_login_is_recent() {
    return isset($this->last_login) &&
           (($this->last_login + self::MAX_LOGIN_AGE) >= time());
  }

  public function message($msg = "") {
    if (!empty($msg)) {
      $_SESSION['message'] = $msg;
      return true;
    } else {
      return $_SESSION['message'] ?? '';
    }
  }

  public function clear_message() {
    unset($_SESSION['message']);
  }

  public static function map_usertype_id_to_role($id) {
    switch ((int)$id) {
      case 1: return 'admin';
      case 2: return 'headquarter';
      case 3: return 'inspectofficer';
      case 4: return 'signer';
      default: return 'unknown';
    }
  }

  public static function redirect_by_role($role) {
    $folder = strtolower(trim($role));
    $destination = "{$folder}/index.php";
    redirect_to($destination);
  }

  public function is_logged_in() {
    return isset($this->user_id) && $this->last_login_is_recent();
  }

    public function require_role(array $allowed_roles) {
      if (!$this->is_logged_in()) {
          if ($this->is_ajax_request()) {
            http_response_code(401);
              header('Content-Type: application/json');
              echo json_encode([
                  'success' => false,
                  'message' => 'กรุณาเข้าสู่ระบบอีกครั้ง (Session หมดอายุ)'
              ]);
              exit;
          }
              redirect_to(WWW_ROOT . '/login.php');
      }

      if (!in_array($this->role, $allowed_roles)) {
          if ($this->is_ajax_request()) {
            http_response_code(403);
              header('Content-Type: application/json');
              echo json_encode([
                  'success' => false,
                  'message' => 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้'
              ]);
              exit;
          }
              redirect_to(WWW_ROOT . '/login.php');
      }
  }

  private function is_ajax_request() {
      return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }


  public function get_display_name() {
      $user = null;
      if ($this->role === 'fisherman') {
          $user = Fisherman::find_by_id($this->user_id);
      } else {
          $user = Officer::find_by_id($this->user_id);
      }
      return $user?->get_display_name() ?? $this->username;
  }
  
  public function user_id() {
      return $this->user_id;
  }
}

