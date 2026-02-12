<?php
// personal_campania.php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// VERIFICAR QUE SE RECIBIÓ EL ID DE CAMPAÑA
if (!isset($_GET['id'])) {
    header('Location: campañas.php');
    exit();
}

$id_campaña = $_GET['id'];

// INCLUIR CONEXIÓN
require_once '../Modelo/SupaConexion.php';
$db = new SupaConexion();
$conn = $db->getConexion();

// Obtener información de la campaña
$sql_campaña = "
    SELECT 
        c.id_campaña,
        c.nombre_campaña,
        c.estatus,
        m.nombre AS marca,
        tc.nombre AS tipo_campaña,
        r.nombre AS responsable
    FROM campañas c
    INNER JOIN marcas m ON c.marca_id = m.id_marca
    INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
    INNER JOIN responsables r ON c.responsable_id = r.id_responsable
    WHERE c.id_campaña = :id
";

$stmt = $conn->prepare($sql_campaña);
$stmt->bindParam(':id', $id_campaña);
$stmt->execute();
$campaña = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaña) {
    header('Location: campañas.php');
    exit();
}

// Obtener personal asignado a la campaña
$sql_asignados = "
    SELECT 
        a.id_asignacion,
        a.rol,
        a.fecha_inicio,
        a.fecha_fin,
        a.estatus_asignacion,
        p.id_personal,
        p.num_empleado,
        s.nombre,
        s.apellido_paterno,
        s.apellido_materno,
        cp.nombre_puesto
    FROM asignaciones a
    INNER JOIN personal p ON a.id_personal = p.id_personal
    INNER JOIN solicitud s ON p.id_solicitud = s.id_solicitud
    LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
    WHERE a.id_campaña = :id
    ORDER BY a.fecha_asignacion DESC
";

$stmt = $conn->prepare($sql_asignados);
$stmt->bindParam(':id', $id_campaña);
$stmt->execute();
$asignados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Personal de Campaña | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/campañas.css">
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
        <a href="asignaciones.php">Asignaciones</a>
        <a href="reportes.php">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>
    </nav>

    <!-- ===== CONTENIDO ===== -->
    <main class="content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Personal de Campaña</h1>
            <a href="campañas.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                ← Volver a Campañas
            </a>
        </div>

        <!-- Información de la campaña -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px; border-left: 5px solid #007bff;">
            <h2 style="margin-top: 0; color: #007bff;"><?php echo htmlspecialchars($campaña['nombre_campaña']); ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Marca:</strong><br>
                    <?php echo htmlspecialchars($campaña['marca']); ?>
                </div>
                <div>
                    <strong>Tipo:</strong><br>
                    <?php echo htmlspecialchars($campaña['tipo_campaña']); ?>
                </div>
                <div>
                    <strong>Responsable:</strong><br>
                    <?php echo htmlspecialchars($campaña['responsable']); ?>
                </div>
                <div>
                    <strong>Estatus:</strong><br>
                    <span style="background: <?php 
                        echo $campaña['estatus'] == 'activa' ? '#28a745' : 
                            ($campaña['estatus'] == 'pendiente' ? '#ffc107' : '#dc3545'); 
                    ?>; color: <?php echo $campaña['estatus'] == 'pendiente' ? 'black' : 'white'; ?>; 
                    padding: 3px 10px; border-radius: 3px; display: inline-block;">
                        <?php echo ucfirst($campaña['estatus']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- SOLO TABLA DE PERSONAL ASIGNADO -->
        <section class="table-section">
            <h2>Personal Asignado (<?php echo count($asignados); ?>)</h2>

            <div style="margin-bottom: 20px;">
                <input type="search" id="searchAsignados" placeholder="Buscar personal asignado..." 
                       style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 3px;">
            </div>

            <table>
                <thead>
                    <tr>
                        <th>No. Empleado</th>
                        <th>Nombre</th>
                        <th>Puesto</th>
                        <th>Rol</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($asignados) > 0): ?>
                        <?php foreach ($asignados as $asignado): 
                            $nombre_completo = trim(
                                ($asignado['nombre'] ?? '') . ' ' . 
                                ($asignado['apellido_paterno'] ?? '') . ' ' . 
                                ($asignado['apellido_materno'] ?? '')
                            );
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($asignado['num_empleado'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($nombre_completo ?: 'Sin nombre'); ?></td>
                                <td><?php echo htmlspecialchars($asignado['nombre_puesto'] ?? 'Sin puesto'); ?></td>
                                <td><?php echo htmlspecialchars($asignado['rol'] ?? 'No especificado'); ?></td>
                                <td><?php echo $asignado['fecha_inicio'] ? date('d/m/Y', strtotime($asignado['fecha_inicio'])) : 'N/A'; ?></td>
                                <td><?php echo $asignado['fecha_fin'] ? date('d/m/Y', strtotime($asignado['fecha_fin'])) : 'N/A'; ?></td>
                                <td>
                                    <span style="background: <?php 
                                        echo $asignado['estatus_asignacion'] == 'activa' ? '#28a745' : '#dc3545'; 
                                    ?>; color: white; padding: 3px 10px; border-radius: 3px; font-size: 12px; display: inline-block;">
                                        <?php echo ucfirst($asignado['estatus_asignacion']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #666;">
                                No hay personal asignado a esta campaña.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        // Búsqueda en tiempo real
        document.getElementById('searchAsignados').addEventListener('keyup', function() {
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