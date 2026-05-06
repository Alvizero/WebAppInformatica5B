<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';

// Solo le agenzie possono creare/modificare pacchetti
if (!isAgency()) {
    redirect('../pages/admin/admin.php?error_msg=Non autorizzato a creare pacchetti');
}

$me = currentUser();
$pdo = getPDO();
$id = isset($_POST['pacchetto_id']) ? (int)$_POST['pacchetto_id'] : 0;
$titolo = trim($_POST['titolo'] ?? '');
$descrizione = trim($_POST['descrizione'] ?? '');
$localita = trim($_POST['localita'] ?? '');
$lat = !empty($_POST['latitudine']) ? (float)$_POST['latitudine'] : null;
$lng = !empty($_POST['longitudine']) ? (float)$_POST['longitudine'] : null;
$prezzo = (float)($_POST['prezzo'] ?? 0);
$link_esterno = trim($_POST['link_esterno'] ?? '');

// L'agenzia usa i suoi dati
$nome_agenzia = $me['nome'] . ' ' . $me['cognome'];
// Recupera email dal DB se non in sessione (auth.php non la salva in sessione)
$stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->execute([$me['id']]);
$email_agenzia = $stmtUser->fetchColumn();
$user_id = (int)$me['id'];

if (empty($titolo) || empty($localita)) {
    redirect('../pages/agency/agency.php?error_msg=Tutti i campi obbligatori devono essere compilati');
}

if ($id > 0) {
    // Update - Verifica che il pacchetto appartenga all'agenzia
    $stmtCheck = $pdo->prepare("SELECT user_id FROM pacchetti WHERE id = ?");
    $stmtCheck->execute([$id]);
    $owner_id = $stmtCheck->fetchColumn();
    
    if ((int)$owner_id !== $user_id) {
        redirect('../pages/agency/agency.php?error_msg=Non puoi modificare pacchetti di altre agenzie');
    }
    
    $stmt = $pdo->prepare("UPDATE pacchetti SET titolo = ?, descrizione = ?, localita = ?, latitudine = ?, longitudine = ?, prezzo = ?, nome_agenzia = ?, email_agenzia = ?, link_esterno = ? WHERE id = ?");
    $stmt->execute([$titolo, $descrizione, $localita, $lat, $lng, $prezzo, $nome_agenzia, $email_agenzia, $link_esterno, $id]);
    $pacchetto_id = $id;
} else {
    // Insert
    $stmt = $pdo->prepare("INSERT INTO pacchetti (titolo, descrizione, localita, latitudine, longitudine, prezzo, nome_agenzia, email_agenzia, link_esterno, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titolo, $descrizione, $localita, $lat, $lng, $prezzo, $nome_agenzia, $email_agenzia, $link_esterno, $user_id]);
    $pacchetto_id = (int)$pdo->lastInsertId();
}

// Gestione Galleria Immagini
if (isset($_FILES['immagini'])) {
    $uploadDir = __DIR__ . '/../uploads/pacchetti/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    foreach ($_FILES['immagini']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['immagini']['error'][$key] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['immagini']['name'][$key], PATHINFO_EXTENSION);
            $filename = uniqid('pkg_img_') . '.' . $ext;
            if (move_uploaded_file($tmp_name, $uploadDir . $filename)) {
                $percorso = 'uploads/pacchetti/' . $filename;
                $pdo->prepare("INSERT INTO pacchetti_immagini (pacchetto_id, percorso) VALUES (?, ?)")
                    ->execute([$pacchetto_id, $percorso]);
            }
        }
    }
}

redirect('../pages/agency/agency.php?success_msg=Pacchetto salvato con successo');
?>
