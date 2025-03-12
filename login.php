<?php
session_start();
require_once 'config/database.php';
require_once 'functions/auth.php';

// Verificar si el usuario ya está logueado
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Fuente Google para mejor apariencia -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
    .bg-custom-red {
        background-color: #90151C !important;
    }
    
    .login-animation {
        animation: fadeInDown 0.7s both;
    }
    
    .login-image {
        position: relative;
        overflow: hidden;
        border-radius: 10px 0 0 10px;
        min-height: 100%;
        background: linear-gradient(135deg, rgba(144, 21, 28, 0.8), rgba(255, 107, 107, 0.8)), url('assets/images/logofamisa.png') center/contain no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        color: white;
        text-align: center;
    }
    
    .login-image::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        animation: pulse-glow 4s infinite;
    }
    
    .login-panel {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
    }
    
    .floating-label {
        position: relative;
        margin-bottom: 20px;
    }
    
    .floating-label input {
        width: 100%;
        padding: 10px 15px;
        font-size: 16px;
        border: 0;
        border-bottom: 2px solid #ddd;
        background-color: transparent;
        transition: border-color 0.3s;
        outline: none;
    }
    
    .floating-label label {
        position: absolute;
        top: 10px;
        left: 15px;
        font-size: 16px;
        color: #999;
        pointer-events: none;
        transition: all 0.3s ease;
    }
    
    .floating-label input:focus,
    .floating-label input:not(:placeholder-shown) {
        border-bottom-color: #90151C;
    }
    
    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label {
        transform: translateY(-20px);
        font-size: 12px;
        color: #90151C;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #90151C, #BB595F);
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        font-weight: 500;
        letter-spacing: 0.5px;
        box-shadow: 0 5px 15px rgba(144, 21, 28, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(144, 21, 28, 0.4);
        background: linear-gradient(135deg, #7a1017, #BB595F);
    }
    
    .login-footer {
        text-align: center;
        margin-top: 30px;
    }
    
    .login-footer a {
        color: #90151C;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .login-footer a:hover {
        color: #7a1017;
        text-decoration: underline;
    }
    
    @media (max-width: 767px) {
        .login-image {
            border-radius: 10px 10px 0 0;
            min-height: 150px;
        }
    }
    </style>
</head>
<body>
    <header class="py-2 shadow-sm">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center">
                <!-- Logo y título con animación mejorada -->
                <div class="d-flex align-items-center me-auto" style="max-width: 25%;">
                    <img src="assets/images/logofamisa.png" alt="Logo" class="logo animate__animated animate__flipInY" style="max-height: 35px;">
                    <h1 class="animate__animated animate__fadeInLeft fs-5 mb-0 ms-2">Programa de Recompensas</h1>
                </div>
                
                <!-- Menú de navegación con animaciones -->
                <nav class="navbar navbar-expand-lg p-0">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a href="index.php" class="nav-link px-2 fade-in-down delay-1"><i class="fas fa-home"></i><span class="d-none d-md-inline ms-1">Inicio</span></a></li>
                            <li class="nav-item"><a href="login.php" class="nav-link px-2 fade-in-down delay-2"><i class="fas fa-sign-in-alt"></i><span class="d-none d-md-inline ms-1">Iniciar</span></a></li>
                            <li class="nav-item"><a href="register.php" class="nav-link px-2 fade-in-down delay-3"><i class="fas fa-user-plus"></i><span class="d-none d-md-inline ms-1">Registrarse</span></a></li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-9">
                    <div class="login-panel animate__animated animate__fadeIn">
                        <div class="row g-0">
                            <!-- Panel de imagen lateral -->
                            <div class="col-md-5 d-none d-md-block">
                                <div class="login-image">
                                    <div class="position-relative" style="z-index: 1;">
                                        <i class="fas fa-users fa-3x mb-3 animate__animated animate__pulse animate__infinite" style="--animate-duration: 3s;"></i>
                                        <h3 class="mb-3">¡Bienvenido!</h3>
                                        <p>Inicia sesión para acceder al Programa de Recompensas y compartir tus ideas.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Formulario de login -->
                            <div class="col-md-7">
                                <div class="card border-0 h-100">
                                    <div class="card-header bg-custom-red text-white text-center py-3">
                                        <h2 class="mb-0 animate__animated animate__fadeInDown">Iniciar Sesión</h2>
                                    </div>
                                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger text-center mb-3 animate__animated animate__shakeX">
                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                <?php echo $error; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($success): ?>
                                            <div class="alert alert-success text-center mb-3 animate__animated animate__bounceIn">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <?php echo $success; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="post" action="" class="fade-in-up">
                                            <div class="floating-label mb-4">
                                                <input type="text" id="username" name="username" required placeholder=" ">
                                                <label for="username"><i class="fas fa-user me-2"></i>Usuario</label>
                                            </div>
                                            
                                            <div class="floating-label mb-4">
                                                <input type="password" id="password" name="password" required placeholder=" ">
                                                <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                            </div>
                                            
                                            <div class="d-grid mt-4">
                                                <button type="submit" class="btn btn-login text-white btn-lg pulse">
                                                    <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <div class="login-footer animate__animated animate__fadeIn animate__delay-1s">
                                            <p>¿No tienes una cuenta? <a href="register.php" class="fw-bold">Regístrate aquí</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p class="animate__animated animate__fadeIn">&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <!-- Script de animaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efecto de ondas para botones
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.type !== 'submit' || this.form.checkValidity()) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;
                    
                    const ripple = document.createElement('span');
                    ripple.classList.add('ripple-effect');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                }
            });
        });
        
        // Efecto para campos de formulario
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('highlight-active');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('highlight-active');
            });
        });
    });
    </script>
</body>
</html>