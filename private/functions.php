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

?>
