<?php
// Asignaciones.php
session_start();
if (!isset($_SESSION['id_usuario'])) { header('Location: login.php?error=no_sesion'); exit(); }

require_once '../Modelo/SupaConexion.php';
try {
    $conn = (new SupaConexion())->getConexion();
} catch (Exception $e) {
    die("<div style='background:#f8d7da;color:#721c24;padding:20px;margin:20px;border-radius:5px;'><h3>❌ ERROR</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
}

// Obtener datos
$responsables = $conn->query("SELECT id_responsable, nombre, puesto FROM responsables WHERE estado='activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$personal = $conn->query("SELECT p.id_personal, s.nombre, s.apellido_paterno, s.apellido_materno, cp.nombre_puesto, CONCAT('EMP', LPAD(p.id_personal::text,5,'0')) as num_empleado FROM personal p INNER JOIN solicitud s ON p.id_solicitud=s.id_solicitud LEFT JOIN cat_puestos cp ON s.id_puesto=cp.id_puesto WHERE p.estatus_laboral='activo' AND p.id_personal NOT IN (SELECT id_personal FROM asignaciones WHERE estatus_asignacion IN ('activa','en_progreso')) ORDER BY s.apellido_paterno")->fetchAll(PDO::FETCH_ASSOC);
$asignaciones = $conn->query("SELECT a.id_asignacion,a.fecha_asignacion,a.estatus_asignacion,CONCAT('EMP',LPAD(p.id_personal::text,5,'0')) as num_empleado,s.nombre,s.apellido_paterno,s.apellido_materno,cp.nombre_puesto,r.nombre as responsable_nombre,c.nombre_campaña,c.estatus as estatus_campana,m.nombre as marca_nombre,tc.nombre as tipo_campaña FROM asignaciones a INNER JOIN personal p ON a.id_personal=p.id_personal INNER JOIN solicitud s ON p.id_solicitud=s.id_solicitud LEFT JOIN cat_puestos cp ON s.id_puesto=cp.id_puesto INNER JOIN responsables r ON a.id_responsable=r.id_responsable INNER JOIN campañas c ON a.id_campaña=c.id_campaña INNER JOIN marcas m ON c.marca_id=m.id_marca INNER JOIN tipos_campaña tc ON c.tipo_campaña_id=tc.id_tipo ORDER BY a.fecha_asignacion DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaciones | TÁCTICA 8</title>
    <link rel="icon" type="image/png" href="../src/imagenes/favicon.png">
    <link rel="stylesheet" href="../src/estilos/estilos.css">
    <script src="../src/js/seguridad.js" defer></script>
</head>
<body>
<header class="header">
    <div class="logo">
        <a href="../index.php"><img src="../src/imagenes/tactica_logo.png" alt="TÁCTICA 8" class="logo-img" width="100" height="100"></a>
    </div>
    <div class="header-center-text">
        <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>Más de 40 años de experiencia.
    </div>
    <div class="header-exit">
        <div style="display:flex;align-items:center;color:white;">
            <div style="margin-right:15px;text-align:right;">
                <div style="font-weight:bold;"><?=htmlspecialchars($_SESSION['usuario_nombre']??'Usuario')?></div>
                <div style="font-size:12px;"><?=htmlspecialchars($_SESSION['correo']??'')?></div>
            </div>
            <a href="../Controlador/logout.php" onclick="return confirm('¿Cerrar sesión?')"><img src="../src/imagenes/logout.png" alt="Salir" width="30" height="30"></a>
        </div>
    </div>
</header>

<nav class="menu">
    <a href="../index.php">Dashboard</a>
    <a href="../Vista/campañas.php">Campañas</a>
    <a href="../Vista/personal.php">Personal</a>
    <a href="../Vista/asignaciones.php" class="active">Asignaciones</a>
    <a href="../Vista/reportes.php">Reportes</a>
    <a href="../Vista/solicitudes.php">Solicitudes</a>
</nav>

<main class="content">
    <?php if(isset($_GET['success']) && $_GET['success']=='asignacion_creada'): ?>
        <div class="alert-success">✅ Asignación creada correctamente.</div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert-error">
            <?php
            $err = $_GET['error'];
            if($err=='campos_vacios') echo "❌ Todos los campos son obligatorios.";
            elseif($err=='personal_ya_asignado') echo "❌ Este personal ya tiene una asignación activa.";
            elseif($err=='campana_inactiva') echo "❌ La campaña seleccionada no está disponible.";
            elseif($err=='personal_inactivo') echo "❌ El personal seleccionado no está activo laboralmente.";
            elseif($err=='fechas_invalidas') echo "❌ Las fechas de la campaña no permiten nuevas asignaciones.";
            elseif($err=='db_error') echo "❌ Error en la base de datos.";
            else echo "❌ Error al crear la asignación.";
            if(isset($_GET['detalle'])) echo "<br><small>".htmlspecialchars(urldecode($_GET['detalle']))."</small>";
            ?>
        </div>
    <?php endif; ?>

    <section class="form-section">
        <h1>Gestión de Asignaciones</h1>
        <form method="POST" action="../Controlador/engine_asignaciones.php">
            <label>Coordinador <span style="color:#999;font-size:12px;">(Responsable)</span></label>
            <select name="id_responsable" id="id_responsable" required onchange="cargarCampanasPorCoordinador(this.value)">
                <option value="" disabled selected>Seleccionar Coordinador</option>
                <?php foreach($responsables as $r): ?>
                    <option value="<?=$r['id_responsable']?>"><?=htmlspecialchars($r['nombre'].' - '.$r['puesto'])?></option>
                <?php endforeach; ?>
            </select>

            <label>Campaña <span style="color:#999;font-size:12px;">(Campañas del coordinador seleccionado)</span></label>
            <select name="id_campaña" id="id_campaña" required>
                <option value="" disabled selected>Selecciona un coordinador</option>
            </select>
            <div id="campana_resumen" style="font-size:12px;color:#666;margin-top:5px;margin-bottom:15px;display:none;">
                <span>Cargando campañas...</span>
            </div>
            <div id="campana_info" class="campana-info" style="display:none;">
                <strong>Detalles de la campaña:</strong>
                <div id="campana_detalles"></div>
            </div>

            <label>Personal <span style="color:#999;font-size:12px;">(Solo personal disponible)</span></label>
            <select name="id_personal" id="id_personal" required>
                <option value="" disabled selected>Seleccionar Personal</option>
                <?php foreach($personal as $p): 
                    $nombre = $p['nombre'].' '.$p['apellido_paterno'].' '.$p['apellido_materno'];
                ?>
                    <option value="<?=$p['id_personal']?>"><?=htmlspecialchars($nombre.($p['nombre_puesto']?' ('.$p['nombre_puesto'].')':'').' ['.$p['num_empleado'].']')?></option>
                <?php endforeach; ?>
                <?php if(empty($personal)): ?>
                    <option value="" disabled>No hay personal disponible</option>
                <?php endif; ?>
            </select>

            <input type="hidden" name="rol" value="personal_asignado">
            
            <?php if(empty($personal)): ?>
                <div class="alert-warning">No hay personal disponible en este momento.</div>
            <?php endif; ?>
            
            <button type="submit" id="btn-submit" <?=empty($personal)?'disabled class="btn-disabled"':''?>>
                Asignar Personal a Campaña
            </button>
        </form>
    </section>

    <section class="table-section">
        <h2>Asignaciones Recientes</h2>
        <div style="margin-bottom:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <input type="search" id="searchAsignaciones" placeholder="Buscar asignaciones..." style="padding:10px;width:300px;border:1px solid #ddd;border-radius:3px;">
            <select id="filtro_estatus" style="padding:10px;border:1px solid #ddd;border-radius:3px;">
                <option value="">Todos los estatus</option>
                <option value="activa">Activas</option>
                <option value="en_progreso">En Progreso</option>
                <option value="pendiente">Pendientes</option>
                <option value="finalizada">Finalizadas</option>
                <option value="completada">Completadas</option>
                <option value="cancelada">Canceladas</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Empleado</th><th>Nombre</th><th>Puesto</th>
                    <th>Coordinador</th><th>Campaña</th><th>Marca / Tipo</th>
                    <th>Fecha Asignación</th><th>Estatus</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($asignaciones as $a): 
                    $nombre = $a['nombre'].' '.$a['apellido_paterno'].' '.$a['apellido_materno'];
                    $estatus_class = match($a['estatus_asignacion']) {
                        'activa' => 'estatus-activa',
                        'en_progreso' => 'estatus-en_progreso',
                        'pendiente' => 'estatus-pendiente',
                        'finalizada','completada' => 'estatus-finalizada',
                        default => 'estatus-inactiva'
                    };
                ?>
                    <tr>
                        <td><?=$a['id_asignacion']?></td>
                        <td><?=htmlspecialchars($a['num_empleado'])?></td>
                        <td><?=htmlspecialchars($nombre)?></td>
                        <td><?=htmlspecialchars($a['nombre_puesto']??'N/A')?></td>
                        <td><?=htmlspecialchars($a['responsable_nombre'])?></td>
                        <td><?=htmlspecialchars($a['nombre_campaña'])?></td>
                        <td><?=htmlspecialchars($a['marca_nombre'])?><br><small><?=htmlspecialchars($a['tipo_campaña'])?></small></td>
                        <td><?=date('d/m/Y',strtotime($a['fecha_asignacion']))?></td>
                        <td>
                            <span class="<?=$estatus_class?>"><?=ucfirst($a['estatus_asignacion'])?></span><br>
                            <small>Campaña: <?=ucfirst($a['estatus_campana'])?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($asignaciones)): ?>
                    <tr><td colspan="9" style="text-align:center;padding:30px;">No hay asignaciones registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<script>
function cargarCampanasPorCoordinador(id) {
    const s = document.getElementById('id_campaña');
    const r = document.getElementById('campana_resumen');
    const i = document.getElementById('campana_info');
    
    if(!id || id==='' || id==='null' || id==='undefined') {
        s.innerHTML = '<option value="" disabled selected>Primero selecciona un coordinador</option>';
        r.style.display = 'none';
        i.style.display = 'none';
        return;
    }
    
    s.innerHTML = '<option value="" disabled selected>Cargando campañas...</option>';
    r.style.display = 'block';
    r.innerHTML = '<span class="loading">Cargando campañas del coordinador...</span>';
    i.style.display = 'none';
    
    fetch('../Controlador/get_campanas_por_coordinador.php?id_responsable='+encodeURIComponent(id))
    .then(r=>r.json())
    .then(d=>{
        if(d.error) {
            s.innerHTML = '<option value="" disabled selected>Error al cargar campañas</option>';
            r.innerHTML = '<span style="color:#dc3545;">❌ '+d.error+'</span>';
            return;
        }
        if(!d.campanas || d.campanas.length===0) {
            s.innerHTML = '<option value="" disabled selected>No hay campañas disponibles</option>';
            r.innerHTML = 'No hay campañas activas, en progreso o pendientes';
            return;
        }
        let opts = '<option value="" disabled selected>Seleccionar Campaña</option>';
        let counts = {en_progreso:0, activa:0, pendiente:0};
        
        d.campanas.forEach(c=>{
            opts += `<option value="${c.id_campaña}" data-estatus="${c.estatus}">${c.nombre_campaña} (${c.marca_nombre} | ${c.tipo_campaña}) - [${c.estatus}]</option>`;
            if(counts.hasOwnProperty(c.estatus)) counts[c.estatus]++;
        });
        
        s.innerHTML = opts;
        
        let resumen = 'Campañas disponibles: ';
        if(counts.en_progreso>0) resumen += `<span class="badge badge-en_progreso">🔵 ${counts.en_progreso} en progreso</span> `;
        if(counts.activa>0) resumen += `<span class="badge badge-activa">🟢 ${counts.activa} activas</span> `;
        if(counts.pendiente>0) resumen += `<span class="badge badge-pendiente">🟡 ${counts.pendiente} pendientes</span> `;
        r.innerHTML = resumen;
    })
    .catch(e=>{
        s.innerHTML = '<option value="" disabled selected>Error al cargar campañas</option>';
        r.innerHTML = '<span style="color:#dc3545;">Error de conexión</span>';
    });
}

document.getElementById('id_campaña')?.addEventListener('change', function() {
    if(!this.value) {
        document.getElementById('campana_info').style.display = 'none';
        return;
    }
    document.getElementById('campana_info').style.display = 'block';
    document.getElementById('campana_detalles').innerHTML = '<strong>'+this.options[this.selectedIndex].text.split(' - ')[0]+'</strong>';
});

function filtrarTabla() {
    const search = document.getElementById('searchAsignaciones').value.toLowerCase();
    const estatus = document.getElementById('filtro_estatus').value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(r=>{
        const texto = r.textContent.toLowerCase();
        const estatusCelda = r.querySelector('td:last-child span:first-child');
        const estatusTexto = estatusCelda ? estatusCelda.textContent.toLowerCase().trim() : '';
        r.style.display = (texto.includes(search) && (!estatus || estatusTexto.includes(estatus))) ? '' : 'none';
    });
}

document.getElementById('searchAsignaciones')?.addEventListener('keyup', filtrarTabla);
document.getElementById('filtro_estatus')?.addEventListener('change', filtrarTabla);
</script>
</body>
</html>