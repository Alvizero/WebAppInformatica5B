<?php
require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
require_once __DIR__ . '/../../shared/lingue_list.php';
requireLogin();

if (isAgency()) {
    header('Location: ../agency/agency.php');
    exit;
}

$pdo = getPDO();
// Carichiamo tutte le nazionalità dal database
$stmtNaz = $pdo->query('SELECT id, nome FROM nazionalita ORDER BY nome ASC');
$allNazionalita = $stmtNaz->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cerca compagni di viaggio — FrienTrip</title>
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
        <select id="f-lingua" class="form-select">
          <option value="">Tutte le lingue</option>
          <?php foreach (getAllLingue($pdo) as $l): ?>
            <option value="<?= (int)$l['id'] ?>"><?= htmlspecialchars($l['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-section">
        <label class="filter-label">Nazionalità <span style="font-size:.7rem;color:var(--muted);">opzionale</span></label>
        <select id="f-nazionalita" class="form-select">
          <option value="">Tutte le nazionalità</option>
          <?php foreach ($allNazionalita as $naz): ?>
            <option value="<?= (int)$naz['id'] ?>"><?= htmlspecialchars($naz['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="filter-section">
        <label class="filter-label">Periodo di viaggio</label>
        <div class="date-grid">
          <input type="date" id="f-inizio" title="Data inizio" oninput="validateSearchDates()">
          <input type="date" id="f-fine" title="Data fine" oninput="validateSearchDates()">
        </div>
        <div id="error-search-inizio" class="inline-error" style="display:none; color:var(--error); font-size:0.7rem; margin-top:0.25rem; font-weight:600;">La data deve essere odierna o successiva.</div>
        <div id="error-search-fine" class="inline-error" style="display:none; color:var(--error); font-size:0.7rem; margin-top:0.25rem; font-weight:600;">La data fine deve essere successiva all'inizio.</div>
      </div>

      <div class="filter-section">
        <label class="filter-label">Destinazione (Città, Hotel, ecc.) *</label>
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
function validateSearchDates() {
    const inizioInput = document.getElementById('f-inizio');
    const fineInput   = document.getElementById('f-fine');
    const errInizio   = document.getElementById('error-search-inizio');
    const errFine     = document.getElementById('error-search-fine');
    const today       = new Date().toISOString().split('T')[0];
    
    let isValid = true;

    if (inizioInput.value && inizioInput.value < today) {
      errInizio.style.display = 'block';
      inizioInput.style.borderColor = 'var(--error)';
      isValid = false;
    } else {
      errInizio.style.display = 'none';
      inizioInput.style.borderColor = '';
    }

    if (fineInput.value && inizioInput.value && fineInput.value < inizioInput.value) {
      errFine.style.display = 'block';
      fineInput.style.borderColor = 'var(--error)';
      isValid = false;
    } else {
      errFine.style.display = 'none';
      fineInput.style.borderColor = '';
    }

    return isValid;
  }

  async function searchUsers() {
    const lingua_id   = document.getElementById('f-lingua').value;
    const nazionalita = document.getElementById('f-nazionalita').value.trim();
    const inizio      = document.getElementById('f-inizio').value;
    const fine        = document.getElementById('f-fine').value;
    const lat         = document.getElementById('f-lat').value;
    const lng         = document.getElementById('f-lng').value;
    const raggio      = document.getElementById('f-raggio').value;
    const citta       = document.getElementById('f-citta').value.trim();

    if (!inizio || !fine) {
      showToast('Inserisci le date del tuo viaggio.', 'warning');
      return;
    }

    if (!validateSearchDates()) return;

    // Salva ricerca recente
    saveRecentSearch({ lingua_id, nazionalita, inizio, fine, lat, lng, raggio, citta });

    const btn = document.querySelector('.btn-search-main');
    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg> Ricerca in corso…`;
    btn.disabled = true;

    const params = new URLSearchParams({ lingua_id, nazionalita, data_inizio: inizio, data_fine: fine, citta });
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
      count.innerHTML = `<span style="display:inline-flex;align-items:center;gap:.4rem;background:#f0fdf4;color:#16a34a;padding:.3rem .75rem;border-radius:999px;border:1px solid rgba(22,163,74,.2);font-size:.8rem;font-weight:700;"><svg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='3' stroke-linecap='round'><polyline points='20 6 9 17 4 12'/></svg>${users.length} compagno${users.length > 1 ? 'i' : ''} trovato${users.length > 1 ? 'i' : ''}</span>`;
    } else {
      count.innerHTML = `<div style="padding:1.5rem .5rem;text-align:center;"><div style="font-size:1.8rem;margin-bottom:.5rem;opacity:.4;">🔍</div><div style="color:var(--ink);font-size:.85rem;font-weight:600;margin-bottom:.25rem;">Nessun risultato</div><div style="color:var(--muted);font-size:.78rem;line-height:1.5;">Prova ad ampliare il raggio o modificare i filtri.</div></div>`;
    }

    if (lat && lng && raggio) {
      circleLayer = L.circle([+lat, +lng], {
        radius: raggio * 1000,
        color: '#2563eb', fillColor: '#2563eb', fillOpacity: .05, weight: 1.5, dashArray: '6,4'
      }).addTo(map);
    }

    users.forEach((u, i) => {
      const isSelf = parseInt(u.user_id) === parseInt(currentUserId);
      const popup = `<div style="font-family:'Inter',system-ui,sans-serif;min-width:200px;max-width:240px;padding:.1rem 0;">
        <!-- header avatar + nome -->
        <div style="display:flex;align-items:center;gap:.65rem;margin-bottom:.7rem;">
          <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2563eb 0%,#60a5fa 100%);color:#fff;font-weight:800;font-size:.8rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(37,99,235,.3);letter-spacing:.03em;">
            ${u.nome[0]}${u.cognome[0]}
          </div>
          <div style="min-width:0;">
            <div style="font-weight:700;font-size:.92rem;color:#111827;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escHtml(u.nome)} ${escHtml(u.cognome)}</div>
            <div style="font-size:.72rem;color:#6b7280;margin-top:.1rem;">${escHtml(u.nazionalita)} · ${escHtml(u.lingua)}</div>
          </div>
        </div>
        <!-- destinazione -->
        <div style="display:flex;align-items:center;gap:.35rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:7px;padding:.4rem .6rem;margin-bottom:.6rem;font-size:.78rem;font-weight:500;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg>
          ${escHtml(u.destinazione)}
        </div>
        <!-- date -->
        <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.75rem;">
          <div style="flex:1;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:.3rem .5rem;text-align:center;">
            <div style="font-size:.58rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Dal</div>
            <div style="font-size:.78rem;font-weight:700;color:#111827;">${formatDate(u.data_inizio)}</div>
          </div>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="3" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          <div style="flex:1;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:.3rem .5rem;text-align:center;">
            <div style="font-size:.58rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;font-weight:700;">Al</div>
            <div style="font-size:.78rem;font-weight:700;color:#111827;">${formatDate(u.data_fine)}</div>
          </div>
        </div>
        <!-- CTA -->
        ${isSelf
          ? `<div style="text-align:center;font-size:.75rem;color:#9ca3af;font-style:italic;padding:.25rem 0;">Questo sei tu</div>`
          : `<a href="../chat/chat.php?user_id=${u.user_id}"
               style="display:flex;align-items:center;justify-content:center;gap:.4rem;width:100%;padding:.55rem 1rem;background:#2563eb;color:#fff;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;box-shadow:0 3px 10px rgba(37,99,235,.35);transition:background .15s ease;font-family:inherit;"
               onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Invia messaggio
            </a>`}
      </div>`;

      const marker = L.marker([+u.latitudine, +u.longitudine], { icon: customIcon })
        .addTo(markersLayer)
        .bindPopup(popup);

      // Card in sidebar
      const card = document.createElement('div');
      card.className = 'user-card';
      card.innerHTML = `
        <div class="user-card-header">
          <div class="user-avatar">${u.nome[0]}${u.cognome[0]}</div>
          <div class="uc-info">
            <div class="uc-name">${escHtml(u.nome)} ${escHtml(u.cognome)}</div>
            <div class="uc-meta">${escHtml(u.nazionalita)} · ${escHtml(u.lingua)}</div>
          </div>
        </div>
        ${u.distanza_km ? `<div class="user-dist">📍 ${u.distanza_km} km</div>` : '<div></div>'}
        <div class="user-card-footer">
          <div class="user-dest-box">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg>
            ${escHtml(u.destinazione)}
          </div>
          <div class="user-dates">
            <span>${formatDate(u.data_inizio)}</span>
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            <span>${formatDate(u.data_fine)}</span>
          </div>
          ${!isSelf
            ? `<a href="../chat/chat.php?user_id=${u.user_id}" onclick="event.stopPropagation()" class="user-card-cta">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Scrivi
              </a>`
            : `<span class="user-self-badge">Tu</span>`}
        </div>
      `;
      card.onclick = () => {
        map.setView([+u.latitudine, +u.longitudine], 12);
        marker.openPopup();
      };
      list.appendChild(card);
    });

    if (users.length > 0 && lat && lng) {
      // Non facciamo nulla, il cerchio già inquadra l'area
    } else if (users.length > 0) {
      const group = new L.featureGroup(markersLayer.getLayers());
      map.fitBounds(group.getBounds().pad(0.1));
    }
  }

  // ── Ricerche Recenti ──
  function saveRecentSearch(s) {
    if (!s.citta) return;
    let searches = JSON.parse(localStorage.getItem('recent_searches') || '[]');
    // Rimuovi duplicati
    searches = searches.filter(x => x.citta !== s.citta);
    searches.unshift(s);
    localStorage.setItem('recent_searches', JSON.stringify(searches.slice(0, 5)));
    renderRecentSearches();
  }

  function renderRecentSearches() {
    const searches = JSON.parse(localStorage.getItem('recent_searches') || '[]');
    const container = document.getElementById('recent-searches-list');
    const section = document.getElementById('recent-searches-section');
    if (!searches.length) { section.style.display = 'none'; return; }
    section.style.display = 'block';
    container.innerHTML = '';
    searches.forEach(s => {
      const btn = document.createElement('button');
      btn.className = 'recent-search-tag';
      btn.innerHTML = `<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg> ${escHtml(s.citta)}`;
      btn.onclick = () => {
        document.getElementById('f-citta').value = s.citta;
        document.getElementById('f-lat').value = s.lat;
        document.getElementById('f-lng').value = s.lng;
        document.getElementById('f-lingua').value = s.lingua_id || '';
        document.getElementById('f-nazionalita').value = s.nazionalita;
        document.getElementById('f-inizio').value = s.inizio;
        document.getElementById('f-fine').value = s.fine;
        document.getElementById('f-raggio').value = s.raggio;
        document.getElementById('raggio-val').textContent = s.raggio;
        geocodeCitta();
        searchUsers();
      };
      container.appendChild(btn);
    });
  }

  // ── Utility ──
  function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
  function formatDate(d) {
    if (!d) return '';
    const parts = d.split('-');
    return `${parts[2]}/${parts[1]}/${parts[0].slice(2)}`;
  }
  function showToast(msg, type='info') {
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
  }

  renderRecentSearches();
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
