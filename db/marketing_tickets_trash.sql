SET @db_name = DATABASE();

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE marketing_tickets ADD COLUMN eliminado_en TIMESTAMP NULL AFTER asignado_a',
        'SELECT "eliminado_en ya existe"'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'marketing_tickets'
      AND COLUMN_NAME = 'eliminado_en'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE marketing_tickets ADD COLUMN eliminado_por INT UNSIGNED NULL AFTER eliminado_en',
        'SELECT "eliminado_por ya existe"'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'marketing_tickets'
      AND COLUMN_NAME = 'eliminado_por'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE marketing_tickets ADD INDEX idx_eliminado (eliminado_en)',
        'SELECT "idx_eliminado ya existe"'
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'marketing_tickets'
      AND INDEX_NAME = 'idx_eliminado'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
