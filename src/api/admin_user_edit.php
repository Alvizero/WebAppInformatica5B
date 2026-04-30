<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/lookup_helper.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
<<<<<<< Updated upstream
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
=======
    redirectMsg('../pages/admin/admin.php', '❌ ID utente non valido.', true);
}

$nome    = trim($_POST['nome'] ?? '');
$cognome = trim($_POST['cognome'] ?? '');
$email   = trim($_POST['email'] ?? '');
$naz_id  = (int)($_POST['nazionalita_id'] ?? 0);
$ling_id = (int)($_POST['lingua_id'] ?? 0);

if (!$nome || !$cognome || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectMsg('../pages/admin/admin.php', '❌ Dati non validi o email mancante.', true);
}
if ($naz_id <= 0 || !getNazionalitaNome($naz_id)) {
    redirectMsg('../pages/admin/admin.php', '❌ Nazionalità non valida.', true);
}
if ($ling_id <= 0 || !getLinguaNome($ling_id)) {
    redirectMsg('../pages/admin/admin.php', '❌ Lingua non valida.', true);
}

getPDO()->prepare("UPDATE users SET nome=:nome, cognome=:cognome, email=:email, nazionalita_id=:naz, lingua_id=:lingua WHERE id=:id")
    ->execute([
        'nome'    => $nome,
        'cognome' => $cognome,
        'email'   => $email,
        'naz'     => $naz_id,
        'lingua'  => $ling_id,
        'id'      => $user_id,
    ]);

redirectMsg('../pages/admin/admin.php', "✅ Dati dell'utente #$user_id aggiornati con successo.");
>>>>>>> Stashed changes
