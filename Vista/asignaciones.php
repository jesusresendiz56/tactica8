<?php
// Vista/asignaciones.php
session_start();

// VERIFICAR SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// SOLO MOSTRAR VISTA
require_once '../Modelo/SupaConexion.php';

try {
    $db = new SupaConexion();
    $conn = $db->getConexion();
} catch (Exception $e) {
    die("<div style='background:#f8d7da; color:#721c24; padding:20px; margin:20px; border-radius:5px;'>
         <h3>❌ ERROR DE CONEXIÓN</h3>
         <p>" . $e->getMessage() . "</p>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaciones | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <style>
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .table-section {
            margin-top: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .estatus-activa {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        .estatus-inactiva {
            background: #dc3545;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="logo">
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

        <!-- USUARIO Y LOGOUT -->
        <div class="header-exit">
            <div style="display: flex; align-items: center; color: white;">
                <div style="margin-right: 15px; text-align: right;">
                    <div style="font-weight: bold;">
                        <?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?>
                    </div>
                    <div style="font-size: 12px;">
                        <?php echo $_SESSION['correo'] ?? ''; ?>
                    </div>
                </div>
                <a href="../Controlador/logout.php" 
                   onclick="return confirm('¿Cerrar sesión?')">
                    <img src="../src/imagenes/logout.png"
                         alt="Salir"
                         width="30"
                         height="30">
                </a>
            </div>
        </div>
    </header>

    <!-- ===== MENÚ ===== -->
    <nav class="menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="campañas.php">Campañas</a>
        <a href="personal.php">Personal</a>
        <a href="asignaciones.php" class="active">Asignaciones</a>
        <a href="reportes.php">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>
    </nav>

    <!-- ===== FORMULARIO ===== -->
    <main class="content">
        <!-- MESSAGES -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'asignacion_creada'): ?>
            <div class="alert-success">
                ✅ Asignación creada exitosamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert-error">
                <?php 
                if ($_GET['error'] == 'campos_vacios') {
                    echo "❌ Todos los campos son obligatorios.";
                } elseif ($_GET['error'] == 'personal_ya_asignado') {
                    echo "❌ Este personal ya tiene una asignación activa.";
                } elseif ($_GET['error'] == 'db_error') {
                    echo "❌ Error en la base de datos.";
                    if (isset($_GET['detalle'])) {
                        echo "<br><small>" . urldecode($_GET['detalle']) . "</small>";
                    }
                } else {
                    echo "❌ Error al crear la asignación.";
                }
                ?>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <h1>Gestión de Asignaciones</h1>

            <!-- action apunta al controlador -->
            <form method="POST" action="../Controlador/engine_asignaciones.php">
                
                <!-- ===== COORDINADOR ===== -->
                <label>Coordinador</label>
                <select name="id_responsable" required>
                    <option value="" disabled selected>Seleccionar Coordinador</option>
                    <?php
                    $stmt = $conn->query("SELECT id_responsable, nombre FROM responsables WHERE estado='activo' ORDER BY nombre");
                    foreach ($stmt as $row) {
                        echo "<option value='" . $row['id_responsable'] . "'>" . htmlspecialchars($row['nombre']) . "</option>";
                    }
                    ?>
                </select>

                <!-- ===== CAMPAÑA ===== -->
                <label>Campaña</label>
                <select name="id_campaña" required> <!-- El name del input sigue siendo id_campaña -->
                    <option value="" disabled selected>Seleccionar Campaña</option>
                    <?php
                    $stmt = $conn->query("
                        SELECT id_campaña, nombre_campaña 
                        FROM campañas 
                        WHERE estatus IN ('activa', 'pendiente', 'en_progreso')
                        ORDER BY fecha_registro DESC
                    ");
                    foreach ($stmt as $row) {
                        echo "<option value='" . $row['id_campaña'] . "'>" . htmlspecialchars($row['nombre_campaña']) . "</option>";
                    }
                    ?>
                </select>

                <!-- ===== PERSONAL ===== -->
                <label>Personal</label>
                <select name="id_personal" required>
                    <option value="" disabled selected>Seleccionar Personal</option>
                    <?php
                    $stmt = $conn->query("
                        SELECT p.id_personal, s.nombre, s.apellido_paterno, s.apellido_materno, cp.nombre_puesto
                        FROM personal p
                        JOIN solicitud s ON p.id_solicitud = s.id_solicitud
                        LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
                        WHERE p.estatus_laboral = 'activo'
                        ORDER BY s.apellido_paterno
                    ");
                    foreach ($stmt as $row) {
                        $nombre_completo = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
                        $puesto = $row['nombre_puesto'] ? ' (' . $row['nombre_puesto'] . ')' : '';
                        echo "<option value='" . $row['id_personal'] . "'>" . htmlspecialchars($nombre_completo . $puesto) . "</option>";
                    }
                    ?>
                </select>

                <button type="submit">Asignar</button>
            </form>

        </section>

        <!-- ===== TABLA DE ASIGNACIONES ===== -->
        <section class="table-section">
            <h2>Asignaciones Recientes</h2>
            
            <div style="margin-bottom: 20px;">
                <input type="search" id="searchAsignaciones" placeholder="Buscar asignaciones..." 
                       style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 3px;">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Empleado</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Coordinador</th>
                        <th>Campaña</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_asignaciones = "
                        SELECT 
                            a.id_asignacion,
                            a.fecha_asignacion,
                            a.estatus_asignacion,
                            p.num_empleado,
                            s.nombre,
                            s.apellido_paterno,
                            s.apellido_materno,
                            cp.nombre_puesto,
                            r.nombre AS responsable_nombre,
                            c.nombre_campaña
                        FROM asignaciones a
                        INNER JOIN personal p ON a.id_personal = p.id_personal
                        INNER JOIN solicitud s ON p.id_solicitud = s.id_solicitud
                        LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
                        INNER JOIN responsables r ON a.id_responsable = r.id_responsable
                        INNER JOIN campañas c ON a.id_campaña = c.id_campaña
                        ORDER BY a.fecha_asignacion DESC
                        LIMIT 50
                    ";
                    
                    $asignaciones = $conn->query($sql_asignaciones)->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($asignaciones) > 0):
                        foreach ($asignaciones as $asig): 
                            $nombre_completo = $asig['nombre'] . ' ' . $asig['apellido_paterno'] . ' ' . $asig['apellido_materno'];
                            $estatus_class = $asig['estatus_asignacion'] == 'activa' ? 'estatus-activa' : 'estatus-inactiva';
                    ?>
                            <tr>
                                <td><?php echo $asig['id_asignacion']; ?></td>
                                <td><?php echo htmlspecialchars($asig['num_empleado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($nombre_completo); ?></td>
                                <td><?php echo htmlspecialchars($asig['nombre_puesto'] ?? 'Sin puesto'); ?></td>
                                <td><?php echo htmlspecialchars($asig['responsable_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($asig['nombre_campaña']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($asig['fecha_asignacion'])); ?></td>
                                <td><span class="<?php echo $estatus_class; ?>"><?php echo ucfirst($asig['estatus_asignacion']); ?></span></td>
                            </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px;">
                                No hay asignaciones registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        document.getElementById('searchAsignaciones')?.addEventListener('keyup', function() {
            var searchText = this.value.toLowerCase();
            var rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    </script>
</body>
</html>