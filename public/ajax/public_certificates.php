<?php
declare(strict_types=1);

require_once('../../private/initialize.php');
header('Content-Type: application/json; charset=utf-8');

function public_datatable_bind(mysqli_stmt $statement, string $types, array &$params): void
{
    if ($types === '') {
        return;
    }
    $bind = [$types];
    foreach ($params as &$value) {
        $bind[] = &$value;
    }
    call_user_func_array([$statement, 'bind_param'], $bind);
}

try {
    $draw = max(0, (int)($_GET['draw'] ?? 0));
    $start = max(0, (int)($_GET['start'] ?? 0));
    $length = (int)($_GET['length'] ?? 10);
    if (!in_array($length, [10, 25, 50, 100], true)) {
        $length = 10;
    }

    $conditions = [];
    $types = '';
    $params = [];
    foreach (['vessel_name', 'ship_code', 'vessel_mark', 'certificate_number'] as $field) {
        $value = trim((string)($_GET[$field] ?? ''));
        if ($value !== '') {
            $conditions[] = "`{$field}` LIKE ?";
            $types .= 's';
            $params[] = '%' . $value . '%';
        }
    }
    foreach (['gear_type', 'certificate_status'] as $field) {
        $value = trim((string)($_GET[$field] ?? ''));
        if ($value !== '') {
            $conditions[] = "`{$field}` = ?";
            $types .= 's';
            $params[] = $value;
        }
    }
    $expirationDate = trim((string)($_GET['expiration_date'] ?? ''));
    if ($expirationDate !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $expirationDate);
        if (!$date || $date->format('Y-m-d') !== $expirationDate) {
            $expirationDate = '';
        } else {
            $conditions[] = '`expiration_date` = ?';
            $types .= 's';
            $params[] = $expirationDate;
        }
    }

    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    $totalResult = $database->query('SELECT COUNT(*) AS total FROM fv_sanitation_certification_old');
    $recordsTotal = (int)($totalResult->fetch_assoc()['total'] ?? 0);

    $countStatement = $database->prepare("SELECT COUNT(*) AS total FROM fv_sanitation_certification_old{$where}");
    if (!$countStatement) {
        throw new RuntimeException('Unable to prepare count query.');
    }
    public_datatable_bind($countStatement, $types, $params);
    $countStatement->execute();
    $recordsFiltered = (int)($countStatement->get_result()->fetch_assoc()['total'] ?? 0);
    $countStatement->close();

    $dataStatement = $database->prepare("SELECT vessel_name, ship_code, vessel_mark, gear_type, status, certificate_number, effective_date, expiration_date, certificate_status FROM fv_sanitation_certification_old{$where} ORDER BY id DESC LIMIT ? OFFSET ?");
    if (!$dataStatement) {
        throw new RuntimeException('Unable to prepare data query.');
    }
    $dataTypes = $types . 'ii';
    $dataParams = $params;
    $dataParams[] = $length;
    $dataParams[] = $start;
    public_datatable_bind($dataStatement, $dataTypes, $dataParams);
    $dataStatement->execute();
    $result = $dataStatement->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $dataStatement->close();

    echo json_encode(['draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['draw' => (int)($_GET['draw'] ?? 0), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'error' => 'ไม่สามารถโหลดข้อมูลได้ในขณะนี้'], JSON_UNESCAPED_UNICODE);
}
