<?php
// functions/auth.php
// Función para actualizar datos de sesión (añadir a functions/auth.php)

/**
 * Actualiza los datos de sesión del usuario desde la base de datos
 * 
 * @param int $user_id ID del usuario cuya sesión se quiere actualizar
 * @return bool True si la actualización fue exitosa, False en caso contrario
 */
function updateSessionData($user_id) {
    // Verificar que haya una sesión iniciada
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Conectar a la base de datos
    $conn = conectarDB();
    
    // Obtener datos actualizados del usuario
    $stmt = $conn->prepare("SELECT id, username, full_name, email, role, total_points FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Actualizar todos los datos de sesión importantes
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['total_points'] = $user['total_points'];
        
        $conn->close();
        return true;
    }
    
    $conn->close();
    return false;
}
/**
 * Registra un nuevo usuario en el sistema
 * 
 * @param string $username Nombre de usuario
 * @param string $password Contraseña
 * @param string $full_name Nombre completo
 * @param string $email Correo electrónico
 * @param string $employee_id Número de ficha de empleado
 * @return array Resultado de la operación
 */
function registerUser($username, $password, $full_name, $email, $employee_id = null) {
    $conn = conectarDB();
    
    // Verificar usuario existente
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'El nombre de usuario o email ya existe'];
    }
    
    // Verificar si el número de ficha ya está registrado
    if ($employee_id) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Este número de ficha ya está registrado'];
        }
    }
    
    // Hash de contraseña con password_hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar el rol del empleado desde la tabla de empleados
    $role = 'empleado'; // Por defecto
    
    if ($employee_id) {
        $stmt = $conn->prepare("SELECT position FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $employee = $result->fetch_assoc();
            
            // Aquí puedes mapear posiciones a roles según tus necesidades
            // Por ejemplo, si una posición "Gerente" debería tener el rol "admin"
            $position = strtolower($employee['position'] ?? '');
            
            if (strpos($position, 'gerente') !== false || strpos($position, 'director') !== false) {
                $role = 'admin';
            } else if (strpos($position, 'supervisor') !== false || strpos($position, 'coordinador') !== false) {
                $role = 'revisor';
            }
            
            // Si tienes posiciones específicas para el rol "premiador", añade aquí la lógica
        }
    }
    
    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, employee_id, role, total_points) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssss", $username, $hashed_password, $full_name, $email, $employee_id, $role);
    $success = $stmt->execute();
    
    $response = [
        'success' => $success,
        'message' => $success ? 'Usuario registrado exitosamente' : 'Error al registrar usuario'
    ];
    
    $stmt->close();
    $conn->close();
    return $response;
}

/**
 * Inicia sesión de un usuario
 * 
 * @param string $username Nombre de usuario
 * @param string $password Contraseña
 * @return array Resultado de la operación
 */
function loginUser($username, $password) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT id, username, password, full_name, email, role, total_points, employee_id FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar contraseña con password_verify
        if (password_verify($password, $user['password'])) {
            // Crear sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['total_points'] = $user['total_points'];
            $_SESSION['employee_id'] = $user['employee_id'];
            
            $response = [
                'success' => true,
                'message' => 'Inicio de sesión exitoso'
            ];
        } else {
            // Contraseña incorrecta
            $response = [
                'success' => false,
                'message' => 'Contraseña incorrecta'
            ];
        }
    } else {
        // Usuario no encontrado
        $response = [
            'success' => false,
            'message' => 'Usuario no encontrado'
        ];
    }
    
    $stmt->close();
    $conn->close();
    return $response;
}

/**
 * Verifica si un usuario está logueado, redirige si no lo está
 */
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

/**
 * Verifica si un usuario tiene los roles permitidos
 * 
 * @param array $allowed_roles Roles permitidos
 */
function checkRole($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ../index.php');
        exit;
    }
}
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>