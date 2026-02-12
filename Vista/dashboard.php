<?php
// dashboard.php
session_start();

// VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php?error=no_sesion');
    exit();
}

// CONEXIÓN DIRECTA A SUPABASE
$host = "aws-0-us-west-2.pooler.supabase.com";
$dbname = "postgres";
$user = "postgres.fbhirrxvzubnwnivrarl";
$password = "B4seD4tosT4ctica8";
$port = "5432";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

// ============================================
// CONSULTAS PARA EL DASHBOARD
// ============================================

// 1. CAMPAÑAS POR ESTATUS
$campanas_pendientes = 0;
$campanas_progreso = 0;
$campanas_completadas = 0;
$campanas_canceladas = 0;
$campanas_activas = 0;

try {
    // Pendientes
    $query = "SELECT COUNT(*) as total FROM campañas WHERE estatus = 'pendiente'";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $campanas_pendientes = $resultado['total'] ?? 0;
    
    // En Progreso
    $query = "SELECT COUNT(*) as total FROM campañas WHERE estatus = 'en_progreso'";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $campanas_progreso = $resultado['total'] ?? 0;
    
    // Completadas
    $query = "SELECT COUNT(*) as total FROM campañas WHERE estatus = 'completada'";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $campanas_completadas = $resultado['total'] ?? 0;
    
    // Canceladas
    $query = "SELECT COUNT(*) as total FROM campañas WHERE estatus = 'cancelada'";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $campanas_canceladas = $resultado['total'] ?? 0;
    
    // Total campañas activas (pendientes + en_progreso)
    $campanas_activas = $campanas_pendientes + $campanas_progreso;
    
} catch (PDOException $e) {
    // Si hay error, dejar valores en 0
}

// 2. PERSONAL ACTIVO
$personal_activo = 0;
try {
    $query = "SELECT COUNT(*) as total FROM personal";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $personal_activo = $resultado['total'] ?? 0;
} catch (PDOException $e) {
    $personal_activo = 0;
}

// 3. PERSONAL DISPONIBLE
$personal_disponible = 0;
try {
    $query = "SELECT COUNT(*) as total FROM solicitudes 
              WHERE estatus = 'aprobada' 
              AND id_solicitud NOT IN (SELECT id_solicitud FROM personal WHERE id_solicitud IS NOT NULL)";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $personal_disponible = $resultado['total'] ?? 0;
} catch (PDOException $e) {
    $personal_disponible = 0;
}

// 4. SOLICITUDES PENDIENTES
$solicitudes_pendientes = 0;
try {
    $query = "SELECT COUNT(*) as total FROM solicitudes WHERE estatus = 'pendiente'";
    $stmt = $conn->query($query);
    $resultado = $stmt->fetch();
    $solicitudes_pendientes = $resultado['total'] ?? 0;
} catch (PDOException $e) {
    $solicitudes_pendientes = 0;
}

// Obtener información del usuario
$usuario_nombre = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : 'Administrador';
$usuario_correo = isset($_SESSION['correo']) ? $_SESSION['correo'] : 'admin@gmail.com';

// Cerrar conexión
$conn = null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | TÁCTICA 8</title>
    <link rel="stylesheet" href="../src/estilos/estilos.css">
   
    <style>
        /* Mantener estilos originales del dashboard.css */
        
        /* Estilos para las cards de estatus de campañas */
        .campanas-estatus {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .estatus-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid transparent;
        }
        
        .estatus-pendiente { 
            border-top-color: #f39c12; 
        }
        .estatus-progreso { 
            border-top-color: #3498db; 
        }
        .estatus-completada { 
            border-top-color: #27ae60; 
        }
        .estatus-cancelada { 
            border-top-color: #e74c3c; 
        }
        
        .estatus-titulo {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            display: block;
        }
        
        .estatus-numero {
            font-size: 42px;
            font-weight: bold;
            margin: 10px 0;
            display: block;
        }
        
        .estatus-pendiente .estatus-numero { 
            color: #f39c12; 
        }
        .estatus-progreso .estatus-numero { 
            color: #3498db; 
        }
        .estatus-completada .estatus-numero { 
            color: #27ae60; 
        }
        .estatus-cancelada .estatus-numero { 
            color: #e74c3c; 
        }
        
        .estatus-label {
            color: #666;
            font-size: 13px;
            display: block;
            margin-top: 5px;
        }
        
        /* Asegurar que los estilos originales del dashboard se mantengan */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #ec1f27;
        }
        
        .card h3 {
            color: #1e3c72;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .card-number {
            font-size: 48px;
            font-weight: bold;
            color: #ec1f27;
            margin: 10px 0;
            line-height: 1;
        }
        
        .card-label {
            color: #666;
            font-size: 14px;
            display: block;
            margin-top: 10px;
        }
        
        .section-title {
            margin: 30px 0 20px 0;
            color: #1e3c72;
            font-size: 22px;
            font-weight: bold;
            border-bottom: 3px solid #ec1f27;
            padding-bottom: 10px;
        }
        
        h1 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 28px;
        }
        
        .content {
            margin-top: 80px;
            
            padding: 30px 40px;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .dashboard-grid,
            .campanas-estatus {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid,
            .campanas-estatus {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-logo">
            <a href="dashboard.php">
                <img src="../src/imagenes/tactica_logo.png"
                     alt="TÁCTICA 8"
                     class="logo-img"
                     width="100"
                     height="100">
            </a>
        </div>

        <div class="header-center-text">
            <strong>Agencia de Servicios Especializados en Marketing con REPSE.</strong><br>
            Más de 40 años de experiencia.
        </div>

        <!-- USUARIO Y LOGOUT -->
        <div class="header-exit">
            <div class="user-info-container" style="margin-right: 15px; text-align: right;">
                <span class="user-name" style="display: block; color: white; font-weight: bold;">
                    <?php echo htmlspecialchars($usuario_nombre); ?>
                </span>
                <span class="user-email" style="display: block; color: white; font-size: 12px; opacity: 0.8;">
                    <?php echo htmlspecialchars($usuario_correo); ?>
                </span>
            </div>
            
            <a href="../Controlador/logout.php" class="logout-link"
           onclick="return confirm('¿Estás seguro de cerrar sesión?')">
            <img src="../src/imagenes/logout.png" width="30" alt="Cerrar Sesión">
        </a>
        </div>
    </header>

    <!-- ===== MENÚ ===== -->
    <nav class="menu">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="campañas.php">Campañas</a>
        <a href="personal.php">Personal</a>
        <a href="asignaciones.php">Asignaciones</a>
        <a href="reportes.php">Reportes</a>
        <a href="solicitudes.php">Solicitudes</a>
    </nav>

    <div class="content">
        <h1>Dashboard</h1>

        <!-- SECCIÓN 1: ESTADÍSTICAS GENERALES -->
        <div class="dashboard-grid">
            <div class="card">
                <h3>Campañas Activas</h3>
                <p class="card-number"><?php echo $campanas_activas; ?></p>
                <span class="card-label">Pendientes + En Progreso</span>
            </div>
            <div class="card">
                <h3>Personal Activo</h3>
                <p class="card-number"><?php echo $personal_activo; ?></p>
                <span class="card-label">Empleados registrados</span>
            </div>
            <div class="card">
                <h3>Personal Disponible</h3>
                <p class="card-number"><?php echo $personal_disponible; ?></p>
                <span class="card-label">Solicitudes aprobadas</span>
            </div>
            <div class="card">
                <h3>Solicitudes Pendientes</h3>
                <p class="card-number"><?php echo $solicitudes_pendientes; ?></p>
                <span class="card-label">Por revisar</span>
            </div>
        </div>

        <!-- SECCIÓN 2: ESTATUS DE CAMPAÑAS -->
        <h2 class="section-title">Estatus de Campañas</h2>
        <div class="campanas-estatus">
            <div class="estatus-card estatus-pendiente">
                <span class="estatus-titulo">⏳ Pendientes</span>
                <span class="estatus-numero"><?php echo $campanas_pendientes; ?></span>
                <span class="estatus-label">Esperando inicio</span>
            </div>
            
            <div class="estatus-card estatus-progreso">
                <span class="estatus-titulo">⚡ En Progreso</span>
                <span class="estatus-numero"><?php echo $campanas_progreso; ?></span>
                <span class="estatus-label">Ejecutándose</span>
            </div>
            
            <div class="estatus-card estatus-completada">
                <span class="estatus-titulo">✅ Completadas</span>
                <span class="estatus-numero"><?php echo $campanas_completadas; ?></span>
                <span class="estatus-label">Finalizadas</span>
            </div>
            
            <div class="estatus-card estatus-cancelada">
                <span class="estatus-titulo">❌ Canceladas</span>
                <span class="estatus-numero"><?php echo $campanas_canceladas; ?></span>
                <span class="estatus-label">No realizadas</span>
            </div>
        </div>

      
    </div>
</body>
</html>