<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAdmin();

$me  = currentUser();
$pdo = getPDO();

$resetMsg = getFlash('reset_msg');
$errorMsg = getFlash('reset_error');

$numUtenti  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$numViaggi  = $pdo->query("SELECT COUNT(*) FROM viaggi")->fetchColumn();
$numTickets = $pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
$numAperti  = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE stato='aperto'")->fetchColumn();

$utenti  = $pdo->query("SELECT id, nome, cognome, email, livello_utente, nazionalita, lingua, created_at FROM users ORDER BY livello_utente ASC, created_at DESC")->fetchAll();
$viaggi  = $pdo->query("SELECT v.*, u.nome, u.cognome FROM viaggi v JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC")->fetchAll();
$tickets = $pdo->query("SELECT t.*, u.nome, u.cognome FROM support_tickets t JOIN users u ON u.id = t.user_id ORDER BY t.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pannello Admin — VacanzaMatch</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="./../../shared/base.css">
  <link rel="stylesheet" href="./admin.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container admin-page">

  <?php if ($resetMsg): ?>
    <div class="alert alert-success" style="margin-bottom:1.25rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <?= htmlspecialchars($resetMsg) ?>
      <br><small style="opacity:.8;margin-top:.3rem;display:block;">Comunica la password temporanea all'utente e invitalo a cambiarla subito.</small>
    </div>
  <?php endif; ?>

  <?php if ($errorMsg): ?>
    <div class="alert alert-error" style="margin-bottom:1.25rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($errorMsg) ?>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="admin-page-header">
    <h1>
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      Pannello Amministratore
    </h1>
    <div class="admin-badge-me">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      <?= htmlspecialchars($me['nome'] . ' ' . $me['cognome']) ?> · <?= adminLevelLabel((int)$me['livello_utente']) ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="admin-stats">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-num"><?= $numUtenti ?></div>
      <div class="stat-label">Utenti registrati</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🗺️</div>
      <div class="stat-num"><?= $numViaggi ?></div>
      <div class="stat-label">Viaggi totali</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💬</div>
      <div class="stat-num"><?= $numTickets ?></div>
      <div class="stat-label">Ticket totali</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🔔</div>
      <div class="stat-num"><?= $numAperti ?></div>
      <div class="stat-label">Ticket aperti</div>
    </div>
  </div>

  <!-- Grid -->
  <div class="admin-grid">
    <aside class="admin-sidebar">
      <h3>Sezioni</h3>
      <button class="admin-nav-item active" onclick="showSection('utenti', this)">
        <span class="nav-icon">👥</span> Utenti
      </button>
      <button class="admin-nav-item" onclick="showSection('viaggi', this)">
        <span class="nav-icon">🗺️</span> Viaggi
      </button>
      <button class="admin-nav-item" onclick="showSection('tickets', this)">
        <span class="nav-icon">💬</span> Supporto
      </button>
    </aside>

    <main class="admin-content">

      <!-- SEZIONE UTENTI -->
      <section class="admin-section active" id="section-utenti">
        <div class="admin-section-header">
          <h2>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Gestione Utenti
          </h2>
          <span class="admin-section-count"><?= count($utenti) ?> utenti</span>
        </div>
        <div class="admin-search-box">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="search-utenti" placeholder="Cerca per nome, email o nazionalità…" onkeyup="filterTable('utenti')">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Ruolo</th>
                <th>Registrato</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($utenti as $u): ?>
                <tr>
                  <td><?= $u['id'] ?></td>
                  <td><strong><?= htmlspecialchars($u['nome'] . ' ' . $u['cognome']) ?></strong></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><span class="badge badge-level-<?= $u['livello_utente'] ?>"><?= adminLevelLabel((int)$u['livello_utente']) ?></span></td>
                  <td style="color:var(--muted-lt);font-size:.8rem;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                  <td>
                    <div class="row-actions">
                      <button type="button" class="btn-edit" title="Modifica dati"
                        onclick="openEditModal(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['nome'])) ?>','<?= htmlspecialchars(addslashes($u['cognome'])) ?>','<?= htmlspecialchars(addslashes($u['email'])) ?>','<?= htmlspecialchars(addslashes($u['nazionalita'] ?? '')) ?>','<?= htmlspecialchars(addslashes($u['lingua'] ?? '')) ?>')">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Modifica
                      </button>

                      <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                        <button type="button" class="btn-edit" title="Modifica ruolo"
                          onclick="openRoleModal(<?= $u['id'] ?>, <?= $u['livello_utente'] ?>, '<?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>')">
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                          Ruolo
                        </button>
                      <?php endif; ?>

                      <form method="POST" action="./../../api/admin_reset_password.php"
                            onsubmit="return confirm('Resettare la password di <?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>?')" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn-edit" title="Reset Password">
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                          Reset
                        </button>
                      </form>

                      <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                        <form method="POST" action="./../../api/admin_user_delete.php"
                              onsubmit="return confirm('Eliminare definitivamente <?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>?')" style="margin:0;">
                          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                          <button type="submit" class="btn-delete" title="Elimina utente">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- SEZIONE VIAGGI -->
      <section class="admin-section" id="section-viaggi">
        <div class="admin-section-header">
          <h2>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/></svg>
            Gestione Viaggi
          </h2>
          <span class="admin-section-count"><?= count($viaggi) ?> viaggi</span>
        </div>
        <div class="admin-search-box">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="search-viaggi" placeholder="Cerca per destinazione o utente..." onkeyup="filterTable('viaggi')">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Utente</th>
                <th>Destinazione</th>
                <th>Dal</th>
                <th>Al</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($viaggi as $v): ?>
                <tr>
                  <td><?= $v['id'] ?></td>
                  <td><strong><?= htmlspecialchars($v['nome'] . ' ' . $v['cognome']) ?></strong></td>
                  <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(explode(',', $v['destinazione'])[0]) ?></td>
                  <td><?= date('d/m/Y', strtotime($v['data_inizio'])) ?></td>
                  <td><?= date('d/m/Y', strtotime($v['data_fine'])) ?></td>
                  <td>
                    <form method="POST" action="./../../api/admin_viaggio_delete.php"
                          onsubmit="return confirm('Eliminare questo viaggio?')" style="margin:0;">
                      <input type="hidden" name="viaggio_id" value="<?= $v['id'] ?>">
                      <button type="submit" class="btn-delete">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Elimina
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- SEZIONE TICKETS -->
      <section class="admin-section" id="section-tickets">
        <div class="admin-section-header">
          <h2>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Gestione Supporto
          </h2>
          <span class="admin-section-count"><?= count($tickets) ?> ticket</span>
        </div>
        <div class="admin-search-box">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="search-tickets" placeholder="Cerca per utente o oggetto..." onkeyup="filterTable('tickets')">
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Utente</th>
                <th>Oggetto</th>
                <th>Stato</th>
                <th>Data</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tickets as $t): ?>
                <tr class="ticket-row-<?= $t['stato'] ?>">
                  <td><?= $t['id'] ?></td>
                  <td><strong><?= htmlspecialchars($t['nome'] . ' ' . $t['cognome']) ?></strong></td>
                  <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($t['oggetto']) ?></td>
                  <td><span class="badge badge-<?= $t['stato'] ?>"><?= $t['stato'] ?></span></td>
                  <td style="color:var(--muted-lt);font-size:.8rem;"><?= date('d/m/Y', strtotime($t['created_at'])) ?></td>
                  <td>
                    <div class="row-actions">
                      <a href="./admin_chat.php?ticket=<?= $t['id'] ?>" class="btn-edit" title="Rispondi">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Rispondi
                      </a>
                      <?php if ($t['stato'] !== 'chiuso'): ?>
                        <form method="POST" action="./../../api/admin_ticket_close.php" style="margin:0;">
                          <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                          <button type="submit" class="btn-delete" title="Chiudi ticket">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Chiudi
                          </button>
                        </form>
                      <?php else: ?>
                        <form method="POST" action="./../../api/admin_ticket_delete.php"
                              onsubmit="return confirm('Eliminare definitivamente questo ticket?')" style="margin:0;">
                          <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                          <button type="submit" class="btn-delete" title="Elimina ticket">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                            Elimina
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- MODAL MODIFICA DATI -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal-box">
    <div class="modal-header">
      <h3>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Modifica Utente
      </h3>
      <button type="button" class="modal-close" onclick="closeEditModal()">✕</button>
    </div>
    <form method="POST" action="./../../api/admin_user_edit.php">
      <input type="hidden" name="user_id" id="edit-user-id">
      <div class="modal-row">
        <div class="modal-group">
          <label>Nome</label>
          <input type="text" name="nome" id="edit-nome" required>
        </div>
        <div class="modal-group">
          <label>Cognome</label>
          <input type="text" name="cognome" id="edit-cognome" required>
        </div>
      </div>
      <div class="modal-group">
        <label>Email</label>
        <input type="email" name="email" id="edit-email" required>
      </div>
      <div class="modal-row">
        <div class="modal-group">
          <label>Nazionalità</label>
          <input type="text" name="nazionalita" id="edit-nazionalita" placeholder="es. Italiana">
        </div>
        <div class="modal-group">
          <label>Lingua</label>
          <input type="text" name="lingua" id="edit-lingua" placeholder="es. Italiano">
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Annulla</button>
        <button type="submit" class="btn-save">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="display:inline;vertical-align:-2px;margin-right:.3rem"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Salva modifiche
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL MODIFICA RUOLO -->
<div class="modal-overlay" id="role-modal">
  <div class="modal-box">
    <div class="modal-header">
      <h3>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Modifica Ruolo
      </h3>
      <button type="button" class="modal-close" onclick="closeRoleModal()">✕</button>
    </div>
    <p style="font-size:.875rem;color:var(--muted);margin-bottom:1.25rem;">Stai modificando il ruolo di <strong id="modal-nome" style="color:var(--ink)"></strong></p>
    <form method="POST" action="./../../api/admin_user_role.php">
      <input type="hidden" name="user_id" id="modal-user-id">
      <div class="modal-group">
        <label>Ruolo</label>
        <select name="livello_utente">
          <option value="255">👤 Utente standard</option>
          <option value="1">⭐ Amministratore</option>
        </select>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeRoleModal()">Annulla</button>
        <button type="submit" class="btn-save">Salva ruolo</button>
      </div>
    </form>
  </div>
</div>

<script>
  function showSection(name, btn) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.admin-nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('section-' + name).classList.add('active');
    btn.classList.add('active');
  }

  function openEditModal(id, nome, cognome, email, nazionalita, lingua) {
    document.getElementById('edit-user-id').value     = id;
    document.getElementById('edit-nome').value        = nome;
    document.getElementById('edit-cognome').value     = cognome;
    document.getElementById('edit-email').value       = email;
    document.getElementById('edit-nazionalita').value = nazionalita;
    document.getElementById('edit-lingua').value      = lingua;
    document.getElementById('edit-modal').classList.add('open');
  }
  function closeEditModal() {
    document.getElementById('edit-modal').classList.remove('open');
  }
  document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
  });

  function openRoleModal(userId, currentLevel, nome) {
    document.getElementById('modal-user-id').value = userId;
    document.getElementById('modal-nome').textContent = nome;
    document.querySelector('#role-modal select[name="livello_utente"]').value = currentLevel;
    document.getElementById('role-modal').classList.add('open');
  }
  function closeRoleModal() {
    document.getElementById('role-modal').classList.remove('open');
  }
  document.getElementById('role-modal').addEventListener('click', function(e) {
    if (e.target === this) closeRoleModal();
  });

  function filterTable(section) {
    const searchInput = document.getElementById('search-' + section);
    const filter = searchInput.value.toLowerCase();
    const table = document.querySelector('#section-' + section + ' .admin-table tbody');
    const rows = table.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      if (text.includes(filter)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    let noResults = table.querySelector('.no-results');
    if (visibleCount === 0) {
      if (!noResults) {
        noResults = document.createElement('tr');
        noResults.className = 'no-results';
        noResults.innerHTML = '<td colspan="100%" style="text-align:center;padding:2rem;color:var(--muted);">Nessun risultato trovato</td>';
        table.appendChild(noResults);
      }
    } else if (noResults) {
      noResults.remove();
    }
  }
</script>
<script src="../../shared/app.js"></script>
</body>
</html>
