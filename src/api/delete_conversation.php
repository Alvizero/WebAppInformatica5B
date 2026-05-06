<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo = getPDO();
$conversation_id = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;

if ($conversation_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID conversazione non valido']);
    exit;
}

// Verifica che l'utente sia parte della conversazione
$stmt = $pdo->prepare("SELECT user1_id, user2_id FROM conversations WHERE id = ?");
$stmt->execute([$conversation_id]);
$conv = $stmt->fetch();

if (!$conv) {
    http_response_code(404);
    echo json_encode(['error' => 'Conversazione non trovata']);
    exit;
}

$user_id = (int)$user['id'];
if ((int)$conv['user1_id'] !== $user_id && (int)$conv['user2_id'] !== $user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Elimina i messaggi della conversazione
$pdo->prepare("DELETE FROM messages WHERE conversation_id = ?")->execute([$conversation_id]);

// Elimina la conversazione
$pdo->prepare("DELETE FROM conversations WHERE id = ?")->execute([$conversation_id]);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Conversazione eliminata']);
?>
