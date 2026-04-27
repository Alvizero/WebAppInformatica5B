<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

requireAdmin();

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
if ($ticket_id > 0) {
    getPDO()->prepare("DELETE FROM support_tickets WHERE id = :id")->execute(['id' => $ticket_id]);
}

redirect('../pages/admin/admin.php');
