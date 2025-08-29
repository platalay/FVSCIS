<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once('../../../private/initialize.php');

try {
    // ถ้ามี CSRF ให้ตรวจที่นี่
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('ไม่พบ id');

    $obj = FvSanitationCertificationOld::find_by_id($id);
    if(!$obj) throw new Exception('ไม่พบข้อมูล');

    // ใช้เมธอด delete() จาก DatabaseObject ถ้ามี
    if(method_exists($obj, 'delete')){
        $ok = $obj->delete();
    } else {
        // fallback: ลบตรง ๆ
        $db = FvSanitationCertificationOld::$database;
        $id_esc = $db->escape_string($id);
        $sql = "DELETE FROM ".FvSanitationCertificationOld::$table_name." WHERE id='{$id_esc}' LIMIT 1";
        $ok = $db->query($sql);
    }

    if($ok){
        echo json_encode(['success'=>true]);
    }else{
        throw new Exception('ไม่สามารถลบข้อมูลได้');
    }
} catch (Throwable $e) {
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
