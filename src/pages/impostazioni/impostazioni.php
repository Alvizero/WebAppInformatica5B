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

    if ($oldPass === '' || $newPass === '')  $errors[] = 'Compila tutti i campi.';
    if (strlen($newPass) < 8)               $errors[] = 'La nuova password deve avere almeno 8 caratteri.';
    if ($newPass !== $newPass2)             $errors[] = 'Le nuove password non coincidono.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute([':id' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($oldPass, $row['password'])) {
            $errors[] = 'La password attuale non è corretta.';
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $upd  = $pdo->prepare('UPDATE users SET password = :p WHERE id = :id');
            $upd->execute([':p' => $hash, ':id' => $user['id']]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impostazioni – VacanzaMatch</title>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="impostazioni.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <div class="card impostazioni-card">
    <h2>Impostazioni account</h2>
    <p>Modifica la tua password di accesso.</p>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ Password aggiornata correttamente.</div>
    <?php endif; ?>

    <form method="POST" class="form-grid">
      <div class="full">
        <label>Password attuale *</label>
        <input type="password" name="password_attuale" required>
      </div>
      <div>
        <label>Nuova password *</label>
        <input type="password" name="password_nuova" required minlength="8">
      </div>
      <div>
        <label>Ripeti nuova password *</label>
        <input type="password" name="password_repeat" required minlength="8">
      </div>
      <div class="full">
        <button type="submit" class="btn-submit">Aggiorna password</button>
      </div>
    </form>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
