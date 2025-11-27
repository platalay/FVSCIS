<?php
class InspectionEvaluation
{
    /**
     * config rule กลางทั้งหมด (TH / EU, normal/none, ห้องเย็น/ไม่ห้องเย็น)
     */
    protected static array $rules = [];

    /**
     * เตรียม rule (cache ครั้งเดียว)
     */
    protected static function initRules(): void
    {
        if (!empty(self::$rules)) {
            return;
        }

        // ------------------------------------
        // 1) Base codes สำหรับเรือแบบปกติ
        // ------------------------------------
        $allCodesNoCold = [
            // 1.x
            '1_1','1_2','1_3','1_4','1_5','1_6','1_7',
            // 2.x
            '2_1','2_2','2_3','2_4','2_5','2_6',
            // 3.x
            '3_1','3_2','3_3','3_4','3_5',
            // 4.x
            '4_1','4_2','4_3','4_4',
            // 5.x
            '5_1','5_2','5_3','5_4','5_5','5_6','5_7',
        ];

        $allCodesCold = array_merge($allCodesNoCold, ['5_8','5_9']);

        // ------------------------------------
        // 2) กลุ่ม none แบบไทย (ข้อ 17)
        // ------------------------------------
        $codes_TH_none_noCold = [
            // 1.x
            '1_1','1_2','1_3','1_4','1_5','1_6','1_7',
            // 2.x
            '2_1','2_2','2_3','2_4','2_5','2_6',
            // 4.x เฉพาะข้อที่ไม่ยกเว้น
            '4_2','4_4',
            // 5.x
            '5_1',
        ]; // = 16 ข้อ

        // none + cold แบบไทยต้องตรวจ 5_8,5_9 ด้วย
        $codes_TH_none_cold = array_merge($codes_TH_none_noCold, ['5_8','5_9']);

        // ------------------------------------
        // 3) กลุ่ม none แบบ EU (ข้อ 18)
        //    ยกเว้น 5_8, 5_9 ด้วย
        // ------------------------------------
        $codes_EU_none_noCold = $codes_TH_none_noCold;
        $codes_EU_none_cold   = $codes_TH_none_noCold; // 5_8,5_9 ถูกยกเว้น → ใช้ชุดเดิม

        // ------------------------------------
        // 4) รวมเป็น rule กลาง (ใช้ inspection_form_type)
        // inspection_form_type = 1 → TH
        // inspection_form_type = 2 → EU
        // ------------------------------------
        self::$rules = [
            'TH' => [
                'normal' => [
                    0 => [
                        'required' => ['1_1','2_1','3_1','4_1','5_1'],
                        'codes'    => $allCodesNoCold,
                        'min_pass' => 18,
                    ],
                    1 => [
                        'required' => ['1_1','2_1','3_1','5_1','5_8','5_9'],
                        'codes'    => $allCodesCold,
                        'min_pass' => 19,
                    ],
                ],
                'none' => [
                    0 => [
                        'required' => ['1_1','2_1','5_1'],
                        'codes'    => $codes_TH_none_noCold,
                        'min_pass' => 10,
                    ],
                    1 => [
                        'required' => ['1_1','2_1','5_1','5_8','5_9'],
                        'codes'    => $codes_TH_none_cold,
                        'min_pass' => 11,
                    ],
                ],
            ],
            'EU' => [
                'normal' => [
                    0 => [
                        'required' => ['1_1','2_1','3_1','4_1','5_1'],
                        'codes'    => $allCodesNoCold,
                        'min_pass' => 18,
                    ],
                    1 => [
                        'required' => ['1_1','2_1','3_1','5_1','5_8','5_9'],
                        'codes'    => $allCodesCold,
                        'min_pass' => 19,
                    ],
                ],
                'none' => [
                    0 => [
                        'required' => ['1_1','2_1','5_1'],
                        'codes'    => $codes_EU_none_noCold,
                        'min_pass' => 10,
                    ],
                    1 => [
                        // EU: 5_8,5_9 ถูกยกเว้นแล้ว
                        'required' => ['1_1','2_1','5_1'],
                        'codes'    => $codes_EU_none_cold,
                        'min_pass' => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * map code → object
     */
    protected static function mapObj(
        string $code,
        $structure,
        $material,
        $crew,
        $water_ice,
        $preservation
    ) {
        switch (substr($code, 0, 1)) {
            case '1': return $structure;
            case '2': return $material;
            case '3': return $crew;
            case '4': return $water_ice;
            case '5': return $preservation;
            default:  return null;
        }
    }

    /**
     * get status_x_x อย่างปลอดภัย
     */
    protected static function getVal($obj, string $code): ?string
    {
        if (!is_object($obj)) return null;
        $field = 'status_' . $code;
        return property_exists($obj, $field) ? $obj->$field : null;
    }

    /**
     * ฟังก์ชันหลักประเมินผล
     */
    public static function check_vessel_pass($request_id, string $mode = 'commercial'): bool|string
    {
        $request = InspectionRequest::find_by_id($request_id);
        if (!$request) return 'ไม่พบคำขอ';

        self::initRules();

        // โหลดแบบฟอร์ม
        $structure    = InspectionFormStructure::find_by_request_id($request_id);
        $material     = InspectionFormMaterial::find_by_request_id($request_id);
        $crew         = InspectionFormCrew::find_by_request_id($request_id);
        $water_ice    = InspectionFormWaterAndIce::find_by_request_id($request_id);
        $preservation = InspectionFormPreservation::find_by_request_id($request_id);

        // ------------------------------------
        // Determine TH/EU from inspection_form_type
        // ------------------------------------
        $certificateType = ($request->inspection_form_type == 2) ? 'EU' : 'TH';

        // license_status
        $licenseMode = ($mode === 'non_permitted' || $request->license_status === 'none')
            ? 'none'
            : 'normal';

        $coldFlag = ((int)($request->cold_room_flag ?? 0) === 1) ? 1 : 0;

        if (!isset(self::$rules[$certificateType][$licenseMode][$coldFlag])) {
            return 'ไม่พบเกณฑ์ประเมินที่ตรงกับประเภทเรือ';
        }

        $rule = self::$rules[$certificateType][$licenseMode][$coldFlag];

        // ------------------------------------
        // 1) เช็ค required
        // ------------------------------------
        foreach ($rule['required'] as $code) {

            $obj = self::mapObj($code, $structure, $material, $crew, $water_ice, $preservation);
            $val = self::getVal($obj, $code);

            if ($val !== 'pass' && $val !== 'fail') {
                return 'incomplete';
            }

            if ($val === 'fail') {
                $request->status = InspectionRequest::STATUS_FAILED;
                if (empty($request->actual_inspect_date) || $request->actual_inspect_date === '0000-00-00') {
                    $request->actual_inspect_date = date('Y-m-d');
                }
                $request->save();
                return "ไม่ผ่านเกณฑ์เพราะข้อหลัก {$code} ไม่ผ่าน";
            }
        }

        // ------------------------------------
        // 2) นับ pass ทั้งหมด
        // ------------------------------------
        $pass_count = 0;
        $answered   = 0;

        foreach ($rule['codes'] as $code) {
            $obj = self::mapObj($code, $structure, $material, $crew, $water_ice, $preservation);
            $val = self::getVal($obj, $code);

            if ($val === 'pass') {
                $pass_count++;
                $answered++;
            } elseif ($val === 'fail') {
                $answered++;
            }
        }

        if ($answered === 0) {
            return 'incomplete';
        }

        // ตั้งวันตรวจจริง
        if (empty($request->actual_inspect_date) || $request->actual_inspect_date === '0000-00-00') {
            $request->actual_inspect_date = date('Y-m-d');
        }

        $totalCodes = count($rule['codes']);
        $minPass    = $rule['min_pass'];

        // ------------------------------------
        // 3) ตัดสินผล
        // ------------------------------------
        if ($pass_count >= $totalCodes) {
            $request->status = InspectionRequest::STATUS_PASSED;
            $request->save();
            return true;
        }

        if ($pass_count >= $minPass) {
            $request->status = InspectionRequest::STATUS_CONDITIONAL;
            $request->save();
            return true;
        }

        $request->status = InspectionRequest::STATUS_FAILED;
        $request->save();
        return "ไม่ผ่านเกณฑ์เพราะจำนวนข้อผ่าน ({$pass_count}) น้อยกว่าเกณฑ์ขั้นต่ำ ({$minPass})";
    }

    /**
     * ใช้บนฟอร์มเพื่อเช็คว่าข้อนี้ถูก "ยกเว้น"
     * true = ควร disabled + แสดงข้อความยกเว้น
     */
    public static function is_exempt(InspectionRequest $request, string $code, string $mode = 'commercial'): bool
    {
        self::initRules();

        $certificateType = ($request->inspection_form_type == 2) ? 'EU' : 'TH';
        $licenseMode     = ($mode === 'non_permitted' || $request->license_status === 'none')
            ? 'none'
            : 'normal';
        $coldFlag        = ((int)($request->cold_room_flag ?? 0) === 1) ? 1 : 0;

        // normal → ไม่ยกเว้น
        if ($licenseMode === 'normal') return false;

        if (!isset(self::$rules[$certificateType][$licenseMode][$coldFlag])) {
            return false;
        }

        $rule = self::$rules[$certificateType][$licenseMode][$coldFlag];

        return !in_array($code, $rule['codes'], true);
    }
}
