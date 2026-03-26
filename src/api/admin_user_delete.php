<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$me      = currentUser();

// Protezioni
if ($user_id <= 0) {
    $_SESSION['reset_error'] = '❌ ID utente non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

if ($user_id === (int)$me['id']) {
    $_SESSION['reset_error'] = '❌ Non puoi eliminare te stesso.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

// Verifica che l'utente esista
$check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
$check->execute(['id' => $user_id]);
if (!$check->fetch()) {
    $_SESSION['reset_error'] = '❌ Utente non trovato.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

// Elimina in cascata (messaggi → ticket → viaggi → utente)
$pdo->prepare("DELETE FROM support_messages WHERE ticket_id IN (SELECT id FROM support_tickets WHERE user_id = :id)")->execute(['id' => $user_id]);
$pdo->prepare("DELETE FROM support_tickets WHERE user_id = :id")->execute(['id' => $user_id]);
$pdo->prepare("DELETE FROM viaggi WHERE user_id = :id")->execute(['id' => $user_id]);
$pdo->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $user_id]);

$_SESSION['reset_msg'] = "✅ Utente #$user_id eliminato con successo.";
header('Location: ../pages/admin/admin.php');
exit;
