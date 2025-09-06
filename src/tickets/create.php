<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (!hasPermission('asistente')) {
    header('Location: index.php?error=no_permission');
    exit;
}

$errors = [];
$success = false;

// Obtener departamentos
try {
    $deptStmt = $pdo->query("SELECT id, name FROM departments ORDER BY name");
    $departments = $deptStmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener departamentos: " . $e->getMessage());
    $departments = [];
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $department_id = (int)$_POST['department_id'];
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);
    $client_phone = trim($_POST['client_phone']);
    $priority = $_POST['priority'];
    
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
            $ticket_number = generateTicketNumber($pdo);
            
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets 
                (ticket_number, title, description, department_id, client_name, client_email, client_phone, priority, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $ticket_number,
                $title,
                $description,
                $department_id,
                $client_name,
                $client_email,
                $client_phone,
                $priority,
                $_SESSION['user_id']
            ]);
            
            $success = true;
            header('Location: index.php?success=ticket_created');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al crear ticket: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket - Hotel Ikin</title>
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
                    <h1 class="h2">Crear Nuevo Ticket</h1>
                    <a href="index.php" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información del Ticket</h6>
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
                                                    value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                                    placeholder="Ej: PC no enciende, Error en sistema, etc.">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
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
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Descripción Detallada *</label>
                                        <textarea name="description" class="form-control" rows="5" required 
                                                placeholder="Describa el problema en detalle, incluyendo pasos para reproducirlo, mensajes de error, etc."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Departamento *</label>
                                        <select name="department_id" class="form-select" required>
                                            <option value="">Seleccionar departamento...</option>
                                            <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>" <?= ($_POST['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                                <?= safeOutput($dept['name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre del Cliente *</label>
                                                <input type="text" name="client_name" class="form-control" required 
                                                    value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>"
                                                    placeholder="Nombre completo del solicitante">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email del Cliente *</label>
                                                <input type="email" name="client_email" class="form-control" required 
                                                    value="<?= htmlspecialchars($_POST['client_email'] ?? '') ?>"
                                                    placeholder="email@hotelikin.com">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Teléfono del Cliente (Opcional)</label>
                                        <input type="tel" name="client_phone" class="form-control" 
                                            value="<?= htmlspecialchars($_POST['client_phone'] ?? '') ?>"
                                            placeholder="555-1234">
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Crear Ticket</button>
                                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
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