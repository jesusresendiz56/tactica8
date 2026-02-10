<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/login.css">  
</head>
<body>

<div class="container">

    <!-- LOGO -->
    <div class="logo">
        <img src="../src/imagenes/logo.png" alt="TÁCTICA 8">
    </div>

    <h1>Iniciar Sesión</h1>

    <!-- MENSAJES DE ERROR -->
    <?php if (isset($_GET["error"])): ?>
        <p style="color:red; text-align:center;">
            <?php
                if ($_GET["error"] === "credenciales") {
                    echo "Correo o contraseña incorrectos";
                } elseif ($_GET["error"] === "campos_vacios") {
                    echo "Todos los campos son obligatorios";
                }
            ?>
        </p>
    <?php endif; ?>

    <form action="../Controlador/validar_login.php" method="POST">

        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>

    </form>

</div>

</body>
</html>

