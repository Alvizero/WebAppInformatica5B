<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

requireAdmin();

$id = (int)($_POST['ticket_id'] ?? 0);
if ($id > 0) {
    $stmt = getPDO()->prepare("UPDATE support_tickets SET stato='chiuso' WHERE id=:id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() > 0) {
        setFlash('reset_msg', "✅ Ticket #$id chiuso con successo.");
    } else {
        setFlash('reset_error', '❌ Ticket non trovato o già chiuso.');
    }
} else {
    setFlash('reset_error', '❌ ID ticket non valido.');
}

redirect('../pages/admin/admin.php');

?>