<?php
function getAllRewards() {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE available = true ORDER BY points_required ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rewards = [];
    while ($row = $result->fetch_assoc()) {
        $rewards[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $rewards;
}

function getRewardById($reward_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE id = ?");
    $stmt->bind_param("i", $reward_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reward = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    return $reward;
}

function createReward($name, $description, $points_required, $image_path = null) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("INSERT INTO rewards (name, description, points_required, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $description, $points_required, $image_path);
    $success = $stmt->execute();
    
    $response = [
        'success' => $success,
        'message' => $success ? 'Recompensa creada exitosamente' : 'Error al crear la recompensa'
    ];
    
    $stmt->close();
    $conn->close();
    return $response;
}

function redeemReward($user_id, $reward_id) {
    $conn = conectarDB();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Obtener la recompensa
        $stmt = $conn->prepare("SELECT * FROM rewards WHERE id = ? AND available = true");
        $stmt->bind_param("i", $reward_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reward = $result->fetch_assoc();
        $stmt->close();
        
        if (!$reward) {
            throw new Exception('Recompensa no disponible');
        }
        
        // Obtener puntos del usuario
        $stmt = $conn->prepare("SELECT total_points FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user['total_points'] < $reward['points_required']) {
            throw new Exception('Puntos insuficientes');
        }
        
        // Registrar el canje
        $stmt = $conn->prepare("INSERT INTO redemptions (user_id, reward_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $reward_id);
        $stmt->execute();
        $redemption_id = $conn->insert_id;
        $stmt->close();
        
        // Actualizar puntos del usuario
        $stmt = $conn->prepare("UPDATE users SET total_points = total_points - ? WHERE id = ?");
        $stmt->bind_param("ii", $reward['points_required'], $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Registrar historia de puntos
        $description = "Puntos canjeados por: " . $reward['name'];
        $type = 'spent';
        $points = -$reward['points_required']; // Negativo porque se gastan
        $stmt = $conn->prepare("INSERT INTO points_history 
                                (user_id, redemption_id, points, type, description) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $user_id, $redemption_id, $points, $type, $description);
        $stmt->execute();
        $stmt->close();
        
        // Actualizar sesión
        $_SESSION['total_points'] -= $reward['points_required'];
        
        // Confirmar transacción
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Recompensa canjeada exitosamente'
        ];
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        $response = [
            'success' => false,
            'message' => 'Error al canjear la recompensa: ' . $e->getMessage()
        ];
    }
    
    $conn->close();
    return $response;
}

function getUserRedemptions($user_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT r.*, rd.name as reward_name, rd.description as reward_description, rd.points_required 
                           FROM redemptions r
                           JOIN rewards rd ON r.reward_id = rd.id
                           WHERE r.user_id = ?
                           ORDER BY r.redeemed_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $redemptions = [];
    while ($row = $result->fetch_assoc()) {
        $redemptions[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $redemptions;
}

function getUserPointsHistory($user_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT * FROM points_history 
                           WHERE user_id = ?
                           ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $history;
}
?>