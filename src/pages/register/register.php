<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
startSession();

if (isLoggedIn()) {
    header('Location: ./../dashboard/dashboard.php');
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']        ?? '');
    $cognome     = trim($_POST['cognome']     ?? '');
    $email       = trim($_POST['email']       ?? '');
    $password    = $_POST['password']         ?? '';
    $password2   = $_POST['password2']        ?? '';
    $nazionalita = trim($_POST['nazionalita'] ?? '');
    $lingua      = trim($_POST['lingua']      ?? '');

    if (empty($nome))                                       $errors[] = 'Nome obbligatorio.';
    if (empty($cognome))                                    $errors[] = 'Cognome obbligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Email non valida.';
    if (strlen($password) < 8)                              $errors[] = 'Password minimo 8 caratteri.';
    if ($password !== $password2)                           $errors[] = 'Le password non coincidono.';
    if (empty($nazionalita))                                $errors[] = 'Nazionalità obbligatoria.';
    if (empty($lingua))                                     $errors[] = 'Lingua obbligatoria.';

    if (empty($errors)) {
        $pdo = getPDO();
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $chk->execute([':email' => $email]);
        if ($chk->fetch()) {
            $errors[] = 'Email già registrata.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (nome, cognome, email, password, nazionalita, lingua)
                 VALUES (:nome, :cognome, :email, :password, :nazionalita, :lingua)'
            );
            $stmt->execute([
                ':nome'        => $nome,
                ':cognome'     => $cognome,
                ':email'       => $email,
                ':password'    => $hash,
                ':nazionalita' => $nazionalita,
                ':lingua'      => $lingua,
            ]);
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
  <title>VacanzaMatch – Registrazione</title>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="register.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <div class="card register-card">
    <h2>Crea un account</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">
        ✅ Account creato! <a href="/src/pages/login/login.php">Accedi ora →</a>
      </div>
    <?php else: ?>

      <?php foreach ($errors as $e): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>

      <form method="POST">
        <div class="form-grid">
          <div>
            <label>Nome *</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
          </div>
          <div>
            <label>Cognome *</label>
            <input type="text" name="cognome" required value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>">
          </div>
          <div class="full">
            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>
          <div>
            <label>Password * <small>(min 8 caratteri)</small></label>
            <input type="password" name="password" required minlength="8">
          </div>
          <div>
            <label>Conferma password *</label>
            <input type="password" name="password2" required minlength="8">
          </div>
          <div>
            <label>Nazionalità *</label>
            <input type="text" name="nazionalita" required value="<?= htmlspecialchars($_POST['nazionalita'] ?? '') ?>">
          </div>
          <div>
            <label>Lingua principale *</label>
            <input type="text" name="lingua" required value="<?= htmlspecialchars($_POST['lingua'] ?? '') ?>">
          </div>
        </div>
        <button type="submit" class="btn-submit">Registrati</button>
      </form>

      <p class="register-footer">
        Hai già un account? <a href="/src/pages/login/login.php">Accedi</a>
      </p>
    <?php endif; ?>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
