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

    $usuario = $stmt->fetch();

  
    if ($usuario && $password === $usuario['password']) {

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['correo']     = $usuario['correo'];

        header("Location: ../Vista/dashboard.html");
        exit();

    } else {
        header("Location: ../Vista/login.php?error=credenciales");
        exit();
    }

} catch (PDOException $e) {
    die("Error en el login.");
}
