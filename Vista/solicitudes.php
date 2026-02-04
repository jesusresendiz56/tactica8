<?php
// Vista/solicitudes.php
session_start();
require_once '../Modelo/conexion.php';

// Obtener todas las solicitudes
$sql = "SELECT 
            id_solicitud as id,
            CONCAT(nombres, ' ', apellido_paterno, ' ', COALESCE(apellido_materno, '')) as nombre_completo,
            puesto,
            COALESCE(celular, telefono_casa, telefono_recados) as telefono,
            'Inmediata' as disponibilidad,
            estatus,
            DATE_FORMAT(fecha_registro, '%d/%m/%Y') as fecha_solicitud,
            correo,
            edad,
            grado_estudios
        FROM solicitudes_empleo 
        ORDER BY fecha_registro DESC";

$result = mysqli_query($conn, $sql);
$solicitudes = [];

while ($row = mysqli_fetch_assoc($result)) {
    $solicitudes[] = $row;
}

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
    <link rel="stylesheet" href="../src/estilos/solicitudes.css">
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-logo">
            <a href="dashboard.php">
                <img src="../src/imagenes/tactica_logo.png"
                     alt="TÁCTICA 8"
                     class="logo-img"
                     width="100"
                     height="100">
            </a>
        </div>

        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

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

    <!-- ===== MENÚ LATERAL ===== -->
    <nav class="menu">
        <a href="dashboard.html">Dashboard</a>
        <a href="campañas.php">Campañas</a>
        <a href="personal.html">Personal</a>
        <a href="asignaciones.html">Asignaciones</a>
        <a href="reportes.html">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="content">
        <section class="form-section">
            <h1>Gestión de Solicitudes</h1>
            
            <!-- Contadores de solicitudes -->
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
            <!-- Filtros y búsqueda -->
            <div class="filtros">
                <input type="text" id="searchInput" placeholder="Buscar por nombre o puesto...">
                <select id="filterStatus">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_revision">En revisión</option>
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
                            // Determinar clase CSS para el estado
                            $clase_estado = 'estado-' . $solicitud['estatus'];
                            $estado_texto = ucfirst(str_replace('_', ' ', $solicitud['estatus']));
                            ?>
                            <tr>
                                <td><?php echo $solicitud['id']; ?></td>
                                <td><?php echo $solicitud['nombre_completo']; ?></td>
                                <td><?php echo $solicitud['puesto']; ?></td>
                                <td><?php echo $solicitud['telefono']; ?></td>
                                <td><?php echo $solicitud['disponibilidad']; ?></td>
                                <td>
                                    <span class="estado-badge <?php echo $clase_estado; ?>">
                                        <?php echo $estado_texto; ?>
                                    </span>
                                </td>
                                <td><?php echo $solicitud['fecha_solicitud']; ?></td>
                                <td>
                                    <a href="ver_solicitud.php?id=<?php echo $solicitud['id']; ?>" 
                                       class="btn-accion btn-ver" title="Ver detalles">
                                        👁️ Ver
                                    </a>
                                    
                                    <?php if ($solicitud['estatus'] == 'pendiente' || $solicitud['estatus'] == 'en_revision'): ?>
                                        <a href="procesar_solicitud.php?accion=aceptar&id=<?php echo $solicitud['id']; ?>" 
                                           class="btn-accion btn-aceptar" 
                                           onclick="return confirm('¿Aceptar esta solicitud?')" 
                                           title="Aceptar solicitud">
                                            ✓ Aceptar
                                        </a>
                                        <a href="procesar_solicitud.php?accion=rechazar&id=<?php echo $solicitud['id']; ?>" 
                                           class="btn-accion btn-rechazar" 
                                           onclick="return confirm('¿Rechazar esta solicitud?')" 
                                           title="Rechazar solicitud">
                                            ✗ Rechazar
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                No hay solicitudes de empleo registradas.
                            </td>
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
                    
                    var matchSearch = nombre.toLowerCase().indexOf(searchText) > -1 || 
                                     puesto.toLowerCase().indexOf(searchText) > -1;
                    
                    var matchStatus = statusValue === '' || 
                                     status.toLowerCase().indexOf(statusValue) > -1;
                    
                    if (matchSearch && matchStatus) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }
        }
        
        // Filtrar al escribir
        document.getElementById('searchInput').addEventListener('keyup', filtrarTabla);
        document.getElementById('filterStatus').addEventListener('change', filtrarTabla);
    </script>
</body>
</html>

<?php
// Cerrar conexión
mysqli_close($conn);
?>