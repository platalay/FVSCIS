<?php
require_once('../../../private/initialize.php');
$session->require_role(['inspectofficer']);
header('Content-Type: application/json; charset=utf-8');

try {
    $certificate_id = (int)($_GET['certificate_id'] ?? 0);
    if ($certificate_id <= 0) {
        throw new Exception('ไม่พบ certificate id');
    }

    $certificate = FvSanitationCertificationOld::find_by_id($certificate_id);
    $officer = Officer::find_by_id($session->user_id());
    if (!$certificate || !$officer || (string)$certificate->evaluation_agency !== (string)$officer->departments_id) {
        throw new Exception('คุณไม่มีสิทธิ์ดูประวัติใบรับรองนี้');
    }

    $audit = [];
    foreach (InspectionLog::find_manual_certificate_audit($certificate_id) as $log) {
        $audit[] = [
            'id' => (int)$log->id,
            'action' => $log->action_name ?? $log->action_code ?? '-',
            'actor_id' => (int)$log->created_by,
            'actor_role' => $log->actor_role,
            'created_at' => $log->created_at,
            'ip' => $log->created_ip,
            'note' => $log->note,
            'old_values' => $log->old_values ? json_decode($log->old_values, true) : null,
            'new_values' => $log->new_values ? json_decode($log->new_values, true) : null,
        ];
    }

    echo json_encode(['success' => true, 'audit' => $audit], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}