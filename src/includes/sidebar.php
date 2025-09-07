<?php
// ...existing code...
// Calcular rutas base relativas al DOCUMENT_ROOT para crear enlaces absolutos dentro del sitio
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$srcDir = str_replace('\\', '/', realpath(__DIR__ . '/../'));        // .../src
$ticketsDir = str_replace('\\', '/', realpath(__DIR__ . '/../../tickets')); // .../tickets (si existe)

// base URLs (ej: "/src" o "/tickets")
$srcBase = '/' . trim(str_replace($docRoot, '', $srcDir), '/');
if ($srcBase === '/') $srcBase = ''; // evitar doble slash
$ticketsBase = $ticketsDir ? '/' . trim(str_replace($docRoot, '', $ticketsDir), '/') : '/tickets';
if ($ticketsBase === '/') $ticketsBase = '';

// ruta de request actual
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// helper para marcar activo — acepta varios patrones (prefijo o substring)
function isActive(...$patterns) {
    global $requestPath;
    foreach ($patterns as $p) {
        if ($p === '') continue;
        // comparar igualdad o que el request contenga el patrón
        if ($requestPath === $p || strpos($requestPath, $p) !== false) {
            return true;
        }
    }
    return false;
}
// ...existing code...
?>
<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center p-3">
            <div class="user-avatar mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.2rem;">
                <?= strtoupper(substr($_SESSION['full_name'], 0, 2)) ?>
            </div>
            <h6 class="text-white mb-0"><?= $_SESSION['full_name'] ?></h6>
            <small class="text-muted"><?= ucfirst($_SESSION['user_role']) ?></small>
        </div>
        
        <hr class="text-light">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/dashboard.php', $srcBase . '/index.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/tasks.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/tasks.php">
                    <i class="bi bi-list-task me-2"></i> Tareas
                </a>
            </li>
            
            <?php if (hasPermission('asistente')): ?>
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/create_task.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/create_task.php">
                    <i class="bi bi-plus-circle me-2"></i> Nueva Tarea
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('jefe')): ?>
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/users.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/users.php">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/reports.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/reports.php">
                    <i class="bi bi-bar-chart me-2"></i> Reportes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/settings.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/settings.php">
                    <i class="bi bi-gear me-2"></i> Configuración
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?= isActive($srcBase . '/profile.php') ? 'active' : '' ?>" href="<?= $srcBase ?>/profile.php">
                    <i class="bi bi-person me-2"></i> Mi Perfil
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= $srcBase ?>/logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
        <li class="nav-item">
            <a class="nav-link <?= isActive($ticketsBase, $ticketsBase . '/index.php') ? 'active' : '' ?>" href="<?= $ticketsBase ?>/">
                <i class="bi bi-ticket-detailed me-2"></i> Tickets Soporte
            </a>
        
        <div class="mt-4 p-3">
            <h6 class="text-white">Departamento de Tecnología</h6>
            <small class="text-muted">Hotel Ikin © 2024</small>
        </div>
    </div>
</nav>