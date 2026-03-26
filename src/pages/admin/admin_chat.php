<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAdmin();

$me        = currentUser();
$pdo       = getPDO();
$ticket_id = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;

// L'admin può vedere TUTTI i ticket, non solo i suoi
$ticket = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id");
$ticket->execute(['id' => $ticket_id]);
$ticket = $ticket->fetch();

if (!$ticket) {
    header('Location: ./admin.php');
    exit;
}

// Invia messaggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['messaggio'] ?? ''))) {
    $msg = trim($_POST['messaggio']);
    $ins = $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)");
    $ins->execute(['tid' => $ticket_id, 'sid' => $me['id'], 'msg' => $msg]);

    // Aggiorna stato a 'risposto'
    $pdo->prepare("UPDATE support_tickets SET stato='risposto' WHERE id=:id")->execute(['id' => $ticket_id]);

    header('Location: ./admin_chat.php?ticket=' . $ticket_id);
    exit;
}

$messaggi = $pdo->prepare("
    SELECT m.*, u.nome, u.admin_level 
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
  <link rel="stylesheet" href="supporto.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container" style="margin-top:2rem;">
  <div class="card chat-container" style="max-width:700px; margin:0 auto;">
    <div class="chat-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:1px solid #eee; padding-bottom:1rem;">
      <div>
        <a href="./admin.php" style="font-size:.85rem; color:#0077b6; text-decoration:none;">← Torna al pannello</a>
        <h2 style="margin:0.5rem 0 0 0; font-size:1.2rem;"><?= htmlspecialchars($ticket['oggetto']) ?></h2>
      </div>
      <span class="badge badge-<?= $ticket['stato'] ?>" style="padding:0.4rem 0.8rem; border-radius:20px; font-size:0.8rem; font-weight:bold;">
        <?= strtoupper($ticket['stato']) ?>
      </span>
    </div>

    <div class="chat-messages" id="chat-messages" style="display:flex; flex-direction:column; gap:1rem; max-height:400px; overflow-y:auto; padding:1rem; background:#f9f9f9; border-radius:8px; margin-bottom:1.5rem;">
      <?php foreach ($messaggi as $m): ?>
        <?php
          $isMine     = ((int)$m['sender_id'] === (int)$me['id']);
          $isAdminMsg = ((int)$m['admin_level'] < 255);
        ?>
        <div style="
            max-width: 80%;
            padding: 0.8rem;
            border-radius: 12px;
            font-size: 0.95rem;
            align-self: <?= $isMine ? 'flex-end' : 'flex-start' ?>;
            background: <?= $isMine ? '#0077b6' : ($isAdminMsg ? '#fff3cd' : '#e4e6eb') ?>;
            color: <?= $isMine ? '#fff' : '#333' ?>;
        ">
          <?= nl2br(htmlspecialchars($m['messaggio'])) ?>
          <div style="font-size:0.7rem; margin-top:0.4rem; opacity:0.8; text-align:right;">
            <?= $isAdminMsg ? '⚙️ Assistenza' : '👤 ' . htmlspecialchars($m['nome']) ?>
            · <?= date('d/m H:i', strtotime($m['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($ticket['stato'] !== 'chiuso'): ?>
    <form method="POST">
      <div style="display:flex; gap:0.5rem;">
        <textarea name="messaggio" placeholder="Scrivi una risposta..." required
                  style="flex:1; padding:0.8rem; border:1px solid #ccc; border-radius:8px; resize:none;"></textarea>
        <button type="submit" class="btn-submit" style="margin:0; width:auto; padding:0 1.5rem;">Invia</button>
      </div>
    </form>
    <?php else: ?>
      <div style="text-align:center; padding:1rem; background:#eee; border-radius:8px; color:#666;">
        ✖ Questa richiesta è stata chiusa.
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  const msgs = document.getElementById('chat-messages');
  if (msgs) msgs.scrollTop = msgs.scrollHeight;
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
 