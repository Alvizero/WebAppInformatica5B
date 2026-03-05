<?php
require_once __DIR__ . '/auth.php';
startSession();
?>
<header>
  <a href="./../index/index.php" class="logo">🌍 VacanzaMatch</a>

  <button class="nav-toggle" type="button" onclick="toggleMenu()" aria-label="Menu">☰</button>

  <nav id="main-nav">
    <a href="./../map_view/map_view.php">🔍 Cerca compagni</a>
    <a href="./../dashboard/dashboard.php">🗺 I miei viaggi</a>

    <?php if (isLoggedIn() && isAdmin()): ?>
      <a href="./../admin/admin.php" class="btn-nav-admin">⚙️ Admin</a>
    <?php endif; ?>


    <?php if (isLoggedIn()):
      $u = currentUser(); ?>
      <div class="nav-profile">
        <button type="button" class="nav-profile-btn" onclick="toggleProfileMenu(event)">
          <span class="nav-avatar-sm"><?= strtoupper(mb_substr($u['nome'], 0, 1)) ?></span>
          <span class="nav-profile-name"><?= htmlspecialchars($u['nome']) ?></span>
          <span class="nav-profile-caret">▾</span>
        </button>

        <div class="nav-profile-menu" id="nav-profile-menu">
          <div class="nav-profile-top">
            <div class="nav-avatar-lg"><?= strtoupper(mb_substr($u['nome'], 0, 1)) ?></div>
            <div>
              <div class="nav-profile-full">
                <?= htmlspecialchars($u['nome'] . ' ' . $u['cognome']) ?>
              </div>
              <div class="nav-profile-sub">
                <?= htmlspecialchars($u['nazionalita']) ?> · <?= htmlspecialchars($u['lingua']) ?>
              </div>
            </div>
          </div>

          <button type="button" class="nav-profile-item"
            onclick="window.location.href='./../profilo/profilo.php'">
            👤 Profilo
          </button>
          <button type="button" class="nav-profile-item"
            onclick="window.location.href='./../impostazioni/impostazioni.php'">
            ⚙️ Impostazioni
          </button>
          <button type="button" class="nav-profile-item"
            onclick="window.location.href='./../supporto/supporto.php'">
            💬 Supporto
          </button>
          <button type="button" class="nav-profile-item nav-profile-danger"
            onclick="window.location.href='./../logout/logout.php'">
            ⏏ Esci
          </button>
        </div>
      </div>
    <?php else: ?>
      <a href="./../login/login.php" class="btn-nav-login">Accedi</a>
      <a href="./../register/register.php" class="btn-nav-register">Registrati</a>
    <?php endif; ?>
  </nav>
</header>