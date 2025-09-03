<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo jefe y admin pueden ver reportes
requireRole('jefe');

// Fechas para filtros
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Obtener estadísticas
try {
    // Tareas por estado
    $statusStats = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM tasks 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Tareas por prioridad
    $priorityStats = $pdo->query("
        SELECT priority, COUNT(*) as count 
        FROM tasks 
        GROUP BY priority
    ")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Tareas por usuario
    $userStats = $pdo->query("
        SELECT u.full_name, COUNT(t.id) as task_count,
            SUM(CASE WHEN t.status = 'completada' THEN 1 ELSE 0 END) as completed_count
        FROM users u
        LEFT JOIN tasks t ON u.id = t.assigned_to
        GROUP BY u.id
        ORDER BY task_count DESC
    ")->fetchAll();
    
    // Tareas completadas por mes
    $monthlyStats = $pdo->query("
        SELECT DATE_FORMAT(completed_at, '%Y-%m') as month,
            COUNT(*) as completed_tasks
        FROM tasks 
        WHERE status = 'completada' AND completed_at IS NOT NULL
        GROUP BY DATE_FORMAT(completed_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en reportes: " . $e->getMessage());
    $statusStats = $priorityStats = [];
    $userStats = $monthlyStats = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Reportes y Estadísticas</h1>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Filtros</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                            </div>
                            
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Aplicar Filtros</button>
                                <a href="reports.php" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas Generales -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Tareas por Estado</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($statusStats as $status => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= ucfirst($status) ?></span>
                                        <span class="badge bg-primary"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Tareas por Prioridad</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($priorityStats as $priority => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= ucfirst($priority) ?></span>
                                        <span class="badge bg-<?= 
                                            $priority == 'urgente' ? 'danger' : 
                                            ($priority == 'alta' ? 'warning' : 
                                            ($priority == 'media' ? 'info' : 'success')) 
                                        ?>"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rendimiento por Usuario -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Rendimiento por Usuario</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Total Tareas</th>
                                                <th>Completadas</th>
                                                <th>Porcentaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userStats as $user): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                    <td><?= $user['task_count'] ?></td>
                                                    <td><?= $user['completed_count'] ?></td>
                                                    <td>
                                                        <?php if ($user['task_count'] > 0): ?>
                                                            <?= round(($user['completed_count'] / $user['task_count']) * 100, 2) ?>%
                                                        <?php else: ?>
                                                            N/A
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tendencias Mensuales -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Tendencias Mensuales</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Mes</th>
                                                <th>Tareas Completadas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monthlyStats as $stat): ?>
                                                <tr>
                                                    <td><?= date('F Y', strtotime($stat['month'] . '-01')) ?></td>
                                                    <td><?= $stat['completed_tasks'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>