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
$db = new SupaConexion();
$conn = $db->getConexion();

// VERIFICAR QUE SE RECIBIERON DATOS POR POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Vista/asignaciones.php?error=metodo_invalido');
    exit();
}

// OBTENER DATOS DEL FORMULARIO
$id_responsable = $_POST['id_responsable'] ?? '';
$id_campaña = $_POST['id_campaña'] ?? '';
$id_personal = $_POST['id_personal'] ?? '';

// VALIDAR CAMPOS OBLIGATORIOS
if (empty($id_responsable) || empty($id_campaña) || empty($id_personal)) {
    header('Location: ../Vista/asignaciones.php?error=campos_vacios');
    exit();
}

try {
    // 1. VERIFICAR SI EL PERSONAL YA ESTÁ ASIGNADO A UNA CAMPAÑA ACTIVA
    $sql_check = "SELECT id_asignacion FROM asignaciones 
                  WHERE id_personal = :id_personal 
                  AND estatus_asignacion = 'activa'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':id_personal', $id_personal);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado');
        exit();
    }
    
    // 2. INSERTAR ASIGNACIÓN
    $sql = "INSERT INTO asignaciones (
                id_personal,
                id_campaña,
                id_responsable,
                fecha_asignacion,
                estatus_asignacion
            ) VALUES (
                :id_personal,
                :id_campaña,
                :id_responsable,
                CURRENT_DATE,
                'activa'
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_personal', $id_personal);
    $stmt->bindParam(':id_campaña', $id_campaña);
    $stmt->bindParam(':id_responsable', $id_responsable);
    
    if ($stmt->execute()) {
        header('Location: ../Vista/asignaciones.php?success=asignacion_creada');
    } else {
        header('Location: ../Vista/asignaciones.php?error=error_guardar');
    }
    
} catch (PDOException $e) {
    header('Location: ../Vista/asignaciones.php?error=db_error');
    exit();
}
?>