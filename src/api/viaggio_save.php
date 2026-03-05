<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo  = getPDO();

$id    = isset($_POST['id']) ? (int)$_POST['id'] : null;
$dest  = trim($_POST['destinazione'] ?? '');
$lat   = filter_input(INPUT_POST, 'latitudine',  FILTER_VALIDATE_FLOAT);
$lng   = filter_input(INPUT_POST, 'longitudine', FILTER_VALIDATE_FLOAT);
$inizio = $_POST['data_inizio'] ?? '';
$fine   = $_POST['data_fine']   ?? '';

// Validazione
if (!$dest || $lat === false || $lng === false || !$inizio || !$fine || $fine < $inizio) {
    header('Location: ./../pages/dashboard/dashboard.php?error=dati_non_validi');
    exit;
}

if ($id) {
    // UPDATE — solo se il viaggio appartiene all'utente
    $stmt = $pdo->prepare("
        UPDATE viaggi
        SET destinazione = :dest,
            latitudine   = :lat,
            longitudine  = :lng,
            data_inizio  = :inizio,
            data_fine    = :fine
        WHERE id = :id AND user_id = :uid
    ");
    $stmt->execute([
        'dest'  => $dest,
        'lat'   => $lat,
        'lng'   => $lng,
        'inizio'=> $inizio,
        'fine'  => $fine,
        'id'    => $id,
        'uid'   => $user['id'],
    ]);
} else {
    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO viaggi (user_id, destinazione, latitudine, longitudine, data_inizio, data_fine)
        VALUES (:uid, :dest, :lat, :lng, :inizio, :fine)
    ");
    $stmt->execute([
        'uid'   => $user['id'],
        'dest'  => $dest,
        'lat'   => $lat,
        'lng'   => $lng,
        'inizio'=> $inizio,
        'fine'  => $fine,
    ]);
}

header('Location: ./../pages/dashboard/dashboard.php');
exit;
      