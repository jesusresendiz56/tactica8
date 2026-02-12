<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: campañas.php');
    exit();
}

$id_campaña = $_GET['id'];

require_once '../Modelo/SupaConexion.php';
$db = new SupaConexion();
$conn = $db->getConexion();

/* =========================
   OBTENER CAMPAÑA
========================= */
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

/* =========================
   OBTENER PERSONAL ASIGNADO
========================= */
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

<link rel="stylesheet" href="../src/estilos/estilos.css">

<style>
/* =========================
   CONTENIDO SIN MENÚ
========================= */
.content.no-menu {
    margin-left: 0;
    max-width: 1200px;
    margin: 100px auto 40px auto;
}

/* =========================
   HEADER PÁGINA
========================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h1 {
    color: #ec1f27;
    margin-bottom: 5px;
}

.subtitle {
    color: #666;
    font-size: 14px;
}

/* =========================
   BOTÓN
========================= */
.btn-secondary {
    background-color: #6c757d;
    color: #fff;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.2s;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* =========================
   CARD CAMPAÑA
========================= */
.campaign-card {
    background: #ffffff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    margin-bottom: 35px;
    border-top: 4px solid #ec1f27;
}

.campaign-title {
    font-size: 20px;
    font-weight: bold;
    color: #ec1f27;
    margin-bottom: 20px;
}

.campaign-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.label {
    display: block;
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
    text-transform: uppercase;
}

/* BADGE */
.badge {
    background-color: #ec1f27;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

/* =========================
   TABLE
========================= */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.search-input {
    max-width: 250px;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid #ccc;
    transition: 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #ec1f27;
    box-shadow: 0 0 5px rgba(236,31,39,0.3);
}
</style>

</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-logo">
        <a href="campañas.php">
            <img src="../src/imagenes/tactica_logo.png"
                 alt="TÁCTICA 8"
                 class="logo-img"
                 width="100">
        </a>
    </div>

    <div class="header-center-text">
        <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
        Más de 40 años de experiencia.
    </div>

    <div class="header-exit">
        <div style="text-align:right;">
            <div style="font-weight:bold;">
                <?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?>
            </div>
            <div style="font-size:12px;">
                <?php echo $_SESSION['correo'] ?? ''; ?>
            </div>
        </div>
        <a href="../Controlador/logout.php" 
           onclick="return confirm('¿Cerrar sesión?')">
            <img src="../src/imagenes/logout.png"
                 alt="Salir"
                 width="30">
        </a>
    </div>
</header>

<main class="content no-menu">

    <div class="page-header">
        <div>
            <h1>Personal de Campaña</h1>
            <p class="subtitle">Gestión del equipo asignado</p>
        </div>

        <a href="campañas.php" class="btn-secondary">
            ← Volver a Campañas
        </a>
    </div>

    <section class="campaign-card">
        <div class="campaign-title">
            <?php echo htmlspecialchars($campaña['nombre_campaña']); ?>
        </div>

        <div class="campaign-grid">
            <div>
                <span class="label">Marca</span>
                <?php echo htmlspecialchars($campaña['marca']); ?>
            </div>

            <div>
                <span class="label">Tipo</span>
                <?php echo htmlspecialchars($campaña['tipo_campaña']); ?>
            </div>

            <div>
                <span class="label">Responsable</span>
                <?php echo htmlspecialchars($campaña['responsable']); ?>
            </div>

            <div>
                <span class="label">Estatus</span>
                <span class="badge">
                    <?php echo ucfirst($campaña['estatus']); ?>
                </span>
            </div>
        </div>
    </section>

    <section class="table-section">
        <div class="table-header">
            <h2>Personal Asignado (<?php echo count($asignados); ?>)</h2>

            <input type="search" 
                   id="searchAsignados"
                   class="search-input"
                   placeholder="Buscar personal...">
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
    <td><?php echo ucfirst($asignado['estatus_asignacion']); ?></td>
</tr>

<?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="7" style="text-align:center; padding:30px;">
        No hay personal asignado a esta campaña.
    </td>
</tr>
<?php endif; ?>

            </tbody>
        </table>
    </section>

</main>

<script>
document.getElementById('searchAsignados').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let rows = document.querySelectorAll('tbody tr');

    rows.forEach(function(row) {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script>

</body>
</html>

