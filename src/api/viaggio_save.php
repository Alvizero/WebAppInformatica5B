<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo  = getPDO();

$id     = (int)($_POST['id'] ?? 0);
$dest   = trim($_POST['destinazione'] ?? '');
$lat    = filter_input(INPUT_POST, 'latitudine',  FILTER_VALIDATE_FLOAT);
$lng    = filter_input(INPUT_POST, 'longitudine', FILTER_VALIDATE_FLOAT);
$inizio = $_POST['data_inizio'] ?? '';
$fine   = $_POST['data_fine']   ?? '';

if (!$dest || $lat === false || $lng === false || !$inizio || !$fine || $fine < $inizio) {
    redirect('../pages/dashboard/dashboard.php', null, 'Dati non validi o date non corrette.');
}

$params = ['uid'=>$user['id'], 'dest'=>$dest, 'lat'=>$lat, 'lng'=>$lng, 'inizio'=>$inizio, 'fine'=>$fine];

if ($id > 0) {
    $params['id'] = $id;
    getPDO()->prepare("UPDATE viaggi SET destinazione=:dest, latitudine=:lat, longitudine=:lng, data_inizio=:inizio, data_fine=:fine WHERE id=:id AND user_id=:uid")
        ->execute($params);
    $msg = "Viaggio aggiornato con successo!";
} else {
    getPDO()->prepare("INSERT INTO viaggi (user_id, destinazione, latitudine, longitudine, data_inizio, data_fine) VALUES (:uid, :dest, :lat, :lng, :inizio, :fine)")
        ->execute($params);
    $msg = "Nuovo viaggio creato con successo!";
}

redirect('../pages/dashboard/dashboard.php', $msg);
