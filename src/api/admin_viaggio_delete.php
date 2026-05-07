<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

requireAdmin();

$id = (int)($_POST['viaggio_id'] ?? 0);
if ($id > 0) {
    $stmt = getPDO()->prepare("DELETE FROM viaggi WHERE id=:id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() > 0) {
        setFlash('reset_msg', "✅ Viaggio #$id eliminato con successo.");
    } else {
        setFlash('reset_error', '❌ Viaggio non trovato.');
    }
} else {
    setFlash('reset_error', '❌ ID viaggio non valido.');
}

redirect('../pages/admin/admin.php');
