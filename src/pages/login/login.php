<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
startSession();

if (isLoggedIn()) {
  header('Location: ./../dashboard/dashboard.php');
  exit;
}

$error    = '';
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? './../dashboard/dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = trim($_POST['email']    ?? '');
  $password = $_POST['password']      ?? '';

  if ($email && $password) {
    $pdo  = getPDO();
    // JOIN con nazionalita per avere il nome in sessione
    $stmt = $pdo->prepare('
        SELECT u.*, n.nome as nazionalita_nome 
        FROM users u 
        LEFT JOIN nazionalita n ON u.nazionalita_id = n.id 
        WHERE u.email = :email
    ');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
      // Prepariamo l'array per loginUser includendo il nome della nazionalità
      $userData = $user;
      $userData['nazionalita'] = $user['nazionalita_nome'] ?? '';
      loginUser($userData);
      header('Location: ' . $redirect);
      exit;
    }
  }
  $error = 'Email o password errati.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accedi — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="../../shared/auth.css">
  <link rel="stylesheet" href="login.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="auth-page">
  <!-- Visual side -->
  <div class="auth-visual">
    <div class="auth-visual-inner">
      <span class="auth-visual-deco">🌍</span>
      <h2>Bentornato<br>viaggiatore</h2>
      <p>Il mondo aspetta di essere<br>esplorato in buona compagnia.</p>
    </div>
  </div>

  <!-- Form side -->
  <div class="auth-form-side">
    <div class="auth-box">
      <h2>Accedi</h2>
      <p class="auth-sub">Inserisci le tue credenziali per continuare</p>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <div class="input-wrap">
          <label>Email</label>
          <input type="email" name="email" required placeholder="nome@esempio.it" autocomplete="email">
        </div>
        <div class="input-wrap">
          <label>Password</label>
          <input type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
          Accedi →
        </button>
      </form>

      <p class="auth-footer">
        Non hai un account? <a href="./../register/register.php">Registrati gratis</a>
      </p>
    </div>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
