<?php
session_start();
require_once '../Modelo/SupaConexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Vista/campañas.php");
    exit;
}

$marca_id        = $_POST['marca_id'] ?? null;
$tipo_id         = $_POST['tipo_campaña_id'] ?? null;
$responsable_id  = $_POST['responsable_id'] ?? null;
$nombre          = trim($_POST['nombre_campaña'] ?? '');
$estatus         = $_POST['estatus'] ?? 'pendiente';

if (!$marca_id || !$tipo_id || !$responsable_id || $nombre === '') {
    header("Location: ../Vista/campañas.php?error=campos");
    exit;
}

$estatus_validos = ['pendiente','en_progreso','completada','cancelada'];
if (!in_array($estatus, $estatus_validos)) {
    $estatus = 'pendiente';
}

try {
    $conn->beginTransaction();

    $sql = "
        INSERT INTO campañas 
        (marca_id, tipo_campaña_id, responsable_id, nombre_campaña, estatus)
        VALUES (:marca, :tipo, :resp, :nombre, :estatus)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':marca'   => $marca_id,
        ':tipo'    => $tipo_id,
        ':resp'    => $responsable_id,
        ':nombre'  => $nombre,
        ':estatus' => $estatus
    ]);

    $conn->commit();
    header("Location: ../Vista/campañas.php?ok=1");
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    die("Error: " . $e->getMessage());
}
?>