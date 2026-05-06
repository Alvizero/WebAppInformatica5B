<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

header('Content-Type: application/json');

$user = currentUser();
$pdo = getPDO();
$package_conversation_id = isset($_GET['package_conversation_id']) ? (int)$_GET['package_conversation_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if (!$package_conversation_id) {
    echo json_encode(['messages' => []]);
    exit;
}

// Verifica che l'utente appartenga a questa conversazione
$stmt = $pdo->prepare("
    SELECT id FROM package_conversations 
    WHERE id = ? AND (user_id = ? OR agenzia_user_id = ?)
");
$stmt->execute([$package_conversation_id, $user['id'], $user['id']]);
$conv = $stmt->fetch();

if (!$conv) {
    echo json_encode(['messages' => []]);
    exit;
}

// Recupera i messaggi successivi
$stmt = $pdo->prepare("
    SELECT pm.id, pm.sender_id, pm.message, pm.created_at, u.nome
    FROM package_messages pm
    JOIN users u ON u.id = pm.sender_id
    WHERE pm.package_conversation_id = ? AND pm.id > ?
    ORDER BY pm.created_at ASC
");
$stmt->execute([$package_conversation_id, $last_id]);
$messages = $stmt->fetchAll();

echo json_encode(['messages' => $messages]);
?>
