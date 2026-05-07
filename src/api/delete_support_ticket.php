<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/db_config.php';
require_once __DIR__ . '/../shared/auth.php';
requireLogin();

$user = currentUser();
$pdo = getPDO();
$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

// Determina se la risposta deve essere JSON (chiamata AJAX) o redirect
$wantsJson = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
             (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

if ($ticket_id <= 0) {
    if ($wantsJson) { http_response_code(400); echo json_encode(['error' => 'ID ticket non valido']); exit; }
    redirect('../pages/supporto/supporto.php', null, 'ID ticket non valido.');
}

// Verifica che l'utente sia parte del ticket (admin o creatore)
$stmt = $pdo->prepare("SELECT user_id FROM support_tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    if ($wantsJson) { http_response_code(404); echo json_encode(['error' => 'Ticket non trovato']); exit; }
    redirect('../pages/supporto/supporto.php', null, 'Ticket non trovato.');
}

$user_id = (int)$user['id'];
$is_admin = isAdmin();
$is_owner = (int)$ticket['user_id'] === $user_id;

if (!$is_admin && !$is_owner) {
    if ($wantsJson) { http_response_code(403); echo json_encode(['error' => 'Non autorizzato']); exit; }
    redirect('../pages/supporto/supporto.php', null, 'Non autorizzato.');
}

$pdo->prepare("DELETE FROM support_messages WHERE ticket_id = ?")->execute([$ticket_id]);
$pdo->prepare("DELETE FROM support_tickets WHERE id = ?")->execute([$ticket_id]);

if ($wantsJson) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Ticket eliminato']);
} else {
    $target = $is_admin ? '../pages/admin/admin.php' : '../pages/supporto/supporto.php';
    if ($is_admin) {
        setFlash('reset_msg', "✅ Ticket #$ticket_id eliminato con successo.");
        redirect($target);
    } else {
        redirect($target, 'Ticket eliminato con successo.');
    }
}
?>
