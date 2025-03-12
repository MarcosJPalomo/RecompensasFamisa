<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado
checkLogin();

// Obtener ideas del usuario
$ideas = getUserIdeas($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Ideas - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Fuente Google para mejor apariencia -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="py-2 shadow-sm">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center">
                <!-- Logo y título con animación mejorada -->
                <div class="d-flex align-items-center me-auto" style="max-width: 25%;">
                    <img src="../assets/images/logofamisa.png" alt="Logo" class="logo animate__animated animate__flipInY" style="max-height: 35px;">
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
                            <li class="nav-item"><a href="../index.php" class="nav-link px-2 fade-in-down delay-1"><i class="fas fa-home"></i><span class="d-none d-md-inline ms-1">Inicio</span></a></li>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <li class="nav-item"><a href="ideas.php" class="nav-link px-2 fade-in-down delay-2"><i class="fas fa-lightbulb"></i><span class="d-none d-md-inline ms-1">Ideas</span></a></li>
                                <li class="nav-item"><a href="submit_idea.php" class="nav-link px-2 fade-in-down delay-3"><i class="fas fa-plus-circle"></i><span class="d-none d-md-inline ms-1">Enviar</span></a></li>
                                <li class="nav-item"><a href="rewards.php" class="nav-link px-2 fade-in-down delay-4"><i class="fas fa-gift"></i><span class="d-none d-md-inline ms-1">Recompensas</span></a></li>
                                
                                <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="review.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-clipboard-check"></i><span class="d-none d-md-inline ms-1">Revisar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="approve.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-check-circle"></i><span class="d-none d-md-inline ms-1">Aprobar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'premiador' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="redemptions.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-exchange-alt"></i><span class="d-none d-md-inline ms-1">Canje</span></a></li>
                                <?php endif; ?>
                                
                                <li class="nav-item"><a href="../logout.php" class="nav-link px-2 fade-in-down delay-5"><i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline ms-1">Salir</span></a></li>
                            <?php else: ?>
                                <li class="nav-item"><a href="../login.php" class="nav-link px-2 fade-in-down delay-2"><i class="fas fa-sign-in-alt"></i><span class="d-none d-md-inline ms-1">Iniciar</span></a></li>
                                <li class="nav-item"><a href="../register.php" class="nav-link px-2 fade-in-down delay-3"><i class="fas fa-user-plus"></i><span class="d-none d-md-inline ms-1">Registrarse</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <div class="card shadow-lg p-4 mb-4 animate__animated animate__fadeIn">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="main-title animate__animated animate__fadeInDown">
                        <i class="fas fa-lightbulb text-warning me-2"></i> Mis Ideas
                    </h2>
                    <a href="submit_idea.php" class="btn btn-primary animate__animated animate__pulse animate__infinite" style="--animate-duration: 2s;">
                        <i class="fas fa-plus-circle me-2"></i> Enviar nueva idea
                    </a>
                </div>

                <?php if (empty($ideas)): ?>
                    <div class="alert alert-info animate__animated animate__fadeIn">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x text-info"></i>
                            </div>
                            <div>
                                <p class="mb-0">Aún no has enviado ninguna idea. ¡Comparte tus propuestas para mejorar la empresa!</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center p-5 mt-3 animate__animated animate__fadeInUp animate__delay-1s">
                        <img src="../assets/images/idea-empty.svg" alt="Sin ideas" style="max-width: 200px; opacity: 0.7;" class="mb-4">
                        <h4>¡Es momento de compartir tus ideas!</h4>
                        <p class="text-muted mb-4">Tus propuestas pueden transformar nuestra empresa y ser recompensadas.</p>
                        <a href="submit_idea.php" class="btn btn-lg btn-primary pulse">
                            <i class="fas fa-lightbulb me-2"></i> Enviar mi primera idea
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Panel de estadísticas -->
                    <div class="row mb-4 animate__animated animate__fadeIn">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-lightbulb fa-3x mb-3"></i>
                                    <h5 class="card-title">Total de Ideas</h5>
                                    <p class="display-4 fw-bold"><?php echo count($ideas); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                                    <h5 class="card-title">Ideas Aprobadas</h5>
                                    <p class="display-4 fw-bold">
                                    <?php 
                                        $approved = array_filter($ideas, function($idea) {
                                            return $idea['status'] === 'aprobada';
                                        });
                                        echo count($approved);
                                    ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-star fa-3x mb-3"></i>
                                    <h5 class="card-title">Puntos Obtenidos</h5>
                                    <p class="display-4 fw-bold">
                                    <?php 
                                        $points = array_reduce($ideas, function($total, $idea) {
                                            return $total + ($idea['status'] === 'aprobada' ? $idea['points_assigned'] : 0);
                                        }, 0);
                                        echo $points;
                                    ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive animate__animated animate__fadeIn animate__delay-1s">
                        <table class="table">
                            <thead>
                                <tr class="animate__animated animate__fadeInDown">
                                    <th><i class="fas fa-heading me-1"></i> Título</th>
                                    <th><i class="fas fa-tag me-1"></i> Categoría</th>
                                    <th><i class="fas fa-tasks me-1"></i> Estado</th>
                                    <th><i class="fas fa-star me-1"></i> Puntos</th>
                                    <th><i class="fas fa-calendar-alt me-1"></i> Fecha</th>
                                    <th><i class="fas fa-cogs me-1"></i> Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ideas as $index => $idea): ?>
                                    <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 100; ?>ms">
                                        <td><?php echo htmlspecialchars($idea['title']); ?></td>
                                        <td>
                                            <span class="badge rounded-pill 
                                                <?php 
                                                switch ($idea['category']) {
                                                    case 'Procesos': echo 'bg-primary'; break;
                                                    case 'Productos': echo 'bg-success'; break;
                                                    case 'Servicio al cliente': echo 'bg-info'; break;
                                                    case 'Tecnología': echo 'bg-warning text-dark'; break;
                                                    case 'Ambiente laboral': echo 'bg-secondary'; break;
                                                    default: echo 'bg-dark';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($idea['category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                switch ($idea['status']) {
                                                    case 'pendiente':
                                                        echo '<span class="badge badge-pending"><i class="fas fa-hourglass-half me-1"></i> Pendiente</span>';
                                                        break;
                                                    case 'revisada':
                                                        echo '<span class="badge badge-reviewed"><i class="fas fa-search me-1"></i> Revisada</span>';
                                                        break;
                                                    case 'aprobada':
                                                        echo '<span class="badge badge-approved"><i class="fas fa-check me-1"></i> Aprobada</span>';
                                                        break;
                                                    case 'rechazada':
                                                        echo '<span class="badge badge-rejected"><i class="fas fa-times me-1"></i> Rechazada</span>';
                                                        break;
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($idea['points_assigned'] === null) {
                                                    echo '<span class="text-muted"><i class="fas fa-hourglass me-1"></i> Pendiente</span>';
                                                } else {
                                                    echo '<span class="fw-bold points-counter">' . $idea['points_assigned'] . '</span>';
                                                }
                                            ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($idea['submitted_at'])); ?></td>
                                        <td>
                                            <a href="view_idea.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> Ver detalles
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p class="animate__animated animate__fadeIn">&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <!-- Script de animaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación para los contadores de puntos
        const pointsCounters = document.querySelectorAll('.points-counter');
        pointsCounters.forEach(counter => {
            const finalValue = parseInt(counter.textContent);
            if (!isNaN(finalValue)) {
                const duration = 1000;
                let startValue = 0;
                const increment = Math.ceil(finalValue / (duration / 16));
                
                const updateCounter = () => {
                    startValue += increment;
                    if (startValue < finalValue) {
                        counter.textContent = startValue;
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = finalValue;
                    }
                };
                
                // Iniciar la animación
                updateCounter();
            }
        });
    
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
    });
    </script>
</body>
</html>