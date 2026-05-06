<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = currentUser();
$pdo = getPDO();

$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($ticket_id === 0 || empty($message)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Verifica che l'utente sia il proprietario del ticket o un admin
$stmt = $pdo->prepare("SELECT stato FROM support_tickets WHERE id = ? AND (user_id = ? OR ? >= 255)");
$stmt->execute([$ticket_id, $user['id'], $user['livello_utente']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

if ($ticket['stato'] === 'chiuso') {
    echo json_encode(['error' => 'Ticket closed']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (?, ?, ?)");
$success = $stmt->execute([$ticket_id, $user['id'], $message]);

// Se l'utente è un admin, aggiorna lo stato del ticket a 'risposto'
if ($user['livello_utente'] >= 255) {
    $pdo->prepare("UPDATE support_tickets SET stato = 'risposto' WHERE id = ?")->execute([$ticket_id]);
}

echo json_encode(['success' => $success]);
