<?php
// Controlador/engine_asignaciones.php
session_start();

// Verificar sesión
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
    $id_campana = $_POST['id_campaña']; // SIN Ñ en la variable
    $id_personal = $_POST['id_personal'];
    $rol = $_POST['rol'] ?? 'personal_asignado';
    
    // VALIDACIÓN: Verificar que el personal no tenga asignaciones activas
    $check_sql = "SELECT COUNT(*) as total 
                  FROM asignaciones 
                  WHERE id_personal = :id_personal 
                  AND estatus_asignacion IN ('activa', 'en_progreso')";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([':id_personal' => $id_personal]);
    $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        // Obtener información de la campaña activa actual
        $info_sql = "SELECT c.nombre_campaña, m.nombre as marca_nombre, c.estatus
                     FROM asignaciones a
                     JOIN campañas c ON a.id_campaña = c.id_campaña
                     JOIN marcas m ON c.marca_id = m.id_marca
                     WHERE a.id_personal = :id_personal 
                     AND a.estatus_asignacion IN ('activa', 'en_progreso')
                     LIMIT 1";
        
        $info_stmt = $conn->prepare($info_sql);
        $info_stmt->execute([':id_personal' => $id_personal]);
        $info = $info_stmt->fetch(PDO::FETCH_ASSOC);
        
        $mensaje = urlencode("El personal ya tiene una asignación {$info['estatus']} en: " . $info['nombre_campaña']);
        header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado&detalle=' . $mensaje);
        exit();
    }
    
    // Verificar que la campaña exista y esté activa
    $check_campana = "SELECT c.*, m.nombre as marca_nombre 
                     FROM campañas c
                     INNER JOIN marcas m ON c.marca_id = m.id_marca
                     WHERE c.id_campaña = :id_campana"; // SIN Ñ en el parámetro
    
    $stmt_campana = $conn->prepare($check_campana);
    $stmt_campana->execute([':id_campana' => $id_campana]); // SIN Ñ
    $campana = $stmt_campana->fetch(PDO::FETCH_ASSOC);
    
    if (!$campana) {
        header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle=' . urlencode('La campaña no existe'));
        exit();
    }
    
    // Validar estatus permitidos
    if (!in_array($campana['estatus'], ['activa', 'pendiente', 'en_progreso'])) {
        header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle=' . urlencode('La campaña está ' . $campana['estatus']));
        exit();
    }
    
    // Verificar fechas
    $fecha_actual = date('Y-m-d');
    if ($campana['fecha_fin'] && $campana['fecha_fin'] < $fecha_actual) {
        header('Location: ../Vista/asignaciones.php?error=fechas_invalidas&detalle=' . urlencode('La campaña finalizó el ' . date('d/m/Y', strtotime($campana['fecha_fin']))));
        exit();
    }
    
    // Verificar que el personal esté activo
    $check_personal = "SELECT p.*, s.nombre, s.apellido_paterno 
                      FROM personal p
                      INNER JOIN solicitud s ON p.id_solicitud = s.id_solicitud
                      WHERE p.id_personal = :id_personal";
    
    $stmt_personal = $conn->prepare($check_personal);
    $stmt_personal->execute([':id_personal' => $id_personal]);
    $personal = $stmt_personal->fetch(PDO::FETCH_ASSOC);
    
    if (!$personal || $personal['estatus_laboral'] !== 'activo') {
        header('Location: ../Vista/asignaciones.php?error=personal_inactivo');
        exit();
    }
    
    // Insertar la asignación
    $sql = "INSERT INTO asignaciones (
                id_personal, 
                id_campaña, 
                id_responsable, 
                rol, 
                fecha_asignacion, 
                fecha_inicio, 
                estatus_asignacion
            ) VALUES (
                :id_personal, 
                :id_campana,  -- SIN Ñ aquí también
                :id_responsable, 
                :rol, 
                CURRENT_DATE, 
                :fecha_inicio, 
                'en_progreso'
            )";
    
    $fecha_inicio = $fecha_actual;
    if ($campana['fecha_inicio'] && $campana['fecha_inicio'] > $fecha_actual) {
        $fecha_inicio = $campana['fecha_inicio'];
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id_personal' => $id_personal,
        ':id_campana' => $id_campana, // SIN Ñ
        ':id_responsable' => $id_responsable,
        ':rol' => $rol,
        ':fecha_inicio' => $fecha_inicio
    ]);
    
    // Registrar log
    $nombre_completo = $personal['nombre'] . ' ' . $personal['apellido_paterno'];
    error_log("Asignación creada: {$nombre_completo} -> {$campana['nombre_campaña']}");
    
    header('Location: ../Vista/asignaciones.php?success=asignacion_creada');
    exit();
    
} catch (PDOException $e) {
    error_log("Error en asignación: " . $e->getMessage());
    
    if ($e->errorInfo[1] == 1062) {
        header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado');
    } else {
        $detalle = urlencode($e->getMessage());
        header('Location: ../Vista/asignaciones.php?error=db_error&detalle=' . $detalle);
    }
    exit();
}
?>