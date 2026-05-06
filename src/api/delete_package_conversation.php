<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo = getPDO();
$package_conversation_id = isset($_POST['package_conversation_id']) ? (int)$_POST['package_conversation_id'] : 0;

if ($package_conversation_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID conversazione non valido']);
    exit;
}

// Verifica che l'utente sia parte della conversazione (utente o agenzia)
$stmt = $pdo->prepare("SELECT user_id, agenzia_user_id FROM package_conversations WHERE id = ?");
$stmt->execute([$package_conversation_id]);
$conv = $stmt->fetch();

if (!$conv) {
    http_response_code(404);
    echo json_encode(['error' => 'Conversazione non trovata']);
    exit;
}

$user_id = (int)$user['id'];
if ((int)$conv['user_id'] !== $user_id && (int)$conv['agenzia_user_id'] !== $user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorizzato']);
    exit;
}

// Elimina i messaggi della conversazione
$pdo->prepare("DELETE FROM package_messages WHERE package_conversation_id = ?")->execute([$package_conversation_id]);

// Elimina la conversazione
$pdo->prepare("DELETE FROM package_conversations WHERE id = ?")->execute([$package_conversation_id]);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Conversazione eliminata']);
?>
