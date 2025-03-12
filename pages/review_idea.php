<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado y tenga el rol adecuado
checkLogin();
checkRole(['revisor', 'admin']);

// Verificar que se haya proporcionado un ID de idea
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: review.php');
    exit;
}

// Obtener la idea
$idea = getIdeaById($_GET['id']);

// Verificar que la idea exista y esté pendiente
if (!$idea || $idea['status'] !== 'pendiente') {
    header('Location: review.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'review') {
        $points = $_POST['points'] ?? '';
        $comments = $_POST['comments'] ?? '';
        
        if (empty($points) || !is_numeric($points) || $points < 0) {
            $error = 'Por favor, ingresa una puntuación válida';
        } else {
            $result = reviewIdea($idea['id'], $points, $comments, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    } elseif ($action === 'reject') {
        $reject_reason = $_POST['reject_reason'] ?? '';
        
        if (empty($reject_reason)) {
            $error = 'Por favor, proporciona un motivo para rechazar la idea';
        } else {
            $result = rejectIdeaWithReason($idea['id'], $reject_reason, $_SESSION['user_id']);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = 'Acción no válida';
    }
}

// Función para rechazar idea con razón (añadir a functions/ideas.php)
if (!function_exists('rejectIdeaWithReason')) {
    function rejectIdeaWithReason($idea_id, $reason, $reviewer_id) {
        $conn = conectarDB();
        
        $stmt = $conn->prepare("UPDATE ideas SET 
                                status = 'rechazada', 
                                reviewer_comments = ?,
                                reviewer_id = ?,
                                reviewed_at = CURRENT_TIMESTAMP
                                WHERE id = ?");
        $stmt->bind_param("sii", $reason, $reviewer_id, $idea_id);
        $success = $stmt->execute();
        
        $response = [
            'success' => $success,
            'message' => $success ? 'Idea rechazada exitosamente' : 'Error al rechazar la idea'
        ];
        
        $stmt->close();
        $conn->close();
        return $response;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Idea - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Fuente Google para mejor apariencia -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs .nav-link {
            margin-bottom: -2px;
            color: #6c757d !important;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            border: none;
            padding: 12px 20px;
            border-radius: 0;
        }
        
        .nav-tabs .nav-link:hover {
            background-color: rgba(0,0,0,0.03);
            border-color: transparent;
        }
        
        .nav-tabs .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            transition: width 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            color: #495057 !important;
            background-color: transparent;
            border-color: transparent;
            font-weight: 600;
        }
        
        .nav-tabs .nav-link.text-success::after {
            background-color: #198754;
        }
        
        .nav-tabs .nav-link.text-danger::after {
            background-color: #dc3545;
        }
        
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }
        
        .nav-tabs .nav-link.active.text-success {
            color: #198754 !important;
        }
        
        .nav-tabs .nav-link.active.text-danger {
            color: #dc3545 !important;
        }
        
        /* Estilos para tarjeta de idea */
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
        
        .idea-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
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
        
        .idea-description {
            background-color: rgba(0,0,0,0.02);
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #90151C;
        }
        
        /* Estilos para formularios */
        .form-container {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #90151C;
            box-shadow: 0 0 0 0.2rem rgba(144,21,28,0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #495057;
        }
        
        /* Botones mejorados */
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
        
        /* Efectos para iconos */
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
        
        /* Efecto de puntos flotantes */
        .idea-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.15) 2px, transparent 2px);
            background-size: 20px 20px;
            opacity: 0.5;
        }
        
        /* Input especial para puntos */
        .points-input {
            font-size: 24px;
            text-align: center;
            font-weight: bold;
            color: #198754;
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
        }
        
        .points-input:focus {
            background-color: white;
        }
        
        .info-row {
            margin-bottom: 10px;
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
        
        /* Alerta de ayuda */
        .help-alert {
            background-color: rgba(25, 135, 84, 0.1);
            border-left: 4px solid #198754;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
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
                    <i class="fas fa-clipboard-check me-2 text-primary"></i> Revisar Idea
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
                        <a href="review.php" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Volver a revisar ideas
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Tarjeta de idea mejorada -->
                    <div class="idea-card mb-4 animate__animated animate__fadeIn">
                        <div class="idea-header">
                            <h3 class="mb-0"><?php echo htmlspecialchars($idea['title']); ?></h3>
                        </div>
                        
                        <div class="idea-meta">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Categoría</small><br>
                                            <strong><?php echo htmlspecialchars($idea['category']); ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-row">
                                        <div class="icon-circle">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Empleado</small><br>
                                            <strong><?php echo htmlspecialchars($idea['full_name']); ?></strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
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
                            </div>
                        </div>
                        
                        <div class="idea-content">
                            <h5 class="mb-3"><i class="fas fa-file-alt me-2 text-primary"></i> Descripción de la idea:</h5>
                            <div class="idea-description">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($idea['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs para aprobar o rechazar -->
                    <ul class="nav nav-tabs mb-0 animate__animated animate__fadeIn" id="reviewTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active text-success" id="approve-tab" data-bs-toggle="tab" data-bs-target="#approve-content" 
                                    type="button" role="tab" aria-controls="approve-content" aria-selected="true">
                                <i class="fas fa-check-circle me-1"></i> Aprobar Idea
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-danger" id="reject-tab" data-bs-toggle="tab" data-bs-target="#reject-content" 
                                    type="button" role="tab" aria-controls="reject-content" aria-selected="false">
                                <i class="fas fa-times-circle me-1"></i> Rechazar Idea
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content animate__animated animate__fadeIn" id="reviewTabsContent">
                        <!-- Formulario para aprobar idea -->
                        <div class="tab-pane fade show active" id="approve-content" role="tabpanel" aria-labelledby="approve-tab">
                            <div class="form-container">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="review">
                                    
                                    <div class="text-center mb-4">
                                        <h5 class="mb-3">Asignar puntuación</h5>
                                        <input type="number" id="points" name="points" min="0" max="100" value="50" 
                                               class="form-control points-input mx-auto" style="max-width: 150px;" required>
                                        <div class="mt-2 text-muted">
                                            De 0 a 100 puntos, basado en la calidad e impacto de la idea
                                        </div>
                                        
                                        <div class="progress mt-3" style="height: 8px; max-width: 400px; margin: 0 auto;">
                                            <div class="progress-bar bg-danger" style="width: 33%"></div>
                                            <div class="progress-bar bg-warning" style="width: 34%"></div>
                                            <div class="progress-bar bg-success" style="width: 33%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2" style="max-width: 400px; margin: 0 auto;">
                                            <small>Bajo impacto</small>
                                            <small>Impacto medio</small>
                                            <small>Alto impacto</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="comments" class="form-label">
                                            <i class="fas fa-comment-alt me-1 text-primary"></i> Comentarios para el empleado:
                                        </label>
                                        <textarea id="comments" name="comments" rows="4" class="form-control" 
                                                  placeholder="Proporciona retroalimentación constructiva sobre la idea..."></textarea>
                                    </div>
                                    
                                    <div class="help-alert">
                                        <i class="fas fa-lightbulb me-2 text-success"></i>
                                        <strong>Consejo:</strong> Una retroalimentación constructiva ayuda al empleado a entender 
                                        el valor de su idea y cómo puede mejorar futuras propuestas.
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="review.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check me-1"></i> Aprobar y asignar puntos
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Formulario para rechazar idea -->
                        <div class="tab-pane fade" id="reject-content" role="tabpanel" aria-labelledby="reject-tab">
                            <div class="form-container">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="reject">
                                    
                                    <div class="mb-4">
                                        <label for="reject_reason" class="form-label">
                                            <i class="fas fa-exclamation-circle me-1 text-danger"></i> Motivo del rechazo:
                                        </label>
                                        <textarea id="reject_reason" name="reject_reason" rows="4" class="form-control" 
                                                  placeholder="Explica por qué la idea no cumple con los criterios..." required></textarea>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h5 class="alert-heading">Importante</h5>
                                                <p class="mb-0">Rechazar una idea debe hacerse con cuidado. Asegúrate de proporcionar retroalimentación constructiva que ayude al empleado a mejorar futuras ideas.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="review.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left me-1"></i> Volver
                                        </a>
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('¿Estás seguro de que quieres rechazar esta idea? Esta acción no se puede deshacer.');">
                                            <i class="fas fa-times me-1"></i> Rechazar Idea
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
        // Contador de puntos
        const pointsInput = document.getElementById('points');
        
        if (pointsInput) {
            // Cambiar color basado en el valor
            function updatePointsColor() {
                const value = parseInt(pointsInput.value);
                
                if (value < 34) {
                    pointsInput.style.color = '#dc3545'; // Rojo
                } else if (value < 67) {
                    pointsInput.style.color = '#ffc107'; // Amarillo
                } else {
                    pointsInput.style.color = '#198754'; // Verde
                }
            }
            
            // Actualizar color inicial
            updatePointsColor();
            
            // Actualizar color al cambiar el valor
            pointsInput.addEventListener('input', updatePointsColor);
        }
    
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
        
        // Animación para los tabs
        const tabButtons = document.querySelectorAll('.nav-tabs .nav-link');
        tabButtons.forEach(tab => {
            tab.addEventListener('click', function() {
                // Animar el contenido del tab seleccionado
                const targetId = this.getAttribute('data-bs-target');
                const targetContent = document.querySelector(targetId);
                
                if (targetContent) {
                    targetContent.classList.add('animate__animated', 'animate__fadeIn');
                    setTimeout(() => {
                        targetContent.classList.remove('animate__animated', 'animate__fadeIn');
                    }, 500);
                }
            });
        });
        
        // Animación de puntos flotantes en el header
        function createFloatingDots() {
            const header = document.querySelector('.idea-header');
            if (!header) return;
            
            for (let i = 0; i < 20; i++) {
                const dot = document.createElement('span');
                dot.className = 'floating-dot';
                
                // Posición aleatoria
                dot.style.left = `${Math.random() * 100}%`;
                dot.style.top = `${Math.random() * 100}%`;
                
                // Tamaño aleatorio
                const size = Math.random() * 5 + 2; // entre 2px y 7px
                dot.style.width = `${size}px`;
                dot.style.height = `${size}px`;
                
                // Animación aleatoria
                dot.style.animationDuration = `${Math.random() * 3 + 2}s`;
                dot.style.animationDelay = `${Math.random() * 2}s`;
                
                header.appendChild(dot);
            }
        }
        
        // Crear confeti en caso de éxito
        <?php if ($success): ?>
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
        
        // Lanzar confeti al cargar la página
        createConfetti();
        <?php endif; ?>
    });
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
    
    .floating-dot {
        position: absolute;
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-10px) translateX(5px); }
    }
    </style>
</body>
</html>