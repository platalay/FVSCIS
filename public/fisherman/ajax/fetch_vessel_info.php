<?php
require_once('../../../private/initialize.php');

header('Content-Type: application/json');

$ship_code = $_GET['ship_code'] ?? '';

if (empty($ship_code)) {
  echo json_encode(['error' => 'Missing ship code']);
  exit;
}

try {
  $vessel = Elicense::find_by_ship_code($el_db, $ship_code);
  if ($vessel) {
    echo json_encode([
      'success' => true,
      'ship_code' => $vessel->ship_code,
      'data_owner_id' => $vessel->nationality_id,
      'vessel_name' => $vessel->vessel_name,
      'vessel_ton_gross' => $vessel->vessel_ton_gross,
      'fishing_area' => $vessel->fishing_area
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลเรือ บ้าบออะไรกันล่ะ']);
  }
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}

