<?php
// Vista/solicitud.php
session_start();
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
        <div class="logo">
            <img src="../src/imagenes/logo.png"
                 alt="TÁCTICA 8"
                 class="logo-img"
                 width="120"
                 height="120">
        </div>
        
        <h2>SOLICITUD DE EMPLEO</h2>
        
        <!-- MOSTRAR MENSAJES -->
        <?php if (isset($_SESSION['solicitud_exito'])): ?>
            <div class="mensaje exito">
                <?php 
                echo $_SESSION['solicitud_exito']; 
                unset($_SESSION['solicitud_exito']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['solicitud_errores'])): ?>
            <div class="mensaje error">
                <strong>❌ Error al enviar la solicitud:</strong>
                <ul class="lista-errores">
                    <?php 
                    foreach ($_SESSION['solicitud_errores'] as $error) {
                        echo "<li>$error</li>";
                    }
                    unset($_SESSION['solicitud_errores']); 
                    ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- FORMULARIO -->
        <form method="POST" action="../Controlador/engine_solicitud.php">

            <!-- ===== DATOS DEL PUESTO ===== -->
            <section>
                <p>
                    <label>Puesto:</label>
                    <select name="puesto" id="puesto" required>
                        <option value="" disabled selected>Seleccionar Puesto</option>
                        <option value="promotor">PROMOTOR</option>
                        <option value="demostrador">DEMOSTRADOR</option>
                        <option value="coordinador">COORDINADOR</option>
                        <option value="supervisor">SUPERVISOR</option>
                        <option value="asesor">ASESOR</option>
                        <option value="promovendedor">PROMOVENDEDOR</option>
                        <option value="degustador">DEGUSTADOR</option>
                        <option value="auxiliar">AUXILIAR</option>
                        <option value="chofer">CHOFER</option>
                        <option value="edecan">EDECANES</option>
                        <option value="visual_merchandising">VISUAL MERCHANDISING</option>
                        <option value="ayudante_general">AYUDANTE GENERAL</option>
                        <option value="promotor_ventas">PROMOTOR DE VENTAS</option>
                        <option value="inflador_armador">INFLADORES Y ARMADORES</option>
                        <option value="capacitador">CAPACITADOR</option>
                        <option value="reclutador_campo">RECLUTADOR DE CAMPO</option>
                        <option value="representante_ventas">REPRESENTANTE DE VENTAS</option>
                        <option value="mercaderista">MERCADERISTA</option>
                        <option value="analista">ANALISTA</option>
                        <option value="consultora">CONSULTORA</option>
                    </select>

                    <label>Salario deseado:</label>
                    <input type="number" name="salario" step="0.01" min="0">
                </p>
            </section>

            <!-- ===== DATOS PERSONALES ===== -->
            <section>
                <p>
                    <label>Nombre completo:</label><br>
                    <input type="text" name="apellido_paterno" placeholder="Apellido Paterno" required>
                    <input type="text" name="apellido_materno" placeholder="Apellido Materno">
                    <input type="text" name="nombres" placeholder="Nombre(s)" required>
                </p>

                <p>
                    <label>Dirección:</label><br>
                    <input type="text" name="calle_numero" placeholder="Calle y número">
                    <input type="text" name="colonia" placeholder="Colonia"><br>
                    <input type="text" name="ciudad_estado" placeholder="Ciudad / Estado">
                    <input type="text" name="municipio_delegacion" placeholder="Delegación / Municipio">
                    <input type="text" name="codigo_postal" placeholder="C.P.">
                </p>

                <p>
                    <label>Celular:</label>
                    <input type="tel" name="celular">

                    <label>Teléfono Casa:</label>
                    <input type="tel" name="telefono_casa"><br>

                    <label>Teléfono Recados:</label>
                    <input type="tel" name="telefono_recados">
                </p>

                <p>
                    <label>Correo electrónico:</label>
                    <input type="email" name="correo" required>
                </p>
            </section>

            <!-- ===== INFORMACIÓN GENERAL ===== -->
            <section>
                <p>
                    <label>Lugar de nacimiento:</label>
                    <input type="text" name="lugar_nacimiento">
                </p>

                <p>
                    <label>Fecha de nacimiento:</label>
                    <input type="date" name="fecha_nacimiento">

                    <label>Edad:</label>
                    <input type="number" name="edad" min="18" max="70"><br>

                    <label>Estado civil:</label>
                    <select name="estado_civil">
                        <option value="" disabled selected>Seleccionar Estado Civil</option>
                        <option value="soltero">Soltero(a)</option>
                        <option value="casado">Casado(a)</option>
                        <option value="divorciado">Divorciado(a)</option>
                        <option value="viudo">Viudo(a)</option>
                        <option value="union_libre">Unión Libre</option>
                    </select>
                </p>

                <p>
                    <label>Nombre de la madre:</label>
                    <input type="text" name="nombre_madre">

                    <label>Nombre del padre:</label>
                    <input type="text" name="nombre_padre">
                </p>

                <p>
                    <label>Número de hijos:</label>
                    <input type="number" name="numero_hijos" min="0" max="20">

                    <label>Edades (separadas por coma):</label>
                    <input type="text" name="edades_hijos" placeholder="Ej: 5,8,12"><br>

                    <label>¿Quién los cuida?</label>
                    <input type="text" name="quien_cuida_hijos">
                </p>

                <p>
                    <label>Sexo:</label>
                    <select name="sexo">
                        <option value="" disabled selected>Seleccionar Sexo</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                    </select>
                </p>
            </section>

            <!-- ===== DOCUMENTOS ===== -->
            <section>
                <p>
                    <label>RFC:</label>
                    <input type="text" name="rfc">

                    <label>Número IMSS:</label>
                    <input type="text" name="imss">
                
                    <label>CURP:</label>
                    <input type="text" name="curp">
                </p>

                <p>
                    <label>Crédito INFONAVIT:</label>
                    <input type="radio" name="infonavit" value="si"> Sí
                    <input type="radio" name="infonavit" value="no" checked> No

                    <label>Crédito FONACOT:</label>
                    <input type="radio" name="fonacot" value="si"> Sí
                    <input type="radio" name="fonacot" value="no" checked> No
                </p>

                <p>
                    <label>Grado máximo de estudios:</label>
                    <input type="text" name="grado_estudios">
                </p>

                <p>
                    <label>Número de cuenta nómina:</label>
                    <input type="text" name="num_cuenta_nomina">
                </p>

                <p>
                    <label>Tipo de sangre:</label>
                    <input type="text" name="tipo_sangre">
                </p>
            </section>

            <!-- ===== REFERENCIAS ===== -->
            <section>
                <h3>REFERENCIAS PERSONALES</h3>

                <table border="1">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Parentesco</th>
                            <th>Teléfono</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="ref1_nombre"></td>
                            <td><input type="text" name="ref1_parentesco"></td>
                            <td><input type="tel" name="ref1_telefono"></td>
                        </tr>
                        <tr>
                            <td><input type="text" name="ref2_nombre"></td>
                            <td><input type="text" name="ref2_parentesco"></td>
                            <td><input type="tel" name="ref2_telefono"></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- ===== AUTORIZACIÓN ===== -->
            <section>
                <p>
                    Autorizo a la empresa a compartir mis datos personales con fines únicamente laborales:
                </p>

                <p>
                    <input type="radio" name="autorizo" value="si"> Sí
                    <input type="radio" name="autorizo" value="no" checked> No
                </p>
            </section>

            <!-- ===== BOTÓN ===== -->
            <p class="acciones">
                <input type="submit" value="Enviar solicitud">
                <input type="reset" value="Limpiar formulario">
            </p>

        </form>
    </div>
</body>
</html>