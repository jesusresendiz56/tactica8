<?php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// Datos del usuario
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Administrador';
$usuario_correo = $_SESSION['correo'] ?? 'admin@gmail.com';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reportes | TÁCTICA 8</title>
    <link rel="icon" type="image/png" href="../src/imagenes/favicon.png">
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <script src="../src/js/seguridad.js" defer></script>

    <style>
        .reportes-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .exportar-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 30px;
        }

        .btn-exportar-excel {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-size: 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-exportar-excel:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>

<header class="header">
    <div class="header-logo">
        <a href="../index.php">
            <img src="../src/imagenes/tactica_logo.png" width="100">
        </a>
    </div>

    <div class="header-center-text">
        <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
        Más de 40 años de experiencia.
    </div>

    <div class="header-exit">
        <div style="margin-right: 15px; text-align: right;">
            <span style="color: white; font-weight: bold;">
                <?php echo htmlspecialchars($usuario_nombre); ?>
            </span><br>
            <span style="color: white; font-size: 12px;">
                <?php echo htmlspecialchars($usuario_correo); ?>
            </span>
        </div>

        <a href="../Controlador/logout.php">
            <img src="../src/imagenes/logout.png" width="30">
        </a>
    </div>
</header>

<nav class="menu">
    <a href="../index.php">Dashboard</a>
    <a href="../Vista/campañas.php">Campañas</a>
    <a href="../Vista/personal.php">Personal</a>
    <a href="../Vista/asignaciones.php">Asignaciones</a>
    <a href="../Vista/reportes.php" class="active">Reportes</a>
    <a href="../Vista/solicitudes.php">Solicitudes</a>
</nav>

<main class="content">
    <section class="form-section">
        <h1>Reportes</h1>

        <div class="reportes-container">
            <div class="exportar-section">
                <h3>Descargar Reporte de Solicitudes Aprobadas</h3>

                <button onclick="exportarAExcel()" class="btn-exportar-excel">
                    <img src="../src/imagenes/excel.png" width="25">
                    Exportar a Excel
                </button>
            </div>
        </div>
    </section>
</main>

<script>
function exportarAExcel() {
    window.location.href = '../Controlador/exportar_solicitudes_excel.php';
}
</script>

</body>
</html>