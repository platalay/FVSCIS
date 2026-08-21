<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);

require_once('../../private/fpdf/fpdf.php');
require_once('../../private/fpdi/src/autoload.php');
require_once('../../private/phpqrcode/qrlib.php');
use setasign\Fpdi\Fpdi;

// รับ token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('ไม่พบ token');
}

// ✅ ดึง InspectionFormStatus จาก token
$form = InspectionFormStatus::find_by_token($token);
$old_document_lock = $form->document_locked;
if (!$form) {
    die('ไม่พบเอกสารที่สอดคล้องกับ token นี้');
}
$form->document_locked = 1;
$form->save();
    
// ✅ ดึง request ที่เกี่ยวข้อง
$request = InspectionRequest::find_by_id($form->request_id);
if ($request && empty($request->is_submitted)) {
    $request->is_submitted = 1;
    $request->submitted_at = date('Y-m-d H:i:s');
    $request->save();
}
        if(!$old_document_lock){
        // ✅ LOG: ใช้คำว่า "กำหนด/เสนอ" ไม่ใช้ "ยืนยัน"
            $log = new InspectionLog();
            $log->inspection_request_id = $request->id;

        // วันที่แบบ พ.ศ.
        $notify_date = thai_date($request->submitted_at, ['format'=>'short', 'show_time'=>true]);

        // สมมุติ id ผู้อนุมัติ
        $officer = Officer::find_by_id($session->user_id());
        $department = Department::find_by_id($officer->departments_id);
        $department_group = DepartmentGroup::find_by_id($department->parent_department);
        $approver_id = $department_group->officer_id ?? null;

        if ($request->status === 'failed') {

                $log->action_id = 17;
                $log->note = "ผลการตรวจเรือ {$request->vessel_name} อยู่ในสถานะไม่ผ่าน เมื่อวันที่ {$notify_date} กรุณาตรวจสอบและยืนยันผลการตรวจ";
                
                Notification::create_notification(
                $approver_id,
                'signer',
                $request->id,
                17,
                "ผลการตรวจเรือ {$request->vessel_name} อยู่ในสถานะไม่ผ่าน เมื่อวันที่ {$notify_date} อยู่ระหว่างยืนยันผลการตรวจไม่ผ่าน",
                'warning'
                );

                Notification::create_notification(
                $request->created_by,
                'fisherman',
                $request->id,
                17,
                "ผลการตรวจเรือ {$request->vessel_name} อยู่ในสถานะไม่ผ่าน เมื่อวันที่ {$notify_date} อยู่ระหว่างยืนยันผลการตรวจไม่ผ่าน",
                'warning'
                );

        } elseif ($request->status === 'passed') {
                
                $log->action_id = 17;
                $log->note = "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจ เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ";
                
            Notification::create_notification(
                $approver_id,
                'signer',
                $request->id,
                16,
                "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจ เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ",
                'info'
            );

            Notification::create_notification(
                $request->created_by,
                'fisherman',
                $request->id,
                16,
                "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจ เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ",
                'info'
            );

        }   elseif ($request->status === 'conditional') {

                $log->action_id = 17;
                $log->note = "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจแบบมีเงื่อนไข เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ";

                Notification::create_notification(
                $approver_id,
                'signer',
                $request->id,
                16,
                "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจแบบมีเงื่อนไข เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ",
                'info'
                );

                Notification::create_notification(
                $request->created_by,
                'fisherman',
                $request->id,
                16,
                "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจแบบมีเงื่อนไข เมื่อวันที่ {$notify_date} อยู่ระหว่างรอการอนุมัติ",
                'info'
                );

        }

        $log->save(); //save log
    }

$info = thai_date($request->submitted_at, [
    'format'       => 'long',
    'return_parts' => true
]);
$d = $info['day'];
$m = $info['month_name'];
$y = $info['year_be'];
$department = Department::find_by_id($request->department_id);
$department_name = $department->name;
// ✅ สร้างลิงก์ตรวจสอบ
$url = 'https://fvscis.fisheries.go.th/verify_fvs02.php?token=' . urlencode($token);

// ✅ สร้าง QR code ชั่วคราว
$tempFile = tempnam(sys_get_temp_dir(), 'qrcode_') . '.png';
QRcode::png($url, $tempFile, QR_ECLEVEL_L, 4);
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
                $pdf->SetXY($cfg['fail']['x'] + 10, $cfg['fail']['y']);
                $pdf->SetFont('THSarabunPSK', '', 16);
                $pdf->MultiCell(80, 5, iconv('UTF-8', 'cp874', $text));
            }
        }
    }

    return $currentPage;
}

// ===================== รับ request_id =====================
$request_id = $request->id;
$request_id = (int)$request_id;

$InspectionApplicantInfo = InspectionApplicantInfo::find_by_request_id($request_id);
$applicant = $InspectionApplicantInfo;
$doc_number = $InspectionApplicantInfo->form1_doc_number;
list($running, $year) =   DocumentCounter::parse_running_year_from_code($doc_number);
$year = $year+543;
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
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$running));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$year));

// กำหนดตำแหน่งเริ่มต้น
$pdf->SetXY(20, 252);

// ข้อความ
$pdf->Cell(0, 6, iconv('UTF-8', 'cp874', 'สแกนเพื่อตรวจสอบข้อมูล'), 0, 1, 'L');

// รูป QR (วางใต้ข้อความเล็กน้อย)
$pdf->Image($tempFile, 20, 258, 30, 30);

// ลบไฟล์ชั่วคราว
unlink($tempFile);

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
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$running));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$year));
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
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$running));

$pdf->SetXY(176, 24);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$year));

$pdf->AddPage();
$tpl = $pdf->importPage(4);
$pdf->useTemplate($tpl, 0, 0);
$pdf->SetXY(120, 32);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$department_name));

$pdf->SetXY(115, 39);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$d));

$pdf->SetXY(135, 39);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$m));

$pdf->SetXY(170, 39);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$y));

$pdf->SetXY(48, 50);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$department_name));

$sor1_num = "ลำที่ {$running} /ปี {$year}";
$pdf->SetXY(148, 50);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$sor1_num));

$el_db_data = Elicense::find_one_by_ship_code($el_db, $request->ship_code);









$pdf->SetXY(48, 57);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$request->vessel_name));
$pdf->SetXY(145, 57);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$request->ship_code));


$pdf->SetXY(64, 64);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$el_db_data->vessel_ton_gross));
$pdf->SetXY(155, 64);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$el_db_data->vessel_length));

$pdf->SetXY(67, 71);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$el_db_data->fishing_mark));
$pdf->SetXY(145, 71);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$el_db_data->license_no));


$pdf->SetXY(80, 78);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$request->gear_type));
// ประเภทผู้ยื่น: 0 = บุคคลธรรมดา, 1 = นิติบุคคล

function build_th_address(
    string $no,
    string $moo,
    string $tambon,
    string $amphoe,
    string $province,
    bool $return_parts = false
) {
    $no       = trim($no);
    $moo      = trim($moo);
    $tambon   = trim($tambon);
    $amphoe   = trim($amphoe);
    $province = trim($province);

    // หมู่ ถ้าไม่มีให้เป็น "-"
    $mooText = ($moo === '' || $moo === '0') ? '-' : $moo;

    // เตรียมข้อความแยกส่วน
    $text_no       = ($no !== '') ? "บ้านเลขที่ {$no}" : "บ้านเลขที่ -";
    $text_moo      = "หมู่ที่ {$mooText}";
    $text_tambon   = ($tambon !== '')   ? "ตำบล{$tambon}"   : '';
    $text_amphoe   = ($amphoe !== '')   ? "อำเภอ{$amphoe}"  : '';
    $text_province = ($province !== '') ? "จังหวัด{$province}" : '';

    // ต่อที่อยู่เต็ม
    $full = implode(' ', array_filter([
        $text_no,
        $text_moo,
        $text_tambon,
        $text_amphoe,
        $text_province
    ]));

    // ถ้าขอแยกส่วน
    if ($return_parts) {
        return [
            'full'     => $full,
            'no'       => $text_no,
            'moo'      => $text_moo,
            'tambon'   => $text_tambon,
            'amphoe'   => $text_amphoe,    // ✅ แยกอำเภอ
            'province' => $text_province,  // ✅ แยกจังหวัด
        ];
    }

    // ค่าเดิม (string)
    return $full;
}



// --- สถานะนิติบุคคล ---
$isJuristic = (int)($applicant->is_juristic ?? 0);

// --- ข้อมูลผู้ให้ข้อมูล (เป็น applicant เสมอ) ---
$informant_name        = trim($applicant->applicant_name ?? '');
$informant_age         = trim($applicant->applicant_age ?? '');
$informant_nationality = trim($applicant->applicant_nationality ?? '');
$informant_phone       = trim($applicant->applicant_phone ?? '');

$informant_addr = build_th_address(
    (string)($applicant->applicant_address_no ?? ''),
    (string)($applicant->applicant_moo ?? ''),
    (string)($applicant->applicant_tambon ?? ''),
    (string)($applicant->applicant_amphoe ?? ''),
    (string)($applicant->applicant_province ?? ''),
    true
);

$informant_address  = $informant_addr['full'];
$informant_amphoe   = $informant_addr['amphoe'];    // "อำเภอ..."
$informant_province = $informant_addr['province'];  // "จังหวัด..."

// --- ข้อมูลเจ้าของเรือ ---
if ($isJuristic === 1) {
    // ✅ เจ้าของเรือ = นิติบุคคล
    $owner_name   = trim($applicant->juristic_name ?? '');
    $owner_office = trim($applicant->juristic_office ?? '');
    $owner_phone = trim($applicant->applicant_phone ?? '');
    $owner_addr = build_th_address(
        (string)($applicant->juristic_address_no ?? ''),
        (string)($applicant->juristic_moo ?? ''),
        (string)($applicant->juristic_tambon ?? ''),
        (string)($applicant->juristic_amphoe ?? ''),
        (string)($applicant->juristic_province ?? ''),
        true
    );
} else {
    // ✅ เจ้าของเรือ = บุคคลธรรมดา
    $owner_name   = trim($applicant->applicant_name ?? '');
    $owner_phone = trim($applicant->applicant_phone ?? '');
    $owner_office = ''; // บุคคลธรรมดาไม่มีสำนักงาน (คงเป็นค่าว่าง)

    $owner_addr = $informant_addr; // owner = applicant → ใช้ชุดเดียวกันได้เลย
}

$owner_address1  = $owner_addr['no'].' '.$owner_addr['moo'].''.$owner_addr['tambon'];
$owner_address2   = $owner_addr['amphoe'].' '.$owner_addr['province'];

$pdf->SetXY(55, 85);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$owner_name));
$pdf->SetXY(125, 85);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$owner_address1));

$pdf->SetXY(35, 92);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$owner_address2));

$pdf->SetXY(140, 92);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$owner_phone));


$pdf->SetXY(42, 106);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$informant_address));

$pdf->SetXY(140, 113);
$pdf->Cell(80, 5, iconv('UTF-8','cp874', (string)$owner_phone));

// ======================= page 5 =======================
$pdf->AddPage();
$tpl = $pdf->importPage(5);
$pdf->useTemplate($tpl, 0, 0);

// ======================= Output =======================
$pdf->Output('I', 'FailNotice.pdf');
exit;
