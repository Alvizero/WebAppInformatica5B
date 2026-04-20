<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo  = getPDO();

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass  = $_POST['password_attuale'] ?? '';
    $newPass  = $_POST['password_nuova']   ?? '';
    $newPass2 = $_POST['password_repeat']  ?? '';

    if ($oldPass === '' || $newPass === '')  $errors[] = 'Compila tutti i campi obbligatori.';
    if (strlen($newPass) < 8)               $errors[] = 'La nuova password deve contenere almeno 8 caratteri.';
    if ($newPass !== $newPass2)             $errors[] = 'Le due password non coincidono. Riprova.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute([':id' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($oldPass, $row['password'])) {
            $errors[] = 'La password attuale inserita non è corretta.';
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $upd  = $pdo->prepare('UPDATE users SET password = :p WHERE id = :id');
            $upd->execute([':p' => $hash, ':id' => $user['id']]);
            header('Location: impostazioni.php?success_msg=' . urlencode('Password modificata con successo!'));
            exit;
        }
    }
}

// Dati utente per la sidebar
$stmt2 = $pdo->prepare('SELECT nome, cognome, email FROM users WHERE id = ?');
$stmt2->execute([$user['id']]);
$dbUser = $stmt2->fetch(PDO::FETCH_ASSOC);
$initials = strtoupper(mb_substr($dbUser['nome'],0,1) . mb_substr($dbUser['cognome'],0,1));
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sicurezza account — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="impostazioni.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container settings-page">
  <div class="settings-layout">

    <!-- Sidebar -->
    <div class="settings-sidebar">
      <div class="settings-sidebar-profile">
        <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
        <div class="user-name"><?= htmlspecialchars($dbUser['nome'] . ' ' . $dbUser['cognome']) ?></div>
        <div class="user-email"><?= htmlspecialchars($dbUser['email']) ?></div>
      </div>
      <nav class="settings-nav">
        <a href="./../profilo/profilo.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          Profilo
        </a>
        <a href="impostazioni.php" class="active">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Sicurezza
        </a>

        <a href="./../supporto/supporto.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Supporto
        </a>
      </nav>
    </div>

    <!-- Form -->
    <div class="settings-card">
      <div class="settings-card-header">
        <h2>Sicurezza account</h2>
        <p>Aggiorna la tua password per mantenere il tuo account al sicuro. Usa una password forte e unica.</p>
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
          Password aggiornata correttamente. Ricordati di usare la nuova password al prossimo accesso.
        </div>
      <?php endif; ?>

      <div class="security-tip">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="flex-shrink:0;margin-top:.1rem"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Usa almeno 8 caratteri, combinando lettere maiuscole, minuscole, numeri e simboli per una password sicura.</span>
      </div>

      <form method="POST" class="form-grid">
        <div class="full">
          <div class="input-wrap">
            <label>Password attuale *</label>
            <input type="password" name="password_attuale" required placeholder="La tua password attuale">
          </div>
        </div>
        <div>
          <div class="input-wrap">
            <label>Nuova password *</label>
            <input type="password" name="password_nuova" id="new-pass" required minlength="8" placeholder="Min. 8 caratteri" oninput="checkStrength(this.value)">
            <div class="password-strength"><div class="password-strength-bar" id="strength-bar"></div></div>
          </div>
        </div>
        <div>
          <div class="input-wrap">
            <label>Conferma nuova password *</label>
            <input type="password" name="password_repeat" required minlength="8" placeholder="Ripeti la nuova password">
          </div>
        </div>
        <div class="full">
          <button type="submit" class="btn btn-primary btn-full">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Aggiorna password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function checkStrength(val) {
    const bar = document.getElementById('strength-bar');
    if (!bar) return;
    bar.className = 'password-strength-bar';
    if (val.length === 0) { bar.style.width = '0'; return; }
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    if (score <= 1) bar.classList.add('strength-weak');
    else if (score <= 2) bar.classList.add('strength-medium');
    else bar.classList.add('strength-strong');
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
