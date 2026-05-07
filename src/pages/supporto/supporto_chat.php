<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

$user      = currentUser();
$pdo       = getPDO();
$ticket_id = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;

$ticket = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id AND user_id = :uid");
$ticket->execute([':id' => $ticket_id, ':uid' => $user['id']]);
$ticket = $ticket->fetch();

if (!$ticket) {
    redirect('supporto.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ticket['stato'] !== 'chiuso') {
    $msg = trim($_POST['messaggio'] ?? '');
    if (!empty($msg)) {
        $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)")
            ->execute([':tid' => $ticket_id, ':sid' => $user['id'], ':msg' => $msg]);
        redirect("supporto_chat.php?ticket=$ticket_id");
    }
}

$messaggi = $pdo->prepare("
    SELECT m.*, u.nome, u.livello_utente
    FROM support_messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.ticket_id = :tid
    ORDER BY m.created_at ASC
");
$messaggi->execute([':tid' => $ticket_id]);
$messaggi = $messaggi->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supporto — <?= htmlspecialchars($ticket['oggetto']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="../../shared/chat.css">
  <style>
    /* Supporto chat: fluid height */
    .chat-box { max-height: 460px; height: auto; }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container chat-page">
  <div class="chat-wrapper">
    <div class="chat-topbar">
      <div class="chat-topbar-left">
        <button onclick="history.back()" class="chat-back" title="Torna indietro">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          Indietro
        </button>
        <h2><?= htmlspecialchars($ticket['oggetto']) ?></h2>
      </div>
      <div style="display:flex; align-items:center; gap:1rem;">
        <span class="badge badge-<?= $ticket['stato'] ?>"><?= strtoupper($ticket['stato']) ?></span>
        <button type="button" onclick="deleteTicket(<?= $ticket_id ?>)" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:0.5rem; transition:color 0.2s; display:flex; align-items:center; justify-content:center;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--muted)'" title="Elimina ticket">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </button>
      </div>
    </div>

    <div class="chat-box" id="chat-messages">
      <?php if (empty($messaggi)): ?>
        <p style="text-align:center;color:var(--muted-lt);font-size:.875rem;padding:2rem 0;">Nessun messaggio ancora. Il team ti risponderà presto.</p>
      <?php endif; ?>
      <?php foreach ($messaggi as $i => $m): ?>
        <?php
          $isMine     = ((int)$m['sender_id'] === (int)$user['id']);
          $isAdminMsg = ((int)$m['livello_utente'] < 255 && !$isMine);
          $bubbleClass = $isMine ? 'mine' : ($isAdminMsg ? 'admin-msg' : 'theirs');
        ?>
        <div class="bubble <?= $bubbleClass ?>" style="animation-delay:<?= $i * 0.03 ?>s">
          <?php if ($isAdminMsg): ?>
            <div class="admin-label">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              Team VacanzaMatch
            </div>
          <?php endif; ?>
          <?= nl2br(htmlspecialchars($m['messaggio'])) ?>
          <div class="bubble-meta">
            <?= $isMine ? 'Tu' : htmlspecialchars($m['nome']) ?>
            · <?= date('d/m H:i', strtotime($m['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="chat-input-area">
      <?php if ($ticket['stato'] !== 'chiuso'): ?>
        <form id="chat-form" method="POST" style="position: relative;">
          <div class="emoji-picker" id="emoji-picker">
            <span>😀</span><span>😂</span><span>😍</span><span>😊</span><span>😎</span><span>🙌</span>
            <span>👍</span><span>🔥</span><span>✨</span><span>📍</span><span>✈️</span><span>🌍</span>
            <span>🏖️</span><span>🍕</span><span>🍻</span><span>📸</span><span>👋</span><span>💬</span>
          </div>
          <div class="chat-input-row">
            <button type="button" class="btn-emoji" id="emoji-btn" title="Aggiungi emoji">😊</button>
            <textarea name="messaggio" id="chat-textarea" placeholder="Scrivi un messaggio…" required rows="2"></textarea>
            <button type="submit" class="btn-send">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Invia
            </button>
          </div>
        </form>
      <?php else: ?>
        <div class="chat-closed-msg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Questo ticket è stato chiuso. Per ulteriore assistenza, apri un nuovo ticket.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  const chatBox = document.getElementById('chat-messages');
  const chatForm = document.getElementById('chat-form');
  const chatTextarea = document.getElementById('chat-textarea');
  const emojiBtn = document.getElementById('emoji-btn');
  const emojiPicker = document.getElementById('emoji-picker');
  
  let lastMessageId = <?= !empty($messaggi) ? (int)end($messaggi)['id'] : 0 ?>;
  const ticketId = <?= $ticket_id ?>;
  const currentUserId = <?= (int)$user['id'] ?>;

  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

  if (chatForm) {
    // Emoji Picker toggle
    emojiBtn.addEventListener('click', () => {
      emojiPicker.style.display = emojiPicker.style.display === 'grid' ? 'none' : 'grid';
    });

    emojiPicker.querySelectorAll('span').forEach(emoji => {
      emoji.addEventListener('click', () => {
        chatTextarea.value += emoji.textContent;
        emojiPicker.style.display = 'none';
        chatTextarea.focus();
      });
    });

    // Chiudi emoji picker se clicchi fuori
    document.addEventListener('click', (e) => {
      if (emojiBtn && !emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
        emojiPicker.style.display = 'none';
      }
    });

    // Invio messaggio via AJAX
    chatForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const msg = chatTextarea.value.trim();
      if (!msg) return;

      chatTextarea.value = '';
      
      try {
        const formData = new FormData();
        formData.append('ticket_id', ticketId);
        formData.append('message', msg);

        const res = await fetch('../../api/send_support_message.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        if (data.success) {
          fetchNewMessages();
        }
      } catch (err) {
        console.error('Errore invio messaggio:', err);
      }
    });
  }

  // Polling per nuovi messaggi
  async function fetchNewMessages() {
    try {
      const res = await fetch(`../../api/get_support_messages.php?ticket_id=${ticketId}&last_id=${lastMessageId}`);
      const data = await res.json();
      
      if (data.messages && data.messages.length > 0) {
        data.messages.forEach(m => {
          const isMine = parseInt(m.sender_id) === currentUserId;
          const isAdminMsg = (parseInt(m.livello_utente) < 255 && !isMine);
          const bubbleClass = isMine ? 'mine' : (isAdminMsg ? 'admin-msg' : 'theirs');
          
          const bubble = document.createElement('div');
          bubble.className = `bubble ${bubbleClass}`;
          
          const date = new Date(m.created_at);
          const timeStr = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth()+1).toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
          
          let adminLabel = '';
          if (isAdminMsg) {
            adminLabel = `
              <div class="admin-label">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                Team VacanzaMatch
              </div>`;
          }

          bubble.innerHTML = `
            ${adminLabel}
            ${m.messaggio.replace(/\n/g, '<br>')}
            <div class="bubble-meta">
              ${isMine ? 'Tu' : m.nome} · ${timeStr}
            </div>
          `;
          chatBox.appendChild(bubble);
          lastMessageId = m.id;
        });
        chatBox.scrollTop = chatBox.scrollHeight;
      }
    } catch (err) {
      console.error('Errore recupero messaggi:', err);
    }
  }

  setInterval(fetchNewMessages, 3000);

  function deleteTicket(ticketId) {
    if (!confirm('Sei sicuro di voler eliminare questo ticket? Non potrai recuperarlo.')) return;
    
    const formData = new FormData();
    formData.append('ticket_id', ticketId);
    fetch('../../api/delete_support_ticket.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(d => {
        if (d.success) window.location.href = './supporto.php?success_msg=' + encodeURIComponent('Ticket eliminato con successo.');
        else showToast('Errore: ' + d.error, 'error');
      })
      .catch(e => showToast('Errore: ' + e.message, 'error'));
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
