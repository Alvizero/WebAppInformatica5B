<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAgency();

$me  = currentUser();
$pdo = getPDO();

// Recupera i pacchetti dell'agenzia
$stmt = $pdo->prepare("
    SELECT p.*, COUNT(DISTINCT pc.id) as num_conversations
    FROM pacchetti p
    LEFT JOIN package_conversations pc ON pc.pacchetto_id = p.id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$me['id']]);
$pacchetti = $stmt->fetchAll();

// Recupera le conversazioni per questa agenzia
$stmt = $pdo->prepare("
    SELECT 
        pc.id as conversation_id,
        pc.pacchetto_id,
        p.titolo as pacchetto_titolo,
        u.id as user_id,
        u.nome,
        u.cognome,
        pm.message as last_message,
        pm.created_at as last_message_time
    FROM package_conversations pc
    JOIN pacchetti p ON p.id = pc.pacchetto_id
    JOIN users u ON u.id = pc.user_id
    LEFT JOIN package_messages pm ON pm.id = (
        SELECT id FROM package_messages 
        WHERE package_conversation_id = pc.id 
        ORDER BY created_at DESC LIMIT 1
    )
    WHERE p.user_id = ?
    ORDER BY pm.created_at DESC
");
$stmt->execute([$me['id']]);
$conversations = $stmt->fetchAll();

$resetMsg = getFlash('reset_msg');
$errorMsg = getFlash('reset_error');
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pannello Agenzia — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="./../../shared/base.css">
  <link rel="stylesheet" href="./agency.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container agency-page">

  <?php if ($resetMsg): ?>
    <div class="alert alert-success" style="margin-bottom:1.25rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <?= htmlspecialchars($resetMsg) ?>
    </div>
  <?php endif; ?>

  <?php if ($errorMsg): ?>
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="agency-page-header">
    <h1>
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Pannello Agenzia
    </h1>
    <div class="agency-badge-me">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      <?= htmlspecialchars($me['nome'] . ' ' . $me['cognome']) ?> · <?= adminLevelLabel((int)$me['livello_utente']) ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="agency-stats">
    <div class="stat-card">
      <div class="stat-icon">🎁</div>
      <div class="stat-num"><?= count($pacchetti) ?></div>
      <div class="stat-label">Pacchetti</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💬</div>
      <div class="stat-num"><?= count($conversations) ?></div>
      <div class="stat-label">Conversazioni</div>
    </div>
  </div>

  <!-- Grid -->
  <div class="agency-grid">
    <aside class="agency-sidebar">
      <h3>Sezioni</h3>
      <button class="agency-nav-item active" onclick="showSection('pacchetti', this)">
        <span class="nav-icon">🎁</span> Pacchetti
      </button>
      <button class="agency-nav-item" onclick="showSection('messaggi', this)">
        <span class="nav-icon">💬</span> Messaggi
      </button>
    </aside>

    <main class="agency-content">

      <!-- SEZIONE PACCHETTI -->
      <section class="agency-section active" id="section-pacchetti">
        <div class="agency-section-header">
          <h2>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            I tuoi Pacchetti
          </h2>
          <button class="btn btn-primary" onclick="openPkgModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuovo Pacchetto
          </button>
        </div>

        <?php if (empty($pacchetti)): ?>
          <div class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎁</div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: .5rem;">Nessun pacchetto ancora</h3>
            <p style="color: var(--muted); margin-bottom: 1.5rem;">Crea il tuo primo pacchetto di viaggio!</p>
            <button class="btn btn-primary" onclick="openPkgModal()">Crea Pacchetto</button>
          </div>
        <?php else: ?>
          <div class="packages-grid">
            <?php foreach ($pacchetti as $p): ?>
              <div class="package-card">
                <div class="package-header">
                  <h3><?= htmlspecialchars($p['titolo']) ?></h3>
                  <span class="badge badge-conversations"><?= $p['num_conversations'] ?> contatti</span>
                </div>
                <p class="package-desc"><?= htmlspecialchars(substr($p['descrizione'], 0, 100)) ?>...</p>
                <div class="package-meta">
                  <span>📍 <?= htmlspecialchars(substr($p['localita'], 0, 50)) ?></span>
                  <span>💰 €<?= number_format((float)$p['prezzo'], 2, ',', '.') ?></span>
                </div>
                <div class="package-actions">
                  <button class="btn-edit" onclick="editPackage(<?= htmlspecialchars(json_encode($p)) ?>)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Modifica
                  </button>
                  <form method="POST" action="./../../api/admin_pacchetti_delete.php" onsubmit="return confirm('Eliminare questo pacchetto?')" style="margin:0;">
                    <input type="hidden" name="pacchetto_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-delete">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- SEZIONE MESSAGGI -->
      <section class="agency-section" id="section-messaggi">
        <div class="agency-section-header">
          <h2>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Conversazioni
          </h2>
          <span class="agency-section-count"><?= count($conversations) ?> conversazioni</span>
        </div>

        <?php if (empty($conversations)): ?>
          <div class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: .5rem;">Nessun messaggio ancora</h3>
            <p style="color: var(--muted);">I clienti inizieranno a contattarti quando vedranno i tuoi pacchetti.</p>
          </div>
        <?php else: ?>
          <div class="conversations-list">
            <?php foreach ($conversations as $c): ?>
              <div style="display:flex; align-items:center; gap:0.5rem;">
                <a href="../chat/package_chat.php?pkg_id=<?= $c['pacchetto_id'] ?>&user_id=<?= $c['user_id'] ?>" class="conversation-item" style="flex:1;">
                  <div class="conv-left">
                    <h4><?= htmlspecialchars($c['nome'] . ' ' . $c['cognome']) ?></h4>
                    <p class="conv-package">Pacchetto: <?= htmlspecialchars($c['pacchetto_titolo']) ?></p>
                    <p class="conv-last-msg"><?= $c['last_message'] ? htmlspecialchars(substr($c['last_message'], 0, 60)) : '<i>Nessun messaggio</i>' ?></p>
                  </div>
                  <div class="conv-right">
                    <?php if ($c['last_message_time']): ?>
                      <span class="conv-time"><?= date('d/m H:i', strtotime($c['last_message_time'])) ?></span>
                    <?php endif; ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
                  </div>
                </a>
                <button type="button" onclick="deleteConversation(<?= $c['conversation_id'] ?>, true)" style="background:none; border:none; color:var(--muted); cursor:pointer; padding:0.5rem; transition:color 0.2s; display:flex; align-items:center; justify-content:center; flex-shrink:0;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='var(--muted)'" title="Elimina conversazione">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    </main>
  </div>
</div>

<!-- Modal Pacchetto -->
<div class="modal-overlay" id="pkg-modal">
  <div class="modal-box" style="max-height: 90vh; overflow-y: auto;">
    <div class="modal-header">
      <h3 id="pkg-modal-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        Nuovo Pacchetto
      </h3>
      <button type="button" class="modal-close" onclick="closePkgModal()">✕</button>
    </div>
    <form method="POST" action="./../../api/admin_pacchetti_save.php" enctype="multipart/form-data">
      <input type="hidden" id="pkg-id" name="pacchetto_id" value="">
      
      <div class="modal-group">
        <label>Titolo *</label>
        <input type="text" id="pkg-titolo" name="titolo" required>
      </div>

      <div class="modal-group">
        <label>Descrizione *</label>
        <textarea id="pkg-descrizione" name="descrizione" rows="3" required style="width:100%; padding:.65rem .9rem; border:1.5px solid var(--border-2); border-radius:var(--radius); font-family:'Inter',sans-serif; font-size:.9rem; resize:vertical;"></textarea>
      </div>

      <div class="modal-group">
        <label>Prezzo (€) *</label>
        <input type="number" id="pkg-prezzo" name="prezzo" step="0.01" required>
      </div>

      <div class="modal-group">
        <label>Link Esterno</label>
        <input type="url" id="pkg-link" name="link_esterno" placeholder="https://...">
      </div>

      <div class="modal-group">
        <label>Localizzazione</label>
        <div style="display:flex; gap:.5rem; margin-bottom:.5rem;">
          <input type="text" id="pkg-geocode-input" placeholder="Cerca località..." style="flex:1; padding:.65rem .9rem; border:1.5px solid var(--border-2); border-radius:var(--radius); font-size:.9rem;">
          <button type="button" onclick="pkgGeocodeSearch()" class="btn btn-primary" style="white-space:nowrap;">Cerca</button>
        </div>
        <div id="pkg-map-pick" style="height:300px; border-radius:var(--radius); border:1px solid var(--border); margin-bottom:.75rem;"></div>
        <div id="pkg-coord-display" style="display:none; align-items:center; gap:.5rem; padding:.75rem; background:var(--surface); border-radius:var(--radius); margin-bottom:.75rem;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span id="pkg-coord-text" style="flex:1; font-size:.85rem; color:var(--ink-mid);"></span>
        </div>
        <input type="hidden" id="pkg-localita" name="localita">
        <input type="hidden" id="pkg-lat" name="latitudine">
        <input type="hidden" id="pkg-lng" name="longitudine">
      </div>

      <div class="modal-group">
        <label>Immagini</label>
        <input type="file" name="immagini[]" multiple accept="image/*" style="padding:.65rem .9rem; border:1.5px solid var(--border-2); border-radius:var(--radius); width:100%;">
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closePkgModal()">Annulla</button>
        <button type="submit" class="btn-save">Salva</button>
      </div>
    </form>
  </div>
</div>

<script>
  function showSection(name, btn) {
    document.querySelectorAll('.agency-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.agency-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + name).classList.add('active');
    btn.classList.add('active');
  }

  function openPkgModal(pkg = null) {
    initPkgMap();
    if (pkg) {
      document.getElementById('pkg-id').value = pkg.id;
      document.getElementById('pkg-titolo').value = pkg.titolo;
      document.getElementById('pkg-prezzo').value = pkg.prezzo;
      document.getElementById('pkg-descrizione').value = pkg.descrizione;
      document.getElementById('pkg-link').value = pkg.link_esterno || '';
      document.getElementById('pkg-localita').value = pkg.localita;
      document.getElementById('pkg-lat').value = pkg.latitudine;
      document.getElementById('pkg-lng').value = pkg.longitudine;
      
      if (pkg.latitudine && pkg.longitudine) {
        pkgMap.setView([pkg.latitudine, pkg.longitudine], 10);
        setPkgMarker(pkg.latitudine, pkg.longitudine, pkg.localita);
      }
      document.getElementById('pkg-modal-title').textContent = 'Modifica Pacchetto';
    } else {
      document.getElementById('pkg-id').value = '';
      document.getElementById('pkg-titolo').value = '';
      document.getElementById('pkg-prezzo').value = '';
      document.getElementById('pkg-descrizione').value = '';
      document.getElementById('pkg-link').value = '';
      document.getElementById('pkg-localita').value = '';
      document.getElementById('pkg-lat').value = '';
      document.getElementById('pkg-lng').value = '';
      if (pkgMarker) pkgMap.removeLayer(pkgMarker);
      document.getElementById('pkg-coord-display').style.display = 'none';
      document.getElementById('pkg-modal-title').textContent = 'Nuovo Pacchetto';
    }
    document.getElementById('pkg-modal').classList.add('open');
    setTimeout(() => pkgMap.invalidateSize(), 200);
  }

  function closePkgModal() {
    document.getElementById('pkg-modal').classList.remove('open');
  }
  document.getElementById('pkg-modal').addEventListener('click', function(e) {
    if (e.target === this) closePkgModal();
  });

  function editPackage(pkg) {
    openPkgModal(pkg);
  }

  // Mappa per i pacchetti
  let pkgMap = null;
  let pkgMarker = null;

  function initPkgMap() {
    if (pkgMap) return;
    pkgMap = L.map('pkg-map-pick').setView([41.9, 12.5], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(pkgMap);
    
    pkgMap.on('click', async function(e) {
      const { lat, lng } = e.latlng;
      try {
        const res  = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
        const data = await res.json();
        setPkgMarker(lat, lng, data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`);
      } catch { setPkgMarker(lat, lng, `${lat.toFixed(5)}, ${lng.toFixed(5)}`); }
    });
  }

  function setPkgMarker(lat, lng, label) {
    if (pkgMarker) pkgMap.removeLayer(pkgMarker);
    pkgMarker = L.marker([lat, lng]).addTo(pkgMap);
    document.getElementById('pkg-lat').value = lat;
    document.getElementById('pkg-lng').value = lng;
    document.getElementById('pkg-localita').value = label;
    document.getElementById('pkg-coord-text').textContent = label.substring(0, 70) + '...';
    document.getElementById('pkg-coord-display').style.display = 'flex';
  }

  async function pkgGeocodeSearch() {
    const query = document.getElementById('pkg-geocode-input').value.trim();
    if (!query) return;
    try {
      const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`);
      const data = await res.json();
      if (!data.length) { alert('Nessun risultato trovato.'); return; }
      pkgMap.setView([+data[0].lat, +data[0].lon], 10);
      setPkgMarker(+data[0].lat, +data[0].lon, data[0].display_name);
    } catch { alert('Errore durante la ricerca.'); }
  }

  function deleteConversation(conversationId, isPackage) {
    if (!confirm('Sei sicuro di voler eliminare questa conversazione? Non potrai recuperarla.')) return;
    
    const formData = new FormData();
    if (isPackage) {
      formData.append('package_conversation_id', conversationId);
      fetch('../../api/delete_package_conversation.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
          if (d.success) location.reload();
          else alert('Errore: ' + d.error);
        })
        .catch(e => alert('Errore: ' + e.message));
    } else {
      formData.append('conversation_id', conversationId);
      fetch('../../api/delete_conversation.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
          if (d.success) location.reload();
          else alert('Errore: ' + d.error);
        })
        .catch(e => alert('Errore: ' + e.message));
    }
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
