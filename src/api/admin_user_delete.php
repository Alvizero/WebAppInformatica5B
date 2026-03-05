<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireLogin();

$me        = currentUser();
$pdo       = getPDO();
$ticket_id = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;

// Verifica che il ticket appartenga all'utente
$ticket = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id AND user_id = :uid");
$ticket->execute(['id' => $ticket_id, 'uid' => $me['id']]);
$ticket = $ticket->fetch();

if (!$ticket) {
    header('Location: ./supporto.php');
    exit;
}

// Invia messaggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['messaggio'] ?? ''))) {
    $msg = trim($_POST['messaggio']);
    $ins = $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)");
    $ins->execute(['tid' => $ticket_id, 'sid' => $me['id'], 'msg' => $msg]);

    // Se era 'risposto' torna ad 'aperto' per notificare l'admin
    if ($ticket['stato'] === 'risposto') {
        $pdo->prepare("UPDATE support_tickets SET stato='aperto' WHERE id=:id")->execute(['id' => $ticket_id]);
    }
    header('Location: ./supporto_chat.php?ticket=' . $ticket_id);
    exit;
}

$messaggi = $pdo->prepare("
    SELECT m.*, u.nome, u.ruolo
    FROM support_messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.ticket_id = :tid
    ORDER BY m.created_at ASC
");
$messaggi->execute(['tid' => $ticket_id]);
$messaggi = $messaggi->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supporto — <?= htmlspecialchars($ticket['oggetto']) ?></title>
  <link rel="stylesheet" href="./../../shared/base.css">
  <link rel="stylesheet" href="./supporto.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container" style="margin-top:2rem;">
  <div class="card chat-container">
    <div class="chat-header">
      <a href="./supporto.php">← Le mie richieste</a>
      <h2><?= htmlspecialchars($ticket['oggetto']) ?></h2>
      <span class="badge badge-<?= $ticket['stato'] ?>"><?= $ticket['stato'] ?></span>
    </div>

    <div class="chat-messages" id="chat-messages">
      <?php foreach ($messaggi as $m): ?>
        <?php $isMine = ((int)$m['sender_id'] === (int)$me['id']); ?>
        <div class="chat-bubble <?= $isMine ? 'mine' : 'theirs' ?>">
          <?= nl2br(htmlspecialchars($m['messaggio'])) ?>
          <div class="bubble-meta">
            <?= $m['ruolo'] === 'admin' ? '⚙️ Admin' : '👤 Tu' ?>
            · <?= date('d/m H:i', strtotime($m['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($ticket['stato'] !== 'chiuso'): ?>
    <form method="POST">
      <div class="chat-input-row">
        <textarea name="messaggio" placeholder="Scrivi un messaggio..." required></textarea>
        <button type="submit">Invia</button>
      </div>
    </form>
    <?php else: ?>
      <div class="chat-closed-notice">✖ Ticket chiuso dall'amministratore</div>
    <?php endif; ?>
  </div>
</div>

<script>
  const msgs = document.getElementById('chat-messages');
  msgs.scrollTop = msgs.scrollHeight;
</script>
</body>
</html>
