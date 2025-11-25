<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
require_once('../../private/fpdf/fpdf.php');
require_once('../../private/fpdi/src/autoload.php');

use setasign\Fpdi\Fpdi;

// test
if (!class_exists('setasign\Fpdi\Fpdi')) {
    die("FPDI not loaded");
}


// สร้าง Pdf
$pdf = new FPDI();
$pdf->SetAutoPageBreak(false);

// ระบุไฟล์ template
$source = '../../private/pdftemplate/FVS21.pdf';
$pageCount = $pdf->setSourceFile($source);

// ======================= หน้า 1 =======================
$pdf->AddPage();
$tpl = $pdf->importPage(1);
$pdf->useTemplate($tpl, 0, 0);

$pdf->AddFont('THSarabunPSK','','THSarabunPSK.php');
$pdf->SetFont('THSarabunPSK','',16);
$pdf->SetXY(160, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874','1'));
$pdf->SetXY(175, 24);
$pdf->Cell(80, 8, iconv('UTF-8','cp874','2568'));
$pdf->SetXY(158, 50);
$pdf->Cell(80, 8, iconv('UTF-8','cp874','รายละเอียดใน สร.2-4'));
$form = InspectionFormStructure::find_by_request_id(3);
foreach (InspectionFormStructure::$statusMap as $section => $pos) {

    $statusField = "status_" . $section;

    // ดึงจาก property ของ object
    $statusVal = $form->$statusField ?? null;

    if (!$statusVal) {
        continue;
    }

    InspectionFormStructure::drawStatus($pdf, $statusVal, $pos);

    if ($statusVal === 'fail') {
        $failCount = InspectionFormStructure::countFails($form, $section);
        $text      = InspectionFormStructure::buildFailSummaryText($failCount);

        if ($text !== '') {
            $xText = $pos['fail']['x'] + 11;
            $yText = $pos['fail']['y'];

            $pdf->SetXY($xText, $yText);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text), 0, 'L');
        }
    }
}

$form = InspectionFormMaterial::find_by_request_id(3);
foreach (InspectionFormMaterial::$statusMap as $section => $pos) {
    $statusField = "status_" . $section;
    // ดึงจาก property ของ object
    $statusVal = $form->$statusField ?? null;
    if (!$statusVal) {
        continue;
    }
    InspectionFormMaterial::drawStatus($pdf, $statusVal, $pos);
    if ($statusVal === 'fail') {
        $failCount = InspectionFormMaterial::countFails($form, $section);
        $text      = InspectionFormMaterial::buildFailSummaryText($failCount);
        error_log("fail".$section." = ".$failCount);
        if ($text !== '') {
            $xText = $pos['fail']['x'] + 11;
            $yText = $pos['fail']['y'];

            $pdf->SetXY($xText, $yText);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text), 0, 'L');
        }
    }
}


// ======================= หน้า 2 =======================
$pdf->AddPage();
$tpl = $pdf->importPage(2);
$pdf->useTemplate($tpl, 0, 0);
foreach (InspectionFormMaterial::$statusMapP2 as $section => $pos) {
    $statusField = "status_" . $section;
    // ดึงจาก property ของ object
    $statusVal = $form->$statusField ?? null;
    if (!$statusVal) {
        continue;
    }
    InspectionFormMaterial::drawStatus($pdf, $statusVal, $pos);
    if ($statusVal === 'fail') {
        $failCount = InspectionFormMaterial::countFailsP2($form, $section);
        $text      = InspectionFormMaterial::buildFailSummaryText($failCount);
        error_log("fail".$section." = ".$failCount);
        if ($text !== '') {
            $xText = $pos['fail']['x'] + 11;
            $yText = $pos['fail']['y'];

            $pdf->SetXY($xText, $yText);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text), 0, 'L');
        }
    }
}

$form = InspectionFormCrew::find_by_request_id(3);
foreach (InspectionFormCrew::$statusMap as $section => $pos) {
    $statusField = "status_" . $section;
    // ดึงจาก property ของ object
    $statusVal = $form->$statusField ?? null;
    if (!$statusVal) {
        continue;
    }
    InspectionFormCrew::drawStatus($pdf, $statusVal, $pos);
    if ($statusVal === 'fail') {
        $failCount = InspectionFormCrew::countFails($form, $section);
        $text      = InspectionFormCrew::buildFailSummaryText($failCount);
        error_log("fail".$section." = ".$failCount);
        if ($text !== '') {
            $xText = $pos['fail']['x'] + 11;
            $yText = $pos['fail']['y'];

            $pdf->SetXY($xText, $yText);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text), 0, 'L');
        }
    }
}

$form = InspectionFormWaterAndIce::find_by_request_id(3);
foreach (InspectionFormWaterAndIce::$statusMap as $section => $pos) {
    $statusField = "status_" . $section;
    // ดึงจาก property ของ object
    $statusVal = $form->$statusField ?? null;
    if (!$statusVal) {
        continue;
    }
    InspectionFormWaterAndIce::drawStatus($pdf, $statusVal, $pos);
    if ($statusVal === 'fail') {
        $failCount = InspectionFormWaterAndIce::countFails($form, $section);
        $text      = InspectionFormWaterAndIce::buildFailSummaryText($failCount);
        error_log("fail".$section." = ".$failCount);
        if ($text !== '') {
            $xText = $pos['fail']['x'] + 11;
            $yText = $pos['fail']['y'];

            $pdf->SetXY($xText, $yText);
            $pdf->SetFont('THSarabunPSK', '', 16);
            $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text), 0, 'L');
        }
    }
}

$ydata = 258;
$pdf->SetXY(133, $ydata);
$pdf->Cell(80, 8, iconv('UTF-8','cp874','/'));
$pdf->SetXY(147, $ydata);
$pdf->Cell(80, 8, iconv('UTF-8','cp874','X'));


// ======================= หน้า 3 =======================
$pdf->AddPage();
$tpl = $pdf->importPage(3);
$pdf->useTemplate($tpl, 0, 0);

$pdf->SetXY(120, 230);
$pdf->Cell(60, 8, iconv('UTF-8','cp874','นายทดสอบ ทดสอบดี'));

$pdf->Output('I', 'FailNotice.pdf');
exit;
