<?php
session_start();
if (!isset($_SESSION['id_usuario'])) { header('Location: ../Vista/login.php?error=no_sesion'); exit(); }

foreach(['id_responsable','id_campaña','id_personal'] as $c) 
    if(empty($_POST[$c])) { header('Location: ../Vista/asignaciones.php?error=campos_vacios'); exit(); }

require_once '../Modelo/SupaConexion.php';
try {
    $conn = (new SupaConexion())->getConexion();
    $id_responsable = (int)$_POST['id_responsable'];
    $id_campana = (int)$_POST['id_campaña'];
    $id_personal = (int)$_POST['id_personal'];
    $rol = trim($_POST['rol'] ?? 'personal_asignado');

    // Validar personal no asignado
    $check = $conn->prepare("SELECT 1 FROM asignaciones WHERE id_personal=? AND estatus_asignacion IN ('activa','en_progreso')");
    $check->execute([$id_personal]);
    if($check->fetch()) { header('Location: ../Vista/asignaciones.php?error=personal_ya_asignado'); exit(); }

    // Validar campaña
    $campana = $conn->prepare("SELECT * FROM campañas WHERE id_campaña=?");
    $campana->execute([$id_campana]);
    $c = $campana->fetch(PDO::FETCH_ASSOC);
    if(!$c) { header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle='.urlencode('Campaña no existe')); exit(); }
    if($c['responsable_id']!=$id_responsable) { header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle='.urlencode('No pertenece al coordinador')); exit(); }
    if(!in_array($c['estatus'],['activa','pendiente','en_progreso'])) { header('Location: ../Vista/asignaciones.php?error=campana_inactiva&detalle='.urlencode('Estatus: '.$c['estatus'])); exit(); }
    if($c['fecha_fin'] && $c['fecha_fin']<date('Y-m-d')) { header('Location: ../Vista/asignaciones.php?error=fechas_invalidas'); exit(); }

    // Validar personal activo
    $personal = $conn->prepare("SELECT estatus_laboral FROM personal WHERE id_personal=?");
    $personal->execute([$id_personal]);
    if($personal->fetchColumn()!=='activo') { header('Location: ../Vista/asignaciones.php?error=personal_inactivo'); exit(); }

    // Insertar
    $fecha_inicio = ($c['fecha_inicio'] && $c['fecha_inicio']>date('Y-m-d')) ? $c['fecha_inicio'] : date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO asignaciones (id_personal,id_campaña,id_responsable,rol,fecha_asignacion,fecha_inicio,estatus_asignacion) VALUES (?,?,?,?,CURRENT_DATE,?,'en_progreso')");
    $stmt->execute([$id_personal,$id_campana,$id_responsable,$rol,$fecha_inicio]);
    header('Location: ../Vista/asignaciones.php?success=asignacion_creada');
} catch(PDOException $e) {
    error_log("Error asignación: ".$e->getMessage());
    header('Location: ../Vista/asignaciones.php?error=db_error&detalle='.urlencode('Error en BD'));
}
?>