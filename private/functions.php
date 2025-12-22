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


function thai_date_safe($value) {
    $value = trim((string)$value);
    if ($value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
        return '-';
    }

    // ตัดเวลาออกถ้ามี
    $value10 = substr($value, 0, 10);

    // ถ้าเป็น YYYY-MM-DD ส่งเข้า thai_date ได้ตรงๆ
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value10)) {
        return thai_date($value10);
    }

    // ถ้าเป็น DD/MM/YYYY แปลงก่อน
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value10)) {
        $dt = DateTime::createFromFormat('d/m/Y', $value10);
        if ($dt) return thai_date($dt->format('Y-m-d'));
    }

    // fallback
    $ts = strtotime($value);
    if ($ts) return thai_date(date('Y-m-d', $ts));

    return '-';
}


/**
 * แปลงวันที่/เวลา (YYYY-MM-DD หรือ DATETIME จาก MySQL) เป็นรูปแบบไทย (พ.ศ.)
 *
 * @param string $dateStr  วันที่จาก MySQL เช่น '2025-08-30' หรือ '2025-08-30 14:35:00'
 * @param array  $options  ['format' => 'long'|'short', 'show_day' => bool, 'show_time' => bool, 'null' => string]
 * @return string
 */
function thai_date(?string $dateStr, array $options = [])
{
    // กันค่าที่ไม่พร้อมใช้หรือไม่ใช่วันที่
    if (
        empty($dateStr) ||
        $dateStr === '0000-00-00' ||
        $dateStr === '0000-00-00 00:00:00' ||
        strtolower($dateStr) === 'null'
    ) {
        return $options['null'] ?? '-';
    }

    // ตั้งค่าพื้นฐาน (override ด้วย $options)
    $opt = array_merge([
        'format'       => 'short',  // short = ม.ค., long = มกราคม
        'show_day'     => false,    // แสดงวัน เช่น วันอังคาร
        'show_time'    => false,    // แสดงเวลา HH:MM น.
        'return_parts' => false,    // ⭐ คืนค่าเป็น array
        'null'         => '-',
    ], $options);

    // timezone
    date_default_timezone_set('Asia/Bangkok');

    // แปลงเป็น timestamp
    $ts = strtotime($dateStr);
    if ($ts === false) return $opt['null'];

    // ชื่อวัน + เดือนภาษาไทย
    $days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];

    $monthsShort = [
        1 => 'ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.',
        'ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'
    ];

    $monthsLong  = [
        1 => 'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
        'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'
    ];

    // ดึงข้อมูลวันที่
    $d = (int)date('j', $ts);
    $m = (int)date('n', $ts);
    $y = (int)date('Y', $ts) + 543;

    // เลือกชื่อเดือนตาม format
    $monthName = ($opt['format'] === 'short') ? $monthsShort[$m] : $monthsLong[$m];

    // ---------- ⭐ โหมดคืนค่าแบบโครงสร้าง ----------
    if ($opt['return_parts']) {
        $weekdayIndex = (int)date('w', $ts);

        return [
            // วันที่ที่คุณต้องการชัด ๆ
            'day'        => $d,
            'month'      => $m,
            'month_name' => $monthName,
            'year_be'    => $y,
            'year_ce'    => (int)date('Y', $ts),

            // วันในสัปดาห์
            'weekday'        => $days[$weekdayIndex],
            'weekday_index' => $weekdayIndex,

            // เวลา
            'time'      => $opt['show_time'] ? date('H:i', $ts) : null,
            'time_full' => $opt['show_time'] ? date('H:i:s', $ts) : null,

            // ข้อความพร้อมใช้
            'date_th' => "{$d} {$monthName} {$y}",
            'text'    => implode(' ', array_filter([
                $opt['show_day'] ? 'วัน' . $days[$weekdayIndex] : null,
                "{$d} {$monthName} {$y}",
                $opt['show_time'] ? 'เวลา ' . date('H:i', $ts) . ' น.' : null
            ])),
        ];
    }

    // ---------- โหมดเดิม (คืน string) ----------
    $parts = [];

    if ($opt['show_day']) {
        $parts[] = 'วัน' . $days[(int)date('w', $ts)];
    }

    $parts[] = "{$d} {$monthName} {$y}";

    if ($opt['show_time']) {
        $parts[] = date('H:i', $ts) . ' น.';
    }

    return implode(' ', $parts);
}


// -- ฟังก์ชั่นย่อย: คืนเฉพาะปี พ.ศ.
function thai_year(?string $dateStr, array $options = []): string
{
    if (
        empty($dateStr) ||
        $dateStr === '0000-00-00' ||
        $dateStr === '0000-00-00 00:00:00' ||
        strtolower($dateStr) === 'null'
    ) {
        return $options['null'] ?? '-';
    }

    $ts = strtotime($dateStr);
    if ($ts === false) {
        return $options['null'] ?? '-';
    }

    return (string)((int)date('Y', $ts) + 543);
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

function cleanup_social_tmp() {
  if (!empty($_SESSION['social_tmp'])) {
    $tmp = $_SESSION['social_tmp'];
    if (!isset($tmp['expires_at']) || time() > $tmp['expires_at']) {
      unset($_SESSION['social_tmp']); // หมดอายุ → ลบทิ้ง
    }
  }
}

function th_wordwrap($text, $width = 50) {
    $result = '';
    $len = mb_strlen($text, 'UTF-8');
    $line = '';

    for ($i=0; $i < $len; $i++) {
        $char = mb_substr($text, $i, 1, 'UTF-8');
        $line .= $char;

        if (mb_strwidth($line, 'UTF-8') >= $width) {
            $result .= $line . "\n";
            $line = '';
        }
    }

    if ($line !== '') {
        $result .= $line;
    }

    return $result;
}

function thaiWrapForCell($pdf, $text, $maxWidth) {
    $lines   = [];
    $current = '';

    $len = mb_strlen($text, 'UTF-8');

    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($text, $i, 1, 'UTF-8');

        // แยกตาม \n ที่มีอยู่เดิมด้วย
        if ($ch === "\n") {
            $lines[] = $current;
            $current = '';
            continue;
        }

        $test = $current . $ch;

        // วัดความกว้างข้อความปัจจุบัน
        $w = $pdf->GetStringWidth(iconv('UTF-8','cp874',$test));

        if ($w > $maxWidth) {
            // เกินแล้ว → ขึ้นบรรทัดใหม่
            $lines[] = $current;
            $current = $ch;
        } else {
            $current = $test;
        }
    }

    if ($current !== '') {
        $lines[] = $current;
    }

    return implode("\n", $lines);
}


?>
