<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado y tenga el rol adecuado
checkLogin();
checkRole(['admin']);

// Verificar que se haya proporcionado un ID de idea
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: approve.php');
    exit;
}

// Obtener la idea
$idea = getIdeaById($_GET['id']);

// Verificar que la idea exista y esté revisada pero no aprobada
if (!$idea || $idea['status'] !== 'revisada' || $idea['approved_by_admin']) {
    header('Location: approve.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $result = approveIdea($idea['id'], $_SESSION['user_id']);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'reject') {
        $result = rejectIdea($idea['id']);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Acción no válida';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprobar Idea - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Fuente Google para mejor apariencia -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos personalizados para esta página */
        .idea-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
        }
        
        .idea-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .idea-header {
            background: linear-gradient(45deg, #90151C, #BB595F);
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .idea-meta {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .idea-content {
            padding: 20px;
            background-color: white;
        }
        
        .info-row {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            padding: 8px;
            border-radius: 8px;
        }
        
        .info-row:hover {
            background-color: rgba(0,0,0,0.02);
            transform: translateX(5px);
        }
        
        .icon-circle {
            width: 40px;
            height: 40px;
            background-color: rgba(0,0,0,0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            transition: all 0.3s ease;
        }
        
        .icon-circle i {
            font-size: 18px;
            color: #90151C;
        }
        
        .info-row:hover .icon-circle {
            background-color: #90151C;
        }
        
        .info-row:hover .icon-circle i {
            color: white;
        }
        
        .action-panel {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
            border-top: 4px solid #90151C;
        }
        
        .btn {
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.3s ease;
        }
        
        .btn:hover::after {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #198754, #20c997);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #f07582);
            border: none;
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #6c757d, #adb5bd);
            border: none;
        }
        
        .floating-points {
            position: relative;
            display: inline-block;
            font-size: 24px;
            font-weight: bold;
            color: #198754;
            margin: 10px 0;
            padding: 10px 20px;
            border-radius: 50px;
            background-color: #e8f5e9;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
        }
        
        .reviewer-comment {
            position: relative;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #17a2b8;
        }
        
        .reviewer-comment::before {
            content: '"';
            font-size: 60px;
            color: rgba(0,0,0,0.05);
            position: absolute;
            top: 10px;
            left: 10px;
            line-height: 1;
        }
        
        .description-content {
            background-color: rgba(0,0,0,0.02);
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #90151C;
        }
    </style>
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
    
    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h2 class="text-center mb-4 animate__animated animate__fadeInDown">
                    <i class="fas fa-check-circle me-2 text-success"></i> Aprobar Idea
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger mb-4 animate__animated animate__shakeX">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success mb-4 animate__animated animate__bounceIn">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                    <div class="text-center animate__animated animate__fadeIn">
                        <a href="approve.php" class="btn btn-primary pulse">
                            <i class="fas fa-arrow-left me-2"></i> Volver a aprobar ideas
                        </a>
                    </div>
                    
                    <!-- Script para activar confeti en caso de éxito -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            createConfetti();
                        });
                    </script>
                <?php else: ?>
                    <!-- Tarjeta de idea mejorada -->
                    <div class="idea-card mb-4 animate__animated animate__fadeIn">
                        <div class="idea-header">
                            <h3 class="mb-0"><?php echo htmlspecialchars($idea['title']); ?></h3>
                        </div>
                        
                        <div class="idea-meta">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Categoría</small><br>
                                            <strong><?php echo htmlspecialchars($idea['category']); ?></strong></p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Empleado</small><br>
                                            <strong><?php echo htmlspecialchars($idea['full_name']); ?></strong></p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Fecha de envío</small><br>
                                            <strong><?php echo date('d/m/Y H:i', strtotime($idea['submitted_at'])); ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-star text-warning"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Puntos asignados</small><br>
                                            <div class="floating-points animate__animated animate__pulse animate__infinite" style="--animate-duration: 3s;">
                                                <?php echo $idea['points_assigned']; ?>
                                            </div>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Fecha de revisión</small><br>
                                            <strong><?php echo date('d/m/Y H:i', strtotime($idea['reviewed_at'])); ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($idea['reviewer_comments']): ?>
                            <div class="reviewer-comment animate__animated animate__fadeIn animate__delay-1s">
                                <h5><i class="fas fa-comment-dots me-2 text-info"></i> Comentarios del revisor:</h5>
                                <div class="mt-3">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($idea['reviewer_comments'])); ?></p>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="reviewer-comment animate__animated animate__fadeIn animate__delay-1s">
                                <h5><i class="fas fa-comment-dots me-2 text-info"></i> Comentarios del revisor:</h5>
                                <div class="mt-3">
                                    <p class="mb-0 text-muted"><em>No hay comentarios del revisor</em></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="idea-content">
                            <h4 class="mb-3"><i class="fas fa-file-alt me-2 text-primary"></i> Descripción de la idea:</h4>
                            <div class="description-content">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($idea['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Panel de acciones mejorado -->
                    <div class="action-panel animate__animated animate__fadeInUp animate__delay-1s">
                        <form method="post" action="">
                            <div class="text-center mb-4">
                                <h5 class="mb-3"><i class="fas fa-question-circle me-2 text-primary"></i> ¿Estás de acuerdo con la puntuación asignada a esta idea?</h5>
                                <p class="mb-3 text-muted">Al aprobar esta idea, los puntos asignados se sumarán al empleado. Al rechazarla, volverá a la cola de revisión.</p>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="approve.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Volver a lista de ideas
                                </a>
                                <div>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger me-2" 
                                            onclick="return confirm('¿Estás seguro de que quieres rechazar esta idea?');">
                                        <i class="fas fa-times me-2"></i> Rechazar idea
                                    </button>
                                    <button type="submit" name="action" value="approve" class="btn btn-success pulse">
                                        <i class="fas fa-check me-2"></i> Aprobar y asignar puntos
                                    </button>
                                </div>
                            </div>
                        </form>
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
        // Efecto de ondas para botones
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!this.hasAttribute('onclick') || confirm(this.getAttribute('onclick').replace('return confirm(\'', '').replace('\');', ''))) {
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
    
    <style>
    /* Estilos adicionales para animaciones */
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.4);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    }
    
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .confetti-container {
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        pointer-events: none;
        z-index: 9999;
    }
    
    .confetti {
        position: absolute;
        top: -10px;
        background-color: #90151C;
        opacity: 0.7;
        animation: confetti-fall 3s linear, confetti-shake 2s linear infinite;
    }
    
    @keyframes confetti-fall {
        0% { top: -10px; }
        100% { top: 100vh; }
    }
    
    @keyframes confetti-shake {
        0% { transform: translateX(0) rotate(0); }
        25% { transform: translateX(5px) rotate(90deg); }
        50% { transform: translateX(-5px) rotate(180deg); }
        75% { transform: translateX(5px) rotate(270deg); }
        100% { transform: translateX(-5px) rotate(360deg); }
    }
    </style>
</body>
</html>