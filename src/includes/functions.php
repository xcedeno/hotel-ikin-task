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
function canDeleteTask($task, $currentUserId) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    // Solo jefes y admin pueden eliminar cualquier tarea
    if ($userRole === 'admin' || $userRole === 'jefe') {
        return true;
    }
    
    // Asistentes pueden eliminar solo las tareas que crearon
    if ($userRole === 'asistente' && isset($task['created_by']) && $task['created_by'] == $currentUserId) {
        return true;
    }
    
    return false;
}
/**
 * Sanitiza output asegurando UTF-8 y previniendo XSS
 */
function safeOutput($text) {
    if (empty($text)) {
        return $text;
    }
    
    // Corregir encoding mal interpretado
    $fixedText = $text;
    
    // Corregir caracteres con problemas de encoding
    if (preg_match('/Ã¡|Ã©|Ã­|Ã³|Ãº|Ã±|Ã|Ã|Ã|Ã|Ã|Ã/', $fixedText)) {
        $fixedText = utf8_encode($fixedText);
    }
    
    // Aplicar htmlspecialchars para prevenir XSS
    return htmlspecialchars($fixedText, ENT_QUOTES, 'UTF-8');
}

/**
 * Función alias más corta para uso en templates
 */
function so($text) {
    return safeOutput($text);
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

/**
 * Generar número de ticket automático
 */
function generateTicketNumber($pdo) {
    $year = date('Y');
    
    // Obtener el último ticket del año actual
    $stmt = $pdo->prepare("SELECT ticket_number FROM support_tickets WHERE ticket_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(["TKT-$year-%"]);
    $lastTicket = $stmt->fetch();
    
    if ($lastTicket) {
        // Extraer el número secuencial
        $parts = explode('-', $lastTicket['ticket_number']);
        $lastNumber = (int)end($parts);
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '0001';
    }
    
    return "TKT-$year-$newNumber";
}

/**
 * Obtener badge para estado de ticket
 */
function getTicketStatusBadge($status) {
    $badges = [
        'abierto' => '<span class="badge bg-primary">Abierto</span>',
        'en_proceso' => '<span class="badge bg-warning">En Proceso</span>',
        'esperando_cliente' => '<span class="badge bg-info">Esperando Cliente</span>',
        'resuelto' => '<span class="badge bg-success">Resuelto</span>',
        'cerrado' => '<span class="badge bg-secondary">Cerrado</span>',
        'cancelado' => '<span class="badge bg-danger">Cancelado</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">Desconocido</span>';
}

/**
 * Calcular tiempo transcurrido desde creación del ticket
 */
function getTicketAge($createdAt) {
    $now = new DateTime();
    $created = new DateTime($createdAt);
    $interval = $now->diff($created);
    
    if ($interval->d > 0) {
        return $interval->d . ' día' . ($interval->d > 1 ? 's' : '');
    } elseif ($interval->h > 0) {
        return $interval->h . ' hora' . ($interval->h > 1 ? 's' : '');
    } else {
        return $interval->i . ' minuto' . ($interval->i > 1 ? 's' : '');
    }
}

/**
 * Verificar si el ticket está vencido
 */
function isTicketOverdue($dueDate, $status) {
    if (empty($dueDate) || in_array($status, ['resuelto', 'cerrado', 'cancelado'])) {
        return false;
    }
    
    return strtotime($dueDate) < time();
}
?>