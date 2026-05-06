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
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($ticket_id === 0) {
    echo json_encode(['error' => 'Invalid ticket ID']);
    exit;
}

// Verifica che l'utente sia il proprietario del ticket o un admin
$stmt = $pdo->prepare("SELECT id FROM support_tickets WHERE id = ? AND (user_id = ? OR ? >= 255)");
$stmt->execute([$ticket_id, $user['id'], $user['livello_utente']]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, u.nome, u.livello_utente
    FROM support_messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.ticket_id = ? AND m.id > ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$ticket_id, $last_id]);
$messages = $stmt->fetchAll();

echo json_encode(['messages' => $messages]);
