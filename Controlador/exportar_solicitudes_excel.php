<?php
// Controlador/exportar_solicitudes_excel.php
session_start();
require_once '../Modelo/SupaConexion.php';

// Función auxiliar para manejar valores nulos
function safe_html($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Vista/login.php');
    exit();
}

// Obtener filtros si se enviaron
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

try {
    // Construir la consulta SQL - TODOS LOS CAMPOS de solicitudes aprobadas
    $sql = "
        SELECT 
            s.id_solicitud AS id,
            TRIM(CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, ''))) AS nombre_completo,
            p.nombre_puesto AS puesto,
            s.nombre,
            s.apellido_paterno,
            s.apellido_materno,
            TO_CHAR(s.fecha_nacimiento, 'DD/MM/YYYY') AS fecha_nacimiento,
            s.sexo,
            s.estado_civil,
            s.rfc,
            s.curp,
            s.imss,
            s.grado_estudios,
            s.celular,
            s.telefono_casa,
            s.telefono_recados,
            s.correo,
            s.lugar_nacimiento,
            s.tipo_sangre,
            s.salario_deseado,
            s.credito_infonavit,
            s.credito_fonacot,
            s.autorizacion_datos,
            'Inmediata' AS disponibilidad,
            INITCAP(s.estatus) AS estatus,
            TO_CHAR(s.fecha_registro, 'DD/MM/YYYY') AS fecha_solicitud,
            -- Datos de dirección
            d.calle,
            d.colonia,
            d.ciudad,
            d.municipio,
            d.estado AS estado_direccion,
            d.cp,
            -- Datos familiares
            df.nombre_padre,
            df.nombre_madre,
            df.numero_hijos,
            df.quien_los_cuida,
            -- Datos de referencias
            (SELECT STRING_AGG(CONCAT(nombre, ' (', parentesco, ': ', telefono, ')'), ' | ') 
             FROM referencias r 
             WHERE r.id_solicitud = s.id_solicitud) AS referencias
        FROM solicitud s
        LEFT JOIN cat_puestos p ON s.id_puesto = p.id_puesto
        LEFT JOIN direcciones d ON s.id_solicitud = d.id_solicitud
        LEFT JOIN datos_familiares df ON s.id_solicitud = df.id_solicitud
        WHERE LOWER(s.estatus) = 'aprobada'
    ";

    $params = array();

    // Agregar filtro por búsqueda
    if (!empty($filtro_busqueda)) {
        $sql .= " AND (
            LOWER(CONCAT(s.nombre, ' ', s.apellido_paterno, ' ', COALESCE(s.apellido_materno, ''))) LIKE :busqueda 
            OR LOWER(p.nombre_puesto) LIKE :busqueda2
            OR LOWER(s.rfc) LIKE :busqueda3
            OR LOWER(s.curp) LIKE :busqueda4
        )";
        $params[':busqueda'] = '%' . strtolower($filtro_busqueda) . '%';
        $params[':busqueda2'] = '%' . strtolower($filtro_busqueda) . '%';
        $params[':busqueda3'] = '%' . strtolower($filtro_busqueda) . '%';
        $params[':busqueda4'] = '%' . strtolower($filtro_busqueda) . '%';
    }

    $sql .= " ORDER BY s.fecha_registro DESC";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Nombre del archivo
    $filename = "solicitudes_aprobadas_completas_" . date('Y-m-d_H-i-s') . ".xls";

    // Limpiar cualquier salida previa
    if (ob_get_level()) ob_end_clean();

    // Encabezados para descargar el archivo
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Crear el contenido del archivo
    echo '<html>';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';

    // Encabezado del reporte
    echo '<table border="0" cellpadding="2" cellspacing="0">';
    echo '<tr><td colspan="37" style="font-size: 14px; font-weight: bold;">Reporte COMPLETO de Solicitudes Aprobadas - TÁCTICA 8</td></tr>';
    echo '<tr><td colspan="37">Fecha de exportación: ' . date('d/m/Y H:i:s') . '</td></tr>';
    
    if (!empty($filtro_busqueda)) {
        echo '<tr><td colspan="37">Filtros aplicados: Búsqueda: "' . safe_html($filtro_busqueda) . '"</td></tr>';
    }
    
    echo '<tr><td colspan="37">&nbsp;</td></tr>';
    echo '</table>';

    // Tabla principal
    echo '<table border="1" cellpadding="4" cellspacing="0">';

    // ENCABEZADOS
    echo '<tr style="background-color: #EC1F27; color: white; font-weight: bold;">';
    echo '<th>ID</th><th>Nombre Completo</th><th>Puesto</th><th>Nombre</th><th>Apellido Paterno</th>';
    echo '<th>Apellido Materno</th><th>Fecha Nacimiento</th><th>Sexo</th><th>Estado Civil</th><th>RFC</th>';
    echo '<th>CURP</th><th>IMSS</th><th>Grado Estudios</th><th>Celular</th><th>Teléfono Casa</th>';
    echo '<th>Teléfono Recados</th><th>Correo</th><th>Lugar Nacimiento</th><th>Tipo Sangre</th>';
    echo '<th>Salario Deseado</th><th>Infonavit</th><th>Fonacot</th><th>Autorización Datos</th>';
    echo '<th>Disponibilidad</th><th>Estatus</th><th>Fecha Solicitud</th><th>Calle</th><th>Colonia</th>';
    echo '<th>Ciudad</th><th>Municipio</th><th>Estado (Dirección)</th><th>CP</th><th>Nombre Padre</th>';
    echo '<th>Nombre Madre</th><th>Número Hijos</th><th>Quien los Cuida</th><th>Referencias</th>';
    echo '</tr>';

    // DATOS
    if (count($solicitudes) > 0) {
        foreach ($solicitudes as $s) {
            echo '<tr>';
            echo '<td>' . safe_html($s['id']) . '</td>';
            echo '<td>' . safe_html($s['nombre_completo']) . '</td>';
            echo '<td>' . safe_html($s['puesto']) . '</td>';
            echo '<td>' . safe_html($s['nombre']) . '</td>';
            echo '<td>' . safe_html($s['apellido_paterno']) . '</td>';
            echo '<td>' . safe_html($s['apellido_materno']) . '</td>';
            echo '<td>' . safe_html($s['fecha_nacimiento']) . '</td>';
            echo '<td>' . safe_html($s['sexo']) . '</td>';
            echo '<td>' . safe_html($s['estado_civil']) . '</td>';
            echo '<td>' . safe_html($s['rfc']) . '</td>';
            echo '<td>' . safe_html($s['curp']) . '</td>';
            echo '<td>' . safe_html($s['imss']) . '</td>';
            echo '<td>' . safe_html($s['grado_estudios']) . '</td>';
            echo '<td>' . safe_html($s['celular']) . '</td>';
            echo '<td>' . safe_html($s['telefono_casa']) . '</td>';
            echo '<td>' . safe_html($s['telefono_recados']) . '</td>';
            echo '<td>' . safe_html($s['correo']) . '</td>';
            echo '<td>' . safe_html($s['lugar_nacimiento']) . '</td>';
            echo '<td>' . safe_html($s['tipo_sangre']) . '</td>';
            echo '<td>' . safe_html($s['salario_deseado']) . '</td>';
            echo '<td>' . ($s['credito_infonavit'] ? 'Sí' : 'No') . '</td>';
            echo '<td>' . ($s['credito_fonacot'] ? 'Sí' : 'No') . '</td>';
            echo '<td>' . ($s['autorizacion_datos'] ? 'Sí' : 'No') . '</td>';
            echo '<td>' . safe_html($s['disponibilidad']) . '</td>';
            echo '<td>' . safe_html($s['estatus']) . '</td>';
            echo '<td>' . safe_html($s['fecha_solicitud']) . '</td>';
            echo '<td>' . safe_html($s['calle']) . '</td>';
            echo '<td>' . safe_html($s['colonia']) . '</td>';
            echo '<td>' . safe_html($s['ciudad']) . '</td>';
            echo '<td>' . safe_html($s['municipio']) . '</td>';
            echo '<td>' . safe_html($s['estado_direccion']) . '</td>';
            echo '<td>' . safe_html($s['cp']) . '</td>';
            echo '<td>' . safe_html($s['nombre_padre']) . '</td>';
            echo '<td>' . safe_html($s['nombre_madre']) . '</td>';
            echo '<td>' . safe_html($s['numero_hijos']) . '</td>';
            echo '<td>' . safe_html($s['quien_los_cuida']) . '</td>';
            echo '<td>' . safe_html($s['referencias']) . '</td>';
            echo '</tr>';
        }
        
        echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
        echo '<td colspan="37" align="right">Total de solicitudes aprobadas: ' . count($solicitudes) . '</td>';
        echo '</tr>';
        
    } else {
        echo '<tr><td colspan="37" align="center">No hay solicitudes aprobadas para exportar</td></tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';

} catch (PDOException $e) {
    header("Content-Type: text/html; charset=utf-8");
    echo '<h3>Error al exportar datos</h3>';
    echo '<p>' . safe_html($e->getMessage()) . '</p>';
    echo '<p><a href="javascript:history.back()">Regresar</a></p>';
}
?>