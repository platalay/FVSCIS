<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

require_once('../../private/fpdf/fpdf.php');
require_once('../../private/fpdi/src/autoload.php');
require_once('../../private/phpqrcode/qrlib.php');
use setasign\Fpdi\Fpdi;

// ===================== สำหรับเขียน pdf ตามตำแหน่งจาก map =====================
// - ใช้กับ FPDI (เพราะต้อง importPage/useTemplate)
// - รับ currentPage เข้าไป และ return currentPage กลับออกมา เพื่อให้ไหลต่อเนื่อง
function renderInspectionForm(
    \setasign\Fpdi\Fpdi $pdf,
    $form,
    array $statusMap,
    callable $drawStatus,
    callable $countFails,
    callable $buildText,
    array $pageTemplates,
    int $currentPage
): int {

    if (!$form) {
        return $currentPage;
    }

    foreach ($statusMap as $section => $cfg) {

        $targetPage = $cfg['page'] ?? $currentPage;

        // เปลี่ยนหน้าเมื่อ map บอกว่าข้ามหน้า
        if ($targetPage !== $currentPage) {
            $currentPage = $targetPage;

            $pdf->AddPage();

            if (isset($pageTemplates[$currentPage])) {
                $tpl = $pdf->importPage($pageTemplates[$currentPage]);
                $pdf->useTemplate($tpl, 0, 0);
            }
        }

        $statusField = "status_{$section}";
        $statusVal   = $form->$statusField ?? null;
        if (!$statusVal) {
            continue;
        }

        // วาด pass/fail
        $drawStatus($pdf, $statusVal, $cfg);

        // ถ้า fail -> นับประเด็น + ใส่ข้อความ
        if ($statusVal === 'fail') {
            $failCount = $countFails($form, $section);
            $text      = $buildText($failCount);

            if ($text !== '') {
                $pdf->SetXY($cfg['fail']['x'] + 13, $cfg['fail']['y']);
                $pdf->SetFont('THSarabunPSK', '', 16);
                $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text));
            }
        }
    }

    return $currentPage;
}

// ===================== รับ request_id =====================
$request_id = $_GET['request_id'] ?? 4;
$request_id = (int)$request_id;

// test
if (!class_exists('setasign\Fpdi\Fpdi')) {
    die("FPDI not loaded");
}

// ===================== สร้าง PDF =====================
$pdf = new FPDI();
$pdf->SetAutoPageBreak(false);

// ระบุไฟล์ template
$source = '../../private/pdftemplate/FVS21.pdf';
$pageCount = $pdf->setSourceFile($source);

// template map: หน้าในเอกสารที่คุณสร้าง => หน้า template ในไฟล์ FVS21.pdf
$pageTemplates = [
    1 => 1,
    2 => 2,
    3 => 3,
];

// ===================== ดึงข้อมูลจากฐานข้อมูล =====================

// คำขอตรวจ
$request = InspectionRequest::find_by_id($request_id);
if (!$request) {
    die('ไม่พบข้อมูลคำขอตรวจ');
}

// ผู้ยื่นคำขอ สร.1
$applicant = InspectionApplicantInfo::find_by_request_id($request_id);
if (!$applicant) {
    die('ไม่พบข้อมูลผู้ยื่นคำขอสำหรับแบบ สร.1');
}

// ===================== เตรียมตัวแปรที่ต้องใช้บนฟอร์ม =====================

// เขียนที่ / วันที่
$writtenAt   = $applicant->written_at   ?? '';
$writtenDate = $applicant->written_date ?? null; // Y-m-d

// ต้องมีฟังก์ชัน thai_year() อยู่แล้วในระบบคุณ
$thai_year = thai_year($writtenDate);

// running number จาก doc number
$run = $applicant ? $applicant->form1_running_number() : null;

// ======================= เริ่ม หน้า 1 =======================
$currentPage = 1;

$pdf->AddPage();
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl, 0, 0);

$pdf->AddFont('THSarabunPSK','','THSarabunPSK.php');
$pdf->SetFont('THSarabunPSK','',16);

// ตัวอย่างวางเลขเอกสาร
$pdf->SetXY(161, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$run));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$thai_year));


// ======================= หมวด 1: Structure (page 1) =======================
$form = InspectionFormStructure::find_by_request_id($request_id);
$currentPage = renderInspectionForm(
    $pdf,
    $form,
    InspectionFormStructure::$statusMap,
    [InspectionFormStructure::class, 'drawStatus'],
    [InspectionFormStructure::class, 'countFails'],
    [InspectionFormStructure::class, 'buildFailSummaryText'],
    $pageTemplates,
    $currentPage
);

// ======================= หมวด 2: Material (page 1,2) =======================
$form = InspectionFormMaterial::find_by_request_id($request_id);
$currentPage = renderInspectionForm(
    $pdf,
    $form,
    InspectionFormMaterial::$statusMap,
    [InspectionFormMaterial::class, 'drawStatus'],
    [InspectionFormMaterial::class, 'countFails'],
    [InspectionFormMaterial::class, 'buildFailSummaryText'],
    $pageTemplates,
    $currentPage
);

// ตัวอย่างวางเลขเอกสาร
$pdf->SetXY(161, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$run));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$thai_year));
// ======================= หมวด 3: Crew (page 2) =======================
$form = InspectionFormCrew::find_by_request_id($request_id);
$currentPage = renderInspectionForm(
    $pdf,
    $form,
    InspectionFormCrew::$statusMap,
    [InspectionFormCrew::class, 'drawStatus'],
    [InspectionFormCrew::class, 'countFails'],
    [InspectionFormCrew::class, 'buildFailSummaryText'],
    $pageTemplates,
    $currentPage
);

// ======================= หมวด 4: WaterAndIce (page 2) =======================
$form = InspectionFormWaterAndIce::find_by_request_id($request_id);
$currentPage = renderInspectionForm(
    $pdf,
    $form,
    InspectionFormWaterAndIce::$statusMap,
    [InspectionFormWaterAndIce::class, 'drawStatus'],
    [InspectionFormWaterAndIce::class, 'countFails'],
    [InspectionFormWaterAndIce::class, 'buildFailSummaryText'],
    $pageTemplates,
    $currentPage
);

// ======================= หมวด 5: Preservation (page 2,3) =======================
$form = InspectionFormPreservation::find_by_request_id($request_id);
$currentPage = renderInspectionForm(
    $pdf,
    $form,
    InspectionFormPreservation::$statusMap,
    [InspectionFormPreservation::class, 'drawStatus'],
    [InspectionFormPreservation::class, 'countFails'],
    [InspectionFormPreservation::class, 'buildFailSummaryText'],
    $pageTemplates,
    $currentPage
);
// ตัวอย่างวางเลขเอกสาร
$pdf->SetXY(161, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$run));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874', (string)$thai_year));
// ======================= Output =======================
$pdf->Output('I', 'FailNotice.pdf');
exit;
