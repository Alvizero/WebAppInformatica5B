<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    getPDO()->prepare("DELETE FROM viaggi WHERE id = :id AND user_id = :uid")
        ->execute(['id' => $id, 'uid' => currentUser()['id']]);
}

redirect('../pages/dashboard/dashboard.php', 'Viaggio eliminato con successo!');
