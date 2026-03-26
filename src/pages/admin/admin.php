<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../shared/auth.php';
require_once __DIR__ . '/../../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$me  = currentUser();
$pdo = getPDO();

$resetMsg = '';
if (!empty($_SESSION['reset_msg'])) {
    $resetMsg = $_SESSION['reset_msg'];
    unset($_SESSION['reset_msg']);
}

$errorMsg = '';
if (!empty($_SESSION['reset_error'])) {
    $errorMsg = $_SESSION['reset_error'];
    unset($_SESSION['reset_error']);
}

$numUtenti  = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$numViaggi  = $pdo->query("SELECT COUNT(*) FROM viaggi")->fetchColumn();
$numTickets = $pdo->query("SELECT COUNT(*) FROM support_tickets")->fetchColumn();
$numAperti  = $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE stato='aperto'")->fetchColumn();

$utenti  = $pdo->query("SELECT id, nome, cognome, email, admin_level, nazionalita, lingua, created_at FROM users ORDER BY admin_level ASC, created_at DESC")->fetchAll();
$viaggi  = $pdo->query("SELECT v.*, u.nome, u.cognome FROM viaggi v JOIN users u ON u.id = v.user_id ORDER BY v.created_at DESC")->fetchAll();
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
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: #fff;
            border-radius: 14px;
            padding: 2rem;
            min-width: 340px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            animation: popIn .18s ease;
        }
        @keyframes popIn {
            from { transform: scale(.92); opacity: 0; }
            to   { transform: scale(1);  opacity: 1; }
        }
        .modal-box h3 { margin-top: 0; }
        .modal-box label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: .3rem;
            color: #333;
        }
        .modal-box input, .modal-box select {
            width: 100%;
            padding: .55rem .8rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: .95rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        .modal-row { display: flex; gap: .8rem; }
        .modal-row > div { flex: 1; }
        .modal-actions {
            display: flex;
            gap: .8rem;
            justify-content: flex-end;
            margin-top: .5rem;
        }
        .btn-cancel {
            padding: .5rem 1.2rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            background: #f5f5f5;
            cursor: pointer;
        }
        .btn-save {
            padding: .5rem 1.2rem;
            border-radius: 8px;
            border: none;
            background: #0077b6;
            color: #fff;
            cursor: pointer;
        }
        .btn-save:hover { background: #005f94; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../shared/navbar.php'; ?>

    <div class="container" style="margin-top:2rem;">

        <?php if ($resetMsg): ?>
            <div style="background:#d4edda;color:#155724;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.2rem;">
                <?= $resetMsg ?>
                <br><small>⚠️ Comunica la password temporanea all'utente e invitalo a cambiarla subito.</small>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div style="background:#f8d7da;color:#721c24;padding:1rem 1.5rem;border-radius:8px;margin-bottom:1.2rem;">
                <?= $errorMsg ?>
            </div>
        <?php endif; ?>

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

                                                <!-- Modifica Dati -->
                                                <button type="button" class="btn-edit" title="Modifica dati"
                                                    onclick="openEditModal(
                                                        <?= $u['id'] ?>,
                                                        '<?= htmlspecialchars(addslashes($u['nome'])) ?>',
                                                        '<?= htmlspecialchars(addslashes($u['cognome'])) ?>',
                                                        '<?= htmlspecialchars(addslashes($u['email'])) ?>',
                                                        '<?= htmlspecialchars(addslashes($u['nazionalita'] ?? '')) ?>',
                                                        '<?= htmlspecialchars(addslashes($u['lingua'] ?? '')) ?>'
                                                    )">✏️</button>

                                                <!-- Modifica Ruolo -->
                                                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                                                    <button type="button" class="btn-edit" title="Modifica ruolo"
                                                        onclick="openRoleModal(<?= $u['id'] ?>, <?= $u['admin_level'] ?>, '<?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>')">⚙️</button>
                                                <?php endif; ?>

                                                <!-- Reset Password -->
                                                <form method="POST" action="./../../api/admin_reset_password.php"
                                                      onsubmit="return confirm('Resettare la password di <?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>?')">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn-edit" title="Reset Password">🔑</button>
                                                </form>

                                                <!-- Elimina -->
                                                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                                                    <form method="POST" action="./../../api/admin_user_delete.php"
                                                          onsubmit="return confirm('Eliminare <?= htmlspecialchars(addslashes($u['nome'] . ' ' . $u['cognome'])) ?>?')">
                                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                        <button type="submit" class="btn-delete" title="Elimina utente">🗑</button>
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
                                                    <form method="POST" action="./../../api/admin_ticket_close.php">
                                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn-delete" title="Chiudi Ticket">✖</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="./../../api/admin_ticket_delete.php">
                                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                                        <button type="submit" class="btn-delete" title="Elimina Definitivamente"
                                                                onclick="return confirm('Eliminare definitivamente questo ticket e i suoi messaggi?')">🗑</button>
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
            <h3>✏️ Modifica Utente</h3>
            <form method="POST" action="./../../api/admin_user_edit.php">
                <input type="hidden" name="user_id" id="edit-user-id">
                <div class="modal-row">
                    <div>
                        <label for="edit-nome">Nome</label>
                        <input type="text" name="nome" id="edit-nome" required>
                    </div>
                    <div>
                        <label for="edit-cognome">Cognome</label>
                        <input type="text" name="cognome" id="edit-cognome" required>
                    </div>
                </div>
                <label for="edit-email">Email</label>
                <input type="email" name="email" id="edit-email" required>
                <div class="modal-row">
                    <div>
                        <label for="edit-nazionalita">Nazionalità</label>
                        <input type="text" name="nazionalita" id="edit-nazionalita" placeholder="es. Italiana">
                    </div>
                    <div>
                        <label for="edit-lingua">Lingua</label>
                        <input type="text" name="lingua" id="edit-lingua" placeholder="es. Italiano">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Annulla</button>
                    <button type="submit" class="btn-save">Salva</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFICA RUOLO -->
    <div class="modal-overlay" id="role-modal">
        <div class="modal-box">
            <h3>⚙️ Modifica Ruolo</h3>
            <p style="color:#555;font-size:.9rem;">Utente: <strong id="modal-nome"></strong></p>
            <form method="POST" action="./../../api/admin_user_role.php">
                <input type="hidden" name="user_id" id="modal-user-id">
                <select name="admin_level">
                    <option value="255">👤 Utente</option>
                    <option value="1">🛡 Admin</option>
                </select>
                <div class="modal-actions" style="margin-top:.5rem;">
                    <button type="button" class="btn-cancel" onclick="closeRoleModal()">Annulla</button>
                    <button type="submit" class="btn-save">Salva</button>
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

        // Modal Modifica Dati
        function openEditModal(id, nome, cognome, email, nazionalita, lingua) {
            document.getElementById('edit-user-id').value    = id;
            document.getElementById('edit-nome').value       = nome;
            document.getElementById('edit-cognome').value    = cognome;
            document.getElementById('edit-email').value      = email;
            document.getElementById('edit-nazionalita').value = nazionalita;
            document.getElementById('edit-lingua').value     = lingua;
            document.getElementById('edit-modal').classList.add('open');
        }
        function closeEditModal() {
            document.getElementById('edit-modal').classList.remove('open');
        }
        document.getElementById('edit-modal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // Modal Modifica Ruolo
        function openRoleModal(userId, currentLevel, nome) {
            document.getElementById('modal-user-id').value = userId;
            document.getElementById('modal-nome').textContent = nome;
            document.querySelector('#role-modal select[name="admin_level"]').value = currentLevel;
            document.getElementById('role-modal').classList.add('open');
        }
        function closeRoleModal() {
            document.getElementById('role-modal').classList.remove('open');
        }
        document.getElementById('role-modal').addEventListener('click', function(e) {
            if (e.target === this) closeRoleModal();
        });
    </script>
    <script src="../../shared/app.js"></script>
</body>
</html>
