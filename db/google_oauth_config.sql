CREATE TABLE IF NOT EXISTS marketing_configuracion (
    clave VARCHAR(120) NOT NULL PRIMARY KEY,
    valor TEXT NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO marketing_configuracion (clave, valor)
VALUES
    ('CDA_GOOGLE_CLIENT_ID', 'TU_CLIENT_ID.apps.googleusercontent.com'),
    ('CDA_GOOGLE_CLIENT_SECRET', 'TU_CLIENT_SECRET'),
    ('CDA_GOOGLE_REDIRECT_URI', 'https://centraldealarmas.com.mx/google-callback.php')
ON DUPLICATE KEY UPDATE
    valor = VALUES(valor);
