<?php
require_once('../../private/initialize.php');
$session->require_role(['signer']);

require_once('../../private/fpdf/fpdf.php');
require_once('../../private/fpdi/src/autoload.php');
require_once('../../private/phpqrcode/qrlib.php');

use setasign\Fpdi\Fpdi;

// ===================== รับ token =====================
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('ไม่พบ token');
}

$form = InspectionFormStatus::find_by_token($token);
if (!$form) {
    die('ไม่พบเอกสารที่สอดคล้องกับ token นี้');
}

$request = InspectionRequest::find_by_id($form->request_id);
$applicant = InspectionApplicantInfo::find_by_request_id($form->request_id);
$officer = Officer::find_by_id($session->user_id());
if (!$request) {
    die('ไม่พบข้อมูลคำขอตรวจ');
}

// ===================== สร้าง PDF =====================
$pdf = new Fpdi();
$pdf->SetAutoPageBreak(false);

// ระบุไฟล์ template สร.3 ปรกติ
$source = '../../private/pdftemplate/FVS032.pdf';

// ✅ กันพลาด: เช็คไฟล์ก่อน
if (!file_exists($source)) {
    die('ไม่พบไฟล์ template: ' . $source);
}

// ✅ ต้องเรียก setSourceFile() ก่อน importPage() เสมอ
$pdf->setSourceFile($source);

// สร้าง URL สำหรับ QR
$url = 'https://fvscis.fisheries.go.th/verify_fvs032.php?token=' . urlencode($token);

// ✅ สร้าง QR code ชั่วคราว
$tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.png';
QRcode::png($url, $tempFile, QR_ECLEVEL_L, 4);

// ======================= เริ่ม หน้า 1 =======================
$pdf->AddPage('L', 'A4');

// ✅ import หลัง setSourceFile แล้วเท่านั้น
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl, 0, 0);

// ฟอนต์ไทย
$pdf->AddFont('THSarabunPSK','','THSarabunPSK.php');
$pdf->SetFont('THSarabunPSK','',16);

/**
 * NOTE:
 * ในโค้ดคุณมี $run และ $thai_year แต่ยังไม่ได้ประกาศค่า
 * ถ้าจะทดสอบให้ไม่ error ให้กำหนดค่าก่อน เช่น:
 */
$run =  " this is test";
$thai_year = " this is test";

$pdf->SetXY(60, 48);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($form->document_number ?? '')));

$pdf->SetXY(225, 48);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($form->document_number ?? '')));

$pdf->SetXY(130, 90);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($request->vessel_name ?? '')));

$pdf->SetXY(145, 97);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($request->ship_code ?? '')));

$pdf->SetXY(47, 104);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($request->owner_name ?? '')));

 
$moo = !empty($applicant->applicant_moo)
    ? 'หมู่ที่ ' . $applicant->applicant_moo
    : '';
$address1 = "เลขที่ ".$applicant->applicant_address_no. $moo. " ตำบล ".$applicant->applicant_tambon;
$address2 = " อำเภอ ".$applicant->applicant_amphoe . " จังหวัด ".$applicant->applicant_province ;
$address = $address1.$address2;
$pdf->SetXY(157, 104);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($address ?? '')));

$pdf->SetXY(207, 150);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($officer->full_name ?? '')));


$pdf->SetXY(205, 156);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($officer->position ?? '')));



$approve_at = thai_date($request->approved_at);
$effective_date = thai_date($request->effective_date);
$expire_at = thai_date($request->expire_at);

$pdf->SetXY(217, 162);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($approve_at ?? '')));

$pdf->SetXY(217, 168);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($effective_date ?? '')));

$pdf->SetXY(217, 174);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)($expire_at ?? '')));

$pdf->Image($tempFile, 20, 178, 25, 25);

// ======================= Output =======================
$pdf->Output('I', 'FailNotice.pdf');
exit;
