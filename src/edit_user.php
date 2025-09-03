<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo jefe y admin pueden editar usuarios
requireRole('jefe');

$errors = [];
$success = false;

// Obtener ID del usuario a editar
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id === 0) {
    header('Location: users.php?error=invalid_id');
    exit;
}

// Obtener datos del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php?error=user_not_found');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error al obtener usuario: " . $e->getMessage());
    header('Location: users.php?error=db_error');
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $department = trim($_POST['department']);
    $change_password = isset($_POST['change_password']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio";
    }
    
    if (empty($full_name)) {
        $errors[] = "El nombre completo es obligatorio";
    }
    
    if (!in_array($role, ['auxiliar', 'asistente', 'analista', 'jefe', 'admin'])) {
        $errors[] = "Rol inválido";
    }
    
    if ($change_password) {
        if (empty($new_password)) {
            $errors[] = "La nueva contraseña es obligatoria";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Las contraseñas no coinciden";
        }
    }
    
    // Verificar si el username ya existe (excluyendo el usuario actual)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $user_id]);
            
            if ($stmt->fetch()) {
                $errors[] = "El nombre de usuario ya existe";
            }
        } catch (PDOException $e) {
            $errors[] = "Error al verificar usuario: " . $e->getMessage();
        }
    }
    
    if (empty($errors)) {
        try {
            if ($change_password) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, full_name = ?, role = ?, department = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username,
                    $full_name,
                    $role,
                    $department,
                    $hashedPassword,
                    $user_id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = ?, full_name = ?, role = ?, department = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $username,
                    $full_name,
                    $role,
                    $department,
                    $user_id
                ]);
            }
            
            header('Location: users.php?success=user_updated');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al actualizar usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Hotel Ikin</title>
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
                    <h1 class="h2">Editar Usuario</h1>
                    <a href="users.php" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Editar Información de Usuario</h6>
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
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre de Usuario *</label>
                                                <input type="text" name="username" class="form-control" required 
                                                    value="<?= htmlspecialchars($user['username']) ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre Completo *</label>
                                                <input type="text" name="full_name" class="form-control" required 
                                                    value="<?= htmlspecialchars($user['full_name']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Rol *</label>
                                                <select name="role" class="form-select" required>
                                                    <option value="auxiliar" <?= $user['role'] == 'auxiliar' ? 'selected' : '' ?>>Auxiliar</option>
                                                    <option value="asistente" <?= $user['role'] == 'asistente' ? 'selected' : '' ?>>Asistente</option>
                                                    <option value="analista" <?= $user['role'] == 'analista' ? 'selected' : '' ?>>Analista</option>
                                                    <option value="jefe" <?= $user['role'] == 'jefe' ? 'selected' : '' ?>>Jefe</option>
                                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Departamento</label>
                                                <input type="text" name="department" class="form-control" 
                                                    value="<?= htmlspecialchars($user['department']) ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="change_password" id="changePassword">
                                        <label class="form-check-label" for="changePassword">
                                            Cambiar contraseña
                                        </label>
                                    </div>
                                    
                                    <div id="passwordFields" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nueva Contraseña</label>
                                                    <input type="password" name="new_password" class="form-control">
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Confirmar Contraseña</label>
                                                    <input type="password" name="confirm_password" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                                        <a href="users.php" class="btn btn-secondary">Cancelar</a>
                                    </div>
                                </form>
                                
                                <hr>
                                
                                <div class="mt-3">
                                    <h6>Información Adicional</h6>
                                    <p class="text-muted small mb-1">
                                        <strong>Fecha de creación:</strong> 
                                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                    </p>
                                    <p class="text-muted small mb-1">
                                        <strong>Último acceso:</strong> 
                                        <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar campos de contraseña
        document.getElementById('changePassword').addEventListener('change', function() {
            const passwordFields = document.getElementById('passwordFields');
            const passwordInputs = passwordFields.querySelectorAll('input');
            
            if (this.checked) {
                passwordFields.style.display = 'block';
                passwordInputs.forEach(input => input.required = true);
            } else {
                passwordFields.style.display = 'none';
                passwordInputs.forEach(input => input.required = false);
            }
        });
    </script>
</body>
</html>