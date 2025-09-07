<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Verificar permisos
if (!hasPermission('asistente')) {
    header('Location: tasks.php?error=no_permission');
    exit;
}

$errors = [];
$success = false;

// Obtener ID de la tarea
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id === 0) {
    header('Location: tasks.php?error=invalid_id');
    exit;
}

// Obtener datos de la tarea
try {
    $stmt = $pdo->prepare("
        SELECT t.*, u1.full_name as assigned_name, u2.full_name as created_name 
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
    
    // Verificar permisos para editar esta tarea específica
    if (!canEditTask($task, $_SESSION['user_id'])) {
        header('Location: tasks.php?error=no_permission');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener tarea: " . $e->getMessage());
    header('Location: tasks.php?error=db_error');
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = $_POST['status'];
    $priority = $_POST['priority'];
    $assigned_to = $_POST['assigned_to'] ?: null;
    $due_date = $_POST['due_date'] ?: null;
    
    // Validaciones
    if (empty($title)) {
        $errors[] = "El título es obligatorio";
    }
    
    if (empty($description)) {
        $errors[] = "La descripción es obligatoria";
    }
    
    if (!in_array($status, ['pendiente', 'en_progreso', 'completada', 'cancelada'])) {
        $errors[] = "Estado inválido";
    }
    
    if (!in_array($priority, ['baja', 'media', 'alta', 'urgente'])) {
        $errors[] = "Prioridad inválida";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE tasks 
                SET title = ?, description = ?, status = ?, priority = ?, 
                    assigned_to = ?, due_date = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title,
                $description,
                $status,
                $priority,
                $assigned_to,
                $due_date,
                $task_id
            ]);
            
            header('Location: tasks.php?success=task_updated');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al actualizar tarea: " . $e->getMessage();
        }
    }
}

// Obtener usuarios para asignar
try {
    $usersStmt = $pdo->query("
        SELECT id, full_name, role 
        FROM users 
        WHERE role IN ('auxiliar', 'asistente', 'analista', 'jefe') 
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
    <title>Editar Tarea - Hotel Ikin</title>
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
                    <h1 class="h2">Editar Tarea</h1>
                    <a href="tasks.php" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Editar Información de Tarea</h6>
                            </div>
                            
                            <div class="card-body">
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                            <li><?= $error ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Título *</label>
                                        <input type="text" name="title" class="form-control" required 
                                            value="<?= htmlspecialchars($task['title']) ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Descripción *</label>
                                        <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($task['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Estado *</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="pendiente" <?= $task['status'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                    <option value="en_progreso" <?= $task['status'] == 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
                                                    <option value="completada" <?= $task['status'] == 'completada' ? 'selected' : '' ?>>Completada</option>
                                                    <option value="cancelada" <?= $task['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Prioridad *</label>
                                                <select name="priority" class="form-select" required>
                                                    <option value="baja" <?= $task['priority'] == 'baja' ? 'selected' : '' ?>>Baja</option>
                                                    <option value="media" <?= $task['priority'] == 'media' ? 'selected' : '' ?>>Media</option>
                                                    <option value="alta" <?= $task['priority'] == 'alta' ? 'selected' : '' ?>>Alta</option>
                                                    <option value="urgente" <?= $task['priority'] == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Asignar a</label>
                                                <select name="assigned_to" class="form-select">
                                                    <option value="">Seleccionar usuario...</option>
                                                    <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= $task['assigned_to'] == $user['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['full_name']) ?> (<?= ucfirst($user['role']) ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha de Vencimiento</label>
                                                <input type="date" name="due_date" class="form-control" 
                                                       value="<?= $task['due_date'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Actualizar Tarea</button>
                                        <a href="tasks.php" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </form>
                                
                                <hr>
                                
                                <div class="mt-3">
                                    <h6>Información de la Tarea</h6>
                                    <p class="text-muted small mb-1">
                                        <strong>Creada por:</strong> <?= $task['created_name'] ?>
                                    </p>
                                    <p class="text-muted small mb-1">
                                        <strong>Fecha creación:</strong> <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?>
                                    </p>
                                    <?php if ($task['completed_at']): ?>
                                    <p class="text-muted small mb-1">
                                        <strong>Completada el:</strong> <?= date('d/m/Y H:i', strtotime($task['completed_at'])) ?>
                                    </p>
                                    <?php endif; ?>
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