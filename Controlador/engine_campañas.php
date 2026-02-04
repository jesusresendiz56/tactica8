<?php
// Controlador/engine_campañas.php
session_start();
require_once '../Modelo/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['marca_id']) && !empty($_POST['tipo_campaña_id']) 
        && !empty($_POST['responsable_id']) && !empty($_POST['nombre_campaña'])) {

        $marca_id = trim($_POST['marca_id']);
        $tipo_campaña_id = trim($_POST['tipo_campaña_id']);
        $responsable_id = trim($_POST['responsable_id']);
        $nombre_campaña = trim($_POST['nombre_campaña']);
        $estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : 'pendiente';

        $sql = "INSERT INTO campañas (marca_id, tipo_campaña_id, responsable_id, nombre_campaña, estatus) 
                VALUES (?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "iiiss", $marca_id, $tipo_campaña_id, $responsable_id, $nombre_campaña, $estatus);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: ../Vista/campañas.php?mensaje=success&texto=Campaña creada exitosamente");
                exit();
            } else {
                mysqli_stmt_close($stmt);
                header("Location: ../Vista/campañas.php?mensaje=error&texto=Error al crear campaña");
                exit();
            }
        } else {
            header("Location: ../Vista/campañas.php?mensaje=error&texto=Error en la preparación");
            exit();
        }
    } else {
        header("Location: ../Vista/campañas.php?mensaje=error&texto=Por favor completa todos los campos requeridos");
        exit();
    }
}

header("Location: ../Vista/campañas.php");
exit();
?>