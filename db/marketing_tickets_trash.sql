ALTER TABLE marketing_tickets ADD COLUMN eliminado_en DATETIME NULL;
ALTER TABLE marketing_tickets ADD COLUMN eliminado_por INT UNSIGNED NULL;
ALTER TABLE marketing_tickets ADD INDEX idx_eliminado (eliminado_en);
