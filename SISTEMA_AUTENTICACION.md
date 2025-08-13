# Sistema de Autenticación y Permisos - Akademia 360

## 📋 Resumen

Se ha implementado un sistema completo de autenticación y permisos similar a Joomla, que permite gestionar usuarios del dashboard de administración con diferentes niveles de acceso y permisos específicos por módulo.

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### 1. `usuarios_admin`
- **Propósito**: Almacena información de usuarios del sistema
- **Campos principales**:
  - `id`: Identificador único
  - `username`: Nombre de usuario (único)
  - `email`: Email del usuario (único)
  - `password_hash`: Hash de la contraseña (bcrypt)
  - `nombre_completo`: Nombre completo del usuario
  - `rol`: Rol del usuario (super_admin, admin, editor)
  - `activo`: Estado del usuario
  - `ultimo_acceso`: Timestamp del último acceso
  - `creado_por`: ID del usuario que lo creó

#### 2. `permisos`
- **Propósito**: Define todos los permisos disponibles en el sistema
- **Campos principales**:
  - `id`: Identificador único
  - `codigo`: Código único del permiso (ej: 'cursos_view')
  - `nombre`: Nombre descriptivo del permiso
  - `descripcion`: Descripción detallada
  - `modulo`: Módulo al que pertenece (cursos, noticias, etc.)
  - `activo`: Estado del permiso

#### 3. `usuarios_permisos`
- **Propósito**: Relación muchos a muchos entre usuarios y permisos
- **Campos principales**:
  - `usuario_id`: ID del usuario
  - `permiso_id`: ID del permiso
  - `otorgado_por`: ID del usuario que otorgó el permiso
  - `fecha_otorgado`: Fecha de asignación

#### 4. `sesiones_activas`
- **Propósito**: Control de sesiones activas para seguridad
- **Campos principales**:
  - `usuario_id`: ID del usuario
  - `session_id`: ID de sesión único
  - `ip_address`: Dirección IP del usuario
  - `user_agent`: Navegador del usuario
  - `activa`: Estado de la sesión

#### 5. `logs_actividad`
- **Propósito**: Registro de todas las actividades del sistema
- **Campos principales**:
  - `usuario_id`: ID del usuario que realizó la acción
  - `accion`: Tipo de acción (login, logout, create, update, delete)
  - `modulo`: Módulo afectado
  - `descripcion`: Descripción de la acción
  - `ip_address`: IP del usuario

## 🔐 Sistema de Permisos

### Tipos de Permisos Implementados

#### Dashboard
- `dashboard_view`: Ver el panel principal

#### Cursos
- `cursos_view`: Ver lista de cursos
- `cursos_create`: Crear nuevos cursos
- `cursos_edit`: Editar cursos existentes
- `cursos_delete`: Eliminar cursos

#### Noticias
- `noticias_view`: Ver lista de noticias
- `noticias_create`: Crear nuevas noticias
- `noticias_edit`: Editar noticias existentes
- `noticias_delete`: Eliminar noticias

#### Relatores
- `relatores_view`: Ver lista de relatores
- `relatores_create`: Crear nuevos relatores
- `relatores_edit`: Editar relatores existentes
- `relatores_delete`: Eliminar relatores

#### Servicios
- `servicios_view`: Ver lista de servicios
- `servicios_create`: Crear nuevos servicios
- `servicios_edit`: Editar servicios existentes
- `servicios_delete`: Eliminar servicios

#### Carrusel
- `carousel_view`: Ver contenido del carrusel
- `carousel_create`: Agregar contenido al carrusel
- `carousel_edit`: Modificar contenido del carrusel
- `carousel_delete`: Eliminar contenido del carrusel

#### Contactos
- `contactos_view`: Ver formularios de contacto
- `contactos_delete`: Eliminar formularios de contacto

#### Administración de Usuarios (Solo Super Admin)
- `usuarios_view`: Ver lista de usuarios
- `usuarios_create`: Crear nuevos usuarios
- `usuarios_edit`: Editar usuarios existentes
- `usuarios_delete`: Eliminar usuarios
- `usuarios_permisos`: Gestionar permisos de usuarios

## 👥 Roles del Sistema

### 1. Super Admin
- **Acceso**: Todos los módulos y permisos
- **Capacidades**:
  - Crear, editar y eliminar usuarios
  - Asignar y revocar permisos
  - Acceso completo a todas las funcionalidades
  - Ver logs de actividad

### 2. Admin
- **Acceso**: Módulos según permisos asignados
- **Capacidades**:
  - Gestionar contenido según permisos
  - No puede gestionar usuarios
  - Acceso limitado a funcionalidades

### 3. Editor
- **Acceso**: Módulos específicos según permisos
- **Capacidades**:
  - Editar contenido según permisos
  - Acceso muy limitado
  - Ideal para editores de contenido específico

## 🚀 Instalación y Configuración

### 1. Ejecutar Script SQL
```sql
-- Ejecutar el archivo sistema_autenticacion.sql en tu base de datos
-- Esto creará todas las tablas y datos iniciales
```

### 2. Usuario por Defecto
- **Usuario**: `superadmin`
- **Contraseña**: `admin123`
- **Rol**: Super Admin
- **Permisos**: Todos los permisos del sistema

### 3. Archivos Requeridos
- `includes/auth_functions.php`: Funciones de autenticación
- `admin/login.php`: Página de login
- `admin/administracion_de_cuentas.php`: Gestión de usuarios
- `admin/logout.php`: Cerrar sesión

## 📝 Uso del Sistema

### Autenticación
```php
// Verificar si el usuario está autenticado
if (isAuthenticated()) {
    // Usuario autenticado
}

// Requerir autenticación
requireAuth();

// Obtener información del usuario actual
$user = getCurrentUser();
```

### Verificación de Permisos
```php
// Verificar permiso específico
if (hasPermission('cursos_create')) {
    // Usuario puede crear cursos
}

// Verificar permiso de módulo
if (hasModulePermission('cursos')) {
    // Usuario tiene algún permiso del módulo cursos
}

// Requerir permiso específico
requirePermission('cursos_create');

// Requerir permiso de módulo
requireModulePermission('cursos');
```

### Verificación de Roles
```php
// Verificar si es super admin
if (isSuperAdmin()) {
    // Usuario es super admin
}

// Verificar si es admin
if (isAdmin()) {
    // Usuario es admin o super admin
}

// Requerir rol de super admin
requireSuperAdmin();
```

### Logging de Actividad
```php
// Registrar actividad
logActivity($user_id, 'create', 'cursos', 'Creó curso: Introducción a PHP');
```

## 🛡️ Características de Seguridad

### 1. Contraseñas
- Hash bcrypt con costo 12
- Validación de complejidad (mínimo 8 caracteres, letra y número)
- Generación de contraseñas aleatorias

### 2. Sesiones
- Control de sesiones en base de datos
- Limpieza automática de sesiones expiradas
- Registro de IP y User Agent

### 3. Logs
- Registro completo de todas las actividades
- Trazabilidad de acciones por usuario
- Información de IP y timestamp

### 4. Validaciones
- Escape de datos SQL
- Validación de entrada
- Prevención de inyección SQL

## 📊 Gestión de Usuarios

### Crear Usuario
1. Acceder a "Administración de Cuentas"
2. Hacer clic en "Agregar Usuario"
3. Completar formulario con datos del usuario
4. Seleccionar permisos específicos
5. Guardar usuario

### Editar Usuario
1. Seleccionar usuario de la lista
2. Hacer clic en "Editar"
3. Modificar datos necesarios
4. Ajustar permisos
5. Guardar cambios

### Cambiar Contraseña
1. Seleccionar usuario
2. Hacer clic en icono de llave
3. Ingresar nueva contraseña
4. Confirmar contraseña
5. Guardar cambios

### Eliminar Usuario
1. Seleccionar usuario
2. Hacer clic en icono de eliminar
3. Confirmar eliminación
4. Usuario eliminado del sistema

## 🔧 Personalización

### Agregar Nuevos Permisos
1. Insertar en tabla `permisos`
2. Actualizar funciones de verificación
3. Agregar al menú de navegación

### Crear Nuevos Roles
1. Modificar ENUM en tabla `usuarios_admin`
2. Actualizar funciones de verificación
3. Ajustar lógica de permisos

### Modificar Validaciones
1. Editar función `validatePassword()`
2. Ajustar reglas de validación
3. Actualizar mensajes de error

## 📱 Interfaz de Usuario

### Login
- Diseño moderno y responsivo
- Validación en tiempo real
- Toggle de visibilidad de contraseña
- Mensajes de error claros

### Dashboard
- Menú dinámico según permisos
- Información del usuario actual
- Acceso rápido a módulos permitidos

### Gestión de Usuarios
- Tabla con información completa
- Filtros y búsqueda
- Acciones en lote
- Modal para cambiar contraseña

## 🚨 Consideraciones Importantes

### Seguridad
- Cambiar contraseña del super admin por defecto
- Revisar logs regularmente
- Mantener sesiones seguras
- Validar todas las entradas

### Mantenimiento
- Limpiar logs antiguos periódicamente
- Revisar sesiones expiradas
- Actualizar permisos según necesidades
- Hacer backup de la base de datos

### Escalabilidad
- El sistema está diseñado para crecer
- Fácil agregar nuevos módulos
- Permisos granulares
- Roles flexibles

## 📞 Soporte

Para dudas o problemas con el sistema de autenticación:
1. Revisar logs de actividad
2. Verificar permisos del usuario
3. Comprobar estado de la sesión
4. Contactar al administrador del sistema
