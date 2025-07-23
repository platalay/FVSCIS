<?php
// ตั้งค่าให้แสดง error (เฉพาะ dev)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// โหลด FPDF และ phpqrcode
require_once('../../private/initialize.php');
require_once '../../private/fpdf.php';
require_once '../../private/phpqrcode/qrlib.php';

// รับ token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('ไม่พบ token');
}

// ลิงก์ที่จะฝังใน QR code
//$url = 'https://yourdomain.com/verify.php?token=' . urlencode($token);
$url = BASE_URL . 'verify.php?token=' . urlencode($token); // ไปแก้ BASE_URL ใน db_credentials.php
// สร้าง QR code ลงไฟล์ชั่วคราว
$tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.png';
QRcode::png($url, $tempFile, QR_ECLEVEL_L, 4);

// สร้าง PDF
$pdf = new FPDF();
$pdf->AddPage();

// หัวเรื่อง
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'ใบรับรองผลตรวจเรือประมง'), 0, 1, 'C');

$pdf->Ln(10);

// ข้อความ token
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, iconv('UTF-8', 'TIS-620', "หมายเลขอ้างอิง: {$token}"));
$pdf->Ln(10);

// แปะ QR code
$pdf->Cell(0, 10, iconv('UTF-8', 'TIS-620', 'สแกนเพื่อตรวจสอบข้อมูล'), 0, 1, 'L');
$pdf->Image($tempFile, $pdf->GetX(), $pdf->GetY(), 40, 40);

// ลบไฟล์ QR ชั่วคราว
unlink($tempFile);

// ส่ง PDF ออก
$pdf->Output('I', 'certificate.pdf');
