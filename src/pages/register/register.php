<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/lingue_list.php';
startSession();

if (isLoggedIn()) {
    header('Location: ./../dashboard/dashboard.php');
    exit;
}

$pdo = getPDO();
$errors  = [];
$success = false;

// Carichiamo tutte le nazionalità dal database
$stmtNaz = $pdo->query('SELECT id, nome FROM nazionalita ORDER BY nome ASC');
$allNazionalita = $stmtNaz->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome           = trim($_POST['nome']           ?? '');
    $cognome        = trim($_POST['cognome']        ?? '');
    $email          = trim($_POST['email']          ?? '');
    $password       = $_POST['password']            ?? '';
    $password2      = $_POST['password2']           ?? '';
    $nazionalita_id = (int)($_POST['nazionalita_id'] ?? 0);
    $lingua_id      = (int)($_POST['lingua_id']      ?? 0);

    // Validazione nazionalità
    $nazExists = false;
    foreach ($allNazionalita as $naz) {
        if ((int)$naz['id'] === $nazionalita_id) {
            $nazExists = true;
            break;
        }
    }

    if (!$nazExists)                                        $errors[] = 'Nazionalità non valida.';
    if (empty($nome))                                       $errors[] = 'Nome obbligatorio.';
    if (empty($cognome))                                    $errors[] = 'Cognome obbligatorio.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = 'Email non valida.';
    if (strlen($password) < 8)                              $errors[] = 'Password minimo 8 caratteri.';
    if ($password !== $password2)                           $errors[] = 'Le password non coincidono.';
    if (empty($nazionalita_id))                             $errors[] = 'Nazionalità obbligatoria.';
    if (empty($lingua_id))                                  $errors[] = 'Lingua obbligatoria.';

    if (empty($errors)) {
        $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $chk->execute([':email' => $email]);
        if ($chk->fetch()) {
            $errors[] = 'Email già registrata.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (nome, cognome, email, password, nazionalita_id, lingua_id)
                 VALUES (:nome, :cognome, :email, :password, :nazionalita_id, :lingua_id)'
            );
            $stmt->execute([
                ':nome'           => $nome,
                ':cognome'        => $cognome,
                ':email'          => $email,
                ':password'       => $hash,
                ':nazionalita_id' => $nazionalita_id,
                ':lingua_id'      => $lingua_id,
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
  <title>Registrati — FrienTrip</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="../../shared/auth.css">
  <link rel="stylesheet" href="register.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="auth-page">
  <div class="auth-visual">
    <div class="auth-visual-inner">
      <span class="auth-visual-deco">✈️</span>
      <h2>Il tuo prossimo<br>viaggio ti aspetta</h2>
      <p>Unisciti a migliaia di viaggiatori<br>che hanno già trovato compagnia.</p>
    </div>
  </div>

  <div class="auth-form-side" style="padding: 2rem;">
    <div class="register-card">
      <h2>Crea un account</h2>
      <p class="auth-sub">Gratis, senza abbonamenti</p>

      <?php if ($success): ?>
        <div class="alert alert-success">
          ✅ Account creato con successo! <a href="./../login/login.php" style="font-weight:600;">Accedi ora →</a>
        </div>
      <?php else: ?>
        <?php foreach ($errors as $e): ?>
          <div class="alert alert-error">⚠ <?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>

        <form method="POST">
          <div class="form-grid">
            <div>
              <div class="input-wrap">
                <label>Nome *</label>
                <input type="text" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" placeholder="Mario">
              </div>
            </div>
            <div>
              <div class="input-wrap">
                <label>Cognome *</label>
                <input type="text" name="cognome" required value="<?= htmlspecialchars($_POST['cognome'] ?? '') ?>" placeholder="Rossi">
              </div>
            </div>
            <div class="full">
              <div class="input-wrap">
                <label>Email *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="mario@esempio.it">
              </div>
            </div>
            <div>
              <div class="input-wrap">
                <label>Password * <span style="font-weight:400;text-transform:none;letter-spacing:0;">(min 8 car.)</span></label>
                <div style="position:relative;">
                  <input type="password" name="password" id="password1" required minlength="8" placeholder="••••••••">
                  <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password1')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:.25rem;color:var(--muted);font-size:1.1rem;transition:color var(--transition);">
                    <span class="eye-icon">👁️</span>
                  </button>
                </div>
              </div>
            </div>
            <div>
              <div class="input-wrap">
                <label>Conferma password *</label>
                <div style="position:relative;">
                  <input type="password" name="password2" id="password2" required minlength="8" placeholder="••••••••">
                  <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password2')" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:.25rem;color:var(--muted);font-size:1.1rem;transition:color var(--transition);">
                    <span class="eye-icon">👁️</span>
                  </button>
                </div>
              </div>
            </div>
            <div>
              <div class="input-wrap">
                <label>Nazionalità *</label>
                <select name="nazionalita_id" required class="form-select">
                  <option value="">Seleziona...</option>
                  <?php foreach ($allNazionalita as $naz): ?>
                    <option value="<?= (int)$naz['id'] ?>" <?= ((int)($_POST['nazionalita_id'] ?? 0) === (int)$naz['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($naz['nome']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div>
              <div class="input-wrap">
                <label>Lingua principale *</label>
                <?= lingueSelectHtml($pdo, 'lingua_id', (int)($_POST['lingua_id'] ?? 0)) ?>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:.5rem;">
            Crea account →
          </button>
        </form>

        <p class="register-footer">
          Hai già un account? <a href="./../login/login.php">Accedi</a>
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
  const field = document.getElementById(fieldId);
  const btn = event.currentTarget;
  if (field.type === 'password') {
    field.type = 'text';
    btn.innerHTML = '<span class="eye-icon">👁️‍🗨️</span>';
    btn.style.color = 'var(--brand)';
  } else {
    field.type = 'password';
    btn.innerHTML = '<span class="eye-icon">👁️</span>';
    btn.style.color = 'var(--muted)';
  }
}
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
