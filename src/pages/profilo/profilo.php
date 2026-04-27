<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';

requireLogin();
$user = currentUser();
$pdo  = getPDO();

$stmt = $pdo->prepare('SELECT nome, cognome, email, nazionalita, lingua FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$dbUser) die('Errore: utente non trovato.');

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']        ?? '');
    $cognome     = trim($_POST['cognome']     ?? '');
    $email       = trim($_POST['email']       ?? '');
    $nazionalita = trim($_POST['nazionalita'] ?? '');
    $lingua      = trim($_POST['lingua']      ?? '');

    if (empty($nome))                               $errors[] = 'Il nome è obbligatorio.';
    if (empty($cognome))                            $errors[] = 'Il cognome è obbligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Inserisci un indirizzo email valido.';
    if (empty($nazionalita))                        $errors[] = 'La nazionalità è obbligatoria.';
    if (empty($lingua))                             $errors[] = 'La lingua principale è obbligatoria.';

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $chk->execute([$email, $user['id']]);
        if ($chk->fetch()) {
            $errors[] = 'Questo indirizzo email è già in uso da un altro account.';
        } else {
            $upd = $pdo->prepare('UPDATE users SET nome=?, cognome=?, email=?, nazionalita=?, lingua=? WHERE id=?');
            $upd->execute([$nome, $cognome, $email, $nazionalita, $lingua, $user['id']]);
            $_SESSION['user_nome']    = $nome;
            $_SESSION['user_cognome'] = $cognome;
            $_SESSION['user_naz']     = $nazionalita;
            $_SESSION['user_lingua']  = $lingua;
            header('Location: profilo.php?success_msg=' . urlencode('Profilo aggiornato con successo!'));
            exit;
        }
    }
}

$initials = strtoupper(mb_substr($dbUser['nome'],0,1) . mb_substr($dbUser['cognome'],0,1));
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Il mio profilo — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="profilo.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container settings-page">
  <div class="settings-layout">

    <?php $activePage = 'profilo'; include __DIR__ . '/../../shared/settings_sidebar.php'; ?>

    <!-- Form -->
    <div class="settings-card">
      <div class="settings-card-header">
        <h2>Il tuo profilo</h2>
        <p>Aggiorna le informazioni personali del tuo account. Le modifiche saranno visibili agli altri utenti.</p>
      </div>

      <?php foreach ($errors as $e): ?>
        <div class="alert alert-error">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Profilo aggiornato con successo!
        </div>
      <?php endif; ?>

      <form method="POST" class="form-grid">
        <div>
          <div class="input-wrap">
            <label>Nome *</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($dbUser['nome'] ?? '') ?>" placeholder="Il tuo nome">
          </div>
        </div>
        <div>
          <div class="input-wrap">
            <label>Cognome *</label>
            <input type="text" name="cognome" required value="<?= htmlspecialchars($dbUser['cognome'] ?? '') ?>" placeholder="Il tuo cognome">
          </div>
        </div>
        <div class="full">
          <div class="input-wrap">
            <label>Indirizzo email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($dbUser['email'] ?? '') ?>" placeholder="nome@esempio.com">
          </div>
        </div>
        <div>
          <div class="input-wrap">
            <label>Nazionalità *</label>
            <input type="text" name="nazionalita" required value="<?= htmlspecialchars($dbUser['nazionalita'] ?? '') ?>" placeholder="es. Italiana">
          </div>
        </div>
        <div>
          <div class="input-wrap">
            <label>Lingua principale *</label>
            <input type="text" name="lingua" required value="<?= htmlspecialchars($dbUser['lingua'] ?? '') ?>" placeholder="es. Italiano">
          </div>
        </div>
        <div class="full">
          <button type="submit" class="btn btn-primary btn-full">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Salva modifiche
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
