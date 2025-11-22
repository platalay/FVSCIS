<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);

// สมมติว่ามีตัวแปร $el_db (PDO ไปยัง Postgres/ELicense) ถูกเตรียมใน initialize.php แล้ว
try {
    $ship_code = trim($_POST['ship_code'] ?? '');
    $fishery_year = trim($_POST['fishery_year'] ?? '2567'); // จะส่งมาหรือใช้ค่าเริ่มต้น

    if ($ship_code === '') {
        throw new Exception('กรุณาระบุ ship_code');
    }

    // เรียกเมธอดที่คุณมี
    $el = Elicense::find_one_by_ship_code($el_db, $ship_code, $fishery_year);
    if (!$el) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลเรือตามรหัส']);
        exit;
    }

    // เตรียม payload ให้ตรง mapping
    $data = [
        'vessel_name'  => $el->vessel_name ?? null,
        'license_no'   => $el->license_no ?? null,
        'display_name' => $el->display_name ?? null,
        // บางฐานอาจใช้ชื่อ field ต่างกัน: geartype / gear_type / vessel_type
        'geartype'     => $el->geartype ?? ($el->gear_type ?? ($el->vessel_type ?? null)),
        'fishing_mark' => $el->fishing_mark ?? null,
    ];

    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
