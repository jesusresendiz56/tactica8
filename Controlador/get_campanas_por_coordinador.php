<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error'=>'No autorizado','campanas'=>[],'total'=>0]);
    exit();
}

require_once '../Modelo/SupaConexion.php';

$id = $_GET['id_responsable']??null;
if(!$id || !is_numeric($id) || ($id=(int)$id)<=0) {
    echo json_encode(['error'=>'ID inválido','campanas'=>[],'total'=>0]);
    exit();
}

try {
    $conn = (new SupaConexion())->getConexion();
    $stmt = $conn->prepare("
        SELECT c.id_campaña,c.nombre_campaña,
               TO_CHAR(c.fecha_inicio,'DD/MM/YYYY') as fecha_inicio,
               TO_CHAR(c.fecha_fin,'DD/MM/YYYY') as fecha_fin,
               c.estatus,m.nombre as marca_nombre,
               tc.nombre as tipo_campaña,
               r.nombre as responsable_nombre
        FROM campañas c
        INNER JOIN marcas m ON c.marca_id=m.id_marca
        INNER JOIN tipos_campaña tc ON c.tipo_campaña_id=tc.id_tipo
        INNER JOIN responsables r ON c.responsable_id=r.id_responsable
        WHERE c.responsable_id=:id AND c.estatus IN ('activa','pendiente','en_progreso')
        ORDER BY CASE c.estatus WHEN 'en_progreso' THEN 1 WHEN 'activa' THEN 2 WHEN 'pendiente' THEN 3 ELSE 4 END,
                 c.fecha_registro DESC
    ");
    $stmt->execute([':id'=>$id]);
    $campanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'campanas'=>$campanas,'total'=>count($campanas)]);
} catch(PDOException $e) {
    error_log("Error get_campanas: ".$e->getMessage());
    echo json_encode(['error'=>'Error en BD','campanas'=>[],'total'=>0]);
}
?>