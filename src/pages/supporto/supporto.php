<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireLogin();

$me  = currentUser();
$pdo = getPDO();

// Apri nuovo ticket
$errore   = '';
$successo = false;
$oggetto  = '';
$messaggio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oggetto   = trim($_POST['oggetto'] ?? '');
    $messaggio = trim($_POST['messaggio'] ?? '');

    if (empty($oggetto) || empty($messaggio)) {
        $errore = 'Compila tutti i campi.';
    } else {
        $ins = $pdo->prepare("INSERT INTO support_tickets (user_id, oggetto) VALUES (:uid, :ogg)");
        $ins->execute(['uid' => $me['id'], 'ogg' => $oggetto]);
        $tid = (int)$pdo->lastInsertId();

        $msg = $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)");
        $msg->execute(['tid' => $tid, 'sid' => $me['id'], 'msg' => $messaggio]);

        $successo  = true;
        $oggetto   = '';
        $messaggio = '';
    }
}

// I miei ticket
$tickets = $pdo->prepare("
    SELECT t.*,
        (SELECT COUNT(*) FROM support_messages WHERE ticket_id = t.id) AS num_messaggi
    FROM support_tickets t
    WHERE t.user_id = :uid
    ORDER BY t.created_at DESC
");
$tickets->execute(['uid' => $me['id']]);
$tickets = $tickets->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VacanzaMatch — Supporto</title>
  <link rel="stylesheet" href="./../../shared/base.css">
  <link rel="stylesheet" href="./supporto.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container" style="margin-top:2rem;">
  <div class="supporto-layout">

    <!-- Form nuovo ticket -->
    <div class="card ticket-form-card">
      <h2>💬 Apri una richiesta</h2>

      <?php if ($successo): ?>
        <div class="alert alert-success">Richiesta inviata! Riceverai una risposta a breve.</div>
      <?php endif; ?>
      <?php if ($errore): ?>
        <div class="alert alert-error"><?= htmlspecialchars($errore) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-grid">
          <div class="full">
            <label>Oggetto</label>
            <input type="text" name="oggetto" required maxlength="255"
                   placeholder="Es. Problema con il mio viaggio"
                   value="<?= htmlspecialchars($oggetto) ?>">
          </div>
          <div class="full">
            <label>Messaggio</label>
            <textarea name="messaggio" required rows="5"
                      style="width:100%;padding:.65rem .85rem;border:1.5px solid #cdd5e0;border-radius:8px;font-size:.92rem;resize:vertical;font-family:inherit;"
                      placeholder="Descrivi il tuo problema..."><?= htmlspecialchars($messaggio) ?></textarea>
          </div>
          <div class="full">
            <button type="submit" class="btn-submit">Invia richiesta</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Lista ticket -->
    <div class="card ticket-list-card">
      <h2>📋 Le mie richieste</h2>

      <?php if (empty($tickets)): ?>
        <p style="color:#aaa;font-size:.9rem;">Nessuna richiesta ancora.</p>
      <?php else: ?>
        <div class="ticket-list">
          <?php foreach ($tickets as $t): ?>
            <div class="ticket-item">
              <div class="ticket-item-left">
                <div class="ticket-item-oggetto"><?= htmlspecialchars($t['oggetto']) ?></div>
                <div class="ticket-item-meta">
                  <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                  · <?= $t['num_messaggi'] ?> messaggio/i
                </div>
              </div>
              <div style="display:flex;align-items:center;gap:.6rem;flex-shrink:0;">
                <span class="badge badge-<?= $t['stato'] ?>"><?= $t['stato'] ?></span>
                <a href="./supporto_chat.php?ticket=<?= $t['id'] ?>" class="btn-edit">
                  <?= $t['stato'] === 'risposto' ? '💬 Rispondi' : '👁 Vedi' ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script src="./../../shared/app.js"></script>
</body>
</html>