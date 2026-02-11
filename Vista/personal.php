<?php
// personal.php - USUARIO A LA IZQUIERDA DEL LOGOUT
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
    <title>Personal | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/personal.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Logo -->
        <div class="header-logo">
            <a href="dashboard.php">
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
            <!-- Usuario primero (izquierda) -->
            <div class="user-info" style="margin-right: 15px; text-align: right;">
                <span class="user-name" style="display: block; color: white; font-weight: bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span class="user-email" style="display: block; color: white; font-size: 12px; opacity: 0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
            
            <!-- Logout después (derecha) -->
            <a href="../Controlador/logout.php" 
               onclick="return confirm('¿Estás seguro de cerrar sesión?')"
               title="Cerrar Sesión">
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
        <a href="campañas.php">Campañas</a>
        <a href="personal.php" class="active">Personal</a>
        <a href="asignaciones.php">Asignaciones</a>
        <a href="reportes.php">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>
    </nav>

    <!-- ===== FORMULARIO ===== -->
    <main class="content">
        <section class="form-section">
            <h1>Gestión de Personal</h1>

            <form>
                <label>Nombre</label>
                <input type="text" name="nombre">

                <label>Telefono</label>
                <input type="number" name="telefono">

                <label>Correo Electrónico</label>
                <input type="email" name="correo">

                <label>Puesto</label>
                <select name="puesto" id="puesto">
                    <option value="promotores">PROMOTORES</option>
                    <option value="demostradoras">DEMOSTRADORAS</option>
                    <option value="coordinadores">COORDINADORES</option>
                    <option value="supervisores">SUPERVISORES</option>
                    <option value="asesor">ASESOR</option>
                    <option value="promovendedor">PROMOVENDEDOR</option>
                    <option value="degustador">DEGUSTADOR</option>
                    <option value="auxiliar">AUXILIAR</option>
                    <option value="chofer">CHOFER</option>
                    <option value="demostrador">DEMOSTRADOR</option>
                    <option value="edecanes">EDECANES</option>
                    <option value="visual-merchandising">VISUAL MERCHANDISING</option>
                    <option value="ayudante-general">AYUDANTE GENERAL</option>
                    <option value="promotor-ventas">PROMOTOR DE VENTAS</option>
                    <option value="infladores-armadores">INFLADORES Y ARMADORES</option>
                    <option value="capacitador">CAPACITADOR</option>
                    <option value="reclutador-campo">RECLUTADOR DE CAMPO</option>
                    <option value="representante-ventas">REPRESENTANTE DE VENTAS</option>
                    <option value="mercaderista">MERCADERISTA</option>
                    <option value="analista">ANALISTA</option>
                    <option value="consultora">CONSULTORA</option>
                </select>

                <label>Estatus</label>
                <select>
                    <option>Activo</option>
                    <option>Inactivo</option>
                </select>

                <button type="submit">Guardar Personal</button>
            </form>
        </section>

        <!-- ===== TABLA ===== -->
        <section class="table-section">
            <h2>Personal Existente</h2>

            <label>
                Buscar Personal:
                <img src="../src/imagenes/busqueda_personal.png"
                     alt="Buscar"
                     class="search-icon"
                     width="20"
                     height="20">
            </label>

            <div class="search-box">
                <input type="search" placeholder="Buscar...">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Estatus</th>
                        <th>Contrato</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos dinámicos -->
                </tbody>
            </table>
        </section>
    </main>    
</body>
</html>