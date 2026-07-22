CREATE TABLE IF NOT EXISTS marketing_ticket_mensajes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    autor_nombre VARCHAR(140) NOT NULL,
    autor_rol ENUM('admin','marketing','usuario') NOT NULL DEFAULT 'marketing',
    mensaje TEXT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE marketing_ticket_mensajes
    MODIFY autor_rol ENUM('admin','marketing','usuario') NOT NULL DEFAULT 'marketing';
