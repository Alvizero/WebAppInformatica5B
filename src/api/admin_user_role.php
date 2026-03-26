<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$me      = currentUser();
$user_id = isset($_POST['user_id'])     ? (int)$_POST['user_id']     : 0;
$level   = isset($_POST['admin_level']) ? (int)$_POST['admin_level'] : -1;

if ($user_id <= 0) {
    $_SESSION['reset_error'] = '❌ ID utente non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

if ($user_id === (int)$me['id']) {
    $_SESSION['reset_error'] = '❌ Non puoi modificare il tuo stesso ruolo.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$valoriAmmessi = [0, 1, 2, 255];
if (!in_array($level, $valoriAmmessi, true)) {
    $_SESSION['reset_error'] = '❌ Livello non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET admin_level = :level WHERE id = :id");
$stmt->execute(['level' => $level, 'id' => $user_id]);

$labels = [0 => 'Super Admin', 1 => 'Admin', 2 => 'Moderatore', 255 => 'Utente'];
$_SESSION['reset_msg'] = "✅ Ruolo dell'utente #$user_id aggiornato a <strong>{$labels[$level]}</strong>.";
header('Location: ../pages/admin/admin.php');
exit;
