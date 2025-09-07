<?php
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ticket_id === 0) {
    header('Location: index.php?error=invalid_id');
    exit;
}

// Obtener datos del ticket
try {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name,
            u1.full_name as assigned_name, 
            u2.full_name as created_name
        FROM support_tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        LEFT JOIN users u1 ON t.assigned_to = u1.id 
        LEFT JOIN users u2 ON t.created_by = u2.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        header('Location: index.php?error=ticket_not_found');
        exit;
    }
    
    // Verificar permisos
    if (!canViewTicket($ticket, $_SESSION['user_id'])) {
        header('Location: index.php?error=no_permission');
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error al obtener ticket: " . $e->getMessage());
    header('Location: index.php?error=db_error');
    exit;
}

// Procesar envío de mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    
    if (!empty($message) && canSendMessage($ticket, $_SESSION['user_id'])) {
        try {
            $isTechnical = in_array($_SESSION['user_role'], ['admin', 'jefe', 'analista', 'asistente', 'auxiliar']);
            
            $stmt = $pdo->prepare("
                INSERT INTO ticket_messages (ticket_id, user_id, message, is_technical)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $ticket_id,
                $_SESSION['user_id'],
                $message,
                $isTechnical
            ]);
            
            // Actualizar timestamp del ticket
            $pdo->prepare("UPDATE support_tickets SET updated_at = NOW() WHERE id = ?")
                ->execute([$ticket_id]);
            
            header("Location: chat.php?id=$ticket_id");
            exit;
            
        } catch (PDOException $e) {
            $error = "Error al enviar mensaje: " . $e->getMessage();
        }
    }
}

// Obtener mensajes del chat
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.full_name, u.role
        FROM ticket_messages m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.ticket_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$ticket_id]);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al obtener mensajes: " . $e->getMessage());
    $messages = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat del Ticket - Hotel Ikin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        .chat-container {
            max-height: 500px;
            overflow-y: auto;
        }
        .message-technical {
            background-color: #e3f2fd;
            border-left: 4px solid #0d6efd;
        }
        .message-user {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
        }
        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Chat del Ticket: <?= safeOutput($ticket['ticket_number']) ?></h1>
                    <div>
                        <a href="view.php?id=<?= $ticket_id ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Volver al Ticket
                        </a>
                    </div>
                </div>

                <!-- Información del Ticket -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5><?= safeOutput($ticket['title']) ?></h5>
                        <div class="d-flex flex-wrap gap-3">
                            <span class="badge bg-primary"><?= safeOutput($ticket['department_name']) ?></span>
                            <?= getPriorityBadge($ticket['priority']) ?>
                            <?= getTicketStatusBadge($ticket['status']) ?>
                            <span class="text-muted">Creado por: <?= safeOutput($ticket['created_name']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Chat -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Comunicación del Ticket</h6>
                    </div>
                    <div class="card-body">
                        <div class="chat-container mb-3">
                            <?php if ($messages): ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="card mb-2 <?= $message['is_technical'] ? 'message-technical' : 'message-user' ?>">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong><?= safeOutput($message['full_name']) ?></strong>
                                                    <small class="text-muted">(<?= getFriendlyRole($message['role']) ?>)</small>
                                                    <?php if ($message['is_technical']): ?>
                                                        <span class="badge bg-info ms-2">Soporte</span>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="message-time">
                                                    <?= date('d/m/Y H:i', strtotime($message['created_at'])) ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 mt-2"><?= nl2br(safeOutput($message['message'])) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-chat-dots display-4 text-muted"></i>
                                    <p class="text-muted mt-2">No hay mensajes aún</p>
                                    <small>Sé el primero en enviar un mensaje</small>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Formulario de mensaje -->
                        <?php if (canSendMessage($ticket, $_SESSION['user_id'])): ?>
                        <form method="POST">
                            <div class="input-group">
                                <textarea name="message" class="form-control" placeholder="Escribe tu mensaje..." rows="2" required></textarea>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Enviar
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No tienes permisos para enviar mensajes en este ticket.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-scroll al final del chat
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</body>
</html>