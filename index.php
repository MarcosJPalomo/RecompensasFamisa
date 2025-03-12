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
    <main class="d-flex align-items-center">
        <div class="container text-center">
            <h2 class="text-dark-red mb-4 main-title animate__animated animate__fadeInDown">Bienvenido al Programa de Recompensas</h2>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="card shadow-lg mx-auto user-card animate__animated animate__zoomIn" style="max-width: 700px;">
                    <div class="card-body">
                        <h3 class="card-title mb-3 animate__animated animate__fadeIn">Hola, <?php echo $_SESSION['full_name']; ?></h3>
                        <div class="row mb-3">
                            <div class="col-md-6 fade-in-up delay-1">
                                <p class="card-text">Rol: <span class="badge bg-info"><?php echo ucfirst($_SESSION['role']); ?></span></p>
                            </div>
                            <div class="col-md-6 fade-in-up delay-2">
                                <p class="card-text">Puntos acumulados: <span class="badge bg-primary points-counter"><?php echo $_SESSION['total_points']; ?></span></p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-center gap-3 mt-3">
                            <a href="pages/submit_idea.php" class="btn btn-primary animate__animated animate__pulse animate__infinite" style="--animate-duration: 2.5s;">Enviar una nueva idea</a>
                            <a href="pages/ideas.php" class="btn btn-secondary fade-in-right delay-1">Mis ideas</a>
                            <a href="pages/rewards.php" class="btn btn-success fade-in-right delay-2">Ver recompensas</a>
                            <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                                <a href="pages/review.php" class="btn btn-warning fade-in-right delay-3">Revisar ideas</a>
                            <?php endif; ?>
                            <?php if($_SESSION['role'] === 'admin'): ?>
                                <a href="pages/approve.php" class="btn btn-danger fade-in-right delay-4">Aprobar puntos</a>
                            <?php endif; ?>
                            <?php if($_SESSION['role'] === 'premiador' || $_SESSION['role'] === 'admin'): ?>
                                <a href="pages/redemptions.php" class="btn btn-info fade-in-right delay-5">Canje de Recompensas</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow-lg animate__animated animate__fadeInUp">
                            <div class="card-body p-4">
                                <h3 class="mb-3 animate__animated animate__fadeInDown animate__delay-1s">¿Qué es el Programa de Recompensas?</h3>
                                <p class="animate__animated animate__fadeIn animate__delay-1s">Este sistema permite a los empleados enviar ideas de mejora para la empresa, obtener puntos por ellas y canjear estos puntos por recompensas.</p>
                                
                                <div class="row mt-4">
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-1s">
                                        <div class="card h-100 highlight-container">
                                            <div class="card-body text-center">
                                                <h4>1. Envía ideas</h4>
                                                <p>Comparte tus propuestas para mejorar procesos, productos o servicios.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-2s">
                                        <div class="card h-100 highlight-container">
                                            <div class="card-body text-center">
                                                <h4>2. Gana puntos</h4>
                                                <p>Las ideas son evaluadas y recibes puntos según su impacto y viabilidad.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 animate__animated animate__fadeInUp animate__delay-3s">
                                        <div class="card h-100 highlight-container">
                                            <div class="card-body text-center">
                                                <h4>3. Obtén recompensas</h4>
                                                <p>Canjea tus puntos por diversos premios y reconocimientos.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4 animate__animated animate__fadeInUp animate__delay-4s">
                                    <a href="login.php" class="btn btn-primary me-2 pulse">Iniciar sesión</a>
                                    <a href="register.php" class="btn btn-outline-primary">Registrarse</a>
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