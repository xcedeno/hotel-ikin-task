<?php
$currentPage = basename($_SERVER['PHP_SELF']);

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
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'tasks.php' ? 'active' : '' ?>" href="tasks.php">
                    <i class="bi bi-list-task me-2"></i> Tareas
                </a>
            </li>
            
            <?php if (hasPermission('asistente')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'create_task.php' ? 'active' : '' ?>" href="create_task.php">
                    <i class="bi bi-plus-circle me-2"></i> Nueva Tarea
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (hasPermission('jefe')): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'users.php' ? 'active' : '' ?>" href="users.php">
                    <i class="bi bi-people me-2"></i> Usuarios
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="bi bi-bar-chart me-2"></i> Reportes
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                    <i class="bi bi-gear me-2"></i> Configuración
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'profile.php' ? 'active' : '' ?>" href="profile.php">
                    <i class="bi bi-person me-2"></i> Mi Perfil
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
        
        <div class="mt-4 p-3">
            <h6 class="text-white">Departamento de Tecnología</h6>
            <small class="text-muted">Hotel Ikin © 2024</small>
        </div>
    </div>
</nav>