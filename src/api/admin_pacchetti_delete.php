<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';

if (!isAdmin() && !isAgency()) {
    redirect('../pages/dashboard/dashboard.php');
}

$me = currentUser();
$pdo = getPDO();
$id = isset($_POST['pacchetto_id']) ? (int)$_POST['pacchetto_id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id > 0) {
    // Se è un'agenzia, verifica che il pacchetto sia suo
    if (isAgency()) {
        $stmt = $pdo->prepare("SELECT user_id FROM pacchetti WHERE id = ?");
        $stmt->execute([$id]);
        $owner_id = $stmt->fetchColumn();
        if ((int)$owner_id !== (int)$me['id']) {
            redirect('../pages/agency/agency.php?error_msg=Non autorizzato');
        }
    }

    // Elimina immagini della galleria
    $stmt = $pdo->prepare("SELECT percorso FROM pacchetti_immagini WHERE pacchetto_id = ?");
    $stmt->execute([$id]);
    $imgs = $stmt->fetchAll();
    foreach ($imgs as $img) {
        @unlink(__DIR__ . '/../' . $img['percorso']);
    }

    $stmt = $pdo->prepare("DELETE FROM pacchetti WHERE id = ?");
    $stmt->execute([$id]);
}

$redirect = isAgency() ? '../pages/agency/agency.php' : '../pages/admin/admin.php';
redirect($redirect . '?success_msg=Pacchetto eliminato');
?>
