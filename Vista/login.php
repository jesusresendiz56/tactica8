<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión | TÁCTICA 8</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../src/estilos/login.css">
    <script src="../src/js/seguridad.js" defer></script>
</head>

<body>

    <div class="split-container">

        <!-- LADO IZQUIERDO - BRANDING / VISUAL -->
        <div class="left-panel">
            <div class="brand-content">
                <div class="brand-logo">
                    <img src="../src/imagenes/logo.png" alt="TÁCTICA 8">
                </div>
                <h2 class="brand-title">TÁCTICA 8</h2>
                <p class="brand-subtitle">
                    Sistema Web para la Gestión de Campañas y Personal de Promotoría
                </p>

                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-bullhorn"></i>
                        <span>Administración de Campañas</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-user-check"></i>
                        <span>Asignación de Personal</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Seguimiento de Solicitudes</span>
                    </div>
                </div>


                <!-- ELEMENTOS FLOTANTES INTERACTIVOS -->
                <div class="floating-shapes">
                    <div class="shape shape1"></div>
                    <div class="shape shape2"></div>
                    <div class="shape shape3"></div>
                </div>

                <!-- CÍRCULOS PULSANTES -->
                <div class="pulse-circles">
                    <div class="pulse-circle"></div>
                    <div class="pulse-circle delay-1"></div>
                    <div class="pulse-circle delay-2"></div>
                </div>
            </div>
        </div>

        <!-- LADO DERECHO - FORMULARIO -->
        <div class="right-panel">
            <div class="form-container">

                <h1>Bienvenido Administrador</h1>
                <p class="welcome-text">Ingresa tus credenciales para acceder</p>

                <!-- MENSAJES DE ERROR MEJORADOS -->
                <?php if (isset($_GET["error"])): ?>
                    <div class="error-message" id="errorMessage">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php
                        if ($_GET["error"] === "credenciales") {
                            echo "Correo o contraseña incorrectos";
                        } elseif ($_GET["error"] === "campos_vacios") {
                            echo "Todos los campos son obligatorios";
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <form action="../Controlador/validar_login.php" method="POST" id="loginForm">

                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="input-field">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" id="email" name="email" placeholder="ejemplo@correo.com" required autocomplete="off">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="input-field">
                            <label for="password">Contraseña</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="off">
                                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                            </div>
                        </div>
                    </div>



                    <button type="submit" class="login-btn" id="loginBtn">
                        <span>Entrar</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>

                </form>

            </div>
        </div>
    </div>

    <!-- SCRIPTS PARA INTERACCIÓN -->
    <script>
        // Mostrar/Ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Efecto en botón al hacer click
        const loginBtn = document.getElementById('loginBtn');
        loginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.add('btn-click');
            setTimeout(() => {
                document.getElementById('loginForm').submit();
            }, 300);
        });

        // Animación en inputs al escribir
        const inputs = document.querySelectorAll('.input-field input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>

</body>

</html>