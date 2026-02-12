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
    <link rel="stylesheet" href="../src/estilos/estilos.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-logo">
            <a href="dashboard.php">
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
        <a href="dashboard.php">Dashboard</a>
        <a href="campañas.php">Campañas</a>
        <a href="personal.php">Personal</a>
        <a href="asignaciones.php">Asignaciones</a>
        <a href="reportes.php">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>
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
                        <th>Acciones</th>
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
                                <td>
                                    <a href="ver_solicitud.php?id=<?php echo $solicitud['id']; ?>" 
                                       class="btn-accion btn-ver" title="Ver detalles">
                                        <img src="../src/imagenes/ver.png" alt="Ver" width="24" height="24">
                                    </a>

                                    <?php if ($solicitud['estatus'] == 'pendiente'): ?>
                                        <a href="../Controlador/engine_procesar_solicitud.php?accion=aceptar&id=<?php echo $solicitud['id']; ?>" 
                                           class="btn-accion btn-aceptar" 
                                           onclick="return confirm('¿Aceptar esta solicitud?')" 
                                           title="Aceptar solicitud">
                                            <img src="../src/imagenes/aceptar.png" alt="Aceptar" width="24" height="24">
                                        </a>
                                        <a href="../Controlador/engine_procesar_solicitud.php?accion=rechazar&id=<?php echo $solicitud['id']; ?>" 
                                           class="btn-accion btn-rechazar" 
                                           onclick="return confirm('¿Rechazar esta solicitud?')" 
                                           title="Rechazar solicitud">
                                            <img src="../src/imagenes/rechazar.png" alt="Rechazar" width="24" height="24">
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">No hay solicitudes de empleo registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        function filtrarTabla() {
            var input = document.getElementById('searchInput');
            var filterStatus = document.getElementById('filterStatus');
            var table = document.getElementById('tablaSolicitudes');
            var tr = table.getElementsByTagName('tr');

            var searchText = input.value.toLowerCase();
            var statusValue = filterStatus.value.toLowerCase();

            for (var i = 1; i < tr.length; i++) {
                var tdNombre = tr[i].getElementsByTagName('td')[1];
                var tdPuesto = tr[i].getElementsByTagName('td')[2];
                var tdStatus = tr[i].getElementsByTagName('td')[5];

                if (tdNombre && tdPuesto && tdStatus) {
                    var nombre = tdNombre.textContent || tdNombre.innerText;
                    var puesto = tdPuesto.textContent || tdPuesto.innerText;
                    var status = tdStatus.textContent || tdStatus.innerText;

                    var matchSearch = nombre.toLowerCase().indexOf(searchText) > -1 || puesto.toLowerCase().indexOf(searchText) > -1;
                    var matchStatus = statusValue === '' || status.toLowerCase().indexOf(statusValue) > -1;

                    tr[i].style.display = (matchSearch && matchStatus) ? '' : 'none';
                }
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', filtrarTabla);
        document.getElementById('filterStatus').addEventListener('change', filtrarTabla);
    </script>
</body>
</html>

