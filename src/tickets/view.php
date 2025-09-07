<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ticket_id === 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Obtener datos del ticket
try {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name, d.description as department_desc,
               u1.full_name as assigned_name, u1.role as assigned_role,
               u2.full_name as created_name, u2.role as created_role
        FROM support_tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        LEFT JOIN users u1 ON t.assigned_to = u1.id 
        LEFT JOIN users u2 ON t.created_by = u2.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        header('Location: index.php?error=ticket_not_found');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener ticket: " . $e->getMessage());
    header('Location: index.php?error=db_error');
    exit;
}

// Procesar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $assigned_to = $_POST['assigned_to'] ?: null;
    
    if (in_array($new_status, ['abierto', 'en_proceso', 'esperando_cliente', 'resuelto', 'cerrado', 'cancelado'])) {
        try {
            $updateData = ['status' => $new_status, 'assigned_to' => $assigned_to];
            
            // Si se marca como resuelto/cerrado, agregar timestamp
            if (in_array($new_status, ['resuelto', 'cerrado'])) {
                $updateData['closed_at'] = date('Y-m-d H:i:s');
            } elseif (in_array($ticket['status'], ['resuelto', 'cerrado']) && !in_array($new_status, ['resuelto', 'cerrado'])) {
                $updateData['closed_at'] = null;
            }
            
            $stmt = $pdo->prepare("UPDATE support_tickets SET status = ?, assigned_to = ?, closed_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $assigned_to, $updateData['closed_at'], $ticket_id]);
            
            // Recargar el ticket actualizado
            header("Location: view.php?id=$ticket_id&success=status_updated");
            exit;
            
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            $error = "Error al cambiar el estado";
        }
    }
}

// Obtener usuarios para asignación
try {
    $usersStmt = $pdo->query("
        SELECT id, full_name, role 
        FROM users 
        WHERE role IN ('analista', 'asistente', 'jefe', 'admin') 
        ORDER BY full_name
    ");
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
    <title>Ticket <?= $ticket['ticket_number'] ?> - Hotel Ikin</title>
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
                    <h1 class="h2">Ticket: <?= safeOutput($ticket['ticket_number']) ?></h1>
                    <div>
                        <a href="index.php" class="btn btn-secondary me-2">Volver</a>
                        <?php if (hasPermission('asistente')): ?>
                        <a href="edit.php?id=<?= $ticket_id ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php
                        switch ($_GET['success']) {
                            case 'status_updated': echo "Estado actualizado correctamente"; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0"><?= safeOutput($ticket['title']) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <?= getPriorityBadge($ticket['priority']) ?>
                                        <?= getTicketStatusBadge($ticket['status']) ?>
                                        <?php if (isTicketOverdue($ticket['due_date'], $ticket['status'])): ?>
                                        <span class="badge bg-danger">Vencido</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Formulario para cambiar estado -->
                                    <form method="POST" class="d-inline">
                                        <div class="input-group">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="abierto" <?= $ticket['status'] == 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                                <option value="en_proceso" <?= $ticket['status'] == 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                                <option value="esperando_cliente" <?= $ticket['status'] == 'esperando_cliente' ? 'selected' : '' ?>>Esperando Cliente</option>
                                                <option value="resuelto" <?= $ticket['status'] == 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                                <option value="cerrado" <?= $ticket['status'] == 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                                <option value="cancelado" <?= $ticket['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Actualizar</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="mb-4">
                                    <h6>Descripción</h6>
                                    <p class="text-muted"><?= nl2br(safeOutput($ticket['description'])) ?></p>
                                </div>
                                
                                <?php if (!empty($ticket['resolution_description'])): ?>
                                <div class="mb-4">
                                    <h6>Solución Aplicada</h6>
                                    <p class="text-success"><?= nl2br(safeOutput($ticket['resolution_description'])) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Panel de información -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Información del Ticket</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Departamento:</strong><br>
                                    <span class="text-primary"><?= safeOutput($ticket['department_name']) ?></span>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Cliente:</strong><br>
                                    <span><?= safeOutput($ticket['client_name']) ?></span>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Email:</strong><br>
                                    <span><?= safeOutput($ticket['client_email']) ?></span>
                                </div>
                                
                                <?php if (!empty($ticket['client_phone'])): ?>
                                <div class="mb-2">
                                    <strong>Teléfono:</strong><br>
                                    <span><?= safeOutput($ticket['client_phone']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-2">
                                    <strong>Asignado a:</strong><br>
                                    <?php if ($ticket['assigned_name']): ?>
                                        <span class="text-info"><?= $ticket['assigned_name'] ?></span>
                                        <small class="text-muted">(<?= ucfirst($ticket['assigned_role']) ?>)</small>
                                    <?php else: ?>
                                        <span class="text-muted">Sin asignar</span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Formulario de asignación -->
                                <form method="POST" class="mb-3">
                                    <div class="input-group input-group-sm">
                                        <select name="assigned_to" class="form-select">
                                            <option value="">Asignar a...</option>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" <?= $ticket['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                                <?= safeOutput($user['full_name']) ?> (<?= ucfirst($user['role']) ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                        <button type="submit" class="btn btn-outline-primary">Asignar</button>
                                    </div>
                                </form>
                                
                                <div class="mb-2">
                                    <strong>Creado por:</strong><br>
                                    <span class="text-info"><?= $ticket['created_name'] ?></span>
                                    <small class="text-muted">(<?= ucfirst($ticket['created_role']) ?>)</small>
                                </div>
                                
                                <div class="mb-2">
                                    <strong>Fecha creación:</strong><br>
                                    <span class="text-muted"><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
                                </div>
                                
                                <?php if ($ticket['due_date']): ?>
                                <div class="mb-2">
                                    <strong>Fecha vencimiento:</strong><br>
                                    <span class="<?= strtotime($ticket['due_date']) < time() ? 'text-danger' : 'text-success' ?>">
                                        <?= date('d/m/Y', strtotime($ticket['due_date'])) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($ticket['closed_at']): ?>
                                <div class="mb-2">
                                    <strong>Cerrado el:</strong><br>
                                    <span class="text-success"><?= date('d/m/Y H:i', strtotime($ticket['closed_at'])) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-2">
                                    <strong>Última actualización:</strong><br>
                                    <span class="text-muted"><?= date('d/m/Y H:i', strtotime($ticket['updated_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel de acciones -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Acciones</h6>
                            </div>
                            <div class="card-body">
                                <?php if (hasPermission('asistente')): ?>
                                <a href="edit.php?id=<?= $ticket_id ?>" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-pencil"></i> Editar Ticket
                                </a>
                                <?php endif; ?>
                                
                                <a href="index.php" class="btn btn-secondary w-100 mb-2">
                                    <i class="bi bi-list"></i> Volver al Listado
                                </a>
                                
                                <?php if (hasPermission('jefe')): ?>
                                <hr>
                                <h6 class="mb-2">Acciones Avanzadas</h6>
                                <button class="btn btn-outline-warning w-100 mb-2">
                                    <i class="bi bi-clock-history"></i> Cambiar Prioridad
                                </button>
                                <a href="delete_confirmation.php?type=ticket&id=<?= $ticket_id ?>" class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> Eliminar Ticket
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Historial de cambios (futura implementación) -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Historial de Cambios</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            <i class="bi bi-clock-history display-4 text-muted"></i>
                            <p class="text-muted mt-2">Sistema de historial en desarrollo</p>
                            <small>Próximamente podrás ver el historial completo de cambios</small>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>