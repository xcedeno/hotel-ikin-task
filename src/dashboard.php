<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Estadísticas para el dashboard
$stats = [
    'total_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn(),
    'pending_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'pendiente'")->fetchColumn(),
    'completed_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'completada'")->fetchColumn(),
    'urgent_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE priority = 'urgente' AND status != 'completada'")->fetchColumn()
];

// Tareas recientes
$stmt = $pdo->query("
    SELECT t.*, u1.full_name as assigned_name, u2.full_name as created_name 
    FROM tasks t 
    LEFT JOIN users u1 ON t.assigned_to = u1.id 
    LEFT JOIN users u2 ON t.created_by = u2.id 
    ORDER BY t.created_at DESC 
    LIMIT 5
");
$recent_tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="me-2">Hola, <?= $_SESSION['full_name'] ?></span>
                        <?= getRoleBadge($_SESSION['user_role']) ?>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number"><?= $stats['total_tasks'] ?></div>
                                <div class="stat-label">Total Tareas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-warning"><?= $stats['pending_tasks'] ?></div>
                                <div class="stat-label">Pendientes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-success"><?= $stats['completed_tasks'] ?></div>
                                <div class="stat-label">Completadas</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-danger"><?= $stats['urgent_tasks'] ?></div>
                                <div class="stat-label">Urgentes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tareas Recientes -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Tareas Recientes</h5>
                                <a href="tasks.php" class="btn btn-sm btn-primary">Ver Todas</a>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_tasks): ?>
                                    <?php foreach ($recent_tasks as $task): ?>
                                        <div class="task-card card mb-2 task-card-<?= $task['priority'] ?>">
                                            <div class="card-body py-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($task['title']) ?></h6>
                                                        <small class="text-muted">
                                                            Asignada a: <?= $task['assigned_name'] ?: 'Sin asignar' ?>
                                                            | <?= getPriorityBadge($task['priority']) ?>
                                                            | <?= getStatusBadge($task['status']) ?>
                                                        </small>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">
                                                            <?= date('d M Y', strtotime($task['due_date'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No hay tareas recientes</p>
                                <?php endif; ?>
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