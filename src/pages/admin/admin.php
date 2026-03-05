<?php

declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAdmin();

$me   = currentUser(); // Definiamo $me qui
$pdo  = getPDO();

// Statistiche
$numUtenti  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$numViaggi  = $pdo->query("SELECT COUNT(*) FROM viaggi")->fetchColumn();
$numTickets = $pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
$numAperti  = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE stato='aperto'")->fetchColumn();

// Query Utenti
$utenti = $pdo->query("SELECT id, nome, cognome, email, admin_level, created_at FROM users ORDER BY admin_level ASC, created_at DESC")->fetchAll();

// Query Viaggi
$viaggi = $pdo->query("SELECT v.*, u.nome, u.cognome FROM viaggi v JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC")->fetchAll();

// Query Ticket supporto
$tickets = $pdo->query("SELECT t.*, u.nome, u.cognome FROM support_tickets t JOIN users u ON u.id = t.user_id ORDER BY t.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VacanzaMatch — Admin</title>
    <link rel="stylesheet" href="./../../shared/base.css">
    <link rel="stylesheet" href="./admin.css">
</head>

<body>
    <?php include __DIR__ . '/../../shared/navbar.php'; ?>

    <div class="container" style="margin-top:2rem;">
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-num"><?= $numUtenti ?></div>
                <div class="stat-label">Utenti</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $numViaggi ?></div>
                <div class="stat-label">Viaggi</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $numTickets ?></div>
                <div class="stat-label">Ticket totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-num" style="color:#c0392b"><?= $numAperti ?></div>
                <div class="stat-label">Aperti</div>
            </div>
        </div>

        <div class="admin-own-level" style="background:#fff; padding:1rem; border-radius:10px; margin-bottom:1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            Sei loggato come <strong><?= adminLevelLabel((int)$me['admin_level']) ?></strong>
            <span class="badge badge-level-<?= $me['admin_level'] ?>">Livello <?= $me['admin_level'] ?></span>
        </div>

        <div class="admin-grid">
            <aside class="admin-sidebar">
                <h3>Pannello</h3>
                <button class="admin-nav-item active" onclick="showSection('utenti', this)">👥 Utenti</button>
                <button class="admin-nav-item" onclick="showSection('viaggi', this)">🗺 Viaggi</button>
                <button class="admin-nav-item" onclick="showSection('tickets', this)">💬 Supporto</button>
            </aside>

            <main class="admin-content">
                <!-- SEZIONE UTENTI -->
                <section class="admin-section active" id="section-utenti">
                    <h2>👥 Gestione Utenti</h2>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Livello</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utenti as $u): ?>
                                    <tr>
                                        <td><?= $u['id'] ?></td>
                                        <td><?= htmlspecialchars($u['nome'] . ' ' . $u['cognome']) ?></td>
                                        <td><?= htmlspecialchars($u['email']) ?></td>
                                        <td><span class="badge badge-level-<?= $u['admin_level'] ?>"><?= adminLevelLabel((int)$u['admin_level']) ?></span></td>
                                        <td>
                                            <div class="row-actions">
                                                <!-- Modifica Livello (Solo Super Admin e non su se stessi) -->
                                                <?php if (isAdminLevel(0) && (int)$u['id'] !== (int)$me['id']): ?>
                                                    <form method="POST" action="./../../api/admin_user_role.php" style="display:flex; gap:.2rem;">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <select name="admin_level" class="role-select">
                                                            <option value="255" <?= $u['admin_level'] == 255 ? 'selected' : '' ?>>Utente</option>
                                                            <option value="2" <?= $u['admin_level'] == 2 ? 'selected' : '' ?>>Mod</option>
                                                            <option value="1" <?= $u['admin_level'] == 1 ? 'selected' : '' ?>>Admin</option>
                                                            <option value="0" <?= $u['admin_level'] == 0 ? 'selected' : '' ?>>S.Admin</option>
                                                        </select>
                                                        <button type="submit" class="btn-edit">OK</button>
                                                    </form>
                                                <?php endif; ?>

                                                <form method="POST" action="./../../api/admin_reset_password.php">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn-edit" title="Reset Password">🔑</button>
                                                </form>

                                                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                                                    <form method="POST" action="./../../api/admin_user_delete.php">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn-delete" onclick="return confirm('Elimina?')">🗑</button>
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
                    <h2>🗺 Gestione Viaggi</h2>
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
                                        <td><?= htmlspecialchars($v['nome'] . ' ' . $v['cognome']) ?></td>
                                        <td><?= htmlspecialchars($v['destinazione']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($v['data_inizio'])) ?></td>
                                        <td><?= date('d/m/Y', strtotime($v['data_fine'])) ?></td>
                                        <td>
                                            <form method="POST" action="./../../api/admin_viaggio_delete.php">
                                                <input type="hidden" name="viaggio_id" value="<?= $v['id'] ?>">
                                                <button type="submit" class="btn-delete">🗑</button>
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
                    <h2>💬 Supporto</h2>
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Utente</th>
                                    <th>Oggetto</th>
                                    <th>Stato</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tickets as $t): ?>
                                    <tr>
                                        <td><?= $t['id'] ?></td>
                                        <td><?= htmlspecialchars($t['nome']) ?></td>
                                        <td><?= htmlspecialchars($t['oggetto']) ?></td>
                                        <td><span class="badge badge-<?= $t['stato'] ?>"><?= $t['stato'] ?></span></td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="./admin_chat.php?ticket=<?= $t['id'] ?>" class="btn-edit" title="Rispondi">💬</a>

                                                <?php if ($t['stato'] !== 'chiuso'): ?>
                                                    <!-- Se aperto/risposto: tasto per CHIUDERE -->
                                                    <form method="POST" action="./../../api/admin_ticket_close.php">
                                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn-delete" title="Chiudi Ticket">✖</button>
                                                    </form>
                                                <?php else: ?>
                                                    <!-- Se già chiuso: tasto per ELIMINARE -->
                                                    <form method="POST" action="./../../api/admin_ticket_delete.php">
                                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn-delete" title="Elimina Definitivamente" onclick="return confirm('Eliminare definitivamente questo ticket e i suoi messaggi?')">🗑</button>
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
    <script>
        function showSection(name, btn) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.admin-nav-item').forEach(b => b.classList.remove('active'));
            document.getElementById('section-' + name).classList.add('active');
            btn.classList.add('active');
        }
    </script>
</body>

</html>