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
    $oggetto  = trim($_POST['oggetto']  ?? '');
    $messaggio = trim($_POST['messaggio'] ?? '');

    if (empty($oggetto))   $errors[] = "L'oggetto del ticket è obbligatorio.";
    if (empty($messaggio)) $errors[] = 'Il messaggio non può essere vuoto.';

    if (empty($errors)) {
        $ins = $pdo->prepare("INSERT INTO support_tickets (user_id, oggetto, stato) VALUES (:uid, :ogg, 'aperto')");
        $ins->execute([':uid' => $user['id'], ':ogg' => $oggetto]);
        $ticketId = $pdo->lastInsertId();

        $insMsg = $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)");
        $insMsg->execute([':tid' => $ticketId, ':sid' => $user['id'], ':msg' => $messaggio]);

        header('Location: supporto.php?success_msg=' . urlencode('Ticket aperto con successo!'));
        exit;
    }
}

$tickets = $pdo->prepare("SELECT * FROM support_tickets WHERE user_id = :uid ORDER BY created_at DESC");
$tickets->execute([':uid' => $user['id']]);
$tickets = $tickets->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supporto — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="supporto.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container supporto-page">

  <!-- Breadcrumb / navigazione -->
  <nav class="supporto-nav" aria-label="Navigazione pagina">
    <button onclick="history.back()" class="supporto-back-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      <span>Indietro</span>
    </button>
    <span class="nav-separator" aria-hidden="true">/</span>
    <span class="nav-current">Supporto</span>
  </nav>

  <!-- Hero -->
  <div class="supporto-hero">
    <div class="supporto-hero-content">
      <h1>Come possiamo aiutarti?</h1>
      <p>Apri un ticket e il nostro team ti risponderà il prima possibile. Siamo qui per te.</p>
    </div>
  </div>

  <div class="supporto-layout">

    <!-- Lista ticket -->
    <div class="tickets-section">
      <div class="tickets-section-header">
        <h2>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          I tuoi ticket
        </h2>
        <span class="tickets-count"><?= count($tickets) ?></span>
      </div>

      <?php if (empty($tickets)): ?>
        <div class="empty-tickets">
          <span class="empty-icon">💬</span>
          <strong>Nessun ticket ancora</strong>
          <p>Non hai ancora aperto nessuna richiesta di supporto. Usa il form a fianco per contattarci.</p>
        </div>
      <?php else: ?>
        <div class="ticket-list">
          <?php foreach ($tickets as $i => $t): ?>
            <a href="./supporto_chat.php?ticket=<?= $t['id'] ?>" class="ticket-item" style="animation-delay:<?= $i * 0.04 ?>s">
              <div class="ticket-item-left">
                <div class="ticket-oggetto"><?= htmlspecialchars($t['oggetto']) ?></div>
                <div class="ticket-meta">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?= date('d/m/Y', strtotime($t['created_at'])) ?>
                </div>
              </div>
              <div class="ticket-item-right">
                <span class="badge badge-<?= $t['stato'] ?>"><?= $t['stato'] ?></span>
                <svg class="ticket-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Nuovo ticket -->
    <div class="new-ticket-card">
      <h3>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Apri un nuovo ticket
      </h3>

      <?php foreach ($errors as $e): ?>
        <div class="alert alert-error">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
          <?= htmlspecialchars($e) ?>
        </div>
      <?php endforeach; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Ticket aperto con successo! Ti risponderemo presto.
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="input-wrap">
          <label>Oggetto *</label>
          <input type="text" name="oggetto" required placeholder="Descrivi brevemente il problema…" maxlength="120">
        </div>
        <div class="input-wrap">
          <label>Messaggio *</label>
          <textarea name="messaggio" required placeholder="Descrivi il problema nel dettaglio, includi eventuali errori o schermate utili…"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-full">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Invia ticket
        </button>
      </form>
    </div>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>