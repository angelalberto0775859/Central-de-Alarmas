CREATE TABLE IF NOT EXISTS marketing_usuarios (
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

CREATE TABLE IF NOT EXISTS marketing_tickets (
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

CREATE TABLE IF NOT EXISTS marketing_ticket_archivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    mime VARCHAR(120) NULL,
    tamano INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_ticket_historial (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    estado VARCHAR(60) NOT NULL,
    comentario TEXT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_ticket_mensajes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NULL,
    autor_nombre VARCHAR(140) NOT NULL,
    autor_rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
    mensaje TEXT NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ticket_id (ticket_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_ticket_mensaje_archivos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mensaje_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    mime VARCHAR(120) NULL,
    tamano INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mensaje_id (mensaje_id),
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marketing_configuracion (
    clave VARCHAR(120) NOT NULL PRIMARY KEY,
    valor TEXT NOT NULL,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
VALUES (
    'Angel Alberto',
    'angelalberto077@gmail.com',
    '$2y$12$EyWnX97s2BYGtdSNO7fRQetNplmYWQrgCmZ7piOalnMcRDvC02iV6',
    'admin',
    1
)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    password_hash = VALUES(password_hash),
    rol = 'admin',
    activo = 1;

INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
VALUES
    ('Salducin', 'salducin@centraldealarmas.com.mx', NULL, 'usuario', 1),
    ('R Villaverde', 'rvillaverde@centraldealarmas.com.mx', NULL, 'manager', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    rol = VALUES(rol),
    activo = 1;

UPDATE marketing_usuarios
SET rol = 'usuario'
WHERE rol = 'admin'
AND correo <> 'angelalberto077@gmail.com';
