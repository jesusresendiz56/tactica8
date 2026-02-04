<?php
// Controlador/engine_solicitud.php - VERSIÓN CORREGIDA
session_start();
require_once '../Modelo/conexion.php';

// ============================================
// SOLUCIÓN 1: NO REDIRIGIR SI NO ES POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // EN VEZ DE REDIRIGIR, MOSTRAMOS UN MENSAJE CLARO
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Error - Método incorrecto</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; text-align: center; }
            .error-box { 
                background: #f8d7da; 
                border: 2px solid #f5c6cb; 
                border-radius: 10px; 
                padding: 30px; 
                max-width: 600px; 
                margin: 0 auto;
                color: #721c24;
            }
            .btn { 
                display: inline-block; 
                background: #28a745; 
                color: white; 
                padding: 10px 20px; 
                text-decoration: none; 
                border-radius: 5px; 
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>⚠️ Error: Acceso incorrecto</h2>
            <p>Este archivo solo procesa envíos de formularios.</p>
            <p>Por favor, utiliza el formulario de solicitud de empleo.</p>
            <a href='../Vista/solicitud.php' class='btn'>Volver al formulario</a>
        </div>
    </body>
    </html>";
    exit(); // IMPORTANTE: Salir sin redirigir
}

// ============================================
// PROCESAMIENTO DEL FORMULARIO (POST)
// ============================================

// 1. RECOLECTAR Y SANITIZAR DATOS
$datos = [
    // Datos del puesto (REQUERIDOS)
    'puesto' => mysqli_real_escape_string($conn, $_POST['puesto'] ?? ''),
    'salario' => floatval($_POST['salario'] ?? 0),
    
    // Datos personales (REQUERIDOS)
    'apellido_paterno' => mysqli_real_escape_string($conn, $_POST['apellido_paterno'] ?? ''),
    'apellido_materno' => mysqli_real_escape_string($conn, $_POST['apellido_materno'] ?? ''),
    'nombres' => mysqli_real_escape_string($conn, $_POST['nombres'] ?? ''),
    
    // Correo (REQUERIDO)
    'correo' => mysqli_real_escape_string($conn, $_POST['correo'] ?? ''),
    
    // Dirección
    'calle_numero' => mysqli_real_escape_string($conn, $_POST['calle_numero'] ?? ''),
    'colonia' => mysqli_real_escape_string($conn, $_POST['colonia'] ?? ''),
    'ciudad_estado' => mysqli_real_escape_string($conn, $_POST['ciudad_estado'] ?? ''),
    'municipio_delegacion' => mysqli_real_escape_string($conn, $_POST['municipio_delegacion'] ?? ''),
    'codigo_postal' => mysqli_real_escape_string($conn, $_POST['codigo_postal'] ?? ''),
    
    // Contacto
    'celular' => mysqli_real_escape_string($conn, $_POST['celular'] ?? ''),
    'telefono_casa' => mysqli_real_escape_string($conn, $_POST['telefono_casa'] ?? ''),
    'telefono_recados' => mysqli_real_escape_string($conn, $_POST['telefono_recados'] ?? ''),
    
    // Información general
    'lugar_nacimiento' => mysqli_real_escape_string($conn, $_POST['lugar_nacimiento'] ?? ''),
    'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
    'edad' => intval($_POST['edad'] ?? 0),
    'estado_civil' => mysqli_real_escape_string($conn, $_POST['estado_civil'] ?? ''),
    'nombre_madre' => mysqli_real_escape_string($conn, $_POST['nombre_madre'] ?? ''),
    'nombre_padre' => mysqli_real_escape_string($conn, $_POST['nombre_padre'] ?? ''),
    'numero_hijos' => intval($_POST['numero_hijos'] ?? 0),
    'edades_hijos' => mysqli_real_escape_string($conn, $_POST['edades_hijos'] ?? ''),
    'quien_cuida_hijos' => mysqli_real_escape_string($conn, $_POST['quien_cuida_hijos'] ?? ''),
    'sexo' => mysqli_real_escape_string($conn, $_POST['sexo'] ?? ''),
    
    // Documentos
    'rfc' => mysqli_real_escape_string($conn, $_POST['rfc'] ?? ''),
    'imss' => mysqli_real_escape_string($conn, $_POST['imss'] ?? ''),
    'curp' => mysqli_real_escape_string($conn, $_POST['curp'] ?? ''),
    'infonavit' => isset($_POST['infonavit']) && $_POST['infonavit'] === 'si' ? 'si' : 'no',
    'fonacot' => isset($_POST['fonacot']) && $_POST['fonacot'] === 'si' ? 'si' : 'no',
    'grado_estudios' => mysqli_real_escape_string($conn, $_POST['grado_estudios'] ?? ''),
    'num_cuenta_nomina' => mysqli_real_escape_string($conn, $_POST['num_cuenta_nomina'] ?? ''),
    'tipo_sangre' => mysqli_real_escape_string($conn, $_POST['tipo_sangre'] ?? ''),
    
    // Referencias
    'ref1_nombre' => mysqli_real_escape_string($conn, $_POST['ref1_nombre'] ?? ''),
    'ref1_parentesco' => mysqli_real_escape_string($conn, $_POST['ref1_parentesco'] ?? ''),
    'ref1_telefono' => mysqli_real_escape_string($conn, $_POST['ref1_telefono'] ?? ''),
    'ref2_nombre' => mysqli_real_escape_string($conn, $_POST['ref2_nombre'] ?? ''),
    'ref2_parentesco' => mysqli_real_escape_string($conn, $_POST['ref2_parentesco'] ?? ''),
    'ref2_telefono' => mysqli_real_escape_string($conn, $_POST['ref2_telefono'] ?? ''),
    
    // Autorización
    'autorizo' => isset($_POST['autorizo']) && $_POST['autorizo'] === 'si' ? 'si' : 'no'
];

// 2. VALIDAR CAMPOS REQUERIDOS
$campos_requeridos = [
    'puesto' => 'Puesto',
    'apellido_paterno' => 'Apellido paterno',
    'nombres' => 'Nombres',
    'correo' => 'Correo electrónico'
];

$errores = [];
foreach ($campos_requeridos as $campo => $nombre) {
    if (empty(trim($datos[$campo]))) {
        $errores[] = "El campo '$nombre' es requerido";
    }
}

// Validar formato de correo
if (!empty($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Correo electrónico no válido";
}

// 3. SI HAY ERRORES, MOSTRARLOS
if (!empty($errores)) {
    $_SESSION['solicitud_errores'] = $errores;
    $_SESSION['solicitud_datos'] = $datos; // Guardar datos para repoblar formulario
    header("Location: ../Vista/solicitud.php");
    exit();
}

// 4. INSERTAR EN LA BASE DE DATOS
try {
    $sql = "INSERT INTO solicitudes_empleo (
        puesto, salario_deseado, apellido_paterno, apellido_materno, nombres,
        calle_numero, colonia, ciudad_estado, municipio_delegacion, codigo_postal,
        celular, telefono_casa, telefono_recados, correo, lugar_nacimiento,
        fecha_nacimiento, edad, estado_civil, nombre_madre, nombre_padre,
        numero_hijos, edades_hijos, quien_cuida_hijos, sexo, rfc, num_imss,
        curp, infonavit, fonacot, grado_estudios, num_cuenta_nomina, tipo_sangre,
        referencia1_nombre, referencia1_parentesco, referencia1_telefono,
        referencia2_nombre, referencia2_parentesco, referencia2_telefono,
        autorizo_compartir_datos
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . mysqli_error($conn));
    }
    
    // Vincular parámetros
    mysqli_stmt_bind_param($stmt, "sdssssssssssssssissssisssssssssssssssss", 
        $datos['puesto'], $datos['salario'], $datos['apellido_paterno'], 
        $datos['apellido_materno'], $datos['nombres'], $datos['calle_numero'], 
        $datos['colonia'], $datos['ciudad_estado'], $datos['municipio_delegacion'], 
        $datos['codigo_postal'], $datos['celular'], $datos['telefono_casa'], 
        $datos['telefono_recados'], $datos['correo'], $datos['lugar_nacimiento'], 
        $datos['fecha_nacimiento'], $datos['edad'], $datos['estado_civil'], 
        $datos['nombre_madre'], $datos['nombre_padre'], $datos['numero_hijos'], 
        $datos['edades_hijos'], $datos['quien_cuida_hijos'], $datos['sexo'], 
        $datos['rfc'], $datos['imss'], $datos['curp'], $datos['infonavit'], 
        $datos['fonacot'], $datos['grado_estudios'], $datos['num_cuenta_nomina'], 
        $datos['tipo_sangre'], $datos['ref1_nombre'], $datos['ref1_parentesco'], 
        $datos['ref1_telefono'], $datos['ref2_nombre'], $datos['ref2_parentesco'], 
        $datos['ref2_telefono'], $datos['autorizo']
    );
    
    // Ejecutar
    if (mysqli_stmt_execute($stmt)) {
        $id_solicitud = mysqli_insert_id($conn);
        
        // Guardar en historial (si existe la tabla)
        if (mysqli_query($conn, "SHOW TABLES LIKE 'historial_solicitudes'")) {
            $sql_historial = "INSERT INTO historial_solicitudes 
                             (id_solicitud, estatus_nuevo, observaciones) 
                             VALUES (?, 'pendiente', 'Solicitud creada')";
            $stmt_hist = mysqli_prepare($conn, $sql_historial);
            mysqli_stmt_bind_param($stmt_hist, "i", $id_solicitud);
            mysqli_stmt_execute($stmt_hist);
            mysqli_stmt_close($stmt_hist);
        }
        
        // ÉXITO
        mysqli_stmt_close($stmt);
        
        // Limpiar datos de sesión temporal
        if (isset($_SESSION['solicitud_datos'])) {
            unset($_SESSION['solicitud_datos']);
        }
        
        // Guardar mensaje de éxito
        $_SESSION['solicitud_exito'] = "✅ Solicitud enviada exitosamente. Nos pondremos en contacto contigo pronto.";
        
        // REDIRIGIR UNA SOLA VEZ
        header("Location: ../Vista/solicitud.php");
        exit();
        
    } else {
        throw new Exception("Error al guardar: " . mysqli_stmt_error($stmt));
    }
    
} catch (Exception $e) {
    // ERROR EN LA BASE DE DATOS
    $_SESSION['solicitud_errores'] = ["Error en el sistema: " . $e->getMessage()];
    $_SESSION['solicitud_datos'] = $datos;
    
    // REDIRIGIR UNA SOLA VEZ
    header("Location: ../Vista/solicitud.php");
    exit();
}

// NOTA: Este punto nunca se debería alcanzar
die("Error inesperado en el procesamiento.");
?>