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

if (!$dbUser) {
    die('Errore: utente non trovato.');
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome        = trim($_POST['nome']        ?? '');
    $cognome     = trim($_POST['cognome']     ?? '');
    $email       = trim($_POST['email']       ?? '');
    $nazionalita = trim($_POST['nazionalita'] ?? '');
    $lingua      = trim($_POST['lingua']      ?? '');

    if (empty($nome))                                       $errors[] = 'Nome obbligatorio.';
    if (empty($cognome))                                    $errors[] = 'Cognome obbligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Email non valida.';
    if (empty($nazionalita))                                $errors[] = 'Nazionalità obbligatoria.';
    if (empty($lingua))                                     $errors[] = 'Lingua obbligatoria.';

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $chk->execute([$email, $user['id']]);
        if ($chk->fetch()) {
            $errors[] = 'Email già usata.';
        } else {
            $upd = $pdo->prepare(
                'UPDATE users SET nome=?, cognome=?, email=?, nazionalita=?, lingua=? WHERE id=?'
            );
            $upd->execute([$nome, $cognome, $email, $nazionalita, $lingua, $user['id']]);
            $success = true;

            $_SESSION['user_nome']    = $nome;
            $_SESSION['user_cognome'] = $cognome;
            $_SESSION['user_naz']     = $nazionalita;
            $_SESSION['user_lingua']  = $lingua;

            // Ricarica i dati aggiornati
            $dbUser = array_merge($dbUser, compact('nome', 'cognome', 'email', 'nazionalita', 'lingua'));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profilo – VacanzaMatch</title>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="profilo.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <div class="card profilo-card">
    <h2>👤 Il tuo profilo</h2>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ Dati aggiornati!</div>
    <?php endif; ?>

    <form method="POST" class="form-grid">
      <div>
        <label>Nome *</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($dbUser['nome'] ?? '') ?>">
      </div>
      <div>
        <label>Cognome *</label>
        <input type="text" name="cognome" required value="<?= htmlspecialchars($dbUser['cognome'] ?? '') ?>">
      </div>
      <div class="full">
        <label>Email *</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($dbUser['email'] ?? '') ?>">
      </div>
      <div>
        <label>Nazionalità *</label>
        <input type="text" name="nazionalita" required value="<?= htmlspecialchars($dbUser['nazionalita'] ?? '') ?>">
      </div>
      <div>
        <label>Lingua principale *</label>
        <input type="text" name="lingua" required value="<?= htmlspecialchars($dbUser['lingua'] ?? '') ?>">
      </div>
      <div class="full">
        <button type="submit" class="btn-submit">💾 Salva modifiche</button>
      </div>
    </form>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
