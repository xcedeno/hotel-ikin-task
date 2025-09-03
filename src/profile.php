<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

$success = false;
$error = '';

// Obtener datos del usuario actual
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    error_log("Error al obtener perfil: " . $e->getMessage());
    $error = "Error al cargar perfil";
}

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($full_name)) {
        $error = "El nombre completo es obligatorio";
    }
    
    if ($new_password && $new_password !== $confirm_password) {
        $error = "Las nuevas contraseñas no coinciden";
    }
    
    if ($new_password && !password_verify($current_password, $user['password'])) {
        $error = "La contraseña actual es incorrecta";
    }
    
    if (empty($error)) {
        try {
            if ($new_password) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $hashedPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
                $stmt->execute([$full_name, $_SESSION['user_id']]);
            }
            
            $_SESSION['full_name'] = $full_name;
            $success = true;
            
        } catch (PDOException $e) {
            $error = "Error al actualizar perfil: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Hotel Ikin</title>
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
                    <h1 class="h2">Mi Perfil</h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">Perfil actualizado correctamente</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información Personal</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre de Usuario</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nombre Completo *</label>
                                        <input type="text" name="full_name" class="form-control" required 
                                            value="<?= htmlspecialchars($user['full_name']) ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Departamento</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['department']) ?>" disabled>
                                    </div>
                                    
                                    <hr>
                                    
                                    <h6 class="mb-3">Cambiar Contraseña</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Contraseña Actual</label>
                                        <input type="password" name="current_password" class="form-control">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nueva Contraseña</label>
                                        <input type="password" name="new_password" class="form-control">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Confirmar Nueva Contraseña</label>
                                        <input type="password" name="confirm_password" class="form-control">
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
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