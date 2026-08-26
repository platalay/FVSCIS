# ระบบ Log / Audit / Activity History ของ FVSCIS

เอกสารนี้วิเคราะห์จาก source code ปัจจุบันใน repository `fvscis` เท่านั้น โดยไม่ได้แก้ source code, schema หรือข้อมูลในฐานข้อมูล การระบุ field/table อ้างอิงจาก model PHP และ SQL ที่พบใน source; ไม่พบ DDL/migration ที่สร้างตาราง log ใน repository ดังนั้นชนิดข้อมูล, primary key, foreign key, index และ constraint ที่ไม่ได้ประกาศใน model จะยังยืนยันจาก source ไม่ได้

## 1. สรุปภาพรวม

FVSCIS มีระบบที่เกี่ยวข้องกับ log/history อยู่หลายชั้น แต่ไม่ได้เป็น audit platform เดียวกันทั้งหมด:

| ประเภท | แหล่งข้อมูล | หน้าที่จริง |
| --- | --- | --- |
| Inspection activity log | `inspection_logs` ผ่าน `InspectionLog` | timeline การดำเนินการของ inspection request และบาง workflow ของ certificate |
| Action dictionary | `log_actions` ผ่าน `LogAction` | ตารางอ้างอิงชื่อ/หมวดของ action ที่ถูกใช้โดย `inspection_logs.action_id` |
| Request history viewer | `get_request_logs.php` + log modal | แสดงเวลา, action, ผู้ดำเนินการ และ note ของ request |
| Notification event | `notifications` ผ่าน `Notification` | แจ้งเหตุการณ์ให้ผู้ใช้ ไม่ใช่ audit trail หลัก |
| Entity audit metadata | `created_*`, `updated_*` ใน entity ต่าง ๆ | metadata ล่าสุดของ record โดย `DatabaseObject::save()` |
| Certificate state/history | `fv_sanitation_certification_old` | เก็บ record certificate หลายสถานะ เช่น `active`, `inactive`, `pending`, `fail`; ไม่ใช่ event log แยก |
| Attachment metadata | `inspection_attachments`, `fv_certificate_attachments` | เก็บไฟล์และผู้สร้างไฟล์; ไม่มี event history ของการลบ/แก้ attachment |
| PHP/application error log | PHP `error_log` และ server PHP log | diagnostic/error logging ไม่ใช่ business audit |

ข้อสรุปสำคัญ: ระบบมี activity history ของ inspection request ใช้งานจริง แต่ยังไม่ใช่ immutable audit trail ที่ตอบได้ครบทุกกรณีว่า record เปลี่ยนจากค่าเดิมเป็นค่าใหม่อะไร โดยเฉพาะ user CRUD, authentication และ attachment operations

## 2. ตารางและ model ที่เกี่ยวข้อง

### 2.1 `inspection_logs`

Model: [private/classes/InspectionLog.class.php](private/classes/InspectionLog.class.php)

Fields ที่ model ประกาศ:

- `id`
- `inspection_request_id`
- `action_id`
- `note`
- `created_at`, `updated_at`
- `created_by`, `updated_by`
- `created_ip`, `updated_ip`

ความหมายจาก source:

- `inspection_request_id`: request ที่ event ผูกอยู่; registration alert บางจุดใช้ `0`
- `action_id`: อ้างอิง `log_actions.id` ในจุดที่ใช้ `LogAction::find_by_code()` และใช้ numeric id โดยตรงในจุดเก่า
- `note`: ข้อความอธิบาย event; หลาย workflow ใส่ชื่อเรือ, ship code, วันที่ หรือชื่อผู้ทำไว้ในข้อความ
- `created_by`: ถูกเติมจาก session โดย `DatabaseObject::save()` เมื่อ insert
- `created_at`: เวลาที่ server เติมเมื่อ insert
- `created_ip`: IP จาก `$_SERVER['REMOTE_ADDR']` เมื่อ insert
- update metadata มีอยู่ใน model/base class แต่ไม่พบ workflow ที่แก้ log เดิมเพื่อเก็บประวัติการแก้ log

Primary key และ foreign key ไม่ได้ประกาศใน model จึงยืนยันได้เพียงว่า `id` ถูกใช้เป็น identifier และ `action_id` ถูก join กับ `log_actions.id` ใน viewer; constraint จริงต้องตรวจ DDL ฐานข้อมูล

### 2.2 `log_actions`

Model: [private/classes/LogAction.class.php](private/classes/LogAction.class.php)

Fields:

- `id`
- `code`
- `description_th`
- `description_en`
- `category`
- `is_visible`

`LogAction::find_by_code($code)` ใช้ค้น action dictionary แล้วคืน object เพื่อเอา `id` ไปใส่ใน `InspectionLog`. Source ไม่ได้สร้าง/seed records ในตารางนี้ จึงไม่ยืนยันรายการ code ทั้งหมดจาก repository ได้

Code ที่พบการเรียกใช้ ได้แก่:

- `edit_request`
- `request_created_by_officer`
- `request_updated_by_officer`
- `request_deleted_by_officer`
- `fvscis_created_by_officer`
- `fvscis_updated_by_officer`
- `fvscis_deleted_by_officer`
- `inspection_passed`
- `fail_notice_signed`

นอกจากนี้มีจุดเก่าที่ใส่ numeric action id โดยตรง เช่น `1`, `2`, `3`, `4`, `7`, `8`, `17` ทำให้ความหมายต้องอาศัยข้อมูลจริงใน `log_actions` และ comment ใน source; ไม่พบ mapping กลางใน code

### 2.3 `notifications`

Model: [private/classes/Notification.class.php](private/classes/Notification.class.php)

Fields:

- `id`
- `user_id`, `user_role`
- `inspection_request_id`
- `action_id`
- `message`, `notification_type`
- `is_read`, `action_taken`
- `created_at`, `updated_at`
- `created_by`, `updated_by`
- `created_ip`, `updated_ip`

Notification เป็น user-facing event distribution ไม่ใช่ audit log เพราะผู้รับสามารถ mark read/action taken และข้อมูลเน้นการแจ้งเตือน ผู้รับอาจไม่ใช่ actor ที่ทำ event

### 2.4 Certificate and attachment tables

จาก model:

- `fv_sanitation_certification_old`: [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) มี status/certificate fields และ audit metadata ของ record ผ่าน base class
- `inspection_attachments`: [private/classes/InspectionAttachment.class.php](private/classes/InspectionAttachment.class.php) มี `id`, `request_id`, `attachment_type`, `file_path`, `file_name`, `file_type`, `file_size`, `created_by`, `created_at`
- `fv_certificate_attachments`: [private/classes/FvCertificateAttachment.class.php](private/classes/FvCertificateAttachment.class.php) มี `id`, `certificate_id`, `attachment_type`, `file_path`, `file_name`, `file_type`, `file_size`, `created_by`, `created_at`

Attachment models ไม่มี `updated_by`, `updated_at`, `updated_ip` หรือ event-log fields ตาม model. การลบเป็นการ delete record และ unlink file ไม่ใช่การเขียน history row

### 2.5 Entity audit metadata จาก `DatabaseObject`

[private/classes/databaseobject.class.php](private/classes/databaseobject.class.php) กำหนด behavior ร่วม:

- insert: เติม `created_at`, `created_by`, `created_ip` และเติม `updated_*` ค่าเดียวกันถ้า property มี
- update: เติม `updated_at`, `updated_by`, `updated_ip`; ไม่เปลี่ยน `created_*`
- actor id มาจาก `$GLOBALS['session']->user_id()`; ถ้าไม่มี session จะ fallback เป็น `0` ตาม expression ใน base class
- IP มาจาก `$_SERVER['REMOTE_ADDR']` หรือ `UNKNOWN`

นี่เป็น last-write metadata ของแต่ละ record ไม่ใช่ immutable history เพราะ update ครั้งถัดไปทับ `updated_*` เดิม

## 3. จุดที่สร้าง `InspectionLog`

### 3.1 Registration / user creation

พบการสร้าง log ใน:

- [public/ajax/save_fisherman.php](public/ajax/save_fisherman.php)
- [public/ajax/save_fisherman_local.php](public/ajax/save_fisherman_local.php)
- [public/ajax/save_officer.php](public/ajax/save_officer.php)
- [public/ajax/save_officer_local.php](public/ajax/save_officer_local.php)

เมื่อสร้าง fisherman/officer สำเร็จ ระบบสร้าง `InspectionLog` โดยใช้ `inspection_request_id = 0`, `action_id = 1`, note เป็นข้อความสมัครใหม่ และสร้าง notification ให้ admin ด้วย

ข้อจำกัด: log นี้ใช้ตาราง inspection log กับ `inspection_request_id=0`; ไม่ใช่ dedicated user audit table และไม่ได้เก็บ target user id เป็น field แยกใน log นอกจากข้อความและ metadata ผู้สร้าง log

### 3.2 Fisherman inspection request

พบจุดสร้าง log ใน:

- [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php): submit request; numeric `action_id = 2`
- [public/fisherman/ajax/update_inspection.php](public/fisherman/ajax/update_inspection.php): edit request; numeric `action_id = 3` และค้น `edit_request` แต่ยัง assign numeric id ให้ log
- [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php): confirm inspection date; numeric `action_id = 8`
- [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php): delete request; numeric `action_id = 4`

Log note ส่วนใหญ่ระบุชื่อเรือ, ship code, วันที่ หรือเลขเอกสารเป็นข้อความ

### 3.3 Inspect officer workflow

พบจุดสร้าง log ใน:

- [public/inspectofficer/ajax/request_inspection.php](public/inspectofficer/ajax/request_inspection.php): manual request; ใช้ `request_created_by_officer`
- [public/inspectofficer/ajax/create_manual_request_by_officer.php](public/inspectofficer/ajax/create_manual_request_by_officer.php): manual request create; ใช้ `request_created_by_officer`
- [public/inspectofficer/ajax/update_manual_request.php](public/inspectofficer/ajax/update_manual_request.php): manual request edit; ใช้ `request_updated_by_officer`
- [public/inspectofficer/ajax/delete_manual_request.php](public/inspectofficer/ajax/delete_manual_request.php): manual request delete; ใช้ `request_deleted_by_officer`
- [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php): set/propose inspection date; numeric `action_id = 7`
- [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php): result/PDF generation notification path; numeric `action_id = 17`

### 3.4 Signer and inspection result

พบจุดสร้าง log ใน:

- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php): approve passed/conditional ใช้ `inspection_passed`; failed ใช้ `fail_notice_signed`
- [public/signer/generate_pdf.php](public/signer/generate_pdf.php): result/PDF generation path; numeric `action_id = 17`

`approve_request.php` สร้าง note ที่ระบุเรือ, ship code, ผลอนุมัติ, ชื่อ signer และวันที่ลงนาม แต่ค่า actor ที่เชื่อถือได้ใน audit metadata คือ `created_by/created_at/created_ip` ที่ base class เติม; ชื่อใน note เป็นข้อความที่ endpoint สร้าง

### 3.5 Manual FV Certificate

Workflow ที่ใช้งานจริงตรวจพบดังนี้:

| Operation | Endpoint | มี `InspectionLog` หรือไม่ | สิ่งที่เก็บ |
| --- | --- | --- | --- |
| Create certificate | [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) | มี | `fvscis_created_by_officer`, certificate id ถูกใส่ใน `inspection_request_id`, note ระบุชื่อเรือ |
| Edit certificate | [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php) | มี | `fvscis_updated_by_officer`, certificate id ถูกใส่ใน `inspection_request_id`, note ระบุชื่อเรือ |
| Delete certificate | [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php) | มี | `fvscis_deleted_by_officer`, certificate id ถูกใส่ใน `inspection_request_id`, note ระบุชื่อเรือ |
| Add certificate attachment | create/edit endpoint บันทึก `FvCertificateAttachment` | ไม่มี log แยก | attachment record มี `created_by`, `created_at` ตาม model/base class |
| Delete certificate attachment | [public/inspectofficer/ajax/fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php) | ไม่มี | ลบไฟล์จริงและลบ DB row; ไม่มี audit event row |
| Delete certificate with attachments | [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php) | มีเฉพาะ log certificate delete | ลบ attachment แต่ไม่สร้าง log ต่อ attachment |

Manual certificate log สามารถบอกได้ว่าใครสร้าง/แก้/ลบ log เมื่อไร จาก `created_by`, `created_at`, `created_ip` และ log action แต่ไม่เก็บค่าเดิม/ค่าใหม่ของ certificate เป็น structured fields. Note ของ edit ระบุเพียงว่าแก้ผลตรวจของเรือใด ไม่ระบุ field ที่แก้หรือ before/after value

มีจุดที่ log ใช้ `inspection_request_id` เก็บ certificate id ใน manual certificate flow ทั้งที่ชื่อ field สื่อถึง request id; viewer จึงอาจตีความ reference ผิด และ source ไม่พบ field `certificate_id` ใน `InspectionLog`

### 3.6 Form status

[private/classes/InspectionFormStatus.class.php](private/classes/InspectionFormStatus.class.php) สร้าง `InspectionLog` ตอนสร้าง form status และ assign properties เช่น `old_value`, `new_value`, `performed_by`, `performed_at`, `target_department_id`, `target_usertype_id`, `target_officer_id`

แต่ properties เหล่านี้ไม่ได้อยู่ใน `$db_columns` ของ [private/classes/InspectionLog.class.php](private/classes/InspectionLog.class.php) และไม่ใช่ fields ที่ base class จะ persist ผ่าน `attributes()`. จาก source จึงยืนยันว่า field extended เหล่านี้ไม่ได้ถูกบันทึกโดย model ปัจจุบัน เว้นแต่ database/model implementation อื่นที่ไม่พบใน repository

## 4. สิ่งที่ไม่มีหรือไม่พบจุดสร้าง log

จากการค้น source ไม่พบ dedicated audit event สำหรับ:

- login success
- login failure
- logout
- session timeout/expiry
- admin approve/reject officer account
- admin edit/delete user หรือ department CRUD เป็น audit event แยก
- certificate attachment add
- certificate attachment delete
- inspection attachment add/delete
- before/after values ของ entity update
- log deletion history

การเปลี่ยนสถานะ certificate เช่น `mark_active()`, `mark_pending()`, `mark_fail()`, `deactivate_other_active()` เป็นการ update ตาราง certificate โดยตรง ไม่พบการสร้าง status-change event row แยกทุกครั้ง

## 5. Signer / approval audit trail

`public/signer/ajax/approve_request.php` มี log สำหรับผลผ่าน/ผ่านแบบมีเงื่อนไข และไม่ผ่าน:

- action dictionary: `inspection_passed` หรือ `fail_notice_signed`
- request reference: `inspection_request_id = $request->id`
- note: ระบุชื่อเรือ, ship code, ผล, ชื่อ signer และวันที่ลงนาม
- audit metadata: `created_by`, `created_at`, `created_ip` จาก `DatabaseObject::save()`

จึงสามารถย้อนดู event approval ใน request history ได้ หากผู้มีสิทธิ์เรียก `get_request_logs.php` และ request id ยังถูกค้นได้

ข้อจำกัด:

- ไม่พบ log event แยกชื่อ `reject` นอกกรณี failed ที่ใช้ `fail_notice_signed`
- issuance/status update ของ certificate ถูกเก็บใน certificate record และบางส่วนมี inspection log แต่ไม่มี comprehensive event log ที่บันทึกทุก field change
- log save หลายจุดไม่ได้ตรวจ return value; จึงไม่สามารถยืนยันจาก source ว่าทุกธุรกรรมจะ fail หากเขียน log ไม่สำเร็จ

## 6. Authentication / session logging

Authentication implementation อยู่ที่:

- [public/logincheck.php](public/logincheck.php): ตรวจ Officer ก่อน Fisherman แล้วเรียก `Session::login()` เมื่อสำเร็จ
- [private/classes/session.class.php](private/classes/session.class.php): เก็บ `user_id`, `username`, `role`, `user_picture`, `last_login` ใน PHP session
- [public/logout.php](public/logout.php): เรียก `$session->logout()`

สิ่งที่มี:

- session timestamp `last_login` ใช้ตรวจอายุ session (`MAX_LOGIN_AGE = 1 วัน`)
- remember token เก็บใน `officer`/`fisherman` และ cookie `remember_token`
- logout ล้าง token, cookie และ session fields

สิ่งที่ไม่มีใน source:

- login success audit row
- failed login audit row
- logout audit row
- session timeout audit row
- IP/device/user-agent history ของ authentication

การ login/logout จึงมี session state แต่ไม่มี authentication audit trail และไม่มีการแยก implementation log ตาม role เพราะไม่มี auth log ที่ role ใดใช้เลย

## 7. Log viewer / UI

### 7.1 Request history viewer ที่มีจริง

มี viewer แบบ modal ใน:

- [public/fisherman/modal/logmodal.php](public/fisherman/modal/logmodal.php)
- [public/inspectofficer/modal/logmodal.php](public/inspectofficer/modal/logmodal.php)
- [public/signer/modal/logmodal.php](public/signer/modal/logmodal.php)

และ endpoint:

- [public/fisherman/ajax/get_request_logs.php](public/fisherman/ajax/get_request_logs.php)
- [public/inspectofficer/ajax/get_request_logs.php](public/inspectofficer/ajax/get_request_logs.php)
- [public/signer/ajax/get_request_logs.php](public/signer/ajax/get_request_logs.php)

Viewer แสดง:

- เวลา
- action name จาก `log_actions.description_th`
- actor name จาก `officer.full_name` หรือ `fisherman.full_name`
- note

เรียงเวลาเก่าก่อนไปใหม่ และไม่พบ pagination/filter/detail old-new ใน endpoint

Fisherman endpoint มี owner check โดยตรวจ `InspectionRequest::created_by` ให้ตรงกับ session user และ filter action id ที่อนุญาตบางรายการ แต่ role อื่น query ตาม request id โดยไม่มี owner/department authorization ที่เทียบเท่าใน endpoint นี้; role access ถูกจำกัดเพียง allowed role list

### 7.2 Activity Log menu

ค้นพบข้อความ `Activity Log` ใน topbar เดิมบางไฟล์ แต่เป็นส่วนที่ถูก comment out และไม่พบหน้า/activity-log endpoint ที่ใช้งานได้จริง. จึงไม่มี global log viewer สำหรับ admin/superuser ที่ค้นทุก event ได้

## 8. ความครบถ้วนของ Audit Trail

กรณีตัวอย่าง: “เจ้าหน้าที่แก้เลขใบรับรองแล้วภายหลังมีข้อสงสัย”

คำตอบตาม source ปัจจุบัน:

- ใครแก้: **ได้บางส่วน** จาก `inspection_logs.created_by` ของ event edit และ `updated_by` ของ certificate record ล่าสุด; แต่ attachment และบาง direct SQL status update ไม่มี event เทียบเท่า
- แก้เมื่อไร: **ได้บางส่วน** จาก `inspection_logs.created_at` และ `fv_sanitation_certification_old.updated_at` ล่าสุด
- แก้ record ไหน: **ได้ใน manual certificate log แบบอ้อม ๆ** เพราะ endpoint ใส่ certificate id ลง `inspection_request_id`; field name/reference semantics ไม่ตรงชื่อ และไม่มี structured certificate reference ใน log
- ก่อนแก้เป็นอะไร: **ไม่ได้** จาก `InspectionLog`; ไม่มี old-value snapshot ใน model จริง
- หลังแก้เป็นอะไร: **ไม่ได้ครบ**; ต้องอ่าน current certificate record ซึ่งอาจถูกแก้ต่อแล้ว และ log note ไม่ได้เก็บ new-value fields

ดังนั้นระบบตอบได้ดีที่สุดว่า “มี event แก้ certificate โดย actor/time ใดตาม log note” แต่ไม่สามารถทำ field-level forensic reconstruction จาก log อย่างน่าเชื่อถือ

## 9. Duplicate / dead / inconsistent code ที่เกี่ยวข้อง

### 9.1 Action id mapping ซ้ำและไม่เป็นศูนย์กลาง

บาง endpoint ใช้ `LogAction::find_by_code()` แต่หลายจุดใช้ numeric id โดยตรง (`1`, `2`, `3`, `4`, `7`, `8`, `17`). มี comment ระบุว่า id ต้องตรงกับตารางจริง และมี TODO ใน viewer ว่า action mapping ต้องตรวจอีกครั้ง จึงเสี่ยง action name ผิดเมื่อข้อมูล `log_actions` เปลี่ยน

### 9.2 Dual save ใน `update_inspection.php`

พบการเรียก `$log->save()` แล้วตรวจผลด้วย `$log->save()` อีกครั้งใน [public/fisherman/ajax/update_inspection.php](public/fisherman/ajax/update_inspection.php). จาก source นี้มีความเสี่ยงที่จะสร้าง log ซ้ำหรือเปลี่ยน record ซ้ำ เพราะครั้งแรกสร้าง log แล้วครั้งที่สองอาจ update object เดิมทันที; ต้องตรวจ runtime/database เพื่อยืนยันจำนวนแถวจริง แต่เป็น duplicate-log risk ที่พิสูจน์ได้จาก control flow

### 9.3 Registration logs วนตาม admin

ใน save fisherman/officer มีการสร้าง `InspectionLog` ภายใน loop ของ admins แต่ใช้ request id `0` และข้อความเดียวกันทุกครั้ง. หากมี admin หลายคน จะได้ activity log ซ้ำตามจำนวน admin แม้ business event สมัครเกิดครั้งเดียว ขณะที่ notification ตั้งใจสร้างแยกต่อผู้รับ

### 9.4 Extended fields ไม่ตรง model

`InspectionFormStatus` assign old/new/performed fields ให้ `InspectionLog` แต่ fields ไม่อยู่ใน model `$db_columns`; เป็น code path ที่ดูเหมือนรองรับ audit detail แต่ไม่ถูก persist ตาม base model ปัจจุบัน

### 9.5 Certificate id อยู่ใน field ชื่อ request id

Manual certificate create/update/delete log ใส่ certificate id ลง `inspection_request_id`. เป็น ambiguity ที่ทำให้ request history viewer และ downstream reader อาจอ่าน reference ผิดประเภท

### 9.6 ไม่มี duplicate/dead log table ที่ยืนยันได้เพิ่ม

จาก source ที่ค้นพบ class หลักด้าน log มี `InspectionLog` และ `LogAction` เท่านั้น; ไม่พบ class อื่นที่ทำหน้าที่เป็น audit log ซ้ำโดยตรง. `Notification` แยกหน้าที่เป็น event delivery ไม่ใช่ log viewer replacement

## 10. Security / data integrity

### สิ่งที่ทำได้ดี

- `DatabaseObject::save()` เติม actor/time/IP จาก server/session โดยอัตโนมัติเมื่อ entity ใช้ base class
- request log viewer จำกัด role ที่อนุญาต
- fisherman viewer ตรวจว่า request เป็นของ user ปัจจุบันก่อนอ่าน log
- log viewer ใช้ SQL numeric request id ที่ตรวจ `ctype_digit` และ cast integer
- log ไม่มี UI ที่พบสำหรับการแก้ไข/ลบโดยผู้ใช้ทั่วไป

### ความเสี่ยงและข้อจำกัด

- actor identity ของ log ขึ้นกับ session; ถ้าไม่มี session base fallback เป็น `0`, จึงมี system/unknown actor ได้
- IP ใช้ `REMOTE_ADDR` โดยตรง; source ไม่แสดง proxy trust/forwarded IP policy จึงไม่ยืนยันว่าเป็น client จริงในทุก deployment
- note เป็นข้อความรวมข้อมูล ไม่ใช่ structured immutable fields; การค้น/พิสูจน์ field-level change ทำได้ยาก
- log viewer ของ inspect officer/signer รับ request id และไม่พบ ownership/department check เทียบเท่า fisherman ใน endpoint เดียวกัน; ต้องพึ่ง page/business flow ที่ส่ง request id
- ไม่พบ endpoint UI สำหรับลบ/แก้ `inspection_logs` โดยตรง แต่ base class มี `delete()` และ generic update capability; การป้องกันระดับ DB/permission ของตารางไม่ปรากฏใน source
- notification กับ inspection log ถูกสร้างแยก calls; บางจุดไม่ตรวจผล `save()`, จึงมีโอกาส history กับ notification ไม่ครบคู่กัน
- direct status updates ของ certificate/request อาจเปลี่ยน current state โดยไม่มี event row ใหม่

## 11. Error logging แยกจาก business audit

### PHP/system error log

พบการใช้ `error_log()` หรือ comment เกี่ยวกับ error logging ใน:

- [private/classes/databaseobject.class.php](private/classes/databaseobject.class.php) มี debug SQL ที่ comment ไว้
- [private/classes/documentcounter.class.php](private/classes/documentcounter.class.php) มี debug ที่ comment ไว้
- [public/fisherman/ajax/ajax_save_applicant_form1.php](public/fisherman/ajax/ajax_save_applicant_form1.php) เขียน error message ลง PHP error log
- [public/fisherman/ajax/test.php](public/fisherman/ajax/test.php) ตั้งค่า error log path เฉพาะ test/debug
- [public/logins2.php](public/logins2.php) มี error logging ของ debug input บางจุด
- มี error handling ใน endpoint จำนวนมากที่ catch exception แล้วคืน JSON `success=false`; นี่ไม่ใช่การเขียน business audit row

PHP error log อยู่ตาม PHP/Apache configuration ของ environment เช่น `C:\xampp\php\logs\php_error.log` ในเครื่องที่ตรวจ แต่ path production ไม่ควรเดาจาก source

### ไม่พบ custom error/audit table

ไม่พบ model/table สำหรับ `error_logs`, `audit_events`, `login_logs` หรือ file logger ของ application ที่แยกจาก PHP error log

PHP error log ไม่ควรถูกใช้แทน audit trail เพราะไม่มี schema/retention/actor-reference/immutability แบบ business audit

## 12. Gap summary และ priority

### มีและใช้งานได้ดี

- request activity timeline พื้นฐานผ่าน `inspection_logs`
- action dictionary ผ่าน `log_actions` ในจุดที่เรียกด้วย code
- actor/time/IP metadata อัตโนมัติสำหรับ records ที่สร้างผ่าน `DatabaseObject::save()`
- signer approval และ failed-result event มี log note
- request history modal มีใช้งานสำหรับ fisherman, inspect officer และ signer
- certificate create/edit/delete มี activity log ระดับ operation

### มีแต่ไม่ครบ

- manual certificate audit: มี operation log แต่ไม่มี old/new field-level values และ attachment event log
- certificate status history: มี current/history rows ใน certificate table แต่ไม่มี event timeline ครบทุก status change
- action classification: มีทั้ง code lookup และ numeric ids
- request viewer authorization: fisherman มี owner check แต่ role อื่นยังไม่เห็น equivalent check ใน endpoint
- registration log: ใช้ inspection log id `0` และอาจซ้ำตามจำนวน admin
- error handling: มี PHP error log/JSON errors แต่ไม่มี centralized operational log

### ไม่มี

- login success/failure/logout/session-timeout audit
- global admin/superuser activity log viewer
- dedicated immutable audit table
- attachment add/delete audit events
- structured before/after values ของ certificate/request/user
- verified log retention/archive policy

### Code เก่าหรือไม่แน่ชัด

- `InspectionFormStatus` extended log fields ที่ไม่ตรงกับ `InspectionLog` model
- numeric action ids ที่ไม่มี mapping กลางใน source
- commented-out Activity Log menu
- `update_inspection.php` dual `$log->save()` ที่เสี่ยง duplicate
- registration logs ภายใน admin loop
- manual certificate logs ที่ใช้ `inspection_request_id` เป็น certificate id

### Priority

#### Critical

1. ไม่มี immutable audit trail สำหรับ field-level changes ของ certificate/request/user; ไม่สามารถพิสูจน์ before/after ได้เมื่อเกิดข้อพิพาท
2. attachment add/delete และการเปลี่ยน certificate status บาง path ไม่มี event audit แยก ทำให้หลักฐานการแก้ไข/ลบไม่ครบ
3. log viewer ของ role เจ้าหน้าที่/ผู้ลงนามไม่แสดง ownership/department authorization ใน endpoint ที่อ่าน request logs ตาม source ที่พบ

#### Important

1. ไม่มี authentication audit สำหรับ login success/failure/logout/session expiry
2. แก้ action id ให้ใช้ code/mapping กลางแทน numeric ids และตรวจความหมายใน `log_actions`
3. แก้/ตรวจ dual save ใน `update_inspection.php` และการสร้าง registration log ซ้ำใน admin loop
4. แยก reference ของ certificate ออกจาก `inspection_request_id` และกำหนด foreign-key semantics ให้ชัด
5. ตรวจให้ทุก log save อยู่ใน transaction เดียวกับ business mutation และตรวจผล save อย่างสม่ำเสมอ

#### Nice to have

1. เพิ่ม global Activity Log viewer สำหรับ admin ที่มี filter, search, pagination และ detail
2. เพิ่ม retention/archive policy และ monitoring ของ PHP/application errors
3. เพิ่ม structured event metadata เช่น entity type/id, old values, new values, actor role และ correlation id
4. เพิ่ม test coverage สำหรับจำนวน log ต่อ workflow และการกัน duplicate

## 13. เอกสารอ้างอิงหลัก

### Classes / models

- [private/classes/InspectionLog.class.php](private/classes/InspectionLog.class.php)
- [private/classes/LogAction.class.php](private/classes/LogAction.class.php)
- [private/classes/Notification.class.php](private/classes/Notification.class.php)
- [private/classes/databaseobject.class.php](private/classes/databaseobject.class.php)
- [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php)
- [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php)
- [private/classes/InspectionAttachment.class.php](private/classes/InspectionAttachment.class.php)
- [private/classes/FvCertificateAttachment.class.php](private/classes/FvCertificateAttachment.class.php)
- [private/classes/InspectionFormStatus.class.php](private/classes/InspectionFormStatus.class.php)
- [private/classes/session.class.php](private/classes/session.class.php)

### Workflow endpoints

- [public/logincheck.php](public/logincheck.php)
- [public/logout.php](public/logout.php)
- [public/ajax/save_fisherman.php](public/ajax/save_fisherman.php)
- [public/ajax/save_officer.php](public/ajax/save_officer.php)
- [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php)
- [public/fisherman/ajax/update_inspection.php](public/fisherman/ajax/update_inspection.php)
- [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php)
- [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php)
- [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php)
- [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php)
- [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php)
- [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php)
- [public/inspectofficer/ajax/fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php)
- [public/inspectofficer/ajax/create_manual_request_by_officer.php](public/inspectofficer/ajax/create_manual_request_by_officer.php)
- [public/inspectofficer/ajax/update_manual_request.php](public/inspectofficer/ajax/update_manual_request.php)
- [public/inspectofficer/ajax/delete_manual_request.php](public/inspectofficer/ajax/delete_manual_request.php)
- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)
- [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php)
- [public/signer/generate_pdf.php](public/signer/generate_pdf.php)

### Viewers

- [public/fisherman/ajax/get_request_logs.php](public/fisherman/ajax/get_request_logs.php)
- [public/inspectofficer/ajax/get_request_logs.php](public/inspectofficer/ajax/get_request_logs.php)
- [public/signer/ajax/get_request_logs.php](public/signer/ajax/get_request_logs.php)
- [public/fisherman/modal/logmodal.php](public/fisherman/modal/logmodal.php)
- [public/inspectofficer/modal/logmodal.php](public/inspectofficer/modal/logmodal.php)
- [public/signer/modal/logmodal.php](public/signer/modal/logmodal.php)

## 14. ขอบเขตงานรอบนี้

- เพิ่ม Manual FV Certificate audit เฉพาะ create/edit/delete และ attachment add/delete
- Migration: [ADD_MANUAL_CERTIFICATE_AUDIT_LOG.sql](ADD_MANUAL_CERTIFICATE_AUDIT_LOG.sql)
- ใช้ `entity_type/entity_id` แยกจาก legacy request reference และใช้ nullable columns เพื่อไม่ทำลาย log เดิม
- ไม่ทำ authentication audit, fisherman workflow audit, e-FVSCIS หรือ global activity platform
- ไม่ commit และไม่ push
- จุดที่ต้องยืนยันจาก runtime/DDL ของ deployment อื่นถูกระบุไว้เป็นข้อจำกัดแล้ว

## 15. Manual Certificate Audit Implementation Update

ส่วนนี้ supersede ข้อสรุปเดิมที่ระบุว่า Manual Certificate ยังไม่มี old/new หรือ attachment event audit

### Schema ที่ตรวจจาก MariaDB จริง

DDL local ยืนยันว่า `inspection_logs` เดิมมี primary key `id`, foreign key `action_id -> log_actions.id` แบบ `ON UPDATE CASCADE`, และ fields เดิมสำหรับ request activity. Migration เพิ่ม nullable fields:

- `entity_type VARCHAR(50)` และ `entity_id INT`: reference ของ entity ที่ไม่ใช่ inspection request
- `old_values LONGTEXT` และ `new_values LONGTEXT`: JSON สำหรับค่าก่อน/หลัง
- `actor_role VARCHAR(50)`: role ของผู้ทำจาก session
- index `(entity_type, entity_id)` และ `created_by`

ตาราง `log_actions.code` เป็น unique และ migration เพิ่มเฉพาะ code ที่ยังไม่มี:

- `fvscis_attachment_added`
- `fvscis_attachment_deleted`

`fv_sanitation_certification_old` และ `fv_certificate_attachments` ไม่มี audit/event table แยกใน DDL ที่ตรวจ และไม่มี foreign key ที่ทำให้ audit row ถูกลบตาม certificate

### Reference semantics

Audit ของ Manual Certificate ใช้:

```text
inspection_request_id = 0
entity_type = manual_certificate
entity_id = fv_sanitation_certification_old.id
```

จึงไม่ใช้ `inspection_request_id` เก็บ certificate id อีก ส่วน log เก่าก่อน migration ไม่ถูก rewrite เพื่อรักษา backward compatibility

### Event coverage

- Create: หลังสร้าง certificate สำเร็จ บันทึก action `fvscis_created_by_officer` และ `new_values` ที่ whitelist เฉพาะ vessel/ship/certificate/date/status fields
- Edit: อ่านค่าเดิมก่อน save, เปรียบเทียบ whitelist fields และบันทึกเฉพาะค่าที่เปลี่ยนใน `old_values`/`new_values`; ไม่มี change event เมื่อค่าจริงไม่เปลี่ยน
- Delete: เก็บ certificate snapshot ใน `old_values` หลัง delete สำเร็จ; audit row อยู่นอก FK certificate และคงอยู่หลัง hard delete
- Attachment add: หลัง attachment DB row สำเร็จ บันทึก `attachment_id`, `attachment_type`, `file_name` ใน `new_values`; ไม่เก็บ binary
- Attachment delete: เก็บ metadata ก่อนลบ, ลบ file/DB และ audit ใน transaction เดียวกัน; action คือ `fvscis_attachment_deleted`
- Delete certificate พร้อม attachments: บันทึก certificate delete และ attachment delete event แยกตาม metadata ที่อ่านก่อนลบ

Actor id/time/IP เติมโดย `DatabaseObject::save()` จาก session/server และ actor role เติมโดย `InspectionLog::create_manual_certificate_audit()`

### Reader and authorization

มี backend reader ที่ [public/inspectofficer/ajax/get_manual_certificate_audit.php](public/inspectofficer/ajax/get_manual_certificate_audit.php) คืน action, actor, เวลา, IP, note และ old/new JSON โดย:

- อนุญาตเฉพาะ role `inspectofficer`
- ต้องพบ certificate และ officer ปัจจุบัน
- ตรวจ `certificate.evaluation_agency == officer.departments_id`
- ยังไม่มี UI timeline/modal ในหน้า Manual Certificate เพื่อจำกัด scope รอบนี้

### Verification status

- `php -l` ผ่านสำหรับ model, migration-related endpoints และ audit reader
- Migration apply สำเร็จบน MariaDB 10.4.32 และพบ columns/action codes ตามที่ออกแบบ
- rollback-only probe ของ audit insert ใช้ยืนยันแนวคิด transaction โดยไม่ทิ้ง row ทดสอบ
- ยังไม่ได้ทำ live Create/Edit/Delete หรือ upload/delete จริง เพราะจะเปลี่ยนข้อมูลและไฟล์ production-like; ต้องทำด้วย test fixture/rollback ที่ควบคุมได้

### Remaining gaps

## Inspect Officer Authentication Runtime Update

ตรวจพบ root cause ของ HTTP 500 จาก PHP error log จริง: [private/initialize.php](private/initialize.php) เรียก `db_el_connect()` ที่ [private/database_functions.php](private/database_functions.php) line 36 แล้ว PostgreSQL e-License `172.16.1.168:5432` connection timeout ทำให้ `PDOException` หลุดก่อน endpoint จะทำงาน ไม่ใช่ปัญหาจาก Manual Audit โดยตรง

การแก้ไข:

- [private/initialize.php](private/initialize.php) ครอบ e-License connection ด้วย `try/catch`, บันทึก `error_log()` และให้ส่วนที่ไม่ใช้ e-License ทำงานต่อด้วย connection `null`
- [private/classes/session.class.php](private/classes/session.class.php) ให้ AJAX ที่ session หมดอายุคืน HTTP 401 JSON, role ไม่ตรงคืน HTTP 403 JSON และ normal page redirect ไป `WWW_ROOT/login.php`

ผล runtime:

- Login UI สำเร็จและ redirect ไป `inspectofficer/index.php`; session ใช้งานได้จริงและแสดง dashboard/ชื่อผู้ใช้
- Authenticated `inspectofficer/ajax/load_notifications.php`: HTTP 200 JSON, unread `12`, รายการ `10`
- Authenticated audit reader certificate `10538` ในหน่วยงานเดียวกัน: HTTP 200 JSON `success=true`
- Audit reader certificate `10506` ต่างหน่วยงาน: HTTP 200 JSON `success=false`
- Unauthenticated loader: HTTP 401 JSON และไม่เปิดเผยข้อมูล
- PHP lint และ editor diagnostics ผ่าน; ไม่มีการ commit/push

ข้อสังเกตที่ไม่เกี่ยวกับ blocker: browser พบ `img/default-user.svg` 404 และ external CDN integrity/network warning

- audit นี้ยังไม่ครอบคลุม certificate status mutation จาก path อื่นนอก Manual Certificate create/edit/delete ที่แก้รอบนี้
- attachment audit ของ `inspection_attachments` ยังไม่มี
- legacy Manual Certificate logs อาจยังมี certificate id อยู่ใน `inspection_request_id`
- ไม่มี global audit viewer และไม่มี authentication audit ตาม scope
