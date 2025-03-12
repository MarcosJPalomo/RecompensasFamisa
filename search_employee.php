<?php
// search_employee.php
// Este archivo busca un empleado por su número de ficha en la base de datos

header('Content-Type: application/json');
require_once 'config/database.php';

// Verificar si se proporcionó un ID de empleado
if (!isset($_GET['employee_id']) || empty($_GET['employee_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No se proporcionó un número de ficha'
    ]);
    exit;
}

$employee_id = trim($_GET['employee_id']);

// Conectar a la base de datos
$conn = conectarDB();

// Consultar la base de datos para verificar si el empleado existe
// Asumimos que hay una tabla 'employees' con información de los empleados
$stmt = $conn->prepare("SELECT id, full_name, email, position FROM employees WHERE employee_id = ?");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // Empleado encontrado
    $employee = $result->fetch_assoc();
    
    // Verificar si el empleado ya tiene una cuenta en la tabla users
    $stmt = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $userResult = $stmt->get_result();
    
    if ($userResult->num_rows > 0) {
        // El empleado ya tiene una cuenta
        echo json_encode([
            'success' => false,
            'message' => 'Este empleado ya tiene una cuenta registrada'
        ]);
    } else {
        // El empleado existe y no tiene cuenta
        echo json_encode([
            'success' => true,
            'name' => $employee['full_name'],
            'email' => $employee['email'] ?? '',
            'position' => $employee['position'] ?? ''
        ]);
    }
} else {
    // Empleado no encontrado
    echo json_encode([
        'success' => false,
        'message' => 'No se encontró ningún empleado con esta ficha'
    ]);
}

$stmt->close();
$conn->close();
?>