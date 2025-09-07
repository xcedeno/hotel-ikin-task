<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

requireRole('jefe'); // Solo jefes y admin pueden eliminar

$type = $_GET['type'] ?? ''; // 'user' o 'task'
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || $id === 0) {
    header('Location: ' . ($type === 'user' ? 'users.php' : 'tasks.php') . '?error=invalid_id');
    exit;
}

// Obtener información según el tipo
try {
    if ($type === 'user') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        $title = "Eliminar Usuario";
        $message = "¿Estás seguro de eliminar al usuario <strong>" . htmlspecialchars($item['full_name'], ENT_QUOTES, 'UTF-8') . "</strong>?";
        
        // Contar tareas asignadas
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to = ?");
        $stmt->execute([$id]);
        $relatedItems = $stmt->fetchColumn();
        $warning = $relatedItems > 0 ? "Este usuario tiene $relatedItems tareas asignadas que también serán eliminadas." : "";
        
    } else { // task
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        $title = "Eliminar Tarea";
        $message = "¿Estás seguro de eliminar la tarea <strong>" . htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . "</strong>?";
        $warning = "Esta acción no se puede deshacer.";
    }
    
    if (!$item) {
        header('Location: ' . ($type === 'user' ? 'users.php' : 'tasks.php') . '?error=not_found');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    header('Location: ' . ($type === 'user' ? 'users.php' : 'tasks.php') . '?error=db_error');
    exit;
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($type === 'user') {
            // Eliminar usuario y sus tareas
            $pdo->beginTransaction();
            
            // Eliminar tareas asignadas al usuario
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE assigned_to = ?");
            $stmt->execute([$id]);
            
            // Reasignar o eliminar tareas creadas por el usuario
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE created_by = ?");
            $stmt->execute([$id]);
            
            // Eliminar usuario
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            header('Location: users.php?success=user_deleted');
            
        } else { // task
            // Eliminar tarea
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: tasks.php?success=task_deleted');
        }
        
        exit;
        
    } catch (PDOException $e) {
        if ($type === 'user') {
            $pdo->rollBack();
        }
        error_log("Error al eliminar: " . $e->getMessage());
        header('Location: ' . ($type === 'user' ? 'users.php' : 'tasks.php') . '?error=delete_failed');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-6 mx-auto mt-5">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><?= $title ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h6>¡Advertencia!</h6>
                            <p><?= $message ?></p>
                            <?php if (!empty($warning)): ?>
                            <p class="mb-0"><?= $warning ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                <a href="<?= $type === 'user' ? 'users.php' : 'tasks.php' ?>" class="btn btn-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>