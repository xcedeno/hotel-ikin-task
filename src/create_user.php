<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo jefe y admin pueden crear usuarios
requireRole('jefe');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $department = trim($_POST['department']);
    
    // Validaciones
    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio";
    }
    
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    if (empty($full_name)) {
        $errors[] = "El nombre completo es obligatorio";
    }
    
    if (!in_array($role, ['auxiliar', 'asistente', 'analista', 'jefe', 'admin'])) {
        $errors[] = "Rol inválido";
    }
    
    // Verificar si el usuario ya existe
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $errors[] = "El nombre de usuario ya existe";
            }
        } catch (PDOException $e) {
            $errors[] = "Error al verificar usuario: " . $e->getMessage();
        }
    }
    
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, full_name, role, department) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $username,
                $hashedPassword,
                $full_name,
                $role,
                $department
            ]);
            
            header('Location: users.php?success=user_created');
            exit;
            
        } catch (PDOException $e) {
            $errors[] = "Error al crear usuario: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - Hotel Ikin</title>
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
                    <h1 class="h2">Crear Nuevo Usuario</h1>
                    <a href="users.php" class="btn btn-secondary">Volver</a>
                </div>

                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información del Usuario</h6>
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
                                                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre Completo *</label>
                                                <input type="text" name="full_name" class="form-control" required 
                                                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Contraseña *</label>
                                                <input type="password" name="password" class="form-control" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Confirmar Contraseña *</label>
                                                <input type="password" name="confirm_password" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Rol *</label>
                                                <select name="role" class="form-select" required>
                                                    <option value="auxiliar" <?= ($_POST['role'] ?? '') == 'auxiliar' ? 'selected' : '' ?>>Auxiliar</option>
                                                    <option value="asistente" <?= ($_POST['role'] ?? '') == 'asistente' ? 'selected' : '' ?>>Asistente</option>
                                                    <option value="analista" <?= ($_POST['role'] ?? '') == 'analista' ? 'selected' : '' ?>>Analista</option>
                                                    <option value="jefe" <?= ($_POST['role'] ?? '') == 'jefe' ? 'selected' : '' ?>>Jefe</option>
                                                    <option value="admin" <?= ($_POST['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Departamento</label>
                                                <input type="text" name="department" class="form-control" 
                                                    value="<?= htmlspecialchars($_POST['department'] ?? 'Tecnología') ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">Crear Usuario</button>
                                        <a href="users.php" class="btn btn-secondary">Cancelar</a>
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