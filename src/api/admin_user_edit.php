<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
    $_SESSION['reset_error'] = '❌ ID utente non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$nome        = trim($_POST['nome']        ?? '');
$cognome     = trim($_POST['cognome']     ?? '');
$email       = trim($_POST['email']       ?? '');
$nazionalita = trim($_POST['nazionalita'] ?? '');
$lingua      = trim($_POST['lingua']      ?? '');

if (!$nome || !$cognome || !$email) {
    $_SESSION['reset_error'] = '❌ Nome, cognome ed email sono obbligatori.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['reset_error'] = '❌ Email non valida.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET nome=:nome, cognome=:cognome, email=:email, nazionalita=:naz, lingua=:lingua WHERE id=:id");
$stmt->execute([
    'nome'    => $nome,
    'cognome' => $cognome,
    'email'   => $email,
    'naz'     => $nazionalita,
    'lingua'  => $lingua,
    'id'      => $user_id,
]);

$_SESSION['reset_msg'] = "✅ Dati dell'utente #$user_id aggiornati con successo.";
header('Location: ../pages/admin/admin.php');
exit;
