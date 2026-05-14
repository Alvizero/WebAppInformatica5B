<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
header('Content-Type: application/json; charset=utf-8');

$lingua         = trim($_GET['lingua']         ?? '');
$nazionalita_id = (int)($_GET['nazionalita']   ?? 0); // Ora riceviamo l'ID
$inizio         = $_GET['data_inizio'] ?? '';
$fine           = $_GET['data_fine']   ?? '';
$lat            = filter_input(INPUT_GET, 'lat',    FILTER_VALIDATE_FLOAT);
$lng            = filter_input(INPUT_GET, 'lng',    FILTER_VALIDATE_FLOAT);
$raggio         = filter_input(INPUT_GET, 'raggio', FILTER_VALIDATE_INT);

$pdo = getPDO();
$current_uid = currentUser()['id'];

$haversine = '(6371 * ACOS(
    COS(RADIANS(:lat)) * COS(RADIANS(v.latitudine)) *
    COS(RADIANS(v.longitudine) - RADIANS(:lng)) +
    SIN(RADIANS(:lat2)) * SIN(RADIANS(v.latitudine))
))';

$params = [
    'inizio'      => $inizio,
    'fine'        => $fine,
    'current_user_id' => $current_uid,
];

$distCol = 'NULL';
if ($lat !== false && $lng !== false && $raggio) {
    $distCol = "ROUND({$haversine}, 2)";
    $params['lat']  = $lat;
    $params['lng']  = $lng;
    $params['lat2'] = $lat;
}

// JOIN con nazionalita per ottenere il nome testuale da restituire al frontend
$sql = "SELECT u.id AS user_id, u.nome, u.cognome, n.nome as nazionalita, u.lingua,
               v.destinazione, v.latitudine, v.longitudine,
               v.data_inizio, v.data_fine,
               {$distCol} AS distanza_km
        FROM viaggi v
        JOIN users u ON u.id = v.user_id
        LEFT JOIN nazionalita n ON u.nazionalita_id = n.id
        WHERE v.data_inizio <= :fine
          AND v.data_fine   >= :inizio
          AND v.user_id != :current_user_id";

if (!empty($lingua)) {
    $sql .= " AND u.lingua = :lingua";
    $params['lingua'] = $lingua;
}
if (!empty($nazionalita_id)) {
    $sql .= " AND u.nazionalita_id = :nazionalita_id";
    $params['nazionalita_id'] = $nazionalita_id;
}

if ($lat !== false && $lng !== false && $raggio) {
    $sql .= " HAVING distanza_km <= :raggio";
    $params['raggio'] = $raggio;
}

$sql .= " ORDER BY distanza_km ASC, v.data_inizio ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Recupero pacchetti per la località cercata (se presente)
$pacchetti = [];
$citta = trim($_GET['citta'] ?? '');
if (!empty($citta)) {
    $stmtPkg = $pdo->prepare("SELECT * FROM pacchetti WHERE localita LIKE ? OR titolo LIKE ? LIMIT 5");
    $stmtPkg->execute(["%$citta%", "%$citta%"]);
    $pacchetti = $stmtPkg->fetchAll();

    foreach ($pacchetti as &$p) {
        $stmtImg = $pdo->prepare("SELECT percorso FROM pacchetti_immagini WHERE pacchetto_id = ?");
        $stmtImg->execute([$p['id']]);
        $p['galleria'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);
    }
}

echo json_encode([
    'current_user_id' => $current_uid,
    'users' => $results,
    'pacchetti' => $pacchetti
]);
?>
