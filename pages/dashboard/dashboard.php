<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo  = getPDO();

$stmt = $pdo->prepare('SELECT * FROM viaggi WHERE user_id = :uid ORDER BY data_inizio DESC');
$stmt->execute([':uid' => $user['id']]);
$viaggi = $stmt->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $eStmt = $pdo->prepare('SELECT * FROM viaggi WHERE id = :id AND user_id = :uid');
    $eStmt->execute([':id' => (int)$_GET['edit'], ':uid' => $user['id']]);
    $edit = $eStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>I miei viaggi — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <div class="page-header">
    <h1>Ciao, <?= htmlspecialchars($user['nome']) ?> 👋</h1>
    <p>Gestisci i tuoi viaggi e trova compagni di avventura in tutto il mondo.</p>
  </div>

  <div class="dashboard-layout">

    <!-- Sidebar: form aggiunta/modifica viaggio -->
    <div class="form-card">
      <h3>
        <?php if ($edit): ?>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Modifica viaggio
        <?php else: ?>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
          Nuovo viaggio
        <?php endif; ?>
      </h3>

      <form method="POST" action="./../../api/viaggio_save.php" id="vForm">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <?php endif; ?>

        <div class="input-wrap">
          <label>Data inizio *</label>
          <input type="date" name="data_inizio" required value="<?= htmlspecialchars($edit['data_inizio'] ?? '') ?>">
        </div>
        <div class="input-wrap">
          <label>Data fine *</label>
          <input type="date" name="data_fine" required value="<?= htmlspecialchars($edit['data_fine'] ?? '') ?>">
        </div>

        <div class="input-wrap">
          <label>Destinazione *</label>
          <div class="search-bar">
            <input type="text" id="geocode-input" placeholder="Cerca una città…">
            <button type="button" onclick="geocodeSearch()" title="Cerca">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </button>
          </div>
          <div id="map-pick"></div>
          <p class="map-hint">Oppure clicca direttamente sulla mappa per selezionare la destinazione.</p>
        </div>

        <input type="hidden" id="destinazione" name="destinazione" value="<?= htmlspecialchars($edit['destinazione'] ?? '') ?>">
        <input type="hidden" id="latitudine"   name="latitudine"   value="<?= htmlspecialchars($edit['latitudine']   ?? '') ?>">
        <input type="hidden" id="longitudine"  name="longitudine"  value="<?= htmlspecialchars($edit['longitudine']  ?? '') ?>">

        <div class="coord-display" id="coord-display" style="<?= $edit ? '' : 'display:none;' ?>">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg>
          <span id="coord-text"><?= $edit ? htmlspecialchars($edit['destinazione']) : '' ?></span>
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
          <button type="submit" class="btn btn-primary" style="flex:1;">
            <?php if ($edit): ?>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Salva modifiche
            <?php else: ?>
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
              Aggiungi viaggio
            <?php endif; ?>
          </button>
          <?php if ($edit): ?>
            <a href="dashboard.php" class="btn btn-ghost">Annulla</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Main: lista viaggi -->
    <div class="trips-section">
      <h2>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        I tuoi viaggi
        <span class="trips-count-badge"><?= count($viaggi) ?></span>
      </h2>

      <?php if (empty($viaggi)): ?>
        <div class="empty-state">
          <span class="empty-icon">🗺️</span>
          <strong>Nessun viaggio ancora</strong>
          <p>Aggiungi il tuo primo viaggio usando il form qui a fianco e inizia a trovare compagni di avventura!</p>
        </div>
      <?php else: ?>
        <div class="trips-grid">
          <?php foreach ($viaggi as $i => $v): ?>
            <div class="trip-card" style="animation-delay:<?= $i * 0.04 ?>s">
              <div class="trip-card-left">
                <div class="trip-dest">
                  <span class="trip-dest-pin"></span>
                  <?= htmlspecialchars(explode(',', $v['destinazione'])[0]) ?>
                </div>
                <div class="trip-date">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?= date('d/m/Y', strtotime($v['data_inizio'])) ?> → <?= date('d/m/Y', strtotime($v['data_fine'])) ?>
                </div>
              </div>
              <div class="trip-actions">
                <a href="dashboard.php?edit=<?= $v['id'] ?>" class="btn-edit">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Modifica
                </a>
                <form method="POST" action="../../api/viaggio_delete.php"
                      onsubmit="return confirm('Vuoi eliminare questo viaggio?')" style="margin:0;">
                  <input type="hidden" name="id" value="<?= $v['id'] ?>">
                  <button type="submit" class="btn-delete">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  </button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const map = L.map('map-pick').setView([46.0, 12.0], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
  let marker = null;

  function setMarker(lat, lng, label) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map).bindPopup(label).openPopup();
    document.getElementById('latitudine').value   = parseFloat(lat).toFixed(7);
    document.getElementById('longitudine').value  = parseFloat(lng).toFixed(7);
    document.getElementById('destinazione').value = label;
    const shortLabel = label.substring(0, 80);
    document.getElementById('coord-text').textContent = shortLabel;
    document.getElementById('coord-display').style.display = 'flex';
  }

  map.on('click', async function(e) {
    const { lat, lng } = e.latlng;
    try {
      const res  = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
      const data = await res.json();
      setMarker(lat, lng, data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`);
    } catch { setMarker(lat, lng, `${lat.toFixed(5)}, ${lng.toFixed(5)}`); }
  });

  async function geocodeSearch() {
    const query = document.getElementById('geocode-input').value.trim();
    if (!query) return;
    try {
      const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`);
      const data = await res.json();
      if (!data.length) { alert('Nessun risultato trovato.'); return; }
      map.setView([+data[0].lat, +data[0].lon], 10);
      setMarker(+data[0].lat, +data[0].lon, data[0].display_name);
    } catch { alert('Errore durante la ricerca.'); }
  }

  document.getElementById('geocode-input').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); geocodeSearch(); }
  });

  const preLat  = "<?= htmlspecialchars($edit['latitudine']   ?? '') ?>";
  const preLng  = "<?= htmlspecialchars($edit['longitudine']  ?? '') ?>";
  const preDest = "<?= htmlspecialchars($edit['destinazione'] ?? '') ?>";
  if (preLat && preLng) { map.setView([+preLat, +preLng], 8); setMarker(+preLat, +preLng, preDest); }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
