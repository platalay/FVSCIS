<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');

try {
    $attrs = $_POST['FvSanitationCertificationOld'] ?? [];
    $id = (int)($attrs['id'] ?? 0);
    if ($id <= 0) throw new Exception('ไม่พบ id');

    $obj = FvSanitationCertificationOld::find_by_id($id);
    if(!$obj) throw new Exception('ไม่พบรายการ');

    // กัน id ออกจาก merge
    unset($attrs['id']);

    // ถ้า DatabaseObject มี merge_attributes() อยู่แล้ว ใช้ได้เลย
    if(method_exists($obj, 'merge_attributes')){
        $obj->merge_attributes($attrs);
    } else {
        // fallback: อัปเดตเฉพาะคอลัมน์ใน whitelist
        foreach (FvSanitationCertificationOld::$db_columns as $col) {
            if($col === 'id') continue;
            if(array_key_exists($col, $attrs)){
                $val = is_string($attrs[$col]) ? trim($attrs[$col]) : $attrs[$col];
                $obj->$col = ($val === '') ? null : $val;
            }
        }
    }

    if($obj->save()){
        echo json_encode(['success'=>true]);
    }else{
        throw new Exception('บันทึกไม่สำเร็จ');
    }
} catch (Throwable $e) {
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
