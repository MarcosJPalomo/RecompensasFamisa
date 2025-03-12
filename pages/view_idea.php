<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado
checkLogin();

// Verificar que se haya proporcionado un ID de idea
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ideas.php');
    exit;
}

// Obtener la idea
$idea = getIdeaById($_GET['id']);

// Verificar que la idea exista y pertenezca al usuario actual o que sea un revisor/admin
if (!$idea || ($idea['user_id'] != $_SESSION['user_id'] && $_SESSION['role'] !== 'revisor' && $_SESSION['role'] !== 'admin')) {
    header('Location: ideas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Idea - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Agregar Font Awesome para los iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="py-2 shadow-sm">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center">
                <!-- Logo y título (más pequeño) -->
                <div class="d-flex align-items-center me-auto" style="max-width: 25%;">
                    <img src="../assets/images/logofamisa.png" alt="Logo" class="logo" style="max-height: 35px;">
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
                            <li class="nav-item"><a href="../index.php" class="nav-link px-2"><i class="fas fa-home"></i><span class="d-none d-md-inline ms-1">Inicio</span></a></li>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <li class="nav-item"><a href="ideas.php" class="nav-link px-2"><i class="fas fa-lightbulb"></i><span class="d-none d-md-inline ms-1">Ideas</span></a></li>
                                <li class="nav-item"><a href="submit_idea.php" class="nav-link px-2"><i class="fas fa-plus-circle"></i><span class="d-none d-md-inline ms-1">Enviar</span></a></li>
                                <li class="nav-item"><a href="rewards.php" class="nav-link px-2"><i class="fas fa-gift"></i><span class="d-none d-md-inline ms-1">Recompensas</span></a></li>
                                
                                <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="review.php" class="nav-link px-2"><i class="fas fa-clipboard-check"></i><span class="d-none d-md-inline ms-1">Revisar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="approve.php" class="nav-link px-2"><i class="fas fa-check-circle"></i><span class="d-none d-md-inline ms-1">Aprobar</span></a></li>
                                <?php endif; ?>
                                
                                <?php if($_SESSION['role'] === 'premiador' || $_SESSION['role'] === 'admin'): ?>
                                    <li class="nav-item"><a href="redemptions.php" class="nav-link px-2"><i class="fas fa-exchange-alt"></i><span class="d-none d-md-inline ms-1">Canje</span></a></li>
                                <?php endif; ?>
                                
                                <li class="nav-item"><a href="../logout.php" class="nav-link px-2"><i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline ms-1">Salir</span></a></li>
                            <?php else: ?>
                                <li class="nav-item"><a href="../login.php" class="nav-link px-2"><i class="fas fa-sign-in-alt"></i><span class="d-none d-md-inline ms-1">Iniciar</span></a></li>
                                <li class="nav-item"><a href="../register.php" class="nav-link px-2"><i class="fas fa-user-plus"></i><span class="d-none d-md-inline ms-1">Registrarse</span></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="card shadow-lg p-4 mb-4 animate__animated animate__fadeIn">
            <h2 class="text-center mb-4">Detalles de la Idea</h2>

            <div class="idea-details">
                <h3 class="mb-3"><?php echo htmlspecialchars($idea['title']); ?></h3>
                
                <div class="idea-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Información general</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($idea['category']); ?></p>
                                    <p><strong>Estado:</strong> 
                                        <?php 
                                            switch ($idea['status']) {
                                                case 'pendiente':
                                                    echo '<span class="badge badge-pending">Pendiente de revisión</span>';
                                                    break;
                                                case 'revisada':
                                                    echo '<span class="badge badge-reviewed">Revisada, pendiente de aprobación</span>';
                                                    break;
                                                case 'aprobada':
                                                    echo '<span class="badge badge-approved">Aprobada</span>';
                                                    break;
                                                case 'rechazada':
                                                    echo '<span class="badge badge-rejected">Rechazada</span>';
                                                    break;
                                            }
                                        ?>
                                    </p>
                                    <p><strong>Fecha de envío:</strong> <?php echo date('d/m/Y H:i', strtotime($idea['submitted_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Información de revisión</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($idea['points_assigned'] !== null): ?>
                                        <p><strong>Puntos asignados:</strong> <span class="badge bg-primary"><?php echo $idea['points_assigned']; ?></span></p>
                                    <?php else: ?>
                                        <p><strong>Puntos asignados:</strong> <span class="text-muted">Pendiente</span></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($idea['reviewed_at']): ?>
                                        <p><strong>Fecha de revisión:</strong> <?php echo date('d/m/Y H:i', strtotime($idea['reviewed_at'])); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($idea['approved_at']): ?>
                                        <p><strong>Fecha de aprobación:</strong> <?php echo date('d/m/Y H:i', strtotime($idea['approved_at'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($idea['reviewer_comments']): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <h5>Comentarios del revisor:</h5>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($idea['reviewer_comments'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="idea-description">
                    <h4 class="mb-2">Descripción:</h4>
                    <div class="p-3 bg-white rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($idea['description'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="ideas.php" class="btn btn-primary">Volver a mis ideas</a>
            </div>
        </div>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>