<?php
session_start();
require_once "../Modelo/SupaConexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Vista/solicitud.php");
    exit();
}

try {
    $conn->beginTransaction();

    /* ===============================
       NORMALIZAR BOOLEANOS (CLAVE)
    =============================== */
    function boolOrNull($value) {
        if ($value === 'TRUE') return true;
        if ($value === 'FALSE') return false;
        return null;
    }

    $autorizacion      = boolOrNull($_POST['autorizacion_datos'] ?? null);
    $credito_infonavit = boolOrNull($_POST['credito_infonavit'] ?? null);
    $credito_fonacot   = boolOrNull($_POST['credito_fonacot'] ?? null);

    /* ===============================
       SOLICITUD
    =============================== */
    $stmt = $conn->prepare("
        INSERT INTO solicitud (
            id_puesto, nombre, apellido_paterno, apellido_materno,
            fecha_nacimiento, lugar_nacimiento, sexo, estado_civil,
            rfc, curp, imss, grado_estudios,
            celular, telefono_casa, telefono_recados, correo, salario_deseado,
            tipo_sangre, estatus,
            autorizacion_datos, credito_infonavit, credito_fonacot
        ) VALUES (
            :id_puesto, :nombre, :ap_paterno, :ap_materno,
            :fecha_nac, :lugar_nac, :sexo, :estado_civil,
            :rfc, :curp, :imss, :grado,
            :celular, :tel_casa, :tel_recados, :correo, :salario,
            :tipo_sangre, 'Pendiente',
            :autorizacion, :infonavit, :fonacot
        )
        RETURNING id_solicitud
    ");

    $stmt->bindValue(':id_puesto', $_POST['id_puesto'], PDO::PARAM_INT);
    $stmt->bindValue(':nombre', $_POST['nombre']);
    $stmt->bindValue(':ap_paterno', $_POST['apellido_paterno']);
    $stmt->bindValue(':ap_materno', $_POST['apellido_materno'] ?: null);
    $stmt->bindValue(':fecha_nac', $_POST['fecha_nacimiento'] ?: null);
    $stmt->bindValue(':lugar_nac', $_POST['lugar_nacimiento'] ?: null);
    $stmt->bindValue(':sexo', $_POST['sexo'] ?: null);
    $stmt->bindValue(':estado_civil', $_POST['estado_civil'] ?: null);
    $stmt->bindValue(':rfc', $_POST['rfc'] ?: null);
    $stmt->bindValue(':curp', $_POST['curp'] ?: null);
    $stmt->bindValue(':imss', $_POST['imss'] ?: null);
    $stmt->bindValue(':grado', $_POST['grado_estudios'] ?: null);
    $stmt->bindValue(':celular', $_POST['celular'] ?: null);
    $stmt->bindValue(':tel_casa', $_POST['telefono_casa'] ?: null);
    $stmt->bindValue(':tel_recados', $_POST['telefono_recados'] ?: null);
    $stmt->bindValue(':correo', $_POST['correo']);
    $stmt->bindValue(':salario', $_POST['salario_deseado'] ?: null);
    $stmt->bindValue(':tipo_sangre', $_POST['tipo_sangre'] ?: null);

    $stmt->bindValue(':autorizacion', $autorizacion, $autorizacion === null ? PDO::PARAM_NULL : PDO::PARAM_BOOL);
    $stmt->bindValue(':infonavit', $credito_infonavit, $credito_infonavit === null ? PDO::PARAM_NULL : PDO::PARAM_BOOL);
    $stmt->bindValue(':fonacot', $credito_fonacot, $credito_fonacot === null ? PDO::PARAM_NULL : PDO::PARAM_BOOL);

    $stmt->execute();
    $id_solicitud = $stmt->fetchColumn();

    /* ===============================
       DIRECCIÓN
    =============================== */
    $stmt = $conn->prepare("
        INSERT INTO direcciones (
            id_solicitud, calle, colonia, ciudad, municipio, estado, cp
        ) VALUES (
            :id, :calle, :colonia, :ciudad, :municipio, :estado, :cp
        )
    ");
    $stmt->execute([
        ':id'        => $id_solicitud,
        ':calle'     => $_POST['calle'] ?: null,
        ':colonia'   => $_POST['colonia'] ?: null,
        ':ciudad'    => $_POST['ciudad'] ?: null,
        ':municipio' => $_POST['municipio'] ?: null,
        ':estado'    => $_POST['estado'] ?: null,
        ':cp'        => $_POST['cp'] ?: null
    ]);

    /* ===============================
       DATOS FAMILIARES
    =============================== */
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

    /* ===============================
       REFERENCIAS
    =============================== */
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
