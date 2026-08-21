<?php
// ajax/get_request_logs.php

require_once('../../../private/initialize.php');

header('Content-Type: application/json; charset=utf-8');

try {

    // ให้สิทธิ์ทั้งชาวประมงและเจ้าหน้าที่กลุ่มที่เกี่ยวข้อง
    $session->require_role([
        'fisherman',
        'officer',
        'inspectofficer',
        'signer',
        'admin',
        'centraladmin'
    ]);

    $role    = $session->role ?? '';
    $user_id = $session->user_id();

    // ✅ รับ inspection_request_id จาก GET
    $request_id = $_GET['id'] ?? '';
    if (empty($request_id) || !ctype_digit((string)$request_id)) {
        throw new Exception('รหัสคำขอไม่ถูกต้อง');
    }
    $request_id = (int)$request_id;

    // 🛡️ ถ้าเป็นชาวประมง ต้องเป็นเจ้าของคำขอเท่านั้นถึงจะดูได้
    if ($role === 'fisherman') {
        $request = InspectionRequest::find_by_id($request_id);
        if (!$request) {
            throw new Exception('ไม่พบคำขอตรวจเรือ');
        }

        // ปรับ field ตามของจริงในคลาสคุณ เช่น fisherman_id / owner_id
        if ((int)$request->created_by !== (int)$user_id) {
            throw new Exception('คุณไม่มีสิทธิ์ดูประวัติของคำขอนี้');
        }
    }

    global $database;

    // 🎯 SQL พื้นฐาน: ดึง log ตาม inspection_request_id
    $sql = "
        SELECT 
            il.created_at,
            il.note,
            la.description_th AS action_name,

            -- actor name: เลือกชื่อจาก officer หรือ fisherman อันไหนที่ไม่ว่างก่อน
            COALESCE(o.full_name, f.full_name, '') AS actor_name

        FROM inspection_logs il
        LEFT JOIN log_actions la ON il.action_id = la.id
        LEFT JOIN officer   o    ON il.created_by = o.id
        LEFT JOIN fisherman f    ON il.created_by = f.id
        WHERE il.inspection_request_id = {$request_id}
    ";

    // 🔎 ถ้าเป็นชาวประมง ให้เห็นเฉพาะ action ที่เกี่ยวข้องกับเขา
    if ($role === 'fisherman') {
        // TODO: ปรับ id ให้ตรงกับ log_actions ของคุณ
        // ตัวอย่าง:
        //  1 = ส่งคำขอ
        //  2 = เจ้าหน้าที่รับคำขอ
        //  4 = กำหนดวันตรวจเรือ
        //  5 = ชาวประมงยืนยันวันตรวจ
        //  6 = ตรวจเรือเสร็จ
        //  7 = อนุมัติใบรับรอง
        //  8 = ส่งกลับให้แก้ไข
        $allowed_actions_for_fisherman = [1, 2, 4, 5, 6, 7, 8];

        if (!empty($allowed_actions_for_fisherman)) {
            $ids = implode(',', array_map('intval', $allowed_actions_for_fisherman));
            $sql .= " AND il.action_id IN ({$ids})";
        }
    }

    // เรียงตามเวลาเก่า → ใหม่
    $sql .= " ORDER BY il.created_at ASC";

    $result = $database->query($sql);
    if (!$result) {
        throw new Exception('ไม่สามารถดึงข้อมูลประวัติได้');
    }

    $logs = [];
    while ($row = $result->fetch_assoc()) {

        // format เวลาให้อ่านง่าย (จะใช้ค่าเดิมตรง ๆ ก็ได้)
        $time_text = $row['created_at'];
        if (!empty($row['created_at'])) {
            
            $time_text = thai_date($row['created_at'], ['show_time' => true]);
        }

        $logs[] = [
            'time'   => $time_text,
            'action' => $row['action_name'] ?? '-',
            'actor'  => $row['actor_name']  ?? '',
            'note'   => $row['note']        ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'logs'    => $logs
    ]);
    exit;

} catch (Throwable $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
