<?php
// Controlador/get_campanas_por_coordinador.php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../Modelo/SupaConexion.php';

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();
    
    // Verificar que existe el parámetro
    if (!isset($_GET['id_responsable']) || $_GET['id_responsable'] === '') {
        echo json_encode([
            'error' => 'No se recibió el ID del coordinador',
            'campanas' => [],
            'total' => 0
        ]);
        exit();
    }
    
    $id_responsable = $_GET['id_responsable'];
    
    // Validar que sea numérico
    if (!is_numeric($id_responsable)) {
        echo json_encode([
            'error' => 'El ID del coordinador debe ser un número',
            'campanas' => [],
            'total' => 0
        ]);
        exit();
    }
    
    $id_responsable = intval($id_responsable);
    
    if ($id_responsable <= 0) {
        echo json_encode([
            'error' => 'ID de coordinador inválido',
            'campanas' => [],
            'total' => 0
        ]);
        exit();
    }
    
    // Obtener campañas del coordinador seleccionado
    $sql = "
        SELECT 
            c.id_campaña, 
            c.nombre_campaña,
            c.fecha_inicio,
            c.fecha_fin,
            c.estatus,
            m.nombre AS marca_nombre,
            tc.nombre AS tipo_campaña,
            r.nombre AS responsable_nombre
        FROM campañas c
        INNER JOIN marcas m ON c.marca_id = m.id_marca
        INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
        INNER JOIN responsables r ON c.responsable_id = r.id_responsable
        WHERE c.responsable_id = :id_responsable
        AND c.estatus IN ('activa', 'pendiente', 'en_progreso')
        ORDER BY 
            CASE 
                WHEN c.estatus = 'en_progreso' THEN 1
                WHEN c.estatus = 'activa' THEN 2
                WHEN c.estatus = 'pendiente' THEN 3
                ELSE 4
            END,
            c.fecha_registro DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_responsable' => $id_responsable]);
    $campanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear fechas para mostrarlas bien en el select
    foreach ($campanas as &$campana) {
        if ($campana['fecha_inicio']) {
            $campana['fecha_inicio'] = date('d/m/Y', strtotime($campana['fecha_inicio']));
        }
        if ($campana['fecha_fin']) {
            $campana['fecha_fin'] = date('d/m/Y', strtotime($campana['fecha_fin']));
        }
    }
    
    echo json_encode([
        'success' => true,
        'campanas' => $campanas,
        'total' => count($campanas)
    ]);
    
} catch (PDOException $e) {
    error_log("Error en get_campanas_por_coordinador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en la base de datos: ' . $e->getMessage(),
        'campanas' => [],
        'total' => 0
    ]);
}
?>