<?php
// Controlador/engine_asignaciones.php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../Vista/login.php?error=no_sesion');
    exit();
}

require_once '../Modelo/SupaConexion.php';

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();
    
    // Validar campos
    if (empty($_POST['id_responsable']) || empty($_POST['id_campaña']) || empty($_POST['id_personal'])) {
        header('Location: ../Vista/asignaciones.php?error=campos_vacios');
        exit();
    }
    
    $id_responsable = $_POST['id_responsable'];
    $id_campana = $_POST['id_campaña']; // SIN Ñ
    $id_personal = $_POST['id_personal'];
    $rol = $_POST['rol'] ?? 'personal_asignado';
    
    // Verificar personal no tenga asignación activa
    $check = $conn->prepare("SELECT COUNT(*) FROM asignaciones WHERE id_personal = ? AND estatus_asignacion IN ('activa', 'en_progreso')");
    $check->execute([$id_personal]);
    
    if ($check->fetchColumn() > 0) {
        header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado');
        exit();
    }
    
    // Verificar campaña
    $campana = $conn->prepare("SELECT * FROM campañas WHERE id_campaña = ?");
    $campana->execute([$id_campana]);
    $c = $campana->fetch(PDO::FETCH_ASSOC);
    
    if (!$c) {
        header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle=' . urlencode('La campaña no existe'));
        exit();
    }
    
    // Verificar que la campaña pertenezca al coordinador seleccionado
    if ($c['responsable_id'] != $id_responsable) {
        header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle=' . urlencode('La campaña no pertenece al coordinador seleccionado'));
        exit();
    }
    
    // Verificar estatus
    if (!in_array($c['estatus'], ['activa', 'pendiente', 'en_progreso'])) {
        header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle=' . urlencode('La campaña está ' . $c['estatus']));
        exit();
    }
    
    // Verificar fechas
    if ($c['fecha_fin'] && $c['fecha_fin'] < date('Y-m-d')) {
        header('Location: ../Vista/asignaciones.php?error=fechas_invalidas');
        exit();
    }
    
    // Verificar personal activo
    $personal = $conn->prepare("SELECT estatus_laboral FROM personal WHERE id_personal = ?");
    $personal->execute([$id_personal]);
    if ($personal->fetchColumn() !== 'activo') {
        header('Location: ../Vista/asignaciones.php?error=personal_inactivo');
        exit();
    }
    
    // Insertar asignación
    $sql = "INSERT INTO asignaciones (id_personal, id_campaña, id_responsable, rol, fecha_asignacion, fecha_inicio, estatus_asignacion) 
            VALUES (?, ?, ?, ?, CURRENT_DATE, ?, 'en_progreso')";
    
    $fecha_inicio = ($c['fecha_inicio'] && $c['fecha_inicio'] > date('Y-m-d')) ? $c['fecha_inicio'] : date('Y-m-d');
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_personal, $id_campana, $id_responsable, $rol, $fecha_inicio]);
    
    header('Location: ../Vista/asignaciones.php?success=asignacion_creada');
    
} catch (PDOException $e) {
    error_log("Error en asignación: " . $e->getMessage());
    header('Location: ../Vista/asignaciones.php?error=db_error&detalle=' . urlencode($e->getMessage()));
}
?>