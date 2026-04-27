<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

$user_id = (int)($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    setFlash('reset_error', '❌ ID utente non valido.');
    redirect('../pages/admin/admin.php');
}

$nome    = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$email   = trim($_POST['email'] ?? '');

if (!$nome || !$cognome || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('reset_error', '❌ Dati non validi o email mancante.');
    redirect('../pages/admin/admin.php');
}

getPDO()->prepare("UPDATE users SET nome=:nome, cognome=:cognome, email=:email, nazionalita=:naz, lingua=:lingua WHERE id=:id")
    ->execute([
        'nome'    => $nome,
        'cognome' => $cognome,
        'email'   => $email,
        'naz'     => trim($_POST['nazionalita'] ?? ''),
        'lingua'  => trim($_POST['lingua'] ?? ''),
        'id'      => $user_id,
    ]);

setFlash('reset_msg', "✅ Dati dell'utente #$user_id aggiornati con successo.");
redirect('../pages/admin/admin.php');
