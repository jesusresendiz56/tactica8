<?php
// Vista/campañas.php
session_start();
require_once '../Modelo/conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Campañas | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/campañas.css">
    <!-- Enlace al CSS de alertas -->
    <link rel="stylesheet" href="../src/estilos/alertas.css">
</head>

<body>
    
    
    <!-- ===== HEADER ===== -->
    <header class="header">
        <!-- Logo -->
        <div class="header-logo">
            <a href="dashboard.php">
                <img src="../src/imagenes/tactica_logo.png"
                     alt="TÁCTICA 8"
                     class="logo-img"
                     width="100"
                     height="100">
            </a>
        </div>

        <!-- Texto central -->
        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

        <!-- Salir -->
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

    <!-- ===== MENÚ ===== -->
    <nav class="menu">
        <a href="dashboard.html">Dashboard</a>
        <a href="campañas.php" class="active">Campañas</a>
        <a href="personal.html">Personal</a>
        <a href="asignaciones.html">Asignaciones</a>
        <a href="reportes.html">Reportes</a>
        <a href="solicitudes.html">Solicitudes</a>
    </nav>

    <!-- ===== FORMULARIO ===== -->
    <main class="content">
        <section class="form-section">
            <h1>Gestión de Campañas</h1>

            <form method="POST" action="../Controlador/engine_campañas.php" id="form-campania">
                
                <label>Campaña/Marca</label>
                <select name="marca_id" id="marca" required>
                    <option value="" disabled selected>Seleccionar Marca</option>
                    <?php
                    $sql_marcas = "SELECT id, nombre FROM marcas WHERE estado = 'activa' ORDER BY nombre";
                    $result_marcas = mysqli_query($conn, $sql_marcas);
                    while($marca = mysqli_fetch_assoc($result_marcas)) {
                        echo "<option value='{$marca['id']}'>{$marca['nombre']}</option>";
                    }
                    ?>
                </select>

                <label>Tipo de Campaña</label>
                <select name="tipo_campaña_id" required>
                    <option value="" disabled selected>Seleccionar Tipo</option>
                    <?php
                    $sql_tipos = "SELECT id, nombre FROM tipos_campaña ORDER BY nombre";
                    $result_tipos = mysqli_query($conn, $sql_tipos);
                    while($tipo = mysqli_fetch_assoc($result_tipos)) {
                        echo "<option value='{$tipo['id']}'>{$tipo['nombre']}</option>";
                    }
                    ?>
                </select>

                <label>Responsable</label>
                <select name="responsable_id" required>
                    <option value="" disabled selected>Seleccionar Responsable</option>
                    <?php
                    $sql_responsables = "SELECT id, nombre FROM responsables WHERE estado = 'activo' ORDER BY nombre";
                    $result_responsables = mysqli_query($conn, $sql_responsables);
                    while($responsable = mysqli_fetch_assoc($result_responsables)) {
                        echo "<option value='{$responsable['id']}'>{$responsable['nombre']}</option>";
                    }
                    ?>
                </select>

                <label>Nombre de la Campaña</label>
                <input type="text" name="nombre_campaña" placeholder="Ej: Lanzamiento Primavera 2024" required>

                <label>Estatus</label>
                <select name="estatus">
                    <option value="pendiente" selected>Pendiente</option>
                    <option value="en_progreso">En Progreso</option>
                    <option value="completada">Completada</option>
                    <option value="cancelada">Cancelada</option>
                </select>

                <button type="submit" class="btn-guardar">Guardar Campaña</button>
            </form>
        </section>

        <!-- ===== TABLA ===== -->
        <section class="table-section">
            <h2>Campañas Existentes</h2>

            <div class="search-container">
                <label>
                    Buscar Campañas:
                    <img src="../src/imagenes/buscar_campañas.png"
                         alt="Buscar"
                         class="search-icon"
                         width="20"
                         height="20">
                </label>
                
                <div class="search-box">
                    <input type="search" placeholder="Buscar por nombre, marca o responsable..." id="buscar-campania">
                </div>
            </div>

            <table id="tabla-campanias">
                <thead>
                    <tr>
                        <th>Campaña / Marca</th>
                        <th>Tipo</th>
                        <th>Coordinador</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_campanias = "SELECT 
                                        c.id,
                                        c.nombre_campaña,
                                        m.nombre as marca_nombre,
                                        tc.nombre as tipo_nombre,
                                        r.nombre as responsable_nombre,
                                        c.estatus
                                      FROM campañas c
                                      INNER JOIN marcas m ON c.marca_id = m.id
                                      INNER JOIN tipos_campaña tc ON c.tipo_campaña_id = tc.id
                                      INNER JOIN responsables r ON c.responsable_id = r.id
                                      ORDER BY c.fecha_registro DESC";
                    
                    $result_campanias = mysqli_query($conn, $sql_campanias);
                    
                    if (mysqli_num_rows($result_campanias) > 0) {
                        while($campania = mysqli_fetch_assoc($result_campanias)) {
                            $estatus_class = 'estatus-' . $campania['estatus'];
                            $estatus_text = ucfirst(str_replace('_', ' ', $campania['estatus']));
                            
                            echo "<tr>
                                    <td>
                                        <strong>{$campania['nombre_campaña']}</strong><br>
                                        <small>{$campania['marca_nombre']}</small>
                                    </td>
                                    <td>{$campania['tipo_nombre']}</td>
                                    <td>{$campania['responsable_nombre']}</td>
                                    <td><span class='{$estatus_class}'>{$estatus_text}</span></td>
                                    <td>
                                        <a href='personal_campania.php?id={$campania['id']}' class='btn-action'>
                                            👥 Personal
                                        </a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr>
                                <td colspan='5' style='text-align: center; padding: 20px;'>
                                    <em>No hay campañas registradas. Crea tu primera campaña.</em>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Enlace al archivo JS separado -->
    <script src="../src/js/campañas.js"></script>

</body>
</html>

<?php
mysqli_close($conn);
?>