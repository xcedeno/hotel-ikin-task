<?php
function hasPermission($requiredRole) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roleHierarchy = [
        'auxiliar' => 1,
        'asistente' => 2,
        'analista' => 3,
        'jefe' => 4,
        'admin' => 5
    ];
    
    $userLevel = $roleHierarchy[$_SESSION['user_role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
    
    return $userLevel >= $requiredLevel;
}

function canEditTask($task, $currentUserId) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    // Admin y jefe pueden editar cualquier tarea
    if ($userRole === 'admin' || $userRole === 'jefe') {
        return true;
    }
    
    // Analistas pueden editar cualquier tarea
    if ($userRole === 'analista') {
        return true;
    }
    
    // Asistentes pueden editar solo las tareas que crearon
    if ($userRole === 'asistente' && isset($task['created_by']) && $task['created_by'] == $currentUserId) {
        return true;
    }
    
    // Auxiliares pueden editar solo las tareas asignadas a ellos
    if ($userRole === 'auxiliar' && isset($task['assigned_to']) && $task['assigned_to'] == $currentUserId) {
        return true;
    }
    
    return false;
}

function getRoleBadge($role) {
    $badges = [
        'auxiliar' => '<span class="badge bg-secondary">Auxiliar</span>',
        'asistente' => '<span class="badge bg-info">Asistente</span>',
        'analista' => '<span class="badge bg-primary">Analista</span>',
        'jefe' => '<span class="badge bg-warning">Jefe</span>',
        'admin' => '<span class="badge bg-danger">Admin</span>'
    ];
    
    return $badges[$role] ?? '<span class="badge bg-secondary">Desconocido</span>';
}

function getPriorityBadge($priority) {
    $badges = [
        'baja' => '<span class="badge bg-success">Baja</span>',
        'media' => '<span class="badge bg-info">Media</span>',
        'alta' => '<span class="badge bg-warning">Alta</span>',
        'urgente' => '<span class="badge bg-danger">Urgente</span>'
    ];
    
    return $badges[$priority] ?? '<span class="badge bg-secondary">Desconocida</span>';
}

function getStatusBadge($status) {
    $badges = [
        'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
        'en_progreso' => '<span class="badge bg-primary">En Progreso</span>',
        'completada' => '<span class="badge bg-success">Completada</span>',
        'cancelada' => '<span class="badge bg-danger">Cancelada</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">Desconocido</span>';
}


// Función para sanitizar datos de entrada
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Función para verificar si hay una sesión activa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Función para redireccionar si no está logueado
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>