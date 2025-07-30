<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../../private/initialize.php');
require_once('../../private/fpdf.php');
require_once('../../private/phpqrcode/qrlib.php');

// รับ token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('ไม่พบ token');
}

// ✅ ดึง InspectionFormStatus จาก token
$form = InspectionFormStatus::find_by_token($token);
if (!$form) {
    die('ไม่พบเอกสารที่สอดคล้องกับ token นี้');
}

// ✅ ดึง request ที่เกี่ยวข้อง
$request = InspectionRequest::find_by_id($form->request_id);
if ($request && empty($request->is_submitted)) {
    $request->is_submitted = 1;
    $request->submitted_at = date('Y-m-d H:i:s');
    $request->save();
}

// ✅ สร้างลิงก์ตรวจสอบ
$url = 'https://fvscis.fisheries.go.th/verify.php?token=' . urlencode($token);

// ✅ สร้าง QR code ชั่วคราว
$tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.png';
QRcode::png($url, $tempFile, QR_ECLEVEL_L, 4);

// ✅ เริ่มสร้าง PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('THSarabunPSK', '', 'THSarabunPSK.php');

// ✅ หัวเรื่อง
$pdf->SetFont('THSarabunPSK', '', 20);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'ใบรับรองผลตรวจเรือประมง'), 0, 1, 'C');
$pdf->Ln(10);

// ✅ ข้อมูลฟอร์ม
$pdf->SetFont('THSarabunPSK', '', 16);
$pdf->MultiCell(0, 10, iconv('UTF-8', 'cp874', "หมายเลขอ้างอิง: {$form->document_token}"));
$pdf->MultiCell(0, 10, iconv('UTF-8', 'cp874', "รหัสเรือ: {$request->ship_code}"));
$pdf->MultiCell(0, 10, iconv('UTF-8', 'cp874', "วันที่ตรวจ: {$request->inspect_date_start} ถึง {$request->inspect_date_end}"));

// ✅ สถานะผลตรวจ
$status_text = 'รอดำเนินการ';
switch ($request->status) {
    case InspectionRequest::STATUS_COMPLETED:
        $status_text = 'ผ่านสมบูรณ์';
        break;
    case InspectionRequest::STATUS_CONDITIONAL:
        $status_text = 'ผ่านแบบมีเงื่อนไข';
        break;
    case InspectionRequest::STATUS_FAILED:
        $status_text = 'ไม่ผ่าน';
        break;
}
$pdf->MultiCell(0, 10, iconv('UTF-8', 'cp874', "สถานะ: {$status_text}"));

$pdf->Ln(10);
$pdf->Cell(0, 10, iconv('UTF-8', 'cp874', 'สแกนเพื่อตรวจสอบข้อมูล'), 0, 1, 'L');
$pdf->Image($tempFile, $pdf->GetX(), $pdf->GetY(), 40, 40);

// ✅ ลบไฟล์ชั่วคราว
unlink($tempFile);

// ✅ แสดง PDF
$pdf->Output('I', 'certificate.pdf');
