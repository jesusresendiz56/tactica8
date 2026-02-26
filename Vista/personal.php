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

// Consulta para obtener personal con datos relacionados

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
        cp.nombre_puesto,
        a.estatus_asignacion
    FROM personal p
    LEFT JOIN solicitud s ON p.id_solicitud = s.id_solicitud
    LEFT JOIN cat_puestos cp ON s.id_puesto = cp.id_puesto
    LEFT JOIN asignaciones a 
        ON p.id_personal = a.id_personal 
        AND a.estatus_asignacion IN ('activa','en_progreso')
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
        case 'activo': $activos++; break;
        case 'en_proceso': $en_proceso++; break;
        default: $inactivos++; break;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Personal | TÁCTICA 8</title>
<link rel="stylesheet" href="../src/estilos/estilos.css">
</head>

<body>

<header class="header">
<div class="header-logo">
<a href="dashboard.php">
<img src="../src/imagenes/tactica_logo.png" width="100">
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
<img src="../src/imagenes/logout.png" width="30" height="30">
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

<section class="form-section">
<h1>Gestión de Personal</h1>

<div style="display:flex; gap:20px; margin-bottom:30px;">

<div style="background:#e3f2fd;padding:15px;border-left:5px solid #2196f3;">
<span style="font-size:24px;font-weight:bold;"><?php echo count($personal); ?></span>
<span style="display:block;color:#666;">Total</span>
</div>

<div style="background:#e8f5e9;padding:15px;border-left:5px solid #4caf50;">
<span style="font-size:24px;font-weight:bold;"><?php echo $activos; ?></span>
<span style="display:block;color:#666;">Activos</span>
</div>

<div style="background:#fff8e1;padding:15px;border-left:5px solid #ff9800;">
<span style="font-size:24px;font-weight:bold;"><?php echo $en_proceso; ?></span>
<span style="display:block;color:#666;">En Proceso</span>
</div>

<div style="background:#ffebee;padding:15px;border-left:5px solid #f44336;">
<span style="font-size:24px;font-weight:bold;"><?php echo $inactivos; ?></span>
<span style="display:block;color:#666;">Inactivos</span>
</div>

</div>
</section>

<section class="table-section">
<h2>Personal Existente</h2>

<table>
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

/* Colores */
$color_laboral = '#f44336';
if ($empleado['estatus_laboral'] == 'activo') $color_laboral = '#4caf50';
if ($empleado['estatus_laboral'] == 'en_proceso') $color_laboral = '#ff9800';

$estatus_asig = $empleado['estatus_asignacion'] ?? null;
$color_asig = '#999';
if ($estatus_asig == 'activa') $color_asig = '#4caf50';
if ($estatus_asig == 'en_progreso') $color_asig = '#ff9800';
?>

<tr>

<td>
<?php echo htmlspecialchars($empleado['num_empleado']); ?><br>
<small>ID: <?php echo $empleado['id_personal']; ?></small>
</td>

<td><?php echo htmlspecialchars($nombre_completo); ?></td>
<td><?php echo htmlspecialchars($empleado['nombre_puesto'] ?? 'Sin puesto'); ?></td>

<!-- ESTATUS LABORAL EDITABLE -->
<td>
<form action="../Controlador/cambiar_estatus_laboral.php" method="POST" style="margin:0;">
<input type="hidden" name="id_personal" value="<?php echo $empleado['id_personal']; ?>">

<select name="nuevo_estatus"
style="background:<?php echo $color_laboral; ?>;color:white;font-size:12px;padding:3px;border-radius:3px;">
<option value="activo" <?php if($empleado['estatus_laboral']=='activo') echo 'selected'; ?>>Activo</option>
<option value="en_proceso" <?php if($empleado['estatus_laboral']=='en_proceso') echo 'selected'; ?>>En Proceso</option>
<option value="inactivo" <?php if($empleado['estatus_laboral']=='inactivo') echo 'selected'; ?>>Inactivo</option>
</select>

<button type="submit" style="font-size:11px;">Guardar</button>
</form>
</td>

<!-- ESTATUS ASIGNACIÓN -->
<td>
<?php if (!$estatus_asig): ?>
<span style="color:#999;">Sin asignación</span>
<?php else: ?>
<span style="background:<?php echo $color_asig; ?>;color:white;padding:3px 10px;border-radius:3px;font-size:12px;">
<?php echo ucfirst($estatus_asig); ?>
</span>
<?php endif; ?>
</td>

<td>
<?php echo $empleado['fecha_alta'] ? date('d/m/Y', strtotime($empleado['fecha_alta'])) : 'N/A'; ?>
</td>

<!--  CONTRATO CON SUBIDA DE ARCHIVOZ -->
<td>

<?php if ($empleado['contrato_url']): ?>
<a href="<?php echo htmlspecialchars($empleado['contrato_url']); ?>" target="_blank">
Ver contrato
</a>
<br><br>
<?php else: ?>
<span style="color:#999;">Sin contrato</span>
<br><br>
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
style="font-size:12px; margin-bottom:5px;">

<button type="submit"
style="padding:4px 8px;font-size:12px;cursor:pointer;">
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
</body>
</html>