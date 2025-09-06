<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireRole('jefe'); // Solo jefes y admin pueden ver estadísticas

// Fechas para filtros
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Obtener estadísticas por departamento
try {
    $statsStmt = $pdo->prepare("
        SELECT d.id, d.name, 
            COUNT(t.id) as total_tickets,
            SUM(CASE WHEN t.status = 'abierto' THEN 1 ELSE 0 END) as abiertos,
            SUM(CASE WHEN t.status = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
            SUM(CASE WHEN t.status IN ('resuelto', 'cerrado') THEN 1 ELSE 0 END) as resueltos,
            SUM(CASE WHEN t.priority = 'urgente' THEN 1 ELSE 0 END) as urgentes,
            AVG(TIMESTAMPDIFF(HOUR, t.created_at, t.closed_at)) as avg_resolution_time
        FROM departments d
        LEFT JOIN support_tickets t ON d.id = t.department_id 
            AND t.created_at BETWEEN ? AND ?
        GROUP BY d.id, d.name
        ORDER BY total_tickets DESC
    ");
    
    $statsStmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
    $departmentStats = $statsStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en estadísticas: " . $e->getMessage());
    $departmentStats = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas por Departamento - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Estadísticas por Departamento</h1>
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
                                <a href="department_stats.php" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="row">
                    <?php foreach ($departmentStats as $stat): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><?= safeOutput($stat['name']) ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-number"><?= $stat['total_tickets'] ?></div>
                                        <div class="stat-label">Total</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-number text-primary"><?= $stat['abiertos'] ?></div>
                                        <div class="stat-label">Abiertos</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-number text-success"><?= $stat['resueltos'] ?></div>
                                        <div class="stat-label">Resueltos</div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-success" style="width: <?= $stat['total_tickets'] > 0 ? ($stat['resueltos'] / $stat['total_tickets'] * 100) : 0 ?>%">
                                            <?= $stat['total_tickets'] > 0 ? round($stat['resueltos'] / $stat['total_tickets'] * 100, 1) : 0 ?>%
                                        </div>
                                    </div>
                                    
                                    <div class="row small text-muted">
                                        <div class="col-6">
                                            <i class="bi bi-clock"></i> 
                                            Tiempo promedio: <?= $stat['avg_resolution_time'] ? round($stat['avg_resolution_time'], 1) . ' horas' : 'N/A' ?>
                                        </div>
                                        <div class="col-6 text-end">
                                            <i class="bi bi-exclamation-triangle"></i> 
                                            Urgentes: <?= $stat['urgentes'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tabla Resumen -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Resumen por Departamento</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Departamento</th>
                                        <th>Total Tickets</th>
                                        <th>Abiertos</th>
                                        <th>En Proceso</th>
                                        <th>Resueltos</th>
                                        <th>Urgentes</th>
                                        <th>Tiempo Promedio</th>
                                        <th>Eficiencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departmentStats as $stat): ?>
                                    <tr>
                                        <td><?= safeOutput($stat['name']) ?></td>
                                        <td><?= $stat['total_tickets'] ?></td>
                                        <td><span class="badge bg-primary"><?= $stat['abiertos'] ?></span></td>
                                        <td><span class="badge bg-warning"><?= $stat['en_proceso'] ?></span></td>
                                        <td><span class="badge bg-success"><?= $stat['resueltos'] ?></span></td>
                                        <td><span class="badge bg-danger"><?= $stat['urgentes'] ?></span></td>
                                        <td><?= $stat['avg_resolution_time'] ? round($stat['avg_resolution_time'], 1) . 'h' : 'N/A' ?></td>
                                        <td>
                                            <?php if ($stat['total_tickets'] > 0): ?>
                                            <span class="badge bg-<?= ($stat['resueltos'] / $stat['total_tickets'] * 100) >= 80 ? 'success' : (($stat['resueltos'] / $stat['total_tickets'] * 100) >= 50 ? 'warning' : 'danger') ?>">
                                                <?= round($stat['resueltos'] / $stat['total_tickets'] * 100, 1) ?>%
                                            </span>
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
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>