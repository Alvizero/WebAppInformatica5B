<?php
declare(strict_types=1);

// Path per src/api/ -> src/shared/ (un solo livello su)
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';

// Solo un admin loggato può resettare le password
requireAdmin();

// Recuperiamo l'ID dell'utente dal form in POST
$user_id = (int)($_POST['user_id'] ?? 0);
$newPass = 'Reset123!'; // La nuova password temporanea

if ($user_id > 0) {
    $pdo = getPDO();
    
    // Generiamo l'hash della password (fondamentale per il login)
    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    
    // Aggiorniamo il database
    $stmt = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
    $stmt->execute([
        'p'  => $hash,
        'id' => $user_id
    ]);
}

// DOPO l'operazione, torniamo alla pagina admin
header('Location: ../pages/admin/admin.php?msg=password_resettata');
exit;
