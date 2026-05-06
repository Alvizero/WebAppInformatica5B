<?php
require_once __DIR__ . '/../../shared/auth.php';
requireLogin();

if (isAgency()) {
    header('Location: ../agency/agency.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cerca compagni di viaggio — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="map_view.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="map-layout">
  <!-- Sidebar filtri -->
  <div class="map-sidebar">
    <div class="map-sidebar-header">
      <h2>Trova compagni di viaggio</h2>
      <p>Imposta i filtri e scopri chi viaggia come te</p>
    </div>

    <div class="sidebar-body">

      <div class="filter-section" id="recent-searches-section" style="display:none;">
        <label class="filter-label">Ricerche recenti</label>
        <div id="recent-searches-list" style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.5rem;"></div>
        <hr class="divider-sm">
      </div>

      <div class="filter-section">
        <label class="filter-label">Lingua parlata <span style="font-size:.7rem;color:var(--muted);">opzionale</span></label>
        <input type="text" id="f-lingua" placeholder="es. Italiano, English…">
      </div>

      <div class="filter-section">
        <label class="filter-label">Nazionalità <span style="font-size:.7rem;color:var(--muted);">opzionale</span></label>
        <input type="text" id="f-nazionalita" placeholder="es. Italiana, Francese…">
      </div>

      <div class="filter-section">
        <label class="filter-label">Periodo di viaggio</label>
        <div class="date-grid">
          <input type="date" id="f-inizio" title="Data inizio">
          <input type="date" id="f-fine" title="Data fine">
        </div>
      </div>

      <div class="filter-section">
        <label class="filter-label">Destinazione</label>
        <div class="search-bar-map">
          <input type="text" id="f-citta" placeholder="es. Barcellona, Roma…">
          <button type="button" onclick="geocodeCitta()" title="Cerca città">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </button>
        </div>
        <input type="hidden" id="f-lat">
        <input type="hidden" id="f-lng">
        <div id="citta-display" style="display:none;"></div>
      </div>

      <div class="filter-section">
        <div class="range-wrap">
          <div class="range-header">
            <label class="filter-label" style="margin:0;">Raggio di ricerca</label>
            <span class="range-val-badge"><span id="raggio-val">10</span> km</span>
          </div>
          <input type="range" id="f-raggio" min="5" max="67" step="5" value="10"
            oninput="document.getElementById('raggio-val').textContent = this.value">
        </div>
      </div>

      <hr class="divider-sm">

      <button class="btn-search-main" onclick="searchUsers()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Cerca sulla mappa
      </button>

      <div class="results-count" id="result-count"></div>
      
      <!-- Sezione Pacchetti -->
      <div id="packages-section" style="display:none; margin-bottom: 1.5rem;">
        <h3 style="font-size: 0.9rem; color: var(--brand); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 12V8H4v4M2 6h20v2H2zM4 12h16v8H4zM12 12v8M8 12v8M16 12v8"/></svg>
          Offerte per te
        </h3>
        <div id="packages-list" style="display: flex; flex-direction: column; gap: 0.75rem;"></div>
      </div>

      <div id="results-list"></div>
    </div>
  </div>

  <!-- Mappa -->
  <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  // ── Mappa ──
  const map = L.map('map', { zoomControl: true }).setView([46.0, 12.0], 4);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  let markersLayer = L.layerGroup().addTo(map);
  let circleLayer  = null;

  // Custom marker icon
  const customIcon = L.divIcon({
    html: `<div style="
      width:32px;height:32px;border-radius:50% 50% 50% 0;
      background:var(--brand,#2563eb);
      border:3px solid #fff;
      box-shadow:0 3px 10px rgba(37,99,235,.4);
      transform:rotate(-45deg);
      display:flex;align-items:center;justify-content:center;
    "><div style="transform:rotate(45deg);color:#fff;font-size:12px;font-weight:700;">✦</div></div>`,
    className: '',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -36]
  });

  // ── Geocoding ──
  async function geocodeCitta() {
    const query = document.getElementById('f-citta').value.trim();
    if (!query) return;
    const btn = document.querySelector('.search-bar-map button');
    btn.style.opacity = '.6';
    try {
      const res  = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`);
      const data = await res.json();
      if (!data.length) { showToast('Città non trovata. Prova con un nome diverso.', 'error'); return; }
      const { lat, lon, display_name } = data[0];
      document.getElementById('f-lat').value = lat;
      document.getElementById('f-lng').value = lon;
      const d = document.getElementById('citta-display');
      d.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg> ${escHtml(display_name.substring(0, 55))}…`;
      d.style.display = 'flex';
      d.style.alignItems = 'center';
      d.style.gap = '.4rem';
      map.setView([+lat, +lon], 7);
    } catch { showToast('Errore durante la geocodifica.', 'error'); }
    finally { btn.style.opacity = '1'; }
  }

  document.getElementById('f-citta').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); geocodeCitta(); }
  });

  // ── Ricerca utenti ──
  async function searchUsers() {
    const lingua      = document.getElementById('f-lingua').value.trim();
    const nazionalita = document.getElementById('f-nazionalita').value.trim();
    const inizio      = document.getElementById('f-inizio').value;
    const fine        = document.getElementById('f-fine').value;
    const lat         = document.getElementById('f-lat').value;
    const lng         = document.getElementById('f-lng').value;
    const raggio      = document.getElementById('f-raggio').value;
    const citta       = document.getElementById('f-citta').value.trim();

    if (!inizio || !fine)        { showToast('Inserisci le date del tuo viaggio.', 'warning'); return; }

    // Salva ricerca recente
    saveRecentSearch({ lingua, nazionalita, inizio, fine, lat, lng, raggio, citta });

    const btn = document.querySelector('.btn-search-main');
    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg> Ricerca in corso…`;
    btn.disabled = true;

    const params = new URLSearchParams({ lingua, nazionalita, data_inizio: inizio, data_fine: fine, citta });
    if (lat && lng) { params.append('lat', lat); params.append('lng', lng); params.append('raggio', raggio); }

    try {
      const res   = await fetch(`../../api/get_users.php?${params}`);
      const data  = await res.json();
      renderResults(data.users, lat, lng, raggio, data.current_user_id);
      renderPackages(data.pacchetti);
    } catch(err) {
      showToast('Errore nel caricamento dei dati.', 'error');
      console.error(err);
    } finally {
      btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> Cerca sulla mappa`;
      btn.disabled = false;
    }
  }

  function renderPackages(packages) {
    const section = document.getElementById('packages-section');
    const list = document.getElementById('packages-list');
    list.innerHTML = '';

    if (!packages || packages.length === 0) {
      section.style.display = 'none';
      return;
    }

    section.style.display = 'block';
    packages.forEach(p => {
      const card = document.createElement('div');
      card.className = 'package-card';
      card.style.cssText = 'background:white; border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; margin-bottom:1rem; box-shadow:var(--shadow-sm);';
      
      const copertina = p.galleria && p.galleria.length > 0 ? `../../${p.galleria[0]}` : null;
      const imgHtml = copertina 
        ? `<img src="${copertina}" style="width:100%; height:120px; object-fit:cover;">`
        : `<div style="width:100%; height:120px; background:var(--surface); display:flex; align-items:center; justify-content:center; font-size:2rem;">🏝️</div>`;

      let galleriaHtml = '';
      if (p.galleria && p.galleria.length > 1) {
        galleriaHtml = `<div style="display:flex; gap:4px; padding:4px; overflow-x:auto;">`;
        p.galleria.slice(1, 5).forEach(img => {
          galleriaHtml += `<img src="../../${img}" style="width:40px; height:40px; object-fit:cover; border-radius:2px; flex-shrink:0;">`;
        });
        if (p.galleria.length > 5) galleriaHtml += `<div style="width:40px; height:40px; background:rgba(0,0,0,0.5); color:white; display:flex; align-items:center; justify-content:center; font-size:0.7rem; border-radius:2px;">+${p.galleria.length - 5}</div>`;
        galleriaHtml += `</div>`;
      }

      card.innerHTML = `
        ${imgHtml}
        ${galleriaHtml}
        <div style="padding:0.75rem;">
          <div style="font-weight:700; font-size:0.95rem; color:var(--ink); margin-bottom:0.25rem;">${escHtml(p.titolo)}</div>
          <div style="font-size:0.75rem; color:var(--muted); margin-bottom:0.5rem;">📍 ${escHtml(p.localita)}</div>
          <div style="background:var(--surface); padding:0.5rem; border-radius:4px; margin-bottom:0.75rem;">
            <div style="font-size:0.7rem; font-weight:700; color:var(--muted); text-transform:uppercase;">Agenzia</div>
            <div style="font-size:0.8rem; font-weight:600; color:var(--ink);">${escHtml(p.nome_agenzia)}</div>
            <div style="font-size:0.75rem; color:var(--brand);">${escHtml(p.email_agenzia)}</div>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
            <span style="font-weight:800; color:var(--brand); font-size:1.1rem;">€${parseFloat(p.prezzo).toFixed(2)}</span>
            <div style="display:flex; gap:0.5rem;">
              ${p.link_esterno ? `<a href="${p.link_esterno}" target="_blank" class="btn btn-ghost" style="padding:0.4rem; font-size:0.75rem;" title="Sito Web">🌐</a>` : ''}
              <a href="../chat/package_chat.php?pkg_id=${p.id}" class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.75rem;">Contatta</a>
            </div>
          </div>
        </div>
      `;
      list.appendChild(card);
    });
  }

  function renderResults(users, lat, lng, raggio, currentUserId) {
    markersLayer.clearLayers();
    if (circleLayer) { map.removeLayer(circleLayer); circleLayer = null; }

    const list  = document.getElementById('results-list');
    const count = document.getElementById('result-count');
    list.innerHTML = '';

    if (users.length > 0) {
      count.innerHTML = `<span style="color:var(--success)">✓</span> ${users.length} compagno${users.length > 1 ? 'i' : ''} trovato${users.length > 1 ? 'i' : ''}`;
    } else {
      count.innerHTML = `<span style="color:var(--muted)">Nessun risultato con questi filtri.</span>`;
    }

    if (lat && lng && raggio) {
      circleLayer = L.circle([+lat, +lng], {
        radius: raggio * 1000,
        color: '#2563eb', fillColor: '#2563eb', fillOpacity: .05, weight: 1.5, dashArray: '6,4'
      }).addTo(map);
    }

    users.forEach((u, i) => {
      const isSelf = parseInt(u.user_id) === parseInt(currentUserId);
      const popup = `<div style="padding:.85rem 1rem;font-family:'Inter',sans-serif;min-width:190px;max-width:240px;">
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:.9rem;color:#0f172a;margin-bottom:.5rem;">${escHtml(u.nome)} ${escHtml(u.cognome)}</div>
        <div style="font-size:.8rem;color:#64748b;line-height:1.8;">
          🌍 ${escHtml(u.nazionalita)} &nbsp;·&nbsp; 🗣 ${escHtml(u.lingua)}<br>
          📍 ${escHtml(u.destinazione.substring(0,50))}…<br>
          📅 ${u.data_inizio} → ${u.data_fine}
        </div>
        <div style="margin-top: 1rem; display: flex; gap: .5rem; align-items: center;">
          ${u.distanza_km ? `<div style="font-size:.75rem;font-weight:700;color:#2563eb;background:#eff6ff;padding:.2rem .55rem;border-radius:999px;display:inline-block;">📏 ${parseFloat(u.distanza_km).toFixed(1)} km</div>` : ''}
          ${!isSelf ? `<a href="../chat/chat.php?user_id=${u.user_id}" style="text-decoration:none; background:var(--brand); color:white; padding: .4rem .8rem; border-radius: 6px; font-size: .8rem; font-weight: 600; flex: 1; text-align: center;">Contatta</a>` : '<span style="font-size:.75rem; color:var(--muted); font-style:italic; flex:1; text-align:center;">Tu</span>'}
        </div>
      </div>`;
      
      const m = L.marker([parseFloat(u.latitudine), parseFloat(u.longitudine)], { icon: customIcon }).bindPopup(popup);
      markersLayer.addLayer(m);

      const card = document.createElement('div');
      card.className = 'result-card';
      card.style.animationDelay = (i * 0.04) + 's';
      card.innerHTML = `
        <div class="result-card-name">${escHtml(u.nome)} ${escHtml(u.cognome)}</div>
        <div class="result-card-meta">
          🌍 ${escHtml(u.nazionalita)} &nbsp;·&nbsp; 🗣 ${escHtml(u.lingua)}<br>
          📅 ${u.data_inizio} → ${u.data_fine}
        </div>
        <div style="display:flex; justify-content: space-between; align-items: center; margin-top: .5rem;">
          ${u.distanza_km ? `<span class="result-card-dist" style="margin:0;">📏 ${parseFloat(u.distanza_km).toFixed(1)} km</span>` : '<span></span>'}
          ${!isSelf ? `<a href="../chat/chat.php?user_id=${u.user_id}" class="btn btn-primary" style="padding: .3rem .7rem; font-size: .75rem; height: auto;">Contatta</a>` : '<span style="font-size:.75rem; color:var(--muted); font-style:italic;">Tu</span>'}
        </div>`;
      
      card.addEventListener('click', () => {
        map.setView([parseFloat(u.latitudine), parseFloat(u.longitudine)], 10);
        m.openPopup();
      });
      list.appendChild(card);
    });

    if (users.length > 0) {
      const bounds = markersLayer.getLayers().map(l => l.getLatLng());
      map.fitBounds(L.latLngBounds(bounds).pad(0.3));
    }
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  // ── Ricerche Recenti ──
  function saveRecentSearch(search) {
    let recent = JSON.parse(localStorage.getItem('recent_searches') || '[]');
    // Rimuovi duplicati (stessa città e date)
    recent = recent.filter(s => !(s.citta === search.citta && s.inizio === search.inizio && s.fine === search.fine));
    recent.unshift(search);
    recent = recent.slice(0, 5); // Tieni solo le ultime 5
    localStorage.setItem('recent_searches', JSON.stringify(recent));
    renderRecentSearches();
  }

  function renderRecentSearches() {
    const recent = JSON.parse(localStorage.getItem('recent_searches') || '[]');
    const container = document.getElementById('recent-searches-section');
    const list = document.getElementById('recent-searches-list');
    
    if (recent.length === 0) {
      container.style.display = 'none';
      return;
    }

    container.style.display = 'block';
    list.innerHTML = '';
    
    recent.forEach(s => {
      const chip = document.createElement('div');
      chip.style.cssText = 'background:var(--surface); border:1px solid var(--border); padding:0.3rem 0.6rem; border-radius:999px; font-size:0.75rem; cursor:pointer; transition:all 0.2s;';
      chip.innerHTML = `📍 ${escHtml(s.citta || 'Mappa')} (${s.inizio})`;
      chip.onclick = () => applySearch(s);
      chip.onmouseover = () => chip.style.borderColor = 'var(--brand)';
      chip.onmouseout = () => chip.style.borderColor = 'var(--border)';
      list.appendChild(chip);
    });
  }

  function applySearch(s) {
    document.getElementById('f-lingua').value = s.lingua || '';
    document.getElementById('f-nazionalita').value = s.nazionalita || '';
    document.getElementById('f-inizio').value = s.inizio || '';
    document.getElementById('f-fine').value = s.fine || '';
    document.getElementById('f-lat').value = s.lat || '';
    document.getElementById('f-lng').value = s.lng || '';
    document.getElementById('f-raggio').value = s.raggio || 10;
    document.getElementById('raggio-val').textContent = s.raggio || 10;
    document.getElementById('f-citta').value = s.citta || '';
    
    if (s.citta) {
      const d = document.getElementById('citta-display');
      d.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg> ${escHtml(s.citta)}`;
      d.style.display = 'flex';
    }
    
    searchUsers();
  }

  // Inizializza ricerche recenti al caricamento
  document.addEventListener('DOMContentLoaded', renderRecentSearches);
</script>
<style>
  @keyframes spin { to { transform: rotate(360deg); } }
  @keyframes slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
</style>
<script src="../../shared/app.js"></script>
</body>
</html>
