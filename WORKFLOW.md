# FVSCIS — Workflow Analysis: `inspection_requests` ↔ `fv_sanitation_certification_old`

> วิเคราะห์แบบ read-only จาก source code จริงเท่านั้น ไม่มีการแก้ไข source/database ในรอบนี้ จุดใดยืนยันจาก source ไม่ได้ ระบุว่า **"ยังไม่ยืนยัน"** อย่างชัดเจน

---

## 1. ตาราง `inspection_requests`

**คลาส:** [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php)
**Primary Key:** `id`

### หน้าที่ของตาราง
เก็บ "คำขอตรวจสุขอนามัยเรือประมง" 1 คำขอ = 1 รอบการยื่น/ตรวจ/อนุมัติ ตั้งแต่ยื่นคำขอจนถึงผลการอนุมัติ เป็นตารางศูนย์กลางของ workflow

### Field ทั้งหมด และความหมายจากการใช้งานจริง (อ้างอิงบรรทัด 12-38 ของไฟล์คลาส)

| Field | ความหมายจากการใช้งานจริง | หลักฐาน |
|---|---|---|
| `id` | PK | ทุกไฟล์ |
| `ship_code` | เลขทะเบียนเรือ ใช้เป็น key เชื่อมกับ e-License และกับ `fv_sanitation_certification_old` | [request_inspection.php](public/fisherman/ajax/request_inspection.php) |
| `vessel_name`, `vessel_mark`, `license_number`, `license_status`, `gear_type`, `owner_name` | ข้อมูลเรือ/ใบอนุญาต คัดลอกมาจาก e-License ตอนยื่นคำขอ (`Elicense::find_one_by_ship_code`) ไม่ใช่ live-reference | [request_inspection.php L76-83](public/fisherman/ajax/request_inspection.php) |
| `contact_phone` | เบอร์ติดต่อผู้ยื่น ตรวจรูปแบบ 9-10 หลัก | [request_inspection.php L44](public/fisherman/ajax/request_inspection.php) |
| `department_id` | หน่วยงาน(เจ้าหน้าที่)ที่รับผิดชอบตรวจ | [request_inspection.php](public/fisherman/ajax/request_inspection.php) |
| `department_group_id` | กลุ่มหน่วยงาน (parent ของ department) ดึงจาก `Department::get_department_group_id()` ใช้กรองสิทธิ์เห็นคำขอของเจ้าหน้าที่/ผู้ลงนาม | [request_inspection.php L91-92](public/fisherman/ajax/request_inspection.php) |
| `data_owner_id` | เจ้าของข้อมูล (ยังไม่ยืนยันความหมายเชิงธุรกิจที่แน่ชัด นอกจาก assign จาก `Department::get_department_group_id()->data_owner_id`) | ยังไม่ยืนยัน |
| `port_province_id/port_amphur_id/port_tambon_id/port_license_no/port_name` | ที่ตั้งท่าเทียบเรือที่จะให้ตรวจ, `port_name` ดึงจาก `ElicensePort` | [request_inspection.php](public/fisherman/ajax/request_inspection.php) |
| `inspect_date_start/inspect_date_end` | ช่วงวันที่ชาวประมง "เสนอ" ให้ตรวจ | [request_inspection.php](public/fisherman/ajax/request_inspection.php) |
| `confirmed_inspect_date` | วันที่เจ้าหน้าที่ "กำหนด/เสนอ" วันตรวจจริง | [confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php) |
| `is_confirm` | flag ว่าชาวประมงยืนยันวันนัดตรวจ (ที่เจ้าหน้าที่กำหนด) แล้วหรือยัง (0/1) | [confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php) |
| `confirm_agreement` | ผู้ยื่นยอมรับเงื่อนไข ตอนสร้างคำขอครั้งแรก (checkbox) | [request_inspection.php L38, L100](public/fisherman/ajax/request_inspection.php) |
| `inspection_form_type` | 1 = มาตรฐานทั่วไป(TH), 2 = EU — กำหนดชุดเกณฑ์ประเมิน | [InspectionEvaluation.class.php L178](private/classes/InspectionEvaluation.class.php) |
| `cold_room_flag` | 0/1 มีห้องเย็นหรือไม่ — มีผลต่อจำนวนข้อที่ต้องตรวจ (5.8-5.9) | [InspectionEvaluation.class.php](private/classes/InspectionEvaluation.class.php) |
| `status` | ค่าควบคุม workflow หลัก (ดูตารางค่า status ด้านล่าง) | ทั้งไฟล์ |
| `is_manual_case` | 0 = ชาวประมงยื่นเอง, 1 = เจ้าหน้าที่สร้างคำขอแทน (manual case) — มีผลต่อวิธีหา `fisherman_id` ตอน insert ลง `fv_sanitation_certification_old` | [approve_request.php L131-146](public/signer/ajax/approve_request.php) |
| `is_submitted` / `submitted_at` | เจ้าหน้าที่ตรวจครบ 5 ฟอร์มแล้ว "ส่งผลตรวจ"/ล็อกฟอร์ม | [generate_pdf.php](public/inspectofficer/generate_pdf.php) (ยืนยันว่ามีการ set แต่ยังไม่ได้ไล่ทุกบรรทัดในรอบนี้ — ตำแหน่ง set ที่แน่ชัด **ยังไม่ยืนยันครบ 100%**) |
| `created_at/updated_at/created_by/updated_by/created_ip/updated_ip` | audit ทั่วไป (auto-set ใน `DatabaseObject::save()`) | [databaseobject.class.php L114-131](private/classes/databaseobject.class.php) |
| `approved_by/approved_at/approved_ip` | ผู้ลงนามอนุมัติ, เวลา/IP ที่อนุมัติ | [approve_request.php](public/signer/ajax/approve_request.php), [confirm_fail.php](public/signer/ajax/confirm_fail.php) |
| `effective_date` | วันที่ใบรับรองมีผล (ผู้ลงนามกรอกตอนอนุมัติ) | [approve_request.php](public/signer/ajax/approve_request.php) |
| `expire_at` | วันหมดอายุใบรับรอง คำนวณจาก `effective_date` (+2 ปี ถ้า passed, +90 วัน ถ้า conditional, = effective_date ถ้า failed) | [approve_request.php L74-86](public/signer/ajax/approve_request.php) |
| `approval_note` | หมายเหตุจากผู้ลงนาม | [approve_request.php](public/signer/ajax/approve_request.php) |
| `temporary_reason` | เหตุผลกรณีออกใบชั่วคราว (conditional) | [approve_request.php](public/signer/ajax/approve_request.php) |
| `actual_inspect_date` | วันที่ตรวจจริง ตั้งค่าตอนประเมินผล (`InspectionEvaluation::check_vessel_pass`) | [InspectionEvaluation.class.php L196, 227](private/classes/InspectionEvaluation.class.php) |
| `is_complete` | ผู้ลงนามอนุมัติแล้ว (set = 1 ใน `approve_request.php` เท่านั้น) — **ไม่ถูก set ใน `confirm_fail.php`** (ดูจุดกำกวมข้อ 1) | [approve_request.php L120](public/signer/ajax/approve_request.php) |

### ค่า `status` ที่เป็นไปได้ (constants)

| ค่า | ความหมาย | จุดที่ set |
|---|---|---|
| `pending` | ยื่นคำขอแล้ว รอเจ้าหน้าที่นัด/ยืนยันวันตรวจ | สร้างคำขอ ([request_inspection.php L104](public/fisherman/ajax/request_inspection.php)), หรือ reset กลับเมื่อเจ้าหน้าที่เปลี่ยนวันนัดและต้องให้ชาวประมงยืนยันใหม่ ([confirm_inspect_date.php L52-53](public/inspectofficer/ajax/confirm_inspect_date.php)) |
| `cancelled` | ยกเลิกคำขอ — **ยังไม่พบจุดที่ set ค่านี้จริงในโค้ดที่ตรวจสอบ** (การ "ยกเลิก" ที่พบจริงคือการ hard delete แถวทิ้งผ่าน [delete_request.php](public/fisherman/ajax/delete_request.php) ไม่ใช่ set status='cancelled') → ดู **Unreachable Status** ข้อ 1 |
| `inspecting` | ชาวประมงยืนยันวันนัดแล้ว อยู่ระหว่างเจ้าหน้าที่ตรวจ | [confirm_by_fisherman.php L21](public/fisherman/ajax/confirm_by_fisherman.php), และตั้งตรงตอนเจ้าหน้าที่สร้างคำขอเอง ([create_manual_request_by_officer.php L90](public/inspectofficer/ajax/create_manual_request_by_officer.php), [inspectofficer/ajax/request_inspection.php L104](public/inspectofficer/ajax/request_inspection.php)) |
| `passed` | ตรวจครบ 5 ฟอร์ม ผ่านทุกข้อ | [InspectionEvaluation.class.php L227-229](private/classes/InspectionEvaluation.class.php) |
| `failed` | ตรวจแล้วไม่ผ่านเกณฑ์ขั้นต่ำ หรือข้อ required ข้อใดข้อหนึ่ง fail | [InspectionEvaluation.class.php L196-201, L235-236](private/classes/InspectionEvaluation.class.php) |
| `conditional` | ผ่านแบบมีเงื่อนไข (ผ่านเกณฑ์ขั้นต่ำแต่ไม่ครบทุกข้อ) → ใบรับรองชั่วคราว 90 วัน | [InspectionEvaluation.class.php L230-233](private/classes/InspectionEvaluation.class.php) |
| `completed` | ตามคอมเมนต์ในคลาส (`//is_complete === 1`) ควรหมายถึง "ผู้ลงนามอนุมัติเสร็จสิ้น" | **set จริงเฉพาะกรณี "ไม่ผ่าน" ผ่าน [confirm_fail.php L56](public/signer/ajax/confirm_fail.php)** — กรณี `passed`/`conditional` ที่อนุมัติผ่าน [approve_request.php](public/signer/ajax/approve_request.php) **ไม่มีการ set `status = 'completed'` เลย** (ดูจุดกำกวมข้อ 1 และ Unreachable Status ข้อ 2) |

---

## 2. ตาราง `fv_sanitation_certification_old`

**คลาส:** [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php)
**Primary Key:** `id`

### หน้าที่ของตาราง
เก็บ "ประวัติ/สถานะใบรับรอง" ของเรือแต่ละลำ (ต่อ `ship_code`) แม้ชื่อคลาส/ตารางจะมีคำว่า "Old" แต่จากโค้ดที่ยัง insert เข้าตารางนี้อยู่ต่อเนื่อง ((`approve_request.php`, `confirm_fail.php`, `create_fvscisold.php`, `fvscis_old_create.php`) สรุปว่า**ยังเป็นตารางหลักที่ใช้งานจริงในปัจจุบัน** ไม่ใช่ตารางที่เลิกใช้แล้ว

### Field ทั้งหมด (จาก `$db_columns` บรรทัด 6-11 ของไฟล์คลาส)

| Field | ความหมายจากการใช้งานจริง | หลักฐาน |
|---|---|---|
| `id` | PK | - |
| `vessel_name`, `ship_code`, `vessel_mark`, `license_number`, `license_status`, `gear_type`, `owner_name` | คัดลอกมาจาก `inspection_requests` ตอน insert (ไม่มี FK เชื่อมตรง เป็นการ copy ค่า) | [approve_request.php L131-149](public/signer/ajax/approve_request.php) |
| `fisherman_id` | เชื่อมไปยัง `fisherman.id` — วิธีหาค่าต่างกัน 2 แบบ: ถ้า `is_manual_case==0` ใช้ `request->created_by` ตรง ๆ, ถ้า `is_manual_case==1` ไป query e-License ด้วย `ship_code` แล้วหา `Fisherman::find_by_citizen_id()` — ถ้าหาไม่เจอทั้งสองทาง **ไม่ set ค่าเลย (เป็น NULL)** | [approve_request.php L131-146](public/signer/ajax/approve_request.php) |
| `certificate_number` | เลขใบรับรอง สร้างจาก `DocumentCounter::next_code_by_effective()` เฉพาะกรณี passed/conditional; กรณี failed ผ่าน `confirm_fail.php` จะเป็น `null` | [approve_request.php L102-104](public/signer/ajax/approve_request.php), [confirm_fail.php L84](public/signer/ajax/confirm_fail.php) |
| `request_date` | วันที่ยื่นคำขอ — **ไม่สอดคล้องกันระหว่าง 2 endpoint**: `approve_request.php` ใช้ `$request->submitted_at`, แต่ `confirm_fail.php` ใช้ `$request->created_at` (ดูจุดกำกวมข้อ 2) | [approve_request.php L155](public/signer/ajax/approve_request.php), [confirm_fail.php L86](public/signer/ajax/confirm_fail.php) |
| `signature_date` | วันที่ผู้ลงนามอนุมัติ (= `request->approved_at`) | ทั้งสองไฟล์ |
| `effective_date` | วันเริ่มมีผล (= ค่าที่ผู้ลงนามกรอก) | ทั้งสองไฟล์ |
| `expiration_date` | วันหมดอายุ (`null` กรณี failed) | ทั้งสองไฟล์ |
| `status` | สถานะควบคุมของ "ใบรับรองล่าสุดต่อเรือ" (ดูตารางค่าด้านล่าง) — **คนละชุดค่ากับ `inspection_requests.status`** | ทั้งไฟล์ |
| `vessel_status` | เก็บค่า `inspection_requests.status` ณ ตอนที่ insert (snapshot) — เป็นข้อมูลซ้ำซ้อนกับตาราง request (ดู Duplicate Responsibility ข้อ 1) | [approve_request.php L168](public/signer/ajax/approve_request.php), [confirm_fail.php L97](public/signer/ajax/confirm_fail.php) |
| `evaluation_agency` | = `department_id` ของคำขอ (คอมเมนต์ในคลาสระบุ "Department") | [FvSanitationCertificationOld.class.php L28](private/classes/FvSanitationCertificationOld.class.php) |
| `signing_unit` | = `department_group_id` ของคำขอ (คอมเมนต์ระบุ "DepartmentGroup") | เดียวกัน |
| `responsible_unit` | = `DepartmentGroup->responsible_unit` (คอมเมนต์ระบุ "DepartmentGroup") | [approve_request.php L165](public/signer/ajax/approve_request.php) |
| `temporary_reason` | เหตุผลใบชั่วคราว (คัดลอกจาก request) | [approve_request.php](public/signer/ajax/approve_request.php) |
| `certificate_status` | ป้ายข้อความสถานะใบรับรองที่แสดงผล เช่น `สร.3`, `สร.3 EU`, `สร.3 ชั่วคราว`, `สร.3 EU ชั่วคราว`, `ไม่ผ่าน` | [approve_request.php L70-84](public/signer/ajax/approve_request.php) |
| `remark` | หมายเหตุ (= `approval_note`) | ทั้งไฟล์ |
| `type` | `1` = ออกผ่านระบบออนไลน์ (online); ค่า `0`/อื่น ๆ ใช้ในกรณี manual entry ผ่าน [create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) (ใบเก่าที่บันทึกย้อนหลังโดยเจ้าหน้าที่ ไม่ผูกกับ `inspection_requests` เลย) | [approve_request.php L177](public/signer/ajax/approve_request.php), [create_fvscisold.php L29-31](public/inspectofficer/ajax/create_fvscisold.php) |

### ค่า `status` ที่พบในตาราง `fv_sanitation_certification_old` (คนละความหมายกับ `inspection_requests.status`)

| ค่า | ความหมาย | จุดที่ set |
|---|---|---|
| `active` | ใบรับรองยังใช้งานได้ (ผ่าน/ผ่านมีเงื่อนไข และยังไม่หมดอายุ) | [approve_request.php L74, L80](public/signer/ajax/approve_request.php), `mark_active()` |
| `inactive` | ใบรับรองหมดอายุ/ไม่ใช้แล้ว | `mark_inactive()`, [confirm_fail.php L129 (UPDATE ... status='inactive')](public/signer/ajax/confirm_fail.php) |
| `fail` | ผลตรวจไม่ผ่าน | [confirm_fail.php L114](public/signer/ajax/confirm_fail.php), `mark_fail()` |
| `pending` | มีคำขอใหม่กำลังดำเนินการอยู่สำหรับเรือลำนี้ (ตั้งตอนยื่นคำขอใหม่ ผ่าน `mark_pending()`) — ใช้เพื่อไม่ให้ใบเก่าโชว์เป็น active ระหว่างรอผลใหม่ | [request_inspection.php L138 `FvSanitationCertificationOld::mark_pending($ship_code)`](public/fisherman/ajax/request_inspection.php) |

---

## 3. Flow ระหว่างสองตาราง

### เมื่อไหร่แต่ละตารางถูกสร้าง
- **`inspection_requests`** ถูกสร้างเมื่อ: (ก) ชาวประมงยื่นคำขอเอง ([request_inspection.php](public/fisherman/ajax/request_inspection.php), `is_manual_case=0`) หรือ (ข) เจ้าหน้าที่สร้างคำขอแทน ([create_manual_request_by_officer.php](public/inspectofficer/ajax/create_manual_request_by_officer.php), [inspectofficer/ajax/request_inspection.php](public/inspectofficer/ajax/request_inspection.php), `is_manual_case=1`)
- **`fv_sanitation_certification_old`** ถูกสร้างเมื่อ: (ก) ผู้ลงนามอนุมัติผลผ่าน/มีเงื่อนไข ([approve_request.php](public/signer/ajax/approve_request.php)) (ข) ผู้ลงนามยืนยันผลไม่ผ่าน ([confirm_fail.php](public/signer/ajax/confirm_fail.php)) (ค) เจ้าหน้าที่บันทึกใบรับรองเก่าย้อนหลังด้วยมือ ผ่าน [create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php)/[fvscis_old_create.php](public/inspectofficer/ajax/fvscis_old_create.php) — เส้นทางนี้**ไม่ผ่าน `inspection_requests` เลย** (ดู Missing Link ข้อ 1)

### Field ที่ใช้เชื่อมสองตาราง
**ไม่มี Foreign Key ที่แท้จริงในระดับ schema** (ยังไม่ยืนยันจาก DDL แต่ในระดับ PHP ORM ไม่พบ field `request_id` ใน `fv_sanitation_certification_old`) ความสัมพันธ์ทำผ่าน **`ship_code` (string)** เท่านั้น — เป็น "soft join" ที่พึ่งพาความถูกต้องของสตริงทะเบียนเรือ ไม่ใช่ id เชิงตัวเลข

### มีการ copy/sync ข้อมูลหรือไม่
มี — เป็นการ **copy ค่าแบบ one-way (snapshot)** ตอน insert เท่านั้น (`vessel_name, ship_code, vessel_mark, license_number, gear_type, owner_name, evaluation_agency, signing_unit, responsible_unit, temporary_reason, vessel_status` ฯลฯ) ไม่มีการ sync กลับหรืออัปเดตอัตโนมัติภายหลังหาก `inspection_requests` เปลี่ยนแปลงอีก ([approve_request.php](public/signer/ajax/approve_request.php), [confirm_fail.php](public/signer/ajax/confirm_fail.php))

### เหตุการณ์ที่ทำให้ `inspection_requests.status` เปลี่ยน
`pending → inspecting → (passed|failed|conditional) → completed(เฉพาะ path failed เท่านั้นที่ยืนยันได้)` ตามรายละเอียดในตารางค่า status ข้อ 1

### เหตุการณ์ที่ทำให้ `fv_sanitation_certification_old.status` เปลี่ยน
สร้างแถวใหม่ทุกครั้งที่มีการอนุมัติ/ยืนยันผล (ไม่ใช่ UPDATE แถวเดิม) โดยแถวใหม่จะมี `status` ตามผล (`active`/`fail`) ส่วนแถวเก่าของเรือลำเดียวกันจะถูก "mark" เปลี่ยนสถานะแยกต่างหากผ่าน `mark_pending() / mark_fail() / mark_active() / mark_inactive()` เมื่อมีการยื่นคำขอใหม่หรือมีการลบคำขอ

### ขั้นตอนใดคือแต่ละ stage

| Stage | ไฟล์ | เปลี่ยน field ใด |
|---|---|---|
| ยื่นคำขอ | [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php) | INSERT `inspection_requests` (`status=pending`), `FvSanitationCertificationOld::mark_pending()` |
| นัดตรวจ | [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php) | UPDATE `confirmed_inspect_date` |
| ยืนยันนัดโดยชาวประมง | [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php) | UPDATE `is_confirm=1`, `status=inspecting` |
| ตรวจ (กรอก 5 ฟอร์ม) | `public/inspectofficer/form_*.php`, `ajax/autosave_*.php`, คลาส `InspectionForm*` | INSERT/UPDATE ตาราง `inspection_form_*`, `inspection_form_status` |
| ประเมินผล | [private/classes/InspectionEvaluation.class.php](private/classes/InspectionEvaluation.class.php) `check_vessel_pass()` | UPDATE `inspection_requests.status` → passed/failed/conditional, `actual_inspect_date` |
| อนุมัติ (ผ่าน/มีเงื่อนไข) | [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) | UPDATE `inspection_requests` (`approved_by/at/effective_date/expire_at/is_complete=1`), INSERT `fv_sanitation_certification_old` |
| ยืนยันผลไม่ผ่าน | [public/signer/ajax/confirm_fail.php](public/signer/ajax/confirm_fail.php) | UPDATE `inspection_requests` (`status=completed`, `approved_*`), INSERT `fv_sanitation_certification_old` |
| ออกใบรับรองแล้ว | มี `certificate_number` ที่ไม่เป็น null ใน `fv_sanitation_certification_old` (เฉพาะกรณี passed/conditional) | [approve_request.php](public/signer/ajax/approve_request.php) |
| workflow จบสมบูรณ์ | **กำกวม** — ตามคอมเมนต์ควรหมายถึง `inspection_requests.status == 'completed'` แต่ในทางปฏิบัติ (สำหรับกรณีอนุมัติผ่าน) จะไม่มีวันถึงค่านี้ ผู้ที่ใช้จริงคือ `is_complete == 1` | ดู Unreachable Status ข้อ 2 |

### สรุป Flow หลัก

```
ยื่นคำขอ (pending)
   → นัดตรวจ (confirmed_inspect_date ถูกกำหนด, status ยังเป็น pending)
   → ชาวประมงยืนยันนัด (status = inspecting)
   → ตรวจ 5 ฟอร์ม (inspection_form_structure/material/crew/water_and_ice/preservation, inspection_form_status)
   → ประเมินผลอัตโนมัติ (status = passed / conditional / failed)
   → อนุมัติ:
        - passed/conditional → INSERT fv_sanitation_certification_old (status=active, มี certificate_number)
                                → inspection_requests.is_complete = 1 (แต่ status ไม่เปลี่ยนเป็น completed — ดูปัญหาข้อ 1)
        - failed → ผู้ลงนามกด "ยืนยันไม่ผ่าน" → inspection_requests.status = completed
                    → INSERT fv_sanitation_certification_old (status=fail, certificate_number=null)
   → สถานะสุดท้าย: ใบรับรอง active ใน fv_sanitation_certification_old (กรณีผ่าน)
                    หรือ inspection_requests.status=completed + fv_sanitation_certification_old.status=fail (กรณีไม่ผ่าน)
```

---

## 4. รายงานปัญหาที่พบ

### ปัญหาที่ 1 — `status` ไม่เปลี่ยนเป็น `completed` เมื่ออนุมัติผ่าน/มีเงื่อนไข (จุดกำกวม / จุดขาดตอน / Unreachable Status)
1. **สิ่งที่พบ:** ใน [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) เมื่อผู้ลงนามอนุมัติคำขอที่มีผล `passed` หรือ `conditional` โค้ดจะ set `$request->is_complete = 1` และ `$request->result = $request->status;` (ซึ่ง `result` ไม่ได้อยู่ใน `$db_columns` ของ `InspectionRequest` เลย จึงไม่ถูกบันทึกลงฐานข้อมูลเลย — เป็น dead assignment) แต่**ไม่มีบรรทัดใดที่ set `$request->status = InspectionRequest::STATUS_COMPLETED`** ต่างจาก [public/signer/ajax/confirm_fail.php](public/signer/ajax/confirm_fail.php) บรรทัด 56 ที่ set `$request->status = 'completed';` อย่างชัดเจนสำหรับกรณีไม่ผ่าน
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** อนุมัติ → ออกใบรับรอง → workflow จบสมบูรณ์
3. **ตาราง/field:** `inspection_requests.status`, `inspection_requests.is_complete`
4. **ไฟล์หลักฐาน:** [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) (บรรทัดรอบ 113-121), [public/signer/ajax/confirm_fail.php](public/signer/ajax/confirm_fail.php) (บรรทัด 56), [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php) (คอมเมนต์ `STATUS_COMPLETED  = 'completed'; //is_complete === 1`)
5. **ผลกระทบต่อการใช้งานจริง:**
   - UI หลายจุดสลับ badge/ลิงก์ด้วยการเทียบ `$req->status === InspectionRequest::STATUS_COMPLETED` เช่นแสดง badge เขียว "อนุมัติ" พร้อมลิงก์ไป `certificate_preview.php` ใน [public/signer/incoming_requests.php บรรทัด 136](public/signer/incoming_requests.php) — เงื่อนไขนี้จะ**ไม่มีวันเป็นจริง**สำหรับคำขอที่ผ่าน/ผ่านมีเงื่อนไข เพราะ status ค้างเป็น `passed`/`conditional` ตลอดไป
   - ปุ่ม "อนุมัติ" ใน [public/signer/incoming_requests.php](public/signer/incoming_requests.php) (JS `loadRequestDetail`) จะ disable ก็ต่อเมื่อ `req.status === 'completed'` เท่านั้น เนื่องจาก path ที่ผ่านจะไม่ถึงค่านี้ **ปุ่มอนุมัติจะยังกดซ้ำได้แม้อนุมัติไปแล้ว** ซึ่งจะไป INSERT แถวใหม่ใน `fv_sanitation_certification_old` ซ้ำอีกครั้ง (ดูปัญหาข้อ 2 — Must Fix)
   - ฝั่งเจ้าหน้าที่ [public/inspectofficer/incoming_requests.php](public/inspectofficer/incoming_requests.php) ใช้ `is_complete` แยกต่างหาก (`$isComplete = ($req->is_complete == 1)`) เพื่อควบคุมปุ่มเปิด PDF — จึงทำงานถูกต้องเฉพาะหน้านี้ แต่หน้าอื่น (`signer/incoming_requests.php`, `signer/inspection_requests.php`, `fisherman/mystatus.php`) ไม่ได้เช็ค `is_complete` เลย
6. **ระดับความมั่นใจ:** ยืนยันได้ (ตรวจโค้ดตรงจุด set ค่าและจุดใช้ค่าทั้งสองฝั่งแล้ว)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ตรวจสอบ DB จริงว่ามีคำขอที่ `status IN ('passed','conditional')` และ `is_complete = 1` ค้างอยู่จำนวนเท่าใด (บ่งชี้ผลกระทบจริงในโปรดักชัน) — ยังไม่ยืนยัน เพราะรอบนี้ไม่แตะฐานข้อมูล

### ปัญหาที่ 2 — ไม่มีการป้องกันอนุมัติซ้ำ (Duplicate Responsibility / Must Fix เชิงข้อมูล)
1. **สิ่งที่พบ:** [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) ตรวจสอบเงื่อนไขก่อนอนุมัติเพียง `in_array($evaluation_status, [STATUS_PASSED, STATUS_CONDITIONAL])` เท่านั้น ไม่ได้เช็คว่า `approved_by`/`approved_at`/`is_complete` มีค่าอยู่แล้วหรือไม่ เมื่อรวมกับปัญหาข้อ 1 (status ไม่เปลี่ยนเป็น completed) ทำให้เงื่อนไขนี้เป็นจริงซ้ำได้ไม่จำกัดจำนวนครั้ง
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** อนุมัติ → ออกใบรับรอง
3. **ตาราง/field:** `inspection_requests.status/is_complete`, `fv_sanitation_certification_old` (ทั้งแถว, โดยเฉพาะ `certificate_number`)
4. **ไฟล์หลักฐาน:** [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) บรรทัด 44
5. **ผลกระทบต่อการใช้งานจริง:** เสี่ยงออกเลขที่ใบรับรอง (`certificate_number`) ซ้ำซ้อนหลายใบต่อการอนุมัติ 1 ครั้งจริง ถ้าผู้ใช้กดปุ่มซ้ำ (double-click) หรือเปิดหลายแท็บ — ข้อมูลใน `fv_sanitation_certification_old` จะมีหลายแถว `status=active` สำหรับ `ship_code` เดียวกันในเวลาใกล้กัน ก่อนที่ logic "ล่าสุดต่อเรือ" (`find_by_ship_code`, `sql_latest_per_ship`) จะเลือกแถวล่าสุดมาแสดง ทำให้แถวเก่าที่ควร inactive กลายเป็นข้อมูลค้าง (orphan-like)
6. **ระดับความมั่นใจ:** มีแนวโน้ม (มั่นใจสูงในเชิง logic แต่ยังไม่ยืนยันว่ามี double-submit ในโปรดักชันจริงหรือไม่ เพราะอาจมี debounce ฝั่ง JS ที่ไม่ได้ตรวจครบทุกจุด)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ตรวจสอบฝั่ง JS ของปุ่ม submit ว่ามี disable-on-click ป้องกันหรือไม่ (ตรวจแล้วบางส่วนใน [incoming_requests.php](public/signer/incoming_requests.php) พบว่า disable อาศัย `req.status==='completed'` ซึ่งไม่ทำงานตามปัญหาข้อ 1) — ยังไม่ยืนยันว่ามี debounce อื่นเพิ่มเติมในไฟล์ JS หลัก [public/js/fvscis.js](public/js/fvscis.js)

### ปัญหาที่ 3 — Missing Link: บันทึกใบรับรองเก่าด้วยมือ ไม่ผูกกับ `inspection_requests`
1. **สิ่งที่พบ:** [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) และ [public/inspectofficer/ajax/fvscis_old_create.php](public/inspectofficer/ajax/fvscis_old_create.php) สร้างแถวใน `fv_sanitation_certification_old` โดยตรงจากฟอร์มที่เจ้าหน้าที่กรอกเอง (`$_POST['FvSanitationCertificationOld']`) ไม่มีการอ้างอิงหรือสร้าง `inspection_requests` คู่กันเลย
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** ออกใบรับรอง (เส้นทางคู่ขนานนอก workflow ปกติ)
3. **ตาราง/field:** `fv_sanitation_certification_old` ทั้งแถว (ไม่มี field ใดอ้างถึง `inspection_requests.id`)
4. **ไฟล์หลักฐาน:** [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) บรรทัด 20-45
5. **ผลกระทบต่อการใช้งานจริง:** ตั้งใจให้ใช้บันทึก "ใบรับรองเก่า/กระดาษ" ย้อนหลัง (ตามชื่อไฟล์ old) จึงคาดว่าเป็นพฤติกรรมที่ตั้งใจออกแบบไว้ ไม่ใช่บั๊ก แต่ทำให้ `fv_sanitation_certification_old` มีข้อมูล 2 ที่มา (จาก workflow ออนไลน์ กับจากการกรอกมือ) ปะปนกันในตารางเดียว ต้องระวังเวลาวิเคราะห์/รายงานสถิติ
6. **ระดับความมั่นใจ:** ยืนยันได้ (โค้ดยืนยันชัดเจนว่าไม่มีการเชื่อมกับ inspection_requests)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ยืนยันเจตนาการออกแบบกับเจ้าของระบบว่าฟีเจอร์นี้ตั้งใจใช้เฉพาะกรณีนำเข้าข้อมูลย้อนหลังจริงหรือไม่ — ยังไม่ยืนยัน

### ปัญหาที่ 4 — Orphan Data Risk: ลบ `inspection_requests` ไม่ลบข้อมูลฟอร์มตรวจที่เกี่ยวข้อง
1. **สิ่งที่พบ:** [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php) ลบแถว `inspection_requests`, `inspection_applicant_info`, `inspection_attachments` แต่**ไม่ลบ**ข้อมูลใน `inspection_form_structure/material/crew/water_and_ice/preservation` หรือ `inspection_form_status` แม้ request นั้นจะเคยถูกยืนยันนัดตรวจ/เริ่มตรวจแล้ว (ไม่มี guard เรื่อง `status` ในไฟล์นี้เลย มีเพียงการเช็คว่าเป็นเจ้าของคำขอ)
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** ยื่นคำขอ → (ลบ) — ตัดตอนกลาง workflow
3. **ตาราง/field:** `inspection_requests.id` ↔ `inspection_form_status.request_id`, `inspection_form_*.request_id` (ยังไม่ยืนยัน FK ชื่อ field ที่แน่นอนในทุกตาราง form เพราะยังไม่ได้เปิดอ่านทุกไฟล์ form ครบ)
4. **ไฟล์หลักฐาน:** [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php) ทั้งไฟล์
5. **ผลกระทบต่อการใช้งานจริง:** ที่ระดับ UI ปุ่มลบ ([public/fisherman/mystatus.php บรรทัด 127-172](public/fisherman/mystatus.php)) แสดงเฉพาะเมื่อ `status === STATUS_PENDING` เท่านั้น จึงลดความเสี่ยงในการใช้งานปกติผ่านหน้าเว็บ แต่ backend เองไม่มีการตรวจสอบสถานะซ้ำ หากมีการเรียก endpoint ตรงหรือ race condition ก็อาจลบคำขอที่มีข้อมูลฟอร์มตรวจไปแล้วได้ ทำให้เกิด orphan record ในตาราง `inspection_form_*`
6. **ระดับความมั่นใจ:** มีแนวโน้ม (backend ยืนยันว่าไม่มี guard, แต่ผลกระทบจริงถูกลดความเสี่ยงด้วย UI — ยังไม่ยืนยันว่ามีเส้นทางอื่นเรียก endpoint นี้ตรงได้หรือไม่)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ตรวจสอบว่ามี endpoint/role อื่นที่เรียกลบคำขอในสถานะอื่นได้หรือไม่ (เฉพาะ fisherman เท่านั้นที่ผ่าน guard `require_role(['fisherman'])`) — ยังไม่ยืนยัน

### ปัญหาที่ 5 — Duplicate Responsibility: `vessel_status` ซ้ำซ้อนกับ `inspection_requests.status`
1. **สิ่งที่พบ:** `fv_sanitation_certification_old.vessel_status` เก็บ snapshot ของ `inspection_requests.status` ตอน insert แต่ไม่มีการ sync ภายหลัง ขณะที่ `fv_sanitation_certification_old.status` (active/inactive/fail/pending) เป็นอีกชุดค่าที่ควบคุม workflow ของตารางนี้เอง ทำให้มี "สถานะ" ที่สื่อความหมายคล้ายกันอยู่ 3 แหล่ง (`inspection_requests.status`, `fv_sanitation_certification_old.status`, `fv_sanitation_certification_old.vessel_status`)
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** อนุมัติ → ออกใบรับรอง → สถานะสุดท้าย
3. **ตาราง/field:** `inspection_requests.status`, `fv_sanitation_certification_old.status`, `fv_sanitation_certification_old.vessel_status`
4. **ไฟล์หลักฐาน:** [public/signer/ajax/approve_request.php บรรทัด 168](public/signer/ajax/approve_request.php), [public/signer/ajax/confirm_fail.php บรรทัด 97](public/signer/ajax/confirm_fail.php)
5. **ผลกระทบต่อการใช้งานจริง:** หากในอนาคตมีการแก้ไข `inspection_requests.status` ภายหลัง (เช่น แก้ไขข้อมูลย้อนหลัง) ค่าที่ snapshot ไว้ใน `vessel_status` จะไม่ตรงกันและอาจทำให้รายงาน/ค้นหาผิดพลาด
6. **ระดับความมั่นใจ:** ยืนยันได้ (เป็นข้อเท็จจริงเชิงโครงสร้างข้อมูลจากโค้ด)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ตรวจสอบว่ามีหน้าจอ/รายงานใดอ่านค่า `vessel_status` ไปแสดงผลจริงหรือไม่ (ยังไม่ได้ไล่ทุกไฟล์ที่อ่านคอลัมน์นี้) — ยังไม่ยืนยัน

### ปัญหาที่ 6 — Dead Code: `$request->result`
1. **สิ่งที่พบ:** [public/signer/ajax/approve_request.php บรรทัด 119](public/signer/ajax/approve_request.php) เขียน `$request->result = $request->status;` แต่ `result` ไม่อยู่ใน `$db_columns` ของ [InspectionRequest.class.php](private/classes/InspectionRequest.class.php) และไม่มีการประกาศ `public $result;` เป็น property คงที่ (เป็น dynamic property ที่ PHP อนุญาตแบบไม่ประกาศ) ค่านี้จะไม่ถูกบันทึกเมื่อเรียก `save()` เพราะ `DatabaseObject::attributes()` ใช้ `$db_columns` เป็นตัวกำหนดว่าคอลัมน์ใดจะถูก INSERT/UPDATE
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** อนุมัติ
3. **ตาราง/field:** `inspection_requests` (ไม่มีคอลัมน์ `result` ในทางปฏิบัติ — ยังไม่ยืนยัน schema จริงว่ามีคอลัมน์นี้อยู่หรือไม่ เพราะ list `$db_columns` ในคลาสไม่มี)
4. **ไฟล์หลักฐาน:** [public/signer/ajax/approve_request.php บรรทัด 119](public/signer/ajax/approve_request.php), [private/classes/InspectionRequest.class.php บรรทัด 12-38 ($db_columns)](private/classes/InspectionRequest.class.php)
5. **ผลกระทบต่อการใช้งานจริง:** ต่ำ — เป็นโค้ดที่ไม่มีผลใด ๆ (no-op) แต่ทำให้ผู้พัฒนาคนถัดไปเข้าใจผิดว่ามีการเก็บผลประเมินแยกจาก `status`
6. **ระดับความมั่นใจ:** ยืนยันได้ (โครงสร้าง `attributes()`/`$db_columns` ยืนยันชัดเจนว่าไม่ถูกบันทึก)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ตรวจสอบ schema จริงของตาราง `inspection_requests` ว่ามีคอลัมน์ `result` อยู่หรือไม่ (ถ้ามีจริงแต่ไม่อยู่ใน `$db_columns` จะยิ่งเป็นปัญหาใหญ่กว่านี้ เพราะแปลว่าตั้งใจเก็บแต่โค้ดลืม whitelist ไว้) — ยังไม่ยืนยัน

### ปัญหาที่ 7 — Unreachable Status: `inspection_requests.status = 'cancelled'`
1. **สิ่งที่พบ:** มี constant `STATUS_CANCELLED = 'cancelled'` และมีการอ้างอิงแสดงผล (`status_label()`, `status_text()`, badge ใน UI หลายจุด) แต่จากไฟล์ที่ตรวจสอบในรอบนี้ **ไม่พบจุดที่ set `$request->status = 'cancelled'` หรือ `InspectionRequest::STATUS_CANCELLED` จริง** — การ "ยกเลิก" ที่ทำได้จริงคือปุ่ม "ยกเลิกคำขอ" ใน [mystatus.php](public/fisherman/mystatus.php) ซึ่งเรียก [delete_request.php](public/fisherman/ajax/delete_request.php) ที่เป็นการ **hard delete แถวทิ้งทั้งหมด** ไม่ใช่เปลี่ยน status
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** ยื่นคำขอ (ช่วงก่อนนัดตรวจ)
3. **ตาราง/field:** `inspection_requests.status`
4. **ไฟล์หลักฐาน:** [private/classes/InspectionRequest.class.php บรรทัด 5](private/classes/InspectionRequest.class.php) (constant ประกาศไว้), [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php) (พฤติกรรมจริงคือ delete)
5. **ผลกระทบต่อการใช้งานจริง:** ไม่มีประวัติ (audit trail) ของคำขอที่ถูกยกเลิกเหลืออยู่ในตาราง `inspection_requests` เอง (มีเพียง `InspectionLog` ที่บันทึกไว้แยกต่างหากก่อนลบ) และค่าคงที่/ป้ายกำกับ `cancelled` ในโค้ดกลายเป็น dead code ในทางปฏิบัติ
6. **ระดับความมั่นใจ:** มีแนวโน้ม (ยืนยันได้ว่าไม่พบใน endpoint ที่ตรวจสอบแล้ว แต่ยังไม่ได้ไล่ทุกไฟล์ ajax ในระบบ 100% จึงยังไม่ปิดประตูว่าไม่มีที่ set ค่านี้เลยในระบบ)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** grep คำว่า `STATUS_CANCELLED` และ `'cancelled'` ให้ครบทุกไฟล์ในระบบ (รอบนี้ตรวจแล้วบางส่วน ยังไม่ครบ 100%) — ยังไม่ยืนยัน

### ปัญหาที่ 8 — จุดกำกวม: `request_date` ไม่สอดคล้องกันระหว่าง approve และ confirm-fail
1. **สิ่งที่พบ:** เมื่ออนุมัติผ่าน [approve_request.php](public/signer/ajax/approve_request.php) ใช้ `$request->submitted_at` เป็น `old->request_date` แต่เมื่อยืนยันไม่ผ่าน [confirm_fail.php](public/signer/ajax/confirm_fail.php) ใช้ `$request->created_at` แทน ทั้งที่ทั้งสองน่าจะควรสื่อความหมายเดียวกันคือ "วันที่ยื่นคำขอ"
2. **ขั้นตอน workflow ที่เกี่ยวข้อง:** อนุมัติ / ยืนยันไม่ผ่าน
3. **ตาราง/field:** `fv_sanitation_certification_old.request_date`
4. **ไฟล์หลักฐาน:** [public/signer/ajax/approve_request.php บรรทัด 155](public/signer/ajax/approve_request.php), [public/signer/ajax/confirm_fail.php บรรทัด 86](public/signer/ajax/confirm_fail.php)
5. **ผลกระทบต่อการใช้งานจริง:** รายงาน/ประวัติที่อ้างอิง `request_date` อาจแสดงวันที่ต่างความหมายกันระหว่างเคส "ผ่าน" กับ "ไม่ผ่าน" (`submitted_at` = วันที่เจ้าหน้าที่ส่งผลตรวจ, `created_at` = วันที่ยื่นคำขอครั้งแรก ซึ่งเป็นคนละเหตุการณ์)
6. **ระดับความมั่นใจ:** ยืนยันได้ (เทียบโค้ดสองไฟล์ตรงกันชัดเจน)
7. **สิ่งที่ต้องตรวจเพิ่มเติม:** ยืนยันความตั้งใจจริงของนักพัฒนาเดิมว่า field นี้ควรหมายถึงอะไรกันแน่ — ยังไม่ยืนยัน

---

## Open Issues Before FVSCIS Closure

### Must Fix
- **[ปัญหาที่ 1]** `inspection_requests.status` ไม่ถูกเปลี่ยนเป็น `completed` เมื่ออนุมัติผ่าน/มีเงื่อนไข ทำให้ badge/ลิงก์ใบรับรองในหลายหน้า UI (เช่น [signer/incoming_requests.php](public/signer/incoming_requests.php)) ไม่ทำงานตามที่ออกแบบไว้
- **[ปัญหาที่ 2]** ไม่มีการป้องกันการอนุมัติซ้ำใน [approve_request.php](public/signer/ajax/approve_request.php) เสี่ยงออกเลขที่ใบรับรองซ้ำซ้อนต่อคำขอเดียว

### Should Verify
- **[ปัญหาที่ 4]** ยืนยันว่าไม่มีเส้นทางลบคำขอที่มีข้อมูลฟอร์มตรวจแล้วโดยไม่ผ่าน UI guard (ตรวจ endpoint/role อื่นที่อาจเรียกลบได้)
- **[ปัญหาที่ 5]** ตรวจสอบว่ามีรายงาน/หน้าจอใดอ้างอิง `vessel_status` ที่อาจไม่ตรงกับ `inspection_requests.status` ปัจจุบัน
- **[ปัญหาที่ 7]** grep ให้ครบทุกไฟล์เพื่อยืนยันว่า `STATUS_CANCELLED` ไม่ถูกใช้งานจริงที่ใดเลยในระบบ
- **[ปัญหาที่ 6]** ยืนยัน schema จริงของตาราง `inspection_requests` ว่ามีคอลัมน์ `result` หรือไม่

### Can Leave
- **[ปัญหาที่ 3]** ฟีเจอร์บันทึกใบรับรองเก่าด้วยมือ (`create_fvscisold.php`) ที่ไม่ผูกกับ `inspection_requests` — น่าจะเป็นการออกแบบตั้งใจสำหรับนำเข้าข้อมูลย้อนหลัง ไม่กระทบ workflow ออนไลน์หลัก
- **[ปัญหาที่ 8]** ความไม่สอดคล้องของ `request_date` ระหว่าง 2 endpoint — เป็นความคลาดเคลื่อนเล็กน้อยด้าน data quality ไม่กระทบการทำงานของระบบโดยตรง
