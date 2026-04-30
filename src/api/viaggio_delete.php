<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

<<<<<<< Updated upstream
$user = currentUser();
$id   = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM viaggi WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $id, 'uid' => $user['id']]);
}

header('Location: ./../pages/dashboard/dashboard.php?success_msg=' . urlencode('Viaggio eliminato con successo!'));exit;
=======
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $stmt = getPDO()->prepare("DELETE FROM viaggi WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $id, 'uid' => currentUser()['id']]);
    if ($stmt->rowCount() > 0) {
        redirectMsg('../pages/dashboard/dashboard.php', '✅ Viaggio eliminato con successo.');
    } else {
        redirectMsg('../pages/dashboard/dashboard.php', '❌ Viaggio non trovato o non autorizzato.', true);
    }
} else {
    redirectMsg('../pages/dashboard/dashboard.php', '❌ ID viaggio non valido.', true);
}
>>>>>>> Stashed changes
