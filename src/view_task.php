<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Obtener ID de la tarea
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id === 0) {
    header('Location: tasks.php?error=invalid_id');
    exit;
}

// Obtener datos de la tarea
try {
    $stmt = $pdo->prepare("
        SELECT t.*, 
            u1.full_name as assigned_name, u1.role as assigned_role,
            u2.full_name as created_name, u2.role as created_role
        FROM tasks t 
        LEFT JOIN users u1 ON t.assigned_to = u1.id 
        LEFT JOIN users u2 ON t.created_by = u2.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: tasks.php?error=task_not_found');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener tarea: " . $e->getMessage());
    header('Location: tasks.php?error=db_error');
    exit;
}

// Procesar cambio de estado (si se solicita)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $new_status = $_POST['status'];
    
    if (in_array($new_status, ['pendiente', 'en_progreso', 'completada', 'cancelada'])) {
        try {
            $updateData = ['status' => $new_status];
            
            // Si se marca como completada, agregar timestamp
            if ($new_status === 'completada') {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            } elseif ($task['status'] === 'completada' && $new_status !== 'completada') {
                $updateData['completed_at'] = null;
            }
            
            $stmt = $pdo->prepare("UPDATE tasks SET status = ?, completed_at = ? WHERE id = ?");
            $stmt->execute([$new_status, $updateData['completed_at'], $task_id]);
            
            // Recargar la tarea actualizada
            header("Location: view_task.php?id=$task_id&success=status_updated");
            exit;
            
        } catch (PDOException $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            $error = "Error al cambiar el estado";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Tarea - Hotel Ikin</title>
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
                    <h1 class="h2">Detalles de Tarea</h1>
                    <div>
                        <a href="tasks.php" class="btn btn-secondary me-2">Volver</a>
                        <?php if (canEditTask($task, $_SESSION['user_id'])): ?>
                        <a href="edit_task.php?id=<?= $task_id ?>" class="btn btn-primary">
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
                                <h5 class="mb-0"><?= htmlspecialchars($task['title']) ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <?= getPriorityBadge($task['priority']) ?>
                                        <?= getStatusBadge($task['status']) ?>
                                    </div>
                                    
                                    <!-- Formulario para cambiar estado -->
                                    <?php if ($_SESSION['user_role'] !== 'auxiliar' || $task['assigned_to'] == $_SESSION['user_id']): ?>
                                    <form method="POST" class="d-inline">
                                        <div class="input-group">
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pendiente" <?= $task['status'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="en_progreso" <?= $task['status'] == 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
                                                <option value="completada" <?= $task['status'] == 'completada' ? 'selected' : '' ?>>Completada</option>
                                                <option value="cancelada" <?= $task['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                            </select>
                                            <input type="hidden" name="change_status" value="1">
                                        </div>
                                    </form>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <h6>Descripción</h6>
                                    <p class="text-muted"><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Asignada a:</strong><br>
                                            <?php if ($task['assigned_name']): ?>
                                                <span class="text-primary"><?= $task['assigned_name'] ?></span>
                                                <small class="text-muted">(<?= ucfirst($task['assigned_role']) ?>)</small>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Creada por:</strong><br>
                                            <span class="text-info"><?= $task['created_name'] ?></span>
                                            <small class="text-muted">(<?= ucfirst($task['created_role']) ?>)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <strong>Fecha de creación:</strong><br>
                                            <span class="text-muted"><?= date('d/m/Y H:i', strtotime($task['created_at'])) ?></span>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Fecha de vencimiento:</strong><br>
                                            <?php if ($task['due_date']): ?>
                                                <span class="<?= strtotime($task['due_date']) < time() ? 'text-danger' : 'text-success' ?>">
                                                    <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin fecha definida</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($task['completed_at']): ?>
                                        <div class="mb-3">
                                            <strong>Completada el:</strong><br>
                                            <span class="text-success"><?= date('d/m/Y H:i', strtotime($task['completed_at'])) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Panel de acciones rápidas -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Acciones</h6>
                            </div>
                            <div class="card-body">
                                <?php if (canEditTask($task, $_SESSION['user_id'])): ?>
                                <a href="edit_task.php?id=<?= $task_id ?>" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-pencil"></i> Editar Tarea
                                </a>
                                <?php endif; ?>
                                
                                <a href="tasks.php" class="btn btn-secondary w-100 mb-2">
                                    <i class="bi bi-list"></i> Volver al Listado
                                </a>
                                
                                <?php if (hasPermission('jefe')): ?>
                                <hr>
                                <h6 class="mb-2">Acciones Avanzadas</h6>
                                <button class="btn btn-outline-warning w-100 mb-2">
                                    <i class="bi bi-clock-history"></i> Reasignar
                                </button>
                                <button class="btn btn-outline-danger w-100">
                                    <i class="bi bi-trash"></i> Eliminar Tarea
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Panel de historial (futura implementación) -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Historial</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">No hay historial disponible</p>
                                <small class="text-muted">(Funcionalidad en desarrollo)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comentarios (futura implementación) -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Comentarios</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            <i class="bi bi-chat-dots display-4 text-muted"></i>
                            <p class="text-muted mt-2">Sistema de comentarios en desarrollo</p>
                            <small>Próximamente podrás agregar comentarios y seguimientos</small>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>