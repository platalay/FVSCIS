<?php
require_once('../../private/initialize.php');
require_once('../../private/phpqrcode/qrlib.php');


$id = $_GET['id'] ?? null;
if (!$id) {
    die('ไม่พบ request_id');
}

// ดึงข้อมูล
$request = InspectionRequest::find_by_id($id);
$form = InspectionFormStatus::find_by_request_id($id);
$elicense = Elicense::find_by_ship_code($el_db, $request->ship_code ?? '');

if (!$request || !$form || !$elicense) {
    die('ไม่พบข้อมูลครบถ้วน');
}

// ข้อมูลที่ต้องใช้
$document_number = $form->document_number ?? '-';
$vessel_name = $elicense->vessel_name ?? '-';
$fishing_area = $elicense->fishing_area ?? '-';
$expire_at = $request->expire_at ?? '-';

// สร้าง qr ครับ



// สร้าง URL ปลายทางของ QR
$token = $form->document_token;
$qr_url = 'https://fvscis.fisheries.go.th/verify.php?token=' . urlencode($token);

// สร้าง QR ลง memory
ob_start();
QRcode::png($qr_url, null, QR_ECLEVEL_L, 4);
$imageString = ob_get_contents();
ob_end_clean();

// สร้าง image จาก string
$qr_image = imagecreatefromstring($imageString);

// เอาไปแปะลงภาพหลัก
$qr_width = 100;
$qr_height = 100;
$qr_x = 470;  // ปรับตามตำแหน่งที่เหมาะ
$qr_y = 375;


// โหลดภาพพื้นหลัง
$bgPath = '../img/FVS3.png'; // ใส่ path ภาพจริง
$fontPath = '../../private/font/TH Sarabun New Bold.ttf'; // ใส่ path ฟอนต์จริง
$stamp = imagecreatefrompng('../img/check.png');

// ขนาดของภาพตรา
$stamp_width = 20;
$stamp_height = 20;

// ตำแหน่งที่ต้องการแปะ (เช่น มุมล่างขวา)
// ตรวจสอบพื้นที่ทำการประมงแล้วตั้งค่า x, y
if (strtolower(trim($fishing_area)) === 'andaman') {
    $x = 320;
    $y = 335;
} elseif (strtolower(trim($fishing_area)) === 'gulf') {
    $x = 203;
    $y = 335;
} else {
    // ค่า default เผื่อไม่ตรง
    $x = 250;
    $y = 335;
}

if (!file_exists($bgPath) || !file_exists($fontPath)) {
    die('ไม่พบไฟล์พื้นหลังหรือฟอนต์');
}

$im = imagecreatefrompng($bgPath);
$im = imagescale($im, 576, 480); // 6x5 นิ้ว @96DPI

$black = imagecolorallocate($im, 0, 0, 0);
$fontSize = 18;
$fontSizebig = 26;
// เขียนข้อความลงภาพ
imagettftext($im, $fontSize, 0, 300, 205, $black, $fontPath, $document_number);
$locale = 'th_TH';
$formatter = new IntlDateFormatter(
    $locale,
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'Asia/Bangkok',
    IntlDateFormatter::GREGORIAN,
    "d MMMM yyyy"
);

// แปลงวันที่ให้เป็นภาษาไทย
$formattedExpireDate = $formatter->format(strtotime($expire_at));

// เขียนลงภาพ
imagettftext($im, $fontSizebig, 0, 220, 250, $black, $fontPath, $formattedExpireDate);
imagettftext($im, $fontSizebig, 0, 220, 290, $black, $fontPath, $vessel_name);
imagettftext($im, $fontSizebig, 0, 220, 325, $black, $fontPath, $request->ship_code);
imagecopyresampled($im, $stamp, $x, $y, 0, 0, $stamp_width, $stamp_height, imagesx($stamp), imagesy($stamp));
imagecopyresampled($im, $qr_image, $qr_x, $qr_y, 0, 0, $qr_width, $qr_height, imagesx($qr_image), imagesy($qr_image));

// แสดงภาพ
header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
?>