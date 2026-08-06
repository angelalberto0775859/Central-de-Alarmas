ALTER TABLE marketing_usuarios
    ADD COLUMN reset_token_hash VARCHAR(64) NULL AFTER google_sub,
    ADD COLUMN reset_token_expira DATETIME NULL AFTER reset_token_hash,
    ADD INDEX idx_reset_token_hash (reset_token_hash);
