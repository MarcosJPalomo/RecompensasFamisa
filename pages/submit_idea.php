<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';
require_once '../functions/ideas.php';

// Verificar que el usuario esté logueado
checkLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = 'Por favor, completa todos los campos';
    } else {
        $result = submitIdea($title, $description, $category, $_SESSION['user_id']);
        
        if ($result['success']) {
            $success = $result['message'];
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
    <title>Enviar Idea - Programa de Recompensas</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg p-4 mb-4 animate__animated animate__fadeIn">
                    <h2 class="text-center main-title mb-4 animate__animated animate__fadeInDown">Enviar Nueva Idea</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4 animate__animated animate__shakeX"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success mb-4 animate__animated animate__bounceIn">
                            <?php echo $success; ?>
                            <div class="mt-3">
                                <a href="ideas.php" class="btn btn-primary">Ver mis ideas</a>
                                <button type="button" class="btn btn-outline-primary ms-2" onclick="location.reload();">Enviar otra idea</button>
                            </div>
                        </div>
                        <!-- Script para activar confeti en caso de éxito -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                createConfetti();
                            });
                        </script>
                    <?php else: ?>
                        <form method="post" action="" id="idea-form" class="highlight-container">
                            <div class="mb-3 fade-in-up delay-1">
                                <label for="title" class="form-label">Título:</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                <div class="form-text">El título debe tener al menos 5 caracteres.</div>
                            </div>
                            
                            <div class="mb-3 fade-in-up delay-2">
                                <label for="category" class="form-label">Categoría:</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="" selected disabled>Seleccionar categoría</option>
                                    <option value="Procesos" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Procesos') ? 'selected' : ''; ?>>Procesos</option>
                                    <option value="Productos" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Productos') ? 'selected' : ''; ?>>Productos</option>
                                    <option value="Servicio al cliente" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Servicio al cliente') ? 'selected' : ''; ?>>Servicio al cliente</option>
                                    <option value="Tecnología" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Tecnología') ? 'selected' : ''; ?>>Tecnología</option>
                                    <option value="Ambiente laboral" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Ambiente laboral') ? 'selected' : ''; ?>>Ambiente laboral</option>
                                    <option value="Otro" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            
                            <div class="mb-4 fade-in-up delay-3">
                                <label for="description" class="form-label">Descripción:</label>
                                <textarea class="form-control" id="description" name="description" rows="6" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <div class="form-text">Describe tu idea con al menos 20 caracteres. Incluye detalles sobre el problema que resuelve y cómo implementarla.</div>
                            </div>
                            
                            <div class="d-flex justify-content-between fade-in-up delay-4">
                                <a href="ideas.php" class="btn btn-secondary">Volver a mis ideas</a>
                                <button type="submit" class="btn btn-primary pulse">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar Idea
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
                
                <!-- Tarjeta informativa con consejos para mejores ideas -->
                <div class="card shadow-lg mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="card-header bg-info text-white">
                        <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i> Consejos para mejores ideas</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item fade-in-left delay-1">
                                <i class="fas fa-check-circle text-success me-2"></i> 
                                <strong>Sé específico:</strong> Detalla claramente el problema que estás resolviendo y cómo funciona tu solución.
                            </li>
                            <li class="list-group-item fade-in-left delay-2">
                                <i class="fas fa-check-circle text-success me-2"></i> 
                                <strong>Considera el impacto:</strong> Explica los beneficios de tu idea para la empresa, clientes o empleados.
                            </li>
                            <li class="list-group-item fade-in-left delay-3">
                                <i class="fas fa-check-circle text-success me-2"></i> 
                                <strong>Piensa en la viabilidad:</strong> Las ideas más valoradas son aquellas que pueden implementarse con recursos razonables.
                            </li>
                            <li class="list-group-item fade-in-left delay-4">
                                <i class="fas fa-check-circle text-success me-2"></i> 
                                <strong>Innova:</strong> Las ideas originales que abordan problemas de manera novedosa tienen más posibilidades de destacar.
                            </li>
                        </ul>
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
    <script src="../assets/js/script.js"></script>
    
    <!-- Script de animaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efecto de ondas para botones
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (this.getAttribute('type') !== 'submit' || this.form.checkValidity()) {
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
        
        // Destacar campos de formulario al enfocarlos
        const formInputs = document.querySelectorAll('.form-control, .form-select');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.mb-3, .mb-4').classList.add('highlight-active');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.mb-3, .mb-4').classList.remove('highlight-active');
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
</body>
</html>