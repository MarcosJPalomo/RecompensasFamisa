<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="py-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="assets/images/logofamisa.png" alt="Logo" class="logo">
                <h1 class="animate__animated animate__fadeInLeft">Programa de Recompensas</h1>
            </div>
            <nav>
                <ul class="nav header-nav">
                    <li class="nav-item"><a href="index.php" class="nav-link">Inicio</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a href="pages/ideas.php" class="nav-link">Ideas</a></li>
                        <li class="nav-item"><a href="pages/submit_idea.php" class="nav-link">Enviar Idea</a></li>
                        <li class="nav-item"><a href="pages/rewards.php" class="nav-link">Recompensas</a></li>
                        <?php if($_SESSION['role'] === 'revisor' || $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a href="pages/review.php" class="nav-link">Revisar Ideas</a></li>
                        <?php endif; ?>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a href="pages/approve.php" class="nav-link">Aprobar Puntos</a></li>
                        <?php endif; ?>
                        <?php if($_SESSION['role'] === 'premiador'): ?>
                            <li class="nav-item"><a href="pages/redemptions.php" class="nav-link">Canje de Recompensas</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a href="logout.php" class="nav-link">Cerrar Sesión (<?php echo $_SESSION['username']; ?>)</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="login.php" class="nav-link">Iniciar Sesión</a></li>
                        <li class="nav-item"><a href="register.php" class="nav-link">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">