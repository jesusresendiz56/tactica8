<?php
session_start();
require_once "../Modelo/SupaConexion.php";

/* ===============================
   VALIDACIÓN BÁSICA
================================ */
if (empty($_POST['email']) || empty($_POST['password'])) {
    header("Location: ../Vista/login.php?error=campos_vacios");
    exit();
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

try {
    /* ===============================
       CONSULTA DE USUARIO RH
    ================================ */
    $sql = "SELECT id_usuario, correo, password
            FROM usuarios_rh
            WHERE correo = :correo
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && $password === $usuario['password']) {
        /* ===============================
           CREAR SESIÓN
        ================================ */
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['usuario_id'] = $usuario['id_usuario']; // Duplicado para compatibilidad
        $_SESSION['correo'] = $usuario['correo'];
        $_SESSION['usuario_nombre'] = 'Administrador'; // Cambia esto si tienes columna 'nombre'
        $_SESSION['usuario_rol'] = 'admin'; // Cambia según tu lógica de roles
        $_SESSION['login_time'] = time();

        /* ===============================
           REDIRIGIR AL DASHBOARD
        ================================ */
        header("Location: ../Vista/dashboard.php");
        exit();

    } else {
        header("Location: ../Vista/login.php?error=credenciales");
        exit();
    }

} catch (PDOException $e) {
    header("Location: ../Vista/login.php?error=servidor");
    exit();
}
?>