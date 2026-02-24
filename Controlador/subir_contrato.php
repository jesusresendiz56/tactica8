<?php
require_once '../Modelo/SupaConexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === 0) {

        $id_personal = $_POST['id_personal'];
        $directorio = "../contratos/";

        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $nombreArchivo = time() . "_" . basename($_FILES["contrato"]["name"]);
        $rutaArchivo = $directorio . $nombreArchivo;

        if (move_uploaded_file($_FILES["contrato"]["tmp_name"], $rutaArchivo)) {

            $db = new SupaConexion();
            $conn = $db->getConexion();

            $sql = "UPDATE personal SET contrato_url = :ruta WHERE id_personal = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ruta', $rutaArchivo);
            $stmt->bindParam(':id', $id_personal);
            $stmt->execute();

            header("Location: ../Vista/personal.php");
            exit();
        }
    }
}

header("Location: ../Vista/personal.php");
exit();