<?php
require_once __DIR__ . '/auth.php';
startSession();
?>
<header id="main-header">
  <a href="./../index/index.php" class="logo">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--brand)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 1 8 8c0 5.25-8 12-8 12S4 15.25 4 10a8 8 0 0 1 8-8z"/>
    </svg>
    VacanzaMatch
  </a>

  <button class="nav-toggle" type="button" onclick="toggleMenu()" aria-label="Menu">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
      <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>

  <nav id="main-nav">
    <a href="./../map_view/map_view.php" class="<?= strpos($_SERVER['PHP_SELF'] ?? '', 'map_view') !== false ? 'nav-active' : '' ?>">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:inline;vertical-align:-2px;margin-right:.3rem"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>Cerca compagni
    </a>
    <a href="./../dashboard/dashboard.php" class="<?= strpos($_SERVER['PHP_SELF'] ?? '', 'dashboard') !== false ? 'nav-active' : '' ?>">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="display:inline;vertical-align:-2px;margin-right:.3rem"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>I miei viaggi
    </a>

    <?php if (isLoggedIn() && isAdmin()): ?>
      <a href="./../admin/admin.php" class="btn-nav-admin">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="display:inline;vertical-align:-2px;margin-right:.3rem"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>Admin
      </a>
    <?php endif; ?>

    <?php if (isLoggedIn()):
      $u = currentUser(); ?>
      <div class="nav-profile">
        <button type="button" class="nav-profile-btn" onclick="toggleProfileMenu(event)">
          <span class="nav-avatar-sm"><?= strtoupper(mb_substr($u['nome'], 0, 1)) ?></span>
          <span class="nav-profile-name"><?= htmlspecialchars($u['nome']) ?></span>
          <svg class="nav-profile-caret" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        <div class="nav-profile-menu" id="nav-profile-menu">
          <div class="nav-profile-top">
            <div class="nav-avatar-lg"><?= strtoupper(mb_substr($u['nome'], 0, 1)) ?></div>
            <div>
              <div class="nav-profile-full"><?= htmlspecialchars($u['nome'] . ' ' . $u['cognome']) ?></div>
              <div class="nav-profile-sub"><?= htmlspecialchars($u['nazionalita']) ?> · <?= htmlspecialchars($u['lingua']) ?></div>
            </div>
          </div>

          <button type="button" class="nav-profile-item" onclick="window.location.href='./../profilo/profilo.php'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Profilo
          </button>
          <button type="button" class="nav-profile-item" onclick="window.location.href='./../impostazioni/impostazioni.php'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Sicurezza
          </button>
          <button type="button" class="nav-profile-item" onclick="window.location.href='./../supporto/supporto.php'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Supporto
          </button>
          <button type="button" class="nav-profile-item" onclick="toggleTheme()" title="Cambia tema">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="theme-icon-light">
              <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="theme-icon-dark" style="display:none;">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <span id="theme-label">Tema scuro</span>
          </button>
          <div class="nav-profile-divider"></div>
          <button type="button" class="nav-profile-item nav-profile-danger" onclick="window.location.href='./../logout/logout.php'">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Esci
          </button>
        </div>
      </div>
    <?php else: ?>
      <a href="./../login/login.php" class="btn-nav-login">Accedi</a>
      <a href="./../register/register.php" class="btn-nav-register">Registrati</a>
    <?php endif; ?>
  </nav>
</header>
