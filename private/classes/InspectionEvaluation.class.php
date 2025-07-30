<?php
class InspectionEvaluation
{
    /**
     * ตรวจสอบว่าผ่านเกณฑ์หรือไม่
     * 
     * @param int $request_id
     * @param string $mode 'commercial' (เรือพาณิชย์) หรือ 'non_permitted' (ไม่มีใบอนุญาต)
     * @return bool|string
     */
    public static function check_vessel_pass($request_id, string $mode = 'commercial'): bool|string
    {
        // โหลดแต่ละฟอร์ม
        $structure    = InspectionFormStructure::find_by_request_id($request_id);
        $material     = InspectionFormMaterial::find_by_request_id($request_id);
        $crew         = InspectionFormCrew::find_by_request_id($request_id);
        $water_ice    = InspectionFormWaterAndIce::find_by_request_id($request_id);
        $preservation = InspectionFormPreservation::find_by_request_id($request_id);
        $request = InspectionRequest::find_by_id($request_id);
        

        // กำหนดข้อบังคับหลัก และจำนวนผ่านขั้นต่ำ ตามโหมด
        if ($mode === 'commercial') {
            $required = [
                '1_1' => $structure->status_1_1 ?? '',
                '2_1' => $material->status_2_1 ?? '',
                '3_1' => $crew->status_3_1 ?? '',
                '4_1' => $water_ice->status_4_1 ?? '',
                '5_1' => $preservation->status_5_1 ?? '',
            ];
            
        } elseif ($mode === 'non_permitted') {
            $required = [
                '1_1' => $structure->status_1_1 ?? '',
                '2_1' => $material->status_2_1 ?? '',
                '3_1' => $crew->status_3_1 ?? '',
            ];
            
        } else {
            return 'โหมดไม่ถูกต้อง';
        }

        // ตรวจข้อบังคับหลัก
        foreach ($required as $key => $val) {
            if ($val !== 'pass') {
                $request->status = InspectionRequest::STATUS_FAILED;
                $request->save();
                return "ไม่ผ่านเกณฑ์เพราะข้อบังคับ {$key} ไม่ผ่าน (ค่า: {$val})";
            }
        }

        // รวมสถานะทั้งหมด
        $all_statuses = [
            $structure->status_1_1, $structure->status_1_2, $structure->status_1_3,
            $structure->status_1_4, $structure->status_1_5, $structure->status_1_6, $structure->status_1_7,
            $material->status_2_1, $material->status_2_2, $material->status_2_3,
            $material->status_2_4, $material->status_2_5, $material->status_2_6,
            $crew->status_3_1, $crew->status_3_2, $crew->status_3_3,
            $crew->status_3_4, $crew->status_3_5,
            $water_ice->status_4_1, $water_ice->status_4_2, $water_ice->status_4_3, $water_ice->status_4_4,
            $preservation->status_5_1, $preservation->status_5_2, $preservation->status_5_3,
            $preservation->status_5_4, $preservation->status_5_5, $preservation->status_5_6,
            $preservation->status_5_7, $preservation->status_5_8, $preservation->status_5_9,
        ];

        $pass_count = 0;
        foreach ($all_statuses as $status) {
            if ($status === 'pass') {
                $pass_count++;
            }
        }

            $today = date('Y-m-d');

            // กำหนด actual_inspect_date ถ้ายังไม่เคยตั้ง
            if (empty($request->actual_inspect_date) || $request->actual_inspect_date === '0000-00-00') {
                $request->actual_inspect_date = $today;
            }

            if ($pass_count >= 14) {
                $request->status = InspectionRequest::STATUS_PASSED;
                $request->save();
                return true;
            } elseif ($pass_count >= 10) {
                $request->status = InspectionRequest::STATUS_CONDITIONAL;
                $request->save();
                return true;
            } else {
                $request->status = InspectionRequest::STATUS_FAILED;
                $request->save();
                return "ไม่ผ่านเกณฑ์เพราะจำนวนข้อผ่านน้อยกว่า 10 ข้อ (ได้ {$pass_count})";
            }
    }
}
?>
