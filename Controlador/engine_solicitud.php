<?php
session_start();
require_once "../Modelo/SupaConexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Vista/solicitud.php");
    exit();
}

try {
    $conn->beginTransaction();

    // Convertir booleanos
    $autorizacion      = isset($_POST['autorizacion_datos']) && $_POST['autorizacion_datos'] === 'TRUE';
    $credito_infonavit = isset($_POST['credito_infonavit']) && $_POST['credito_infonavit'] === 'TRUE';
    $credito_fonacot   = isset($_POST['credito_fonacot']) && $_POST['credito_fonacot'] === 'TRUE';

    /* SOLICITUD */
    $stmt = $conn->prepare("
        INSERT INTO solicitud (
            id_puesto, nombre, apellido_paterno, apellido_materno,
            fecha_nacimiento, lugar_nacimiento, sexo, estado_civil,
            rfc, curp, imss, grado_estudios,
            celular, telefono_casa, telefono_recados, correo, salario_deseado,
            tipo_sangre, estatus, autorizacion_datos, credito_infonavit, credito_fonacot
        ) VALUES (
            :id_puesto, :nombre, :ap_paterno, :ap_materno,
            :fecha_nac, :lugar_nac, :sexo, :estado_civil,
            :rfc, :curp, :imss, :grado,
            :celular, :tel_casa, :tel_recados, :correo, :salario,
            :tipo_sangre, 'Pendiente', :autorizacion, :infonavit, :fonacot
        )
        RETURNING id_solicitud
    ");

    $stmt->execute([
        ':id_puesto'   => $_POST['id_puesto'],
        ':nombre'      => $_POST['nombre'],
        ':ap_paterno'  => $_POST['apellido_paterno'],
        ':ap_materno'  => $_POST['apellido_materno'],
        ':fecha_nac'   => $_POST['fecha_nacimiento'] ?: null,
        ':lugar_nac'   => $_POST['lugar_nacimiento'] ?: null,
        ':sexo'        => $_POST['sexo'],
        ':estado_civil'=> $_POST['estado_civil'],
        ':rfc'         => $_POST['rfc'],
        ':curp'        => $_POST['curp'],
        ':imss'        => $_POST['imss'],
        ':grado'       => $_POST['grado_estudios'],
        ':celular'     => $_POST['celular'] ?: null,
        ':tel_casa'    => $_POST['telefono_casa'] ?: null,
        ':tel_recados' => $_POST['telefono_recados'] ?: null,
        ':correo'      => $_POST['correo'],
        ':salario'     => $_POST['salario_deseado'] ?: null,
        ':tipo_sangre' => $_POST['tipo_sangre'] ?: null,
        ':autorizacion'=> $autorizacion,
        ':infonavit'   => $credito_infonavit,
        ':fonacot'     => $credito_fonacot
    ]);

    $id_solicitud = $stmt->fetchColumn();

    /* DIRECCIÓN */
    $stmt = $conn->prepare("
        INSERT INTO direcciones (
            id_solicitud, calle, colonia, ciudad, municipio, estado, cp
        ) VALUES (
            :id, :calle, :colonia, :ciudad, :municipio, :estado, :cp
        )
    ");
    $stmt->execute([
        ':id'        => $id_solicitud,
        ':calle'     => $_POST['calle'],
        ':colonia'   => $_POST['colonia'],
        ':ciudad'    => $_POST['ciudad'],
        ':municipio' => $_POST['municipio'],
        ':estado'    => $_POST['estado'],
        ':cp'        => $_POST['cp']
    ]);

    /* DATOS FAMILIARES */
    $stmt = $conn->prepare("
        INSERT INTO datos_familiares (
            id_solicitud, nombre_padre, nombre_madre,
            numero_hijos, quien_los_cuida
        ) VALUES (
            :id, :padre, :madre, :hijos, :cuida
        )
    ");
    $stmt->execute([
        ':id'     => $id_solicitud,
        ':padre'  => $_POST['nombre_padre'] ?: null,
        ':madre'  => $_POST['nombre_madre'] ?: null,
        ':hijos'  => $_POST['numero_hijos'] ?: 0,
        ':cuida'  => $_POST['quien_los_cuida'] ?: null
    ]);

    /* REFERENCIAS */
    $stmt = $conn->prepare("
        INSERT INTO referencias (
            id_solicitud, nombre, parentesco, telefono
        ) VALUES (
            :id, :nombre, :parentesco, :telefono
        )
    ");

    for ($i = 0; $i < count($_POST['ref_nombre']); $i++) {
        if (!empty($_POST['ref_nombre'][$i])) {
            $stmt->execute([
                ':id'         => $id_solicitud,
                ':nombre'     => $_POST['ref_nombre'][$i],
                ':parentesco' => $_POST['ref_parentesco'][$i],
                ':telefono'   => $_POST['ref_telefono'][$i]
            ]);
        }
    }

    $conn->commit();
    $_SESSION['solicitud_exito'] = "✅ Solicitud enviada correctamente.";

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['solicitud_errores'] = [
        "❌ Error al guardar la solicitud",
        $e->getMessage()
    ];
}

header("Location: ../Vista/solicitud.php");
exit();
?>
