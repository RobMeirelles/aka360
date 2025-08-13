<?php
/**
 * Funciones para manejo de subida de imágenes
 */

// Configuración de subida de archivos
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

/**
 * Sube una imagen desde el formulario
 * @param array $file El archivo de $_FILES
 * @param string $old_image_url URL de imagen anterior (opcional)
 * @return array ['success' => bool, 'url' => string, 'error' => string]
 */
function uploadImage($file, $old_image_url = null) {
    // Verificar si se proporcionó un archivo
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'url' => '', 'error' => 'No se seleccionó ningún archivo'];
    }

    // Verificar errores de subida
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = getUploadErrorMessage($file['error']);
        return ['success' => false, 'url' => '', 'error' => $error_message];
    }

    // Verificar tamaño del archivo
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'url' => '', 'error' => 'El archivo es demasiado grande. Máximo 5MB.'];
    }

    // Verificar extensión
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'url' => '', 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF y WebP.'];
    }

    // Crear directorio si no existe
    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true)) {
            return ['success' => false, 'url' => '', 'error' => 'Error al crear directorio de uploads'];
        }
    }

    // Generar nombre único para el archivo
    $unique_filename = generateUniqueFilename($file_extension);
    $upload_path = UPLOAD_DIR . $unique_filename;

    // Mover archivo subido
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => false, 'url' => '', 'error' => 'Error al mover el archivo subido'];
    }

    // Generar URL relativa
    $image_url = 'uploads/images/' . $unique_filename;

    // Eliminar imagen anterior si existe y es local
    if ($old_image_url && isLocalImage($old_image_url)) {
        deleteLocalImage($old_image_url);
    }

    return ['success' => true, 'url' => $image_url, 'error' => ''];
}

/**
 * Genera un nombre único para el archivo
 * @param string $extension Extensión del archivo
 * @return string Nombre único del archivo
 */
function generateUniqueFilename($extension) {
    $timestamp = time();
    $random_string = bin2hex(random_bytes(8));
    return $timestamp . '_' . $random_string . '.' . $extension;
}

/**
 * Obtiene mensaje de error de subida
 * @param int $error_code Código de error
 * @return string Mensaje de error
 */
function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el servidor';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo excede el tamaño máximo permitido por el formulario';
        case UPLOAD_ERR_PARTIAL:
            return 'El archivo se subió parcialmente';
        case UPLOAD_ERR_NO_FILE:
            return 'No se subió ningún archivo';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Falta el directorio temporal';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Error al escribir el archivo en disco';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extensión de PHP detuvo la subida del archivo';
        default:
            return 'Error desconocido al subir el archivo';
    }
}

/**
 * Verifica si una imagen es local (no es una URL externa)
 * @param string $image_url URL de la imagen
 * @return bool True si es local
 */
function isLocalImage($image_url) {
    if (empty($image_url)) return false;
    
    // Si es una URL relativa que comienza con uploads/
    if (strpos($image_url, 'uploads/') === 0) {
        return true;
    }
    
    // Si es una ruta absoluta local
    if (strpos($image_url, 'http') !== 0 && file_exists(dirname(__DIR__) . '/' . $image_url)) {
        return true;
    }
    
    return false;
}

/**
 * Elimina una imagen local
 * @param string $image_url URL de la imagen
 * @return bool True si se eliminó correctamente
 */
function deleteLocalImage($image_url) {
    if (!isLocalImage($image_url)) {
        return false;
    }
    
    $file_path = dirname(__DIR__) . '/' . $image_url;
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    
    return false;
}

/**
 * Procesa la imagen del formulario (URL o archivo subido)
 * @param array $post_data Datos del POST
 * @param array $files_data Datos de $_FILES
 * @param string $field_name Nombre del campo de imagen
 * @param string $old_image_url URL de imagen anterior
 * @return string URL final de la imagen
 */
function processImageField($post_data, $files_data, $field_name, $old_image_url = '') {
    // Si se subió un archivo, procesarlo
    if (isset($files_data[$field_name]) && $files_data[$field_name]['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadImage($files_data[$field_name], $old_image_url);
        if ($upload_result['success']) {
            return $upload_result['url'];
        } else {
            // Si falla la subida, mantener la imagen anterior
            return $old_image_url;
        }
    }
    
    // Si no se subió archivo, usar la URL del formulario
    $image_url = trim($post_data[$field_name . '_url'] ?? '');
    if (!empty($image_url)) {
        // Si hay una nueva URL y la anterior era local, eliminar la anterior
        if (!empty($old_image_url) && isLocalImage($old_image_url) && $image_url !== $old_image_url) {
            deleteLocalImage($old_image_url);
        }
        return $image_url;
    }
    
    // Si no hay nueva imagen ni URL, mantener la anterior
    return $old_image_url;
}

/**
 * Valida una URL de imagen
 * @param string $url URL a validar
 * @return bool True si es válida
 */
function isValidImageUrl($url) {
    if (empty($url)) return false;
    
    // Verificar formato de URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Verificar extensión de imagen
    $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    return in_array($extension, ALLOWED_EXTENSIONS);
}

/**
 * Obtiene información de una imagen
 * @param string $image_path Ruta de la imagen
 * @return array Información de la imagen
 */
function getImageInfo($image_path) {
    $full_path = dirname(__DIR__) . '/' . $image_path;
    
    if (!file_exists($full_path)) {
        return null;
    }
    
    $image_info = getimagesize($full_path);
    if ($image_info === false) {
        return null;
    }
    
    return [
        'width' => $image_info[0],
        'height' => $image_info[1],
        'type' => $image_info[2],
        'mime' => $image_info['mime'],
        'size' => filesize($full_path)
    ];
}
