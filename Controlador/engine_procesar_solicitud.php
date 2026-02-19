<?php
// Controlador/procesar_solicitud.php
session_start();
require_once '../Modelo/SupaConexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../Vista/login.php?error=no_sesion');
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['accion'])) {
    header('Location: ../Vista/solicitudes.php');
    exit();
}

$id_solicitud = $_GET['id'];
$accion = $_GET['accion'];

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();

    // 🔹 Obtener datos del solicitante 
    $sqlDatos = "SELECT nombre, 
                        COALESCE(celular, telefono_casa, telefono_recados) AS telefono
                 FROM solicitud
                 WHERE id_solicitud = :id";
    $stmtDatos = $conn->prepare($sqlDatos);
    $stmtDatos->bindParam(':id', $id_solicitud);
    $stmtDatos->execute();
    $datos = $stmtDatos->fetch(PDO::FETCH_ASSOC);

    $nombre = $datos['nombre'] ?? '';
    $telefono = $datos['telefono'] ?? '';

    if ($accion === 'aceptar') {

        // INICIAR TRANSACCIÓN
        $conn->beginTransaction();

        // 1. ACTUALIZAR ESTATUS DE LA SOLICITUD
        $sql1 = "UPDATE solicitud SET estatus = 'aprobada' WHERE id_solicitud = :id";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':id', $id_solicitud);
        $stmt1->execute();

        // 2. INSERTAR EN PERSONAL
        $sql2 = "INSERT INTO personal (id_solicitud, fecha_alta, estatus_laboral)
                 VALUES (:id, CURRENT_DATE, 'activo')";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':id', $id_solicitud);
        $stmt2->execute();

        // CONFIRMAR TRANSACCIÓN
        $conn->commit();

        // 🔹 Preparar mensaje WhatsApp
        if ($telefono) {
            $telefono = preg_replace('/[^0-9]/', '', $telefono);

            if (substr($telefono, 0, 2) != '52') {
                $telefono = '52' . $telefono;
            }

            $mensaje = urlencode("Hola $nombre, tu solicitud fue APROBADA en TÁCTICA 8. Nos pondremos en contacto contigo por este medio.");
            $whatsapp_url = "https://wa.me/$telefono?text=$mensaje";

            header("Location: $whatsapp_url");
            exit();
        }

        header('Location: ../Vista/personal.php?success=nuevo_personal');
        exit();

    } elseif ($accion === 'rechazar') {

        $sql = "UPDATE solicitud SET estatus = 'rechazada' WHERE id_solicitud = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id_solicitud);
        $stmt->execute();

        // 🔹 Preparar mensaje WhatsApp
        if ($telefono) {
            $telefono = preg_replace('/[^0-9]/', '', $telefono);

            if (substr($telefono, 0, 2) != '52') {
                $telefono = '52' . $telefono;
            }

            $mensaje = urlencode("Hola $nombre, tu solicitud fue RECHAZADA en TÁCTICA 8. Gracias por tu interés.");
            $whatsapp_url = "https://wa.me/$telefono?text=$mensaje";

            header("Location: $whatsapp_url");
            exit();
        }

        header('Location: ../Vista/solicitudes.php?success=rechazada');
        exit();
    }

} catch (PDOException $e) {

    if ($accion === 'aceptar' && isset($conn)) {
        $conn->rollBack();
    }

    header('Location: ../Vista/solicitudes.php?error=db');
    exit();
}
?>
