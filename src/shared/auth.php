<?php
declare(strict_types=1);

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ./../login/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function currentUser(): array {
    startSession();
    return [
        'id'          => $_SESSION['user_id']      ?? null,
        'nome'        => $_SESSION['user_nome']     ?? '',
        'cognome'     => $_SESSION['user_cognome']  ?? '',
        'lingua'      => $_SESSION['user_lingua']   ?? '',
        'nazionalita' => $_SESSION['user_naz']      ?? '',
    ];
}

function loginUser(array $user): void {
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']      = $user['id'];
    $_SESSION['user_nome']    = $user['nome'];
    $_SESSION['user_cognome'] = $user['cognome'];
    $_SESSION['user_lingua']  = $user['lingua'];
    $_SESSION['user_naz']     = $user['nazionalita'];
}

function logoutUser(): void {
    startSession();
    session_unset();
    session_destroy();
}
