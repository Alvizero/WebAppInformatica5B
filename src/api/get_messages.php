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
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($conversation_id === 0) {
    echo json_encode(['error' => 'Invalid conversation ID']);
    exit;
}

// Verifica che l'utente appartenga alla conversazione
$stmt = $pdo->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->execute([$conversation_id, $user['id'], $user['id']]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, u.nome
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.conversation_id = ? AND m.id > ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversation_id, $last_id]);
$messages = $stmt->fetchAll();

echo json_encode(['messages' => $messages]);
