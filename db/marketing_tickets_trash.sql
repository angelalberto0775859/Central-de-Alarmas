ALTER TABLE marketing_tickets
    ADD COLUMN eliminado_en TIMESTAMP NULL AFTER asignado_a,
    ADD COLUMN eliminado_por INT UNSIGNED NULL AFTER eliminado_en,
    ADD INDEX idx_eliminado (eliminado_en);
