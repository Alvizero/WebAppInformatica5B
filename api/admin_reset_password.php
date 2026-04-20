<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

if (session_status() === PHP_SESSION_NONE) session_start();

$pdo     = getPDO();
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
    $_SESSION['reset_error'] = '❌ ID utente non valido.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$newPassword  = bin2hex(random_bytes(6));
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = :pwd WHERE id = :id");
$stmt->execute(['pwd' => $passwordHash, 'id' => $user_id]);

if ($stmt->rowCount() === 0) {
    $_SESSION['reset_error'] = '❌ Utente non trovato.';
    header('Location: ../pages/admin/admin.php');
    exit;
}

$_SESSION['reset_msg'] = "✅ Password resettata per l'utente #$user_id. Password temporanea: <strong>$newPassword</strong>";
header('Location: ../pages/admin/admin.php');
exit;
