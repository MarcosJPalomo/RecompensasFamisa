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
    <style>
        /* Estilos adicionales para mejorar el formulario */
        .idea-form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .idea-form-header {
            background: linear-gradient(135deg, var(--primary-red), #BB595F);
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .idea-form-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
            transition: transform 0.5s;
        }
        
        .idea-form-container:hover .idea-form-header::after {
            transform: rotate(30deg) translate(-10%, -10%);
        }
        
        .idea-form-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .idea-form-body {
            padding: 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating label {
            color: #6c757d;
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            opacity: 0.7;
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }
        
        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.25rem rgba(144, 21, 28, 0.25);
        }
        
        .category-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .category-option {
            flex: 1;
            min-width: calc(33% - 10px);
            position: relative;
        }
        
        .category-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .category-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 15px 10px;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-option i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .category-option input[type="radio"]:checked + label {
            border-color: var(--primary-red);
            background-color: rgba(144, 21, 28, 0.05);
            box-shadow: 0 0 0 2px var(--primary-red);
        }
        
        .category-option input[type="radio"]:checked + label i {
            color: var(--primary-red);
        }
        
        .category-option:hover label {
            border-color: #adb5bd;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-section-title {
            margin-bottom: 15px;
            font-weight: 600;
            color: var(--primary-red);
            display: flex;
            align-items: center;
        }
        
        .form-section-title i {
            margin-right: 8px;
        }
        
        .character-counter {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        .btn-submit-idea {
            padding: 12px 30px;
            font-weight: 500;
            border-radius: 50px;
            background: linear-gradient(135deg, var(--primary-red), #BB595F);
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .btn-submit-idea::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #7a1017, var(--primary-red));
            opacity: 0;
            z-index: -1;
            transition: opacity 0.3s ease;
        }
        
        .btn-submit-idea:hover::before {
            opacity: 1;
        }
        
        .btn-submit-idea:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(144, 21, 28, 0.3);
        }
        
        .tips-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .tips-card-header {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
            padding: 15px 20px;
            position: relative;
        }
        
        .tips-card-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .tips-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .tips-list li {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: flex-start;
            transition: all 0.3s ease;
        }
        
        .tips-list li:last-child {
            border-bottom: none;
        }
        
        .tips-list li:hover {
            background-color: #f8f9fa;
            padding-left: 20px;
        }
        
        .tips-list li i {
            color: #28a745;
            margin-right: 10px;
            margin-top: 3px;
            font-size: 0.9rem;
        }
        
        /* Animación para input focus */
        @keyframes focusPulse {
            0% { box-shadow: 0 0 0 0 rgba(144, 21, 28, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(144, 21, 28, 0); }
            100% { box-shadow: 0 0 0 0 rgba(144, 21, 28, 0); }
        }
        
        .form-control:focus, .form-select:focus {
            animation: focusPulse 1.5s;
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
        <h2 class="text-center main-title animate__animated animate__fadeInDown mb-4">
            <i class="fas fa-lightbulb text-warning me-2"></i> Enviar Nueva Idea
        </h2>

        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__bounceIn mb-4">
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
            <div class="row">
                <!-- Formulario de envío de idea mejorado -->
                <div class="col-lg-8 mb-4">
                    <div class="idea-form-container animate__animated animate__fadeIn">
                        <div class="idea-form-header">
                            <h3><i class="fas fa-plus-circle me-2"></i> Completa el formulario</h3>
                        </div>
                        <div class="idea-form-body">
                            <form method="post" action="" id="idea-form">
                                <!-- Título de la idea -->
                                <div class="form-section-title">
                                    <i class="fas fa-heading"></i> Título de tu idea
                                </div>
                                <div class="form-floating mb-4">
                                    <input type="text" class="form-control" id="title" name="title" placeholder="Título de la idea" required
                                        value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                    <label for="title">Escribe un título claro y conciso</label>
                                    <div class="character-counter">
                                        <span id="title-counter">0</span>/100 caracteres
                                    </div>
                                </div>
                                
                                <!-- Categoría con opciones visuales -->
                                <div class="form-section-title">
                                    <i class="fas fa-tag"></i> Categoría
                                </div>
                                <div class="category-selector">
                                    <div class="category-option">
                                        <input type="radio" id="cat-procesos" name="category" value="Procesos" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Procesos') ? 'checked' : ''; ?>>
                                        <label for="cat-procesos">
                                            <i class="fas fa-cogs"></i>
                                            <span>Procesos</span>
                                        </label>
                                    </div>
                                    
                                    <div class="category-option">
                                        <input type="radio" id="cat-productos" name="category" value="Productos" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Productos') ? 'checked' : ''; ?>>
                                        <label for="cat-productos">
                                            <i class="fas fa-box"></i>
                                            <span>Productos</span>
                                        </label>
                                    </div>
                                    
                                    <div class="category-option">
                                        <input type="radio" id="cat-servicio" name="category" value="Servicio al cliente" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Servicio al cliente') ? 'checked' : ''; ?>>
                                        <label for="cat-servicio">
                                            <i class="fas fa-headset"></i>
                                            <span>Servicio al cliente</span>
                                        </label>
                                    </div>
                                    
                                    <div class="category-option">
                                        <input type="radio" id="cat-tecnologia" name="category" value="Tecnología" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Tecnología') ? 'checked' : ''; ?>>
                                        <label for="cat-tecnologia">
                                            <i class="fas fa-laptop-code"></i>
                                            <span>Tecnología</span>
                                        </label>
                                    </div>
                                    
                                    <div class="category-option">
                                        <input type="radio" id="cat-ambiente" name="category" value="Ambiente laboral" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Ambiente laboral') ? 'checked' : ''; ?>>
                                        <label for="cat-ambiente">
                                            <i class="fas fa-users"></i>
                                            <span>Ambiente laboral</span>
                                        </label>
                                    </div>
                                    
                                    <div class="category-option">
                                        <input type="radio" id="cat-otro" name="category" value="Otro" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Otro') ? 'checked' : ''; ?>>
                                        <label for="cat-otro">
                                            <i class="fas fa-star"></i>
                                            <span>Otro</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Descripción detallada -->
                                <div class="form-section-title">
                                    <i class="fas fa-align-left"></i> Descripción detallada
                                </div>
                                <div class="form-floating mb-4">
                                    <textarea class="form-control" id="description" name="description" 
                                              placeholder="Describe tu idea" style="height: 200px" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    <label for="description">Explica tu idea con detalle, beneficios e implementación</label>
                                    <div class="character-counter">
                                        <span id="description-counter">0</span> caracteres (mínimo 20)
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="ideas.php" class="btn btn-outline-secondary rounded-pill">
                                        <i class="fas fa-arrow-left me-1"></i> Volver
                                    </a>
                                    <button type="submit" class="btn btn-submit-idea">
                                        <i class="fas fa-paper-plane me-1"></i> Enviar Idea
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Consejos para mejores ideas -->
                <div class="col-lg-4 mb-4">
                    <div class="tips-card animate__animated animate__fadeInRight">
                        <div class="tips-card-header">
                            <h4><i class="fas fa-lightbulb me-2"></i> Consejos para mejores ideas</h4>
                        </div>
                        <div class="card-body p-0">
                            <ul class="tips-list">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div><strong>Sé específico:</strong> Detalla claramente el problema que estás resolviendo y cómo funciona tu solución.</div>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div><strong>Considera el impacto:</strong> Explica los beneficios de tu idea para la empresa, clientes o empleados.</div>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div><strong>Piensa en la viabilidad:</strong> Las ideas más valoradas son aquellas que pueden implementarse con recursos razonables.</div>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div><strong>Innova:</strong> Las ideas originales que abordan problemas de manera novedosa tienen más posibilidades de destacar.</div>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div><strong>Estructura tu idea:</strong> Organiza tu propuesta en problema, solución, beneficios e implementación.</div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p class="animate__animated animate__fadeIn">&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <!-- Script mejorado para el formulario -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Contadores de caracteres
        const titleInput = document.getElementById('title');
        const descriptionInput = document.getElementById('description');
        const titleCounter = document.getElementById('title-counter');
        const descriptionCounter = document.getElementById('description-counter');
        
        if (titleInput && titleCounter) {
            titleCounter.textContent = titleInput.value.length;
            
            titleInput.addEventListener('input', function() {
                titleCounter.textContent = this.value.length;
                
                if (this.value.length > 100) {
                    titleCounter.style.color = '#dc3545';
                } else {
                    titleCounter.style.color = '';
                }
            });
        }
        
        if (descriptionInput && descriptionCounter) {
            descriptionCounter.textContent = descriptionInput.value.length;
            
            descriptionInput.addEventListener('input', function() {
                descriptionCounter.textContent = this.value.length;
                
                if (this.value.length < 20) {
                    descriptionCounter.style.color = '#dc3545';
                } else {
                    descriptionCounter.style.color = '';
                }
            });
        }
        
        // Validación del formulario
        const ideaForm = document.getElementById('idea-form');
        
        if (ideaForm) {
            ideaForm.addEventListener('submit', function(e) {
                let isValid = true;
                let errorMessage = '';
                
                // Validar título
                if (titleInput.value.trim().length < 5) {
                    isValid = false;
                    errorMessage = 'El título debe tener al menos 5 caracteres';
                    titleInput.classList.add('is-invalid');
                } else if (titleInput.value.trim().length > 100) {
                    isValid = false;
                    errorMessage = 'El título no debe exceder los 100 caracteres';
                    titleInput.classList.add('is-invalid');
                } else {
                    titleInput.classList.remove('is-invalid');
                }
                
                // Validar descripción
                if (descriptionInput.value.trim().length < 20) {
                    isValid = false;
                    errorMessage = 'La descripción debe tener al menos 20 caracteres';
                    descriptionInput.classList.add('is-invalid');
                } else {
                    descriptionInput.classList.remove('is-invalid');
                }
                
                // Validar categoría
                const categorySelected = document.querySelector('input[name="category"]:checked');
                if (!categorySelected) {
                    isValid = false;
                    errorMessage = 'Por favor, selecciona una categoría';
                }
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Mostrar mensaje de error
                    if (!document.querySelector('.validation-alert')) {
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger validation-alert animate__animated animate__shakeX';
                        alertDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + errorMessage;
                        
                        ideaForm.insertBefore(alertDiv, ideaForm.firstChild);
                        
                        // Eliminar la alerta después de 5 segundos
                        setTimeout(() => {
                            alertDiv.classList.add('animate__fadeOut');
                            setTimeout(() => {
                                alertDiv.remove();
                            }, 500);
                        }, 5000);
                    }
                }
            });
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
        
        // Destacar campos de formulario al enfocarlos
        const formInputs = document.querySelectorAll('.form-control, .form-select');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.form-floating')?.classList.add('highlight-active');
            });
            
            input.addEventListener('blur', function() {
                this.closest('.form-floating')?.classList.remove('highlight-active');
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