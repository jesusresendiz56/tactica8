<?php
session_start();
require_once '../Modelo/SupaConexion.php';

// Configuración 
$SUPABASE_URL = 'https://fbhirrxvzubnwnivrarl.supabase.co';  // ← la URL
$SUPABASE_KEY = 'anon public: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZiaGlycnh2enVibnduaXZyYXJsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzAxNDM5MzAsImV4cCI6MjA4NTcxOTkzMH0.-9BDLJn1rlpmkAqtIGhC31vbLyGT5pCBU2r1t1reWb4';  // ← APi KEY
$BUCKET_NAME = 'contratos';  // Nombre del bucket en Supabase

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['contrato'])) {
    
    $id_personal = $_POST['id_personal'] ?? null;
    
    if (!$id_personal) {
        $_SESSION['error'] = "ID de personal no proporcionado";
        header("Location: ../Vista/personal.php");
        exit();
    }
    
    $archivo = $_FILES['contrato'];
    
    // Validar archivo
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Error al subir el archivo";
        header("Location: ../Vista/personal.php");
        exit();
    }
    
    // Validar tipo de archivo
    $tipos_permitidos = [
        'application/pdf', 
        'image/jpeg', 
        'image/jpg', 
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($archivo['type'], $tipos_permitidos)) {
        $_SESSION['error'] = "Tipo de archivo no permitido. Solo PDF, JPG, PNG, DOC";
        header("Location: ../Vista/personal.php");
        exit();
    }
    
    // Validar tamaño (10MB máximo)
    if ($archivo['size'] > 10 * 1024 * 1024) {
        $_SESSION['error'] = "El archivo es demasiado grande (máximo 10MB)";
        header("Location: ../Vista/personal.php");
        exit();
    }
    
    try {
        // Generar nombre único
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombreArchivo = "personal_{$id_personal}_" . time() . "." . $extension;
        
        // Leer contenido del archivo
        $fileContent = file_get_contents($archivo['tmp_name']);
        
        // Subir a Supabase
        $url_subida = $SUPABASE_URL . '/storage/v1/object/' . $BUCKET_NAME . '/' . $nombreArchivo;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_subida);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $SUPABASE_KEY,
            'apiKey: ' . $SUPABASE_KEY,
            'Content-Type: ' . $archivo['type']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 || $httpCode === 201) {
            // URL pública del archivo
            $url_publica = $SUPABASE_URL . '/storage/v1/object/public/' . $BUCKET_NAME . '/' . $nombreArchivo;
            
            // Conectar a BD y actualizar
            $db = new SupaConexion();
            $conn = $db->getConexion();
            
            $sql = "UPDATE personal SET contrato_url = :ruta WHERE id_personal = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ruta', $url_publica);
            $stmt->bindParam(':id', $id_personal);
            $stmt->execute();
            
            $_SESSION['mensaje'] = "✅ Contrato subido correctamente";
            $_SESSION['tipo'] = "success";
        } else {
            $_SESSION['error'] = "Error al subir a Supabase. Código: " . $httpCode;
            error_log("Supabase error: " . $response);
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    // Redireccionar
    header("Location: ../Vista/detalle_personal.php?id=" . $id_personal);
    exit();
}

// Si no es POST
header("Location: ../Vista/personal.php");
exit();
?>