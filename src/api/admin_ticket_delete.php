<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

requireAdmin(); // Solo admin possono cancellare

$ticket_id = (int)($_POST['ticket_id'] ?? 0);

if ($ticket_id > 0) {
    // La cancellazione del ticket eliminerà a cascata anche i messaggi grazie alla Foreign Key
    getPDO()->prepare("DELETE FROM support_tickets WHERE id = :id")->execute(['id' => $ticket_id]);
}

header('Location: ../pages/admin/admin.php');
exit;
