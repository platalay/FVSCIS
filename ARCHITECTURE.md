# FVSCIS — สถาปัตยกรรมระบบ

> เอกสารนี้สรุปข้อเท็จจริงที่ยืนยันได้จาก source code ณ วันที่ 23 Aug 2026 จุดใดที่ไม่สามารถยืนยันได้จาก source จะระบุว่า **"ยังไม่ยืนยัน"** อย่างชัดเจน แทนการเดา

## 1. โครงสร้างโฟลเดอร์หลัก

| โฟลเดอร์ | หน้าที่ |
|---|---|
| [public/](public) | Web root ที่เข้าถึงได้จาก browser: หน้า login, portal ของแต่ละ role (`admin`, `fisherman`, `headquarter`, `inspectofficer`, `signer`), ajax endpoints, asset (css/js/img), OAuth callback |
| [private/](private) | Business logic, DB bootstrap/config, ORM classes (`classes/`), PDF/QR library (`fpdf/`, `fpdi/`, `phpqrcode/`, `qrcode/`), PDF template (`pdftemplate/`) — ไม่ได้ออกแบบให้เข้าถึงตรงจาก URL |
| `private/classes/` | คลาส ORM ของแต่ละ entity/module ทั้งหมด สืบทอดจาก `DatabaseObject` (MySQL), `DatabaseObjectEl` (e-License PostgreSQL) หรือ `DatabaseObjectFi` (FI PostgreSQL) |

## 2. Entry Point และ Initialization

- [index.php](index.php) (root) — ทำหน้าที่ redirect ไปยัง `/FVSCIS/index.php` เท่านั้น ไม่มี business logic
- [private/initialize.php](private/initialize.php) — bootstrap หลักที่ทุกหน้าใน `public/` เรียกใช้ ลำดับการทำงาน:
  1. `require functions.php`, `status_error_functions.php`
  2. `require db_credentials.php` → โหลด config จาก `config.local.php` แล้ว `define()` เป็น PHP constants
  3. `require database_functions.php` → ประกาศฟังก์ชัน connect ฐานข้อมูล
  4. เปิดการเชื่อมต่อ FVSCIS MySQL (`db_connect()`) แล้ว inject เข้า `DatabaseObject::set_database()`
  5. เปิดการเชื่อมต่อ e-License PostgreSQL (`db_el_connect()`) แล้ว inject เข้า `DatabaseObjectEl::set_database()`
  6. การเชื่อมต่อ FI PostgreSQL (`db_fi_connect()` / `DatabaseObjectFi::set_database()`) **ถูก comment ปิดไว้ ไม่ทำงานจริงในปัจจุบัน**

## 3. การเชื่อมต่อฐานข้อมูล (3 แหล่ง)

| ฐานข้อมูล | Engine | Config key (config.local.php) | ฟังก์ชันเชื่อมต่อ | Base ORM class | สถานะ |
|---|---|---|---|---|---|
| FVSCIS (หลัก) | MySQLi | `DB_SERVER/DB_PORT/DB_USER/DB_PASS/DB_NAME` | `db_connect()` ใน [private/database_functions.php](private/database_functions.php) | `DatabaseObject` ([private/classes/databaseobject.class.php](private/classes/databaseobject.class.php)) | **ใช้งานจริง** |
| FI (Fishing Info) | PostgreSQL ผ่าน PDO | `DB_SERVER_FI/DB_PORT_FI/DB_USER_FI/DB_PASS_FI/DB_NAME_FI` | `db_fi_connect()` | `DatabaseObjectFi` ([private/classes/databaseobjectFi.class.php](private/classes/databaseobjectFi.class.php)) | **ปิดใช้งาน** (comment ใน `initialize.php`); ยังไม่พบคลาสใดสืบทอดคลาสนี้ |
| e-License | PostgreSQL ผ่าน PDO | `DB_SERVER_EL/DB_PORT_EL/DB_USER_EL/DB_PASS_EL/DB_NAME_EL` | `db_el_connect()` | `DatabaseObjectEl` ([private/classes/databaseobjectEl.class.php](private/classes/databaseobjectEl.class.php)) | **ใช้งานจริง** ผ่านคลาส `Elicense`/`ElicensePort` |

คลาสที่ query e-License PostgreSQL โดยตรง (รับ `PDO $pdo` เป็นพารามิเตอร์):
- [private/classes/elicense.class.php](private/classes/elicense.class.php) — query ตาราง `public.fishing_license`, `public.elicense_office`, `public.res_partner`, `public.fishing_vessel`
- [private/classes/elicenseport.class.php](private/classes/elicenseport.class.php) — query ตาราง `public.elicense_license_port`

**⚠️ ความเสี่ยงด้าน security ที่พบ:** [private/config.local.php](private/config.local.php) เก็บรหัสผ่านฐานข้อมูลทั้ง 3 แหล่ง และ OAuth client secret (LINE/Google/Facebook) เป็น plaintext ในไฟล์นี้โดยตรง แม้ชื่อไฟล์ `.local.` จะสื่อว่าเป็น config เฉพาะเครื่องที่ไม่ควร commit ค่าจริงก็ตาม ควรพิจารณาย้ายไป environment variable หรือ secret manager

## 4. Authentication, Session, Role/User Type และ Social Login

### Session
[private/classes/session.class.php](private/classes/session.class.php)
- เก็บใน `$_SESSION`: `user_id, username, role, user_picture, last_login`
- `MAX_LOGIN_AGE = 86400` วินาที (1 วัน); remember-me cookie อายุ 30 วัน ชื่อ `remember_token`
- `require_role(array $allowed_roles)` ใช้ตรวจสิทธิ์เข้าหน้าแต่ละ portal
- `map_usertype_id_to_role($id)`: 1→admin, 2→headquarter, 3→inspectofficer, 4→signer

### Role / User Type
- [private/classes/UserType.class.php](private/classes/UserType.class.php) → ตาราง `user_types`
- ผู้ใช้ 2 กลุ่มหลัก:
  - `Officer` ([private/classes/officer.class.php](private/classes/officer.class.php)) → ตาราง `officer` มี `usertype_id`, `departments_id`
  - `Fisherman` ([private/classes/fisherman.class.php](private/classes/fisherman.class.php)) → ตาราง `fisherman`
- `usertype_id = 6` หรือ `departments_id = 38` หมายถึงบัญชีลงทะเบียนไม่สมบูรณ์/รออนุมัติ

### Login flow (username/password)
[public/login.php](public/login.php) → [public/logincheck.php](public/logincheck.php): เช็ค `Officer::find_by_username()` ก่อน แล้ว fallback ไป `Fisherman::find_by_username()`, ตรวจด้วย `password_verify()`, ต้อง `is_approved = 1`

### Social login (3 ผู้ให้บริการ)
| ผู้ให้บริการ | ไฟล์เริ่ม | ไฟล์ callback |
|---|---|---|
| Facebook | [public/loginfb.php](public/loginfb.php) | [public/fbcallback.php](public/fbcallback.php) |
| Google | [public/logingoogle.php](public/logingoogle.php) | [public/googlecallback.php](public/googlecallback.php) |
| LINE | [public/loginline.php](public/loginline.php) | [public/linecallback.php](public/linecallback.php) + [private/classes/LineLoginLib.class.php](private/classes/LineLoginLib.class.php) |

ผู้ใช้ social login ที่ยังไม่มีบัญชี → เก็บข้อมูลชั่วคราวใน `$_SESSION['social_tmp']` (หมดอายุ 10 นาที) แล้วนำไปกรอกข้อมูลต่อที่ [public/logins2.php](public/logins2.php); สมัครตรงไม่ผ่าน social ใช้ [public/logins3.php](public/logins3.php)

### ลืมรหัสผ่าน
[public/forgot-password.html](public/forgot-password.html) + [public/forgot_password_process.php](public/forgot_password_process.php): สุ่มรหัสผ่านใหม่ (`bin2hex(random_bytes(4))`) แล้ว **คืนค่าเป็น plaintext ใน JSON response โดยตรง** (ยังไม่มีการส่งอีเมลจริงในขั้นตอนนี้)

### Role-based portals
5 โฟลเดอร์แยกตาม role: [public/admin/](public/admin), [public/fisherman/](public/fisherman), [public/headquarter/](public/headquarter), [public/inspectofficer/](public/inspectofficer), [public/signer/](public/signer)

## 5. โมดูลหลักและไฟล์สำคัญ

| Module | ไฟล์/คลาสหลัก |
|---|---|
| คำขอตรวจ | [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php) — ตาราง `inspection_requests`; ค่า status: `pending, cancelled, inspecting, passed, failed, conditional, completed` |
| เกณฑ์ตรวจ (master data) | [inspection_main_item](inspection_main_item) (ไฟล์ SQL insert), [private/classes/InspectionMainItem.class.php](private/classes/InspectionMainItem.class.php) (ตาราง `inspection_main_items`), [private/classes/InspectionFailItem.class.php](private/classes/InspectionFailItem.class.php) (ตาราง `inspection_fail_items`) — 5 หมวด: โครงสร้างเรือ, วัสดุ/อุปกรณ์, ลูกเรือ, น้ำและน้ำแข็ง, การถนอมรักษา |
| แบบฟอร์มตรวจ 5 ด้าน | `InspectionFormStructure`, `InspectionFormMaterial`, `InspectionFormCrew`, `InspectionFormWaterAndIce`, `InspectionFormPreservation` (ใน `private/classes/`) |
| ผลการประเมิน | [private/classes/InspectionEvaluation.class.php](private/classes/InspectionEvaluation.class.php) — rule engine ตามชนิดใบรับรอง (ไทย/EU) × license mode × cold storage flag |
| สถานะเอกสาร/เลขที่เอกสาร | [private/classes/InspectionFormStatus.class.php](private/classes/InspectionFormStatus.class.php) (ตาราง `inspection_form_status`), [private/classes/documentcounter.class.php](private/classes/documentcounter.class.php) (ตาราง `document_counters`) |
| ข้อมูลผู้ยื่นคำขอ | [private/classes/InspectionApplicantInfo.class.php](private/classes/InspectionApplicantInfo.class.php) (ตาราง `inspection_applicant_info`) |
| ไฟล์แนบ | [private/classes/InspectionAttachment.class.php](private/classes/InspectionAttachment.class.php) (ตาราง `inspection_attachments`), [private/classes/FvCertificateAttachment.class.php](private/classes/FvCertificateAttachment.class.php) (ตาราง `fv_certificate_attachments`) |
| ใบรับรองและสถานะการรับรอง | [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) (ตาราง `fv_sanitation_certification_old` — เป็นตารางหลักที่ใช้เก็บข้อมูลใบรับรองและสถานะสำคัญของการรับรองเรือประมงในระบบปัจจุบัน แม้ชื่อคลาสและตารางจะมีคำว่า "Old" แต่ยังใช้งานจริงและมีบทบาทสำคัญต่อ workflow การรับรอง) |
| Audit / Notification | [private/classes/InspectionLog.class.php](private/classes/InspectionLog.class.php) (ตาราง `inspection_logs`), [private/classes/LogAction.class.php](private/classes/LogAction.class.php) (ตาราง `log_actions`), [private/classes/Notification.class.php](private/classes/Notification.class.php) (ตาราง `notifications`) |
| หน่วยงาน | [private/classes/department.class.php](private/classes/department.class.php) (ตาราง `departments`), [private/classes/departmentgroup.class.php](private/classes/departmentgroup.class.php) (ตาราง `department_groups`) |
| ข้อมูลอ้างอิงพื้นที่ | `Province`, `Amphur`, `Tambon` (ใน `private/classes/`) |
| e-License integration | `Elicense`, `ElicensePort` |
| PDF/QR | [private/fpdf/](private/fpdf) (FPDF library), [private/fpdi/](private/fpdi) (PDF template import), [private/phpqrcode/](private/phpqrcode), [private/qrcode/](private/qrcode) (asset รูปภาพ), [private/pdftemplate/](private/pdftemplate) (template PDF ของแบบฟอร์ม/ใบรับรอง เช่น FVS031-034, FVS1, FVS21-24, FVS3, SorRor3) |

## 6. Workflow หลัก (ยื่นคำขอ → ตรวจ → ออกใบรับรอง)

1. **ยื่นคำขอ** — ชาวประมงเลือกเรือ (ดึงข้อมูลจาก e-License ผ่าน `Elicense::find_one_by_ship_code`) และยื่นคำขอผ่าน `public/fisherman/ajax/request_inspection.php` → สร้าง `InspectionRequest` (status `pending`) + `InspectionApplicantInfo` + แจ้งเตือนเจ้าหน้าที่ผ่านตาราง `notifications`
2. **เจ้าหน้าที่ยืนยันนัดตรวจ** — [public/inspectofficer/incoming_requests.php](public/inspectofficer/incoming_requests.php) กำหนด `confirmed_inspect_date`, สถานะเปลี่ยนเป็น `inspecting`
3. **กรอกผลตรวจ 5 แบบฟอร์ม** — [public/inspectofficer/form_inspect.php](public/inspectofficer/form_inspect.php) และไฟล์ `form_structure/material/crew/waterice/preservation.php` (บันทึกผ่าน ajax autosave)
4. **ประเมินผลอัตโนมัติ** — `InspectionEvaluation::check_vessel_pass()` กำหนดสถานะเป็น `passed`, `conditional` หรือ `failed`
5. **สร้าง PDF ผลตรวจ/ล็อกเอกสาร** — [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php) ล็อก document แล้วแจ้งเตือนผู้ลงนาม (signer) และชาวประมง
6. **ผู้ลงนามอนุมัติ/ออกใบรับรอง** — [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) เรียก `DocumentCounter::next_code_by_effective()` เพื่อออกเลขที่ใบรับรอง แล้วสร้าง PDF ผ่าน `public/signer/generate_pdf*.php` / `gen_pdf_result_fvs03X.php`

## 7. ระบบภายนอกและ Dependency สำคัญ

- **e-License PostgreSQL** (`172.16.1.168`, DB `db_elicense_live`) — ระบบใบอนุญาตทำประมง เชื่อมต่อจริงผ่าน `DatabaseObjectEl`
- **FI PostgreSQL** (`172.16.1.141`, DB `FI2`) — โค้ดมีไว้แต่**ปิดใช้งานจริง** ใน `initialize.php`
- **Facebook / Google / LINE OAuth2** — สำหรับ social login (ดูหัวข้อ 4)
- **Gmail SMTP ผ่าน PHPMailer** — [public/sendmail.php](public/sendmail.php) ใช้ library ใน `public/PHPMailer/`, host `smtp.gmail.com` — ไฟล์นี้ดูเหมือนเป็นไฟล์ทดสอบ **ยังไม่ยืนยัน** ว่าถูกเรียกใช้จริงใน flow แจ้งเตือน/ลืมรหัสผ่านของระบบ
- **FPDF/FPDI** — สร้าง PDF จาก template ([private/fpdi/composer.json](private/fpdi/composer.json) ระบุ require `php: ^7.1||^8.0`, `ext-zlib`)
- **phpqrcode** — สร้าง QR code บนใบรับรอง
- **Frontend**: SB Admin 2 (Bootstrap 4 theme) — [public/package.json](public/package.json): jQuery 3.6.0, Bootstrap 4.6.0, Chart.js 2.9.4, DataTables

## 8. Configuration ที่แตกต่างตามเครื่อง

- [private/config.local.php](private/config.local.php) — ไฟล์ config เฉพาะเครื่อง เก็บ `BASE_URL`, DB credentials 3 ชุด, Social login keys (LINE/Google/Facebook), `APP_PASSWORD`
- โหลดผ่าน [private/db_credentials.php](private/db_credentials.php) ซึ่งตรวจสอบว่า key ที่จำเป็น (`$required_config`) มีครบก่อน `define()` เป็น constant

## 9. จุด Hard-code / ข้อจำกัดที่ควรบันทึก

- **`fishery_year = '2569'`** hard-code ใน [private/classes/elicense.class.php](private/classes/elicense.class.php) (หลายจุด) และ [public/inspectofficer/ajax/get_elicense_by_ship_code.php](public/inspectofficer/ajax/get_elicense_by_ship_code.php) — ต้องแก้ด้วยมือทุก 2 ปี (บันทึกไว้แล้วใน [REMARK.md](REMARK.md) พร้อมประวัติ: 23 Aug 2026 เปลี่ยนจาก 2567 เป็น 2569; อาการเมื่อไม่ได้เปลี่ยน: หน้า My Vessel ไม่พบข้อมูลเรือ และอาจเกิด error `foreach() ... bool given`)
- LINE channel id ปรากฏ hard-code ใน [public/loginline.php](public/loginline.php) — **ยังไม่ยืนยัน** ว่าไฟล์นี้อ้างอิง constant จาก config.local.php หรือใช้ literal ซ้ำแยกต่างหาก ควรตรวจสอบเพิ่มเติม
- FI PostgreSQL connection มีโค้ดพร้อมใช้แต่ปิดอยู่ (dead/future-use code) — ไม่มีคลาสใดสืบทอด `DatabaseObjectFi` ในปัจจุบัน
- Credential จริง (DB password, OAuth secret) ปรากฏเป็น plaintext ใน `config.local.php` ของ workspace นี้
- Forgot-password คืนรหัสผ่านใหม่แบบ plaintext ตรงใน JSON response แทนการส่งอีเมล

## 10. ตารางฐานข้อมูลหลักต่อโมดูล

| Module | ตาราง |
|---|---|
| ผู้ใช้ | `officer`, `fisherman`, `user_types` |
| หน่วยงาน | `departments`, `department_groups` |
| คำขอตรวจ | `inspection_requests` |
| แบบฟอร์มตรวจ | `inspection_form_structure`, `inspection_form_material`, `inspection_form_crew`, `inspection_form_water_and_ice`, `inspection_form_preservation` |
| เกณฑ์ตรวจ (master data) | `inspection_main_items`, `inspection_fail_items` |
| สถานะเอกสาร | `inspection_form_status` |
| เลขเอกสาร | `document_counters` |
| ข้อมูลผู้ยื่นคำขอ | `inspection_applicant_info` |
| ไฟล์แนบ | `inspection_attachments`, `fv_certificate_attachments` |
| ใบรับรอง | `fv_sanitation_certification_old` |
| Audit/Notification | `inspection_logs`, `log_actions`, `notifications` |
| e-License (PostgreSQL, DB `db_elicense_live`) | `public.fishing_license`, `public.elicense_office`, `public.res_partner`, `public.fishing_vessel`, `public.elicense_license_port` |

> ตารางของ `Province`, `Amphur`, `Tambon` (ข้อมูลอ้างอิงพื้นที่) — **ยังไม่ยืนยัน** ชื่อ table_name ที่แน่นอน ควรเปิดไฟล์คลาสตรวจสอบเพิ่มก่อนใช้อ้างอิงเชิงเทคนิค

## สรุป

FVSCIS เป็นระบบ PHP procedural + lightweight custom ORM (pattern คล้าย Larry Ullman) แยกฐานข้อมูลหลักเป็น MySQL (ระบบ FVSCIS เอง) และเชื่อมต่อ PostgreSQL ภายนอก 2 แหล่ง (FI ปัจจุบันปิดใช้งาน, e-License ใช้งานจริง) มี 5 role-portal (admin, fisherman, headquarter, inspectofficer, signer), workflow หลักคือ ยื่นคำขอ → เจ้าหน้าที่นัดตรวจ → กรอกผลตรวจ 5 หมวด → ประเมินผลอัตโนมัติ → ล็อกเอกสาร/แจ้งเตือน → ผู้ลงนามออกเลขที่และใบรับรอง PDF พร้อม QR code และมีจุดที่ต้องดูแลเป็นพิเศษ ได้แก่ hard-code `fishery_year`, credential เป็น plaintext ในไฟล์ config เฉพาะเครื่อง, และกระบวนการลืมรหัสผ่านที่ส่งค่า plaintext กลับทาง JSON
