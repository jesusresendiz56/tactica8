<?php
session_start();
require_once '../Modelo/SupaConexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../Vista/login.php?error=no_sesion');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_personal = $_POST['id_personal'] ?? null;
    $nuevo_estatus = $_POST['nuevo_estatus'] ?? null;

    if ($id_personal && $nuevo_estatus) {

        $db = new SupaConexion();
        $conn = $db->getConexion();

        $conn->beginTransaction();

        try {

            // Actualizar estatus laboral
            $sql = "UPDATE personal 
                    SET estatus_laboral = :estatus 
                    WHERE id_personal = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':estatus' => $nuevo_estatus,
                ':id' => $id_personal
            ]);

            // 🔥 Si se vuelve inactivo → cancelar asignaciones activas
            if ($nuevo_estatus === 'inactivo') {

                $sql2 = "UPDATE asignaciones 
                         SET estatus_asignacion = 'cancelada'
                         WHERE id_personal = :id
                         AND estatus_asignacion IN ('activa','en_progreso')";

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([':id' => $id_personal]);
            }

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
        }
    }
}

header("Location: ../Vista/personal.php");
exit();