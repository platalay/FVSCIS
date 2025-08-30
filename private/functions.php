<?php

function url_for($script_path) {
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return WWW_ROOT . $script_path;
}

function nav_active($url)
{
  if(strtok($_SERVER['REQUEST_URI'], '?')==url_for($url))
  {
    return "active";
  }else{
    return "";
  }
}

function u($string="") {
  return urlencode($string);
}

function raw_u($string="") {
  return rawurlencode($string);
}

function h($string="") {
  return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
  
}

function error_404() {
  header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
  exit();
}

function error_500() {
  header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
  exit();
}

function redirect_to($location) {
  header("Location: " . $location);
  exit;
}

function is_post_request() {
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function is_get_request() {
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// PHP on Windows does not have a money_format() function.
// This is a super-simple replacement.
if(!function_exists('money_format')) {
  function money_format($format, $number) {
    return '$' . number_format($number, 2);
  }
}

function sci_to_plain(string $val): string {
    $s = trim($val);
    if ($s === '') return $s;
    if (!preg_match('/^([+-]?)(\d+)(?:\.(\d+))?[eE]([+-]?\d+)$/', $s, $m)) {
        return $s; // ไม่ใช่ e-notation ก็ส่งคืนตามเดิม
    }
    $sign     = $m[1] ?? '';
    $intPart  = $m[2] ?? '0';
    $fracPart = $m[3] ?? '';
    $exp      = (int)($m[4] ?? 0);

    $digits         = $intPart . $fracPart;
    $decimalPos0    = strlen($intPart);
    $decimalPosNew  = $decimalPos0 + $exp;

    if ($decimalPosNew <= 0) {
        $res = '0.' . str_repeat('0', -$decimalPosNew) . $digits;
    } elseif ($decimalPosNew >= strlen($digits)) {
        $res = $digits . str_repeat('0', $decimalPosNew - strlen($digits));
    } else {
        $res = substr($digits, 0, $decimalPosNew) . '.' . substr($digits, $decimalPosNew);
    }

    // ตัด . และศูนย์ท้ายถ้าเป็นจำนวนเต็ม
    $res = preg_replace('/\.?0+$/', '', $res);
    return $sign . $res;
}


/**
 * แปลงวันที่/เวลา (YYYY-MM-DD หรือ DATETIME จาก MySQL) เป็นรูปแบบไทย (พ.ศ.)
 *
 * @param string $dateStr  วันที่จาก MySQL เช่น '2025-08-30' หรือ '2025-08-30 14:35:00'
 * @param array  $options  ['format' => 'long'|'short', 'show_day' => bool, 'show_time' => bool, 'null' => string]
 * @return string
 */
function thai_date(string $dateStr, array $options = []): string
{
    // กันค่าไม่พร้อมใช้
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') {
        return $options['null'] ?? '-';
    }

    // ตั้งค่าเริ่มต้น
    $opt = array_merge([
        'format'    => 'short',   // 'long' = มกราคม, 'short' = ม.ค.
        'show_day'  => false,    // แสดงวัน จันทร์/อังคาร/...
        'show_time' => false,    // แสดงเวลา HH:MM น.
        'null'      => '-',
    ], $options);

    // ให้แน่ใจว่าใช้เวลาไทย
    date_default_timezone_set('Asia/Bangkok');

    $ts = strtotime($dateStr);
    if ($ts === false) return $opt['null'];

    $days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    $monthsShort = [1=>'ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $monthsLong  = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];

    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = (int)date('Y', $ts) + 543;

    $monthName = ($opt['format'] === 'short') ? $monthsShort[$m] : $monthsLong[$m];

    $parts = [];
    if ($opt['show_day']) {
        $parts[] = 'วัน'.$days[(int)date('w', $ts)];
    }
    $parts[] = "{$d} {$monthName} {$y}";

    if ($opt['show_time']) {
        $parts[] = date('H:i', $ts) . ' น.';
    }

    return implode(' ', $parts);
}

// --- ฟังก์ชันย่อย: คืนเฉพาะชื่อวันไทย (เช่น "วันเสาร์")
function thai_day(string $dateStr): string
{
    if (empty($dateStr) || $dateStr === '0000-00-00' || $dateStr === '0000-00-00 00:00:00') return '-';
    date_default_timezone_set('Asia/Bangkok');
    $ts = strtotime($dateStr);
    if ($ts === false) return '-';
    $days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
    return 'วัน' . $days[(int)date('w', $ts)];
}


?>
