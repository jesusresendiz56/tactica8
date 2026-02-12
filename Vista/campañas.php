<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

require_once '../Modelo/SupaConexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Campañas | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <style>
        .header-user { display: flex; align-items: center; color: white; text-align: right; }
        .user-info { margin-right: 15px; }
        .user-name { font-weight: bold; display: block; }
        .user-email { font-size: 12px; opacity: 0.8; display: block; }
        .logout-link { color: white; text-decoration: none; display: flex; align-items: center; }
        .logout-link:hover { opacity: 0.8; }

        .status-pending { color: #f39c12; font-weight: bold; }
        .status-in-progress { color: #3498db; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .status-cancelled { color: #e74c3c; font-weight: bold; }
    </style>
</head>

<body>

<header class="header">
    <div class="header-logo">
        <a href="dashboard.php">
            <img src="../src/imagenes/tactica_logo.png" width="100" alt="TÁCTICA 8">
        </a>
    </div>

    <div class="header-center-text">
        <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
        Más de 40 años de experiencia.
    </div>

    <div class="header-user">
        <div class="user-info">
            <span class="user-name">
                <?php echo isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Usuario'; ?>
            </span>
            <span class="user-email">
                <?php echo isset($_SESSION['correo']) ? htmlspecialchars($_SESSION['correo']) : ''; ?>
            </span>
        </div>
        <a href="../Controlador/logout.php" class="logout-link"
           onclick="return confirm('¿Estás seguro de cerrar sesión?')">
            <img src="../src/imagenes/logout.png" width="30" alt="Cerrar Sesión">
        </a>
    </div>
</header>

<nav class="menu">
    <a href="dashboard.php">Dashboard</a>
    <a href="campañas.php" class="active">Campañas</a>
    <a href="personal.php">Personal</a>
    <a href="asignaciones.php">Asignaciones</a>
    <a href="reportes.php">Reportes</a>
    <a href="solicitudes.php">Solicitudes</a>
</nav>

<main class="content">

<section class="form-section">
    <h1>Gestión de Campañas</h1>

    <form method="POST" action="../Controlador/engine_campañas.php">

        <label>Marca</label>
        <select name="marca_id" required>
            <option value="" disabled selected>Seleccionar Marca</option>
            <?php
            $stmt = $conn->query("SELECT id_marca, nombre FROM marcas WHERE estado='activa' ORDER BY nombre");
            foreach ($stmt as $row) {
                echo "<option value='{$row['id_marca']}'>" . htmlspecialchars($row['nombre']) . "</option>";
            }
            ?>
        </select>

        <label>Tipo de Campaña</label>
        <select name="tipo_campaña_id" required>
            <option value="" disabled selected>Seleccionar Tipo</option>
            <?php
            $stmt = $conn->query("SELECT id_tipo, nombre FROM tipos_campaña ORDER BY nombre");
            foreach ($stmt as $row) {
                echo "<option value='{$row['id_tipo']}'>" . htmlspecialchars($row['nombre']) . "</option>";
            }
            ?>
        </select>

        <label>Responsable</label>
        <select name="responsable_id" required>
            <option value="" disabled selected>Seleccionar Responsable</option>
            <?php
            $stmt = $conn->query("SELECT id_responsable, nombre FROM responsables WHERE estado='activo' ORDER BY nombre");
            foreach ($stmt as $row) {
                echo "<option value='{$row['id_responsable']}'>" . htmlspecialchars($row['nombre']) . "</option>";
            }
            ?>
        </select>

        <label>Nombre de la Campaña</label>
        <input type="text" name="nombre_campaña" required>

        <label>Estatus</label>
        <select name="estatus">
            <option value="pendiente">Pendiente</option>
            <option value="en_progreso">En Progreso</option>
            <option value="completada">Completada</option>
            <option value="cancelada">Cancelada</option>
        </select>

        <label>Fecha de Inicio</label>
        <input type="date" name="fecha_inicio">

        <label>Fecha de Fin</label>
        <input type="date" name="fecha_fin">

        <button type="submit">Guardar Campaña</button>
    </form>
</section>

<section class="table-section">
    <h2>Campañas Existentes</h2>

    <table>
        <thead>
            <tr>
                <th>Campaña / Marca</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th>Fecha Registro</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estatus</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
<?php
$sql = "
    SELECT 
        c.id_campaña,
        c.nombre_campaña,
        c.estatus,
        c.fecha_registro,
        c.fecha_inicio,
        c.fecha_fin,
        m.nombre AS marca,
        tc.nombre AS tipo,
        r.nombre AS responsable
    FROM campañas c
    INNER JOIN marcas m ON c.marca_id = m.id_marca
    INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
    INNER JOIN responsables r ON c.responsable_id = r.id_responsable
    ORDER BY c.id_campaña DESC
";

$stmt = $conn->query($sql);

if ($stmt->rowCount() > 0) {
    foreach ($stmt as $row) {

        $estatus_class = match($row['estatus']) {
            'pendiente'   => 'status-pending',
            'en_progreso' => 'status-in-progress',
            'completada'  => 'status-completed',
            'cancelada'   => 'status-cancelled',
            default       => ''
        };

        echo "
        <tr>
            <td><strong>{$row['nombre_campaña']}</strong><br><small>{$row['marca']}</small></td>
            <td>{$row['tipo']}</td>
            <td>{$row['responsable']}</td>
            <td>" . date('d/m/Y H:i', strtotime($row['fecha_registro'])) . "</td>
            <td>" . ($row['fecha_inicio'] ? date('d/m/Y', strtotime($row['fecha_inicio'])) : '-') . "</td>
            <td>" . ($row['fecha_fin'] ? date('d/m/Y', strtotime($row['fecha_fin'])) : '-') . "</td>
            <td><span class='{$estatus_class}'>{$row['estatus']}</span></td>
            <td>
         <a href='personal_campaña.php?id={$row['id_campaña']}' title='Ver personal' style='text-decoration:none;'>
    <img src='../src/imagenes/personal.png' alt='Icono Personal' style='width:16px; height:16px; vertical-align:middle; border:none;'>
</a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8'>No hay campañas registradas</td></tr>";
}
?>
        </tbody>
    </table>
</section>

</main>
</body>
</html>


