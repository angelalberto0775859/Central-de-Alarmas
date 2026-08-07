ALTER TABLE marketing_tickets ADD COLUMN fecha_entrega_estimada DATE NULL AFTER fecha_requerida;
ALTER TABLE marketing_tickets ADD INDEX idx_fecha_entrega_estimada (fecha_entrega_estimada);
