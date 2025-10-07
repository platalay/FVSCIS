<?php
class InspectionEvaluation
{
    /**
     * ตรวจสอบว่าผ่านเกณฑ์หรือไม่
     * - จะ "ไม่ปรับสถานะ" และคืนค่า 'incomplete' ถ้าข้อมูลยังไม่ครบพอที่จะตัดสิน
     * - จะไม่นับ 5.8–5.9 ถ้าเรือไม่ใช่ห้องเย็น (cold_room_flag != 1)
     *
     * @param int $request_id
     * @param string $mode 'commercial' หรือ 'non_permitted'
     * @return bool|string true/false หรือข้อความสาเหตุ ('incomplete' เมื่อยังตัดสินไม่ได้)
     */
    public static function check_vessel_pass($request_id, string $mode = 'commercial'): bool|string
    {
        $request = InspectionRequest::find_by_id($request_id);
        if (!$request) { return 'ไม่พบคำขอ'; }

        // โหลดแบบฟอร์ม
        $structure    = InspectionFormStructure::find_by_request_id($request_id);
        $material     = InspectionFormMaterial::find_by_request_id($request_id);
        $crew         = InspectionFormCrew::find_by_request_id($request_id);
        $water_ice    = InspectionFormWaterAndIce::find_by_request_id($request_id);
        $preservation = InspectionFormPreservation::find_by_request_id($request_id);

        // helper: ดึงค่าอย่างปลอดภัย (รองรับ null)
        $get = function($obj, string $field) {
            return (is_object($obj) && property_exists($obj, $field)) ? $obj->$field : null;
        };

        $cold = ((int)($request->cold_room_flag ?? 0) === 1);

        // กำหนด "ข้อบังคับหลัก" ตามโหมด
        if ($mode === 'commercial') {
            $required = [
                '1_1' => $get($structure, 'status_1_1'),
                '2_1' => $get($material,  'status_2_1'),
                '3_1' => $get($crew,      'status_3_1'),
                '4_1' => $get($water_ice, 'status_4_1'),
                '5_1' => $get($preservation,'status_5_1'),
            ];
        } elseif ($mode === 'non_permitted') {
            $required = [
                '1_1' => $get($structure, 'status_1_1'),
                '2_1' => $get($material,  'status_2_1'),
                '3_1' => $get($crew,      'status_3_1'),
            ];
        } else {
            return 'โหมดไม่ถูกต้อง';
        }

        // ถ้า required ยังไม่ถูกตอบ (ไม่ใช่ 'pass' หรือ 'fail') → ยังไม่ประเมิน
        foreach ($required as $k => $v) {
            if ($v !== 'pass' && $v !== 'fail') {
                // ยังไม่เซ็ตสถานะคำขอ ปล่อยเป็นระหว่างดำเนินการ
                return 'incomplete';
            }
        }

        // ถ้า required ข้อใดเป็น 'fail' → ไม่ผ่านทันที
        foreach ($required as $k => $v) {
            if ($v === 'fail') {
                $request->status = InspectionRequest::STATUS_FAILED;
                if (empty($request->actual_inspect_date) || $request->actual_inspect_date === '0000-00-00') {
                    $request->actual_inspect_date = date('Y-m-d');
                }
                $request->save();
                return "ไม่ผ่านเกณฑ์เพราะข้อบังคับ {$k} ไม่ผ่าน";
            }
        }

        // รวมสถานะทั้งหมด (ตัด 5_8, 5_9 ออกหากไม่ใช่เรือห้องเย็น)
        $fields = [
            // 1.x
            ['obj'=>$structure, 'code'=>'1_1'], ['obj'=>$structure, 'code'=>'1_2'], ['obj'=>$structure, 'code'=>'1_3'],
            ['obj'=>$structure, 'code'=>'1_4'], ['obj'=>$structure, 'code'=>'1_5'], ['obj'=>$structure, 'code'=>'1_6'], ['obj'=>$structure, 'code'=>'1_7'],
            // 2.x
            ['obj'=>$material, 'code'=>'2_1'], ['obj'=>$material, 'code'=>'2_2'], ['obj'=>$material, 'code'=>'2_3'],
            ['obj'=>$material, 'code'=>'2_4'], ['obj'=>$material, 'code'=>'2_5'], ['obj'=>$material, 'code'=>'2_6'],
            // 3.x
            ['obj'=>$crew, 'code'=>'3_1'], ['obj'=>$crew, 'code'=>'3_2'], ['obj'=>$crew, 'code'=>'3_3'],
            ['obj'=>$crew, 'code'=>'3_4'], ['obj'=>$crew, 'code'=>'3_5'],
            // 4.x
            ['obj'=>$water_ice, 'code'=>'4_1'], ['obj'=>$water_ice, 'code'=>'4_2'], ['obj'=>$water_ice, 'code'=>'4_3'], ['obj'=>$water_ice, 'code'=>'4_4'],
            // 5.x
            ['obj'=>$preservation, 'code'=>'5_1'], ['obj'=>$preservation, 'code'=>'5_2'], ['obj'=>$preservation, 'code'=>'5_3'],
            ['obj'=>$preservation, 'code'=>'5_4'], ['obj'=>$preservation, 'code'=>'5_5'], ['obj'=>$preservation, 'code'=>'5_6'],
            ['obj'=>$preservation, 'code'=>'5_7'],
        ];
        if ($cold) {
            $fields[] = ['obj'=>$preservation, 'code'=>'5_8'];
            $fields[] = ['obj'=>$preservation, 'code'=>'5_9'];
        }

        $pass_count = 0;
        $answered   = 0;

        foreach ($fields as $f) {
            $val = $get($f['obj'], 'status_' . $f['code']);
            if ($val === 'pass') { $pass_count++; $answered++; }
            elseif ($val === 'fail') { $answered++; }
            // ถ้าเป็น null/ว่าง → ยังไม่ตอบ
        }

        // ถ้ายังไม่ตอบอะไรเลย → ยังไม่ประเมิน
        if ($answered === 0) {
            return 'incomplete';
        }

        // ตั้งวันตรวจจริงเมื่อมีการตัดสินผล
        if (empty($request->actual_inspect_date) || $request->actual_inspect_date === '0000-00-00') {
            $request->actual_inspect_date = date('Y-m-d');
        }

        // ใช้เงื่อนไขเดิมของคุณ
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
