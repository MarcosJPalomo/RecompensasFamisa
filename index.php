<?php
session_start();
require_once 'config/database.php';
require_once 'functions/auth.php';
updateSessionData($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Agregar Font Awesome para los iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Fuente Google para mejor apariencia -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos adicionales para los botones del menú */
        .menu-card {
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            height: 100%;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .menu-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .menu-card:hover .menu-icon {
            transform: scale(1.2);
        }
        
        .menu-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .menu-description {
            color: #6c757d;
            flex-grow: 1;
        }
        
        .menu-header {
            background: linear-gradient(135deg, var(--primary-red), #7a1017);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        
        .menu-body {
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex-grow: 1;
        }
        
        .btn-menu {
            width: 100%;
            padding: 12px;
            font-weight: 500;
            margin-top: 15px;
            border-radius: 8px;
        }
        
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
                
                <!-- Menú de navegación con elementos más compactos y animados -->
                <nav class="navbar navbar-expand-lg p-0">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a href="index.php" class="nav-link px-2 fade-in-down delay-1"><i class="fas fa-home"></i><span class="d-none d-md-inline ms-1">Inicio</span></a></li>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <li class="nav-item"><a href="pages/ideas.php" class="nav-link px-2 fade-in-down delay-2"><i class="fas fa-lightbulb"></i><span class="d-none d-md-inline ms-1">Ideas</span></a></li>
                                <li class="nav-item"><a href="pages/submit_idea.php" class="nav-link px-2 fade-in-down delay-3"><i class="fas fa-plus-circle"></i><span class="d-none d-md-inline ms-1">Enviar</span></a></li>
                                <li class="nav-item"><a href="pages/rewards.php" class="nav-link px-2 fade-in-down delay-4"><i class="fas fa-gift"></i><span class="d-none d-md-inline ms-1">Recompensas</span></a></li>
                                
                                <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="pages/review.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-clipboard-check"></i><span class="d-none d-md-inline ms-1">Revisar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="pages/approve.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-check-circle"></i><span class="d-none d-md-inline ms-1">Aprobar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'premiador' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="pages/redemptions.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-exchange-alt"></i><span class="d-none d-md-inline ms-1">Canje</span></a></li>
                                <?php endif; ?>
                                
                                <li class="nav-item"><a href="logout.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline ms-1">Salir</span></a></li>
                            <?php else: ?>
                                <li class="nav-item"><a href="login.php" class="nav-link px-2 fade-in-down delay-2"><i class="fas fa-sign-in-alt"></i><span class="d-none d-md-inline ms-1">Iniciar</span></a></li>
                                <li class="nav-item"><a href="register.php" class="nav-link px-2 fade-in-down delay-3"><i class="fas fa-user-plus"></i><span class="d-none d-md-inline ms-1">Registrarse</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <main class="d-flex align-items-center py-5">
        <div class="container">
            <h2 class="text-dark-red mb-4 main-title animate__animated animate__fadeInDown text-center">Bienvenido al Programa de Recompensas</h2>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- Dashboard para usuarios logueados -->
                <div class="dashboard-card mx-auto animate__animated animate__zoomIn mb-5" style="max-width: 900px;">
                    <div class="card-body p-4">
                        <h3 class="card-title mb-4 animate__animated animate__fadeIn text-center">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            Hola, <?php echo $_SESSION['full_name']; ?>
                        </h3>
                        
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-4 mb-3 mb-md-0 text-center text-md-start">
                                <span class="badge bg-info fade-in-up delay-1 fs-6 px-3 py-2">
                                    <i class="fas fa-id-badge me-1"></i> <?php echo ucfirst($_SESSION['role']); ?>
                                </span>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0 text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                    <i class="fas fa-coins text-warning me-2 fa-2x"></i>
                                    <div>
                                        <span class="d-block text-muted fs-6">Puntos acumulados:</span>
                                        <span class="badge bg-primary fs-5 points-counter px-3 py-2"><?php echo $_SESSION['total_points']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center text-md-end">
                                <a href="pages/submit_idea.php" class="btn btn-primary animate__animated animate__pulse animate__infinite" style="--animate-duration: 2.5s;">
                                    <i class="fas fa-plus-circle me-1"></i> Nueva idea
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Menú de opciones como tarjetas -->
                <div class="row justify-content-center">
                    <!-- Tarjeta para Enviar Ideas -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Ideas</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-lightbulb menu-icon text-warning"></i>
                                <h5 class="menu-title">Enviar Ideas</h5>
                                <p class="menu-description">Comparte tus propuestas para mejorar la empresa</p>
                                <a href="pages/submit_idea.php" class="btn btn-primary btn-menu">
                                    <i class="fas fa-plus-circle me-2"></i> Nueva Idea
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tarjeta para Mis Ideas -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i> Seguimiento</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-clipboard-list menu-icon text-info"></i>
                                <h5 class="menu-title">Mis Ideas</h5>
                                <p class="menu-description">Consulta el estado de todas tus ideas enviadas</p>
                                <a href="pages/ideas.php" class="btn btn-info btn-menu text-white">
                                    <i class="fas fa-search me-2"></i> Ver Mis Ideas
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tarjeta para Recompensas -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-gift me-2"></i> Premios</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-award menu-icon text-warning"></i>
                                <h5 class="menu-title">Recompensas</h5>
                                <p class="menu-description">Canjea tus puntos por increíbles premios</p>
                                <a href="pages/rewards.php" class="btn btn-success btn-menu">
                                    <i class="fas fa-gift me-2"></i> Ver Recompensas
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                    <!-- Tarjeta para Revisar Ideas -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-4s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Revisión</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-tasks menu-icon text-primary"></i>
                                <h5 class="menu-title">Revisar Ideas</h5>
                                <p class="menu-description">Evalúa y asigna puntos a las ideas recibidas</p>
                                <a href="pages/review.php" class="btn btn-warning btn-menu text-dark">
                                    <i class="fas fa-clipboard-check me-2"></i> Revisar Ideas
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($_SESSION['role'] === 'admin'): ?>
                    <!-- Tarjeta para Aprobar Puntos -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-5s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i> Aprobación</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-stamp menu-icon text-danger"></i>
                                <h5 class="menu-title">Aprobar Puntos</h5>
                                <p class="menu-description">Aprueba la asignación final de puntos</p>
                                <a href="pages/approve.php" class="btn btn-danger btn-menu">
                                    <i class="fas fa-check-double me-2"></i> Aprobar Puntos
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($_SESSION['role'] === 'premiador' || $_SESSION['role'] === 'admin'): ?>
                    <!-- Tarjeta para Canje de Recompensas -->
                    <div class="col-md-4 col-lg-3 mb-4 animate__animated animate__fadeInUp animate__delay-6s">
                        <div class="menu-card">
                            <div class="menu-header">
                                <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Canjes</h4>
                            </div>
                            <div class="menu-body">
                                <i class="fas fa-exchange-alt menu-icon text-info"></i>
                                <h5 class="menu-title">Gestión de Canjes</h5>
                                <p class="menu-description">Administra las solicitudes de recompensas</p>
                                <a href="pages/redemptions.php" class="btn btn-info btn-menu text-white">
                                    <i class="fas fa-sync-alt me-2"></i> Gestionar Canjes
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php else: ?>
                <!-- Página de bienvenida para usuarios no logueados -->
                <div class="row justify-content-center">
                    <div class="col-md-10 col-lg-8">
                        <div class="card shadow-lg animate__animated animate__fadeInUp">
                            <div class="card-body p-5">
                                <h3 class="mb-4 text-center animate__animated animate__fadeInDown animate__delay-1s">
                                    <i class="fas fa-star text-warning me-2"></i>
                                    ¿Qué es el Programa de Recompensas?
                                </h3>
                                <p class="lead text-center mb-5 animate__animated animate__fadeIn animate__delay-1s">
                                    Este sistema permite a los empleados enviar ideas de mejora para la empresa, 
                                    obtener puntos por ellas y canjear estos puntos por recompensas.
                                </p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
                                        <div class="card h-100 highlight-container text-center shadow-sm">
                                            <div class="card-body p-4">
                                                <i class="fas fa-lightbulb fa-3x mb-3 text-primary"></i>
                                                <h4>1. Envía ideas</h4>
                                                <p>Comparte tus propuestas para mejorar procesos, productos o servicios.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
                                        <div class="card h-100 highlight-container text-center shadow-sm">
                                            <div class="card-body p-4">
                                                <i class="fas fa-coins fa-3x mb-3 text-warning"></i>
                                                <h4>2. Gana puntos</h4>
                                                <p>Las ideas son evaluadas y recibes puntos según su impacto y viabilidad.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-3s">
                                        <div class="card h-100 highlight-container text-center shadow-sm">
                                            <div class="card-body p-4">
                                                <i class="fas fa-gift fa-3x mb-3 text-success"></i>
                                                <h4>3. Obtén recompensas</h4>
                                                <p>Canjea tus puntos por diversos premios y reconocimientos.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-5 animate__animated animate__fadeInUp animate__delay-4s">
                                    <a href="login.php" class="btn btn-lg btn-primary me-3 pulse">
                                        <i class="fas fa-sign-in-alt me-2"></i> Iniciar sesión
                                    </a>
                                    <a href="register.php" class="btn btn-lg btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i> Registrarse
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p class="animate__animated animate__fadeIn">&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    
    <!-- Scripts originales -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    
    <!-- Script de animaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación para el contador de puntos
        const pointsCounter = document.querySelector('.points-counter');
        if (pointsCounter) {
            const finalValue = parseInt(pointsCounter.textContent);
            const duration = 1500;
            let startValue = 0;
            const increment = Math.ceil(finalValue / (duration / 16));
            
            const updateCounter = () => {
                startValue += increment;
                if (startValue < finalValue) {
                    pointsCounter.textContent = startValue;
                    requestAnimationFrame(updateCounter);
                } else {
                    pointsCounter.textContent = finalValue;
                }
            };
            
            // Iniciar la animación
            updateCounter();
        }
        
        // Efecto de ondas para botones
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
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
            });
        });
        
        // Si hay mensaje de éxito, mostrar efecto de confeti
        const successMessage = document.querySelector('.alert-success');
        if (successMessage) {
            createConfetti();
        }
    });
    
    // Crear efecto de confeti
    function createConfetti() {
        const container = document.createElement('div');
        container.className = 'confetti-container';
        document.body.appendChild(container);
        
        // Crear piezas de confeti
        const colors = ['#90151C', '#f8d568', '#28a745', '#17a2b8', '#ffc107'];
        
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            confetti.style.opacity = Math.random() + 0.5;
            confetti.style.width = (Math.random() * 8 + 5) + 'px';
            confetti.style.height = (Math.random() * 8 + 5) + 'px';
            confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
            
            container.appendChild(confetti);
        }
        
        // Eliminar después de completar la animación
        setTimeout(() => {
            container.remove();
        }, 5000);
    }
    </script>
</body>
</html>