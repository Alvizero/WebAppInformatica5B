<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

if (isAgency()) {
    header('Location: ../agency/agency.php');
    exit;
}

$user = currentUser();
$pdo = getPDO();

// Recupera tutte le conversazioni dell'utente (standard + pacchetti)
$stmt = $pdo->prepare("
    (SELECT 
        c.id as conversation_id,
        u.id as other_user_id,
        u.nome,
        u.cognome,
        m.message as last_message,
        m.created_at as last_message_time,
        NULL as pacchetto_id,
        NULL as pacchetto_titolo
    FROM conversations c
    JOIN users u ON (u.id = c.user1_id OR u.id = c.user2_id) AND u.id <> ?
    LEFT JOIN messages m ON m.id = (
        SELECT id FROM messages 
        WHERE conversation_id = c.id 
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE c.user1_id = ? OR c.user2_id = ?
    )
    UNION ALL
    (SELECT 
        pc.id as conversation_id,
        u.id as other_user_id,
        u.nome,
        u.cognome,
        pm.message as last_message,
        pm.created_at as last_message_time,
        p.id as pacchetto_id,
        p.titolo as pacchetto_titolo
    FROM package_conversations pc
    JOIN pacchetti p ON p.id = pc.pacchetto_id
    JOIN users u ON (u.id = pc.user_id OR u.id = pc.agenzia_user_id) AND u.id <> ?
    LEFT JOIN package_messages pm ON pm.id = (
        SELECT id FROM package_messages 
        WHERE package_conversation_id = pc.id 
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE pc.user_id = ? OR pc.agenzia_user_id = ?
    )
    ORDER BY last_message_time DESC
");
$stmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>I miei messaggi — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <style>
    .conv-page { padding: 2rem 0 4rem; }
    .conv-list {
      max-width: 720px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    .conv-card-wrapper {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .conv-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      color: inherit;
      transition: all var(--transition);
      box-shadow: var(--shadow-sm);
      flex: 1;
    }
    .conv-card:hover {
      border-color: var(--brand);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    .conv-avatar {
      width: 48px;
      height: 48px;
      background: var(--brand-glow);
      color: var(--brand);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1.1rem;
      flex-shrink: 0;
    }
    .conv-info { flex: 1; min-width: 0; }
    .conv-header {
      display: flex;
      justify-content: space-between;
      align-items: baseline;
      margin-bottom: .25rem;
    }
    .conv-name {
      font-weight: 700;
      color: var(--ink);
      font-size: 1rem;
    }
    .conv-time {
      font-size: .75rem;
      color: var(--muted);
    }
    .conv-last-msg {
      font-size: .875rem;
      color: var(--muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .conv-delete-btn {
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      padding: 0.5rem;
      transition: color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .conv-delete-btn:hover {
      color: var(--error);
    }
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
      background: var(--white);
      border-radius: var(--radius-xl);
      border: 2px dashed var(--border);
    }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container conv-page">
  <div class="section-header" style="max-width:720px; margin: 0 auto 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--ink);">I tuoi messaggi</h1>
    <p style="color: var(--muted);">Gestisci le conversazioni con i tuoi potenziali compagni di viaggio.</p>
  </div>

  <div class="conv-list">
    <?php if (empty($conversations)): ?>
      <div class="empty-state">
        <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: .5rem;">Nessun messaggio ancora</h2>
        <p style="color: var(--muted); margin-bottom: 1.5rem;">Cerca compagni di viaggio sulla mappa e inizia a chattare!</p>
        <a href="../map_view/map_view.php" class="btn btn-primary">Vai alla mappa</a>
      </div>
    <?php else: ?>
      <?php foreach ($conversations as $c): ?>
        <?php $initials = strtoupper(mb_substr($c['nome'],0,1) . mb_substr($c['cognome'],0,1)); ?>
        <?php 
          $link = $c['pacchetto_id'] 
            ? "package_chat.php?pkg_id={$c['pacchetto_id']}&user_id={$c['other_user_id']}" 
            : "chat.php?user_id={$c['other_user_id']}";
          $is_package = !empty($c['pacchetto_id']);
        ?>
        <div class="conv-card-wrapper">
          <a href="<?= $link ?>" class="conv-card">
            <div class="conv-avatar"><?= $initials ?></div>
            <div class="conv-info">
              <div class="conv-header">
                <span class="conv-name">
                  <?= htmlspecialchars($c['nome'] . ' ' . $c['cognome']) ?>
                  <?php if ($c['pacchetto_titolo']): ?>
                    <small style="display:block; color:var(--brand); font-weight:600;">Pacchetto: <?= htmlspecialchars($c['pacchetto_titolo']) ?></small>
                  <?php endif; ?>
                </span>
                <?php if ($c['last_message_time']): ?>
                  <span class="conv-time"><?= date('d/m H:i', strtotime($c['last_message_time'])) ?></span>
                <?php endif; ?>
              </div>
              <div class="conv-last-msg">
                <?= $c['last_message'] ? htmlspecialchars($c['last_message']) : '<i>Nessun messaggio</i>' ?>
              </div>
            </div>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color: var(--border-2)"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
          <button type="button" class="conv-delete-btn" onclick="deleteConversation(<?= $c['conversation_id'] ?>, <?= $is_package ? 'true' : 'false' ?>)" title="Elimina conversazione">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
          </button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
  function deleteConversation(conversationId, isPackage) {
    if (!confirm('Sei sicuro di voler eliminare questa conversazione? Non potrai recuperarla.')) return;
    
    const formData = new FormData();
    if (isPackage) {
      formData.append('package_conversation_id', conversationId);
      fetch('../../api/delete_package_conversation.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
          if (d.success) { showToast('Conversazione eliminata.', 'success'); setTimeout(() => location.reload(), 800); }
          else showToast('Errore: ' + d.error, 'error');
        })
        .catch(e => showToast('Errore: ' + e.message, 'error'));
    } else {
      formData.append('conversation_id', conversationId);
      fetch('../../api/delete_conversation.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
          if (d.success) { showToast('Conversazione eliminata.', 'success'); setTimeout(() => location.reload(), 800); }
          else showToast('Errore: ' + d.error, 'error');
        })
        .catch(e => showToast('Errore: ' + e.message, 'error'));
    }
  }
</script>

<script src="../../shared/app.js"></script>
</body>
</html>
