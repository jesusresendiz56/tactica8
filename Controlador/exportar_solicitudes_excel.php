<?php
// Controlador/exportar_solicitudes_excel.php
session_start();
require_once '../Modelo/SupaConexion.php';

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Vista/login.php');
    exit();
}

// Obtener filtros si se enviaron
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

try {
    // Construir la consulta SQL base - CORREGIDA: INITCAP → INITCAP
    $sql = "
        SELECT 
            s.id_solicitud AS id,
            TRIM(CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, ''))) AS nombre_completo,
            p.nombre_puesto AS puesto,
            COALESCE(s.celular, s.telefono_casa, s.telefono_recados) AS telefono,
            'Inmediata' AS disponibilidad,
            INITCAP(s.estatus) AS estatus,  -- CORREGIDO: INITCAP en lugar de INITCAP
            TO_CHAR(s.fecha_registro, 'DD/MM/YYYY') AS fecha_solicitud
        FROM solicitud s
        LEFT JOIN cat_puestos p ON s.id_puesto = p.id_puesto
        WHERE 1=1
    ";

    $params = array();

    // Agregar filtro por estado
    if (!empty($filtro_estado)) {
        $sql .= " AND LOWER(s.estatus) = LOWER(:estado)";
        $params[':estado'] = $filtro_estado;
    }

    // Agregar filtro por búsqueda (nombre o puesto)
    if (!empty($filtro_busqueda)) {
        $sql .= " AND (
            LOWER(CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, ''))) LIKE :busqueda 
            OR LOWER(p.nombre_puesto) LIKE :busqueda2
        )";
        $params[':busqueda'] = '%' . strtolower($filtro_busqueda) . '%';
        $params[':busqueda2'] = '%' . strtolower($filtro_busqueda) . '%';
    }

    $sql .= " ORDER BY s.fecha_registro DESC";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre del archivo
    $filename = "solicitudes_" . date('Y-m-d_H-i-s') . ".xls";

    // Limpiar cualquier salida previa
    if (ob_get_level()) ob_end_clean();

    // Encabezados para descargar el archivo
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

    // Crear el contenido del archivo
    echo '<html>';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';

    // Información de los filtros aplicados - IGUAL A LA IMAGEN
    echo '<table border="0" cellpadding="2" cellspacing="0">';
    echo '<tr><td colspan="7" style="font-size: 14px; font-weight: bold;">Reporte de Solicitudes - TÁCTICA 8</td></tr>';
    echo '<tr><td colspan="7">Fecha de exportación: ' . date('d/m/Y H:i:s') . '</td></tr>';
    
    if (!empty($filtro_estado) || !empty($filtro_busqueda)) {
        echo '<tr><td colspan="7">Filtros aplicados: ';
        $filtros_aplicados = array();
        if (!empty($filtro_estado)) $filtros_aplicados[] = 'Estado: ' . ucfirst($filtro_estado);
        if (!empty($filtro_busqueda)) $filtros_aplicados[] = 'Búsqueda: "' . htmlspecialchars($filtro_busqueda) . '"';
        echo implode(' | ', $filtros_aplicados);
        echo '</td></tr>';
    }
    
    echo '<tr><td colspan="7">&nbsp;</td></tr>';
    echo '</table>';

    // Iniciar la tabla de datos
    echo '<table border="1" cellpadding="4" cellspacing="0">';

    // Encabezados de columna - IGUALES A LA IMAGEN
    echo '<tr style="background-color: #4CAF50; color: white; font-weight: bold;">';
    echo '<th>ID</th>';
    echo '<th>Nombre Completo</th>';
    echo '<th>Puesto</th>';
    echo '<th>Teléfono</th>';
    echo '<th>Disponibilidad</th>';
    echo '<th>Estatus</th>';
    echo '<th>Fecha de Solicitud</th>';
    echo '</tr>';

    // Datos
    if (count($solicitudes) > 0) {
        foreach ($solicitudes as $solicitud) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($solicitud['id']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['nombre_completo']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['puesto']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['telefono']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['disponibilidad']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['estatus']) . '</td>';
            echo '<td>' . htmlspecialchars($solicitud['fecha_solicitud']) . '</td>';
            echo '</tr>';
        }
        
        // Fila de totales
        echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
        echo '<td colspan="7" align="right">Total de registros: ' . count($solicitudes) . '</td>';
        echo '</tr>';
        
    } else {
        echo '<tr><td colspan="7" align="center">No hay solicitudes para exportar con los filtros seleccionados</td></tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';

} catch (PDOException $e) {
    // Manejo de errores
    header("Content-Type: text/html; charset=utf-8");
    echo '<h3>Error al exportar datos</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="javascript:history.back()">Regresar</a></p>';
}
?>