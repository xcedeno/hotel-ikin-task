
<?php
// ...existing code...
// cargar auth relativo al includes (estable cuando se incluye desde otras carpetas)
require_once __DIR__ . '/auth.php';

// Calcular rutas base relativas al DOCUMENT_ROOT (mismo método que en sidebar)
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$srcDir = str_replace('\\', '/', realpath(__DIR__ . '/../'));        // .../src
$ticketsDir = str_replace('\\', '/', realpath(__DIR__ . '/../../tickets')); // .../tickets (si existe)

$srcBase = '/' . trim(str_replace($docRoot, '', $srcDir), '/');
if ($srcBase === '/') $srcBase = '';
$ticketsBase = $ticketsDir ? '/' . trim(str_replace($docRoot, '', $ticketsDir), '/') : '/tickets';
if ($ticketsBase === '/') $ticketsBase = '';
// ...existing code...
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
    <!-- usar ruta absoluta calculada para el CSS -->
    <link href="<?= $srcBase ?>/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= $srcBase ?>/dashboard.php">
                <i class="bi bi-building"></i> Hotel Ikin Technology
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $srcBase ?>/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $srcBase ?>/tasks.php">Tareas</a>
                    </li>
                    
                    <?php if (hasPermission('jefe')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $srcBase ?>/users.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $srcBase ?>/reports.php">Reportes</a>
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
                            <li><a class="dropdown-item" href="<?= $srcBase ?>/profile.php">Mi Perfil</a></li>
                            <li><a class="dropdown-item" href="<?= $srcBase ?>/logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar será incluido aquí -->