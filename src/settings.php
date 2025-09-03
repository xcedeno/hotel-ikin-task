<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo jefe y admin pueden acceder a configuración
requireRole('jefe');

$success = false;
$error = '';

// Procesar cambio de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí se procesarían los cambios de configuración
    // Por ahora es un ejemplo básico
    
    $success = true;
    $message = "Configuración actualizada correctamente";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Hotel Ikin</title>
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
                    <h1 class="h2">Configuración del Sistema</h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Configuración General</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nombre del Hotel</label>
                                        <input type="text" class="form-control" value="Hotel Ikin" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Departamento</label>
                                        <input type="text" class="form-control" value="Tecnología" disabled>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Tiempo de sesión (minutos)</label>
                                        <input type="number" name="session_timeout" class="form-control" value="30" min="5" max="120">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Información del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Versión PHP:</strong> <?= phpversion() ?>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Base de Datos:</strong> MySQL
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Servidor Web:</strong> Apache
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Usuarios Registrados:</strong> 
                                    <?php 
                                    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                                    echo $userCount;
                                    ?>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Tareas Totales:</strong> 
                                    <?php 
                                    $taskCount = $pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
                                    echo $taskCount;
                                    ?>
                                </div>
                                
                                <hr>
                                
                                <div class="text-center">
                                    <a href="backup.php" class="btn btn-outline-primary">
                                        <i class="bi bi-download"></i> Crear Backup
                                    </a>
                                    
                                    <a href="logs.php" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-list-check"></i> Ver Logs
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración de Notificaciones -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Configuración de Notificaciones</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                        <label class="form-check-label" for="emailNotifications">
                                            Notificaciones por Email
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="taskAssigned" checked>
                                        <label class="form-check-label" for="taskAssigned">
                                            Notificar cuando se asigne tarea
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="taskCompleted" checked>
                                        <label class="form-check-label" for="taskCompleted">
                                            Notificar cuando se complete tarea
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="urgentTasks" checked>
                                        <label class="form-check-label" for="urgentTasks">
                                            Alertas de tareas urgentes
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
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