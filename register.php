<?php
session_start();
require_once 'config/database.php';
require_once 'functions/auth.php';

// Verificar si el usuario ya está logueado
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $employee_id = $_POST['employee_id'] ?? '';
    
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name) || empty($email) || empty($employee_id)) {
        $error = 'Por favor, completa todos los campos';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        $result = registerUser($username, $password, $full_name, $email, $employee_id);
        
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
    <title>Registro - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .bg-custom-red {
        background-color: #90151C !important;
    }
    
    /* Estilo para cuando la búsqueda está en progreso */
    .searching {
        position: relative;
    }
    
    .searching::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    /* Estilos para el formulario */
    .input-group-append .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    /* Efecto de carga para el botón de búsqueda */
    .btn-searching {
        position: relative;
        color: transparent !important;
        pointer-events: none;
    }
    
    .btn-searching::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: calc(50% - 8px);
        left: calc(50% - 8px);
        border: 2px solid rgba(255, 255, 255, 0.5);
        border-radius: 50%;
        border-top-color: white;
        animation: spinner .6s linear infinite;
    }
    
    @keyframes spinner {
        to { transform: rotate(360deg); }
    }
    
    /* Campo encontrado */
    .field-success {
        border-color: #28a745 !important;
        background-color: rgba(40, 167, 69, 0.05) !important;
    }
    
    /* Mensaje de error para el campo de ficha */
    #employee-feedback {
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
    
    /* Mensaje que aparece cuando el usuario no existe */
    .employee-not-found {
        color: #dc3545;
    }
    </style>
</head>
<body>
    <header class="py-2 shadow-sm">
        <div class="container">
            <div class="d-flex flex-wrap align-items-center">
                <!-- Logo y título (más pequeño) -->
                <div class="d-flex align-items-center me-auto" style="max-width: 25%;">
                    <img src="assets/images/logofamisa.png" alt="Logo" class="logo" style="max-height: 35px;">
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
                            <li class="nav-item"><a href="index.php" class="nav-link px-2"><i class="fas fa-home"></i><span class="d-none d-md-inline ms-1">Inicio</span></a></li>
                            <li class="nav-item"><a href="login.php" class="nav-link px-2"><i class="fas fa-sign-in-alt"></i><span class="d-none d-md-inline ms-1">Iniciar</span></a></li>
                            <li class="nav-item"><a href="register.php" class="nav-link px-2"><i class="fas fa-user-plus"></i><span class="d-none d-md-inline ms-1">Registrarse</span></a></li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg animate__animated animate__fadeIn">
                        <div class="card-header bg-custom-red text-white text-center py-3">
                            <h2 class="mb-0">Registro de Usuario</h2>
                        </div>
                        <div class="card-body p-4">
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center mb-3"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success text-center mb-3">
                                    <?php echo $success; ?>
                                    <p class="mt-2 mb-0"><a href="login.php" class="alert-link">Iniciar sesión ahora</a></p>
                                </div>
                            <?php else: ?>
                                <form method="post" action="" id="registerForm">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Número de Ficha:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="employee_id" name="employee_id" required value="<?php echo isset($_POST['employee_id']) ? htmlspecialchars($_POST['employee_id']) : ''; ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn bg-custom-red text-white" id="searchEmployee">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div id="employee-feedback"></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Nombre Completo:</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required readonly value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Nombre de Usuario:</label>
                                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                        <div class="form-text">Este será tu nombre de usuario para iniciar sesión.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico:</label>
                                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Contraseña:</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Confirmar Contraseña:</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn bg-custom-red text-white btn-lg">Registrarse</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <div class="text-center mt-3">
                                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="text-white text-center py-3">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Programa de Recompensas</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchButton = document.getElementById('searchEmployee');
        const employeeIdInput = document.getElementById('employee_id');
        const fullNameInput = document.getElementById('full_name');
        const feedbackDiv = document.getElementById('employee-feedback');
        
        // Agregar evento de búsqueda por botón
        searchButton.addEventListener('click', searchEmployeeById);
        
        // También buscar cuando se presione Enter en el campo de ficha
        employeeIdInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evitar envío del formulario
                searchEmployeeById();
            }
        });
        
        function searchEmployeeById() {
            const employeeId = employeeIdInput.value.trim();
            
            if (!employeeId) {
                showFeedback('Por favor, ingresa un número de ficha', true);
                return;
            }
            
            // Mostrar estado de carga
            searchButton.classList.add('btn-searching');
            fullNameInput.value = '';
            fullNameInput.classList.remove('field-success');
            
            // Realizar la búsqueda mediante AJAX
            fetch('search_employee.php?employee_id=' + encodeURIComponent(employeeId))
                .then(response => response.json())
                .then(data => {
                    searchButton.classList.remove('btn-searching');
                    
                    if (data.success) {
                        // Empleado encontrado
                        fullNameInput.value = data.name;
                        fullNameInput.classList.add('field-success');
                        showFeedback('Empleado encontrado', false);
                        
                        // Si hay correo electrónico, también lo llenamos
                        if (data.email && document.getElementById('email')) {
                            document.getElementById('email').value = data.email;
                        }
                        
                        // Sugerimos un nombre de usuario basado en la ficha
                        if (document.getElementById('username') && !document.getElementById('username').value) {
                            document.getElementById('username').value = 'user' + employeeId;
                        }
                    } else {
                        // Empleado no encontrado
                        showFeedback('No se encontró ningún empleado con esta ficha', true);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchButton.classList.remove('btn-searching');
                    showFeedback('Error al buscar el empleado. Intenta nuevamente.', true);
                });
        }
        
        function showFeedback(message, isError) {
            feedbackDiv.textContent = message;
            feedbackDiv.className = isError ? 'employee-not-found' : 'text-success';
        }
    });
    </script>
</body>
</html>