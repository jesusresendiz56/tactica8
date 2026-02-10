<?php
session_start();
require_once "../Modelo/SupaConexion.php";

if (!isset($_GET['id'])) {
    header("Location: solicitudes.php");
    exit();
}

$id = (int)$_GET['id'];

/* SOLICITUD + PUESTO */
$stmt = $conn->prepare("
    SELECT s.*, p.nombre_puesto
    FROM solicitud s
    JOIN cat_puestos p ON p.id_puesto = s.id_puesto
    WHERE s.id_solicitud = :id
");
$stmt->execute(['id' => $id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$s) {
    die("Solicitud no encontrada");
}

/* DIRECCIÓN */
$d = $conn->prepare("SELECT * FROM direcciones WHERE id_solicitud = :id");
$d->execute(['id' => $id]);
$dir = $d->fetch(PDO::FETCH_ASSOC);

/* FAMILIA */
$f = $conn->prepare("SELECT * FROM datos_familiares WHERE id_solicitud = :id");
$f->execute(['id' => $id]);
$fam = $f->fetch(PDO::FETCH_ASSOC);

/* REFERENCIAS */
$r = $conn->prepare("SELECT * FROM referencias WHERE id_solicitud = :id");
$r->execute(['id' => $id]);
$refs = $r->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Solicitud de Empleo</title>

<link rel="stylesheet" href="../src/estilos/imprimir.css">
</head>

<body>
<div class="recuadro hoja">

<header class="encabezado">
    <img src="../src/imagenes/logo.png" alt="TÁCTICA 8">
    <h2>SOLICITUD DE EMPLEO</h2>
</header>

<!-- ===== DATOS DEL PUESTO ===== -->
<fieldset>
    <legend>Datos del Puesto</legend>
    <div class="grid-2">
        <div>
            <label>Puesto</label>
            <div class="campo"><?= $s['nombre_puesto'] ?></div>
        </div>
        <div>
            <label>Salario deseado</label>
            <div class="campo">$<?= number_format($s['salario_deseado'],2) ?></div>
        </div>
    </div>
</fieldset>

<!-- ===== DATOS PERSONALES ===== -->
<fieldset>
    <legend>Datos Personales</legend>

    <div class="grid-3">
        <div class="campo"><?= $s['apellido_paterno'] ?></div>
        <div class="campo"><?= $s['apellido_materno'] ?></div>
        <div class="campo"><?= $s['nombre'] ?></div>
    </div>

    <div class="grid-3">
        <div class="campo"><?= $s['fecha_nacimiento'] ?></div>
        <div class="campo"><?= $s['lugar_nacimiento'] ?></div>
        <div class="campo"><?= ucfirst($s['sexo']) ?></div>
    </div>

    <div class="grid-3">
        <div class="campo"><?= $s['celular'] ?></div>
        <div class="campo"><?= $s['telefono_casa'] ?></div>
        <div class="campo"><?= $s['telefono_recados'] ?></div>
    </div>

    <div class="grid-2">
        <div class="campo"><?= $s['correo'] ?></div>
        <div class="campo"><?= ucfirst($s['estado_civil']) ?></div>
    </div>

    <div class="grid-2">
        <div class="campo"><?= $s['tipo_sangre'] ?></div>
        <div class="campo">Infonavit: <?= $s['credito_infonavit'] ? 'Sí' : 'No' ?> | Fonacot: <?= $s['credito_fonacot'] ? 'Sí' : 'No' ?></div>
    </div>
</fieldset>

<!-- ===== DIRECCIÓN ===== -->
<fieldset>
    <legend>Dirección</legend>
    <div class="grid-3">
        <div class="campo"><?= $dir['calle'] ?? '' ?></div>
        <div class="campo"><?= $dir['colonia'] ?? '' ?></div>
        <div class="campo"><?= $dir['cp'] ?? '' ?></div>
    </div>
    <div class="grid-3">
        <div class="campo"><?= $dir['ciudad'] ?? '' ?></div>
        <div class="campo"><?= $dir['municipio'] ?? '' ?></div>
        <div class="campo"><?= $dir['estado'] ?? '' ?></div>
    </div>
</fieldset>

<!-- ===== DOCUMENTOS ===== -->
<fieldset>
    <legend>Documentos</legend>
    <div class="grid-4">
        <div class="campo"><?= $s['rfc'] ?></div>
        <div class="campo"><?= $s['curp'] ?></div>
        <div class="campo"><?= $s['imss'] ?></div>
        <div class="campo"><?= $s['grado_estudios'] ?></div>
    </div>
</fieldset>

<!-- ===== DATOS FAMILIARES ===== -->
<fieldset>
    <legend>Datos Familiares</legend>
    <div class="grid-3">
        <div class="campo"><?= $fam['nombre_padre'] ?? '—' ?></div>
        <div class="campo"><?= $fam['nombre_madre'] ?? '—' ?></div>
        <div class="campo"><?= $fam['numero_hijos'] ?? '—' ?></div>
    </div>
    <div class="grid-1">
        <div class="campo"><?= $fam['quien_los_cuida'] ?? '—' ?></div>
    </div>
</fieldset>

<!-- ===== REFERENCIAS ===== -->
<fieldset>
    <legend>Referencias Personales</legend>
    <?php foreach ($refs as $ref): ?>
        <div class="grid-3">
            <div class="campo"><?= $ref['nombre'] ?></div>
            <div class="campo"><?= $ref['parentesco'] ?></div>
            <div class="campo"><?= $ref['telefono'] ?></div>
        </div>
    <?php endforeach; ?>
</fieldset>

<!-- ===== FIRMAS ===== -->
<fieldset class="firmas">
    <div class="grid-2 firmas-box">
        <div>
            _____________________________<br>
            <strong>Firma del Solicitante</strong>
        </div>
        <div>
            _____________________________<br>
            <strong>Recursos Humanos</strong>
        </div>
    </div>
</fieldset>

<div class="acciones">
    <button onclick="window.print()">🖨 Imprimir</button>
</div>

</div>
</body>
</html>

