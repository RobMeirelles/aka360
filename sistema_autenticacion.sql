-- Sistema de Autenticación y Permisos para Akademia 360
-- Base de Datos: akademia360new

USE `akademia360new`;

-- Tabla de usuarios del sistema
CREATE TABLE IF NOT EXISTS `usuarios_admin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `nombre_completo` VARCHAR(255) NOT NULL,
    `rol` ENUM('super_admin', 'admin', 'editor') DEFAULT 'editor',
    `activo` BOOLEAN DEFAULT TRUE,
    `ultimo_acceso` TIMESTAMP NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `creado_por` INT NULL,
    FOREIGN KEY (`creado_por`) REFERENCES `usuarios_admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de permisos del sistema
CREATE TABLE IF NOT EXISTS `permisos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(50) UNIQUE NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `modulo` VARCHAR(50) NOT NULL,
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación usuarios-permisos
CREATE TABLE IF NOT EXISTS `usuarios_permisos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `permiso_id` INT NOT NULL,
    `otorgado_por` INT NULL,
    `fecha_otorgado` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permiso_id`) REFERENCES `permisos`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`otorgado_por`) REFERENCES `usuarios_admin`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `usuario_permiso_unique` (`usuario_id`, `permiso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones activas
CREATE TABLE IF NOT EXISTS `sesiones_activas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `session_id` VARCHAR(255) UNIQUE NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_ultima_actividad` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `activa` BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de logs de actividad
CREATE TABLE IF NOT EXISTS `logs_actividad` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `accion` VARCHAR(100) NOT NULL,
    `modulo` VARCHAR(50) NOT NULL,
    `descripcion` TEXT,
    `ip_address` VARCHAR(45),
    `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios_admin`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar permisos del sistema
INSERT INTO `permisos` (`codigo`, `nombre`, `descripcion`, `modulo`) VALUES
-- Dashboard
('dashboard_view', 'Ver Dashboard', 'Acceso al panel principal del administrador', 'dashboard'),

-- Cursos
('cursos_view', 'Ver Cursos', 'Ver lista de cursos', 'cursos'),
('cursos_create', 'Crear Cursos', 'Crear nuevos cursos', 'cursos'),
('cursos_edit', 'Editar Cursos', 'Modificar cursos existentes', 'cursos'),
('cursos_delete', 'Eliminar Cursos', 'Eliminar cursos', 'cursos'),

-- Noticias
('noticias_view', 'Ver Noticias', 'Ver lista de noticias', 'noticias'),
('noticias_create', 'Crear Noticias', 'Crear nuevas noticias', 'noticias'),
('noticias_edit', 'Editar Noticias', 'Modificar noticias existentes', 'noticias'),
('noticias_delete', 'Eliminar Noticias', 'Eliminar noticias', 'noticias'),

-- Relatores
('relatores_view', 'Ver Relatores', 'Ver lista de relatores', 'relatores'),
('relatores_create', 'Crear Relatores', 'Crear nuevos relatores', 'relatores'),
('relatores_edit', 'Editar Relatores', 'Modificar relatores existentes', 'relatores'),
('relatores_delete', 'Eliminar Relatores', 'Eliminar relatores', 'relatores'),

-- Servicios
('servicios_view', 'Ver Servicios', 'Ver lista de servicios', 'servicios'),
('servicios_create', 'Crear Servicios', 'Crear nuevos servicios', 'servicios'),
('servicios_edit', 'Editar Servicios', 'Modificar servicios existentes', 'servicios'),
('servicios_delete', 'Eliminar Servicios', 'Eliminar servicios', 'servicios'),

-- Carrusel
('carousel_view', 'Ver Carrusel', 'Ver contenido del carrusel', 'carousel'),
('carousel_create', 'Crear Carrusel', 'Agregar contenido al carrusel', 'carousel'),
('carousel_edit', 'Editar Carrusel', 'Modificar contenido del carrusel', 'carousel'),
('carousel_delete', 'Eliminar Carrusel', 'Eliminar contenido del carrusel', 'carousel'),

-- Contactos
('contactos_view', 'Ver Contactos', 'Ver formularios de contacto', 'contactos'),
('contactos_delete', 'Eliminar Contactos', 'Eliminar formularios de contacto', 'contactos'),

-- Administración de Cuentas (solo super admin)
('usuarios_view', 'Ver Usuarios', 'Ver lista de usuarios del sistema', 'usuarios'),
('usuarios_create', 'Crear Usuarios', 'Crear nuevos usuarios del sistema', 'usuarios'),
('usuarios_edit', 'Editar Usuarios', 'Modificar usuarios del sistema', 'usuarios'),
('usuarios_delete', 'Eliminar Usuarios', 'Eliminar usuarios del sistema', 'usuarios'),
('usuarios_permisos', 'Gestionar Permisos', 'Asignar y revocar permisos a usuarios', 'usuarios');

-- Crear usuario super admin por defecto
-- Password: admin123 (hash bcrypt)
INSERT INTO `usuarios_admin` (`username`, `email`, `password_hash`, `nombre_completo`, `rol`, `activo`) VALUES
('superadmin', 'admin@akademia360.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrador', 'super_admin', TRUE);

-- Asignar todos los permisos al super admin
INSERT INTO `usuarios_permisos` (`usuario_id`, `permiso_id`, `otorgado_por`)
SELECT 1, id, 1 FROM `permisos`;

-- Crear índices para mejor rendimiento
CREATE INDEX idx_usuarios_admin_username ON `usuarios_admin` (`username`);
CREATE INDEX idx_usuarios_admin_email ON `usuarios_admin` (`email`);
CREATE INDEX idx_usuarios_admin_activo ON `usuarios_admin` (`activo`);
CREATE INDEX idx_permisos_codigo ON `permisos` (`codigo`);
CREATE INDEX idx_permisos_modulo ON `permisos` (`modulo`);
CREATE INDEX idx_usuarios_permisos_usuario ON `usuarios_permisos` (`usuario_id`);
CREATE INDEX idx_usuarios_permisos_permiso ON `usuarios_permisos` (`permiso_id`);
CREATE INDEX idx_sesiones_activas_usuario ON `sesiones_activas` (`usuario_id`);
CREATE INDEX idx_sesiones_activas_session_id ON `sesiones_activas` (`session_id`);
CREATE INDEX idx_logs_actividad_usuario ON `logs_actividad` (`usuario_id`);
CREATE INDEX idx_logs_actividad_fecha ON `logs_actividad` (`fecha`);
CREATE INDEX idx_logs_actividad_modulo ON `logs_actividad` (`modulo`);

-- Crear vistas útiles para consultas frecuentes
CREATE VIEW `v_usuarios_permisos` AS
SELECT 
    u.id as usuario_id,
    u.username,
    u.nombre_completo,
    u.rol,
    u.activo as usuario_activo,
    p.codigo as permiso_codigo,
    p.nombre as permiso_nombre,
    p.modulo as permiso_modulo,
    up.fecha_otorgado
FROM usuarios_admin u
LEFT JOIN usuarios_permisos up ON u.id = up.usuario_id
LEFT JOIN permisos p ON up.permiso_id = p.id
WHERE u.activo = TRUE AND (p.activo = TRUE OR p.activo IS NULL);

-- Crear procedimiento para limpiar sesiones expiradas
DELIMITER //
CREATE PROCEDURE `limpiar_sesiones_expiradas`()
BEGIN
    DELETE FROM sesiones_activas 
    WHERE fecha_ultima_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    OR activa = FALSE;
END //
DELIMITER ;

-- Crear evento para limpiar sesiones automáticamente
CREATE EVENT `limpiar_sesiones_event`
ON SCHEDULE EVERY 1 HOUR
DO CALL limpiar_sesiones_expiradas();
