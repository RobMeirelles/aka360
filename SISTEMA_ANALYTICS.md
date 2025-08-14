# Sistema de Analytics - Akademia 360

## 📊 Resumen

Se ha implementado un sistema completo de analytics para el dashboard de administrador de Akademia 360, que permite rastrear y analizar el comportamiento de los visitantes del sitio web.

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### 1. `analytics_visitas`
- **Propósito**: Registra cada visita a una página
- **Campos principales**:
  - `id`: Identificador único
  - `ip_address`: Dirección IP del visitante
  - `user_agent`: Navegador y sistema operativo
  - `pagina`: URL de la página visitada
  - `referer`: Página de origen
  - `session_id`: ID de sesión
  - `dispositivo`: Tipo de dispositivo (desktop, mobile, tablet)
  - `navegador`: Navegador detectado
  - `sistema_operativo`: Sistema operativo detectado
  - `fecha_visita`: Timestamp de la visita

#### 2. `analytics_sesiones`
- **Propósito**: Control de sesiones de usuarios
- **Campos principales**:
  - `session_id`: ID único de sesión
  - `ip_address`: IP del usuario
  - `fecha_inicio`: Inicio de la sesión
  - `fecha_fin`: Fin de la sesión
  - `duracion`: Duración en segundos
  - `paginas_vistas`: Número de páginas vistas
  - `dispositivo`, `navegador`, `sistema_operativo`: Información del dispositivo

#### 3. `analytics_eventos`
- **Propósito**: Eventos específicos del usuario
- **Campos principales**:
  - `tipo_evento`: Tipo de evento (page_view, link_click, form_submit, etc.)
  - `categoria`: Categoría del evento (navigation, engagement, business)
  - `accion`: Acción específica (view, click, submit)
  - `etiqueta`: Etiqueta descriptiva
  - `valor`: Valor adicional del evento
  - `pagina`: Página donde ocurrió el evento

#### 4. `analytics_contenido`
- **Propósito**: Métricas de contenido específico
- **Campos principales**:
  - `tipo_contenido`: Tipo (curso, noticia, relator, servicio)
  - `contenido_id`: ID del contenido
  - `vistas`: Número de vistas
  - `tiempo_promedio`: Tiempo promedio en el contenido
  - `fecha_ultima_vista`: Última vez que se vio

#### 5. `analytics_formularios`
- **Propósito**: Métricas de formularios
- **Campos principales**:
  - `tipo_formulario`: Tipo de formulario
  - `enviados`: Total de envíos
  - `exitosos`: Envíos exitosos
  - `fallidos`: Envíos fallidos

#### 6. `analytics_metricas_diarias`
- **Propósito**: Métricas agregadas por día
- **Campos principales**:
  - `fecha`: Fecha de las métricas
  - `visitas_totales`: Visitas del día
  - `usuarios_unicos`: Usuarios únicos
  - `sesiones`: Número de sesiones
  - `tasa_rebote`: Porcentaje de rebote

#### 7. `analytics_config`
- **Propósito**: Configuración del sistema
- **Campos principales**:
  - `clave`: Clave de configuración
  - `valor`: Valor de la configuración
  - `descripcion`: Descripción de la configuración

## 🔧 Funcionalidades Implementadas

### 1. Tracking Automático
- **Page Views**: Registro automático de cada visita a página
- **Sesiones**: Control de sesiones de usuario
- **Dispositivos**: Detección automática de dispositivo, navegador y SO
- **Bots**: Filtrado automático de bots y crawlers
- **IPs**: Sistema de exclusión de IPs específicas

### 2. Eventos Personalizados
- **Navegación**: Clicks en enlaces importantes
- **Formularios**: Envíos de formularios
- **Contenido**: Visualización de cursos, noticias, etc.
- **Engagement**: Tiempo en página, scroll, clicks importantes
- **Conversiones**: Eventos de negocio

### 3. Dashboard de Analytics
- **Estadísticas Generales**: Visitas, usuarios únicos, sesiones
- **Gráficos Interactivos**: Visitas por día, distribución de dispositivos
- **Páginas Populares**: Top páginas más visitadas
- **Contenido Más Visto**: Métricas de contenido específico
- **Filtros**: Por período y tipo de contenido
- **Exportación**: Reportes en CSV

### 4. Integración con el Sistema
- **Permisos**: Sistema de permisos integrado
- **Menú**: Acceso desde el menú principal
- **Dashboard**: Métricas básicas en el dashboard principal
- **Configuración**: Panel de configuración

## 📱 Frontend Tracking

### JavaScript Analytics (`js/analytics.js`)
- **Inicialización Automática**: Se carga automáticamente en todas las páginas
- **Session Management**: Gestión de sesiones en localStorage
- **Event Tracking**: Tracking automático de eventos importantes
- **Intersection Observer**: Tracking de visualización de contenido
- **Performance**: Tracking de tiempo en página y scroll

### Eventos Automáticos
```javascript
// Page views
trackPageView()

// Link clicks
trackEvent('link_click', 'navigation', 'click', 'text', 'url')

// Form submissions
trackEvent('form_submit', 'form', 'submit', 'form_id', 'action')

// Content views
trackEvent('content_view', 'curso', 'view', 'curso', 'id')

// Engagement
trackEvent('scroll', 'engagement', 'scroll', 'scroll_percent', '50')
```

## 🎯 Métricas Disponibles

### Métricas Generales
- **Visitas Totales**: Número total de visitas
- **Usuarios Únicos**: Visitantes únicos por IP
- **Sesiones**: Número de sesiones iniciadas
- **Páginas por Sesión**: Promedio de páginas por sesión
- **Tiempo en Sitio**: Tiempo promedio de sesión

### Métricas de Contenido
- **Cursos Más Vistos**: Top cursos por vistas
- **Noticias Populares**: Noticias más leídas
- **Relatores Destacados**: Relatores más visitados
- **Servicios Interesantes**: Servicios más consultados

### Métricas de Dispositivos
- **Distribución por Dispositivo**: Desktop, Mobile, Tablet
- **Navegadores**: Chrome, Firefox, Safari, Edge, etc.
- **Sistemas Operativos**: Windows, macOS, Linux, Android, iOS

### Métricas de Engagement
- **Tasa de Rebote**: Porcentaje de visitas de una sola página
- **Tiempo en Página**: Tiempo promedio por página
- **Scroll Depth**: Profundidad de scroll
- **Formularios**: Tasa de envío de formularios

## 🔐 Seguridad y Privacidad

### Protección de Datos
- **Exclusión de Bots**: Filtrado automático de crawlers
- **IPs Excluidas**: Sistema para excluir IPs específicas
- **Configuración**: Panel para gestionar exclusiones
- **Retención**: Configuración de días de retención de datos

### Cumplimiento
- **GDPR Ready**: Sistema preparado para GDPR
- **Anonimización**: IPs no se almacenan en texto plano
- **Consentimiento**: Sistema de consentimiento integrado
- **Eliminación**: Proceso de eliminación de datos

## 📊 Reportes y Exportación

### Tipos de Reportes
1. **Reporte General**: Métricas básicas del sitio
2. **Reporte de Contenido**: Análisis de contenido específico
3. **Reporte de Dispositivos**: Análisis de dispositivos
4. **Reporte de Engagement**: Métricas de engagement
5. **Reporte Personalizado**: Por período y filtros

### Formatos de Exportación
- **CSV**: Para análisis en Excel/Google Sheets
- **JSON**: Para integración con otras herramientas
- **PDF**: Para presentaciones (futuro)

## 🚀 Instalación y Configuración

### 1. Crear Tablas
```sql
-- Ejecutar el archivo sistema_analytics.sql
source sistema_analytics.sql;
```

### 2. Configurar Permisos
```sql
-- Asignar permisos de analytics al super admin
INSERT INTO usuarios_permisos (usuario_id, permiso_id, otorgado_por)
SELECT 1, id, 1 FROM permisos WHERE modulo = 'analytics';
```

### 3. Incluir JavaScript
```html
<!-- Agregar en el head de todas las páginas -->
<script src="js/analytics.js"></script>
```

### 4. Configurar Tracking
```php
// En cada página del frontend
<?php
require_once 'includes/analytics_functions.php';
trackPageView();
?>
```

## 🔧 Configuración Avanzada

### Configuración de Analytics
```php
// Habilitar/deshabilitar tracking
updateAnalyticsConfig('tracking_habilitado', '1');

// Configurar retención de datos
updateAnalyticsConfig('retener_datos_dias', '90');

// Excluir IPs
updateAnalyticsConfig('excluir_ips', '127.0.0.1,192.168.1.1');
```

### Eventos Personalizados
```javascript
// Tracking manual de eventos
trackEvent('custom_event', 'category', 'action', 'label', 'value');

// Tracking de conversiones
trackConversion('form_submit', 'contact_form');

// Tracking de errores
trackError('Error message', 'page_context');
```

## 📈 Futuras Mejoras

### Funcionalidades Planificadas
1. **Heatmaps**: Mapas de calor de páginas
2. **Funnels**: Análisis de embudos de conversión
3. **A/B Testing**: Sistema de pruebas A/B
4. **Real-time Analytics**: Métricas en tiempo real
5. **Email Reports**: Reportes automáticos por email
6. **API REST**: API para integración externa
7. **Machine Learning**: Predicciones y insights automáticos

### Integraciones Futuras
- **Google Analytics**: Sincronización con GA
- **Facebook Pixel**: Integración con Facebook Ads
- **Google Ads**: Tracking de conversiones
- **CRM**: Integración con sistemas CRM
- **Email Marketing**: Tracking de campañas de email

## 🛠️ Mantenimiento

### Limpieza Automática
```sql
-- Limpiar datos antiguos automáticamente
CALL limpiar_datos_analytics_antiguos();
```

### Backup de Datos
```sql
-- Backup de tablas de analytics
mysqldump -u usuario -p base_datos analytics_* > analytics_backup.sql
```

### Monitoreo
- **Rendimiento**: Monitorear impacto en rendimiento
- **Espacio**: Controlar uso de espacio en base de datos
- **Errores**: Monitorear errores de tracking
- **Privacidad**: Auditorías regulares de privacidad

## 📞 Soporte

Para soporte técnico o consultas sobre el sistema de analytics:

- **Documentación**: Este archivo y comentarios en el código
- **Logs**: Revisar logs de errores en el servidor
- **Base de Datos**: Verificar integridad de las tablas
- **Configuración**: Revisar configuración en `analytics_config`

---

**Sistema de Analytics - Akademia 360**  
*Versión 1.0 - Implementado con PHP, MySQL y JavaScript*
