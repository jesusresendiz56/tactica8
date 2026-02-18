<?php
// Controlador/campanas_respo.php
session_start();
require_once '../Modelo/SupaConexion.php';

// VERIFICAR SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    echo '<option value="" disabled>Error: Sesión no válida</option>';
    exit();
}

// VERIFICAR QUE SE RECIBIÓ EL ID DEL COORDINADOR
if (!isset($_POST['responsable_id']) || empty($_POST['responsable_id'])) {
    echo '<option value="" disabled>Error: No se recibió el ID del coordinador</option>';
    exit();
}

$responsable_id = $_POST['responsable_id'];

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();
    
    // Obtener el nombre del coordinador seleccionado
    $stmt_nombre = $conn->prepare("SELECT nombre FROM responsables WHERE id_responsable = :id");
    $stmt_nombre->bindParam(':id', $responsable_id);
    $stmt_nombre->execute();
    $coordinador = $stmt_nombre->fetch(PDO::FETCH_ASSOC);
    $nombre_coordinador = $coordinador ? $coordinador['nombre'] : 'Coordinador';

    // Consultar campañas del coordinador seleccionado
    $sql = "SELECT id_campaña, nombre_campaña 
            FROM campañas 
            WHERE responsable_id = :responsable_id 
            AND estatus IN ('activa', 'pendiente', 'en_progreso')
            ORDER BY fecha_registro DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':responsable_id', $responsable_id);
    $stmt->execute();
    
    $options = '';
    $count = 0;
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $options .= '<option value="' . $row['id_campaña'] . '">' . htmlspecialchars($row['nombre_campaña']) . '</option>';
        $count++;
    }
    
    if ($count > 0) {
        echo $options;
    } else {
        echo '<option value="" disabled>No hay campañas para ' . htmlspecialchars($nombre_coordinador) . '</option>';
    }
    
} catch (PDOException $e) {
    echo '<option value="" disabled>Error: ' . $e->getMessage() . '</option>';
}
?>