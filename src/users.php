<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Solo jefe y admin pueden gestionar usuarios
requireRole('jefe');

// Paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Obtener usuarios
try {
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY full_name LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Total de usuarios
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);
    
} catch (PDOException $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
    $users = [];
    $totalUsers = 0;
    $totalPages = 1;
}

// Procesar eliminación de usuario
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    
    // No permitir eliminarse a sí mismo
    if ($deleteId != $_SESSION['user_id']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);
            header('Location: users.php?success=user_deleted');
            exit;
        } catch (PDOException $e) {
            $error = "Error al eliminar usuario: " . $e->getMessage();
        }
    } else {
        $error = "No puedes eliminarte a ti mismo";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Hotel Ikin</title>
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
                    <h1 class="h2">Gestión de Usuarios</h1>
                    <a href="create_user.php" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Nuevo Usuario
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <?php
                        switch ($_GET['success']) {
                            case 'user_deleted': echo "Usuario eliminado correctamente"; break;
                            case 'user_created': echo "Usuario creado correctamente"; break;
                            case 'user_updated': echo "Usuario actualizado correctamente"; break;
                        }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Lista de Usuarios (<?= $totalUsers ?> total)</h6>
                    </div>
                    
                    <div class="card-body">
                        <?php if ($users): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Departamento</th>
                                            <th>Fecha Creación</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= getRoleBadge($user['role']) ?></td>
                                                <td><?= htmlspecialchars($user['department']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="delete_confirmation.php?type=user&id=<?= $user['id'] ?>" 
                                                        class="btn btn-outline-danger"
                                                        onclick="confirmDelete(this, 'user', <?= $user['id'] ?>, '<?= addslashes($user['full_name']) ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación -->
                            <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-people display-4 text-muted"></i>
                                <p class="text-muted mt-3">No hay usuarios registrados</p>
                                <a href="create_user.php" class="btn btn-primary">Crear primer usuario</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>