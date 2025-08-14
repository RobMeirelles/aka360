-- =============================================================================
-- SISTEMA COMPLETO AKADEMIA 360 - BASE DE DATOS UNIFICADA
-- =============================================================================
-- Este archivo contiene toda la estructura de la base de datos para Akademia 360
-- Incluye: Tablas principales, sistema de autenticación y sistema de analytics
-- =============================================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS `akademia360new` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE `akademia360new`;

-- =============================================================================
-- TABLAS PRINCIPALES DEL SISTEMA
-- =============================================================================

-- Tabla de contenido destacado para el carrusel
CREATE TABLE IF NOT EXISTS `contenido_destacado` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT,
    `imagen` VARCHAR(255),
    `tipo` ENUM('curso', 'noticia', 'servicio') NOT NULL,
    `id_contenido` INT NOT NULL,
    `fecha_inicio` DATE,
    `fecha_fin` DATE,
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de noticias
CREATE TABLE IF NOT EXISTS `noticias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `resumen` TEXT,
    `contenido` LONGTEXT,
    `imagen` VARCHAR(255),
    `autor` VARCHAR(100),
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relatores
CREATE TABLE IF NOT EXISTS `relatores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `titulo` VARCHAR(255),
    `biografia` TEXT,
    `especializacion` VARCHAR(255),
    `universidad` VARCHAR(255),
    `imagen` VARCHAR(255),
    `email` VARCHAR(255),
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de servicios
CREATE TABLE IF NOT EXISTS `servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `descripcion` TEXT,
    `icono` VARCHAR(100),
    `imagen` VARCHAR(255),
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cursos
CREATE TABLE IF NOT EXISTS `cursos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion_corta` TEXT,
    `descripcion_completa` LONGTEXT,
    `objetivos` TEXT,
    `programa_detallado` LONGTEXT,
    `codigo` VARCHAR(50) UNIQUE,
    `precio` DECIMAL(10,2),
    `enlace_pago` VARCHAR(255),
    `imagen` VARCHAR(255),
    `id_relator` INT,
    `categoria` VARCHAR(100),
    `activo` BOOLEAN DEFAULT TRUE,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_relator`) REFERENCES `relatores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de formularios de contacto
CREATE TABLE IF NOT EXISTS `formularios_contacto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(50),
    `mensaje` TEXT,
    `id_curso` INT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`id_curso`) REFERENCES `cursos`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SISTEMA DE AUTENTICACIÓN Y PERMISOS
-- =============================================================================

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

-- =============================================================================
-- SISTEMA DE ANALYTICS
-- =============================================================================

-- Tabla para tracking de visitantes
CREATE TABLE IF NOT EXISTS `analytics_visitas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `pagina` VARCHAR(255) NOT NULL,
    `referer` VARCHAR(500),
    `session_id` VARCHAR(255),
    `fecha_visita` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `tiempo_en_pagina` INT DEFAULT 0,
    `dispositivo` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
    `navegador` VARCHAR(100),
    `sistema_operativo` VARCHAR(100),
    `pais` VARCHAR(100),
    `ciudad` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para tracking de sesiones
CREATE TABLE IF NOT EXISTS `analytics_sesiones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(255) UNIQUE NOT NULL,
    `ip_address` VARCHAR(45),
    `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_fin` TIMESTAMP NULL,
    `duracion` INT DEFAULT 0,
    `paginas_vistas` INT DEFAULT 0,
    `dispositivo` ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
    `navegador` VARCHAR(100),
    `sistema_operativo` VARCHAR(100),
    `pais` VARCHAR(100),
    `ciudad` VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para tracking de eventos específicos
CREATE TABLE IF NOT EXISTS `analytics_eventos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(255),
    `tipo_evento` VARCHAR(100) NOT NULL,
    `categoria` VARCHAR(100),
    `accion` VARCHAR(100),
    `etiqueta` VARCHAR(100),
    `valor` VARCHAR(255),
    `pagina` VARCHAR(255),
    `fecha_evento` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para métricas de contenido
CREATE TABLE IF NOT EXISTS `analytics_contenido` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_contenido` ENUM('curso', 'noticia', 'relator', 'servicio') NOT NULL,
    `contenido_id` INT NOT NULL,
    `vistas` INT DEFAULT 0,
    `tiempo_promedio` INT DEFAULT 0,
    `fecha_ultima_vista` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_contenido` (`tipo_contenido`, `contenido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para métricas de formularios
CREATE TABLE IF NOT EXISTS `analytics_formularios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_formulario` VARCHAR(100) NOT NULL,
    `enviados` INT DEFAULT 0,
    `exitosos` INT DEFAULT 0,
    `fallidos` INT DEFAULT 0,
    `fecha_ultimo_envio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para métricas diarias
CREATE TABLE IF NOT EXISTS `analytics_metricas_diarias` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE UNIQUE NOT NULL,
    `visitas_totales` INT DEFAULT 0,
    `usuarios_unicos` INT DEFAULT 0,
    `sesiones` INT DEFAULT 0,
    `paginas_vistas` INT DEFAULT 0,
    `tiempo_promedio_sesion` INT DEFAULT 0,
    `tasa_rebote` DECIMAL(5,2) DEFAULT 0,
    `formularios_enviados` INT DEFAULT 0,
    `cursos_vistos` INT DEFAULT 0,
    `noticias_vistas` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para configuración de analytics
CREATE TABLE IF NOT EXISTS `analytics_config` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(100) UNIQUE NOT NULL,
    `valor` TEXT,
    `descripcion` TEXT,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- DATOS INICIALES
-- =============================================================================

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
('usuarios_permisos', 'Gestionar Permisos', 'Asignar y revocar permisos a usuarios', 'usuarios'),

-- Analytics
('analytics_view', 'Ver Analytics', 'Acceso al panel de analytics y estadísticas', 'analytics'),
('analytics_export', 'Exportar Reportes', 'Exportar reportes de analytics en CSV', 'analytics'),
('analytics_config', 'Configurar Analytics', 'Configurar parámetros del sistema de analytics', 'analytics');

-- Crear usuario super admin por defecto
-- Password: admin123 (hash bcrypt)
INSERT INTO `usuarios_admin` (`username`, `email`, `password_hash`, `nombre_completo`, `rol`, `activo`) VALUES
('superadmin', 'admin@akademia360.cl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrador', 'super_admin', TRUE);

-- Asignar todos los permisos al super admin
INSERT INTO `usuarios_permisos` (`usuario_id`, `permiso_id`, `otorgado_por`)
SELECT 1, id, 1 FROM `permisos`;

-- Relatores de muestra
INSERT INTO `relatores` (`nombre`, `titulo`, `biografia`, `especializacion`, `universidad`, `email`) VALUES
('Dr. María González', 'Especialista en Recursos Humanos', 'Más de 15 años de experiencia en gestión de talento y desarrollo organizacional.', 'Recursos Humanos', 'Universidad de Chile', 'maria.gonzalez@akademia360.cl'),
('Lic. Carlos Rodríguez', 'Consultor Laboral', 'Experto en derecho laboral y relaciones laborales con amplia experiencia en empresas nacionales.', 'Derecho Laboral', 'Universidad Católica', 'carlos.rodriguez@akademia360.cl'),
('CPA Ana Martínez', 'Contadora Pública', 'Especialista en contabilidad y auditoría con más de 10 años de experiencia.', 'Contabilidad', 'Universidad de Santiago', 'ana.martinez@akademia360.cl');

-- Servicios de muestra
INSERT INTO `servicios` (`nombre`, `descripcion`, `icono`, `activo`) VALUES
('Recursos Humanos', 'Consultoría especializada en gestión de talento, desarrollo organizacional y políticas de RRHH.', 'fas fa-users', 1),
('Derecho Laboral', 'Asesoría legal en relaciones laborales, contratos de trabajo y cumplimiento normativo.', 'fas fa-gavel', 1),
('Contabilidad', 'Servicios contables, auditoría y asesoría tributaria para empresas y personas naturales.', 'fas fa-calculator', 1),
('Tributario', 'Asesoría especializada en impuestos, planificación tributaria y cumplimiento fiscal.', 'fas fa-file-invoice-dollar', 1),
('Legal', 'Servicios legales integrales para empresas, incluyendo contratos y asesoría corporativa.', 'fas fa-balance-scale', 1);

-- Cursos de muestra
INSERT INTO `cursos` (`titulo`, `descripcion_corta`, `descripcion_completa`, `objetivos`, `programa_detallado`, `codigo`, `precio`, `id_relator`, `categoria`, `activo`) VALUES
('Gestión Estratégica de RRHH', 'Aprende las mejores prácticas en gestión de recursos humanos para el éxito organizacional.', 'Curso completo sobre gestión estratégica de recursos humanos, incluyendo reclutamiento, selección, desarrollo y retención de talento.', 'Al finalizar el curso, los participantes serán capaces de diseñar e implementar estrategias efectivas de RRHH.', 'Módulo 1: Fundamentos de RRHH\nMódulo 2: Reclutamiento y Selección\nMódulo 3: Desarrollo Organizacional\nMódulo 4: Evaluación de Desempeño', 'RRHH001', 150000.00, 1, 'Recursos Humanos', 1),
('Derecho Laboral Aplicado', 'Actualización en normativa laboral y su aplicación práctica en las empresas.', 'Curso práctico sobre derecho laboral chileno, incluyendo contratos, jornadas de trabajo y terminación de relaciones laborales.', 'Comprender la normativa laboral vigente y aplicarla correctamente en situaciones empresariales.', 'Módulo 1: Marco Legal Laboral\nMódulo 2: Contratos de Trabajo\nMódulo 3: Jornadas y Remuneraciones\nMódulo 4: Terminación de Contratos', 'LAB001', 120000.00, 2, 'Derecho Laboral', 1),
('Contabilidad para No Contadores', 'Fundamentos de contabilidad para profesionales de otras áreas.', 'Curso introductorio a la contabilidad empresarial, ideal para ejecutivos y profesionales que necesitan entender estados financieros.', 'Interpretar estados financieros básicos y comprender los principios contables fundamentales.', 'Módulo 1: Principios Contables\nMódulo 2: Estados Financieros\nMódulo 3: Análisis Financiero\nMódulo 4: Presupuestos', 'CONT001', 100000.00, 3, 'Contabilidad', 1);

-- Noticias de muestra
INSERT INTO `noticias` (`titulo`, `resumen`, `contenido`, `autor`) VALUES
('Nuevas Normativas Laborales 2024', 'Actualización importante sobre los cambios en la legislación laboral que afectan a todas las empresas.', 'El Ministerio del Trabajo ha anunciado importantes cambios en la normativa laboral que entrarán en vigencia durante 2024. Estos cambios incluyen modificaciones en las jornadas de trabajo, nuevos derechos para trabajadores y ajustes en los procedimientos de terminación de contratos. Es fundamental que las empresas se preparen para estos cambios y actualicen sus políticas internas.', 'Equipo Akademia 360'),
('Tendencias en Recursos Humanos', 'Las nuevas tendencias que están transformando la gestión de talento en las organizaciones.', 'La gestión de recursos humanos está evolucionando rápidamente con la incorporación de nuevas tecnologías y metodologías. El trabajo remoto, la inteligencia artificial en reclutamiento y el enfoque en el bienestar laboral son solo algunas de las tendencias que están marcando el futuro del área.', 'Dr. María González'),
('Reformas Tributarias', 'Análisis de las principales reformas tributarias y su impacto en las empresas.', 'El gobierno ha presentado una serie de reformas tributarias que afectarán significativamente a las empresas chilenas. Es crucial que los empresarios y contadores comprendan estos cambios para adaptar sus estrategias financieras y de cumplimiento.', 'CPA Ana Martínez');

-- Contenido destacado de muestra
INSERT INTO `contenido_destacado` (`titulo`, `descripcion`, `tipo`, `id_contenido`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
('Nuevas Normativas Laborales 2024', 'Actualización importante sobre los cambios en la legislación laboral que afectan a todas las empresas.', 'noticia', 1, '2024-01-01', '2024-12-31', 1),
('Gestión Estratégica de RRHH', 'Aprende las mejores prácticas en gestión de recursos humanos para el éxito organizacional.', 'curso', 1, '2024-01-01', '2024-12-31', 1),
('Recursos Humanos', 'Consultoría especializada en gestión de talento, desarrollo organizacional y políticas de RRHH.', 'servicio', 1, '2024-01-01', '2024-12-31', 1);

-- Insertar configuración inicial de analytics
INSERT INTO `analytics_config` (`clave`, `valor`, `descripcion`) VALUES
('tracking_habilitado', '1', 'Habilitar/deshabilitar tracking de analytics'),
('retener_datos_dias', '90', 'Días para retener datos de analytics'),
('excluir_ips', '', 'IPs a excluir del tracking (separadas por coma)'),
('excluir_robots', '1', 'Excluir bots y crawlers del tracking'),
('timezone', 'America/Santiago', 'Zona horaria para analytics');

-- =============================================================================
-- ÍNDICES Y OPTIMIZACIÓN
-- =============================================================================

-- Índices para tablas principales
CREATE INDEX idx_contenido_destacado_fechas ON `contenido_destacado` (`fecha_inicio`, `fecha_fin`);
CREATE INDEX idx_contenido_destacado_activo ON `contenido_destacado` (`activo`);
CREATE INDEX idx_cursos_categoria ON `cursos` (`categoria`);
CREATE INDEX idx_cursos_activo ON `cursos` (`activo`);
CREATE INDEX idx_noticias_creacion ON `noticias` (`fecha_creacion`);
CREATE INDEX idx_formularios_contacto_creacion ON `formularios_contacto` (`fecha_creacion`);

-- Índices para sistema de autenticación
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

-- Índices para sistema de analytics
CREATE INDEX `idx_visitas_fecha` ON `analytics_visitas` (`fecha_visita`);
CREATE INDEX `idx_visitas_pagina` ON `analytics_visitas` (`pagina`);
CREATE INDEX `idx_sesiones_fecha` ON `analytics_sesiones` (`fecha_inicio`);
CREATE INDEX `idx_eventos_fecha` ON `analytics_eventos` (`fecha_evento`);
CREATE INDEX `idx_eventos_tipo` ON `analytics_eventos` (`tipo_evento`);
CREATE INDEX `idx_contenido_tipo` ON `analytics_contenido` (`tipo_contenido`);
CREATE INDEX `idx_metricas_fecha` ON `analytics_metricas_diarias` (`fecha`);

-- =============================================================================
-- VISTAS ÚTILES
-- =============================================================================

-- Vista de usuarios con permisos
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

-- =============================================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =============================================================================

-- Procedimiento para limpiar sesiones expiradas
DELIMITER //
CREATE PROCEDURE `limpiar_sesiones_expiradas`()
BEGIN
    DELETE FROM sesiones_activas 
    WHERE fecha_ultima_actividad < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    OR activa = FALSE;
END //
DELIMITER ;

-- =============================================================================
-- EVENTOS AUTOMÁTICOS
-- =============================================================================

-- Evento para limpiar sesiones automáticamente
CREATE EVENT `limpiar_sesiones_event`
ON SCHEDULE EVERY 1 HOUR
DO CALL limpiar_sesiones_expiradas();

-- =============================================================================
-- PERMISOS DE BASE DE DATOS
-- =============================================================================

-- Otorgar permisos (ajustar según tu entorno)
-- GRANT ALL PRIVILEGES ON akademia360new.* TO 'tu_usuario'@'localhost';
-- FLUSH PRIVILEGES;

-- =============================================================================
-- FINALIZACIÓN
-- =============================================================================

-- Verificar que todas las tablas se crearon correctamente
SELECT 'Base de datos Akademia 360 creada exitosamente' as mensaje;
SELECT COUNT(*) as total_tablas FROM information_schema.tables WHERE table_schema = 'akademia360new';
