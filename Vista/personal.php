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

    <!-- ===== PROTECCIÓN TOTAL CONTRA INSPECCIÓN ===== -->
    <script>
        (function() {
            'use strict';
            
            // 1. BLOQUEAR CLIC DERECHO
            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                showAlert();
                return false;
            });
            
            // 2. BLOQUEAR TECLAS DE DESARROLLO
            document.addEventListener('keydown', function(e) {
                const blockedKeys = [
                    'F12', 'F5', 'F11', 'F7', 'F3', 'Delete', 'Insert'
                ];
                
                const blockedCombos = [
                    (e.ctrlKey && e.shiftKey && e.key === 'I'),
                    (e.ctrlKey && e.shiftKey && e.key === 'J'),
                    (e.ctrlKey && e.key === 'U'),
                    (e.ctrlKey && e.key === 's'),
                    (e.ctrlKey && e.key === 'S'),
                    (e.ctrlKey && e.shiftKey && e.key === 'C'),
                    (e.ctrlKey && e.key === 'Shift'),
                    (e.ctrlKey && e.key === 'p'),
                    (e.ctrlKey && e.key === 'P'),
                    (e.altKey && e.key === 'F4')
                ];
                
                if (blockedKeys.includes(e.key) || blockedCombos.includes(true)) {
                    e.preventDefault();
                    showAlert();
                    return false;
                }
            });
            
            // 3. DETECTAR DEVTOOLS
            function detectDevTools() {
                const widthThreshold = window.outerWidth - window.innerWidth > 160;
                const heightThreshold = window.outerHeight - window.innerHeight > 160;
                
                if (widthThreshold || heightThreshold) {
                    document.body.innerHTML = `
                        <div style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: #1e3c72;
                            color: white;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                            font-family: Arial, sans-serif;
                            z-index: 999999;
                        ">
                            <h1 style="color: #ec1f27; font-size: 48px; margin-bottom: 20px;">⛔ ACCESO DENEGADO</h1>
                            <p style="font-size: 20px; margin-bottom: 10px;">No está permitido inspeccionar esta página</p>
                            <p style="font-size: 16px; opacity: 0.8;">TÁCTICA 8 - Sistema Privado</p>
                        </div>
                    `;
                }
            }
            
            // 4. BLOQUEAR SELECCIÓN DE TEXTO
            document.addEventListener('selectstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // 5. BLOQUEAR ARRASTRAR
            document.addEventListener('dragstart', function(e) {
                e.preventDefault();
                return false;
            });
            
            // 6. BLOQUEAR COPIAR, CORTAR Y PEGAR
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                showAlert();
                return false;
            });
            
            document.addEventListener('cut', function(e) {
                e.preventDefault();
                return false;
            });
            
            document.addEventListener('paste', function(e) {
                e.preventDefault();
                return false;
            });
            
            // 7. ALERTA PERSONALIZADA
            function showAlert() {
                const alertBox = document.createElement('div');
                alertBox.innerHTML = `
                    <div style="
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: #ec1f27;
                        color: white;
                        padding: 15px 25px;
                        border-radius: 5px;
                        font-weight: bold;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
                        z-index: 99999;
                        animation: slideIn 0.3s ease;
                    ">
                        ❌ Acción no permitida en este sistema
                    </div>
                    <style>
                        @keyframes slideIn {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                    </style>
                `;
                document.body.appendChild(alertBox);
                
                setTimeout(function() {
                    alertBox.remove();
                }, 3000);
            }
            
            // 8. DETECCIÓN CONTINUA DE DEVTOOLS
            setInterval(detectDevTools, 1000);
            
            // 9. BLOQUEAR CONSOLA
            setInterval(function() {
                if (typeof console.clear !== 'undefined') {
                    console.clear = function() {};
                }
                if (typeof console.log !== 'undefined') {
                    console.log = function() {};
                }
                if (typeof console.info !== 'undefined') {
                    console.info = function() {};
                }
                if (typeof console.warn !== 'undefined') {
                    console.warn = function() {};
                }
                if (typeof console.error !== 'undefined') {
                    console.error = function() {};
                }
                if (typeof console.table !== 'undefined') {
                    console.table = function() {};
                }
            }, 100);
            
            // 10. OFUSCAR HTML
            const style = document.createElement('style');
            style.innerHTML = `
                body {
                    user-select: none !important;
                    -webkit-user-select: none !important;
                    -moz-user-select: none !important;
                    -ms-user-select: none !important;
                }
                
                img, a, .card, .menu, .header {
                    pointer-events: none;
                }
                
                a, button, .exit-link, .exit-icon, .header-exit a, .menu a {
                    pointer-events: auto !important;
                }
            `;
            document.head.appendChild(style);
            
        })();
    </script>

    <!-- MARCA DE AGUA INVISIBLE -->
    <div style="display: none;">
        TÁCTICA 8 - Sistema Privado - Prohibida su reproducción
        <?php echo md5(date('Y-m-d')); ?>
    </div>

    <!-- BLOQUEAR CAPTURA DE PANTALLA -->
    <script>
        document.addEventListener('keyup', function(e) {
            if (e.key === 'PrintScreen') {
                navigator.clipboard.writeText('TÁCTICA 8 - Captura de pantalla bloqueada');
                showAlert();
            }
        });

        // Confirmación de logout
        document.querySelector('a[href*="logout.php"]')?.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de cerrar sesión?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>