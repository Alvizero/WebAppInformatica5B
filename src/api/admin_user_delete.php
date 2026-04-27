<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

$pdo     = getPDO();
$user_id = (int)($_POST['user_id'] ?? 0);
$me      = currentUser();

if ($user_id <= 0) {
    setFlash('reset_error', '❌ ID utente non valido.');
    redirect('../pages/admin/admin.php');
}

if ($user_id === (int)$me['id']) {
    setFlash('reset_error', '❌ Non puoi eliminare te stesso.');
    redirect('../pages/admin/admin.php');
}

// Elimina in cascata (messaggi → ticket → viaggi → utente)
$pdo->prepare("DELETE FROM support_messages WHERE ticket_id IN (SELECT id FROM support_tickets WHERE user_id = :id)")->execute(['id' => $user_id]);
$pdo->prepare("DELETE FROM support_tickets WHERE user_id = :id")->execute(['id' => $user_id]);
$pdo->prepare("DELETE FROM viaggi WHERE user_id = :id")->execute(['id' => $user_id]);
$stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);

if ($stmt->rowCount() > 0) {
    setFlash('reset_msg', "✅ Utente #$user_id eliminato con successo.");
} else {
    setFlash('reset_error', '❌ Utente non trovato.');
}

redirect('../pages/admin/admin.php');
