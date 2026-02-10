<?php
session_start();
require_once '../Modelo/SupaConexion.php';

if (!isset($_GET['id'])) {
    header("Location: solicitudes.php");
    exit();
}

$id = (int) $_GET['id'];

/* ===============================
   SOLICITUD + PUESTO
================================ */
$sql = "
SELECT 
    s.*,
    p.nombre_puesto
FROM solicitud s
JOIN cat_puestos p ON p.id_puesto = s.id_puesto
WHERE s.id_solicitud = :id
";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$solicitud) {
    die('Solicitud no encontrada');
}

/* ===============================
   DIRECCIÓN
================================ */
$dir = $conn->prepare("SELECT * FROM direcciones WHERE id_solicitud = :id");
$dir->execute([':id' => $id]);
$direccion = $dir->fetch(PDO::FETCH_ASSOC);

/* ===============================
   DATOS FAMILIARES
================================ */
$fam = $conn->prepare("SELECT * FROM datos_familiares WHERE id_solicitud = :id");
$fam->execute([':id' => $id]);
$familia = $fam->fetch(PDO::FETCH_ASSOC);

/* ===============================
   REFERENCIAS
================================ */
$ref = $conn->prepare("SELECT * FROM referencias WHERE id_solicitud = :id");
$ref->execute([':id' => $id]);
$referencias = $ref->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Empleo</title>
    <link rel="stylesheet" href="../src/estilos/imprimir.css">
</head>
<body>

<div class="hoja">

<header>
    <img src="../src/imagenes/logo.png" width="120">
    <h2>SOLICITUD DE EMPLEO</h2>
</header>

<section class="bloque">
    <strong>PUESTO:</strong> <?= $solicitud['nombre_puesto'] ?>
    &nbsp;&nbsp;
    <strong>SALARIO:</strong> $<?= $solicitud['salario_deseado'] ?>
</section>

<section class="bloque">
    <strong>NOMBRE:</strong>
    <?= $solicitud['apellido_paterno'] ?>
    <?= $solicitud['apellido_materno'] ?>
    <?= $solicitud['nombre'] ?>
</section>

<section class="bloque">
    <strong>DIRECCIÓN:</strong><br>
    <?= $direccion['calle'] ?>,
    <?= $direccion['colonia'] ?>,
    <?= $direccion['ciudad'] ?>,
    <?= $direccion['municipio'] ?>,
    <?= $direccion['estado'] ?>,
    CP <?= $direccion['cp'] ?>
</section>

<section class="bloque grid-2">
    <div><strong>Celular:</strong> <?= $solicitud['celular'] ?></div>
    <div><strong>Casa:</strong> <?= $solicitud['telefono_casa'] ?></div>
    <div><strong>Correo:</strong> <?= $solicitud['correo'] ?></div>
    <div><strong>Recados:</strong> <?= $solicitud['telefono_recados'] ?></div>
</section>

<section class="bloque grid-3">
    <div><strong>Lugar de nacimiento:</strong> <?= $solicitud['lugar_nacimiento'] ?></div>
    <div><strong>Fecha nacimiento:</strong> <?= $solicitud['fecha_nacimiento'] ?></div>
    <div><strong>Estado civil:</strong> <?= $solicitud['estado_civil'] ?></div>
</section>

<section class="bloque grid-3">
    <div><strong>Sexo:</strong> <?= $solicitud['sexo'] ?></div>
    <div><strong>RFC:</strong> <?= $solicitud['rfc'] ?></div>
    <div><strong>IMSS:</strong> <?= $solicitud['imss'] ?></div>
</section>

<section class="bloque">
    <strong>CURP:</strong> <?= $solicitud['curp'] ?><br>
    <strong>Grado máximo de estudios:</strong> <?= $solicitud['grado_estudios'] ?><br>
    <strong>Tipo de sangre:</strong> <?= $solicitud['tipo_sangre'] ?>
</section>

<section class="bloque">
    <strong>Nombre del padre:</strong> <?= $familia['nombre_padre'] ?><br>
    <strong>Nombre de la madre:</strong> <?= $familia['nombre_madre'] ?><br>
    <strong>Número de hijos:</strong> <?= $familia['numero_hijos'] ?><br>
    <strong>Quién los cuida:</strong> <?= $familia['quien_los_cuida'] ?>
</section>

<section class="bloque">
    <strong>REFERENCIAS PERSONALES</strong>
    <table>
        <tr><th>Nombre</th><th>Parentesco</th><th>Teléfono</th></tr>
        <?php foreach ($referencias as $r): ?>
        <tr>
            <td><?= $r['nombre'] ?></td>
            <td><?= $r['parentesco'] ?></td>
            <td><?= $r['telefono'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="bloque firmas">
    <div>_____________________________<br>FIRMA DEL SOLICITANTE</div>
    <div>_____________________________<br>RECURSOS HUMANOS</div>
</section>

<button onclick="window.print()">🖨 Imprimir</button>

</div>
</body>
</html>
