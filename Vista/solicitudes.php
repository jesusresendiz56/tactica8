<?php
// Vista/solicitudes.php
session_start();
require_once '../Modelo/SupaConexion.php'; // Conexión PostgreSQL con PDO

// Obtener todas las solicitudes con JOIN a cat_puestos
$sql = "
    SELECT 
        s.id_solicitud AS id,
        CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, '')) AS nombre_completo,
        p.nombre_puesto AS puesto,
        COALESCE(s.celular, s.telefono_casa, s.telefono_recados) AS telefono,
        'Inmediata' AS disponibilidad,
        LOWER(s.estatus) AS estatus,
        TO_CHAR(s.fecha_registro, 'DD/MM/YYYY') AS fecha_solicitud
    FROM solicitud s
    LEFT JOIN cat_puestos p ON s.id_puesto = p.id_puesto
    ORDER BY s.fecha_registro DESC
";

$stmt = $conn->query($sql);
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas
$total_solicitudes = count($solicitudes);
$pendientes = 0;
$aprobadas = 0;
$rechazadas = 0;

foreach ($solicitudes as $solicitud) {
    switch ($solicitud['estatus']) {
        case 'pendiente':
            $pendientes++;
            break;
        case 'aprobada':
            $aprobadas++;
            break;
        case 'rechazada':
            $rechazadas++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Solicitudes | TÁCTICA 8</title>
    <link rel="icon" type="image/png" href="../src/imagenes/favicon.png">
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <script src="../src/js/seguridad.js" defer></script>
    <style>
        /* Estilos adicionales para las columnas de acciones y exportación */
        .accion-columna {
            text-align: center;
            vertical-align: middle;
            width: 50px;
        }

        .accion-deshabilitada {
            color: #ccc;
            font-weight: bold;
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
        }

        table td {
            padding: 8px 5px;
        }

        /* Estilos para filtros y botón de exportar */
        .filtros {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filtros input,
        .filtros select,
        .filtros button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .filtros button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filtros button:hover {
            background-color: #0056b3;
        }

        .btn-exportar {
            background-color: #28a745 !important;
            margin-left: 10px;
        }

        .btn-exportar:hover {
            background-color: #218838 !important;
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
            <a href="login.php">
                <img src="../src/imagenes/logout.png" alt="Salir" class="exit-icon" width="30" height="30">
            </a>
        </div>
    </header>

    <!-- ===== MENÚ LATERAL ===== -->
    <nav class="menu">
        <a href="../index.php">Dashboard</a>
        <a href="../Vista/campañas.php">Campañas</a>
        <a href="../Vista/personal.php">Personal</a>
        <a href="../Vista/asignaciones.php">Asignaciones</a>
        <a href="../Vista/reportes.php">Reportes</a>
        <a href="../Vista/solicitudes.php" class="active">Solicitudes</a>
    </nav>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="content">
        <section class="form-section">
            <h1>Gestión de Solicitudes</h1>
            <div class="contadores">
                <div class="contador contador-total">
                    <span class="numero"><?php echo $total_solicitudes; ?></span>
                    <span class="texto">Total Solicitudes</span>
                </div>
                <div class="contador contador-pendientes">
                    <span class="numero"><?php echo $pendientes; ?></span>
                    <span class="texto">Pendientes</span>
                </div>
                <div class="contador contador-aprobadas">
                    <span class="numero"><?php echo $aprobadas; ?></span>
                    <span class="texto">Aprobadas</span>
                </div>
                <div class="contador contador-rechazadas">
                    <span class="numero"><?php echo $rechazadas; ?></span>
                    <span class="texto">Rechazadas</span>
                </div>
            </div>
        </section>

        <section class="table-section">
            <div class="filtros">
                <input type="text" id="searchInput" placeholder="Buscar por nombre o puesto...">
                <select id="filterStatus">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
                <button onclick="filtrarTabla()">Buscar</button>
                
            </div>

            <table id="tablaSolicitudes">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Teléfono</th>
                        <th>Disponibilidad</th>
                        <th>Estatus</th>
                        <th>Fecha de Solicitud</th>
                        <th>Ver</th>
                        <th>Aceptar</th>
                        <th>Rechazar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($solicitudes) > 0): ?>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <?php
                            $clase_estado = 'estado-' . $solicitud['estatus'];
                            $estado_texto = ucfirst($solicitud['estatus']);
                            ?>
                            <tr>
                                <td><?php echo $solicitud['id']; ?></td>
                                <td><?php echo $solicitud['nombre_completo']; ?></td>
                                <td><?php echo $solicitud['puesto']; ?></td>
                                <td><?php echo $solicitud['telefono']; ?></td>
                                <td><?php echo $solicitud['disponibilidad']; ?></td>
                                <td><span class="estado-badge <?php echo $clase_estado; ?>"><?php echo $estado_texto; ?></span></td>
                                <td><?php echo $solicitud['fecha_solicitud']; ?></td>
                                <td class="accion-columna">
                                    <a href="ver_solicitud.php?id=<?php echo $solicitud['id']; ?>"
                                        class="btn-accion btn-ver" title="Ver detalles">
                                        <img src="../src/imagenes/ver.png" alt="Ver" width="24" height="24">
                                    </a>
                                </td>
                                <td class="accion-columna">
                                    <?php if ($solicitud['estatus'] == 'pendiente'): ?>
                                        <a href="../Controlador/engine_procesar_solicitud.php?accion=aceptar&id=<?php echo $solicitud['id']; ?>"
                                            class="btn-accion btn-aceptar"
                                            onclick="return confirm('¿Aceptar esta solicitud?')"
                                            title="Aceptar solicitud">
                                            <img src="../src/imagenes/aceptar.png" alt="Aceptar" width="24" height="24">
                                        </a>
                                    <?php else: ?>
                                        <span class="accion-deshabilitada">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="accion-columna">
                                    <?php if ($solicitud['estatus'] == 'pendiente'): ?>
                                        <a href="../Controlador/engine_procesar_solicitud.php?accion=rechazar&id=<?php echo $solicitud['id']; ?>"
                                            class="btn-accion btn-rechazar"
                                            onclick="return confirm('¿Rechazar esta solicitud?')"
                                            title="Rechazar solicitud">
                                            <img src="../src/imagenes/rechazar.png" alt="Rechazar" width="24" height="24">
                                        </a>
                                    <?php else: ?>
                                        <span class="accion-deshabilitada">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 20px;">No hay solicitudes de empleo registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>


</body>

</html>