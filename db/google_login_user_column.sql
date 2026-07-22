ALTER TABLE marketing_usuarios
    ADD COLUMN google_sub VARCHAR(160) NULL AFTER password_hash;
