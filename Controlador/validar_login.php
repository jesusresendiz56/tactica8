<?php
session_start();
require_once "../Modelo/SupaConexion.php";

// Validar campos vacíos
if (empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: ../Vista/login.php?error=campos_vacios");
    exit();
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

try {
    // Buscar usuario por correo
    $sql = "SELECT id_usuario, correo, password
            FROM usuarios_rh
            WHERE correo = :correo
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // VALIDACIÓN TEMPORAL (SIN HASH)
    if ($usuario && $password === $usuario['password']) {

        // Crear sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['correo'] = $usuario['correo'];

        // Redirigir al dashboard
        header("Location: ../Vista/dashboard.html");
        exit();

    } else {
        header("Location: ../Vista/login.php?error=credenciales");
        exit();
    }

} catch (PDOException $e) {
    die("Error en el login: " . $e->getMessage());
}
?>