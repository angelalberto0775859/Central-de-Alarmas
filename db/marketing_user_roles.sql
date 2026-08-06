ALTER TABLE marketing_usuarios
    MODIFY rol ENUM('admin','marketing','usuario','manager','trabajador') NOT NULL DEFAULT 'usuario';
