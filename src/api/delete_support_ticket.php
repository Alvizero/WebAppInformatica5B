<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo = getPDO();
$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

if ($ticket_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID ticket non valido']);
    exit;
}

// Verifica che l'utente sia parte del ticket (admin o creatore)
$stmt = $pdo->prepare("SELECT user_id FROM support_tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket non trovato']);
    exit;
}

$user_id = (int)$user['id'];
$is_admin = isAdmin();
$is_owner = (int)$ticket['user_id'] === $user_id;

if (!$is_admin && !$is_owner) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Elimina i messaggi del ticket (ON DELETE CASCADE farà questo automaticamente, ma lo facciamo esplicitamente)
$pdo->prepare("DELETE FROM support_messages WHERE ticket_id = ?")->execute([$ticket_id]);

// Elimina il ticket
$pdo->prepare("DELETE FROM support_tickets WHERE id = ?")->execute([$ticket_id]);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Ticket eliminato']);
?>
