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
$other_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$admin_contact = isset($_GET['admin_contact']) ? (int)$_GET['admin_contact'] : 0;
$pkg_id = isset($_GET['pkg_id']) ? (int)$_GET['pkg_id'] : 0;

if ($admin_contact === 1) {
    // Trova il primo admin disponibile o un admin specifico
    $stmtAdmin = $pdo->query("SELECT id FROM users WHERE livello_utente <= 10 LIMIT 1");
    $admin = $stmtAdmin->fetch();
    if ($admin) {
        $other_user_id = (int)$admin['id'];
    }
}

if ($other_user_id === 0 || $other_user_id === (int)$user['id']) {
    redirect('../dashboard/dashboard.php');
}

// Se veniamo da un pacchetto, aggiungiamo un messaggio automatico
$initial_msg = "";
if ($pkg_id > 0) {
    $stmtPkg = $pdo->prepare("SELECT titolo FROM pacchetti WHERE id = ?");
    $stmtPkg->execute([$pkg_id]);
    $pkg = $stmtPkg->fetch();
    if ($pkg) {
        $initial_msg = "Ciao! Sono interessato al pacchetto: " . $pkg['titolo'];
    }
}

// Verifica se l'altro utente esiste
$stmt = $pdo->prepare("SELECT nome, cognome FROM users WHERE id = ?");
$stmt->execute([$other_user_id]);
$other_user = $stmt->fetch();

if (!$other_user) {
    redirect('../dashboard/dashboard.php');
}

// Trova o crea la conversazione
$u1 = min((int)$user['id'], $other_user_id);
$u2 = max((int)$user['id'], $other_user_id);

$stmt = $pdo->prepare("SELECT id FROM conversations WHERE user1_id = ? AND user2_id = ?");
$stmt->execute([$u1, $u2]);
$conv = $stmt->fetch();

if (!$conv) {
    $stmt = $pdo->prepare("INSERT INTO conversations (user1_id, user2_id) VALUES (?, ?)");
    $stmt->execute([$u1, $u2]);
    $conversation_id = (int)$pdo->lastInsertId();
} else {
    $conversation_id = (int)$conv['id'];
}

// Gestione invio messaggio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['messaggio'] ?? '');
    if (!empty($msg)) {
        $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)")
            ->execute([$conversation_id, $user['id'], $msg]);
        redirect("chat.php?user_id=$other_user_id");
    }
}

// Recupero messaggi
$stmt = $pdo->prepare("
    SELECT m.*, u.nome
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversation_id]);
$messaggi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat con <?= htmlspecialchars($other_user['nome']) ?> — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <style>
    .chat-page { padding: 2rem 0 3rem; }
    .chat-wrapper {
      max-width: 720px;
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
      margin-bottom: .2rem;
      transition: color var(--transition);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
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
      height: 460px;
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
    .bubble-meta {
      font-size: .72rem;
      margin-top: .45rem;
      opacity: .75;
      text-align: right;
    }
    .bubble.theirs .bubble-meta { text-align: left; }
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
    @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
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
        <h2>Chat con <?= htmlspecialchars($other_user['nome'] . ' ' . $other_user['cognome']) ?></h2>
      </div>
    </div>

    <div class="chat-box" id="chat-messages">
      <?php if (empty($messaggi)): ?>
        <p style="text-align:center;color:var(--muted-lt);font-size:.875rem;padding:2rem 0;">Inizia la conversazione! Saluta <?= htmlspecialchars($other_user['nome']) ?>.</p>
      <?php endif; ?>
      <?php foreach ($messaggi as $i => $m): ?>
        <?php $isMine = ((int)$m['sender_id'] === (int)$user['id']); ?>
        <div class="bubble <?= $isMine ? 'mine' : 'theirs' ?>" style="animation-delay:<?= $i * 0.03 ?>s">
          <?= nl2br(htmlspecialchars($m['message'])) ?>
          <div class="bubble-meta">
            <?= $isMine ? 'Tu' : htmlspecialchars($m['nome']) ?>
            · <?= date('d/m H:i', strtotime($m['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="chat-input-area">
      <form id="chat-form" method="POST" style="position: relative;">
        <div class="emoji-picker" id="emoji-picker">
          <span>😀</span><span>😂</span><span>😍</span><span>😊</span><span>😎</span><span>🙌</span>
          <span>👍</span><span>🔥</span><span>✨</span><span>📍</span><span>✈️</span><span>🌍</span>
          <span>🏖️</span><span>🍕</span><span>🍻</span><span>📸</span><span>👋</span><span>💬</span>
        </div>
        <div class="chat-input-row">
          <button type="button" class="btn-emoji" id="emoji-btn" title="Aggiungi emoji">😊</button>
          <textarea name="messaggio" id="chat-textarea" placeholder="Scrivi un messaggio…" required rows="2"><?= htmlspecialchars($initial_msg) ?></textarea>
          <button type="submit" class="btn-send">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Invia
          </button>
        </div>
      </form>
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
  const conversationId = <?= $conversation_id ?>;
  const currentUserId = <?= (int)$user['id'] ?>;

  if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

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
    if (!emojiBtn.contains(e.target) && !emojiPicker.contains(e.target)) {
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
      formData.append('conversation_id', conversationId);
      formData.append('message', msg);

      const res = await fetch('../../api/send_message.php', {
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

  // Polling per nuovi messaggi
  async function fetchNewMessages() {
    try {
      const res = await fetch(`../../api/get_messages.php?conversation_id=${conversationId}&last_id=${lastMessageId}`);
      const data = await res.json();
      
      if (data.messages && data.messages.length > 0) {
        data.messages.forEach(m => {
          const isMine = parseInt(m.sender_id) === currentUserId;
          const bubble = document.createElement('div');
          bubble.className = `bubble ${isMine ? 'mine' : 'theirs'}`;
          
          const date = new Date(m.created_at);
          const timeStr = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth()+1).toString().padStart(2, '0')} ${date.getHours().toString().padStart(2, '0')}:${date.getMinutes().toString().padStart(2, '0')}`;
          
          bubble.innerHTML = `
            ${m.message.replace(/\n/g, '<br>')}
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
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
