<?php
require_once __DIR__ . '/../../shared/auth.php';
startSession();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VacanzaMatch</title>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="index.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <div class="hero">
    <h2>Trova compagni di viaggio 🌍</h2>
    <p>Scopri persone della tua stessa nazionalità in vacanza nello stesso posto e nello stesso periodo.</p>
    <div class="hero-actions">
      <a href="./../map_view/map_view.php" class="btn-hero btn-hero-primary">🔍 Cerca compagni</a>
      <?php if (isLoggedIn()): ?>
        <a href="./../dashboard/dashboard.php" class="btn-hero btn-hero-secondary">🗺 I miei viaggi</a>
      <?php else: ?>
        <a href="./../register/register.php" class="btn-hero btn-hero-secondary">Registrati gratis</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>
