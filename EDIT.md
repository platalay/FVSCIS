# Inspect Officer Authentication Runtime Fix

- Root cause HTTP 500: `private/initialize.php` เรียก e-License PostgreSQL `172.16.1.168:5432` แล้ว connection timeout ที่ `private/database_functions.php` line 36 ก่อน endpoint จะถึง auth guard
- แก้ `private/initialize.php` ให้ catch `Throwable`, เขียน `error_log()` และให้ระบบที่ไม่ใช้ e-License bootstrap ต่อได้
- แก้ `private/classes/session.class.php`: AJAX expired session เป็น HTTP 401 JSON, wrong role เป็น HTTP 403 JSON, normal page redirect ไป `WWW_ROOT/login.php`
- Runtime ผ่าน: login UI สำเร็จ, Inspect Officer loader HTTP 200 JSON (`unread=12`, 10 รายการ), audit reader ในหน่วยงานเดียวกันสำเร็จ, ต่างหน่วยงานถูกปฏิเสธ, unauthenticated loader HTTP 401 JSON
- พบ `img/default-user.svg` 404 และ external CDN warnings ซึ่งไม่เกี่ยวกับ auth/audit blocker
- ไม่มีการ commit หรือ push

---

# Manual FV Certificate Audit Log

เพิ่ม audit trail เฉพาะ Manual FV Certificate ตาม scope: create, edit, delete และ attachment add/delete

## Files Changed

| ไฟล์ | รายละเอียด |
|---|---|
| [private/classes/InspectionLog.class.php](private/classes/InspectionLog.class.php) | เพิ่ม `entity_type`, `entity_id`, `old_values`, `new_values`, `actor_role`; เพิ่ม helper สร้าง/อ่าน Manual Certificate audit |
| [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) | audit Create และ attachment Add หลัง business save สำเร็จใน transaction เดิม |
| [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php) | อ่าน old snapshot ก่อน update, เก็บเฉพาะ field ที่เปลี่ยนใน old/new JSON และ audit attachment Add |
| [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php) | เก็บ certificate/attachment metadata ก่อน hard delete และสร้าง audit หลังลบใน transaction |
| [public/inspectofficer/ajax/fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php) | ทำ attachment file/DB delete และ audit ให้อยู่ใน transaction เดียวกัน |
| [public/inspectofficer/ajax/get_manual_certificate_audit.php](public/inspectofficer/ajax/get_manual_certificate_audit.php) | reader เฉพาะ inspect officer พร้อมตรวจ evaluation agency กับ department |
| [ADD_MANUAL_CERTIFICATE_AUDIT_LOG.sql](ADD_MANUAL_CERTIFICATE_AUDIT_LOG.sql) | migration แบบ backward-compatible และ idempotent สำหรับ MariaDB |
| [SYSTEM_LOG.md](SYSTEM_LOG.md) | อัปเดต schema, event coverage, reader และข้อจำกัด |

## Audit Design

- ไม่ใช้ `inspection_request_id` เก็บ certificate id สำหรับ audit ใหม่
- ใช้ `entity_type='manual_certificate'` และ `entity_id=certificate.id`; ตั้ง `inspection_request_id=0` เพื่อคง compatibility กับ column เดิมที่เป็น NOT NULL
- `old_values` และ `new_values` เป็น JSON แบบ whitelist; Edit เก็บเฉพาะ field ที่เปลี่ยน และไม่สร้าง change event เมื่อไม่มีการเปลี่ยนค่า
- Create เก็บค่า certificate สำคัญหลังสร้าง; Delete เก็บ snapshot สำคัญก่อนลบ; attachment เก็บ id, type และ file name ไม่เก็บ binary
- Actor id/time/IP เติมจาก `DatabaseObject::save()` และ role จาก session
- เพิ่ม action codes `fvscis_attachment_added` และ `fvscis_attachment_deleted`; action codes certificate เดิมถูกใช้ต่อโดยไม่ใช้ numeric id ใหม่
- ทุก audit insert ใน create/update/delete certificate และ attachment delete อยู่หลัง business mutation สำเร็จ; attachment delete เพิ่ม transaction ครอบคลุม file/DB/audit

## Verification

- ตรวจ DDL จริงของ `inspection_logs`, `log_actions`, `fv_sanitation_certification_old` และ `fv_certificate_attachments` จาก MariaDB 10.4.32 ก่อนออกแบบ migration
- Apply migration บน local database สำเร็จ; columns และ action codes ใหม่มีอยู่จริง
- PHP lint ผ่านสำหรับ model, endpoints และ audit reader
- ทดสอบ authorization reader ตาม role/department logic จาก source; ยังไม่ได้ยิง authenticated browser reader หลัง session หมดอายุ
- ยังไม่ได้สร้าง/แก้/ลบ certificate หรือ upload/delete attachment จริง เพราะไม่มี isolated fixture และการทำดังกล่าวเปลี่ยนข้อมูล/ไฟล์ระบบ
- ไม่มีการ commit หรือ push

---

# FVSCIS — EDIT.md

การแก้ไขรอบนี้อ้างอิงผลวิเคราะห์ใน [WORKFLOW.md](WORKFLOW.md) และ Business Rule (Rule B) ที่เจ้าของระบบยืนยัน
**ไม่มีการรัน Runtime Test เต็ม workflow ไม่มีการสร้าง test data และไม่มีการแก้ไขฐานข้อมูลในรอบนี้** ทำเฉพาะแก้ source code + static verification เท่านั้น

---

# Notification Bell / Destination Fix

แก้ไขระบบ Notification / กระดิ่งของ FVSCIS ให้ยึด source of truth ตาม model จริง และให้หน้า bell + รายการแจ้งเตือนไป link ถึงหน้าที่ถูกต้อง โดยไม่พึ่ง field ที่ไม่มีอยู่จริง

## รายละเอียดการแก้ไข

- **Root cause:** หน้า bell และหน้ารายการแจ้งเตือนใช้ helper ที่อ้างอิง `reference_type` / `reference_id` ขณะที่ schema จริงของ `notifications` ไม่มี field เหล่านี้ รวมทั้งบาง endpoint คืน `link => null` หรือ `#`
- **ไฟล์ที่แก้:** [private/classes/Notification.class.php](private/classes/Notification.class.php), [public/js/fvscis.js](public/js/fvscis.js), [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php), [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php), [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php), [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php), [public/headquarter/ajax/load_notifications.php](public/headquarter/ajax/load_notifications.php), [public/fisherman/notifications.php](public/fisherman/notifications.php), [public/inspectofficer/notifications.php](public/inspectofficer/notifications.php), [public/signer/notifications.php](public/signer/notifications.php), [public/admin/notifications.php](public/admin/notifications.php), [public/headquarter/notifications.php](public/headquarter/notifications.php)
- **การแก้ไข:** เพิ่ม `Notification::build_destination($notification, $user_role)` เพื่อ resolve ลิงก์จาก `inspection_request_id` ผ่าน `InspectionRequest::find_by_id()` แล้วใช้ `ship_code` สร้าง target page ตาม role; ปรับ AJAX loader ให้ส่ง `id`, `link`, `message`, `type`, `time` แบบสอดคล้องกัน; ปรับ dropdown bell ให้ mark notification เดี่ยวเป็นอ่านก่อนเปิด link
- **Single-item read:** เพิ่ม `Notification::mark_single_as_read($notification_id, $user_id, $user_role)` และ role-specifc endpoint `notifications_mark_read.php` สำหรับทุก role พร้อม guard ที่จำกัดด้วย `id + user_id + user_role`
- **Read semantics:** ปรับ `mark_action_taken()` ให้เซ็ตเฉพาะ `action_taken = 1` และไม่แตะ `is_read` เพื่อให้ action status และ read status เป็นคนละเรื่อง
- **Cleanup:** ลบไฟล์สำเนาไม่ใช้จริง [public/headquarter/ajax/load_notifications copy.php](public/headquarter/ajax/load_notifications%20copy.php) หลังยืนยันว่าไม่มี code อ้างอิง

## Static Verification

- `php -l private/classes/Notification.class.php` ผ่าน
- `php -l public/fisherman/ajax/load_notifications.php` ผ่าน
- `php -l public/inspectofficer/ajax/load_notifications.php` ผ่าน
- `php -l public/signer/ajax/load_notifications.php` ผ่าน
- `php -l public/admin/ajax/load_notifications.php` ผ่าน
- `php -l public/headquarter/ajax/load_notifications.php` ผ่าน
- `php -l public/fisherman/notifications.php` ผ่าน
- `php -l public/inspectofficer/notifications.php` ผ่าน
- `php -l public/signer/notifications.php` ผ่าน
- `php -l public/admin/notifications.php` ผ่าน
- `php -l public/headquarter/notifications.php` ผ่าน
- `php -l public/fisherman/notifications_mark_read.php` ผ่าน
- `php -l public/inspectofficer/notifications_mark_read.php` ผ่าน
- `php -l public/signer/notifications_mark_read.php` ผ่าน
- `php -l public/admin/notifications_mark_read.php` ผ่าน
- `php -l public/headquarter/notifications_mark_read.php` ผ่าน
- `php -l public/js/fvscis.js` ผ่าน
- ไม่มี automated test หรือ runtime check ตามข้อกำหนด; ให้เจ้าของระบบทดสอบจริงใน browser ต่อไป

## Notification Runtime Test Result

- ทดสอบจริงด้วย session `inspectofficer` บน XAMPP: loader ตอบ HTTP 200 JSON, badge แสดง 13, dropdown แสดง 10 รายการ และ polling ทำงานห่างประมาณ 60 วินาที
- Single read notification id `46` ผ่าน: unread ลด 13 เหลือ 12, `is_read=1`, `action_taken` คงเดิมที่ 0; อ่านซ้ำ/wrong user/wrong role ตอบ failure และไม่ update
- Dropdown และหน้า `notifications.php` ใช้ destination เดียวกัน (`incoming_requests.php` ในรายการที่ทดสอบ)
- Mark-all ตรวจแบบ rollback-only transaction: เปลี่ยนเฉพาะ `is_read`, ไม่เปลี่ยน `action_taken`; ยังไม่ได้ mark-all จริงเพื่อรักษาข้อมูลระบบ
- Route เดิมที่เคยรายงาน 404 เกิดจาก unauthenticated request ถูก redirect ไป `inspectofficer/login.php` ที่ไม่มีอยู่; authenticated browser session เรียก loader ได้จริง
- **ยังทดสอบไม่ได้:** end-to-end ของ fisherman/signer/admin/headquarter, workflow สร้าง notification จริง, duplicate workflow และ regression ครบทุก role เนื่องจากไม่มี credential/test data ที่ปลอดภัยสำหรับสร้างข้อมูลจริง
- **Data finding:** notification หลายรายการชี้ไป `inspection_request_id` ที่ไม่พบใน `inspection_requests`; destination จึงเป็น fallback generic และยังยืนยัน ship code ไม่ได้

---

# Certificate Attachment Document Type Fix

แก้ bug ที่การเพิ่มหรือลบไฟล์ใน Modal Add และ Modal Edit ทำให้ประเภทเอกสารของไฟล์เดิมกลับเป็น `ทะเบียนเรือ`

## รายละเอียดการแก้ไข

- **Root cause:** preview ถูก rebuild ใหม่ทั้งชุดทุกครั้งที่เพิ่ม/ลบไฟล์ แต่ `<select>` ที่สร้างใหม่ไม่ได้รับค่าที่ผู้ใช้เลือกไว้ จึงใช้ option แรกเป็นค่าใหม่โดยอัตโนมัติ และตอน submit ยังอ่าน selector แบบ global
- **ไฟล์ที่แก้:** [public/inspectofficer/old_certification.php](public/inspectofficer/old_certification.php), [public/inspectofficer/all_old_certification.php](public/inspectofficer/all_old_certification.php)
- **Modal Add:** เพิ่ม `_selectedTypes` เป็น state array ที่เรียงตำแหน่งคู่กับ `_selectedFiles`; ก่อน rebuild จะเก็บค่าจาก dropdown เดิม, ตอนเพิ่ม/ลบจะเพิ่มหรือลบ type ตำแหน่งเดียวกัน และตอน render จะ restore ค่าเดิม โดยใช้ `ทะเบียนเรือ` เฉพาะรายการใหม่
- **Modal Edit:** เพิ่ม `selectedTypes` ใน data ของ input สำหรับไฟล์ใหม่; ค่าเดิมจากฐานข้อมูลยังแสดงจาก `attachment_type` ตามเดิม, ส่วนไฟล์ใหม่จะ preserve/restore type เมื่อเพิ่มหรือลบไฟล์ และลบ type คู่กันตาม index
- **การจับคู่ตอน Save:** submit อ่าน `<select>` ภายใน `#form-fvscisold-add` หรือ `#form-fvscisold-edit` เท่านั้น แล้วส่ง type ตาม index เดียวกับ `attachments[]`; endpoint เดิมจึงบันทึกประเภทของแต่ละไฟล์ได้ถูกคู่
- **Refactor:** ไม่มี refactor ใหญ่ แก้เฉพาะ logic state/render ของ attachment ที่ซ้ำกันในสองหน้าซึ่งเป็น code path ที่ใช้งานจริง
- **ขอบเขต:** ไม่ได้แก้ business rule PASS/FAIL, ACTIVE/INACTIVE/EXPIRED, schema database หรือเพิ่ม index ใด ๆ

## Static Verification

- `php -l public/inspectofficer/old_certification.php` ผ่าน
- `php -l public/inspectofficer/all_old_certification.php` ผ่าน
- VS Code ตรวจไฟล์ทั้งสองแล้วไม่พบ error
- ไม่มี automated test, manual test หรือ test data ตามข้อกำหนด; ให้เจ้าของระบบทดสอบ runtime

---

## Summary

รอบนี้แก้ไข Critical Workflow ของการอนุมัติ/ยืนยันผลตรวจ (`inspection_requests` ↔ `fv_sanitation_certification_old`) ให้ตรงกับ State Matrix (Case A–G) และ Rule B ที่กำหนด โดย:

1. ปิด workflow ให้จบสมบูรณ์: `inspection_requests.status = completed` เมื่ออนุมัติผ่าน/มีเงื่อนไขสำเร็จ (เดิมมีแต่ `is_complete = 1` ไม่เคย set status)
2. เพิ่ม backend guard ป้องกันการอนุมัติซ้ำ (ก่อนเรียก `DocumentCounter::next_code_by_effective()`) พร้อม row lock (`SELECT ... FOR UPDATE`) กัน race condition เบื้องต้น
3. แก้ `vessel_status` ให้เก็บผลตรวจเดิม (`passed`/`conditional`/`failed`) แทนการเก็บ `completed` ทับผลตรวจ ทั้งฝั่งอนุมัติผ่านและฝั่งยืนยันไม่ผ่าน
4. ใช้งาน Rule B: กรณีมีใบรับรองเดิม `active` และยังไม่หมดอายุ + ผลตรวจใหม่ `failed` → คงใบเดิมเป็น `active` และ append remark แทนการเขียนทับ
5. เมื่อออกใบใหม่ (passed/conditional) สำเร็จ → ปิดใบ `active` เดิมของเรือลำเดียวกันเป็น `inactive` (คงไว้เป็น history ไม่ลบ/ไม่ทับ)
6. แก้ dead binding `#oc_remark` ในหน้า signer เพื่อให้แสดง remark ได้จริง
7. แก้ regression ของปุ่มเปิด PDF ฝั่งเจ้าหน้าที่ตรวจใน `incoming_requests.php` ที่จะพังหลังจาก status ถูกเปลี่ยนเป็น `completed`
8. ลบ dead assignment `$request->result` (ยืนยันแล้วว่าไม่มีผลใด ๆ เพราะไม่อยู่ใน `$db_columns`)

---

## Files Changed

| ไฟล์ | หน้าที่ของการแก้ |
|---|---|
| [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) | เพิ่ม guard กันอนุมัติซ้ำ (ก่อน + หลัง lock), ย้าย `DocumentCounter::next_code_by_effective()` ไปอยู่หลัง guard, ปิด request เป็น `status=completed`, แก้ `vessel_status` ให้ใช้ `$evaluation_status`, เพิ่มการปิดใบ `active` เดิมด้วย `deactivate_other_active()`, ลบ `$request->result = ...` (dead code) |
| [public/signer/ajax/confirm_fail.php](public/signer/ajax/confirm_fail.php) | เก็บ `$evaluation_status` ก่อน mutate, แก้ `vessel_status` ให้ใช้ `$evaluation_status` แทน `$request->status` (เดิมจะกลายเป็น `completed` ผิดความหมาย), เพิ่ม Rule B: หาใบเดิม active+ไม่หมดอายุ แล้ว append remark แทนการปิดใบ |
| [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) | เพิ่ม method ใหม่ 3 ตัว: `find_active_unexpired_by_ship_code()`, `append_remark()`, `deactivate_other_active()` — ไม่มีการแก้ method เดิมที่มีอยู่ |
| [public/inspectofficer/incoming_requests.php](public/inspectofficer/incoming_requests.php) | แก้ regression: เพิ่ม `$isCompletedFailed` / `$isCompletedApproved` (derived จาก `is_complete` + `status===completed`) แทนการเช็ค `$isComplete && $isPass/$isFailed/$isCondition` เดิมซึ่งจะไม่เป็นจริงอีกต่อไปหลัง status ถูกปิดเป็น `completed` |
| [public/signer/old_certification.php](public/signer/old_certification.php) | เพิ่ม `<div id="oc_remark">-</div>` ในส่วน modal ที่เดิมมีแต่ placeholder `...` เพื่อให้ JS ที่มีอยู่แล้ว (`$('#oc_remark').text(...)`) มี element ให้ผูกจริง |

**ไม่ได้แตะ:** `create_fvscisold.php`, `fvscis_old_create.php`, schema/DDL ใด ๆ, PDF template files, `STATUS_CANCELLED`, `request_date` inconsistency, `vessel_status` redesign เชิงโครงสร้าง

---

## Certification State Logic (หลังแก้)

- **Passed (ไม่มีใบเดิม / Case A):** `inspection_requests.status = completed`, `is_complete=1`, INSERT `fv_sanitation_certification_old` (`status=active`, มี `certificate_number`, `vessel_status='passed'`) `deactivate_other_active()` ทำงานแต่ไม่มีแถวให้ปิด (no-op)
- **Conditional (ไม่มีใบเดิม / Case B):** เหมือน Passed แต่ `expiration_date = effective_date + 90 วัน`, `vessel_status='conditional'`
- **Passed/Conditional + มีใบเดิม active (Case C/D):** เหมือนข้างต้น + หลัง insert ใบใหม่สำเร็จ เรียก `FvSanitationCertificationOld::deactivate_other_active($ship_code, $new_id)` ปิดใบ `active` เดิม (เฉพาะแถวที่ status ยังเป็น `active`) เป็น `inactive` แถวเดิมยังอยู่ในตาราง (ไม่ลบ ไม่ทับข้อมูล)
- **Failed + มีใบเดิม active ยังไม่หมดอายุ (Case E / Rule B):** `inspection_requests.status = completed` (เหมือนเดิม), INSERT แถวใหม่ `status=fail`, `certificate_number=null`, `vessel_status='failed'` — **ใบเดิมยังคง `active`** ไม่ถูกแตะสถานะ มีเพียง `remark` ของใบเดิมถูก append ข้อความเตือน
- **Failed ไม่มีใบเดิม active (Case F):** เหมือน Case E แต่ไม่มีใบเดิมให้ append remark (ตรวจแล้วไม่พบ → ไม่ทำอะไรเพิ่ม)
- **Approve ซ้ำ (Case G):** guard ปฏิเสธตั้งแต่ก่อนเข้า transaction (เช็ค `is_complete`/`status`/`approved_at` จาก object ที่โหลดมา) และตรวจซ้ำอีกครั้งแบบ lock แถวจริงในฐานข้อมูลหลัง `begin_transaction()` ก่อนเรียก `DocumentCounter` เสมอ

---

## Remark Logic

- **จุดที่ append remark:** [public/signer/ajax/confirm_fail.php](public/signer/ajax/confirm_fail.php) หลัง insert แถว `fail` ใหม่สำเร็จ เรียก `FvSanitationCertificationOld::find_active_unexpired_by_ship_code($ship_code)` แล้ว `append_remark()` ที่ใบเดิม (ถ้ามี)
- **ข้อความที่ใช้:** `ผลการตรวจครั้งล่าสุดไม่ผ่าน อยู่ระหว่างดำเนินการแก้ไข` (ตรงตามที่กำหนด)
- **วิธีรักษา remark เดิม:** `FvSanitationCertificationOld::append_remark()` ใน [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) จะ `SELECT remark` เดิมก่อน ถ้าว่าง (`trim() === ''`) จะบันทึกข้อความใหม่ตรง ๆ ถ้ามีข้อความเดิมอยู่แล้วจะต่อท้ายด้วย `"\n"` แล้วค่อย UPDATE (ไม่มีการ SET ทับโดยไม่เช็คค่าเดิม) — ทำให้ `approval_note` เดิม (ถ้าเคยถูกเก็บไว้ใน remark จากการอนุมัติครั้งก่อน) ไม่สูญหาย
- **หน้า signer ที่เพิ่มการแสดง remark:** [public/signer/old_certification.php](public/signer/old_certification.php) เพิ่ม `<div id="oc_remark">-</div>` ในโมดัลดูรายละเอียดใบรับรองเก่า (element เดิมไม่มีอยู่เลย ทำให้ `$('#oc_remark').text(d.remark || '-')` ที่มีอยู่แล้วในไฟล์นี้ใช้งานได้จริงเป็นครั้งแรก)

---

## Duplicate Approval Protection

- **Guard ที่เพิ่ม:** ใน [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php) มี 2 ชั้น
  1. ก่อนเข้า transaction: เช็คจาก object ที่ query มา (`is_complete==1` หรือ `status===completed` หรือ `approved_at` ไม่ว่าง) → ตอบกลับ `success:false` ทันที
  2. หลัง `begin_transaction()`: `SELECT is_complete, status, approved_at FROM inspection_requests WHERE id=? FOR UPDATE` แล้วเช็คเงื่อนไขเดียวกันซ้ำจากข้อมูลที่ล็อกไว้จริง ก่อนเรียก `DocumentCounter::next_code_by_effective()`
- **Guard อยู่ก่อน DocumentCounter หรือไม่:** อยู่ก่อนแน่นอน — ย้ายการเรียก `DocumentCounter::next_code_by_effective()` มาไว้หลังจากผ่าน lock-guard ทั้งสองชั้นแล้วเท่านั้น (เดิมอยู่ก่อน `begin_transaction()` ด้วยซ้ำ)
- **Transaction/locking:** ใช้ `$database->begin_transaction()` (มีอยู่แล้วเดิม) ร่วมกับ `SELECT ... FOR UPDATE` (เพิ่มใหม่) เพื่อล็อกแถว `inspection_requests` ระหว่างตรวจสอบซ้ำจนถึงตอน commit
- **Concurrency risk ที่ยังเหลือ:**
  - `confirm_fail.php` **ไม่ได้เพิ่ม row lock** เหมือน `approve_request.php` เพราะ endpoint นี้ไม่เรียก `DocumentCounter` (ไม่เสี่ยงเลขที่ซ้ำ) และมี guard เดิม (`status !== STATUS_FAILED` → reject) ที่ป้องกันการยืนยันซ้ำได้ในทางปฏิบัติอยู่แล้ว (หลังยืนยันครั้งแรก status จะกลายเป็น `completed` ทันที) — แต่ยังมีช่องโหว่ race condition เชิงทฤษฎีหากมีการยิง request พร้อมกันสองครั้งในเสี้ยววินาทีก่อน commit ครั้งแรก (ยังไม่ได้ใส่ `FOR UPDATE` ในไฟล์นี้เพื่อจำกัดขอบเขตการแก้ไขตามที่ระบุไว้ — ถือเป็นความเสี่ยงคงเหลือระดับต่ำ ไม่ได้แก้ในรอบนี้)
  - หากในอนาคตต้องรองรับ concurrency ระดับสูงกว่านี้ (เช่น หลาย process/หลาย server พร้อมกันจำนวนมาก) ควรพิจารณา unique constraint ระดับ DB (เช่น unique index บน `inspection_requests.id + is_complete` เชิง logic หรือ application-level distributed lock) — เกินขอบเขตที่อนุญาตให้แก้ในรอบนี้ (ต้อง schema change) จึงบันทึกไว้เป็นความเสี่ยงคงเหลือเท่านั้น

---

## PDF Impact

- **ไฟล์ PDF ที่ถูกแก้:** ไม่มี — ไม่ได้แตะไฟล์ PDF/template ใด ๆ ตามข้อกำหนด (ห้าม redesign PDF)
- **ไฟล์ที่ตรวจสอบ (static) ว่าพึ่งพา `request.status` โดยตรง:**
  - [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php) และ [public/signer/generate_pdf.php](public/signer/generate_pdf.php): มีการเช็ค `$request->status === 'failed'/'passed'/'conditional'` จริง แต่โค้ดทั้งหมดอยู่ภายใน `if (!$old_document_lock) { ... }` ซึ่งทำงาน**ครั้งเดียว**ตอนเจ้าหน้าที่ตรวจส่งผลครั้งแรก (ตอนนั้น `document_locked` ยังเป็น 0 และ `inspection_requests.status` ยังเป็น `passed/failed/conditional` เสมอ เพราะการอนุมัติของผู้ลงนามยังไม่เกิดขึ้น) → **ไม่ได้รับผลกระทบ** จากการเปลี่ยน status เป็น `completed` เพราะ path นี้ไม่มีวันถูกเรียกซ้ำหลัง `document_locked=1`
  - [public/signer/certificate_preview.php](public/signer/certificate_preview.php) และ [public/signer/gen_pdf_result_fvs031-034.php](public/signer/gen_pdf_result_fvs031.php): ตรวจแล้วไม่มีการอ้างอิง `request->status` เพื่อแยก template เลย (certificate_preview.php ใช้ `$request->expire_at`, ข้อมูลจาก e-License และ `InspectionFormStatus` เท่านั้น) → ไม่ได้รับผลกระทบ นอกจากนี้ยังตรวจไม่พบไฟล์ใดในระบบที่เรียกใช้ `gen_pdf_result_fvs031-034.php` เลย (grep ทั้ง repo ไม่เจอ caller) — บันทึกไว้เป็นข้อสังเกต ไม่ใช่จุดที่ต้องแก้
  - [public/inspectofficer/incoming_requests.php](public/inspectofficer/incoming_requests.php): **พบ regression จริง** — ปุ่ม "หนังสือ สร.3" (บล็อกที่สองในไฟล์นี้ ใช้เงื่อนไข `$isComplete && $isPass/$isFailed/$isCondition`) จะไม่มีวันแสดงอีกหลังจาก `status` ถูกปิดเป็น `completed` (เพราะ `$isPass/$isFailed/$isCondition` เทียบกับค่า status ที่ตอนนี้ไม่ใช่ passed/failed/conditional อีกแล้ว) — **แก้แล้ว** โดยเพิ่มตัวแปร `$isCompletedFailed`/`$isCompletedApproved` ที่แยกแยะจาก `is_complete` (true เฉพาะ path อนุมัติผ่านของ `approve_request.php`) ร่วมกับ `status===completed` แทน ไม่กระทบ UI ส่วนอื่นในไฟล์เดียวกัน (บล็อกแรกที่ใช้ `$isPass/$isFailed/$isCondition` ตรง ๆ ยังทำงานถูกต้องเหมือนเดิม เพราะมีไว้แสดงก่อนอนุมัติซึ่ง status ยังไม่ใช่ completed)
- **ข้อมูลที่ใช้แยก Passed/Conditional หลังแก้:** ในกรณีที่ต้องแยกแยะหลัง `status=completed` ใช้ `inspection_requests.is_complete` (1 = อนุมัติผ่าน/มีเงื่อนไขสำเร็จ, 0/null = ยืนยันไม่ผ่าน) ร่วมกับ `status===completed` — ไม่ต้องเพิ่มคอลัมน์ใหม่ ใช้ field ที่มีอยู่แล้ว

---

## Paper / Historical Flow

- **ไม่ได้แก้:** [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) และ [public/inspectofficer/ajax/fvscis_old_create.php](public/inspectofficer/ajax/fvscis_old_create.php) ไม่ถูกแตะเลยในรอบนี้ ตามข้อกำหนด
- **Finding (ตามที่ระบุให้บันทึกแทนการแก้):** ยืนยันจาก source ว่า endpoint ทั้งสองนี้สร้างแถวใน `fv_sanitation_certification_old` โดยตรงจากฟอร์มที่เจ้าหน้าที่กรอกเอง (`$_POST['FvSanitationCertificationOld']`) โดย**ไม่มีการอ้างอิงหรือสร้างแถวใน `inspection_requests` เลย** และไม่มี logic ใดใน endpoint เหล่านี้ที่จะ generate สร.3 อิเล็กทรอนิกส์ (ไม่เรียก `DocumentCounter`, ไม่เรียก PDF generator) — จึงยืนยันได้ว่า **manual/paper record ไม่สามารถ generate electronic สร.3 ได้** ในสถานะปัจจุบันของ source code (ไม่มีการอ้างอิงเลขที่เอกสารแบบเดียวกับ flow ออนไลน์) ไม่พบความเสี่ยงที่ manual flow จะรั่วไปสร้างใบ electronic ได้เอง

---

## Static Verification

- **PHP syntax check (`php -l`):** รันกับทุกไฟล์ที่แก้ไข ผลลัพธ์ "No syntax errors detected" ทุกไฟล์:
  - `public/signer/ajax/approve_request.php`
  - `public/signer/ajax/confirm_fail.php`
  - `private/classes/FvSanitationCertificationOld.class.php`
  - `public/inspectofficer/incoming_requests.php`
  - `public/signer/old_certification.php`
- **Static verification อื่นที่ทำ:**
  - grep ยืนยันว่า `$request->result` (ที่ลบออก) ไม่ปรากฏที่อื่นในระบบ (ไม่มีที่ใดอ่านค่านี้กลับมาใช้)
  - grep ยืนยันขอบเขตผลกระทบของ `isComplete`/`isPass`/`isFailed`/`isCondition` พบเฉพาะใน `public/inspectofficer/incoming_requests.php` ไฟล์เดียวในระบบ (ไม่มีไฟล์อื่นที่ต้องแก้ตามรูปแบบเดียวกัน)
  - grep ยืนยันว่าไม่มีไฟล์ PDF/template อื่นที่อ่าน `request->status` เพื่อแยกประเภทใบรับรอง นอกจาก `generate_pdf.php` ทั้งสองตัวที่ตรวจสอบแล้วว่าไม่ได้รับผลกระทบ (อยู่ใน branch ที่รันครั้งเดียวก่อนอนุมัติ)
  - ตรวจสอบ method เดิม `mark_active()`/`mark_inactive()`/`mark_fail()`/`mark_pending()` ใน `FvSanitationCertificationOld` ไม่ได้ถูกแก้ไข (ยังทำงานเหมือนเดิมทุกจุดที่เคยเรียกอยู่: `request_inspection.php` เรียก `mark_pending()`, `create_fvscisold.php` เรียก `mark_active()`/`mark_fail()`) เพื่อไม่ให้กระทบ flow อื่นที่ใช้ methods เหล่านี้อยู่แล้ว
- **ไม่มี runtime test ใด ๆ ในรอบนี้ตามคำสั่ง** — ไม่มีการสร้าง/แก้ไขข้อมูลจริงในฐานข้อมูล ไม่มีการยิง endpoint จริงเพื่อทดสอบ

---

## Owner Runtime Test Required

ทุก case ด้านล่างนี้ **NOT TESTED — OWNER TO TEST**

- **Case A — Passed ไม่มีใบเดิม:** `NOT TESTED — OWNER TO TEST`
- **Case B — Conditional ไม่มีใบเดิม:** `NOT TESTED — OWNER TO TEST`
- **Case C — มีใบเดิม Active + Passed:** `NOT TESTED — OWNER TO TEST`
- **Case D — มีใบเดิม Active + Conditional:** `NOT TESTED — OWNER TO TEST`
- **Case E — มีใบเดิม Active ยังไม่หมดอายุ + Failed (Rule B):** `NOT TESTED — OWNER TO TEST`
- **Case F — Failed ไม่มีใบ Active เดิม:** `NOT TESTED — OWNER TO TEST`
- **Case G — Approve ซ้ำ:** `NOT TESTED — OWNER TO TEST`

---

## Remaining Risks / Open Issues

- **Concurrency ใน `confirm_fail.php`:** ไม่มี `SELECT ... FOR UPDATE` เหมือน `approve_request.php` (ดูเหตุผลในหัวข้อ Duplicate Approval Protection) — ความเสี่ยงต่ำแต่ยังไม่ปิดสนิททางทฤษฎี
- **`request_date` inconsistency** (`submitted_at` vs `created_at` ระหว่าง approve/confirm_fail) — ตามข้อกำหนดข้อ 13 **ห้ามแตะ** ในรอบนี้ ยังคงเป็นปัญหาที่บันทึกไว้ใน [WORKFLOW.md](WORKFLOW.md) ปัญหาที่ 8
- **`STATUS_CANCELLED`** ที่ยังไม่พบจุด set ค่าจริงในระบบ (Unreachable Status) — ตามข้อกำหนดข้อ 13 **ห้ามแตะ** ในรอบนี้
- **`vessel_status` เป็น snapshot ที่อาจไม่ sync กับ `inspection_requests.status` ในอนาคต** (Duplicate Responsibility ตาม WORKFLOW.md ปัญหาที่ 5) — รอบนี้แก้เฉพาะให้ snapshot ค่าเริ่มต้นถูกต้อง (`passed/conditional/failed` แทน `completed`) แต่ไม่ได้ redesign กลไก sync ทั้งระบบ
- **`gen_pdf_result_fvs031-034.php`:** ตรวจไม่พบ caller ใด ๆ ในระบบ (dead/ไม่ถูกเรียกใช้จาก UI ปัจจุบัน) — ไม่ได้แก้ไข เพราะไม่อยู่ในขอบเขตงาน เป็นเพียงข้อสังเกตให้เจ้าของระบบพิจารณา
- **Paper/manual flow (`create_fvscisold.php`, `fvscis_old_create.php`):** ยืนยันแล้วว่าไม่สามารถ generate electronic สร.3 ได้ในปัจจุบัน (ดูหัวข้อ Paper/Historical Flow) ไม่มีความเสี่ยงเพิ่มเติมที่พบจากการตรวจสอบรอบนี้
- **Database schema ยังไม่ได้ตรวจสอบจริง**: การวิเคราะห์ทั้งหมดอิงจาก `$db_columns` ใน PHP class เท่านั้น (ไม่ได้เชื่อมต่อฐานข้อมูลเพื่อ verify DDL จริงตามข้อกำหนด) หากมี column เพิ่มเติมในฐานข้อมูลจริงที่ไม่ปรากฏใน class (เช่น `result`) จะไม่ถูกครอบคลุมโดยการวิเคราะห์นี้

---

# Paper Certification Single Active Fix

รอบนี้แก้เฉพาะ Paper/Historical Certification **create flow** (บันทึกใบรับรอง สร.3 ใหม่ด้วยมือ) ให้เรือหนึ่งลำมีใบรับรอง `status=active` ได้เพียง 1 ใบในเวลาเดียวกัน ตาม Business Rule ที่กำหนด **ไม่มีการรัน Runtime Test, ไม่มีการสร้าง test data, ไม่มีการแก้ไขข้อมูลเดิมในฐานข้อมูล** — ทำเฉพาะแก้ source + `php -l` static check เท่านั้น

## 1. Files Changed

| ไฟล์ | สิ่งที่แก้ |
|---|---|
| [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) | เพิ่ม method ใหม่ `find_all_active_unexpired_by_ship_code()` (คืนทุกแถวที่ `status=active` และยังไม่หมดอายุของ ship_code นั้น ใช้ตรวจจำนวน) — ไม่ได้แก้ method เดิมใด ๆ (`mark_active()`, `mark_fail()`, `mark_pending()`, `mark_inactive()`, `find_active_unexpired_by_ship_code()`, `deactivate_other_active()` ที่มีอยู่แล้วจากรอบก่อนหน้ายังคงเดิมทุกตัว) |
| [public/inspectofficer/ajax/create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) | เพิ่ม Single-Active Guard ก่อน insert: ตรวจจำนวนใบ active+unexpired ของ ship_code, reject ถ้ามากกว่า 1 ใบ (data inconsistency), ขอ confirmation ถ้ามี 1 ใบ, แทนที่การเรียก `mark_active()` เดิมด้วย `deactivate_other_active()` เมื่อยืนยันแล้ว |
| [public/inspectofficer/all_old_certification.php](public/inspectofficer/all_old_certification.php) | แก้ JS submit handler ของฟอร์ม Add ให้รองรับ response `need_confirmation` (แสดง SweetAlert แล้ว resubmit พร้อม `confirm_replace_active=1`) และ `inconsistency` (แสดง error, ไม่ resubmit) |
| [public/inspectofficer/old_certification.php](public/inspectofficer/old_certification.php) | แก้ JS submit handler ของฟอร์ม Add แบบเดียวกัน (หน้านี้มีสำเนา JS แยกต่างหากจากไฟล์ข้างบน) พร้อมลบตัวแปร `fd` ที่ไม่ได้ใช้ซึ่งหลงเหลือจากโค้ดเดิม |

**ไม่ได้แตะ:** `public/inspectofficer/ajax/fvscis_old_create.php` — ตรวจยืนยันด้วย grep แล้วว่าไม่มีไฟล์ใดในระบบเรียกใช้ endpoint นี้เลย (ไม่มี AJAX url ใดชี้มาที่ไฟล์นี้) จึงเป็น dead code อยู่แล้ว ไม่ใช่เส้นทางที่ใช้งานจริง ไม่จำเป็นต้องแก้ตาม scope ("แก้เฉพาะจุดที่ใช้งานจริง")

## 2. Warning Logic

- Client (JS) ยังไม่ต้องเช็คก่อน submit แบบ live — ใช้รูปแบบ **submit-then-confirm-then-resubmit** แทน: ครั้งแรก submit ฟอร์มตามปกติ (ไม่มี `confirm_replace_active`) → ถ้า backend พบว่ามีใบ active+unexpired อยู่ 1 ใบ จะปฏิเสธการบันทึกทันที (ยังไม่ insert อะไรเลย) แล้วส่งรายละเอียดใบเดิมกลับมาให้ JS แสดง SweetAlert ตามข้อความที่กำหนดเป๊ะ ๆ (เลขที่/มีผลตั้งแต่/หมดอายุ/ข้อความเตือน/ปุ่มดำเนินการต่อ-ยกเลิก)
- ถ้าผู้ใช้กด "ยกเลิก" ใน SweetAlert: ไม่มีการเรียก AJAX ซ้ำ ไม่มี state ใดถูกเปลี่ยนแปลง (เพราะ backend ปฏิเสธไปตั้งแต่รอบแรกโดยยังไม่ insert/update อะไรเลย)
- ถ้าผู้ใช้กด "ดำเนินการต่อ": JS resubmit ฟอร์มเดิม + `confirm_replace_active=1` ไปยัง endpoint เดิม

## 3. Active-Unexpired Detection

ใช้เงื่อนไขเดียวกับที่กำหนด: `status = 'active' AND expiration_date IS NOT NULL AND expiration_date >= วันนี้` — implement ใน `FvSanitationCertificationOld::find_all_active_unexpired_by_ship_code($ship_code)` ([private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php)) เรียกใช้ใน [create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) ก่อนสร้าง record ใหม่ทุกครั้งที่ค่า `status` ของใบใหม่จะเป็น `active` (ถ้าใบใหม่เป็น `fail` ตาม `certificate_status='ไม่ผ่าน'` จะไม่ตรวจ/ไม่กระทบ logic นี้เลย ตาม scope เดิม)

## 4. Multiple-Active Detection

ถ้า `count(find_all_active_unexpired_by_ship_code($ship_code)) > 1` → **reject ทันที** ก่อน insert ใด ๆ, คืน `{success:false, inconsistency:true, message:'พบใบรับรองที่ยังใช้งานอยู่มากกว่า 1 รายการ กรุณาตรวจสอบข้อมูลก่อนบันทึกใบรับรองใหม่'}` — ไม่มี logic ใดเลือกใบใดใบหนึ่งเองจาก expiration date/created_at/id/certificate number ตามที่ห้ามไว้ (JS แสดง error แล้วหยุด ไม่มีทาง resubmit ให้ผ่านกรณีนี้ได้)

## 5. Backend Guard

Guard อยู่ใน [create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) หัวข้อ "1.1) Single-Active Guard" **ก่อน** `$cert = new FvSanitationCertificationOld($attrs); $cert->save();` เสมอ (server-side จริง ไม่ใช่แค่ JS validation — ผู้ใช้ bypass JS ไม่ได้เพราะ backend เช็คซ้ำทุกครั้ง):
- ไม่พบ active+unexpired → insert ปกติ
- พบ 1 ใบ + ไม่มี `confirm_replace_active` ใน POST → reject พร้อม `need_confirmation:true` และรายละเอียดใบเดิม
- พบ 1 ใบ + มี `confirm_replace_active=1` → insert ใบใหม่ แล้ว deactivate ใบเดิม (ดูข้อ 7)
- พบมากกว่า 1 ใบ → reject เสมอไม่ว่าจะส่ง `confirm_replace_active` มาหรือไม่ก็ตาม (ไม่มี bypass ทางใดให้ผ่านกรณีนี้)

## 6. Transaction/Atomicity

ไฟล์ [create_fvscisold.php](public/inspectofficer/ajax/create_fvscisold.php) มี `begin_transaction()` อยู่แล้วตั้งแต่ต้นไฟล์ (ก่อน parse `$_POST`) และมี `commit()`/`rollback()` เดิมอยู่แล้วในโครงสร้าง try/catch — รอบนี้ **ไม่ต้อง refactor transaction ใหม่** เพียงแทรก guard logic เข้าไปในลำดับที่ถูกต้อง (ตรวจ → insert → deactivate ใบเดิม) ทั้งหมดยังอยู่ภายใน transaction เดียวกันเดิม:
- ถ้า guard reject (พบ >1 ใบ หรือยังไม่ confirm) → เรียก `rollback()`/`autocommit(true)` แล้ว `exit` ทันที ก่อนมี insert ใด ๆ เกิดขึ้น (ไม่มี partial state)
- ถ้า insert ใบใหม่ล้มเหลว (`$cert->save()` return false) → เข้า `catch` เดิมที่มี `rollback()` อยู่แล้ว (ไม่ได้แก้ path นี้)
- ถ้า insert สำเร็จแล้ว `deactivate_other_active()` ล้มเหลว (query fail) → **ยังไม่ได้เพิ่ม explicit check ผลลัพธ์ของ `deactivate_other_active()` แยกเพื่อ throw exception** เพราะ method นี้ (ที่เพิ่มไว้ตั้งแต่รอบก่อนหน้า) คืน `int|false` แต่การเรียกใน `create_fvscisold.php` รอบนี้ไม่ได้เช็ค return value — **นี่คือความเสี่ยงคงเหลือ**: ในกรณีที่ query UPDATE ปิดใบเดิมล้มเหลวจริง (โอกาสต่ำมากเพราะเป็น query ธรรมดาไม่มีเงื่อนไขซับซ้อน) จะยัง commit ต่อไปโดยใบเดิมไม่ถูกปิด ทำให้เกิด active 2 ใบได้ในทางทฤษฎี — บันทึกไว้เป็น Remaining Risk (ไม่ refactor transaction ใหญ่เพิ่มตามข้อกำหนด "ถ้า architecture ทำ transaction ยาก ให้รายงานก่อน ห้าม refactor ใหญ่เอง" จึงเลือกรายงานความเสี่ยงนี้แทนการเปลี่ยนโครงสร้าง error handling ทั้งไฟล์)

## 7. Single-Active Update Logic

หลัง `$cert->save()` สำเร็จ (สร้างใบใหม่แล้ว, `status` ของใบใหม่ = `active` อยู่แล้วตามค่าที่ตั้งไว้ก่อน insert):
```php
if ($attrs['status'] == 'fail') {
    FvSanitationCertificationOld::mark_fail($cert->ship_code);
} elseif ($old_to_deactivate_id !== null) {
    FvSanitationCertificationOld::deactivate_other_active($cert->ship_code, (int)$cert->id);
}
```
- `$old_to_deactivate_id` ถูกจำไว้ตอน guard ผ่าน (เฉพาะกรณีเคยพบ 1 ใบ active+unexpired และผู้ใช้ยืนยันแล้ว) — ถ้าไม่เคยมีใบ active เดิมเลย (`$old_to_deactivate_id === null`) จะไม่เรียกอะไรเพิ่มเติม (ไม่จำเป็น เพราะไม่มีใบอื่นให้ปิด)
- `deactivate_other_active($ship_code, $exclude_id)` (method ที่มีอยู่แล้วจากรอบก่อนหน้า ไม่ได้แก้ไขใหม่) รัน `UPDATE ... SET status='inactive' WHERE ship_code=? AND status='active' AND id<>?` — ปิดเฉพาะแถวอื่นที่เป็น `active` เท่านั้น ไม่แตะแถวใหม่ที่เพิ่ง insert (`id<>?` กันไว้) และไม่ลบแถวใดเลย (เป็น UPDATE ไม่ใช่ DELETE)

## 8. วิธีที่ทำให้ใบใหม่ active และใบเดิม inactive

```
ก่อน:  certificate A (id=X) = active, unexpired
บันทึกใบใหม่ certificate B สำเร็จ (status ถูกกำหนดเป็น 'active' ตั้งแต่ก่อน insert จาก $attrs['certificate_status'])
หลัง:  certificate B (id=Y) = active   (ค่าที่ตั้งไว้ตั้งแต่แรก ไม่ได้ไปเลือกทีหลัง)
       certificate A (id=X) = inactive (ปิดโดย deactivate_other_active(ship_code, Y) หลัง insert สำเร็จ)
```
แถว A ยังอยู่ในฐานข้อมูลครบทุกฟิลด์เดิม (เป็น UPDATE เฉพาะคอลัมน์ `status` เท่านั้น)

## 9. ยืนยันว่าไม่ใช้ expiration date ไกลที่สุดเป็นตัวตัดสิน

**ยืนยัน**: ใน create flow นี้ **ไม่มีการเรียก `mark_active()` อีกต่อไป** (ลบการเรียกออกจาก `create_fvscisold.php` แล้วแทนที่ด้วย logic ข้างต้น) `mark_active()` เดิมยังคงอยู่ในคลาสเหมือนเดิมทุกประการ (ไม่ได้แก้ไข/ไม่ได้ลบ) และยังถูกใช้งานอยู่ตามเดิมในที่อื่น (เช่น `reset_after_request_deleted()` เรียกทางอ้อมผ่าน logic เดิม) — จึงไม่กระทบ flow อื่นที่พึ่งพา behavior เดิมของ `mark_active()` เลยตามข้อกำหนด "ห้าม refactor หรือเปลี่ยน behavior เดิมทั้งระบบโดยไม่จำเป็น"

## 10. Existing Bad Data — ไม่ถูกแก้

ข้อมูลเดิมของ `ship_code = 473100027` ที่มี certification มากกว่า 1 ใบ `status=active` พร้อมกันอยู่แล้วในฐานข้อมูล (พบจากการ SELECT read-only ในรอบวิเคราะห์ก่อนหน้า) **ไม่ถูกแก้ไข/ไม่ถูก UPDATE ใด ๆ ในรอบนี้** ตามข้อกำหนด — โค้ดที่แก้ใหม่จะป้องกันไม่ให้เกิดกรณีนี้ซ้ำอีกในอนาคต (ทั้งกรณี insert ใบใหม่ปกติ และกรณี insert ทับซ้ำ) แต่จะไม่ไปแก้ข้อมูลที่ผิดปกติอยู่แล้วให้เอง — ถ้าเจ้าหน้าที่พยายามสร้างใบใหม่ (status=active) ให้เรือ `473100027` ตอนนี้ ระบบจะตรวจพบว่ามี **มากกว่า 1** ใบ active+unexpired อยู่แล้ว (2 ใบ) และจะ **reject การบันทึกทันที** พร้อมข้อความ data inconsistency (ไม่ปล่อยให้สร้างใบที่ 3 ทับปัญหาเดิมต่อ) จนกว่าเจ้าของระบบจะจัดการข้อมูลเดิมเอง

## 11. Delete Flow — ยังไม่ถูกแก้ (Open Issue)

**Delete Business Rule ยังคงเป็น Open Issue** — ไม่ได้แก้ไขใด ๆ ในรอบนี้:
- `public/inspectofficer/ajax/delete_fvscisold.php` ยังคงเป็น hard delete เหมือนเดิมทุกประการ
- `FvSanitationCertificationOld::mark_active()` ที่ถูกเรียกหลัง delete ใน `delete_fvscisold.php` **ไม่ได้ถูกแก้ไข** ยังเลือกใบจาก "expiration date ไกลที่สุด" เหมือนเดิม (behavior เดิมที่เคยวิเคราะห์ไว้ในรอบก่อนหน้ายังคงอยู่ครบ)
- ไม่มีการเปลี่ยน hard delete เป็น soft delete
- ไม่มีการเพิ่ม restore logic ใหม่ใด ๆ หลัง delete
- **ต้องรอ Business Rule เพิ่มเติมจากเจ้าของระบบก่อนแก้ในรอบถัดไป**

## 12. Attachment Bugs — ยังไม่ถูกแก้ (Open Issue)

ยังไม่ได้แตะตามที่กำหนดไว้ให้แยก scope:
- `fv_certificate_attachments.id` ไม่มี PRIMARY KEY/AUTO_INCREMENT (schema bug ที่เคยพบ) — ยังคงเดิม
- Preview URL ที่คำนวณ path ผิด (`get_certification_attachments.php`) — ยังคงเดิม
- ฟีเจอร์ลบ attachment ทีละรูป (`fvscisold_attachment_delete.php`) — ยังคงเดิม

## 13. Static Verification

- **PHP syntax check (`php -l`)** รันกับทุกไฟล์ที่แก้ไข ผลลัพธ์ "No syntax errors detected" ทุกไฟล์:
  - `private/classes/FvSanitationCertificationOld.class.php`
  - `public/inspectofficer/ajax/create_fvscisold.php`
  - `public/inspectofficer/all_old_certification.php`
  - `public/inspectofficer/old_certification.php`
- **grep ยืนยันเพิ่มเติม:**
  - `fvscis_old_create.php` ไม่มี caller ใด ๆ ในระบบ (ยืนยันไม่ต้องแก้)
  - `deactivate_other_active()` และ `find_active_unexpired_by_ship_code()` มีอยู่แล้วจากรอบก่อนหน้า ไม่ได้ถูกแก้ไขซ้ำในรอบนี้ ใช้งานตามเดิม
  - ชื่อ field ฟอร์ม (`ship_code`, `certificate_status`, `certificate_number`, `effective_date`, `expiration_date`) ตรวจสอบตรงกับ `name="FvSanitationCertificationOld[...]"` ใน [modal_add_fvsanitation_old.php](public/inspectofficer/modal/modal_add_fvsanitation_old.php) แล้ว
- **ไม่มี runtime test ใด ๆ ในรอบนี้** — ไม่ได้กดบันทึก Paper Certification จริง ไม่ได้สร้าง record ใหม่ ไม่ได้ UPDATE/DELETE ฐานข้อมูล ไม่ได้แก้ไขข้อมูล active ซ้ำที่มีอยู่แล้ว

## 14. Owner Runtime Test Required

- **Case: ไม่มีใบเดิม active+unexpired → บันทึกใบใหม่สำเร็จปกติ:** `NOT TESTED — OWNER TO TEST`
- **Case: มีใบเดิม 1 ใบ active+unexpired → แสดง warning ก่อนบันทึก:** `NOT TESTED — OWNER TO TEST`
- **Case: กด "ยกเลิก" ที่ warning → ไม่มีการบันทึก ไม่มีการเปลี่ยนสถานะใบเดิม:** `NOT TESTED — OWNER TO TEST`
- **Case: กด "ดำเนินการต่อ" ที่ warning → ใบใหม่ active, ใบเดิม inactive, ใบเดิมยังอยู่เป็น history:** `NOT TESTED — OWNER TO TEST`
- **Case: มีใบ active+unexpired มากกว่า 1 ใบ (เช่น ship_code 473100027 ที่มีอยู่แล้ว) → reject การบันทึกใบใหม่พร้อมข้อความ data inconsistency เสมอ:** `NOT TESTED — OWNER TO TEST`
- **Case: บันทึกใบใหม่เป็น `certificate_status='ไม่ผ่าน'` (status=fail) ขณะมีใบเดิม active → ต้องไม่ถูก guard นี้บล็อก (logic เดิม `mark_fail()` ทำงานตามปกติ ไม่เกี่ยวกับ single-active rule):** `NOT TESTED — OWNER TO TEST`
- **Case: แนบไฟล์ระหว่างสร้างใบใหม่ (Add form) ยังทำงานถูกต้องหลังแก้ JS:** `NOT TESTED — OWNER TO TEST`

## Remaining Risks / Open Issues (ส่วนเพิ่มจากงานนี้)

- **Atomicity ของ `deactivate_other_active()` ใน create flow**: ไม่มีการเช็ค return value ของ `deactivate_other_active()` แยกเพื่อ rollback หาก UPDATE ปิดใบเดิมล้มเหลว (ความเสี่ยงต่ำมาก แต่ยังไม่ปิดสนิททางทฤษฎี — ดูรายละเอียดหัวข้อ 6)
- **Existing bad data** ของ `ship_code=473100027` (2 ใบ active พร้อมกัน) ยังคงค้างอยู่ในฐานข้อมูล ต้องให้เจ้าของระบบจัดการเอง — ระบบใหม่จะ "reject" การสร้างใบเพิ่มให้เรือนี้จนกว่าจะแก้ข้อมูลเดิมให้เหลือ active ไม่เกิน 1 ใบ
- **Delete Flow** และ **Attachment bugs** ยังคงเป็น Open Issue เดิมตามที่วิเคราะห์ไว้ก่อนหน้า ยังไม่ได้แก้ในรอบนี้

---

# Paper Certification Active-Only Main List + Locked History

รอบนี้แก้เฉพาะ UI/behavior ของ Paper/Historical Certification ให้ตรงกับ Business Rule ใหม่: **Active = Working Record** (View/Edit/Delete/Attachment ได้) ส่วน **record อื่นทั้งหมด = History** (View ได้อย่างเดียว) **ไม่มีการรัน Runtime Test, ไม่มีการสร้าง/ลบ/แก้ข้อมูลจริงในฐานข้อมูล** ทำเฉพาะแก้ source + `php -l` static check เท่านั้น

> **REVISED:** แนวทาง "History Modal" ที่เพิ่มไว้ในรอบแรกถูก **ยกเลิก/revert แล้วทั้งหมด** ตามคำสั่งเจ้าของระบบ เปลี่ยนเป็น **ตัวกรองสถานะ (status filter dropdown)** ในตารางเดิมแทน — Backend Guard (ข้อ 6) **ยังคงอยู่เหมือนเดิมไม่ถูกแก้** รายละเอียดการ revert อยู่ท้ายหัวข้อนี้ (หัวข้อ "Revert Log")

## 1. Files Changed

| ไฟล์ | สิ่งที่แก้ (สถานะล่าสุดหลัง revert) |
|---|---|
| [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) | **รอบแรก:** เพิ่ม 4 method (`find_active_unexpired_by_evaluation_agency()`, `find_history_by_evaluation_agency()`, `find_active_unexpired_by_responsible_unit()`, `find_history_by_responsible_unit()`). **รอบ revert:** ลบ 4 method ดังกล่าวทิ้งทั้งหมด แทนที่ด้วย 2 method ใหม่: `find_by_status_filter_and_evaluation_agency($agency, $filter='active')` และ `find_by_status_filter_and_responsible_unit($unit, $filter='active')` พร้อม helper `status_filter_sql_condition()` ที่แปลงค่า filter (`active`/`inactive`/`fail`/`expired`/`all`) เป็นเงื่อนไข SQL — ไม่ได้แก้ method เดิมใด ๆ ที่มีมาก่อนหน้าทั้งหมด (`find_all_by_evaluation_agency()`, `find_all_by_responsible_unit()`, `mark_active()`, `deactivate_other_active()` ฯลฯ ยังอยู่เหมือนเดิมทุกตัว) |
| [public/inspectofficer/all_old_certification.php](public/inspectofficer/all_old_certification.php) | **รอบแรก:** เปลี่ยน Main List เป็น active-only + เพิ่มปุ่ม/Modal History. **รอบ revert:** คืน Main List ให้แสดงทุก record ในขอบเขต `responsible_unit` เดิม (ผ่าน `find_by_status_filter_and_responsible_unit()`) เพิ่ม dropdown ตัวกรองสถานะแทนปุ่ม/Modal History ที่ถูกลบออก และ gate ปุ่ม Edit/Delete ต่อแถวด้วย `$isActiveWorking` |
| [public/inspectofficer/old_certification.php](public/inspectofficer/old_certification.php) | เหมือนไฟล์ข้างบนแต่ scope เป็น `evaluation_agency` (ผ่าน `find_by_status_filter_and_evaluation_agency()`) |
| [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php) | **Backend guard คงอยู่ไม่เปลี่ยนแปลง:** ถ้า `$obj->status !== 'active'` → throw exception พร้อมข้อความ "รายการนี้เป็นประวัติใบรับรองและไม่สามารถแก้ไขหรือลบได้" ก่อนมีการ update/แนบไฟล์ใด ๆ |
| [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php) | **Backend guard คงอยู่ไม่เปลี่ยนแปลง** เดียวกัน ก่อนเริ่ม transaction ลบใด ๆ |
| [public/inspectofficer/ajax/fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php) | **Backend guard คงอยู่ไม่เปลี่ยนแปลง:** โหลด `FvSanitationCertificationOld` จาก `$att->certificate_id` แล้วเช็ค `status==='active'` ก่อนอนุญาตลบไฟล์แนบ |

**ไม่ได้แตะ (เหมือนเดิม):** `create_fvscisold.php`, `fvscis_old_create.php` (ยืนยันแล้วว่าไม่มี caller ใด ๆ ในระบบ — dead code), `get_certification_attachments.php`/`get_old_certification_by_id.php`/`get_certification_detail.php` (read-only endpoints อยู่แล้ว)

**ห้ามเปลี่ยน scope เดิม:** ยืนยันว่า `evaluation_agency` (หน้า `old_certification.php` = "ข้อมูลการอนุมัติ" ตามหน่วยงานของ user/login) และ `responsible_unit` (หน้า `all_old_certification.php` = "ข้อมูลที่รับผิดชอบ" ตามพื้นที่/หน่วยงานที่ responsible unit ดูแล) **ไม่ถูกเปลี่ยนแปลง** และ **ไม่ได้รวม 2 หน้าเข้าด้วยกัน** — ทั้งสองไฟล์ยังคงแยกกันเหมือนเดิมทุกประการ มีเพียง query filter และ UI ภายในไฟล์ตัวเองที่เปลี่ยน

## 2. Main List Query/Filter (หลัง revert)

Main List ของทั้ง 2 หน้ากลับมาแสดง **ทุก record ในขอบเขตหน่วยงานเดิม** (เหมือนก่อนรอบแรกที่แก้) แต่เพิ่ม dropdown `สถานะ` (GET parameter `status_filter`, ค่าเริ่มต้น = `active`) ที่ submit ฟอร์มไปที่ตัวเอง (`onchange="this.form.submit()"`) ตัวเลือก:

| ค่า filter | นิยาม SQL |
|---|---|
| `active` (default) | `status = 'active' AND expiration_date IS NOT NULL AND expiration_date >= วันนี้` |
| `inactive` | `status = 'inactive'` |
| `fail` | `status = 'fail'` |
| `expired` | `expiration_date IS NOT NULL AND expiration_date < วันนี้` (ไม่สนใจค่า status) |
| `all` | ไม่กรองสถานะ/วันที่เพิ่มเติมเลย (ทุก record ในขอบเขต) |

ไม่มีการ DELETE ข้อมูลใด ๆ จากการเปลี่ยน query — เป็นการเปลี่ยนเงื่อนไข SELECT เท่านั้น

## 3. History Access — ยกเลิกแล้ว

**Modal History (`#modalFvscisOldHistory`) และปุ่ม "ประวัติใบรับรอง" ถูกลบออกทั้งหมด** จากทั้ง 2 ไฟล์ ตามคำสั่ง แทนที่ด้วยตัวกรองสถานะในตารางเดียวกัน (ดูหัวข้อ 2) — ผู้ใช้เข้าถึง record ที่ไม่ใช่ active ได้โดยเลือก filter `inactive`/`fail`/`expired`/`all` ในตารางหลักแทน ไม่ต้องเปิด Modal แยก

## 4. Active Actions

แถวที่ `$isActiveWorking === true` (คำนวณจาก `status === 'active' && expiration_date >= วันนี้` ต่อแถว ไม่ใช่จาก query filter ที่ผู้ใช้เลือก เพื่อความถูกต้องแม้ผู้ใช้เลือก filter `all` แล้วเห็นทั้ง active และไม่ active ปนกันในตารางเดียวกัน) แสดงปุ่มครบ: View / Edit (เฉพาะ `all_old_certification.php` ที่ยังคงเงื่อนไข `$department_id_check == $req->evaluation_agency` เดิมอยู่ ไม่ได้แก้) / Delete / Attachment

## 5. History Actions (สำหรับแถวที่ไม่ใช่ active-working ภายในตารางเดียวกัน)

แถวที่ `$isActiveWorking === false` แสดงเฉพาะ:
- ปุ่ม **View** (`openOldCertificationModalById()` เดิม)
- ปุ่ม **ไฟล์แนบ** (`.btn-attachments` เดิม, เปิด `#modalPhotoAttachments` ซึ่งเป็น read-only โดยธรรมชาติอยู่แล้ว — ไม่มีปุ่มลบ/เพิ่มไฟล์ฝังอยู่) เฉพาะกรณีมี attachment อยู่แล้ว

**ไม่ render ปุ่ม Edit/Delete ให้แถวเหล่านี้เลย** (ตัด PHP `if` ทั้งสองปุ่มออกจาก markup ตรง ๆ ไม่ใช่แค่ CSS ซ่อน)

## 6. Backend Guards — คงอยู่ไม่เปลี่ยนแปลง

Guard เดิมทั้ง 3 endpoint จากรอบก่อนหน้ายังคงอยู่ **ไม่ถูกแก้ไข/ไม่ถูกลบ** ตามคำสั่ง ("ให้ backend guards ... ที่เพิ่งเพิ่มไว้คงอยู่"):
- `update_fvscisold.php` — reject ถ้า `status !== 'active'` ก่อน update/แนบไฟล์
- `delete_fvscisold.php` — reject ถ้า `status !== 'active'` ก่อนลบ
- `fvscisold_attachment_delete.php` — reject ถ้า certification เจ้าของไฟล์แนบ `status !== 'active'`

ข้อความ error เดิม: `"รายการนี้เป็นประวัติใบรับรองและไม่สามารถแก้ไขหรือลบได้"` — ทำงานไม่ขึ้นกับว่า UI จะแสดง Main List แบบ active-only หรือแบบ filter แบบใหม่ (bypass ผ่าน request ตรงยังถูกกันเหมือนเดิม)

## 7. Expired Record Handling

Filter `expired` ในตัวกรองสถานะใหม่ใช้เงื่อนไข `expiration_date < วันนี้` ตรง ๆ (ไม่สนใจค่า status) ตามที่กำหนด ยังคง**ไม่มีการสร้าง scheduler ใหม่** ในรอบนี้เช่นเดิม

**Open Issue (status synchronization) — เหมือนเดิม:** คอลัมน์ `status` ของ record ที่หมดอายุแล้วยังคงเป็น `'active'` ในฐานข้อมูลจริง (ไม่มี mechanism sync สถานะอัตโนมัติ) การกรองทำที่ query/UI เท่านั้น — ยังไม่ได้แก้ในรอบนี้

## 8. Attachment Read-Only Behavior

ไม่เปลี่ยนแปลงจากที่วิเคราะห์ไว้ก่อนหน้า: ไม่ได้แก้ schema bug ของ `fv_certificate_attachments.id` และไม่ได้แก้ Preview URL ปุ่มไฟล์แนบสำหรับแถวที่ไม่ใช่ active-working ใช้ modal เดิม (`#modalPhotoAttachments`) ที่เป็น read-only อยู่แล้วโดยธรรมชาติ ไม่ต้องแก้ subsystem เพิ่มเติม

## 9. Single Active Compatibility

Logic เดิมจาก Single-Active Rule (`create_fvscisold.php` + `deactivate_other_active()`) **ไม่ถูกแตะ**: เมื่อบันทึกใบใหม่สำเร็จ ใบใหม่ (`active`) จะปรากฏใน Main List ทันทีเมื่อเลือก filter `active` (default) และใบเดิมที่ถูกเปลี่ยนเป็น `inactive` จะยังปรากฏในตารางเดียวกันเมื่อเลือก filter `inactive`/`all` (ไม่ได้หายไปจากระบบ เพียงต้องเปลี่ยน filter เพื่อดู)

## 10. Delete Behavior ที่ยังคงเดิม

**Delete = Hard Delete** เหมือนเดิมทุกประการ ไม่มีการเปลี่ยนเป็น soft delete ไม่มีการแก้ `mark_active()` ที่ถูกเรียกหลัง delete ไม่มีการเพิ่ม restore logic ใหม่ใด ๆ — เงื่อนไขว่าใครลบได้ (`status='active'` เท่านั้น ผ่าน backend guard ข้อ 6) ยังคงอยู่เหมือนเดิมไม่เปลี่ยนแปลงจากรอบก่อนหน้า

## 11. Existing Data — ไม่ถูกแก้

ไม่มีการ UPDATE/DELETE ข้อมูลเดิมในฐานข้อมูลใด ๆ ในรอบนี้ (รวมถึงข้อมูลผิดปกติของ `ship_code=473100027` ที่มี active มากกว่า 1 ใบจากรอบก่อนหน้า) — record เหล่านั้นจะปรากฏในตารางเดียวกันตาม filter ที่เลือก (เลือก `active` จะเห็นทั้งคู่พร้อมกันถ้ายังไม่หมดอายุทั้งคู่จริง สะท้อนสภาพข้อมูลผิดปกติเดิมจนกว่าเจ้าของระบบจะจัดการเอง)

## 12. Static Verification

**PHP syntax check (`php -l`)** รันกับทุกไฟล์ที่แก้ไขในรอบ revert นี้ ผลลัพธ์ "No syntax errors detected" ทุกไฟล์:
- `private/classes/FvSanitationCertificationOld.class.php`
- `public/inspectofficer/all_old_certification.php`
- `public/inspectofficer/old_certification.php`

(3 endpoint guard files ไม่ได้ถูกแก้ไขซ้ำในรอบนี้ จึงไม่ต้องรัน `php -l` ใหม่ — ผลตรวจจากรอบก่อนหน้ายังใช้ได้)

**grep ยืนยันเพิ่มเติม:** ไม่พบการอ้างอิงถึง `find_active_unexpired_by_*`, `find_history_by_*`, หรือ `modalFvscisOldHistory` เหลืออยู่ใน source code แล้ว (มีเหลือเฉพาะใน EDIT.md ที่เป็นบันทึกประวัติ)

**ไม่มี runtime test ใด ๆ ในรอบนี้** — ไม่ได้กด Edit/Delete/Attachment/filter จริง ไม่ได้แก้ไขข้อมูลในฐานข้อมูล

## 13. Owner Runtime Test Required

- **Default filter เมื่อเข้าหน้าครั้งแรก = "ใช้งานจริง" และแสดงเฉพาะ active+unexpired:** `NOT TESTED — OWNER TO TEST`
- **เลือก filter "ไม่ใช้งาน" → เห็นเฉพาะ status=inactive:** `NOT TESTED — OWNER TO TEST`
- **เลือก filter "ไม่ผ่าน" → เห็นเฉพาะ status=fail:** `NOT TESTED — OWNER TO TEST`
- **เลือก filter "หมดอายุ" → เห็นเฉพาะ expiration_date < วันนี้ (ไม่สนใจ status):** `NOT TESTED — OWNER TO TEST`
- **เลือก filter "ทั้งหมด" → เห็นทุก record ในขอบเขตหน้านั้น:** `NOT TESTED — OWNER TO TEST`
- **แถว active+unexpired ยังมีปุ่ม View/Edit/Delete/Attachment ครบ:** `NOT TESTED — OWNER TO TEST`
- **แถวที่ไม่ใช่ active-working มีเฉพาะปุ่ม View (+ Attachment แบบดูอย่างเดียวถ้ามีไฟล์):** `NOT TESTED — OWNER TO TEST`
- **ยิง `update_fvscisold.php` ตรงกับ id ของ record ที่ไม่ใช่ active → backend reject:** `NOT TESTED — OWNER TO TEST`
- **ยิง `delete_fvscisold.php` ตรงกับ id ของ record ที่ไม่ใช่ active → backend reject:** `NOT TESTED — OWNER TO TEST`
- **ยิง `fvscisold_attachment_delete.php` กับ attachment ของ record ที่ไม่ใช่ active → backend reject:** `NOT TESTED — OWNER TO TEST`
- **บันทึกใบใหม่สำเร็จ → ใบใหม่ปรากฏเมื่อเลือก filter active, ใบเดิมปรากฏเมื่อเลือก filter inactive/all:** `NOT TESTED — OWNER TO TEST`
- **หน้า `old_certification.php` (evaluation_agency) และ `all_old_certification.php` (responsible_unit) ยังคงแสดงข้อมูลคนละ scope ตามเดิม ไม่ได้ถูกรวมกัน:** `NOT TESTED — OWNER TO TEST`

## 14. Remaining Risks / Open Issues

- **Status synchronization:** `status` ของใบที่หมดอายุแล้วยังคงเป็น `'active'` ในฐานข้อมูลจริง ไม่มีการซิงก์อัตโนมัติ (ดูหัวข้อ 7) — เหมือนเดิมจากรอบก่อน ไม่ได้แก้เพิ่ม
- **`fv_certificate_attachments.id` schema bug เดิม** ยังไม่ถูกแก้
- **Preview URL bug เดิม** ยังไม่ถูกแก้
- **Delete Flow logic เดิม** (`mark_active()` เลือกใบจาก expiration date ไกลที่สุดหลัง delete) ยังคงเป็น Open Issue เดิม
- **Existing bad data** (`ship_code=473100027` มี active มากกว่า 1 ใบ) ยังคงค้างอยู่ ต้องให้เจ้าของระบบจัดการเอง

## Revert Log

- ลบ Modal `#modalFvscisOldHistory` และปุ่ม "ประวัติใบรับรอง" ออกจากทั้ง `all_old_certification.php` และ `old_certification.php` ทั้งหมด
- ลบ method `find_active_unexpired_by_evaluation_agency()`, `find_history_by_evaluation_agency()`, `find_active_unexpired_by_responsible_unit()`, `find_history_by_responsible_unit()` ออกจาก `FvSanitationCertificationOld.class.php` แทนที่ด้วย `find_by_status_filter_and_evaluation_agency()` / `find_by_status_filter_and_responsible_unit()` + helper `status_filter_sql_condition()`
- Main List กลับมาแสดงทุก record ในขอบเขตเดิม (ไม่บังคับ active-only อีกต่อไป) ควบคุมด้วย dropdown ตัวกรองสถานะแทน (default = ใช้งานจริง)
- **Backend guard ทั้ง 3 endpoint (`update_fvscisold.php`, `delete_fvscisold.php`, `fvscisold_attachment_delete.php`) ไม่ถูกแตะ ยังคงทำงานเหมือนเดิมทุกประการ**
- Scope `evaluation_agency`/`responsible_unit` และการแยก 2 หน้าไม่ถูกเปลี่ยนแปลง

---

# Paper Certification Status Filter + Effective Status

รอบนี้ปรับ default filter, เพิ่มสถานะ `pending` (e-FVSCIS), เพิ่ม **Effective Status** (คำนวณจาก `status` + `expiration_date` ร่วมกัน แทนการอ่าน `status` ตรง ๆ อย่างเดียว) และ**ปรับ backend guard ให้ตรวจ effective state เดียวกับที่ UI ใช้** ตามคำสั่งเจ้าของระบบ **ไม่มีการรัน Runtime Test ไม่มีการแก้ไขข้อมูลในฐานข้อมูล** ทำเฉพาะแก้ source + `php -l` เท่านั้น

## 1. Files Changed

| ไฟล์ | สิ่งที่แก้ |
|---|---|
| [private/classes/FvSanitationCertificationOld.class.php](private/classes/FvSanitationCertificationOld.class.php) | เพิ่ม `is_active_working($status, $expiration_date)` และ `effective_status_code($status, $expiration_date)` (คืนค่า `active`/`expired`/`inactive`/`fail`/`pending`/`unknown`) ใช้ร่วมกันทั้งฝั่งแสดงผลและฝั่ง backend guard; ปรับ `status_filter_sql_condition()` ให้รองรับ `pending` และแก้ `expired` ให้ตรงตาม matrix (`status='active' AND expiration_date < วันนี้`); เปลี่ยนค่า default parameter ของ `find_by_status_filter_and_*()` จาก `'active'` เป็น `'all'` |
| [public/inspectofficer/all_old_certification.php](public/inspectofficer/all_old_certification.php) | เปลี่ยน default `status_filter` เป็น `all`, จัดลำดับ/เพิ่ม option `pending` ("อยู่ระหว่างการตรวจ e-FVSCIS"), เปลี่ยน `$isActiveWorking` ให้เรียก `FvSanitationCertificationOld::is_active_working()` แทนการคำนวณเองซ้ำในไฟล์, เปลี่ยน badge ให้ใช้ `effective_status_code()` แสดงสถานะเดียวไม่ขัดกันเอง |
| [public/inspectofficer/old_certification.php](public/inspectofficer/old_certification.php) | เหมือนไฟล์ข้างบน (scope `evaluation_agency`) |
| [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php) | เปลี่ยน guard จาก `$obj->status !== 'active'` เป็น `!FvSanitationCertificationOld::is_active_working($obj->status, $obj->expiration_date)` และเปลี่ยนข้อความ error เป็น "รายการนี้ไม่ใช่ใบรับรองที่ใช้งานอยู่ในปัจจุบัน และไม่สามารถแก้ไขหรือลบได้" |
| [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php) | เปลี่ยน guard เดียวกัน |
| [public/inspectofficer/ajax/fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php) | เปลี่ยน guard เดียวกัน (ตรวจจาก certification เจ้าของไฟล์แนบ) |

**ไม่ได้แตะ:** `create_fvscisold.php`, `fvscis_old_create.php` (ไม่มี caller ในระบบ), `mark_active()`/`deactivate_other_active()`/`append_remark()`/`find_active_unexpired_by_ship_code()` (Single-Active Rule methods เดิมจากรอบก่อนหน้ายังอยู่ครบ ไม่ถูกแก้ไข)

## 2. History Modal ที่ Revert

Modal History และปุ่ม "ประวัติใบรับรอง" ถูกลบไปแล้วในรอบก่อนหน้า (ยืนยันอีกครั้งด้วย grep ว่าไม่มีการอ้างอิง `modalFvscisOldHistory` หรือ method `find_history_by_*`/`find_active_unexpired_by_evaluation_agency`/`find_active_unexpired_by_responsible_unit` หลงเหลือใน source แล้ว) รอบนี้ไม่ได้เพิ่ม History กลับมาอีก ยังคงใช้ Status Filter ในตารางเดียวตามที่กำหนด

## 3. Business Scope ที่รักษาไว้

ยืนยันว่า **ไม่ได้เปลี่ยน scope และไม่ได้รวม 2 หน้าเข้าด้วยกัน**:
- [public/inspectofficer/old_certification.php](public/inspectofficer/old_certification.php) = **"ข้อมูลการอนุมัติ"** — query ด้วย `evaluation_agency = $Officer->departments_id` (หน่วยงานของ user ที่ login) เหมือนเดิมทุกประการ เปลี่ยนแค่เพิ่มเงื่อนไข filter ต่อท้าย
- [public/inspectofficer/all_old_certification.php](public/inspectofficer/all_old_certification.php) = **"ข้อมูลที่รับผิดชอบ"** — query ด้วย `responsible_unit = $new_departments_id` (จาก `Officer::map_departments_id()`) เหมือนเดิมทุกประการ เปลี่ยนแค่เพิ่มเงื่อนไข filter ต่อท้าย
- ลำดับ logic เป็นไปตามที่กำหนด: **Business Scope (evaluation_agency/responsible_unit) → Status Filter → (Search/Sort/Pagination ของ DataTables ฝั่ง client เดิม ทำงานต่อจากผลลัพธ์ที่ query กรองมาแล้ว)** — ไม่มีจุดใดที่ filter สถานะไปเปลี่ยน scope หน่วยงานหรือรวมข้อมูลข้ามหน่วยงาน
- ไม่ได้สร้าง navigation ใหม่ใด ๆ แทน 2 หน้านี้ ทั้งสองไฟล์ยังคงเป็นหน้าแยกกันเหมือนเดิม

## 4. Status Filter ที่เพิ่ม

Dropdown `สถานะ` (GET parameter `status_filter`) ในตารางเดิมของทั้ง 2 หน้า ตัวเลือกตามลำดับที่กำหนด: `ทั้งหมด`, `ใช้งานจริง`, `หมดอายุ`, `ไม่ใช้งาน`, `ไม่ผ่าน`, `อยู่ระหว่างการตรวจ e-FVSCIS` — เปลี่ยนค่าแล้ว auto-submit ฟอร์ม GET ไปหน้าเดิม

## 5. Default Filter

เปลี่ยน default จาก `active` (รอบก่อน) เป็น **`all` (ทั้งหมด)** ตามคำสั่งใหม่ — เข้าหน้าครั้งแรกโดยไม่มี query string จะเห็นทุก record ในขอบเขตหน่วยงานตนเอง ไม่ซ่อนข้อมูลใด ๆ โดยไม่ตั้งใจ

## 6. Effective Status Matrix

Implement ตรงตาม matrix ที่กำหนดใน `FvSanitationCertificationOld::effective_status_code($status, $expiration_date)`:

| DB `status` | เงื่อนไข `expiration_date` | Effective status (UI) |
|---|---|---|
| `active` | `>= วันนี้` | `active` → "ใช้งานจริง" |
| `active` | `< วันนี้` | `expired` → "หมดอายุ" |
| `inactive` | (ไม่พึ่งวันที่) | `inactive` → "ไม่ใช้งาน" |
| `fail` | (ไม่พึ่งวันที่) | `fail` → "ไม่ผ่าน" |
| `pending` | (ไม่พึ่งวันที่) | `pending` → "อยู่ระหว่างการตรวจ e-FVSCIS" |
| อื่น ๆ / `expiration_date` เป็น NULL ขณะ `status='active'` | — | ตกไปที่ branch `expired` เพราะ `is_active_working()` คืน `false` เมื่อ `expiration_date` ว่าง/เป็น `'0000-00-00'` (**พบกรณีกำกวมนี้จริง — ดูหมายเหตุด้านล่าง**) |

**หมายเหตุกรณีกำกวมที่พบ:** ถ้า `status='active'` แต่ `expiration_date` เป็น `NULL` หรือ `'0000-00-00'` (ไม่มีข้อมูลวันหมดอายุเลย) โค้ดปัจจุบันจะจัดเป็น **"หมดอายุ"** โดยปริยาย (เพราะ `is_active_working()` ถือว่าไม่ใช่ working record หากไม่มีวันหมดอายุที่ตรวจสอบได้) ตามคำสั่ง "รักษา behavior เดิม" — พฤติกรรมเดิมของระบบ (ก่อนรอบนี้) ก็ปฏิบัติแบบเดียวกันอยู่แล้วในการนับใบ active-unexpired (`find_active_unexpired_by_ship_code()` ก็กรอง `expiration_date IS NOT NULL` เช่นกัน) จึงถือว่าสอดคล้องกับ behavior เดิมของระบบ ไม่ใช่การเปลี่ยนแปลงใหม่ — บันทึกไว้เป็นข้อสังเกต ไม่ใช่บั๊กใหม่จากรอบนี้

## 7. Pending = e-FVSCIS

ยืนยันตามที่กำหนด: `status='pending'` แสดงเป็น **"อยู่ระหว่างการตรวจ e-FVSCIS"** เท่านั้น (ไม่ใช้คำว่า "รอผลทั่วไป"/"หมดอายุ"/"ไม่ใช้งาน") ทั้งใน badge, filter dropdown option, และ Action Matrix (`pending` ถูกจัดเป็น non-working record เสมอ ไม่ว่าจะมี `expiration_date` หรือไม่ — Manual/Paper UI จึงไม่มีสิทธิ Edit/Delete record ประเภทนี้ ตามหมายเหตุที่ระบุว่า pending มาจาก e-FVSCIS workflow)

## 8. Expired Handling

**ไม่มีการสร้าง scheduler/cron/UPDATE ทั้งตารางใด ๆ** ตามข้อกำหนด — DB `status` ของใบที่หมดอายุแล้วยังคงเป็น `'active'` เหมือนเดิมในฐานข้อมูลจริง (ตั้งใจให้เป็นเช่นนั้นใน Phase นี้) มีเพียง UI/Effective Status ที่คำนวณและแสดงเป็น "หมดอายุ" แทน — `DB status != UI effective status` ในกรณีนี้เป็น behavior ที่ตั้งใจตามที่กำหนด ไม่ใช่บั๊ก

## 9. Status Badge

Badge แสดงผลตาม Effective Status เพียงค่าเดียวเสมอ (ไม่มีข้อความซ้อนกันแบบ "ใช้งานจริง (หมดอายุแล้ว)" อีกต่อไป — แก้จากรอบก่อนหน้าที่เคยมีข้อความลักษณะนี้ใน Modal History ที่ถูก revert ไปแล้ว): `ใช้งานจริง` (bg-success) / `หมดอายุ` (bg-warning) / `ไม่ใช้งาน` (bg-secondary) / `ไม่ผ่าน` (bg-danger) / `อยู่ระหว่างการตรวจ e-FVSCIS` (bg-primary) — ใช้ Bootstrap badge class เดิมที่มีอยู่แล้วในระบบ ไม่ได้เพิ่ม class ใหม่ ไม่ได้ redesign UI

## 10. Action Matrix

แถวที่ `is_active_working($req->status, $req->expiration_date) === true` (คือ Effective Status = `active` เท่านั้น) แสดงปุ่มครบ View/Edit (เฉพาะ `all_old_certification.php` ที่ยังคงเงื่อนไขแผนก `$department_id_check == $req->evaluation_agency` เดิม)/Delete/Attachment (แก้ไขได้) — แถวอื่นทั้งหมด (`expired`/`inactive`/`fail`/`pending`) แสดงเฉพาะ View + ดู Attachment แบบ read-only เท่านั้น (ไม่ render ปุ่ม Edit/Delete ให้เลย)

## 11. Backend Guard

**ปรับปรุงจากรอบก่อนหน้า:** เดิม guard เช็คเพียง `$obj->status !== 'active'` ซึ่งจะปล่อยให้แก้ไข/ลบ record ที่ `status='active'` แต่หมดอายุแล้วได้ (ไม่ตรงกับ Effective Status ที่ควรเป็น "หมดอายุ" = read-only) — รอบนี้แก้ทั้ง 3 endpoint ให้เรียก `FvSanitationCertificationOld::is_active_working($status, $expiration_date)` แทน (ตรวจทั้ง `status='active'` และ `expiration_date >= วันนี้` พร้อมกัน) และเปลี่ยนข้อความ error เป็นตามที่กำหนด: **"รายการนี้ไม่ใช่ใบรับรองที่ใช้งานอยู่ในปัจจุบัน และไม่สามารถแก้ไขหรือลบได้"** — ไม่มีการเปลี่ยนแปลงข้อมูลใน DB เพื่อทำให้ guard ผ่านแต่อย่างใด

## 12. Single Active Compatibility

ตรวจสอบยืนยันด้วย grep ว่า method ของ Single-Active Rule จากรอบก่อนหน้า (`create_fvscisold.php`'s guard logic, `deactivate_other_active()`, `find_active_unexpired_by_ship_code()`, `append_remark()`) **ไม่ถูกแก้ไขหรือแตะต้องเลยในรอบนี้** ยังคงทำงานเหมือนเดิมทุกประการ — logic "มี active+unexpired 1 ใบ → เตือนก่อนแทนที่ → ยืนยัน → ใบใหม่ active/ใบเดิม inactive" และ "มี active+unexpired มากกว่า 1 ใบ → block" ยังคงอยู่ครบ

## 13. Delete Open Issue

**Delete = Hard Delete** เหมือนเดิม ใช้ได้เฉพาะ record ที่ `is_active_working()===true` (active+unexpired) เท่านั้น (แก้ guard ให้เข้มงวดขึ้นตามหัวข้อ 11) — สิ่งที่เกิดขึ้น**หลัง**ลบ (เรียก `mark_active()` เดิม เลือกใบจาก expiration date ไกลที่สุด) **ยังไม่ถูกแก้ไข** ยังคงเป็น Open Issue เดิมรอ Business Rule เพิ่มเติม — ไม่ได้ redesign restore logic, ไม่ได้แก้ `mark_active()`, ไม่ได้เปลี่ยนเป็น soft delete ตามข้อกำหนด

## 14. Attachment Open Issues

ยังไม่แก้ในรอบนี้ (เหมือนเดิมทุกรอบที่ผ่านมา): `fv_certificate_attachments.id` ไม่มี PK/AUTO_INCREMENT, Preview URL คำนวณผิด, ความเสี่ยงจาก single-attachment-delete ที่อาจลบผิดแถว — การแก้ Status Filter/Effective Status ในรอบนี้ไม่ได้แตะ attachment subsystem เลยแม้แต่บรรทัดเดียว (มีเพียงข้อความ title ของปุ่มไฟล์แนบที่ใช้ `$isActiveWorking` เดิมจากรอบก่อน ไม่ได้เปลี่ยนเพิ่ม)

## 15. Static Verification

**PHP syntax check (`php -l`)** รันกับทุกไฟล์ที่แก้ไขในรอบนี้ ผลลัพธ์ "No syntax errors detected" ทุกไฟล์:
- `private/classes/FvSanitationCertificationOld.class.php`
- `public/inspectofficer/all_old_certification.php`
- `public/inspectofficer/old_certification.php`
- `public/inspectofficer/ajax/update_fvscisold.php`
- `public/inspectofficer/ajax/delete_fvscisold.php`
- `public/inspectofficer/ajax/fvscisold_attachment_delete.php`

**grep ยืนยันเพิ่มเติม:** ไม่พบการอ้างอิงเหลือของ method/UI ที่ revert ไปแล้ว (`find_history_by_*`, `find_active_unexpired_by_evaluation_agency`, `find_active_unexpired_by_responsible_unit`, `modalFvscisOldHistory`) ในไฟล์ source ใด ๆ; ยืนยัน `mark_active()`/`deactivate_other_active()`/`append_remark()`/`find_active_unexpired_by_ship_code()` (Single-Active Rule เดิม) ยังอยู่ครบไม่ถูกแก้

**ไม่มี runtime test ใด ๆ ในรอบนี้** — ไม่ได้กด filter/Edit/Delete/Attachment จริง ไม่ได้แก้ไขข้อมูลในฐานข้อมูล

## 16. Owner Runtime Test Required

- **ข้อมูลการอนุมัติ (`old_certification.php`) ยังแสดง scope ตาม `evaluation_agency` ของ user เดิม ไม่เปลี่ยน:** `NOT TESTED — OWNER TO TEST`
- **ข้อมูลที่รับผิดชอบ (`all_old_certification.php`) ยังแสดง scope ตาม `responsible_unit` เดิม ไม่เปลี่ยน:** `NOT TESTED — OWNER TO TEST`
- **Default filter "ทั้งหมด" เมื่อเข้าหน้าครั้งแรก แสดง records ครบทุกสถานะในขอบเขต:** `NOT TESTED — OWNER TO TEST`
- **Filter "ใช้งานจริง" ถูกต้อง (status=active AND expiration_date>=วันนี้):** `NOT TESTED — OWNER TO TEST`
- **Filter "หมดอายุ" ถูกต้อง (status=active AND expiration_date<วันนี้):** `NOT TESTED — OWNER TO TEST`
- **Filter "ไม่ใช้งาน" ถูกต้อง (status=inactive):** `NOT TESTED — OWNER TO TEST`
- **Filter "ไม่ผ่าน" ถูกต้อง (status=fail):** `NOT TESTED — OWNER TO TEST`
- **Filter "อยู่ระหว่างการตรวจ e-FVSCIS" แสดงเฉพาะ status=pending พร้อม label ที่ถูกต้อง:** `NOT TESTED — OWNER TO TEST`
- **Record ที่ active+หมดอายุ แสดง badge "หมดอายุ" ไม่ใช่ "ใช้งานจริง":** `NOT TESTED — OWNER TO TEST`
- **Record ที่ active+หมดอายุ ไม่มีปุ่ม Edit/Delete:** `NOT TESTED — OWNER TO TEST`
- **Record ที่ inactive/fail/pending ไม่มีปุ่ม Edit/Delete:** `NOT TESTED — OWNER TO TEST`
- **Record ที่ active+ยังไม่หมดอายุ ยัง Edit/Delete ได้ตามปกติ:** `NOT TESTED — OWNER TO TEST`
- **Search/Sort/Pagination (DataTables ฝั่ง client) ยังทำงานถูกต้องภายใต้ผลลัพธ์ที่ผ่าน filter มาแล้ว:** `NOT TESTED — OWNER TO TEST`
- **Single-active create flow (เตือนก่อนแทนที่ใบเดิม, block เมื่อมี active มากกว่า 1 ใบ) ยังทำงานเหมือนเดิมทุกประการ:** `NOT TESTED — OWNER TO TEST`
- **ยิง `update_fvscisold.php`/`delete_fvscisold.php`/`fvscisold_attachment_delete.php` ตรงกับ record ที่ active-แต่หมดอายุ → backend reject พร้อมข้อความใหม่:** `NOT TESTED — OWNER TO TEST`

## Remaining Risks / Open Issues (สะสมจากทุกรอบ)

- **Status synchronization:** DB `status` ของใบหมดอายุยังคงเป็น `'active'` ตลอดไปโดยไม่มีการซิงก์อัตโนมัติ (ตั้งใจไว้ใน Phase นี้ตามคำสั่ง)
- **`fv_certificate_attachments.id` schema bug** ยังไม่ถูกแก้
- **Preview URL bug** ยังไม่ถูกแก้
- **Delete Flow logic เดิม** (`mark_active()` หลัง delete) ยังคงเป็น Open Issue เดิม
- **Existing bad data** (`ship_code=473100027` มี active มากกว่า 1 ใบ) ยังคงค้างอยู่ ต้องให้เจ้าของระบบจัดการเอง
- **กรณีกำกวม `expiration_date` เป็น NULL ขณะ `status='active'`** ถูกจัดเป็น "หมดอายุ" โดยปริยาย (ดูหัวข้อ 6) — สอดคล้อง behavior เดิมของระบบ แต่ควรยืนยันกับเจ้าของระบบว่าต้องการ behavior นี้จริงหรือไม่ในระยะยาว

---

# Paper Certification Attachment Fix

รอบนี้แก้ **Preview URL bug** ของไฟล์แนบ Paper Certification (View + Edit modal) และเตรียม **migration script** (ยังไม่ execute) เพื่อแก้ schema bug ของ `fv_certificate_attachments.id` **ไม่มีการรัน Runtime Test ไม่มีการรัน migration ไม่มีการแก้ไข/ลบ/อัปโหลด test data ใด ๆ** ทำเฉพาะแก้ source (2 ไฟล์) + สร้างไฟล์ SQL migration (1 ไฟล์) + `php -l` เท่านั้น

## 1. Files Changed

| ไฟล์ | สิ่งที่แก้ |
|---|---|
| [private/classes/FvCertificateAttachment.class.php](private/classes/FvCertificateAttachment.class.php) | เพิ่ม canonical helper `public_url($file_path)` + `normalize_rel_path($file_path)` (protected) — ใช้ `url_for()` ที่มีอยู่แล้วใน [private/functions.php](private/functions.php) (ซึ่งใช้ `WWW_ROOT` ที่คำนวณจาก `$_SERVER['SCRIPT_NAME']` จริงใน [private/initialize.php](private/initialize.php#L8-L15)) แทนการ diff `DOCUMENT_ROOT`/`PUBLIC_PATH` เอง — ไม่ได้แก้ method อื่นใดในคลาสนี้เลย (`find_by_certificate_id()`, `create_from_upload()`, `delete_with_file()`, `delete_by_certificate_id()` ยังเหมือนเดิมทุกประการ) |
| [public/inspectofficer/ajax/get_certification_attachments.php](public/inspectofficer/ajax/get_certification_attachments.php) | ลบ logic คำนวณ `$appBase` จาก `$_SERVER['DOCUMENT_ROOT']` กับ `PUBLIC_PATH` (root cause ของบั๊ก) เปลี่ยนมาเรียก `FvCertificateAttachment::public_url($a->file_path)` แทนสำหรับ field `url`/`url_enc` ที่ส่งกลับไปยัง JS — ยังคงใช้ `$pubPath`/`normalize_rel_upload()` เดิมสำหรับตรวจ `exists` (physical path check เท่านั้น ไม่ใช่ URL) |
| [FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql](FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql) | **ไฟล์ใหม่** — migration script แก้ schema `fv_certificate_attachments.id` ให้เป็น `AUTO_INCREMENT PRIMARY KEY` พร้อม backup/verification query ครบ **ยังไม่ execute** |

**ไม่ได้แตะ:** `FvSanitationCertificationOld.class.php`, `create_fvscisold.php`, `update_fvscisold.php`, `delete_fvscisold.php`, `fvscisold_attachment_delete.php`, `all_old_certification.php`, `old_certification.php`, modal ไฟล์ทั้งสอง — เพราะไม่มี logic สร้าง URL ของตัวเองอยู่แล้ว (พึ่งพา JSON จาก `get_certification_attachments.php` เพียงจุดเดียว), และ backend guard/business rule ของรอบก่อนหน้าไม่ถูกแตะเลยตามข้อกำหนด (ดูหัวข้อ 13)

## 2. Runtime Evidence ที่ใช้เป็นฐาน

อ้างอิงจาก Runtime Test จริงของเรือ `สงวนสิน 25` (`ship_code=292212077`) ที่เจ้าของระบบยืนยัน: metadata/count/ชื่อไฟล์/ประเภทเอกสารถูกต้องครบทั้ง View และ Edit modal แต่ thumbnail/preview โหลดไม่ขึ้นทั้งคู่ — สรุปตรงกับ static analysis ก่อนหน้าว่า **Upload = OK, Association = OK, Metadata = OK, เหลือเฉพาะ File URL/Preview = FAIL** จึงเป็นจุดเดียวที่ต้องแก้ในหัวข้อนี้

## 3. Preview URL Root Cause

**CONFIRMED** (ยืนยันซ้ำจาก source เดิม): [get_certification_attachments.php](public/inspectofficer/ajax/get_certification_attachments.php) คำนวณ `$appBase = str_replace($_SERVER['DOCUMENT_ROOT'], '', PUBLIC_PATH)` โดย `DOCUMENT_ROOT` เป็นค่ากลางของทั้งเว็บเซิร์ฟเวอร์ (`C:/xampp/htdocs`) ไม่ใช่ context ของ request จริงที่ Apache Alias ไปยัง `.../fvscis/public` โดยตรง ผลคือ `$appBase` มี `/public` เกินมาเสมอ (`/fvscis/public`) ทำให้ URL รูปภาพที่ส่งไปยัง `<img src>`/`<a href>` ผิดพลาด (404) ทั้ง 2 modal ที่ใช้ endpoint นี้ร่วมกัน

## 4. URL Logic Before/After

**Before:**
```php
$docRoot = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/');
$pubPath = rtrim(str_replace('\\','/', PUBLIC_PATH), '/');
$appBase = str_replace($docRoot, '', $pubPath);          // => '/fvscis/public' (ผิด มี /public เกิน)
$url = $appBase . $rel;
```

**After:**
```php
// FvCertificateAttachment::public_url()
$rel = static::normalize_rel_path($file_path);            // => '/uploads/certificationold/...'
return $rel !== '' ? url_for($rel) : '';                   // url_for() = WWW_ROOT . $rel (มีอยู่แล้วใน private/functions.php)
```
`WWW_ROOT` คำนวณจาก `strpos($_SERVER['SCRIPT_NAME'], '/fvscis')` ในทุก request จริง ([private/initialize.php](private/initialize.php#L8-L15)) — เป็น **logical web path** ที่ไม่ผูกกับ physical filesystem path และ**ไม่ hardcode host/domain ใด ๆ** ทำงานได้ทั้ง local XAMPP และ production ตราบใดที่ path prefix ของแอปยังคงเป็น `/fvscis` (ตาม convention เดิมของระบบที่มีอยู่แล้วก่อนรอบนี้ ไม่ใช่สิ่งที่สร้างใหม่)

## 5. View Modal Fix

`.btn-attachments` → `#modalPhotoAttachments` → `renderPhotoGrid()` (ใน `all_old_certification.php`/`old_certification.php`) เรียก `ajax/get_certification_attachments.php` เพื่อดึง `url`/`url_enc` — เมื่อ endpoint คืนค่า URL ที่ถูกต้องแล้ว thumbnail (`<img>`) และ preview (คลิกเปิดรูปใหญ่/ลิงก์) จะใช้ URL เดียวกันนี้ทั้งหมด ไม่ต้องแก้ JS ฝั่งนี้เลย (โครงสร้าง JS เดิมถูกต้องอยู่แล้ว ปัญหาอยู่ที่ค่า URL ที่ endpoint ส่งมาเท่านั้น)

## 6. Edit Modal Fix

`renderExistingAttachments()` ใน `all_old_certification.php`/`old_certification.php` (แสดงไฟล์เดิมในฟอร์ม Edit พร้อมปุ่มลบ `.btn-del-existing`) **เรียก endpoint เดียวกันทุกประการ** (`ajax/get_certification_attachments.php`) — ยืนยันจาก source แล้วว่าไม่มี URL-building logic แยกต่างหากในฝั่ง Edit เลย จึงได้รับการแก้ไปพร้อมกันโดยอัตโนมัติจากการแก้จุดเดียว ไม่ต้องแก้ JS ของ Edit modal เพิ่มเติม

## 7. Physical Path vs Public URL

แยกให้ชัดตามที่กำหนด:
- **Physical path** (`file_exists()`, `unlink()`): ยังคงใช้ `PUBLIC_PATH . $rel` เหมือนเดิมทุกจุด (ใน `get_certification_attachments.php`'s `exists` check, และใน `FvCertificateAttachment::delete_with_file()`) — **ไม่ได้เปลี่ยน**
- **Public URL** (`<img src>`, `<a href>`): เปลี่ยนมาใช้ `FvCertificateAttachment::public_url()` (= `url_for($rel)`) เท่านั้น
- ไม่มีจุดใดในโค้ดที่แก้ไขนี้ใช้ค่าเดียวกันแทนกันทั้งสองแบบ

## 8. `fv_certificate_attachments.id` Schema Root Cause

**CONFIRMED** (ยืนยันซ้ำจาก `SHOW CREATE TABLE` ในรอบวิเคราะห์ก่อนหน้า): คอลัมน์ `id` เป็น `int(11) NOT NULL` เฉย ๆ ไม่มี PRIMARY KEY/AUTO_INCREMENT/INDEX ใด ๆ — ตรวจโค้ดเพิ่มเติมยืนยันว่า [DatabaseObject::create()](private/classes/databaseobject.class.php#L242-L258) เขียนถูกต้องอยู่แล้ว (`$this->id = self::$database->insert_id;` หลัง INSERT) แต่เพราะคอลัมน์ไม่ใช่ AUTO_INCREMENT จริง `insert_id` จึงเป็น `0` เสมอ — **สรุปว่าเป็น schema bug ล้วน ๆ ไม่ใช่ bug ของ ORM/PHP code**

## 9. Migration File

สร้างไฟล์ [FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql](FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql) ที่ root ของโปรเจกต์ (ไม่มี migration convention เดิมในระบบ จึงตั้งชื่อไฟล์ให้ชัดเจนตามที่กำหนดไว้เป็นทางเลือก) ประกอบด้วย: Step 0 (ตรวจสอบสถานะ + หา FK ที่อาจอ้างอิง), Step 1 (backup เป็นตารางสำเนาในฐานข้อมูลเดียวกัน), Step 2-4 (เพิ่มคอลัมน์ auto_increment ใหม่ → ลบคอลัมน์เดิม → เปลี่ยนชื่อ + ตั้ง PRIMARY KEY), Step 5 (verification query ครบ 5 ข้อ), และหัวข้อ Rollback Limitation

**อัปเดตล่าสุด (ตามคำสั่งย้ำ):** ปรับ STEP 5.4 ให้ตรวจสอบ**ทุกคอลัมน์**ที่ต้องรักษาไว้อย่างชัดเจน ไม่ใช่แค่ 3 คอลัมน์ — เทียบ `certificate_id`, `file_path`, `file_name`, `file_type`, `file_size`, `attachment_type`, `created_by`, `created_at` ระหว่างตาราง backup กับตารางหลังแก้ทีละคอลัมน์ด้วย NULL-safe equality (`<=>`) พร้อมเพิ่ม 5.4b (นับจำนวนแถวเทียบกันแบบ sanity check) และเพิ่มหมายเหตุในหัวไฟล์ (header comment) ยืนยันชัดเจนว่า ALTER TABLE ทุกคำสั่งกระทบเฉพาะคอลัมน์ `id`/`id_new` เท่านั้น ไม่มี UPDATE ข้อมูลคอลัมน์ใดเลย — **ยังคง: ห้าม execute, ห้ามแก้ข้อมูลจริง, ห้ามลบ attachment row เดิม, ไม่มีการแก้ source code อื่นใดในรอบนี้**

## 10. Existing Rows Migration Strategy

เลือกวิธี **"เพิ่ม auto_increment column ใหม่ (`id_new`) → ลบ column `id` เดิม → rename `id_new` เป็น `id` → ตั้ง PRIMARY KEY"** แทนการ `ALTER ... MODIFY id AUTO_INCREMENT PRIMARY KEY` ตรง ๆ (ซึ่งจะ error ทันทีเพราะมีค่า `id=0` ซ้ำกันหลายแถวอยู่แล้ว ขัดกับข้อบังคับ UNIQUE ของ PRIMARY KEY) วิธีนี้:
- ให้ MySQL/MariaDB เป็นผู้กำหนดเลขลำดับใหม่ให้ทุกแถวโดยอัตโนมัติแบบ deterministic (ทุกแถวได้เลขไม่ซ้ำแน่นอน)
- ไม่ลบแถวใดทิ้งเลย (ยืนยันด้วย verification query เทียบจำนวนแถวก่อน/หลัง และ join กับตาราง backup ด้วย `file_path`+`certificate_id`+`file_name`)
- ไม่กระทบ `certificate_id`, `file_path`, `file_name`, หรือคอลัมน์อื่นใดเลย (ค่าเดิมทุกคอลัมน์คงอยู่ ยกเว้นคอลัมน์ `id` เท่านั้นที่เปลี่ยนความหมาย)

## 11. ORM/Class Impact

**ไม่ต้องแก้ ORM/Class ใด ๆ เพิ่มเติมหลัง migration** — ยืนยันจาก source ว่า `FvCertificateAttachment::find_by_id()` (สืบทอดจาก `DatabaseObject`), `delete()`, `delete_with_file()` เขียนถูกต้องสมบูรณ์อยู่แล้วโดยอิงว่า `id` เป็น unique key เสมอ (ตามสมมติฐานปกติของ ORM) เมื่อ schema ถูกต้องแล้ว method เหล่านี้จะทำงานถูกต้องทันทีโดยไม่ต้องแก้โค้ดเพิ่ม `$db_columns` ก็มี `'id'` อยู่แล้วตั้งแต่ต้น ([FvCertificateAttachment.class.php](private/classes/FvCertificateAttachment.class.php#L4-L14)) ไม่ต้องเปลี่ยน

## 12. Single Attachment Delete Safety

ตรวจ flow ใน [fvscisold_attachment_delete.php](public/inspectofficer/ajax/fvscisold_attachment_delete.php) (ที่มี backend guard สถานะ active-working จากรอบก่อนหน้าอยู่แล้ว) ยืนยันว่าลำดับ logic ตรงตามที่กำหนดไว้แล้วทุกขั้นตอน: `attachment_id → find_by_id() → โหลด certification เจ้าของผ่าน certificate_id → ตรวจ is_active_working() → unlink physical path → delete() DB row` — **ไม่ต้องแก้ไฟล์นี้เพิ่มในรอบนี้** เพราะโครงสร้าง code ถูกต้องอยู่แล้ว ปัญหา "ลบผิดแถว" มีสาเหตุจาก schema bug (`id=0` ซ้ำ) ล้วน ๆ ไม่ใช่ลำดับ logic ผิด — เมื่อ migration ใน STEP นี้ถูก execute (โดยเจ้าของระบบ) ความเสี่ยงนี้จะหมดไปเองโดยไม่ต้องแก้โค้ดเพิ่ม

## 13. Business Logic Not Changed

ยืนยันว่า**ไม่ได้แก้**: Single Active Rule, Warning ก่อนแทนใบเดิม, Status Filter, Effective Status, Rule B, Delete Certification flow, `mark_active()`, `inspection_requests`, Electronic approval workflow, PDF สร.3 — grep ยืนยันว่าไฟล์ที่แก้ในรอบนี้ (2 ไฟล์ PHP) ไม่มีการอ้างอิงถึงสิ่งเหล่านี้เลย เป็นการแก้เฉพาะ URL-building logic ของ attachment เท่านั้น

## 14. Static Verification

**PHP syntax check (`php -l`)** รันกับทั้ง 2 ไฟล์ที่แก้ ผลลัพธ์ "No syntax errors detected":
- `private/classes/FvCertificateAttachment.class.php`
- `public/inspectofficer/ajax/get_certification_attachments.php`

**ตรวจ physical path vs public URL แยกกันจริง:** ยืนยันด้วยการอ่านโค้ดว่า `exists` (physical, ใช้ `$pubPath`) และ `url`/`url_enc` (public, ใช้ `public_url()`) ไม่ใช้ตัวแปรเดียวกันแล้ว

**ตรวจ class id handling:** ยืนยันว่า `$db_columns` มี `'id'` และ `DatabaseObject::create()`/`delete()` ใช้ `id` ถูกต้องตามหลักการทั่วไปอยู่แล้ว (ดูหัวข้อ 8, 11)

**ตรวจ SQL migration แบบ static (ไม่ execute):** ตรวจสอบ syntax ของทุก statement ด้วยสายตาตาม MySQL/MariaDB DDL syntax มาตรฐาน (`ALTER TABLE ... ADD COLUMN ... AUTO_INCREMENT`, `ADD UNIQUE KEY`, `DROP COLUMN`, `CHANGE COLUMN`, `ADD PRIMARY KEY`) — เป็นรูปแบบที่ใช้กันทั่วไปสำหรับกรณี backfill auto_increment ให้ตารางที่ไม่เคยมี PK มาก่อน ไม่มีเครื่องมือ static SQL linter อัตโนมัติในสภาพแวดล้อมนี้ จึงตรวจด้วยการอ่านทวนเท่านั้น **ไม่ได้ execute กับฐานข้อมูลจริงตามข้อกำหนด**

**grep ยืนยัน pattern เดียวกันที่พบในโมดูลอื่น (ไม่ได้แก้ ตามขอบเขต):**
| ไฟล์ | โมดูล |
|---|---|
| `public/fisherman/ajax/get_edit_request_detail.php` | Inspection Request attachment (คนละ module — `InspectionAttachment`) |
| `public/inspectofficer/ajax/get_manual_request_detail_for_update.php` | Manual Inspection Request attachment |
| `public/inspectofficer/ajax/get_request_attachments.php` | Inspection Request attachment |

ทั้ง 3 ไฟล์นี้มี `DOCUMENT_ROOT`/`PUBLIC_PATH` diff pattern เดียวกัน (โอกาสสูงที่จะมีบั๊ก URL แบบเดียวกัน) แต่**ไม่ได้แก้ในรอบนี้**เพราะอยู่นอกขอบเขต Paper Certification ตามข้อกำหนด — บันทึกไว้เป็น Open Issue ให้พิจารณาแก้แยกต่างหากในรอบถัดไปหากเจ้าของระบบต้องการ

**ไม่มี runtime test ใด ๆ ในรอบนี้** — ไม่ได้เปิด View/Edit modal จริง ไม่ได้อัปโหลด/ลบไฟล์จริง ไม่ได้รัน migration

## 15. Owner Actions Required

1. **Backup database** (mysqldump ทั้งฐานข้อมูลหรืออย่างน้อยตาราง `fv_certificate_attachments` แยกไว้นอกฐานข้อมูล เพิ่มเติมจาก backup table ที่ migration script สร้างให้อัตโนมัติใน Step 1)
2. **Run migration SQL** — เปิด [FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql](FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql) อ่านทบทวนทุก Step ก่อน แล้วรันทีละ Step ตามลำดับ (ไม่ใช่รันทั้งไฟล์รวดเดียวโดยไม่ดูผลระหว่างทาง)
3. **Verify attachment IDs** — รัน Step 5 (verification queries) ทุกข้อ ต้องได้ผลลัพธ์ตรงตามที่ระบุไว้ในคอมเมนต์ (จำนวนแถวเท่าเดิม, ไม่มี id ซ้ำ, ไม่มีแถวหลุดจาก backup) ก่อนถือว่า migration สำเร็จ
4. **Runtime test existing record** — ใช้ record จริงของเรือ `292212077` (ship_code) ทดสอบตาม Owner Runtime Test Cases ด้านล่าง

## 16. Owner Runtime Test Required

- **เปิด View ของเรือ 292212077 → เห็น 3 thumbnails:** `NOT TESTED — OWNER TO TEST`
- **click thumbnail → preview แสดงรูปจริง (ไม่ 404):** `NOT TESTED — OWNER TO TEST`
- **เปิด Edit → เห็นรูปเดิมครบ 3 รูป พร้อม preview ไม่พัง:** `NOT TESTED — OWNER TO TEST`
- **เพิ่ม attachment ใหม่ → เปิดดูได้ (URL ถูกต้อง):** `NOT TESTED — OWNER TO TEST`
- **ลบ attachment ทีละรูป (หลัง migration) → ลบถูกไฟล์เท่านั้น:** `NOT TESTED — OWNER TO TEST`
- **attachment อื่นของ certificate เดียวกันไม่หายไปด้วย:** `NOT TESTED — OWNER TO TEST`
- **attachment ของ certificate อื่นไม่หาย:** `NOT TESTED — OWNER TO TEST`
- **refresh หน้าแล้วยังแสดงถูกต้อง:** `NOT TESTED — OWNER TO TEST`
- **PDF attachment ถ้ามี → เปิดได้ (ไม่พยายาม render เป็น `<img>`):** `NOT TESTED — OWNER TO TEST`
- **History/non-working certification → ดู attachment ได้ แต่ลบไม่ได้ (backend guard เดิม):** `NOT TESTED — OWNER TO TEST`
- **Active working certification → จัดการ attachment ได้ตามสิทธิเดิม:** `NOT TESTED — OWNER TO TEST`
- **migration script รันผ่านครบทุก Step โดยไม่มี error และ verification query ทุกข้อผ่าน:** `NOT TESTED — OWNER TO TEST`

## 17. Remaining Risks

- **Migration ยังไม่ได้ execute** — schema bug ของ `fv_certificate_attachments.id` ยังคงอยู่จนกว่าเจ้าของระบบจะรัน `FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql` เอง (single attachment delete ยังมีความเสี่ยงลบผิดแถวอยู่จนกว่าจะรัน migration)
- **Broken URL pattern เดียวกันพบในอีก 3 ไฟล์นอกขอบเขต Paper Certification** (ดูหัวข้อ 14) — ยังไม่ได้แก้ ควรพิจารณาแก้แยกในรอบถัดไป
- **Redundant unique index หลัง migration**: Step 4 ของ migration อาจทิ้ง unique key `uq_fv_cert_att_id_new` ซ้ำซ้อนกับ PRIMARY KEY ไว้ (ไม่กระทบความถูกต้อง แต่ไม่สะอาด) มีคำสั่ง `DROP INDEX` เป็นทางเลือกไว้ให้ในไฟล์ migration ถ้าต้องการ cleanup
- **Race condition ระหว่างรัน migration**: ถ้ามีการ insert/update attachment ใหม่ระหว่างที่กำลังรัน migration steps (Step 1–4) ข้อมูลใหม่นั้นจะไม่อยู่ใน backup table — แนะนำให้เจ้าของระบบหยุดการใช้งานฟีเจอร์แนบไฟล์ Paper Certification ชั่วคราวระหว่างรัน (บันทึกไว้ในไฟล์ migration แล้ว)
- Open Issues เดิมทั้งหมดจากรอบก่อนหน้า (Delete Flow `mark_active()`, Existing bad data `ship_code=473100027`, `expiration_date` NULL edge case) ยังคงอยู่เหมือนเดิม ไม่ได้รับผลกระทบจากรอบนี้

---

**หยุดงานตามคำสั่ง — รอเจ้าของระบบ Runtime Test และดำเนินการ migration เอง**


