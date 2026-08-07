UPDATE marketing_usuarios
SET rol = 'usuario'
WHERE rol = 'admin'
AND correo NOT IN ('angelalberto077@gmail.com', 'salducin@centraldealarmas.com.mx');

INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
VALUES
    ('Angel Alberto', 'angelalberto077@gmail.com', '$2y$12$EyWnX97s2BYGtdSNO7fRQetNplmYWQrgCmZ7piOalnMcRDvC02iV6', 'admin', 1),
    ('Salducin', 'salducin@centraldealarmas.com.mx', NULL, 'admin', 1),
    ('R Villaverde', 'rvillaverde@centraldealarmas.com.mx', NULL, 'manager', 1)
ON DUPLICATE KEY UPDATE
    rol = VALUES(rol),
    activo = 1;
