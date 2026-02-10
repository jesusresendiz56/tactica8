<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/dashboard.css">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Logo -->
        <div class="header-logo">
            <a href="../Vista/dashboard.php">
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

        <!-- Salir -->
        <div class="header-exit">
            <a href="login.php">
                <img src="../src/imagenes/logout.png"
                     alt="Salir"
                     class="exit-icon"
                     width="30"
                     height="30">
            </a>
        </div>
    </header>

    <!-- ===== MENÚ ===== -->
    <nav class="menu">
       <a href="dashboard.php">Dashboard</a>
    <a href="campañas.php" class="active">Campañas</a>
    <a href="personal.php">Personal</a>
    <a href="asignaciones.php">Asignaciones</a>
    <a href="reportes.php">Reportes</a>
    <a href="solicitudes.php">Solicitudes</a>
    </nav>

    <h1>Dashboard</h1>

    <div class="dashboard-grid">
        <div class="card">Campañas Activas:</div>
        <div class="card">Personal Activo:</div>
        <div class="card">Personal Disponible:</div>
        <div class="card">Solicitudes Pendientes:</div>
    </div>
</body>
</html>
