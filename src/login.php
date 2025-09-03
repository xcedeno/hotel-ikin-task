<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4 class="mb-0">Hotel Ikin Technology</h4>
                        <small>Sistema de Gestión de Tareas</small>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small>Credenciales de prueba: admin / password</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>