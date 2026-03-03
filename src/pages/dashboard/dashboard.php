<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo  = getPDO();

$stmt = $pdo->prepare(
    'SELECT * FROM viaggi WHERE user_id = :uid ORDER BY data_inizio DESC'
);
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
  <title>VacanzaMatch – I miei viaggi</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <h2 class="dashboard-title">
    👋 Ciao, <?= htmlspecialchars($user['nome']) ?>! I tuoi viaggi
  </h2>

  <!-- FORM aggiunta / modifica -->
  <div class="card dashboard-form-card">
    <h3><?= $edit ? '✏️ Modifica viaggio' : '➕ Aggiungi un viaggio' ?></h3>
    <form method="POST" action="./../../api/viaggio_save.php" id="vForm">
      <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= $edit['id'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div>
          <label>Data inizio *</label>
          <input type="date" name="data_inizio" required
                 value="<?= htmlspecialchars($edit['data_inizio'] ?? '') ?>">
        </div>
        <div>
          <label>Data fine *</label>
          <input type="date" name="data_fine" required
                 value="<?= htmlspecialchars($edit['data_fine'] ?? '') ?>">
        </div>

        <div class="full">
          <label>Destinazione *</label>
          <div class="search-bar">
            <input type="text" id="geocode-input" placeholder="Cerca una città o luogo...">
            <button type="button" onclick="geocodeSearch()">🔍 Cerca</button>
          </div>
          <div id="map-pick"></div>
          <p class="map-hint">Oppure clicca direttamente sulla mappa.</p>
        </div>

        <input type="hidden" id="destinazione" name="destinazione"
               value="<?= htmlspecialchars($edit['destinazione'] ?? '') ?>">
        <input type="hidden" id="latitudine"   name="latitudine"
               value="<?= htmlspecialchars($edit['latitudine']   ?? '') ?>">
        <input type="hidden" id="longitudine"  name="longitudine"
               value="<?= htmlspecialchars($edit['longitudine']  ?? '') ?>">

        <div class="full coord-display" id="coord-display"
             style="<?= $edit ? '' : 'display:none;' ?>">
          📍 <span id="coord-text">
            <?= $edit ? htmlspecialchars($edit['destinazione']) : '' ?>
          </span>
        </div>
      </div>

      <div style="display:flex;gap:1rem;margin-top:1.5rem;">
        <button type="submit" class="btn-submit" style="flex:1;">
          <?= $edit ? '💾 Salva modifiche' : '➕ Aggiungi viaggio' ?>
        </button>
        <?php if ($edit): ?>
          <a href="dashboard.php" class="btn-submit"
             style="flex:1;background:#888;text-align:center;text-decoration:none;line-height:1.2;">
            ✕ Annulla
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- LISTA viaggi -->
  <?php if (empty($viaggi)): ?>
    <div class="card empty-state">
      🗺 Nessun viaggio inserito. Aggiungine uno sopra!
    </div>
  <?php else: ?>
    <div class="trips-grid">
      <?php foreach ($viaggi as $v): ?>
        <div class="trip-card">
          <div class="trip-dest">📍 <?= htmlspecialchars(explode(',', $v['destinazione'])[0]) ?></div>
          <div class="trip-date">📅 <?= $v['data_inizio'] ?> → <?= $v['data_fine'] ?></div>
          <div class="trip-actions">
            <a href="dashboard.php?edit=<?= $v['id'] ?>" class="btn-edit">✏️ Modifica</a>
            <form method="POST" action="./../../api/viaggio_delete.php"
                  onsubmit="return confirm('Eliminare questo viaggio?')">
              <input type="hidden" name="id" value="<?= $v['id'] ?>">
              <button type="submit" class="btn-delete">🗑 Elimina</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const map = L.map('map-pick').setView([46.0, 12.0], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  let marker = null;

  function setMarker(lat, lng, label) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map).bindPopup(label).openPopup();
    document.getElementById('latitudine').value   = parseFloat(lat).toFixed(7);
    document.getElementById('longitudine').value  = parseFloat(lng).toFixed(7);
    document.getElementById('destinazione').value = label;
    document.getElementById('coord-text').textContent = label.substring(0, 80);
    document.getElementById('coord-display').style.display = 'block';
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
      if (!data.length) { alert('Nessun risultato.'); return; }
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
  if (preLat && preLng) {
    map.setView([+preLat, +preLng], 8);
    setMarker(+preLat, +preLng, preDest);
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
