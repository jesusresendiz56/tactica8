<?php
session_start();
require_once '../Modelo/SupaConexion.php'; // $conn (PDO)

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../Vista/login.php?error=no_sesion");
    exit();
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Vista/campañas.php");
    exit();
}

try {

    // ==============================
    // 1. CAPTURA Y LIMPIEZA DE DATOS
    // ==============================

    $marca_id        = $_POST['marca_id'] ?? null;
    $tipo_campaña_id = $_POST['tipo_campaña_id'] ?? null;
    $responsable_id  = $_POST['responsable_id'] ?? null;
    $nombre_campaña  = trim($_POST['nombre_campaña'] ?? '');
    $estatus         = $_POST['estatus'] ?? 'pendiente';
    $fecha_inicio    = !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
    $fecha_fin       = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;

    // ==============================
    // 2. VALIDACIONES BÁSICAS
    // ==============================

    if (!$marca_id || !$tipo_campaña_id || !$responsable_id || empty($nombre_campaña)) {
        header("Location: ../Vista/campañas.php?error=campos_vacios");
        exit();
    }

    if ($fecha_inicio && $fecha_fin) {
        if ($fecha_fin < $fecha_inicio) {
            header("Location: ../Vista/campañas.php?error=fechas_invalidas");
            exit();
        }
    }

    // ==============================
    // 3. INSERT
    // ==============================

    $conn->beginTransaction();

    $sql = "
        INSERT INTO campañas
        (marca_id, tipo_campaña_id, responsable_id, nombre_campaña, estatus, fecha_inicio, fecha_fin)
        VALUES
        (:marca_id, :tipo_campana_id, :responsable_id, :nombre_campana, :estatus, :fecha_inicio, :fecha_fin)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':marca_id', $marca_id, PDO::PARAM_INT);
    $stmt->bindParam(':tipo_campana_id', $tipo_campaña_id, PDO::PARAM_INT);
    $stmt->bindParam(':responsable_id', $responsable_id, PDO::PARAM_INT);
    $stmt->bindParam(':nombre_campana', $nombre_campaña, PDO::PARAM_STR);
    $stmt->bindParam(':estatus', $estatus, PDO::PARAM_STR);

    $stmt->bindValue(':fecha_inicio', $fecha_inicio, $fecha_inicio ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':fecha_fin', $fecha_fin, $fecha_fin ? PDO::PARAM_STR : PDO::PARAM_NULL);

    $stmt->execute();

    $conn->commit();

    header("Location: ../Vista/campañas.php?success=guardado");
    exit();

} catch (PDOException $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    header("Location: ../Vista/campañas.php?error=bd&detalle=" . urlencode($e->getMessage()));
    exit();
}
?>

