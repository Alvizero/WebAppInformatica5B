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
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
      loginUser($user);
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
  <title>VacanzaMatch – Accedi</title>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="login.css">
</head>

<body>
  <?php include __DIR__ . '/../../shared/navbar.php'; ?>

  <div class="container">
    <div class="card login-card">
      <h2>Accedi</h2>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-submit">Accedi</button>
      </form>

      <p class="login-footer">
        Non hai un account? <a href="./../register/register.php">Registrati</a>
      </p>
    </div>
  </div>

  <script src="../../shared/app.js"></script>
</body>

</html>