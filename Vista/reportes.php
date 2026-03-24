<?php
// reportes.php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

require_once '../Modelo/SupaConexion.php'; // Conexión PostgreSQL con PDO

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Administrador';
$usuario_correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'admin@gmail.com';

// Obtener estadísticas de solicitudes aprobadas
$sql_stats = "
    SELECT 
        COUNT(*) as total_aprobadas,
        COUNT(DISTINCT p.id_puesto) as puestos_diferentes
    FROM solicitud s
    LEFT JOIN cat_puestos p ON s.id_puesto = p.id_puesto
    WHERE LOWER(s.estatus) = 'aprobada'
";

$stmt_stats = $conn->query($sql_stats);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
$total_aprobadas = $stats['total_aprobadas'] ?? 0;
$puestos_diferentes = $stats['puestos_diferentes'] ?? 0;
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
    <style>
        /* Estilos adicionales para la sección de reportes */
        .reportes-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .estadisticas {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .estadistica-card {
            flex: 1;
            min-width: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .estadistica-card.total {
            background: linear-gradient(135deg, #EC1F27 0%, #c41e24 100%);
        }
        
        .estadistica-card.puestos {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        
        .estadistica-card .numero {
            font-size: 36px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        
        .estadistica-card .texto {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .exportar-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .exportar-section h3 {
            margin-top: 0;
            color: #333;
            margin-bottom: 15px;
        }
        
        .btn-exportar-excel {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-exportar-excel:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .btn-exportar-excel:active {
            transform: translateY(0);
        }
        
        .info-exportacion {
            margin-top: 15px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 5px;
            font-size: 14px;
            color: #495057;
        }
        
        .info-exportacion i {
            font-style: normal;
            font-weight: bold;
        }
        
        .filtros-reportes {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .filtros-reportes .grupo-filtro {
            flex: 1;
            min-width: 200px;
        }
        
        .filtros-reportes label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .filtros-reportes input,
        .filtros-reportes select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn-filtrar {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            height: 38px;
        }
        
        .btn-filtrar:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../src/imagenes/tactica_logo.png" alt="TÁCTICA 8" class="logo-img" width="100" height="100">
            </a>
        </div>

        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

        <div class="header-exit">
            <div class="user-info-container" style="margin-right: 15px; text-align: right;">
                <span class="user-name" style="display: block; color: white; font-weight: bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span class="user-email" style="display: block; color: white; font-size: 12px; opacity: 0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
            <a href="../Controlador/logout.php" class="logout-link" onclick="return confirm('¿Estás seguro de cerrar sesión?')">
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
            <h1>Reportes de Solicitudes Aprobadas</h1>
            
            <div class="reportes-container">
                <!-- Tarjetas de estadísticas -->
                <div class="estadisticas">
                    <div class="estadistica-card total">
                        <span class="numero"><?php echo $total_aprobadas; ?></span>
                        <span class="texto">Solicitudes Aprobadas</span>
                    </div>
                    <div class="estadistica-card puestos">
                        <span class="numero"><?php echo $puestos_diferentes; ?></span>
                        <span class="texto">Puestos Diferentes</span>
                    </div>
                </div>

                <!-- Sección de filtros y exportación -->
                <div class="exportar-section">
                    <h3>Exportar Reporte de Solicitudes Aprobadas</h3>
                    
                    <div class="filtros-reportes">
    <div class="grupo-filtro">
        <label>&nbsp;</label>
        <button onclick="exportarAExcel()" class="btn-exportar-excel">
            <img src="../src/imagenes/excel.png" width="20" alt="Excel">
            Exportar a Excel
        </button>
    </div>
</div>
                    
                    <div class="info-exportacion">
                        <i>ℹInformación:</i> Se exportarán todas las solicitudes con estatus APROBADA, incluyendo todos los campos: datos personales, dirección, datos familiares y referencias.
                        <?php if($total_aprobadas > 0): ?>
                            <strong><?php echo $total_aprobadas; ?></strong> solicitudes disponibles para exportar.
                        <?php else: ?>
                            <strong>No hay solicitudes aprobadas</strong> para exportar en este momento.
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lista resumen de solicitudes aprobadas (opcional) -->
                <div class="exportar-section" style="margin-top: 20px;">
                    <h3>Últimas Solicitudes Aprobadas</h3>
                    <?php
                    // Mostrar las últimas 10 solicitudes aprobadas como resumen
                    $sql_resumen = "
                        SELECT 
                            s.id_solicitud,
                            CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, '')) AS nombre_completo,
                            p.nombre_puesto AS puesto,
                            TO_CHAR(s.fecha_registro, 'DD/MM/YYYY') AS fecha_aprobacion
                        FROM solicitud s
                        LEFT JOIN cat_puestos p ON s.id_puesto = p.id_puesto
                        WHERE LOWER(s.estatus) = 'aprobada'
                        ORDER BY s.fecha_registro DESC
                        LIMIT 10
                    ";
                    $stmt_resumen = $conn->query($sql_resumen);
                    $ultimas_solicitudes = $stmt_resumen->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    
                    <?php if(count($ultimas_solicitudes) > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 8px; border: 1px solid #ddd;">ID</th>
                                    <th style="padding: 8px; border: 1px solid #ddd;">Nombre Completo</th>
                                    <th style="padding: 8px; border: 1px solid #ddd;">Puesto</th>
                                    <th style="padding: 8px; border: 1px solid #ddd;">Fecha de Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($ultimas_solicitudes as $solicitud): ?>
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $solicitud['id_solicitud']; ?></td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($solicitud['nombre_completo']); ?></td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo htmlspecialchars($solicitud['puesto']); ?></td>
                                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo $solicitud['fecha_aprobacion']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 20px;">No hay solicitudes aprobadas para mostrar.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script>
        function exportarAExcel() {
            // Obtener los valores de los filtros
            var searchText = document.getElementById('searchInput').value;
            
            // Construir la URL con los filtros
            var url = '../Controlador/exportar_solicitudes_excel.php?';
            
            if (searchText) {
                url += 'busqueda=' + encodeURIComponent(searchText);
            }
            
            // Eliminar el último & si existe
            url = url.replace(/&$/, '');
            
            // Redireccionar para descargar el archivo
            window.location.href = url;
        }
        
        // Event listener para el Enter en el campo de búsqueda
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                exportarAExcel();
            }
        });
    </script>
</body>

</html>