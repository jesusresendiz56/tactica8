<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// Conexión a la base de datos
require_once '../Modelo/SupaConexion.php';
$db = new SupaConexion();
$conn = $db->getConexion();

// Obtener datos del usuario para mostrar en el header
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Administrador';
$usuario_correo = $_SESSION['correo'] ?? 'admin@gmail.com';

// ===== PROCESAR FORMULARIO DE RESPONSABLE =====
$mensaje_responsable = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_responsable'])) {
    if ($_POST['accion_responsable'] === 'agregar') {
        $nombre = trim($_POST['nombre'] ?? '');
        $puesto = trim($_POST['puesto'] ?? '');
        $estado = $_POST['estado'] ?? 'activo';
        
        if ($nombre && $puesto) {
            try {
                $stmt = $conn->prepare("INSERT INTO responsables (nombre, puesto, estado) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $puesto, $estado]);
                $mensaje_responsable = '<div class="alert-success" style="padding:10px; margin-bottom:15px;">✅ Responsable agregado correctamente</div>';
            } catch (PDOException $e) {
                $mensaje_responsable = '<div class="alert-error" style="padding:10px; margin-bottom:15px;">❌ Error al agregar: ' . $e->getMessage() . '</div>';
            }
        } else {
            $mensaje_responsable = '<div class="alert-error" style="padding:10px; margin-bottom:15px;">❌ Nombre y puesto son obligatorios</div>';
        }
    }
}

// Consulta para obtener personal con datos relacionados
$sql = "
    SELECT 
        p.id_personal,
        CONCAT('EMP', LPAD(p.id_personal::text, 5, '0')) as num_empleado,
        p.contrato_url,
        p.fecha_alta,
        p.estatus_laboral,
        s.nombre,
        s.apellido_paterno,
        s.apellido_materno,
        cp.nombre_puesto,
        (SELECT a.estatus_asignacion 
         FROM asignaciones a 
         WHERE a.id_personal = p.id_personal 
         AND a.estatus_asignacion IN ('activa','en_progreso')
         LIMIT 1) as estatus_asignacion
    FROM personal p
    LEFT JOIN solicitud s ON p.id_solicitud = s.id_solicitud
    LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
    ORDER BY p.fecha_alta DESC
";

$stmt = $conn->query($sql);
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contadores para estatus
$activos = 0;
$inactivos = 0;
$en_proceso = 0;

foreach ($personal as $empleado) {
    switch ($empleado['estatus_laboral']) {
        case 'activo':
            $activos++;
            break;
        case 'en_proceso':
            $en_proceso++;
            break;
        default:
            $inactivos++;
            break;
    }
}

// Obtener lista de responsables para mostrar
$responsables = $conn->query("SELECT id_responsable, nombre, puesto, estado FROM responsables ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Personal | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <link rel="icon" type="image/png" href="../src/imagenes/favicon.png">
    <script src="../src/js/seguridad.js" defer></script>
    <style>
        /* Estilos originales de personal.php */
        .badge-estatus {
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
            display: inline-block;
        }
        
        .btn-subir {
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
        }
        
        .btn-subir:hover {
            background-color: #45a049;
        }
        
        select.estatus-select {
            font-size: 12px;
            padding: 3px;
            border-radius: 3px;
            color: white;
            border: none;
        }
        
        .search-box {
            margin-bottom: 15px;
            padding: 8px;
            width: 300px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            padding: 15px;
            min-width: 120px;
            border-radius: 5px;
        }
        
        /* ===== NUEVOS ESTILOS PARA RESPONSABLES (dentro de form-section) ===== */
        .responsable-form {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        
        .responsable-form h3 {
            margin-top: 0;
            color: #ec1f27;
            border-bottom: 2px solid #ec1f27;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 14px;
        }
        
        .btn-agregar {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            height: 38px;
        }
        
        .btn-agregar:hover {
            background: #218838;
        }
        
        .responsables-mini-lista {
            margin-top: 15px;
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            padding: 10px;
        }
        
        .responsable-item {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 12px;
            margin: 2px 5px 2px 0;
            border-left: 3px solid #ec1f27;
        }
        
        .responsable-item.activo {
            border-left-color: #28a745;
        }
        
        .responsable-item.inactivo {
            border-left-color: #dc3545;
            opacity: 0.7;
        }
        
        /* Mantener estilos originales de tabla */
        .table-section {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .table-section h2 {
            color: #ec1f27;
            margin-bottom: 15px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background-color: #ec1f27;
            color: #ffffff;
            padding: 12px 15px;
            text-align: left;
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        
        th:last-child {
            border-right: none;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            border-right: 1px solid #eee;
        }
        
        td:last-child {
            border-right: none;
        }
        
        tr:hover td {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="header-logo">
            <a href="../index.php">
                <img src="../src/imagenes/tactica_logo.png" width="100" alt="TÁCTICA 8">
            </a>
        </div>

        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

        <div class="header-exit">
            <div style="margin-right:15px;text-align:right;">
                <span style="display:block;color:white;font-weight:bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span style="display:block;color:white;font-size:12px;opacity:0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
            <a href="../Controlador/logout.php" onclick="return confirm('¿Cerrar sesión?')">
                <img src="../src/imagenes/logout.png" width="30" height="30" alt="Salir">
            </a>
        </div>
    </header>

    <nav class="menu">
        <a href="../index.php">Dashboard</a>
        <a href="../Vista/campañas.php">Campañas</a>
        <a href="../Vista/personal.php" class="active">Personal</a>
        <a href="../Vista/asignaciones.php">Asignaciones</a>
        <a href="../Vista/reportes.php">Reportes</a>
        <a href="../Vista/solicitudes.php">Solicitudes</a>
    </nav>

    <main class="content">

        <!-- ===== PRIMER FORM-SECTION: ESTADÍSTICAS ===== -->
        <section class="form-section">
            <h1>Gestión de Personal</h1>

            <div class="stats-container">
                <div class="stat-card" style="background:#e3f2fd;border-left:5px solid #2196f3;">
                    <span style="font-size:24px;font-weight:bold;"><?php echo count($personal); ?></span>
                    <span style="display:block;color:#666;">Total</span>
                </div>

                <div class="stat-card" style="background:#e8f5e9;border-left:5px solid #4caf50;">
                    <span style="font-size:24px;font-weight:bold;"><?php echo $activos; ?></span>
                    <span style="display:block;color:#666;">Activos</span>
                </div>

                <div class="stat-card" style="background:#fff8e1;border-left:5px solid #ff9800;">
                    <span style="font-size:24px;font-weight:bold;"><?php echo $en_proceso; ?></span>
                    <span style="display:block;color:#666;">En Proceso</span>
                </div>

                <div class="stat-card" style="background:#ffebee;border-left:5px solid #f44336;">
                    <span style="font-size:24px;font-weight:bold;"><?php echo $inactivos; ?></span>
                    <span style="display:block;color:#666;">Inactivos</span>
                </div>
            </div>
        </section>

        <!-- ===== SEGUNDO FORM-SECTION: FORMULARIO DE RESPONSABLES ===== -->
        <section class="form-section">
            <div class="responsable-form">
                <h3>Agregar Nuevo Responsable / Coordinador</h3>
                
                <?php echo $mensaje_responsable; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="accion_responsable" value="agregar">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre completo *</label>
                            <input type="text" name="nombre" required placeholder="Ej: Juan Pérez López">
                        </div>
                        
                        <div class="form-group">
                            <label>Puesto *</label>
                            <input type="text" name="puesto" required placeholder="Ej: Coordinador de campañas">
                        </div>
                        
                        <div class="form-group">
                            <label>Estado inicial</label>
                            <select name="estado">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-agregar">Agregar Responsable</button>
                        </div>
                    </div>
                </form>
                
                <!-- Mini lista de responsables existentes -->
                <?php if (!empty($responsables)): ?>
                <div class="responsables-mini-lista">
                    <small style="color:#666;">Responsables registrados:</small><br>
                    <?php foreach ($responsables as $r): ?>
                        <span class="responsable-item <?php echo $r['estado']; ?>">
                            <?php echo htmlspecialchars($r['nombre']); ?> (<?php echo htmlspecialchars($r['puesto']); ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== TERCER SECTION: TABLA DE PERSONAL ===== -->
        <section class="table-section">
            <h2>Personal Existente</h2>

            <div>
                <input type="text" id="searchPersonal" class="search-box" placeholder="Buscar personal...">
            </div>

            <table id="tablaPersonal">
                <thead>
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Estatus Laboral</th>
                        <th>Estatus Asignación</th>
                        <th>Fecha Alta</th>
                        <th>Contrato</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($personal as $empleado):
                        $nombre_completo = trim(
                            ($empleado['nombre'] ?? '') . ' ' .
                            ($empleado['apellido_paterno'] ?? '') . ' ' .
                            ($empleado['apellido_materno'] ?? '')
                        );

                        // Colores para estatus laboral
                        $color_laboral = '#f44336'; // inactivo
                        if ($empleado['estatus_laboral'] == 'activo') $color_laboral = '#4caf50';
                        if ($empleado['estatus_laboral'] == 'en_proceso') $color_laboral = '#ff9800';

                        // Colores para estatus asignación
                        $estatus_asig = $empleado['estatus_asignacion'] ?? null;
                        $color_asig = '#999'; // sin asignación
                        if ($estatus_asig == 'activa') $color_asig = '#4caf50';
                        if ($estatus_asig == 'en_progreso') $color_asig = '#ff9800';
                    ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($empleado['num_empleado'] ?? 'N/A'); ?><br>
                                <small style="color:#999;">ID: <?php echo $empleado['id_personal']; ?></small>
                            </td>

                            <td><?php echo htmlspecialchars($nombre_completo ?: 'Sin nombre'); ?></td>
                            
                            <td><?php echo htmlspecialchars($empleado['nombre_puesto'] ?? 'Sin puesto'); ?></td>

                            <!-- ESTATUS LABORAL EDITABLE -->
                            <td>
                                <form action="../Controlador/cambiar_estatus_laboral.php" method="POST" style="margin:0;">
                                    <input type="hidden" name="id_personal" value="<?php echo $empleado['id_personal']; ?>">
                                    
                                    <select name="nuevo_estatus" class="estatus-select" style="background:<?php echo $color_laboral; ?>;">
                                        <option value="activo" <?php if ($empleado['estatus_laboral'] == 'activo') echo 'selected'; ?>>Activo</option>
                                        <option value="en_proceso" <?php if ($empleado['estatus_laboral'] == 'en_proceso') echo 'selected'; ?>>En Proceso</option>
                                        <option value="inactivo" <?php if ($empleado['estatus_laboral'] == 'inactivo') echo 'selected'; ?>>Inactivo</option>
                                    </select>
                                    
                                    <button type="submit" style="margin-left:5px; padding:3px 8px; font-size:11px;">✓</button>
                                </form>
                            </td>

                            <!-- ESTATUS ASIGNACIÓN -->
                            <td>
                                <?php if (!$estatus_asig): ?>
                                    <span style="color:#999;">Sin asignación</span>
                                <?php else: ?>
                                    <span class="badge-estatus" style="background:<?php echo $color_asig; ?>;">
                                        <?php echo ucfirst($estatus_asig); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo $empleado['fecha_alta'] ? date('d/m/Y', strtotime($empleado['fecha_alta'])) : 'N/A'; ?>
                            </td>

                            <!-- CONTRATO CON SUBIDA DE ARCHIVO -->
                            <td>
                                <?php if ($empleado['contrato_url']): ?>
                                    <a href="<?php echo htmlspecialchars($empleado['contrato_url']); ?>" target="_blank" style="display:block; margin-bottom:5px;">
                                        📄 Ver contrato
                                    </a>
                                <?php else: ?>
                                    <span style="color:#999; display:block; margin-bottom:5px;">Sin contrato</span>
                                <?php endif; ?>

                                <form action="../Controlador/subir_contrato.php"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    style="margin:0;">

                                    <input type="hidden"
                                        name="id_personal"
                                        value="<?php echo $empleado['id_personal']; ?>">

                                    <input type="file"
                                        name="contrato"
                                        required
                                        style="font-size:11px; margin-bottom:5px; width:100%;">

                                    <button type="submit" class="btn-subir">
                                        Subir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Búsqueda en tiempo real
        document.getElementById('searchPersonal').addEventListener('keyup', function() {
            let searchText = this.value.toLowerCase();
            let rows = document.querySelectorAll('#tablaPersonal tbody tr');
            
            rows.forEach(function(row) {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    </script>
</body>
</html>