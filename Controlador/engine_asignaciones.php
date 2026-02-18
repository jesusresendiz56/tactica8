<?php
// Controlador/engine_asignaciones.php
session_start();

// VERIFICAR SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../Vista/login.php?error=no_sesion');
    exit();
}

// INCLUIR CONEXIÓN
require_once '../Modelo/SupaConexion.php';

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

// VERIFICAR QUE SE RECIBIERON DATOS POR POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Vista/asignaciones.php?error=metodo_invalido');
    exit();
}

// OBTENER DATOS DEL FORMULARIO
$id_responsable = $_POST['id_responsable'] ?? '';
$id_campana = $_POST['id_campaña'] ?? ''; // El input del form se llama id_campaña pero lo guardamos como id_campana
$id_personal = $_POST['id_personal'] ?? '';

// VALIDAR CAMPOS OBLIGATORIOS
if (empty($id_responsable) || empty($id_campana) || empty($id_personal)) {
    header('Location: ../Vista/asignaciones.php?error=campos_vacios');
    exit();
}

try {
    $conn->beginTransaction();
    
    // Verificar si el personal ya está asignado
    $sql_check = "SELECT id_asignacion FROM asignaciones 
                  WHERE id_personal = :id_personal 
                  AND estatus_asignacion = 'activa'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':id_personal', $id_personal);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        $conn->rollBack();
        header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado');
        exit();
    }
    
    // Insertar asignación - USANDO id_campana SIN Ñ
    $sql = "INSERT INTO asignaciones (
                id_personal,
                id_campaña,  -- La columna en BD tiene ñ, pero el parámetro NO
                id_responsable,
                fecha_asignacion,
                estatus_asignacion
            ) VALUES (
                :id_personal,
                :id_campana,  -- Parámetro sin ñ
                :id_responsable,
                CURRENT_DATE,
                'activa'
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_personal', $id_personal);
    $stmt->bindParam(':id_campana', $id_campana); // Sin ñ
    $stmt->bindParam(':id_responsable', $id_responsable);
    $stmt->execute();
    
    $conn->commit();
    
    header('Location: ../Vista/asignaciones.php?success=asignacion_creada');
    exit();
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    // Mostrar error detallado
    header('Location: ../Vista/asignaciones.php?error=db_error&detalle=' . urlencode($e->getMessage()));
    exit();
}
?>