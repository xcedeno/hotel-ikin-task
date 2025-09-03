<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Paginación y filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$assignedFilter = $_GET['assigned'] ?? '';

// Construir consulta con filtros
$whereConditions = [];
$params = [];

if ($statusFilter) {
    $whereConditions[] = "t.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter) {
    $whereConditions[] = "t.priority = ?";
    $params[] = $priorityFilter;
}

if ($assignedFilter) {
    $whereConditions[] = "t.assigned_to = ?";
    $params[] = $assignedFilter;
}

// Si es auxiliar, solo ver sus tareas
if ($_SESSION['user_role'] === 'auxiliar') {
    $whereConditions[] = "t.assigned_to = ?";
    $params[] = $_SESSION['user_id'];
}

$whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Obtener tareas - SOLUCIÓN AL ERROR
try {
    $sql = "
        SELECT t.*, u1.full_name as assigned_name, u2.full_name as created_name 
        FROM tasks t 
        LEFT JOIN users u1 ON t.assigned_to = u1.id 
        LEFT JOIN users u2 ON t.created_by = u2.id 
        $whereClause 
        ORDER BY t.created_at DESC 
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters nombrados para LIMIT y OFFSET
    foreach ($params as $key => $value) {
        $stmt->bindValue(($key + 1), $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $tasks = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en consulta de tareas: " . $e->getMessage());
    $tasks = [];
}

// Total de tareas para paginación
try {
    $countSql = "SELECT COUNT(*) FROM tasks t $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalTasks = $countStmt->fetchColumn();
    $totalPages = ceil($totalTasks / $limit);
    
} catch (PDOException $e) {
    error_log("Error en conteo de tareas: " . $e->getMessage());
    $totalTasks = 0;
    $totalPages = 1;
}

// Obtener usuarios para filtro
try {
    $usersStmt = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name");
    $users = $usersStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas - Hotel Ikin</title>
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
                    <h1 class="h2">Gestión de Tareas</h1>
                    
                    <?php if (hasPermission('asistente')): ?>
                    <a href="create_task.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nueva Tarea
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Filtros</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pendiente" <?= $statusFilter == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="en_progreso" <?= $statusFilter == 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
                                    <option value="completada" <?= $statusFilter == 'completada' ? 'selected' : '' ?>>Completada</option>
                                    <option value="cancelada" <?= $statusFilter == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Prioridad</label>
                                <select name="priority" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="baja" <?= $priorityFilter == 'baja' ? 'selected' : '' ?>>Baja</option>
                                    <option value="media" <?= $priorityFilter == 'media' ? 'selected' : '' ?>>Media</option>
                                    <option value="alta" <?= $priorityFilter == 'alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="urgente" <?= $priorityFilter == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Asignado a</label>
                                <select name="assigned" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $assignedFilter == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['full_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                <a href="tasks.php" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Lista de Tareas -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Lista de Tareas (<?= $totalTasks ?> total)</h6>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($tasks): ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="task-card card mb-3 task-card-<?= $task['priority'] ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="card-title">
                                                    <?= htmlspecialchars($task['title']) ?>
                                                    <?= getPriorityBadge($task['priority']) ?>
                                                    <?= getStatusBadge($task['status']) ?>
                                                </h5>
                                                
                                                <p class="card-text"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                                                
                                                <div class="task-meta text-muted small">
                                                    <span>Asignada a: <strong><?= $task['assigned_name'] ?: 'Sin asignar' ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Creada por: <strong><?= $task['created_name'] ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Vence: <strong><?= $task['due_date'] ? date('d/m/Y', strtotime($task['due_date'])) : 'Sin fecha' ?></strong></span>
                                                </div>
                                            </div>
                                            
                                            <div class="btn-group">
                                                <?php if (canEditTask($task, $_SESSION['user_id'])): ?>
                                                <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>
                                                
                                                <a href="view_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Paginación -->
                            <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="text-muted mt-3">No se encontraron tareas</p>
                                <?php if (hasPermission('asistente')): ?>
                                <a href="create_task.php" class="btn btn-primary">Crear primera tarea</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>