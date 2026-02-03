<?php
session_start();
require_once "../Modelo/conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($correo) || empty($password)) {
        header("Location: ../Vista/login.php?error=campos_vacios");
        exit;
    }

    $sql = "SELECT id_usuario FROM usuarios_rh 
            WHERE correo = ? AND password = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Error en la consulta");
    }

    mysqli_stmt_bind_param($stmt, "ss", $correo, $password);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($usuario = mysqli_fetch_assoc($resultado)) {

        $_SESSION["id_usuario"] = $usuario["id_usuario"];
        $_SESSION["correo"] = $correo;

        header("Location: ../Vista/dashboard.html");
        exit;

    } else {
        header("Location: ../Vista/login.php?error=credenciales");
        exit;
    }
}
?>