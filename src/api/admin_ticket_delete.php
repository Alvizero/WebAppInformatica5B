<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

requireAdmin();

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
if ($ticket_id > 0) {
    $pdo = getPDO();
    $pdo->prepare("DELETE FROM support_messages WHERE ticket_id = :id")->execute(['id' => $ticket_id]);
    $stmt = $pdo->prepare("DELETE FROM support_tickets WHERE id = :id");
    $stmt->execute(['id' => $ticket_id]);
    if ($stmt->rowCount() > 0) {
        setFlash('reset_msg', "✅ Ticket #$ticket_id eliminato con successo.");
    } else {
        setFlash('reset_error', '❌ Ticket non trovato.');
    }
} else {
    setFlash('reset_error', '❌ ID ticket non valido.');
}

redirect('../pages/admin/admin.php');