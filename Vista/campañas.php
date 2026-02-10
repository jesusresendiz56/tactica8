<?php
session_start();
require_once '../Modelo/SupaConexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Campañas | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/campañas.css">
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
        <a href="login.php">
            <img src="../src/imagenes/logout.png" width="30">
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
                echo "<option value='{$row['id_marca']}'>{$row['nombre']}</option>";
            }
            ?>
        </select>

        <label>Tipo de Campaña</label>
        <select name="tipo_campaña_id" required>
            <option value="" disabled selected>Seleccionar Tipo</option>
            <?php
            $stmt = $conn->query("SELECT id_tipo, nombre FROM tipos_campaña ORDER BY nombre");
            foreach ($stmt as $row) {
                echo "<option value='{$row['id_tipo']}'>{$row['nombre']}</option>";
            }
            ?>
        </select>

        <label>Responsable</label>
        <select name="responsable_id" required>
            <option value="" disabled selected>Seleccionar Responsable</option>
            <?php
            $stmt = $conn->query("SELECT id_responsable, nombre FROM responsables WHERE estado='activo' ORDER BY nombre");
            foreach ($stmt as $row) {
                echo "<option value='{$row['id_responsable']}'>{$row['nombre']}</option>";
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
                m.nombre AS marca,
                tc.nombre AS tipo,
                r.nombre AS responsable
            FROM campañas c
            INNER JOIN marcas m ON c.marca_id = m.id_marca
            INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id_tipo
            INNER JOIN responsables r ON c.responsable_id = r.id_responsable
            ORDER BY c.fecha_registro DESC
        ";

        $stmt = $conn->query($sql);

        if ($stmt->rowCount() > 0) {
            foreach ($stmt as $row) {
                echo "
                <tr>
                    <td><strong>{$row['nombre_campaña']}</strong><br><small>{$row['marca']}</small></td>
                    <td>{$row['tipo']}</td>
                    <td>{$row['responsable']}</td>
                    <td>{$row['estatus']}</td>
                    <td>
                        <a href='personal_campania.php?id={$row['id_campaña']}'>👥 Personal</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No hay campañas registradas</td></tr>";
        }
        ?>
        </tbody>
    </table>
</section>

</main>

</body>
</html>

