-- Manual FV Certificate audit extension.
-- Backward-compatible: existing inspection request logs keep their current fields.
-- Run once against the FVSCIS database.

SET @has_entity_type := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND COLUMN_NAME = 'entity_type'
);
SET @sql := IF(@has_entity_type = 0,
    'ALTER TABLE inspection_logs ADD COLUMN entity_type VARCHAR(50) NULL AFTER inspection_request_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_entity_id := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND COLUMN_NAME = 'entity_id'
);
SET @sql := IF(@has_entity_id = 0,
    'ALTER TABLE inspection_logs ADD COLUMN entity_id INT NULL AFTER entity_type',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_old_values := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND COLUMN_NAME = 'old_values'
);
SET @sql := IF(@has_old_values = 0,
    'ALTER TABLE inspection_logs ADD COLUMN old_values LONGTEXT NULL AFTER note',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_new_values := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND COLUMN_NAME = 'new_values'
);
SET @sql := IF(@has_new_values = 0,
    'ALTER TABLE inspection_logs ADD COLUMN new_values LONGTEXT NULL AFTER old_values',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_actor_role := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND COLUMN_NAME = 'actor_role'
);
SET @sql := IF(@has_actor_role = 0,
    'ALTER TABLE inspection_logs ADD COLUMN actor_role VARCHAR(50) NULL AFTER created_by',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_entity_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND INDEX_NAME = 'idx_inspection_logs_entity'
);
SET @sql := IF(@has_entity_index = 0,
    'ALTER TABLE inspection_logs ADD KEY idx_inspection_logs_entity (entity_type, entity_id)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_actor_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'inspection_logs' AND INDEX_NAME = 'idx_inspection_logs_created_by'
);
SET @sql := IF(@has_actor_index = 0,
    'ALTER TABLE inspection_logs ADD KEY idx_inspection_logs_created_by (created_by)',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO log_actions (code, description_th, description_en, category, is_visible)
SELECT 'fvscis_attachment_added', 'เพิ่มไฟล์แนบใบรับรอง Manual', 'Manual certificate attachment added', 'certificate', 1
WHERE NOT EXISTS (SELECT 1 FROM log_actions WHERE code = 'fvscis_attachment_added');

INSERT INTO log_actions (code, description_th, description_en, category, is_visible)
SELECT 'fvscis_attachment_deleted', 'ลบไฟล์แนบใบรับรอง Manual', 'Manual certificate attachment deleted', 'certificate', 1
WHERE NOT EXISTS (SELECT 1 FROM log_actions WHERE code = 'fvscis_attachment_deleted');
