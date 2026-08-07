CREATE TABLE marketing_usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    google_sub VARCHAR(160) NULL,
    reset_token_hash VARCHAR(64) NULL,
    reset_token_expira DATETIME NULL,
    rol ENUM('admin','usuario','manager','trabajador') NOT NULL DEFAULT 'usuario',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reset_token_hash (reset_token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE marketing_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(32) NOT NULL UNIQUE,
    solicitante VARCHAR(140) NOT NULL,
    correo VARCHAR(160) NOT NULL,
    departamento VARCHAR(140) NOT NULL,
    actividad VARCHAR(180) NOT NULL,
    tipo_solicitud VARCHAR(120) NOT NULL,
    objetivo TEXT NOT NULL,
    publico VARCHAR(180) NULL,
    referencias TEXT NULL,
    fecha_requerida DATE NOT NULL,
    fecha_entrega_estimada DATE NULL,
    prioridad ENUM('Normal','Alta','Urgente') NOT NULL DEFAULT 'Normal',
    estado VARCHAR(60) NOT NULL DEFAULT 'Recibido',
    comentarios TEXT NULL,
    respuesta_interna TEXT NULL,
    asignado_a VARCHAR(140) NULL,
    eliminado_en TIMESTAMP NULL,
    eliminado_por INT UNSIGNED NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_folio_correo (folio, correo),
    INDEX idx_estado (estado),
    INDEX idx_fecha_entrega_estimada (fecha_entrega_estimada),
    INDEX idx_eliminado (eliminado_en),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE marketing_ticket_archivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    mime VARCHAR(120) NULL,
    tamano INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_marketing_archivos_ticket FOREIGN KEY (ticket_id) REFERENCES marketing_tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE marketing_ticket_historial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado VARCHAR(60) NOT NULL,
    comentario TEXT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_marketing_historial_ticket FOREIGN KEY (ticket_id) REFERENCES marketing_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_marketing_historial_usuario FOREIGN KEY (usuario_id) REFERENCES marketing_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE marketing_ticket_mensajes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    autor_nombre VARCHAR(140) NOT NULL,
    autor_rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
    mensaje TEXT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_marketing_mensajes_ticket FOREIGN KEY (ticket_id) REFERENCES marketing_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_marketing_mensajes_usuario FOREIGN KEY (usuario_id) REFERENCES marketing_usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE marketing_ticket_mensaje_archivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mensaje_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    mime VARCHAR(120) NULL,
    tamano INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_marketing_mensaje_archivos_mensaje FOREIGN KEY (mensaje_id) REFERENCES marketing_ticket_mensajes(id) ON DELETE CASCADE,
    CONSTRAINT fk_marketing_mensaje_archivos_ticket FOREIGN KEY (ticket_id) REFERENCES marketing_tickets(id) ON DELETE CASCADE,
    INDEX idx_mensaje_id (mensaje_id),
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Genera el hash con:
-- php -r "echo password_hash('TuPasswordSeguro', PASSWORD_DEFAULT), PHP_EOL;"
-- y reemplaza PASSWORD_HASH_AQUI antes de importar o despues con UPDATE.
INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol)
VALUES ('Angel Alberto', 'angelalberto077@gmail.com', '$2y$12$EyWnX97s2BYGtdSNO7fRQetNplmYWQrgCmZ7piOalnMcRDvC02iV6', 'admin');

INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
VALUES
    ('Salducin', 'salducin@centraldealarmas.com.mx', NULL, 'admin', 1),
    ('R Villaverde', 'rvillaverde@centraldealarmas.com.mx', NULL, 'manager', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    rol = VALUES(rol),
    activo = 1;

UPDATE marketing_usuarios
SET rol = 'usuario'
WHERE rol = 'admin'
AND correo NOT IN ('angelalberto077@gmail.com', 'salducin@centraldealarmas.com.mx');
