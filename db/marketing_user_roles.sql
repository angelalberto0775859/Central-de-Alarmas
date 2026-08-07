UPDATE marketing_usuarios SET rol = 'manager' WHERE rol = 'marketing';

ALTER TABLE marketing_usuarios
    MODIFY rol ENUM('admin','usuario','manager','trabajador') NOT NULL DEFAULT 'usuario';
