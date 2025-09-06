<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Paginación y filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$departmentFilter = $_GET['department'] ?? '';
$assignedFilter = $_GET['assigned'] ?? '';

// Construir consulta con filtros
$whereConditions = [];
$params = [];

if (!empty($statusFilter)) {
    $whereConditions[] = "t.status = ?";
    $params[] = $statusFilter;
}

if (!empty($priorityFilter)) {
    $whereConditions[] = "t.priority = ?";
    $params[] = $priorityFilter;
}

if (!empty($departmentFilter)) {
    $whereConditions[] = "t.department_id = ?";
    $params[] = $departmentFilter;
}

if (!empty($assignedFilter)) {
    $whereConditions[] = "t.assigned_to = ?";
    $params[] = $assignedFilter;
}

// Si no es admin/jefe, solo ver tickets asignados o propios
if (!hasPermission('jefe')) {
    $whereConditions[] = "(t.assigned_to = ? OR t.created_by = ?)";
    $params[] = $_SESSION['user_id'];
    $params[] = $_SESSION['user_id'];
}

$whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Obtener tickets
try {
    $sql = "
        SELECT t.*, d.name as department_name, 
               u1.full_name as assigned_name, u2.full_name as created_name
        FROM support_tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        LEFT JOIN users u1 ON t.assigned_to = u1.id 
        LEFT JOIN users u2 ON t.created_by = u2.id 
        $whereClause 
        ORDER BY 
            CASE WHEN t.status = 'abierto' THEN 1 
                 WHEN t.status = 'en_proceso' THEN 2
                 WHEN t.status = 'esperando_cliente' THEN 3
                 ELSE 4 END,
            t.priority DESC,
            t.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $pdo->prepare($sql);
    
    // Combinar todos los parámetros
    $allParams = array_merge($params, [$limit, $offset]);
    
    $stmt->execute($allParams);
    $tickets = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error en consulta de tickets: " . $e->getMessage());
    $tickets = [];
}

// Total de tickets para paginación
try {
    $countSql = "SELECT COUNT(*) FROM support_tickets t $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalTickets = $countStmt->fetchColumn();
    $totalPages = ceil($totalTickets / $limit);
    
} catch (PDOException $e) {
    error_log("Error en conteo de tickets: " . $e->getMessage());
    $totalTickets = 0;
    $totalPages = 1;
}

// Obtener departamentos para filtro
try {
    $deptStmt = $pdo->query("SELECT id, name FROM departments ORDER BY name");
    $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener departamentos: " . $e->getMessage());
    $departments = [];
}

// Obtener usuarios para filtro
try {
    $usersStmt = $pdo->query("SELECT id, full_name FROM users WHERE role IN ('analista', 'asistente', 'jefe', 'admin') ORDER BY full_name");
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
    <title>Tickets de Soporte - Hotel Ikin</title>
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
                    <h1 class="h2">Tickets de Soporte</h1>
                    <a href="create.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nuevo Ticket
                    </a>
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
                                    <option value="abierto" <?= $statusFilter == 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                    <option value="en_proceso" <?= $statusFilter == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                    <option value="esperando_cliente" <?= $statusFilter == 'esperando_cliente' ? 'selected' : '' ?>>Esperando Cliente</option>
                                    <option value="resuelto" <?= $statusFilter == 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                    <option value="cerrado" <?= $statusFilter == 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                    <option value="cancelado" <?= $statusFilter == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
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
                                <label class="form-label">Departamento</label>
                                <select name="department" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $departmentFilter == $dept['id'] ? 'selected' : '' ?>>
                                        <?= safeOutput($dept['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label">Asignado a</label>
                                <select name="assigned" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $assignedFilter == $user['id'] ? 'selected' : '' ?>>
                                        <?= safeOutput($user['full_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                <a href="index.php" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Estadísticas Rápidas -->
                <div class="row mb-4">
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number"><?= $totalTickets ?></div>
                                <div class="stat-label">Total Tickets</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-primary">
                                    <?php
                                    $openCount = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'abierto'")->fetchColumn();
                                    echo $openCount;
                                    ?>
                                </div>
                                <div class="stat-label">Abiertos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-warning">
                                    <?php
                                    $processCount = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'en_proceso'")->fetchColumn();
                                    echo $processCount;
                                    ?>
                                </div>
                                <div class="stat-label">En Proceso</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-success">
                                    <?php
                                    $resolvedCount = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status IN ('resuelto', 'cerrado')")->fetchColumn();
                                    echo $resolvedCount;
                                    ?>
                                </div>
                                <div class="stat-label">Resueltos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="card text-center">
                            <div class="card-body">
                                <div class="stat-number text-danger">
                                    <?php
                                    $urgentCount = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE priority = 'urgente' AND status NOT IN ('resuelto', 'cerrado', 'cancelado')")->fetchColumn();
                                    echo $urgentCount;
                                    ?>
                                </div>
                                <div class="stat-label">Urgentes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Tickets -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Lista de Tickets (<?= $totalTickets ?> total)</h6>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($tickets): ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="ticket-card card mb-3 <?= isTicketOverdue($ticket['due_date'], $ticket['status']) ? 'border-danger' : '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="card-title mb-0 me-2">
                                                        <a href="view.php?id=<?= $ticket['id'] ?>" class="text-decoration-none">
                                                            <?= safeOutput($ticket['ticket_number']) ?>: <?= safeOutput($ticket['title']) ?>
                                                        </a>
                                                    </h5>
                                                    <?= getPriorityBadge($ticket['priority']) ?>
                                                    <?= getTicketStatusBadge($ticket['status']) ?>
                                                    
                                                    <?php if (isTicketOverdue($ticket['due_date'], $ticket['status'])): ?>
                                                    <span class="badge bg-danger ms-2">Vencido</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <p class="card-text text-muted"><?= nl2br(safeOutput(substr($ticket['description'], 0, 150))) ?><?= strlen($ticket['description']) > 150 ? '...' : '' ?></p>
                                                
                                                <div class="ticket-meta text-muted small">
                                                    <span>Departamento: <strong><?= safeOutput($ticket['department_name']) ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Cliente: <strong><?= safeOutput($ticket['client_name']) ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Asignado a: <strong><?= $ticket['assigned_name'] ?: 'Sin asignar' ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Creación: <strong><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></strong></span>
                                                    <span class="mx-2">|</span>
                                                    <span>Edad: <strong><?= getTicketAge($ticket['created_at']) ?></strong></span>
                                                </div>
                                            </div>
                                            
                                            <div class="btn-group">
                                                <a href="view.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if (hasPermission('asistente')): ?>
                                                <a href="edit.php?id=<?= $ticket['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php endif; ?>
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
                                <p class="text-muted mt-3">No se encontraron tickets</p>
                                <a href="create.php" class="btn btn-primary">Crear primer ticket</a>
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