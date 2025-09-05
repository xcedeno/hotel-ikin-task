<?php require_once 'auth.php'; ?>
<?php
// Define getRoleBadge if not already defined

if (!function_exists('getRoleBadge')) {
    function getRoleBadge($role) {
        switch ($role) {
            case 'jefe':
                return '<span class="badge bg-primary">Jefe</span>';
            case 'empleado':
                return '<span class="badge bg-success">Empleado</span>';
            default:
                return '<span class="badge bg-secondary">Invitado</span>';
        }
    }
}
?>
<!-- En includes/header.php, dentro del <head> -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Ikin - Sistema de Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-building"></i> Hotel Ikin Technology
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tasks.php">Tareas</a>
                    </li>
                    
                    <?php if (hasPermission('jefe')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reportes</a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="user-avatar">
                                <?= strtoupper(substr($_SESSION['full_name'], 0, 2)) ?>
                            </span>
                            <?= $_SESSION['full_name'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text"><?= getRoleBadge($_SESSION['user_role']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="profile.php">Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar será incluido aquí -->