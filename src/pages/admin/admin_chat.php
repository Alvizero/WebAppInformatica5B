<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAdmin();

$me        = currentUser();
$pdo       = getPDO();
$ticket_id = isset($_GET['ticket']) ? (int)$_GET['ticket'] : 0;

$ticket = $pdo->prepare("SELECT * FROM support_tickets WHERE id = :id");
$ticket->execute(['id' => $ticket_id]);
$ticket = $ticket->fetch();

if (!$ticket) {
    redirect('admin.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['messaggio'] ?? ''))) {
    $msg = trim($_POST['messaggio']);
    $pdo->prepare("INSERT INTO support_messages (ticket_id, sender_id, messaggio) VALUES (:tid, :sid, :msg)")
        ->execute(['tid' => $ticket_id, 'sid' => $me['id'], 'msg' => $msg]);
    $pdo->prepare("UPDATE support_tickets SET stato='risposto' WHERE id=:id")->execute(['id' => $ticket_id]);
    redirect("admin_chat.php?ticket=$ticket_id");
}

$messaggi = $pdo->prepare("
    SELECT m.*, u.nome, u.livello_utente
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
  <title>Supporto Admin — <?= htmlspecialchars($ticket['oggetto']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="./../../shared/base.css">
  <style>
    .chat-page { padding: 2rem 0 3rem; }
    .chat-wrapper {
      max-width: 760px;
      margin: 0 auto;
      background: var(--white);
      border-radius: var(--radius-xl);
      border: 1px solid var(--border);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      animation: fadeUp .35s ease both;
    }
    .chat-topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border);
      background: var(--surface);
    }
    .chat-topbar-left { display: flex; flex-direction: column; gap: .2rem; }
    .chat-back {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      font-size: .82rem;
      font-weight: 600;
      color: var(--brand);
      text-decoration: none;
      margin-bottom: .3rem;
      transition: color var(--transition);
    }
    .chat-back:hover { color: var(--brand-dark); }
    .chat-topbar h2 {
      font-size: 1.05rem;
      color: var(--ink);
      font-weight: 700;
    }
    .chat-box {
      display: flex;
      flex-direction: column;
      gap: .85rem;
      padding: 1.5rem;
      max-height: 460px;
      overflow-y: auto;
      background: var(--surface);
    }
    .bubble {
      max-width: 78%;
      padding: .85rem 1.1rem;
      border-radius: 16px;
      font-size: .9rem;
      line-height: 1.6;
      animation: fadeUp .2s ease both;
    }
    .bubble.mine {
      align-self: flex-end;
      background: var(--brand);
      color: #fff;
      border-bottom-right-radius: 4px;
    }
    .bubble.theirs {
      align-self: flex-start;
      background: var(--white);
      color: var(--ink);
      border: 1px solid var(--border-2);
      border-bottom-left-radius: 4px;
    }
    .bubble.admin-msg {
      align-self: flex-start;
      background: #fffbeb;
      color: var(--ink);
      border: 1px solid #fde68a;
      border-bottom-left-radius: 4px;
    }
    .bubble-meta {
      font-size: .72rem;
      margin-top: .45rem;
      opacity: .75;
      text-align: right;
    }
    .bubble.theirs .bubble-meta,
    .bubble.admin-msg .bubble-meta { text-align: left; }
    .chat-input-area {
      padding: 1.25rem 1.5rem;
      border-top: 1px solid var(--border);
      background: var(--white);
    }
    .chat-input-row {
      display: flex;
      gap: .6rem;
      align-items: flex-end;
    }
    .chat-input-row textarea {
      flex: 1;
      padding: .75rem 1rem;
      border: 1.5px solid var(--border-2);
      border-radius: var(--radius);
      resize: none;
      font-family: 'Inter', sans-serif;
      font-size: .9rem;
      color: var(--ink);
      background: var(--surface);
      transition: all var(--transition);
      min-height: 48px;
      max-height: 120px;
    }
    .chat-input-row textarea:focus {
      outline: none;
      border-color: var(--brand);
      box-shadow: 0 0 0 3px var(--brand-glow);
      background: var(--white);
    }
    .btn-emoji {
      background: none;
      border: none;
      font-size: 1.4rem;
      cursor: pointer;
      padding: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s;
      user-select: none;
    }
    .btn-emoji:hover { transform: scale(1.2); }
    .emoji-picker {
      position: absolute;
      bottom: 100%;
      right: 0;
      background: white;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow-lg);
      padding: 0.5rem;
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 0.25rem;
      z-index: 100;
      margin-bottom: 0.5rem;
      display: none;
    }
    .emoji-picker span {
      cursor: pointer;
      font-size: 1.2rem;
      padding: 0.25rem;
      border-radius: 4px;
      text-align: center;
    }
    .emoji-picker span:hover { background: var(--surface); }
    .btn-send {
      padding: .75rem 1.25rem;
      background: var(--brand);
      color: #fff;
      border: none;
      border-radius: var(--radius);
      cursor: pointer;
      font-weight: 700;
      font-family: 'Inter', sans-serif;
      font-size: .875rem;
      transition: all var(--transition);
      display: flex;
      align-items: center;
      gap: .4rem;
      box-shadow: 0 2px 8px var(--brand-glow);
      white-space: nowrap;
    }
    .btn-send:hover { background: var(--brand-dark); transform: translateY(-1px); }
    .chat-closed-msg {
      text-align: center;
      padding: 1rem;
      background: var(--surface-2);
      border-radius: var(--radius);
      color: var(--muted);
      font-size: .875rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .5rem;
    }
    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  </style>
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container chat-page">
  <div class="chat-wrapper">
    <div class="chat-topbar">
      <div class="chat-topbar-left">
        <a href="./admin.php" class="chat-back">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          Torna al pannello
        </a>
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
        <p style="text-align:center;color:var(--muted-lt);font-size:.875rem;padding:2rem 0;">Nessun messaggio ancora.</p>
      <?php endif; ?>
      <?php foreach ($messaggi as $i => $m): ?>
        <?php
          $isMine     = ((int)$m['sender_id'] === (int)$me['id']);
          $isAdminMsg = ((int)$m['livello_utente'] < 255);
          $bubbleClass = $isMine ? 'mine' : ($isAdminMsg ? 'admin-msg' : 'theirs');
        ?>
        <div class="bubble <?= $bubbleClass ?>" style="animation-delay:<?= $i * 0.03 ?>s">
          <?= nl2br(htmlspecialchars($m['messaggio'])) ?>
          <div class="bubble-meta">
            <?= $isAdminMsg ? '⚙️ Assistenza' : '👤 ' . htmlspecialchars($m['nome']) ?>
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
            <textarea name="messaggio" id="chat-textarea" placeholder="Scrivi una risposta all'utente…" required rows="2"></textarea>
            <button type="submit" class="btn-send">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Invia
            </button>
          </div>
        </form>
      <?php else: ?>
        <div class="chat-closed-msg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Questo ticket è stato chiuso.
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
  const currentUserId = <?= (int)$me['id'] ?>;

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
          const isAdminMsg = (parseInt(m.livello_utente) < 255);
          const bubbleClass = isMine ? 'mine' : (isAdminMsg ? 'admin-msg' : 'theirs');
          
          const bubble = document.createElement('div');
          bubble.className = `bubble ${bubbleClass}`;
          
          const date = new Date(m.created_at);
          const timeStr = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth()+1).toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
          
          bubble.innerHTML = `
            ${m.messaggio.replace(/\n/g, '<br>')}
            <div class="bubble-meta">
              ${isAdminMsg ? '⚙️ Assistenza' : '👤 ' + m.nome} · ${timeStr}
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
        if (d.success) window.location.href = './admin.php?success_msg=' + encodeURIComponent('Ticket eliminato con successo.');
        else showToast('Errore: ' + d.error, 'error');
      })
      .catch(e => showToast('Errore: ' + e.message, 'error'));
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
