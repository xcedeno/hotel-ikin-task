<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!hasPermission('asistente')) {
    header('Location: index.php?error=no_permission');
    exit;
}

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ticket_id === 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Obtener datos del ticket
try {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name
        FROM support_tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
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

$errors = [];

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $department_id = (int)$_POST['department_id'];
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);
    $client_phone = trim($_POST['client_phone']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'] ?: null;
    $resolution_description = trim($_POST['resolution_description']);
    
    // Validaciones
    if (empty($title)) {
        $errors[] = "El título es obligatorio";
    }
    
    if (empty($description)) {
        $errors[] = "La descripción es obligatoria";
    }
    
    if (empty($department_id)) {
        $errors[] = "El departamento es obligatorio";
    }
    
    if (empty($client_name)) {
        $errors[] = "El nombre del cliente es obligatorio";
    }
    
    if (empty($client_email) || !filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El email del cliente es inválido";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE support_tickets 
                SET title = ?, description = ?, department_id = ?, client_name = ?, 
                    client_email = ?, client_phone = ?, priority = ?, due_date = ?,
                    resolution_description = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title,
                $description,
                $department_id,
                $client_name,
                $client_email,
                $client_phone,
                $priority,
                $due_date,
                $resolution_description,
                $ticket_id
            ]);
            
            header('Location: view.php?id=' . $ticket_id . '&success=ticket_updated');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al actualizar ticket: " . $e->getMessage();
        }
    }
}

// Obtener departamentos
try {
    $deptStmt = $pdo->query("SELECT id, name FROM departments ORDER BY name");
    $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener departamentos: " . $e->getMessage());
    $departments = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ticket - Hotel Ikin</title>
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
                    <h1 class="h2">Editar Ticket: <?= safeOutput($ticket['ticket_number']) ?></h1>
                    <a href="view.php?id=<?= $ticket_id ?>" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Editar Información del Ticket</h6>
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
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="mb-3">
                                                <label class="form-label">Título del Problema *</label>
                                                <input type="text" name="title" class="form-control" required 
                                                    value="<?= htmlspecialchars($ticket['title']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Prioridad *</label>
                                                <select name="priority" class="form-select" required>
                                                    <option value="baja" <?= $ticket['priority'] == 'baja' ? 'selected' : '' ?>>Baja</option>
                                                    <option value="media" <?= $ticket['priority'] == 'media' ? 'selected' : '' ?>>Media</option>
                                                    <option value="alta" <?= $ticket['priority'] == 'alta' ? 'selected' : '' ?>>Alta</option>
                                                    <option value="urgente" <?= $ticket['priority'] == 'urgente' ? 'selected' : '' ?>>Urgente</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Descripción Detallada *</label>
                                        <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($ticket['description']) ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Solución Aplicada</label>
                                        <textarea name="resolution_description" class="form-control" rows="3"><?= htmlspecialchars($ticket['resolution_description'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Departamento *</label>
                                                <select name="department_id" class="form-select" required>
                                                    <?php foreach ($departments as $dept): ?>
                                                    <option value="<?= $dept['id'] ?>" <?= $ticket['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                                        <?= safeOutput($dept['name']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Fecha de Vencimiento</label>
                                                <input type="date" name="due_date" class="form-control" 
                                                    value="<?= $ticket['due_date'] ?>"
                                                    min="<?= date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre del Cliente *</label>
                                                <input type="text" name="client_name" class="form-control" required 
                                                    value="<?= htmlspecialchars($ticket['client_name']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Email del Cliente *</label>
                                                <input type="email" name="client_email" class="form-control" required 
                                                    value="<?= htmlspecialchars($ticket['client_email']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Teléfono del Cliente</label>
                                                <input type="tel" name="client_phone" class="form-control" 
                                                    value="<?= htmlspecialchars($ticket['client_phone']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Actualizar Ticket</button>
                                        <a href="view.php?id=<?= $ticket_id ?>" class="btn btn-secondary">Cancelar</a>
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