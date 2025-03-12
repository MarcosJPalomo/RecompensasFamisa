<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado y tenga el rol adecuado
checkLogin();
checkRole(['revisor', 'admin']);

// Obtener ideas pendientes
$ideas = getPendingIdeas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Ideas - Programa de Recompensas</title>
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
    <main class="container">
        <div class="card shadow-lg p-4 mb-4 animate__animated animate__fadeIn">
            <h2 class="text-center main-title mb-4 animate__animated animate__fadeInDown">
                <i class="fas fa-clipboard-check me-2 text-primary"></i> Revisar Ideas
            </h2>

            <?php if (empty($ideas)): ?>
                <div class="alert alert-info static-alert animate__animated animate__fadeIn" style="animation: none !important; opacity: 1 !important; visibility: visible !important; display: block !important;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3 animate__animated animate__pulse animate__infinite" style="--animate-duration: 2s;"></i>
                        <p class="mb-0">No hay ideas pendientes de revisión en este momento.</p>
                    </div>
                </div>
                <!-- Mensaje motivacional para cuando no hay ideas -->
                <div class="text-center p-4 mt-3 animate__animated animate__fadeIn animate__delay-1s">
                    <i class="fas fa-coffee fa-3x mb-3 text-secondary"></i>
                    <h4>¡Todo al día!</h4>
                    <p>Vuelve más tarde para revisar nuevas ideas enviadas por los empleados.</p>
                </div>
            <?php else: ?>
                <div class="mb-4 highlight-container animate__animated animate__fadeIn">
                    <div class="alert alert-primary bg-light border-primary">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lightbulb fa-2x me-3 text-warning animate__animated animate__tada animate__infinite" style="--animate-duration: 3s;"></i>
                            <div>
                                <h5 class="mb-1">Tienes <?php echo count($ideas); ?> ideas pendientes de revisión</h5>
                                <p class="mb-0">Tu revisión cuidadosa ayuda a impulsar la innovación en la empresa.</p>
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
                                <th><i class="fas fa-user me-1"></i> Empleado</th>
                                <th><i class="fas fa-calendar-alt me-1"></i> Fecha</th>
                                <th><i class="fas fa-tasks me-1"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ideas as $index => $idea): ?>
                                <tr class="animate__animated animate__fadeInUp" style="animation-delay: <?php echo $index * 100; ?>ms">
                                    <td><?php echo $idea['title']; ?></td>
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
                                            <?php echo $idea['category']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $idea['full_name']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($idea['submitted_at'])); ?></td>
                                    <td>
                                        <a href="review_idea.php?id=<?php echo $idea['id']; ?>" class="btn btn-sm btn-primary pulse">
                                            <i class="fas fa-search me-1"></i> Revisar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Panel de estadísticas -->
                <div class="card mt-4 shadow-sm animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Estadísticas de revisión</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="display-4 fw-bold text-primary"><?php echo count($ideas); ?></div>
                                    <p class="text-muted">Ideas pendientes</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="display-4 fw-bold text-success">
                                        <?php 
                                            $conn = conectarDB();
                                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ideas WHERE status != 'pendiente' AND reviewer_id = ?");
                                            $stmt->bind_param("i", $_SESSION['user_id']);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $row = $result->fetch_assoc();
                                            echo $row['count'];
                                            $conn->close();
                                        ?>
                                    </div>
                                    <p class="text-muted">Ideas revisadas por ti</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="display-4 fw-bold text-info">
                                        <?php 
                                            $conn = conectarDB();
                                            $stmt = $conn->prepare("SELECT AVG(points_assigned) as avg FROM ideas WHERE status IN ('revisada', 'aprobada') AND reviewer_id = ?");
                                            $stmt->bind_param("i", $_SESSION['user_id']);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $row = $result->fetch_assoc();
                                            echo $row['avg'] ? number_format($row['avg'], 1) : '0.0';
                                            $conn->close();
                                        ?>
                                    </div>
                                    <p class="text-muted">Puntos promedio asignados</p>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <!-- Script de animaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>