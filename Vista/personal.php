<?php
// personal.php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// INCLUIR CONEXIÓN
require_once '../Modelo/SupaConexion.php';
$db = new SupaConexion();
$conn = $db->getConexion();

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Administrador';
$usuario_correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'admin@gmail.com';

// Obtener personal desde la base de datos - CORREGIDO SEGÚN TU ESQUEMA
$sql = "
    SELECT 
        p.id_personal,
        p.num_empleado,
        p.cuenta_nomina,
        p.contrato_url,
        p.fecha_alta,
        p.estatus_laboral,
        s.nombre,
        s.apellido_paterno,
        s.apellido_materno,
        s.id_puesto,
        cp.nombre_puesto
    FROM personal p
    LEFT JOIN solicitud s ON p.id_solicitud = s.id_solicitud
    LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
    ORDER BY p.fecha_alta DESC
";

$stmt = $conn->query($sql);
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar personal activo e inactivo
$activos = 0;
$inactivos = 0;
foreach ($personal as $empleado) {
    if ($empleado['estatus_laboral'] == 'activo') {
        $activos++;
    } else {
        $inactivos++;
    }
}
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

        <!-- USUARIO Y LOGOUT -->
        <div class="header-exit">
            <div class="user-info" style="margin-right: 15px; text-align: right;">
                <span class="user-name" style="display: block; color: white; font-weight: bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span class="user-email" style="display: block; color: white; font-size: 12px; opacity: 0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
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

    <!-- ===== CONTENIDO ===== -->
    <main class="content">
        <!-- Mensaje de éxito -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 'nuevo_personal'): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c3e6cb;">
                ✅ Personal agregado exitosamente desde solicitudes.
            </div>
        <?php endif; ?>

        <section class="form-section">
            <h1>Gestión de Personal</h1>
            
            <!-- Contadores -->
            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div style="background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 5px solid #2196f3;">
                    <span style="font-size: 24px; font-weight: bold;"><?php echo count($personal); ?></span>
                    <span style="display: block; color: #666;">Total Personal</span>
                </div>
                <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 5px solid #4caf50;">
                    <span style="font-size: 24px; font-weight: bold;"><?php echo $activos; ?></span>
                    <span style="display: block; color: #666;">Activos</span>
                </div>
                <div style="background: #ffebee; padding: 15px; border-radius: 5px; border-left: 5px solid #f44336;">
                    <span style="font-size: 24px; font-weight: bold;"><?php echo $inactivos; ?></span>
                    <span style="display: block; color: #666;">Inactivos</span>
                </div>
            </div>

            <form method="POST" action="../Controlador/engine_personal.php">
                <label>Nombre</label>
                <input type="text" name="nombre" required placeholder="Nombre completo">

                <label>Teléfono</label>
                <input type="text" name="telefono" placeholder="10 dígitos">

                <label>Correo Electrónico</label>
                <input type="email" name="correo" required placeholder="ejemplo@email.com">

                <label>Puesto</label>
                <select name="id_puesto" required>
                    <option value="" disabled selected>Seleccionar Puesto</option>
                    <?php
                    $puestos = $conn->query("SELECT id_puesto, nombre_puesto FROM cat_puestos ORDER BY nombre_puesto");
                    foreach ($puestos as $puesto) {
                        echo "<option value='" . $puesto['id_puesto'] . "'>" . htmlspecialchars($puesto['nombre_puesto']) . "</option>";
                    }
                    ?>
                </select>

                <label>Estatus</label>
                <select name="estatus">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>

                <button type="submit">Guardar Personal</button>
            </form>
        </section>

        <!-- ===== TABLA ===== -->
        <section class="table-section">
            <h2>Personal Existente</h2>

            <div style="margin-bottom: 20px;">
                <input type="search" id="searchPersonal" placeholder="Buscar por nombre o puesto..." 
                       style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 3px;">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Estatus</th>
                        <th>Fecha Alta</th>
                        <th>Contrato</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($personal) > 0): ?>
                        <?php foreach ($personal as $empleado): 
                            $nombre_completo = trim(
                                ($empleado['nombre'] ?? '') . ' ' . 
                                ($empleado['apellido_paterno'] ?? '') . ' ' . 
                                ($empleado['apellido_materno'] ?? '')
                            );
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['num_empleado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($nombre_completo ?: 'Sin nombre'); ?></td>
                                <td><?php echo htmlspecialchars($empleado['nombre_puesto'] ?? 'Sin puesto'); ?></td>
                                <td>
                                    <span style="background: <?php echo $empleado['estatus_laboral'] == 'activo' ? '#4caf50' : '#f44336'; ?>; 
                                                 color: white; padding: 3px 10px; border-radius: 3px; font-size: 12px;">
                                        <?php echo ucfirst($empleado['estatus_laboral'] ?? 'inactivo'); ?>
                                    </span>
                                </td>
                                <td><?php echo $empleado['fecha_alta'] ? date('d/m/Y', strtotime($empleado['fecha_alta'])) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($empleado['contrato_url']): ?>
                                        <a href="<?php echo htmlspecialchars($empleado['contrato_url']); ?>" 
                                           style="color: #2196f3; text-decoration: none;" target="_blank">
                                            Ver contrato
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999;">Sin contrato</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                No hay personal registrado. Acepta solicitudes desde el módulo de Solicitudes.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>    
    
    <script>
        document.getElementById('searchPersonal').addEventListener('keyup', function() {
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