<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
header('Content-Type: application/json; charset=utf-8');

$lingua      = trim($_GET['lingua']      ?? '');
$nazionalita = trim($_GET['nazionalita'] ?? '');
$inizio      = $_GET['data_inizio'] ?? '';
$fine        = $_GET['data_fine']   ?? '';
$lat         = filter_input(INPUT_GET, 'lat',    FILTER_VALIDATE_FLOAT);
$lng         = filter_input(INPUT_GET, 'lng',    FILTER_VALIDATE_FLOAT);
$raggio      = filter_input(INPUT_GET, 'raggio', FILTER_VALIDATE_INT);

if (empty($lingua) && empty($nazionalita)) {
    echo json_encode([]); exit;
}

$pdo = getPDO();

$haversine = '(6371 * ACOS(
    COS(RADIANS(:lat)) * COS(RADIANS(v.latitudine)) *
    COS(RADIANS(v.longitudine) - RADIANS(:lng)) +
    SIN(RADIANS(:lat2)) * SIN(RADIANS(v.latitudine))
))';

$params = [
    'lingua'      => $lingua,
    'nazionalita' => $nazionalita,
    'inizio'      => $inizio,
    'fine'        => $fine,
];

$distCol = 'NULL';
if ($lat !== false && $lng !== false && $raggio) {
    $distCol = "ROUND({$haversine}, 2)";
    $params['lat']  = $lat;
    $params['lng']  = $lng;
    $params['lat2'] = $lat;
}

$sql = "SELECT u.nome, u.cognome, u.nazionalita, u.lingua,
               v.destinazione, v.latitudine, v.longitudine,
               v.data_inizio, v.data_fine,
               {$distCol} AS distanza_km
        FROM viaggi v
        JOIN users u ON u.id = v.user_id
        WHERE (u.lingua = :lingua OR u.nazionalita = :nazionalita)
          AND v.data_inizio <= :fine
          AND v.data_fine   >= :inizio";

if ($lat !== false && $lng !== false && $raggio) {
    $sql .= " HAVING distanza_km <= :raggio";
    $params['raggio'] = $raggio;
}

$sql .= " ORDER BY distanza_km ASC, v.data_inizio ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
