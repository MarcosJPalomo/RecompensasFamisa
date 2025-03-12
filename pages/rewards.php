<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/rewards.php';

// Verificar que el usuario esté logueado
checkLogin();

// Obtener recompensas disponibles
$rewards = getAllRewards();

// Obtener canjes del usuario
$redemptions = getUserRedemptions($_SESSION['user_id']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reward_id = $_POST['reward_id'] ?? '';
    
    if (empty($reward_id)) {
        $error = 'Recompensa no válida';
    } else {
        $result = redeemReward($_SESSION['user_id'], $reward_id);
        
        if ($result['success']) {
            $success = $result['message'];
            // Comentamos esta línea para evitar refrescos automáticos que puedan interferir con los mensajes
            // header('Refresh: 2');
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
    <title>Recompensas - Programa de Recompensas</title>
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
                <!-- Logo y título (más pequeño) -->
                <div class="d-flex align-items-center me-auto" style="max-width: 25%;">
                    <img src="../assets/images/logofamisa.png" alt="Logo" class="logo animate__animated animate__flipInY" style="max-height: 35px;">
                    <h1 class="animate__animated animate__fadeInLeft fs-5 mb-0 ms-2">Programa de Recompensas</h1>
                </div>
                
                <!-- Menú de navegación con elementos más compactos -->
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
            <h2 class="text-center main-title animate__animated animate__fadeInDown mb-4">Recompensas</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger animate__animated animate__fadeIn mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success animate__animated animate__fadeIn mb-4"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Sección de puntos mejorada con animación -->
            <div class="user-points shadow highlight-container mb-4 animate__animated animate__fadeInUp">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <i class="fas fa-coins fa-3x text-warning animate__animated animate__swing animate__infinite" style="--animate-duration: 3s;"></i>
                    </div>
                    <div class="col-md-8">
                        <h4 class="mb-2 animate__animated animate__fadeIn">Tu balance actual</h4>
                        <p class="mb-0 fs-4">Puntos disponibles: <strong class="points-counter"><?php echo $_SESSION['total_points']; ?></strong></p>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-gift me-2"></i> Recompensas Disponibles</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($rewards)): ?>
                        <div class="alert alert-info static-alert" style="animation: none !important; opacity: 1 !important; visibility: visible !important; display: block !important;">
                            <p class="mb-0">No hay recompensas disponibles en este momento.</p>
                        </div>
                    <?php else: ?>
                        <div class="rewards-list row">
                            <?php foreach ($rewards as $index => $reward): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="reward-card animate__animated animate__fadeIn" style="animation-delay: <?php echo $index * 150; ?>ms">
                                        <div class="ribbon <?php echo ($_SESSION['total_points'] >= $reward['points_required']) ? 'ribbon-available' : 'ribbon-locked'; ?>">
                                            <?php echo ($_SESSION['total_points'] >= $reward['points_required']) ? 'Disponible' : 'Bloqueado'; ?>
                                        </div>
                                        <h4><?php echo htmlspecialchars($reward['name']); ?></h4>
                                        <p><?php echo nl2br(htmlspecialchars($reward['description'])); ?></p>
                                        <p class="points-required">Puntos requeridos: <strong><?php echo $reward['points_required']; ?></strong></p>
                                        
                                        <form method="post" action="">
                                            <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                            <button type="submit" class="btn btn-primary <?php echo ($_SESSION['total_points'] >= $reward['points_required']) ? 'pulse' : ''; ?>" 
                                                    <?php echo ($_SESSION['total_points'] < $reward['points_required']) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-exchange-alt me-1"></i> Canjear
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-lg mb-4 animate__animated animate__fadeInUp animate__delay-2s">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0"><i class="fas fa-history me-2"></i> Mis Canjes</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($redemptions)): ?>
                        <div class="alert alert-info static-alert" style="animation: none !important; opacity: 1 !important; visibility: visible !important; display: block !important;">
                            <p class="mb-0">Aún no has canjeado ninguna recompensa.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr class="animate__animated animate__fadeInDown">
                                        <th>Recompensa</th>
                                        <th>Puntos</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($redemptions as $index => $redemption): ?>
                                        <tr class="animate__animated animate__fadeInRight" style="animation-delay: <?php echo $index * 100; ?>ms">
                                            <td><?php echo htmlspecialchars($redemption['reward_name']); ?></td>
                                            <td><?php echo $redemption['points_required']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($redemption['redeemed_at'])); ?></td>
                                            <td>
                                                <?php if($redemption['status'] === 'completado'): ?>
                                                    <span class="badge bg-success">Completado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-lg mb-4 animate__animated animate__fadeInUp animate__delay-3s">
                <div class="card-header bg-secondary text-white">
                    <h3 class="mb-0"><i class="fas fa-chart-line me-2"></i> Historial de Puntos</h3>
                </div>
                <div class="card-body">
                    <?php 
                    $history = getUserPointsHistory($_SESSION['user_id']);

                    if (empty($history)): ?>
                        <div class="alert alert-info static-alert" style="animation: none !important; opacity: 1 !important; visibility: visible !important; display: block !important;">
                            <p class="mb-0">Aún no tienes historial de puntos.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr class="animate__animated animate__fadeInDown">
                                        <th>Descripción</th>
                                        <th>Puntos</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $index => $entry): ?>
                                        <tr class="animate__animated animate__fadeInRight" style="animation-delay: <?php echo $index * 100; ?>ms">
                                            <td><?php echo htmlspecialchars($entry['description']); ?></td>
                                            <td><?php echo abs($entry['points']); ?></td>
                                            <td>
                                                <?php if($entry['type'] === 'earned'): ?>
                                                    <span class="badge bg-success">Ganados</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Gastados</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($entry['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
    <script src="../assets/js/script.js"></script>
    
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
        
        // Asegurar que los mensajes de alerta permanezcan visibles
        const staticAlerts = document.querySelectorAll('.static-alert');
        staticAlerts.forEach(function(alert) {
            // Forzar la visibilidad del alerta
            alert.style.display = 'block';
            alert.style.visibility = 'visible';
            alert.style.opacity = '1';
            alert.style.animation = 'none';
        });
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