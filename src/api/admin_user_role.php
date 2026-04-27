<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

$me      = currentUser();
$user_id = (int)($_POST['user_id'] ?? 0);
$level   = (int)($_POST['livello_utente'] ?? -1);

if ($user_id <= 0 || $user_id === (int)$me['id']) {
    setFlash('reset_error', '❌ Operazione non valida.');
    redirect('../pages/admin/admin.php');
}

if (!in_array($level, [0, 1, 2, 255], true)) {
    setFlash('reset_error', '❌ Livello non valido.');
    redirect('../pages/admin/admin.php');
}

getPDO()->prepare("UPDATE users SET livello_utente = :level WHERE id = :id")
    ->execute(['level' => $level, 'id' => $user_id]);

$label = adminLevelLabel($level);
setFlash('reset_msg', "✅ Ruolo dell'utente #$user_id aggiornato a <strong>$label</strong>.");
redirect('../pages/admin/admin.php');
