<?php
// reportes.php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Administrador';
$usuario_correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'admin@gmail.com';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | TÁCTICA 8</title>
    <link rel="icon" type="image/png" href="../src/imagenes/favicon.png">
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <script src="../src/js/seguridad.js" defer></script>
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Logo -->
        <div class="header-logo">
            <a href="../index.php">
                <img src="../src/imagenes/tactica_logo.png"
                     alt="TÁCTICA 8"
                     class="logo-img"
                     width="100"
                     height="100">
            </a>
        </div>

        <!-- Texto central -->
        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

        <!-- USUARIO Y LOGOUT -->
        <div class="header-exit">
            <div class="user-info-container" style="margin-right: 15px; text-align: right;">
                <span class="user-name" style="display: block; color: white; font-weight: bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span class="user-email" style="display: block; color: white; font-size: 12px; opacity: 0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
            
          <a href="../Controlador/logout.php" class="logout-link"
           onclick="return confirm('¿Estás seguro de cerrar sesión?')">
            <img src="../src/imagenes/logout.png" width="30" alt="Cerrar Sesión">
        </a>
        </div>
    </header>

    <!-- ===== MENÚ ===== -->
    <nav class="menu">
        <a href="../index.php">Dashboard</a>
        <a href="../Vista/campañas.php">Campañas</a>
        <a href="../Vista/personal.php">Personal</a>
        <a href="../Vista/asignaciones.php">Asignaciones</a>
        <a href="../Vista/reportes.php" class="active">Reportes</a>
        <a href="../Vista/solicitudes.php">Solicitudes</a>
    </nav>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="content">
        <section class="form-section">
            <h1>Reportes</h1>
        </section>
    </main>


</body>
</html>