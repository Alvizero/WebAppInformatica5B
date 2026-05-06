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

$conversation_id = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($conversation_id === 0 || empty($message)) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Verifica che l'utente appartenga alla conversazione
$stmt = $pdo->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->execute([$conversation_id, $user['id'], $user['id']]);
if (!$stmt->fetch()) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)");
$success = $stmt->execute([$conversation_id, $user['id'], $message]);

echo json_encode(['success' => $success]);
