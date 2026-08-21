<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']); // หรือ ['inspectofficer'] ตามสิทธิ์ที่ใช้จริง

require_once('../../private/fpdf/fpdf.php');
require_once('../../private/fpdi/src/autoload.php');

// ====== phpqrcode ใช้สำหรับสร้าง QR ======
require_once('../../private/phpqrcode/qrlib.php'); // ปรับ path ตามจริงถ้าจำเป็น

use setasign\Fpdi\Fpdi;

// ===================== ตรวจ FPDI =====================
if (!class_exists('setasign\Fpdi\Fpdi')) {
    die("FPDI not loaded");
}

// ===================== รับ request_id =====================
$request_id = $_GET['request_id'] ?? '';
$request_id = trim($request_id);

if ($request_id === '') {
    die('ไม่พบ request_id');
}

// ===================== ดึงข้อมูลจากฐานข้อมูล =====================

// คำขอตรวจ
$request = InspectionRequest::find_by_id($request_id);
if (!$request) {
    die('ไม่พบข้อมูลคำขอตรวจ');
}
$type = $request->inspection_form_type;

// ผู้ยื่นคำขอ สร.1
$applicant = InspectionApplicantInfo::find_by_request_id($request_id);
if (!$applicant) {
    die('ไม่พบข้อมูลผู้ยื่นคำขอสำหรับแบบ สร.1');
}

// ===================== เตรียมตัวแปรที่ต้องใช้บนฟอร์ม =====================

// เขียนที่ / วันที่
$writtenAt   = $applicant->written_at   ?? '';
$writtenDate = $applicant->written_date ?? null; // Y-m-d

// ========================== แตกวันที่แบบละเอียด ==========================
$writtenDay      = '';
$writtenMonthNum = '';
$writtenMonthTh  = '';
$writtenYearAD   = '';
$writtenYearBE   = '';

if (!empty($writtenDate)) {

    // แปลง string -> DateTime
    $dt = DateTime::createFromFormat('Y-m-d', $writtenDate);

    if ($dt) {
        // วันที่
        $writtenDay = (int)$dt->format('j'); // 1–31

        // เดือนตัวเลข
        $writtenMonthNum = $dt->format('m'); // 01–12

        // เดือนชื่อไทย
        $monthsTh = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $writtenMonthTh = $monthsTh[(int)$dt->format('n')];

        // ปี ค.ศ. + ปี พ.ศ.
        $writtenYearAD = (int)$dt->format('Y');
        $writtenYearBE = $writtenYearAD + 543;
    }
}

if (!empty($writtenDate)) {
    // ใช้ฟังก์ชัน thai_date ที่คุณมีอยู่แล้ว
    $writtenDateText = thai_date($writtenDate, ['format' => 'j F พ.ศ. Y']);
} else {
    $writtenDateText = '';
}

// ประเภทผู้ยื่น: 0 = บุคคลธรรมดา, 1 = นิติบุคคล
$isJuristic = (int)($applicant->is_juristic ?? 0);

// บุคคลธรรมดา / ผู้แทน
$applicantName        = $applicant->applicant_name        ?? '';
$applicantAge         = $applicant->applicant_age         ?? '';
$applicantNationality = $applicant->applicant_nationality ?? '';
$applicantAddressNo   = $applicant->applicant_address_no  ?? '';
$applicantMoo         = $applicant->applicant_moo         ?? '';
$applicantTambon      = $applicant->applicant_tambon      ?? '';
$applicantAmphoe      = $applicant->applicant_amphoe      ?? '';
$applicantProvince    = $applicant->applicant_province    ?? '';
$applicantPhone       = $applicant->applicant_phone       ?? '';

// นิติบุคคล (ถ้ามี)
$juristicName        = $applicant->juristic_name        ?? '';
$juristicOffice      = $applicant->juristic_office      ?? '';
$juristicAddressNo   = $applicant->juristic_address_no  ?? '';
$juristicMoo         = $applicant->juristic_moo         ?? '';
$juristicTambon      = $applicant->juristic_tambon      ?? '';
$juristicAmphoe      = $applicant->juristic_amphoe      ?? '';
$juristicProvince    = $applicant->juristic_province    ?? '';

// เลขเอกสาร สร.1 ที่ gen ไว้แล้ว (เช่น efvscis-2025-SR1-00001)
$form1DocNumber = $applicant->document_token ?? '';
$Doc_Number = $applicant->form1_doc_number ?? '';
// ===================== แตกเลขเอกสาร → ปี / ลำดับ =====================
// รูปแบบ: efvscis-YYYY-TYPE-RUNNN
list($running, $year) =   DocumentCounter::parse_running_year_from_code($Doc_Number);

// ===================== ข้อมูลเรือ (ถ้าต้องการใช้) =====================
$shipCode     = $request->ship_code    ?? '';
$vesselName   = $request->vessel_name  ?? '';
$vesselRegNo  = $request->vessel_regno ?? '';
$portName     = $request->port_name    ?? '';
$inspectedAt  = $request->inspect_date ?? ''; // Y-m-d ถ้ามี

// ===================== ข้อความสำหรับทำ QR =====================
// ให้ยิงไปที่: https://fishlanding.fisheries.go.th/fvscis?docnumber=form1_doc_number

$qrText = '';
if (!empty($form1DocNumber)) {
    $qrText = 'https://fishlanding.fisheries.go.th/fvscis?docnumber='
            . urlencode($form1DocNumber);
} else {
    // เผื่อกรณีไม่มีเลขเอกสาร (ป้องกัน error)
    $qrText = 'https://fishlanding.fisheries.go.th/fvscis';
}

// ===================== สร้าง QR Code เป็นไฟล์ชั่วคราว (temp file) =====================

$tempQrFile = null;
if (!empty($qrText)) {
    // สร้างชื่อไฟล์ชั่วคราวใน temp dir ของระบบ
    $tempQrFile = tempnam(sys_get_temp_dir(), 'qr_');
    // phpqrcode ต้องการนามสกุล .png
    $realQrFile = $tempQrFile . '.png';
    // เปลี่ยนชื่อไฟล์ temp เดิมให้มี .png
    rename($tempQrFile, $realQrFile);
    $tempQrFile = $realQrFile;

    // สร้างไฟล์ QR PNG
    QRcode::png($qrText, $tempQrFile, QR_ECLEVEL_L, 3, 1);
}

// ===================== เริ่มสร้าง PDF ด้วย FPDI =====================

$pdf = new FPDI();
$pdf->SetAutoPageBreak(false);

// ระบุไฟล์ template ของแบบ สร.1
$map = [
    1 => '../../private/pdftemplate/FVS1.pdf',
    2 => '../../private/pdftemplate/FVS1EU.pdf'
];

$source = $map[$type] ?? $map[1]; // default = type 1
$pageCount = $pdf->setSourceFile($source);

// โหลดฟอนต์ TH Sarabun
$pdf->AddFont('THSarabunPSK','','THSarabunPSK.php');
$pdf->SetFont('THSarabunPSK','',16);

// ======================= หน้า 1 =======================
$pdf->AddPage();
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl, 0, 0);

// 1) เลขที่ / ลำดับ / ปี
$pdf->SetXY(35, 20);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $running), 0, 0, 'L');

$pdf->SetXY(50, 20);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $year), 0, 0, 'L');

// 2) เขียนที่ / วันที่
$pdf->SetXY(137, 37);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $writtenAt), 0, 0, 'L');

$pdf->SetXY(135, 45);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $writtenDay), 0, 0, 'L');

$pdf->SetXY(153, 45);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $writtenMonthTh), 0, 0, 'L');

$pdf->SetXY(184, 45);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $writtenYearBE), 0, 0, 'L');

// 3) ข้อมูลบุคคลธรรมดา
$pdf->SetXY(65, 54);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantName), 0, 0, 'L');

if ($type == 1) {
    $pdf->SetXY(135, 54);
    $pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantAge), 0, 0, 'L');
}

$pdf->SetXY(165, 54);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantNationality), 0, 0, 'L');

$pdf->SetXY(57, 62);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantAddressNo), 0, 0, 'L');

$pdf->SetXY(76, 62);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantMoo), 0, 0, 'L');

$pdf->SetXY(95, 62);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantTambon), 0, 0, 'L');

$pdf->SetXY(155, 62);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantAmphoe), 0, 0, 'L');

$pdf->SetXY(40, 69);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantProvince), 0, 0, 'L');

$pdf->SetXY(120, 69);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantPhone), 0, 0, 'L');

// 3) ข้อมูลนิติบุคคล
$pdf->SetXY(50, 76);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicName), 0, 0, 'L');

$pdf->SetXY(153, 76);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicOffice), 0, 0, 'L');

$pdf->SetXY(36, 84);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicAddressNo), 0, 0, 'L');

$pdf->SetXY(56, 84);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicMoo), 0, 0, 'L');

$pdf->SetXY(76, 84);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicTambon), 0, 0, 'L');

$pdf->SetXY(124, 84);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicAmphoe), 0, 0, 'L');

$pdf->SetXY(172, 84);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicProvince), 0, 0, 'L');

// 4) ข้อมูลเรือ
$pdf->SetXY(99, 92);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $vesselName), 0, 0, 'L');

$pdf->SetXY(182, 92);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $shipCode), 0, 0, 'L');

$pdf->SetXY(75, 99);
$pdf->Cell(0, 8, iconv('UTF-8','cp874', $writtenAt), 0, 0, 'L');

// 4) คำรับรอง
if ($type == 1) {
    $pdf->SetXY(65, 195);
    $pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantName), 0, 0, 'L');

    $pdf->SetXY(147, 195);
    $pdf->Cell(0, 8, iconv('UTF-8','cp874', $juristicName), 0, 0, 'L');
}

// 4) ลายเซ็นผู้ยื่น
if ($type == 1) {
    $pdf->SetXY(143, 224);
    $pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantName), 0, 0, 'L');
} else {
    $pdf->SetXY(143, 232);
    $pdf->Cell(0, 8, iconv('UTF-8','cp874', $applicantName), 0, 0, 'L');
}

// ======================= แปะ QR ด้านล่างซ้าย =======================
// ใช้ไฟล์ชั่วคราวที่สร้างจาก phpqrcode
if (!empty($tempQrFile) && file_exists($tempQrFile)) {
    $pdf->Image($tempQrFile, 15, 260, 18, 18, 'PNG'); // X, Y, W, H
}

// ======================= ส่งออก PDF =======================
$fileName = 'Sor1_' . $request_id . '.pdf';
$pdf->Output('I', $fileName);

// ลบไฟล์ QR ชั่วคราวทิ้ง
if (!empty($tempQrFile) && file_exists($tempQrFile)) {
    unlink($tempQrFile);
}

exit;
