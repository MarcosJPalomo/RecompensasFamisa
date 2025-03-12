<?php
session_start();
require_once '../config/database.php';
require_once '../functions/auth.php';

// Verificar que el usuario esté logueado y tenga el rol adecuado
checkLogin();
checkRole(['premiador', 'admin']);

// Procesar cambios de estado si se envía el formulario
$success_message = '';
$error_message = '';

// Buscar usuario si se proporciona
$search_username = isset($_GET['search_username']) ? trim($_GET['search_username']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $redemption_id = $_POST['redemption_id'];
    $new_status = $_POST['status'];
    
    // Actualizar estado
    $conn = conectarDB();
    $sql = "UPDATE redemptions SET status = ?, completed_at = NOW() WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $new_status, $redemption_id);
        
        if ($stmt->execute()) {
            // Actualización exitosa
            $success_message = "Estado del canje actualizado correctamente.";
            
            // Agregar a historial de puntos si es necesario
            if ($new_status == "completado") {
                // Obtener información del canje
                $query = "SELECT user_id, reward_id FROM redemptions WHERE id = ?";
                $stmt_info = $conn->prepare($query);
                $stmt_info->bind_param("i", $redemption_id);
                $stmt_info->execute();
                $result = $stmt_info->get_result();
                $redemption_info = $result->fetch_assoc();
                
                // Obtener puntos de la recompensa
                $query = "SELECT points_required FROM rewards WHERE id = ?";
                $stmt_points = $conn->prepare($query);
                $stmt_points->bind_param("i", $redemption_info['reward_id']);
                $stmt_points->execute();
                $result = $stmt_points->get_result();
                $reward_info = $result->fetch_assoc();
                
                // Actualizar historial de puntos
                $query = "INSERT INTO points_history (user_id, redemption_id, points, type, description) 
                          VALUES (?, ?, ?, 'spent', 'Canje de recompensa completado')";
                $stmt_history = $conn->prepare($query);
                $points = -$reward_info['points_required']; // Puntos negativos porque se gastan
                $stmt_history->bind_param("iii", $redemption_info['user_id'], $redemption_id, $points);
                $stmt_history->execute();
            }
        } else {
            $error_message = "Error al actualizar estado: " . $stmt->error;
        }
        
        $stmt->close();
    }
    $conn->close();
}

// Consultar canjes con filtro opcional por usuario
$conn = conectarDB();

if (!empty($search_username)) {
    $sql = "SELECT r.id, r.redeemed_at, r.status, 
                   u.username, u.full_name, 
                   rw.name as reward_name, rw.points_required 
            FROM redemptions r
            JOIN users u ON r.user_id = u.id
            JOIN rewards rw ON r.reward_id = rw.id
            WHERE u.username LIKE ? OR u.full_name LIKE ?
            ORDER BY r.redeemed_at DESC";
    
    $stmt = $conn->prepare($sql);
    $search_pattern = "%$search_username%";
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT r.id, r.redeemed_at, r.status, 
                   u.username, u.full_name, 
                   rw.name as reward_name, rw.points_required 
            FROM redemptions r
            JOIN users u ON r.user_id = u.id
            JOIN rewards rw ON r.reward_id = rw.id
            ORDER BY r.redeemed_at DESC";
    
    $result = $conn->query($sql);
}

$redemptions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $redemptions[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Canjes - Programa de Recompensas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
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
            <h2 class="text-center mb-4">Gestión de Canjes</h2>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success mb-4"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger mb-4"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Formulario de búsqueda con alineación precisa -->
            <div class="mb-4">
                <form method="get" action="">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="search_username" name="search_username" 
                            placeholder="Buscar por nombre o usuario" value="<?php echo htmlspecialchars($search_username); ?>">
                        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" style="height: 31px; width: 38px;">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($search_username)): ?>
                        <a href="redemptions.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="height: 31px; width: 38px;">
                            <i class="fas fa-times"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="form-text">Ingrese el nombre o usuario para filtrar los resultados</div>
                </form>
            </div>
            
            <?php if (empty($redemptions)): ?>
                <div class="alert alert-info">
                    <?php if (!empty($search_username)): ?>
                        <p class="mb-0">No se encontraron canjes para el usuario "<?php echo htmlspecialchars($search_username); ?>".</p>
                    <?php else: ?>
                        <p class="mb-0">No hay canjes registrados en el sistema.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Resumen de resultados de búsqueda si es relevante -->
                <?php if (!empty($search_username)): ?>
                    <div class="alert alert-info mb-4">
                        Mostrando <?php echo count($redemptions); ?> canjes para la búsqueda: "<?php echo htmlspecialchars($search_username); ?>"
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Recompensa</th>
                                <th>Puntos</th>
                                <th>Fecha Solicitud</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($redemptions as $redemption): ?>
                                <tr>
                                    <td><?php echo $redemption['id']; ?></td>
                                    <td><?php echo htmlspecialchars($redemption['full_name']); ?> <br><small class="text-muted">(<?php echo htmlspecialchars($redemption['username']); ?>)</small></td>
                                    <td><?php echo htmlspecialchars($redemption['reward_name']); ?></td>
                                    <td><?php echo $redemption['points_required']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($redemption['redeemed_at'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($redemption['status'] == 'pendiente') ? 'bg-warning' : 'bg-success'; ?>">
                                            <?php echo ucfirst($redemption['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="redemption_id" value="<?php echo $redemption['id']; ?>">
                                            <?php if ($redemption['status'] == 'pendiente'): ?>
                                                <input type="hidden" name="status" value="completado">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('¿Estás seguro de que quieres aprobar este canje?');">
                                                    <i class="fas fa-check me-1"></i> Aprobar
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="pendiente">
                                                <button type="submit" name="update_status" class="btn btn-sm btn-secondary" 
                                                        onclick="return confirm('¿Estás seguro de que quieres revertir este canje?');">
                                                    <i class="fas fa-undo me-1"></i> Revertir
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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