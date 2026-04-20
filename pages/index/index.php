<?php
require_once __DIR__ . '/../../shared/auth.php';
startSession();
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VacanzaMatch — Trova compagni di viaggio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="../../shared/base.css">
  <link rel="stylesheet" href="index.css">
</head>
<body>
<?php include __DIR__ . '/../../shared/navbar.php'; ?>

<div class="container">
  <section class="hero">
    <!-- Left: text -->
    <div class="hero-text">
      <div class="hero-eyebrow">Travel companion platform</div>
      <h1 class="hero-title">
        Trova chi viaggia<br>
        <em>dove vai tu</em>
      </h1>
      <p class="hero-subtitle">
        Scopri persone della tua stessa nazionalità in vacanza nello stesso posto e nello stesso periodo. Il viaggio è più bello in buona compagnia.
      </p>
      <div class="hero-actions">
        <a href="./../map_view/map_view.php" class="btn btn-primary btn-lg">
          Cerca sulla mappa
        </a>
        <?php if (isLoggedIn()): ?>
          <a href="./../dashboard/dashboard.php" class="btn btn-ghost btn-lg">I miei viaggi</a>
        <?php else: ?>
          <a href="./../register/register.php" class="btn btn-ghost btn-lg">Registrati gratis</a>
        <?php endif; ?>
      </div>
      <div class="hero-trust">
        <span>Gratuito</span>
        <span class="hero-trust-dot"></span>
        <span>Nessuna pubblicità</span>
        <span class="hero-trust-dot"></span>
        <span>Privacy first</span>
      </div>
    </div>

    <!-- Right: visual -->
    <div class="hero-visual">
      <div class="hero-map-card">
        <div class="hero-map-bg"></div>
        <div class="hero-map-overlay"></div>
        <div class="hero-pins">
          <div class="pin pin-1">
            <div class="pin-bubble"><span class="pin-flag">🇮🇹</span> Marco · Barcellona</div>
            <div class="pin-dot"></div>
          </div>
          <div class="pin pin-2">
            <div class="pin-bubble"><span class="pin-flag">🇫🇷</span> Sophie · Parigi</div>
            <div class="pin-dot"></div>
          </div>
          <div class="pin pin-3">
            <div class="pin-bubble"><span class="pin-flag">🇩🇪</span> Hans · Roma</div>
            <div class="pin-dot"></div>
          </div>
          <div class="pin pin-4">
            <div class="pin-bubble"><span class="pin-flag">🇯🇵</span> Yuki · Tokyo</div>
            <div class="pin-dot"></div>
          </div>
          <div class="pin pin-5">
            <div class="pin-bubble"><span class="pin-flag">🇧🇷</span> Ana · Lisbona</div>
            <div class="pin-dot"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="features">
    <div class="features-header">
      <h2>Come funziona</h2>
      <p>Tre passi per trovare il tuo compagno di viaggio ideale</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🗺</div>
        <h3>Aggiungi i tuoi viaggi</h3>
        <p>Inserisci destinazione e date. Tutto resta privato finché non cerchi attivamente.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>Cerca per lingua e luogo</h3>
        <p>Filtra per nazionalità, lingua, date e raggio geografico sulla mappa interattiva.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">✈️</div>
        <h3>Parti insieme</h3>
        <p>Contatta chi trovi sulla mappa e organizza il viaggio in totale autonomia.</p>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <?php if (!isLoggedIn()): ?>
  <div class="cta-section">
    <h2>Pronto a trovare compagni?</h2>
    <p>Crea il tuo profilo in meno di un minuto. È completamente gratis.</p>
    <a href="./../register/register.php" class="btn btn-accent btn-lg">Inizia ora</a>
  </div>
  <?php endif; ?>
</div>

<script src="../../shared/app.js"></script>
</body>
</html>