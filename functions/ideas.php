<?php
function submitIdea($title, $description, $category, $user_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("INSERT INTO ideas (title, description, category, user_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $description, $category, $user_id);
    $success = $stmt->execute();
    
    $response = [
        'success' => $success,
        'message' => $success ? 'Idea enviada exitosamente' : 'Error al enviar la idea'
    ];
    
    $stmt->close();
    $conn->close();
    return $response;
}

function getUserIdeas($user_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT * FROM ideas WHERE user_id = ? ORDER BY submitted_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ideas = [];
    while ($row = $result->fetch_assoc()) {
        $ideas[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $ideas;
}

function getPendingIdeas() {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT i.*, u.full_name FROM ideas i 
                           JOIN users u ON i.user_id = u.id 
                           WHERE i.status = 'pendiente' 
                           ORDER BY i.submitted_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ideas = [];
    while ($row = $result->fetch_assoc()) {
        $ideas[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $ideas;
}

function getIdeasForApproval() {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT i.*, u.full_name, r.full_name as reviewer_name 
                           FROM ideas i 
                           JOIN users u ON i.user_id = u.id 
                           JOIN users r ON i.reviewer_id = r.id 
                           WHERE i.status = 'revisada' 
                           AND i.approved_by_admin = false 
                           ORDER BY i.reviewed_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ideas = [];
    while ($row = $result->fetch_assoc()) {
        $ideas[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $ideas;
}

function getIdeaById($idea_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("SELECT i.*, u.full_name FROM ideas i 
                           JOIN users u ON i.user_id = u.id 
                           WHERE i.id = ?");
    $stmt->bind_param("i", $idea_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $idea = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    return $idea;
}

function reviewIdea($idea_id, $points, $comments, $reviewer_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("UPDATE ideas SET 
                            status = 'revisada', 
                            points_assigned = ?, 
                            reviewer_comments = ?,
                            reviewer_id = ?,
                            reviewed_at = CURRENT_TIMESTAMP
                            WHERE id = ?");
    $stmt->bind_param("isii", $points, $comments, $reviewer_id, $idea_id);
    $success = $stmt->execute();
    
    $response = [
        'success' => $success,
        'message' => $success ? 'Idea revisada exitosamente' : 'Error al revisar la idea'
    ];
    
    $stmt->close();
    $conn->close();
    return $response;
}

function approveIdea($idea_id, $admin_id) {
    $conn = conectarDB();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Obtener la idea
        $stmt = $conn->prepare("SELECT * FROM ideas WHERE id = ?");
        $stmt->bind_param("i", $idea_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $idea = $result->fetch_assoc();
        $stmt->close();
        
        if (!$idea) {
            throw new Exception('Idea no encontrada');
        }
        
        // Actualizar la idea
        $stmt = $conn->prepare("UPDATE ideas SET 
                                status = 'aprobada', 
                                approved_by_admin = true,
                                approved_at = CURRENT_TIMESTAMP
                                WHERE id = ?");
        $stmt->bind_param("i", $idea_id);
        $stmt->execute();
        $stmt->close();
        
        // Actualizar puntos del usuario
        $stmt = $conn->prepare("UPDATE users SET 
                                total_points = total_points + ? 
                                WHERE id = ?");
        $stmt->bind_param("ii", $idea['points_assigned'], $idea['user_id']);
        $stmt->execute();
        $stmt->close();
        
        // Registrar historia de puntos
        $description = "Puntos ganados por idea: " . $idea['title'];
        $type = 'earned';
        $stmt = $conn->prepare("INSERT INTO points_history 
                                (user_id, idea_id, points, type, description) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $idea['user_id'], $idea_id, $idea['points_assigned'], $type, $description);
        $stmt->execute();
        $stmt->close();
        
        // Confirmar transacción
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Idea aprobada y puntos asignados exitosamente'
        ];
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        $response = [
            'success' => false,
            'message' => 'Error al aprobar la idea: ' . $e->getMessage()
        ];
    }
    
    $conn->close();
    return $response;
}

function rejectIdea($idea_id) {
    $conn = conectarDB();
    
    $stmt = $conn->prepare("UPDATE ideas SET 
                            status = 'rechazada' 
                            WHERE id = ?");
    $stmt->bind_param("i", $idea_id);
    $success = $stmt->execute();
    
    $response = [
        'success' => $success,
        'message' => $success ? 'Idea rechazada' : 'Error al rechazar la idea'
    ];
    
    $stmt->close();
    $conn->close();
    return $response;
}
?>