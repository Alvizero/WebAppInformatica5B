<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

$user = currentUser();
$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    getPDO()
        ->prepare("DELETE FROM viaggi WHERE id = :id AND user_id = :uid")
        ->execute(['id' => $id, 'uid' => $user['id']]);
}

header('Location: ./../dashboard/dashboard.php');
exit;
