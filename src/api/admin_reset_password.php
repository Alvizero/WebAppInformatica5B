<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/db_config.php';
requireAdmin();

$user_id = (int)($_POST['user_id'] ?? 0);
if ($user_id <= 0) {
    setFlash('reset_error', '❌ ID utente non valido.');
    redirect('../pages/admin/admin.php');
}

$newPassword  = bin2hex(random_bytes(6));
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = getPDO()->prepare("UPDATE users SET password = :pwd WHERE id = :id");
$stmt->execute(['pwd' => $passwordHash, 'id' => $user_id]);

if ($stmt->rowCount() === 0) {
    setFlash('reset_error', '❌ Utente non trovato.');
    redirect('../pages/admin/admin.php');
}

setFlash('reset_msg', "✅ Password resettata per l'utente #$user_id. Password temporanea: <strong>$newPassword</strong>");
redirect('../pages/admin/admin.php');
