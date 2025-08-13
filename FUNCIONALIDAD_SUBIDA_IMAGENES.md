# Funcionalidad de Subida de Imágenes

## Descripción

Se ha implementado una nueva funcionalidad que permite a los administradores subir imágenes directamente desde sus computadoras en todos los módulos de administración que manejan imágenes.

## Módulos Actualizados

### 1. Carrusel (`admin/carousel.php`)
- **Funcionalidad**: Agregar y editar contenido destacado con imágenes
- **Opciones**: Subir archivo o usar URL
- **Vista previa**: En tiempo real

### 2. Noticias (`admin/noticias.php`)
- **Funcionalidad**: Crear y editar noticias con imágenes
- **Opciones**: Subir archivo o usar URL
- **Vista previa**: En tiempo real

### 3. Cursos (`admin/cursos.php`)
- **Funcionalidad**: Crear y editar cursos con imágenes
- **Opciones**: Subir archivo o usar URL
- **Vista previa**: En tiempo real

### 4. Relatores (`admin/relatores.php`)
- **Funcionalidad**: Crear y editar relatores con fotos
- **Opciones**: Subir archivo o usar URL
- **Vista previa**: En tiempo real (formato circular)

### 5. Servicios (`admin/servicios.php`)
- **Funcionalidad**: Crear y editar servicios con imágenes
- **Opciones**: Subir archivo o usar URL
- **Vista previa**: En tiempo real

## Características Técnicas

### Formatos Soportados
- **JPG/JPEG**: Formato estándar de imagen
- **PNG**: Formato con transparencia
- **GIF**: Formato animado
- **WebP**: Formato moderno y eficiente

### Límites de Seguridad
- **Tamaño máximo**: 5MB por archivo
- **Validación**: Verificación de tipo MIME
- **Nombres únicos**: Generación automática de nombres únicos
- **Limpieza**: Eliminación automática de imágenes anteriores

### Estructura de Archivos
```
uploads/
├── .htaccess          # Configuración de acceso
└── images/            # Directorio de imágenes
    ├── timestamp_random.jpg
    ├── timestamp_random.png
    └── ...
```

## Funciones Implementadas

### `uploadImage($file, $old_image_url = null)`
Sube una imagen y maneja errores de validación.

### `processImageField($post_data, $files_data, $field_name, $old_image_url = '')`
Procesa el campo de imagen del formulario, priorizando archivos subidos sobre URLs.

### `isLocalImage($image_url)`
Verifica si una imagen es local (subida al servidor).

### `deleteLocalImage($image_url)`
Elimina una imagen local del servidor.

### `generateUniqueFilename($extension)`
Genera nombres únicos para evitar conflictos.

## Interfaz de Usuario

### Formularios Actualizados
Cada formulario ahora incluye:

1. **Campo de archivo**: Para subir desde la computadora
2. **Campo de URL**: Para usar imágenes externas
3. **Vista previa**: Muestra la imagen seleccionada
4. **Información**: Texto explicativo sobre formatos y límites

### Comportamiento
- **Prioridad**: Si se sube un archivo, se ignora la URL
- **Vista previa**: Se actualiza automáticamente al seleccionar archivo o ingresar URL
- **Validación**: Mensajes de error claros para archivos inválidos
- **Limpieza**: Eliminación automática de imágenes anteriores al actualizar

## Seguridad

### Validaciones Implementadas
- **Tipo de archivo**: Solo imágenes permitidas
- **Tamaño**: Límite de 5MB
- **Nombres seguros**: Sin caracteres especiales
- **Acceso controlado**: Configuración .htaccess

### Prevención de Ataques
- **Validación MIME**: Verificación del tipo real del archivo
- **Nombres únicos**: Evita sobrescritura de archivos
- **Directorio seguro**: Acceso restringido a uploads

## Compatibilidad

### Navegadores Soportados
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### Funcionalidades JavaScript
- **FileReader API**: Para vista previa de archivos
- **FormData**: Para envío de archivos
- **Event listeners**: Para actualización en tiempo real

## Mantenimiento

### Limpieza Automática
- Las imágenes anteriores se eliminan automáticamente al actualizar
- Solo se eliminan imágenes locales (no URLs externas)

### Monitoreo
- Verificar espacio en disco del directorio `uploads/images/`
- Revisar logs de errores de subida
- Monitorear uso de ancho de banda

## Troubleshooting

### Problemas Comunes

1. **Error de permisos**
   - Verificar permisos del directorio `uploads/images/`
   - Asegurar que el servidor web tenga permisos de escritura

2. **Archivo demasiado grande**
   - Verificar configuración `upload_max_filesize` en PHP
   - Verificar configuración `post_max_size` en PHP

3. **Vista previa no funciona**
   - Verificar que JavaScript esté habilitado
   - Revisar consola del navegador para errores

4. **Imagen no se muestra**
   - Verificar que el archivo se subió correctamente
   - Verificar permisos del archivo
   - Verificar configuración .htaccess

### Configuración PHP Recomendada
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

## Beneficios

1. **Facilidad de uso**: Los administradores pueden subir imágenes directamente
2. **Flexibilidad**: Mantiene la opción de usar URLs externas
3. **Seguridad**: Validaciones robustas y nombres únicos
4. **Experiencia**: Vista previa en tiempo real
5. **Mantenimiento**: Limpieza automática de archivos antiguos
6. **Compatibilidad**: Funciona con el sistema existente
