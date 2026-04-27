<?php
/**
 * Sidebar condivisa per le pagine del profilo e delle impostazioni.
 * Variabili attese: $dbUser (array con nome, cognome, email), $activePage (string)
 */
$initials = strtoupper(mb_substr($dbUser['nome'] ?? '', 0, 1) . mb_substr($dbUser['cognome'] ?? '', 0, 1));
?>
<div class="settings-sidebar">
  <div class="settings-sidebar-profile">
    <div class="avatar-circle"><?= htmlspecialchars($initials) ?></div>
    <div class="user-name"><?= htmlspecialchars(($dbUser['nome'] ?? '') . ' ' . ($dbUser['cognome'] ?? '')) ?></div>
    <div class="user-email"><?= htmlspecialchars($dbUser['email'] ?? '') ?></div>
  </div>
  <nav class="settings-nav">
    <a href="./../profilo/profilo.php" class="<?= $activePage === 'profilo' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Profilo
    </a>
    <a href="./../impostazioni/impostazioni.php" class="<?= $activePage === 'impostazioni' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Sicurezza
    </a>
    <a href="./../supporto/supporto.php" class="<?= $activePage === 'supporto' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Supporto
    </a>
  </nav>
</div>
