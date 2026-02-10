<?php
// campañas.php - VERSIÓN INTEGRADA Y CORREGIDA
session_start();

// VERIFICACIÓN DE SESIÓN - CORREGIDA
// Verifica la variable correcta que guardas en validar_login.php
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
    <link rel="stylesheet" href="../src/estilos/campañas.css">
    <style>
        /* Estilos adicionales para el header de usuario */
        .header-user {
            display: flex;
            align-items: center;
            color: white;
            text-align: right;
        }
        .user-info {
            margin-right: 15px;
        }
        .user-name {
            font-weight: bold;
            display: block;
        }
        .user-email {
            font-size: 12px;
            opacity: 0.8;
            display: block;
        }
        .logout-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .logout-link:hover {
            opacity: 0.8;
        }
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

    <!-- HEADER DE USUARIO CORREGIDO -->
    <div class="header-user">
        <div class="user-info">
            <span class="user-name">
                <?php 
                // Mostrar nombre del usuario
                if (isset($_SESSION['usuario_nombre'])) {
                    echo htmlspecialchars($_SESSION['usuario_nombre']);
                } else {
                    echo 'Usuario';
                }
                ?>
            </span>
            <span class="user-email">
                <?php 
                // Mostrar correo del usuario
                echo isset($_SESSION['correo']) ? htmlspecialchars($_SESSION['correo']) : '';
                ?>
            </span>
        </div>
        <a href="../Controlador/logout.php" 
           class="logout-link"
           onclick="return confirm('¿Estás seguro de cerrar sesión?')"
           title="Cerrar Sesión">
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
        <input type="text" name="nombre_campaña" required placeholder="Ej: Lanzamiento Primavera 2024">

        <label>Estatus</label>
        <select name="estatus">
            <option value="pendiente" selected>Pendiente</option>
            <option value="en_progreso">En Progreso</option>
            <option value="completada">Completada</option>
            <option value="cancelada">Cancelada</option>
        </select>

        <button type="submit">Guardar Campaña</button>
    </form>
</section>

<section class="table-section">
    <h2>Campañas Existentes</h2>

    <!-- Búsqueda -->
    <div style="margin-bottom: 20px;">
        <input type="search" id="searchInput" placeholder="Buscar campañas..." style="padding: 8px; width: 300px;">
    </div>

    <table id="campaignsTable">
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
            ORDER BY c.created_at DESC
        ";

        $stmt = $conn->query($sql);

        if ($stmt->rowCount() > 0) {
            foreach ($stmt as $row) {
                // Clase CSS según estatus
                $estatus_class = '';
                switch ($row['estatus']) {
                    case 'pendiente': $estatus_class = 'status-pending'; break;
                    case 'en_progreso': $estatus_class = 'status-in-progress'; break;
                    case 'completada': $estatus_class = 'status-completed'; break;
                    case 'cancelada': $estatus_class = 'status-cancelled'; break;
                }
                
                echo "
                <tr>
                    <td><strong>{$row['nombre_campaña']}</strong><br><small>{$row['marca']}</small></td>
                    <td>{$row['tipo']}</td>
                    <td>{$row['responsable']}</td>
                    <td><span class='{$estatus_class}'>{$row['estatus']}</span></td>
                    <td>
                        <a href='personal_campania.php?id={$row['id_campaña']}'>👥 Personal</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No hay campañas registradas</td></tr>";
        }
        ?>
        </tbody>
    </table>
</section>

</main>

<script>
    // Búsqueda en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const table = document.getElementById('campaignsTable');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const text = row.textContent.toLowerCase();
                    
                    if (text.indexOf(filter) > -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }
        
        // Confirmación antes de logout
        const logoutLink = document.querySelector('a[href*="logout.php"]');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de cerrar sesión?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>

</body>
</html>