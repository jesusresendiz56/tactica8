<?php
session_start();
require_once "../Modelo/SupaConexion.php";

/* Obtener puestos desde la BD */
$stmt = $conn->query("
    SELECT id_puesto, nombre_puesto
    FROM cat_puestos
    ORDER BY nombre_puesto
");
$puestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Empleo | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/solicitud.css">
</head>

<body>
<div class="recuadro">

    <header class="encabezado">
        <img src="../src/imagenes/logo.png" alt="TÁCTICA 8">
        <h2>SOLICITUD DE EMPLEO</h2>
    </header>

    <!-- Mensajes -->
    <?php if (!empty($_SESSION['solicitud_exito'])): ?>
        <div class="mensaje exito">
            <?= $_SESSION['solicitud_exito']; unset($_SESSION['solicitud_exito']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['solicitud_errores'])): ?>
        <div class="mensaje error">
            <ul>
                <?php foreach ($_SESSION['solicitud_errores'] as $e): ?>
                    <li><?= $e ?></li>
                <?php endforeach; unset($_SESSION['solicitud_errores']); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="../Controlador/engine_solicitud.php">

        <!-- ===== DATOS DEL PUESTO ===== -->
        <fieldset>
            <legend>Datos del Puesto</legend>
            <div class="grid-2">
                <div>
                    <label>Puesto</label>
                    <select name="id_puesto" required>
                        <option value="">Seleccionar puesto</option>
                        <?php foreach ($puestos as $p): ?>
                            <option value="<?= $p['id_puesto'] ?>">
                                <?= htmlspecialchars($p['nombre_puesto']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Salario deseado</label>
                    <input type="number" name="salario_deseado" step="0.01">
                </div>
            </div>
        </fieldset>

        <!-- ===== DATOS PERSONALES ===== -->
        <fieldset>
            <legend>Datos Personales</legend>
            <div class="grid-3">
                <input type="text" name="apellido_paterno" placeholder="Apellido paterno" required>
                <input type="text" name="apellido_materno" placeholder="Apellido materno">
                <input type="text" name="nombre" placeholder="Nombre(s)" required>
            </div>

            <div class="grid-3">
                <input type="date" name="fecha_nacimiento">
                <input type="text" name="lugar_nacimiento" placeholder="Lugar de nacimiento">
                <select name="sexo">
                    <option value="">Sexo</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                </select>
            </div>

            <div class="grid-3">
                <input type="number" name="celular" placeholder="Celular">
                <input type="number" name="telefono_casa" placeholder="Teléfono de casa">
                <input type="number" name="telefono_recados" placeholder="Teléfono de recados">
            </div>

            <div class="grid-2">
                <input type="email" name="correo" placeholder="Correo electrónico" required>
                <select name="estado_civil">
                    <option value="">Estado civil</option>
                    <option value="soltero">Soltero(a)</option>
                    <option value="casado">Casado(a)</option>
                    <option value="union_libre">Unión libre</option>
                    <option value="divorciado">Divorciado(a)</option>
                    <option value="viudo">Viudo(a)</option>
                </select>
            </div>

            <div class="grid-2">
                <label>Tipo de sangre</label>
                <select name="tipo_sangre">
                    <option value="">Seleccionar</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                </select>
            </div>

            <div class="grid-2">
                <label>¿Crédito Infonavit?</label>
                <select name="credito_infonavit">
                    <option value="">Seleccionar</option>
                    <option value="TRUE">Sí</option>
                    <option value="FALSE">No</option>
                </select>
                <label>¿Crédito Fonacot?</label>
                <select name="credito_fonacot">
                    <option value="">Seleccionar</option>
                    <option value="TRUE">Sí</option>
                    <option value="FALSE">No</option>
                </select>
            </div>
        </fieldset>

        <!-- ===== DIRECCIÓN ===== -->
        <fieldset>
            <legend>Dirección</legend>
            <div class="grid-3">
                <input type="text" name="calle" placeholder="Calle y Numero">
                <input type="text" name="colonia" placeholder="Colonia">
                <input type="text" name="cp" placeholder="Código postal">
            </div>
            <div class="grid-3">
                <input type="text" name="ciudad" placeholder="Ciudad">
                <input type="text" name="municipio" placeholder="Municipio">
                <input type="text" name="estado" placeholder="Estado">
            </div>
        </fieldset>

        <!-- ===== DOCUMENTOS ===== -->
        <fieldset>
            <legend>Documentos</legend>
            <div class="grid-4">
                <input type="text" name="rfc" placeholder="RFC">
                <input type="text" name="curp" placeholder="CURP">
                <input type="text" name="imss" placeholder="IMSS">
                <input type="text" name="grado_estudios" placeholder="Grado de estudios">
            </div>
        </fieldset>

        <!-- ===== DATOS FAMILIARES ===== -->
        <fieldset>
            <legend>Datos Familiares</legend>
            <div class="grid-3">
                <input type="text" name="nombre_padre" placeholder="Nombre del padre">
                <input type="text" name="nombre_madre" placeholder="Nombre de la madre">
                <input type="number" name="numero_hijos" placeholder="Número de hijos">
            </div>
            <div class="grid-1">
                <input type="text" name="quien_los_cuida" placeholder="Quién los cuida">
            </div>
        </fieldset>

        <!-- ===== REFERENCIAS ===== -->
        <fieldset>
            <legend>Referencias Personales</legend>
            <div class="referencia">
                <input type="text" name="ref_nombre[]" placeholder="Nombre">
                <input type="text" name="ref_parentesco[]" placeholder="Parentesco">
                <input type="number" name="ref_telefono[]" placeholder="Teléfono">
            </div>
            <div class="referencia">
                <input type="text" name="ref_nombre[]" placeholder="Nombre">
                <input type="text" name="ref_parentesco[]" placeholder="Parentesco">
                <input type="number" name="ref_telefono[]" placeholder="Teléfono">
            </div>
        </fieldset>

        <!-- ===== AUTORIZACIÓN ===== -->
        <fieldset class="autorizacion">
            <label>Autorizo a la empresa a usar mis datos:</label>
            <select name="autorizacion_datos" required>
                <option value="">Seleccionar</option>
                <option value="TRUE">Sí</option>
                <option value="FALSE">No</option>
            </select>
        </fieldset>

        <div class="acciones">
            <button type="submit">Enviar solicitud</button>
            <button type="reset" class="secundario">Limpiar</button>
        </div>

    </form>
</div>
</body>
</html>


