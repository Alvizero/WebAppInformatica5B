<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
header('Content-Type: application/json; charset=utf-8');

// Filtri: accettano sia ID numerico che nome testuale (retrocompatibilità)
$lingua_raw      = trim($_GET['lingua']      ?? '');
$nazionalita_raw = trim($_GET['nazionalita'] ?? '');
$inizio = $_GET['data_inizio'] ?? '';
$fine   = $_GET['data_fine']   ?? '';
$lat    = filter_input(INPUT_GET, 'lat',    FILTER_VALIDATE_FLOAT);
$lng    = filter_input(INPUT_GET, 'lng',    FILTER_VALIDATE_FLOAT);
$raggio = filter_input(INPUT_GET, 'raggio', FILTER_VALIDATE_INT);

$pdo = getPDO();

$haversine = '(6371 * ACOS(
    COS(RADIANS(:lat)) * COS(RADIANS(v.latitudine)) *
    COS(RADIANS(v.longitudine) - RADIANS(:lng)) +
    SIN(RADIANS(:lat2)) * SIN(RADIANS(v.latitudine))
))';

$params = [
    'inizio' => $inizio,
    'fine'   => $fine,
];

$distCol = 'NULL';
if ($lat !== false && $lng !== false && $raggio) {
    $distCol = "ROUND({$haversine}, 2)";
    $params['lat']  = $lat;
    $params['lng']  = $lng;
    $params['lat2'] = $lat;
}

// Filtro lingua: se numerico usa id, altrimenti cerca per nome
$linguaFilter = '';
if ($lingua_raw !== '') {
    if (ctype_digit($lingua_raw)) {
        $linguaFilter = " AND u.lingua_id = :lingua";
        $params['lingua'] = (int)$lingua_raw;
    } else {
        $linguaFilter = " AND l.nome = :lingua";
        $params['lingua'] = mb_strtolower($lingua_raw);
    }
}

// Filtro nazionalità: stessa logica
$nazFilter = '';
if ($nazionalita_raw !== '') {
    if (ctype_digit($nazionalita_raw)) {
        $nazFilter = " AND u.nazionalita_id = :nazionalita";
        $params['nazionalita'] = (int)$nazionalita_raw;
    } else {
        $nazFilter = " AND n.nome = :nazionalita";
        $params['nazionalita'] = mb_strtolower($nazionalita_raw);
    }
}

$sql = "SELECT u.nome, u.cognome,
               n.nome AS nazionalita, l.nome AS lingua,
               v.destinazione, v.latitudine, v.longitudine,
               v.data_inizio, v.data_fine,
               {$distCol} AS distanza_km
        FROM viaggi v
        JOIN users u ON u.id = v.user_id
        LEFT JOIN nazionalita n ON n.id = u.nazionalita_id
        LEFT JOIN lingue l ON l.id = u.lingua_id
        WHERE v.data_inizio <= :fine
          AND v.data_fine   >= :inizio
          {$linguaFilter}
          {$nazFilter}";

if ($lat !== false && $lng !== false && $raggio) {
    $sql .= " HAVING distanza_km <= :raggio";
    $params['raggio'] = $raggio;
}

$sql .= " ORDER BY distanza_km ASC, v.data_inizio ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
