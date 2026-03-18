<?php
session_start();
ob_start(); // Prevenir errores de headers

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../Vista/login.php?error=no_sesion');
    exit();
}

require_once '../Modelo/SupaConexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificar que se recibió el ID
    if (!isset($_POST['id_personal']) || empty($_POST['id_personal'])) {
        header('Location: ../Vista/personal.php?error=ID no especificado');
        exit();
    }

    $id_personal = intval($_POST['id_personal']);

    // Verificar que se recibió el archivo
    if (!isset($_FILES['contrato']) || $_FILES['contrato']['error'] !== UPLOAD_ERR_OK) {
        $error_code = $_FILES['contrato']['error'] ?? 'No file';
        header('Location: ../Vista/personal.php?error=Error al subir archivo: ' . $error_code);
        exit();
    }

    // Configurar directorio (usar ruta absoluta)
    $directorio = dirname(__DIR__) . '/contratos/';

    // Crear directorio si no existe (con permisos adecuados)
    if (!is_dir($directorio)) {
        mkdir($directorio, 0755, true); // 755 es más seguro que 777
    }

    // Verificar permisos de escritura
    if (!is_writable($directorio)) {
        // Intentar corregir permisos
        chmod($directorio, 0755);
        
        if (!is_writable($directorio)) {
            header('Location: ../Vista/personal.php?error=El directorio no tiene permisos de escritura');
            exit();
        }
    }

    // Obtener información del archivo
    $archivo_info = pathinfo($_FILES["contrato"]["name"]);
    $extension = strtolower($archivo_info['extension'] ?? '');
    
    // Limpiar el nombre del archivo (quitar caracteres especiales)
    $nombre_original = preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo_info['filename']);
    $nombreArchivo = $id_personal . "_" . time() . "_" . $nombre_original . "." . $extension;
    $rutaArchivo = $directorio . $nombreArchivo;

    // Mover el archivo
    if (move_uploaded_file($_FILES["contrato"]["tmp_name"], $rutaArchivo)) {

        try {
            $db = new SupaConexion();
            $conn = $db->getConexion();

            // Guardar ruta relativa para acceso web
            $ruta_relativa = '../contratos/' . $nombreArchivo;

            $sql = "UPDATE personal SET contrato_url = :ruta WHERE id_personal = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ruta', $ruta_relativa); // Guardar ruta relativa
            $stmt->bindParam(':id', $id_personal);
            
            if ($stmt->execute()) {
                header("Location: ../Vista/personal.php?success=1");
                exit();
            } else {
                // Si falla la BD, eliminar el archivo subido
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
                header("Location: ../Vista/personal.php?error=Error al guardar en BD");
                exit();
            }
        } catch (PDOException $e) {
            // Error de BD, eliminar archivo
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            header("Location: ../Vista/personal.php?error=Error en BD: " . $e->getMessage());
            exit();
        }
    } else {
        // Error al mover archivo
        $error = error_get_last();
        $mensaje = "Error al mover archivo. ";
        
        // Verificar permisos específicos
        if (!is_writable($directorio)) {
            $mensaje .= "Directorio sin permisos. ";
        }
        
        if (isset($error['message'])) {
            $mensaje .= "Detalle: " . $error['message'];
        }
        
        header("Location: ../Vista/personal.php?error=" . urlencode($mensaje));
        exit();
    }
}

// Si llegamos aquí, algo salió mal
header("Location: ../Vista/personal.php?error=Error desconocido");
exit();
?>