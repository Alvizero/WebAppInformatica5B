<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$me      = currentUser();
$user_id = isset($_POST['user_id'])     ? (int)$_POST['user_id']     : 0;
$level   = isset($_POST['livello_utente']) ? (int)$_POST['livello_utente'] : -1;

<<<<<<< Updated upstream
if ($user_id <= 0) {
    $_SESSION['reset_error'] = '❌ ID utente non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

if ($user_id === (int)$me['id']) {
    $_SESSION['reset_error'] = '❌ Non puoi modificare il tuo stesso ruolo.';
    header('Location: ../pages/admin/admin.php');
    exit;
=======
if ($user_id <= 0 || $user_id === (int)$me['id']) {
    redirectMsg('../pages/admin/admin.php', '❌ Operazione non valida.', true);
}

if (!in_array($level, [0, 1, 2, 255], true)) {
    redirectMsg('../pages/admin/admin.php', '❌ Livello non valido.', true);
>>>>>>> Stashed changes
}

$valoriAmmessi = [0, 1, 2, 255];
if (!in_array($level, $valoriAmmessi, true)) {
    $_SESSION['reset_error'] = '❌ Livello non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

<<<<<<< Updated upstream
$stmt = $pdo->prepare("UPDATE users SET livello_utente = :level WHERE id = :id");
$stmt->execute(['level' => $level, 'id' => $user_id]);

$labels = [0 => 'Super Admin', 1 => 'Admin', 2 => 'Moderatore', 255 => 'Utente'];
$_SESSION['reset_msg'] = "✅ Ruolo dell'utente #$user_id aggiornato a <strong>{$labels[$level]}</strong>.";
header('Location: ../pages/admin/admin.php');
exit;
=======
$label = adminLevelLabel($level);
redirectMsg('../pages/admin/admin.php', "✅ Ruolo dell'utente #$user_id aggiornato a $label.");
>>>>>>> Stashed changes
