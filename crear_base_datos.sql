-- Script de Creación de Base de Datos para Akademia 360
-- Base de Datos: akademia360new

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS `akademia360new` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE `akademia360new`;

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

-- Insertar datos de muestra

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

-- Crear índices para mejor rendimiento
CREATE INDEX idx_contenido_destacado_fechas ON `contenido_destacado` (`fecha_inicio`, `fecha_fin`);
CREATE INDEX idx_contenido_destacado_activo ON `contenido_destacado` (`activo`);
CREATE INDEX idx_cursos_categoria ON `cursos` (`categoria`);
CREATE INDEX idx_cursos_activo ON `cursos` (`activo`);
CREATE INDEX idx_noticias_creacion ON `noticias` (`fecha_creacion`);
CREATE INDEX idx_formularios_contacto_creacion ON `formularios_contacto` (`fecha_creacion`);

-- Otorgar permisos (ajustar según tu entorno)
-- GRANT ALL PRIVILEGES ON akademia360new.* TO 'tu_usuario'@'localhost';
-- FLUSH PRIVILEGES; 