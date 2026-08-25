-- ============================================================================
-- FIX_FV_CERTIFICATE_ATTACHMENTS_ID.sql
-- ============================================================================
-- ปัญหาเดิม (ยืนยันจาก SHOW CREATE TABLE จริง):
--   fv_certificate_attachments.id  =  INT NOT NULL
--   ไม่มี PRIMARY KEY, ไม่มี AUTO_INCREMENT, ไม่มี index ใด ๆ บนคอลัมน์นี้เลย
--   ผลคือทุกแถวที่เคย INSERT มา (ผ่าน create_fvscisold.php / update_fvscisold.php)
--   มีค่า id = 0 ซ้ำกันทั้งหมด เพราะ DatabaseObject::create() อ่านค่าจาก
--   mysqli::$insert_id ซึ่งจะเป็น 0 เสมอถ้าคอลัมน์ไม่ใช่ AUTO_INCREMENT จริง
--
--   ผลกระทบ: FvCertificateAttachment::find_by_id()/delete() (ที่ query ด้วย
--   WHERE id = ?) ไม่สามารถระบุแถวที่ต้องการได้อย่างแม่นยำ เสี่ยงลบ/อ้างอิง
--   attachment ผิดแถวเมื่อมีมากกว่า 1 แถวที่ id=0 อยู่ในตาราง
--
-- เป้าหมาย schema หลังแก้:
--   id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY
--
-- คอลัมน์อื่นทั้งหมด (certificate_id, file_path, file_name, file_type, file_size,
-- attachment_type, created_by, created_at) จะไม่ถูกแตะต้องเลยโดยโครงสร้าง migration นี้
-- (ทุก ALTER TABLE ด้านล่างกระทำเฉพาะคอลัมน์ id/id_new เท่านั้น ไม่มี UPDATE ข้อมูลใด ๆ)
-- และมี verification query (STEP 5.4) ตรวจย้ำอีกชั้นว่าค่าจริงในทุกคอลัมน์เหล่านี้ไม่เปลี่ยนแปลง
--
-- ขอบเขต: แก้เฉพาะตาราง fv_certificate_attachments เท่านั้น
--   ไม่แตะ fv_sanitation_certification_old, inspection_requests, หรือตารางอื่นใด
--   ไม่แตะ business logic ของ Paper Certification / Electronic FVSCIS
--   ไม่ลบ attachment row เดิมแม้แต่แถวเดียว (มีแต่ ADD/DROP คอลัมน์ id เท่านั้น)
--
-- **ห้าม RUN สคริปต์นี้ในรอบที่สร้างไฟล์นี้ขึ้นมา**
-- เจ้าของระบบเป็นผู้ตัดสินใจรันเองหลังตรวจสอบ backup และ verification query แล้ว
-- ============================================================================


-- ----------------------------------------------------------------------------
-- STEP 0: ตรวจสอบสถานะปัจจุบันก่อนแก้ (READ-ONLY — รันตรวจสอบได้ทันทีอย่างปลอดภัย)
-- ----------------------------------------------------------------------------

-- 0.1 ยืนยันโครงสร้างตารางปัจจุบัน (ต้องเห็นว่า id ไม่มี PRIMARY KEY/AUTO_INCREMENT)
SHOW CREATE TABLE fv_certificate_attachments;

-- 0.2 นับจำนวนแถวทั้งหมด (เก็บตัวเลขนี้ไว้เทียบหลัง migration ต้องเท่ากันเป๊ะ)
SELECT COUNT(*) AS total_rows_before FROM fv_certificate_attachments;

-- 0.3 ตรวจว่ามีแถว id ซ้ำกันจริงตามที่วิเคราะห์ไว้ (คาดว่าจะเห็น id=0 มีจำนวนมาก)
SELECT id, COUNT(*) AS dup_count
FROM fv_certificate_attachments
GROUP BY id
HAVING COUNT(*) > 1;

-- 0.4 ตรวจว่ามี FOREIGN KEY ใดอ้างอิงมาที่ fv_certificate_attachments.id หรือไม่
--     (คาดว่าไม่มี เพราะไม่เคยมี PRIMARY KEY ให้ FK อ้างอิงได้มาก่อน — ควรตรวจยืนยันเองก่อนรัน)
SELECT
  TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = 'fv_certificate_attachments';


-- ----------------------------------------------------------------------------
-- STEP 1: สำรองข้อมูลก่อนแก้ (BACKUP) — ทำในฐานข้อมูลเดียวกัน เพื่อย้อนกลับได้เร็วที่สุด
--         (นอกเหนือจากนี้ แนะนำให้ mysqldump ตารางนี้แยกไว้อีกชั้นนอกฐานข้อมูลด้วย)
-- ----------------------------------------------------------------------------

CREATE TABLE fv_certificate_attachments_backup_20260823 AS
SELECT * FROM fv_certificate_attachments;

-- ตรวจว่าจำนวนแถวใน backup ตรงกับต้นฉบับ (ต้องเท่ากับผลจาก STEP 0.2)
SELECT COUNT(*) AS total_rows_backup FROM fv_certificate_attachments_backup_20260823;


-- ----------------------------------------------------------------------------
-- STEP 2: เพิ่มคอลัมน์ id ใหม่แบบ AUTO_INCREMENT + UNIQUE เพื่อกำหนด unique id
--         ให้ทุกแถวเดิมโดยอัตโนมัติ (MySQL/MariaDB จะเติมเลขให้เรียงตามลำดับแถว
--         ที่มีอยู่จริงในตาราง ณ ขณะ ALTER — deterministic ในความหมายที่ว่า
--         ทุกแถวจะได้เลขไม่ซ้ำกันแน่นอน แม้ไม่อ้างอิงความหมายเดิมของ id=0)
--         วิธีนี้ไม่ลบแถวใด ๆ ทิ้ง และไม่กระทบ certificate_id/file_path/file_name เลย
-- ----------------------------------------------------------------------------

ALTER TABLE fv_certificate_attachments
  ADD COLUMN id_new INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ADD UNIQUE KEY uq_fv_cert_att_id_new (id_new);

-- ตรวจว่าจำนวนแถวยังเท่าเดิม และ id_new ไม่มีค่าซ้ำ/ไม่มีค่า NULL
SELECT COUNT(*) AS total_rows_after_step2 FROM fv_certificate_attachments;
SELECT id_new, COUNT(*) AS dup_count
FROM fv_certificate_attachments
GROUP BY id_new
HAVING COUNT(*) > 1;


-- ----------------------------------------------------------------------------
-- STEP 3: ลบคอลัมน์ id เดิม (ค่า 0 ซ้ำทั้งหมด ไม่มีความหมายอ้างอิงใด ๆ อยู่แล้ว
--         ยืนยันจาก STEP 0.4 ว่าไม่มี FK อ้างอิงมาที่คอลัมน์นี้)
-- ----------------------------------------------------------------------------

ALTER TABLE fv_certificate_attachments DROP COLUMN id;


-- ----------------------------------------------------------------------------
-- STEP 4: เปลี่ยนชื่อ id_new -> id พร้อมตั้งเป็น PRIMARY KEY
--         (คอลัมน์นี้มี UNIQUE KEY + AUTO_INCREMENT อยู่แล้วจาก STEP 2)
-- ----------------------------------------------------------------------------

ALTER TABLE fv_certificate_attachments
  CHANGE COLUMN id_new id INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE fv_certificate_attachments
  ADD PRIMARY KEY (id);

-- ตรวจสอบ index ที่มีอยู่หลังตั้ง PRIMARY KEY แล้ว (จะเห็นทั้ง PRIMARY และ uq_fv_cert_att_id_new
-- ซ้ำซ้อนกันอยู่บนคอลัมน์เดียวกัน — ไม่กระทบความถูกต้องของข้อมูล แต่ถ้าต้องการความสะอาด
-- ของ schema สามารถ DROP INDEX uq_fv_cert_att_id_new ทิ้งได้ในภายหลัง หลังยืนยัน PRIMARY KEY ใช้งานได้แล้ว)
SHOW INDEX FROM fv_certificate_attachments;

-- (ทางเลือก — ไม่บังคับ) ลบ unique key ที่ซ้ำซ้อนกับ PRIMARY KEY ทิ้งเพื่อความสะอาดของ schema
-- ควรรันแยกต่างหาก "หลัง" ยืนยันว่า Step 4 ผ่านและแอปพลิเคชันทำงานถูกต้องแล้วเท่านั้น
-- ALTER TABLE fv_certificate_attachments DROP INDEX uq_fv_cert_att_id_new;


-- ----------------------------------------------------------------------------
-- STEP 5: VERIFICATION — ต้องรันและตรวจผลทุกข้อก่อนถือว่า migration สำเร็จ
-- ----------------------------------------------------------------------------

-- 5.1 โครงสร้างตารางต้องมี PRIMARY KEY + AUTO_INCREMENT บน id แล้ว
SHOW CREATE TABLE fv_certificate_attachments;

-- 5.2 จำนวนแถวต้องเท่ากับ STEP 0.2 เป๊ะ (ไม่มีแถวหาย/ไม่มีแถวเพิ่ม)
SELECT COUNT(*) AS total_rows_after FROM fv_certificate_attachments;

-- 5.3 id ต้องไม่มีค่าใดซ้ำกันอีกต่อไป
SELECT id, COUNT(*) AS dup_count
FROM fv_certificate_attachments
GROUP BY id
HAVING COUNT(*) > 1;
-- (คาดหวังผลลัพธ์: 0 แถว)

-- 5.4 ข้อมูลสำคัญอื่นทั้งหมด (certificate_id, file_path, file_name, file_type, file_size,
--     attachment_type, created_by, created_at) ต้องไม่เปลี่ยนแปลงเลย เทียบกับ backup
--     แถวต่อแถว โดย join ด้วยคอลัมน์ที่ไม่ใช่ id (file_path มี timestamp+random อยู่ใน
--     ชื่อไฟล์ ทำให้ไม่ซ้ำกันในทางปฏิบัติ จึงใช้เป็น join key ที่ปลอดภัย)
SELECT b.file_path
FROM fv_certificate_attachments_backup_20260823 b
LEFT JOIN fv_certificate_attachments a
  ON a.file_path = b.file_path
 AND a.certificate_id  <=> b.certificate_id
 AND a.file_name       <=> b.file_name
 AND a.file_type        <=> b.file_type
 AND a.file_size        <=> b.file_size
 AND a.attachment_type <=> b.attachment_type
 AND a.created_by      <=> b.created_by
 AND a.created_at      <=> b.created_at
WHERE a.file_path IS NULL;
-- (คาดหวังผลลัพธ์: 0 แถว — ถ้ามีแถวหลุดออกมา แปลว่ามีบางคอลัมน์เปลี่ยนไปจาก backup
--  ห้าม DROP ตาราง backup จนกว่าจะตรวจสอบสาเหตุให้แน่ชัด; <=> คือ NULL-safe equality
--  ของ MySQL/MariaDB ใช้เทียบค่าที่อาจเป็น NULL ได้โดยไม่ error)

-- 5.4b นับจำนวนแถวทั้งสองฝั่งให้เท่ากันแบบตรง ๆ อีกชั้น (sanity check เพิ่มเติม)
SELECT
  (SELECT COUNT(*) FROM fv_certificate_attachments_backup_20260823) AS backup_count,
  (SELECT COUNT(*) FROM fv_certificate_attachments) AS current_count;
-- (คาดหวังผลลัพธ์: backup_count = current_count เป๊ะ)

-- 5.5 ทดสอบ query จริงที่แอปพลิเคชันใช้ (read-only) เพื่อยืนยันว่าไม่พัง
--     ตัวอย่าง: ใช้ certificate_id ของ record ทดสอบที่เจ้าของระบบใช้อ้างอิง (ship_code=292212077)
--     ให้แทน <certificate_id> ด้วยค่าจริงก่อนรัน
-- SELECT * FROM fv_certificate_attachments WHERE certificate_id = <certificate_id>;


-- ----------------------------------------------------------------------------
-- ROLLBACK LIMITATION
-- ----------------------------------------------------------------------------
-- หากพบปัญหาหลัง Step 4 และต้องการย้อนกลับ:
--   1) ตารางต้นฉบับ (ก่อนแก้) ยังอยู่ครบใน fv_certificate_attachments_backup_20260823
--   2) การย้อนกลับต้อง DROP ตาราง fv_certificate_attachments ปัจจุบันแล้ว RENAME
--      ตาราง backup กลับมาใช้แทน (จะทำให้ id กลับไปเป็น 0 ซ้ำเหมือนเดิมทุกประการ)
--   3) ถ้ามีการ INSERT/UPDATE attachment ใหม่เกิดขึ้นระหว่างช่วงที่ migration ทำงานไปแล้ว
--      (หลัง Step 2) ข้อมูลใหม่เหล่านั้นจะไม่อยู่ใน backup — ควรหยุดการใช้งานฟีเจอร์แนบไฟล์
--      Paper Certification ชั่วคราวระหว่างรัน migration นี้ เพื่อป้องกัน race condition
--   4) ไม่มีทาง rollback แบบอัตโนมัติ (ต้องทำด้วยมือตามขั้นตอนข้างต้น)
--
-- อย่าลบตาราง fv_certificate_attachments_backup_20260823 จนกว่าจะมั่นใจว่าระบบทำงาน
-- ถูกต้องสมบูรณ์แล้วเป็นระยะเวลาหนึ่ง (แนะนำอย่างน้อยหลาย  วันของการใช้งานจริง)
-- ============================================================================
