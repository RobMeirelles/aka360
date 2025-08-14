-- Sistema de Analytics para Akademia 360
-- Archivo: sistema_analytics.sql

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

-- Insertar configuración inicial
INSERT INTO `analytics_config` (`clave`, `valor`, `descripcion`) VALUES
('tracking_habilitado', '1', 'Habilitar/deshabilitar tracking de analytics'),
('retener_datos_dias', '90', 'Días para retener datos de analytics'),
('excluir_ips', '', 'IPs a excluir del tracking (separadas por coma)'),
('excluir_robots', '1', 'Excluir bots y crawlers del tracking'),
('timezone', 'America/Santiago', 'Zona horaria para analytics');

-- Crear índices para optimizar consultas
CREATE INDEX `idx_visitas_fecha` ON `analytics_visitas` (`fecha_visita`);
CREATE INDEX `idx_visitas_pagina` ON `analytics_visitas` (`pagina`);
CREATE INDEX `idx_sesiones_fecha` ON `analytics_sesiones` (`fecha_inicio`);
CREATE INDEX `idx_eventos_fecha` ON `analytics_eventos` (`fecha_evento`);
CREATE INDEX `idx_eventos_tipo` ON `analytics_eventos` (`tipo_evento`);
CREATE INDEX `idx_contenido_tipo` ON `analytics_contenido` (`tipo_contenido`);
CREATE INDEX `idx_metricas_fecha` ON `analytics_metricas_diarias` (`fecha`);
