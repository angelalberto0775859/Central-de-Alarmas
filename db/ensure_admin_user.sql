-- Ejecuta este SQL en phpMyAdmin si el administrador inicial no puede entrar.
-- Asegura que el correo exista, este activo y tenga la contrasena admin12345.

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
