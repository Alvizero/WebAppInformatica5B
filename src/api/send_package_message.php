<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

header('Content-Type: application/json');

$user = currentUser();
$pdo = getPDO();
$package_conversation_id = isset($_POST['package_conversation_id']) ? (int)$_POST['package_conversation_id'] : 0;
$message = trim($_POST['message'] ?? '');

if (!$package_conversation_id || !$message) {
    echo json_encode(['success' => false, 'error' => 'Dati mancanti']);
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
    echo json_encode(['success' => false, 'error' => 'Non autorizzato']);
    exit;
}

// Inserisci il messaggio
try {
    $stmt = $pdo->prepare("
        INSERT INTO package_messages (package_conversation_id, sender_id, message) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$package_conversation_id, $user['id'], $message]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
