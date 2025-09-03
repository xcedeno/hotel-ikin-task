<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo asistentes, analistas, jefes y admin pueden crear tareas
if (!hasPermission('asistente')) {
    header('Location: tasks.php?error=no_permission');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
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
    
    if (!in_array($priority, ['baja', 'media', 'alta', 'urgente'])) {
        $errors[] = "Prioridad inválida";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tasks (title, description, priority, assigned_to, created_by, due_date) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $title,
                $description,
                $priority,
                $assigned_to,
                $_SESSION['user_id'],
                $due_date
            ]);
            
            $success = true;
            header('Location: tasks.php?success=task_created');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al crear la tarea: " . $e->getMessage();
        }
    }
}

// Obtener usuarios para asignar
$usersStmt = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('auxiliar', 'asistente', 'analista') ORDER BY full_name");
$users = $usersStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea - Hotel Ikin</title>
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
                    <h1 class="h2">Crear Nueva Tarea</h1>
                    <a href="tasks.php" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información de la Tarea</h6>
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
                                            value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Descripción *</label>
                                        <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Prioridad *</label>
                                                <select name="priority" class="form-select" required>
                                                    <option value="media" <?= ($_POST['priority'] ?? '') == 'media' ? 'selected' : '' ?>>Media</option>
                                                    <option value="baja" <?= ($_POST['priority'] ?? '') == 'baja' ? 'selected' : '' ?>>Baja</option>
                                                    <option value="alta" <?= ($_POST['priority'] ?? '') == 'alta' ? 'selected' : '' ?>>Alta</option>
                                                    <option value="urgente" <?= ($_POST['priority'] ?? '') == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Asignar a</label>
                                                <select name="assigned_to" class="form-select">
                                                    <option value="">Seleccionar usuario...</option>
                                                    <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>" <?= ($_POST['assigned_to'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($user['full_name']) ?> (<?= ucfirst($user['role']) ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de Vencimiento</label>
                                        <input type="date" name="due_date" class="form-control" 
                                            value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>"
                                            min="<?= date('Y-m-d') ?>">
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Crear Tarea</button>
                                        <a href="tasks.php" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </form>
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