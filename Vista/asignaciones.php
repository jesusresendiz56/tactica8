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
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ffeeba;
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
        .estatus-en_progreso {
            background: #17a2b8;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        .estatus-pendiente {
            background: #ffc107;
            color: #212529;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        .estatus-inactiva, .estatus-cancelada {
            background: #dc3545;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        .estatus-finalizada, .estatus-completada {
            background: #6c757d;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            display: inline-block;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-right: 5px;
        }
        .badge-en_progreso {
            background: #17a2b8;
            color: white;
        }
        .badge-pendiente {
            background: #ffc107;
            color: #212529;
        }
        .badge-activa {
            background: #28a745;
            color: white;
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
                    if (isset($_GET['detalle'])) {
                        echo "<br><small>" . urldecode($_GET['detalle']) . "</small>";
                    }
                } elseif ($_GET['error'] == 'campana_inactiva') {
                    echo "❌ La campaña seleccionada no está disponible para asignaciones.";
                    if (isset($_GET['detalle'])) {
                        echo "<br><small>" . urldecode($_GET['detalle']) . "</small>";
                    }
                } elseif ($_GET['error'] == 'personal_inactivo') {
                    echo "❌ El personal seleccionado no está activo laboralmente.";
                } elseif ($_GET['error'] == 'fechas_invalidas') {
                    echo "❌ Las fechas de la campaña no permiten nuevas asignaciones.";
                    if (isset($_GET['detalle'])) {
                        echo "<br><small>" . urldecode($_GET['detalle']) . "</small>";
                    }
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
                <label>Coordinador <span style="color: #999; font-size: 12px;">(Responsable de la asignación)</span></label>
                <select name="id_responsable" required>
                    <option value="" disabled selected>Seleccionar Coordinador</option>
                    <?php
                    $stmt = $conn->query("SELECT id_responsable, nombre, puesto FROM responsables WHERE estado='activo' ORDER BY nombre");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . $row['id_responsable'] . "'>" . htmlspecialchars($row['nombre']) . " - " . htmlspecialchars($row['puesto']) . "</option>";
                    }
                    ?>
                </select>

                <!-- ===== CAMPAÑA ===== -->
                <label>Campaña <span style="color: #999; font-size: 12px;">(Campañas activas, en progreso o pendientes)</span></label>
                <select name="id_campaña" id="id_campaña" required>
                    <option value="" disabled selected>Seleccionar Campaña</option>
                    <?php
                    $stmt = $conn->query("
                        SELECT 
                            c.id_campaña, 
                            c.nombre_campaña,
                            c.fecha_inicio,
                            c.fecha_fin,
                            c.estatus,
                            m.nombre AS marca_nombre,
                            tc.nombre AS tipo_campaña,
                            r.nombre AS responsable_nombre
                        FROM campañas c
                        INNER JOIN marcas m ON c.marca_id = m.id_marca
                        INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
                        INNER JOIN responsables r ON c.responsable_id = r.id_responsable
                        WHERE c.estatus IN ('activa', 'pendiente', 'en_progreso')
                        ORDER BY 
                            CASE 
                                WHEN c.estatus = 'en_progreso' THEN 1
                                WHEN c.estatus = 'activa' THEN 2
                                WHEN c.estatus = 'pendiente' THEN 3
                                ELSE 4
                            END,
                            c.fecha_registro DESC
                    ");
                    
                    $campañas_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($campañas_disponibles) > 0):
                        foreach ($campañas_disponibles as $row):
                            $fechas = '';
                            if ($row['fecha_inicio']) {
                                $fechas .= ' Inicio: ' . date('d/m/Y', strtotime($row['fecha_inicio']));
                            }
                            if ($row['fecha_fin']) {
                                $fechas .= ' - Fin: ' . date('d/m/Y', strtotime($row['fecha_fin']));
                            }
                            
                            $info_extra = $row['marca_nombre'] . ' | ' . $row['tipo_campaña'];
                            
                            echo "<option value='" . $row['id_campaña'] . "'>" 
                                . htmlspecialchars($row['nombre_campaña']) 
                                . " (" . htmlspecialchars($info_extra) . ")"
                                . " - [" . ucfirst($row['estatus']) . "]"
                                . ($fechas ? " - " . $fechas : "")
                                . " - Resp: " . htmlspecialchars($row['responsable_nombre'])
                                . "</option>";
                        endforeach;
                    else:
                        echo "<option value='' disabled>No hay campañas disponibles</option>";
                    endif;
                    ?>
                </select>

                <!-- Mostrar resumen de campañas -->
                <?php if (count($campañas_disponibles) > 0): 
                    $count_en_progreso = 0;
                    $count_pendiente = 0;
                    $count_activa = 0;
                    foreach ($campañas_disponibles as $c) {
                        if ($c['estatus'] == 'en_progreso') $count_en_progreso++;
                        if ($c['estatus'] == 'pendiente') $count_pendiente++;
                        if ($c['estatus'] == 'activa') $count_activa++;
                    }
                ?>
                <div style="font-size: 12px; color: #666; margin-top: 5px; margin-bottom: 15px; display: flex; gap: 15px;">
                    <span>📊 Campañas disponibles:</span>
                    <?php if ($count_en_progreso > 0): ?>
                        <span class="badge badge-en_progreso">🔵 <?php echo $count_en_progreso; ?> en progreso</span>
                    <?php endif; ?>
                    <?php if ($count_activa > 0): ?>
                        <span class="badge badge-activa">🟢 <?php echo $count_activa; ?> activas</span>
                    <?php endif; ?>
                    <?php if ($count_pendiente > 0): ?>
                        <span class="badge badge-pendiente">🟡 <?php echo $count_pendiente; ?> pendientes</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- ===== PERSONAL ===== -->
                <label>Personal <span style="color: #999; font-size: 12px;">(Solo personal sin asignaciones activas)</span></label>
                <select name="id_personal" required>
                    <option value="" disabled selected>Seleccionar Personal</option>
                    <?php
                    // Solo mostrar personal que NO tiene asignaciones activas
                    $stmt = $conn->query("
                        SELECT 
                            p.id_personal, 
                            s.nombre, 
                            s.apellido_paterno, 
                            s.apellido_materno, 
                            cp.nombre_puesto,
                            p.num_empleado
                        FROM personal p
                        INNER JOIN solicitud s ON p.id_solicitud = s.id_solicitud
                        LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
                        WHERE p.estatus_laboral = 'activo'
                        AND p.id_personal NOT IN (
                            SELECT id_personal 
                            FROM asignaciones 
                            WHERE estatus_asignacion IN ('activa', 'en_progreso')
                        )
                        ORDER BY s.apellido_paterno, s.nombre
                    ");
                    
                    $personal_disponible = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($personal_disponible) > 0):
                        foreach ($personal_disponible as $row):
                            $nombre_completo = $row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno'];
                            $puesto = $row['nombre_puesto'] ? ' (' . $row['nombre_puesto'] . ')' : '';
                            $num_empleado = $row['num_empleado'] ? ' [Empleado: ' . $row['num_empleado'] . ']' : '';
                            echo "<option value='" . $row['id_personal'] . "'>" 
                                . htmlspecialchars($nombre_completo . $puesto . $num_empleado) 
                                . "</option>";
                        endforeach;
                    else:
                        echo "<option value='' disabled>No hay personal disponible</option>";
                    endif;
                    ?>
                </select>

                <!-- Campo oculto para rol -->
                <input type="hidden" name="rol" value="personal_asignado">

                <!-- Mensaje si no hay personal disponible -->
                <?php if (count($personal_disponible) == 0): ?>
                    <div class="alert-warning" style="margin-top: 10px;">
                        ⚠️ No hay personal disponible en este momento. Todos los empleados activos ya están asignados a campañas.
                    </div>
                <?php endif; ?>

                <button type="submit" <?php echo (count($personal_disponible) == 0) ? 'disabled' : ''; ?>>
                    Asignar Personal a Campaña
                </button>
            </form>
        </section>

        <!-- ===== TABLA DE ASIGNACIONES ===== -->
        <section class="table-section">
            <h2>Asignaciones Recientes</h2>
            
            <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="search" id="searchAsignaciones" placeholder="Buscar asignaciones..." 
                       style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 3px;">
                
                <select id="filtro_estatus" style="padding: 10px; border: 1px solid #ddd; border-radius: 3px;">
                    <option value="">Todos los estatus</option>
                    <option value="activa">Activas</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="finalizada">Finalizadas</option>
                    <option value="completada">Completadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
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
                        <th>Marca / Tipo</th>
                        <th>Fecha Asignación</th>
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
                            c.nombre_campaña,
                            c.estatus AS estatus_campana,
                            m.nombre AS marca_nombre,
                            tc.nombre AS tipo_campaña
                        FROM asignaciones a
                        INNER JOIN personal p ON a.id_personal = p.id_personal
                        INNER JOIN solicitud s ON p.id_solicitud = s.id_solicitud
                        LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
                        INNER JOIN responsables r ON a.id_responsable = r.id_responsable
                        INNER JOIN campañas c ON a.id_campaña = c.id_campaña
                        INNER JOIN marcas m ON c.marca_id = m.id_marca
                        INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
                        ORDER BY a.fecha_asignacion DESC
                        LIMIT 50
                    ";
                    
                    $asignaciones = $conn->query($sql_asignaciones)->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($asignaciones) > 0):
                        foreach ($asignaciones as $asig): 
                            $nombre_completo = $asig['nombre'] . ' ' . $asig['apellido_paterno'] . ' ' . $asig['apellido_materno'];
                            
                            // Determinar clase de estatus
                            if ($asig['estatus_asignacion'] == 'activa') {
                                $estatus_class = 'estatus-activa';
                            } elseif ($asig['estatus_asignacion'] == 'en_progreso') {
                                $estatus_class = 'estatus-en_progreso';
                            } elseif ($asig['estatus_asignacion'] == 'pendiente') {
                                $estatus_class = 'estatus-pendiente';
                            } elseif ($asig['estatus_asignacion'] == 'finalizada' || $asig['estatus_asignacion'] == 'completada') {
                                $estatus_class = 'estatus-finalizada';
                            } else {
                                $estatus_class = 'estatus-inactiva';
                            }
                    ?>
                            <tr>
                                <td><?php echo $asig['id_asignacion']; ?></td>
                                <td><?php echo htmlspecialchars($asig['num_empleado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($nombre_completo); ?></td>
                                <td><?php echo htmlspecialchars($asig['nombre_puesto'] ?? 'Sin puesto'); ?></td>
                                <td><?php echo htmlspecialchars($asig['responsable_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($asig['nombre_campaña']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($asig['marca_nombre']); ?><br>
                                    <small><?php echo htmlspecialchars($asig['tipo_campaña']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($asig['fecha_asignacion'])); ?></td>
                                <td>
                                    <span class="<?php echo $estatus_class; ?>"><?php echo ucfirst($asig['estatus_asignacion']); ?></span>
                                    <br>
                                    <small>Campaña: <?php echo ucfirst($asig['estatus_campana']); ?></small>
                                </td>
                            </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 30px;">
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
            filtrarTabla();
        });

        document.getElementById('filtro_estatus')?.addEventListener('change', function() {
            filtrarTabla();
        });

        function filtrarTabla() {
            var searchText = document.getElementById('searchAsignaciones').value.toLowerCase();
            var filtroEstatus = document.getElementById('filtro_estatus').value.toLowerCase();
            var rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                var estatusCelda = row.querySelector('td:last-child span:first-child');
                var estatus = estatusCelda ? estatusCelda.textContent.toLowerCase().trim() : '';
                
                var coincideTexto = text.includes(searchText);
                var coincideEstatus = filtroEstatus === '' || estatus.includes(filtroEstatus);
                
                row.style.display = coincideTexto && coincideEstatus ? '' : 'none';
            });
        }
    </script>
</body>
</html>