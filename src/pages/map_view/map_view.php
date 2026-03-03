<?php
require_once __DIR__ . '/../../shared/auth.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VacanzaMatch – Trova compagni</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="map_view.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="layout">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>🔍 Cerca compagni di viaggio</h2>

    <label for="f-lingua">La tua lingua</label>
    <input type="text" id="f-lingua" placeholder="es. Italiano">

    <label for="f-nazionalita">La tua nazionalità</label>
    <input type="text" id="f-nazionalita" placeholder="es. Italiana">

    <label for="f-inizio">Data inizio vacanza</label>
    <input type="date" id="f-inizio">

    <label for="f-fine">Data fine vacanza</label>
    <input type="date" id="f-fine">

    <label for="f-citta">Città di destinazione</label>
    <div class="search-bar">
      <input type="text" id="f-citta" placeholder="es. Barcellona">
      <button type="button" onclick="geocodeCitta()">🔍</button>
    </div>
    <input type="hidden" id="f-lat">
    <input type="hidden" id="f-lng">
    <div id="citta-display" style="font-size:.8rem;color:#0077b6;margin-bottom:.8rem;display:none;"></div>

    <label for="f-raggio">
      Raggio di ricerca: <strong><span id="raggio-val">50</span> km</strong>
    </label>
    <input type="range" id="f-raggio" min="5" max="500" step="5" value="50"
           style="padding:0;border:none;box-shadow:none;margin-bottom:1.2rem;"
           oninput="document.getElementById('raggio-val').textContent = this.value">

    <button class="btn-search" onclick="searchUsers()">Cerca sulla mappa</button>

    <div id="result-count"></div>
    <div id="results-list"></div>
  </div>

  <!-- Mappa -->
  <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const map = L.map('map').setView([46.0, 12.0], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  let markersLayer = L.layerGroup().addTo(map);
  let circleLayer  = null;

  async function geocodeCitta() {
    const query = document.getElementById('f-citta').value.trim();
    if (!query) return;
    try {
      const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`);
      const data = await res.json();
      if (data.length === 0) { alert('Città non trovata.'); return; }
      const { lat, lon, display_name } = data[0];
      document.getElementById('f-lat').value = lat;
      document.getElementById('f-lng').value = lon;
      document.getElementById('citta-display').textContent = '📍 ' + display_name.substring(0, 60) + '...';
      document.getElementById('citta-display').style.display = 'block';
      map.setView([+lat, +lon], 7);
    } catch { alert('Errore durante la geocodifica.'); }
  }

  document.getElementById('f-citta').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); geocodeCitta(); }
  });

  async function searchUsers() {
    const lingua      = document.getElementById('f-lingua').value.trim();
    const nazionalita = document.getElementById('f-nazionalita').value.trim();
    const inizio      = document.getElementById('f-inizio').value;
    const fine        = document.getElementById('f-fine').value;
    const lat         = document.getElementById('f-lat').value;
    const lng         = document.getElementById('f-lng').value;
    const raggio      = document.getElementById('f-raggio').value;

    if (!lingua && !nazionalita) { alert('Inserisci almeno lingua o nazionalità.'); return; }
    if (!inizio || !fine)        { alert('Inserisci le date del tuo viaggio.'); return; }

    const params = new URLSearchParams({ lingua, nazionalita, data_inizio: inizio, data_fine: fine });
    if (lat && lng) {
      params.append('lat', lat);
      params.append('lng', lng);
      params.append('raggio', raggio);
    }

    try {
      const res   = await fetch(`../../api/get_users.php?${params}`);
      const users = await res.json();
      renderResults(users, lat, lng, raggio);
    } catch (err) {
      alert('Errore nel caricamento dei dati.');
      console.error(err);
    }
  }

  function renderResults(users, lat, lng, raggio) {
    markersLayer.clearLayers();
    if (circleLayer) { map.removeLayer(circleLayer); circleLayer = null; }

    document.getElementById('results-list').innerHTML = '';
    document.getElementById('result-count').textContent =
      users.length > 0 ? `✅ ${users.length} compagno/i trovato/i` : '😕 Nessun risultato.';

    if (lat && lng && raggio) {
      circleLayer = L.circle([+lat, +lng], {
        radius: raggio * 1000,
        color: '#0077b6', fillColor: '#00b4d8',
        fillOpacity: 0.08, weight: 2, dashArray: '6,4'
      }).addTo(map);
    }

    users.forEach(u => {
      const popup = `
        <div style="min-width:160px">
          <strong>👤 ${escHtml(u.nome)} ${escHtml(u.cognome)}</strong><br>
          🌍 ${escHtml(u.nazionalita)} · 🗣 ${escHtml(u.lingua)}<br>
          📍 ${escHtml(u.destinazione.substring(0, 60))}...<br>
          📅 ${u.data_inizio} → ${u.data_fine}<br>
          ${u.distanza_km ? `📏 ${parseFloat(u.distanza_km).toFixed(1)} km da te` : ''}
        </div>`;

      const m = L.marker([parseFloat(u.latitudine), parseFloat(u.longitudine)]).bindPopup(popup);
      markersLayer.addLayer(m);

      const card = document.createElement('div');
      card.className = 'result-card';
      card.innerHTML = `
        <strong>${escHtml(u.nome)} ${escHtml(u.cognome)}</strong>
        <div><small>🌍 ${escHtml(u.nazionalita)} &nbsp;|&nbsp; 🗣 ${escHtml(u.lingua)}</small></div>
        <div><small>📅 ${u.data_inizio} → ${u.data_fine}</small></div>
        ${u.distanza_km ? `<div><small>📏 ${parseFloat(u.distanza_km).toFixed(1)} km</small></div>` : ''}`;
      card.addEventListener('click', () => {
        map.setView([parseFloat(u.latitudine), parseFloat(u.longitudine)], 10);
        m.openPopup();
      });
      document.getElementById('results-list').appendChild(card);
    });

    if (users.length > 0) {
      const bounds = markersLayer.getLayers().map(l => l.getLatLng());
      map.fitBounds(L.latLngBounds(bounds).pad(0.3));
    }
  }

  function escHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
