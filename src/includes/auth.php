<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
// Verificar si el usuario está autenticado
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Verificar permisos específicos
function requireRole($requiredRole) {
    requireAuth();
    
    if (!isset($_SESSION['user_role']) || !hasPermission($requiredRole)) {
        header('Location: dashboard.php?error=no_permission');
        exit;
    }
}

// Verificar permisos para acciones específicas
function checkPermission($requiredRole) {
    if (!isset($_SESSION['user_role']) || !hasPermission($requiredRole)) {
        return false;
    }
    return true;
}
?>