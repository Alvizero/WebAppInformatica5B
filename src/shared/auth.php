<?php
declare(strict_types=1);

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

// Qualsiasi livello admin (< 255)
function isAdmin(): bool {
    startSession();
    return isset($_SESSION['admin_level']) && $_SESSION['admin_level'] < 255;
}

// Livello specifico o superiore (numero più basso = più potere)
function isAdminLevel(int $maxLevel): bool {
    startSession();
    return isset($_SESSION['admin_level']) && $_SESSION['admin_level'] <= $maxLevel;
}

function getAdminLevel(): int {
    startSession();
    return $_SESSION['admin_level'] ?? 255;
}

function adminLevelLabel(int $level): string {
    return match(true) {
        $level === 0  => '⭐ Super Admin',
        $level === 1  => '🛡 Admin',
        $level === 2  => '🔧 Moderatore',
        default       => '👤 Utente',
    };
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $redirect = urlencode($_SERVER['REQUEST_URI']);
        header('Location: /informatica/mene2/src/pages/login/login.php?redirect=' . $redirect);
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /informatica/mene2/src/pages/dashboard/dashboard.php');
        exit;
    }
}

function requireAdminLevel(int $maxLevel): void {
    requireLogin();
    if (!isAdminLevel($maxLevel)) {
        header('Location: /informatica/mene2/src/pages/dashboard/dashboard.php');
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
        'admin_level' => $_SESSION['admin_level']   ?? 255,
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
    $_SESSION['admin_level']  = (int)$user['admin_level'];
}

function logoutUser(): void {
    startSession();
    session_unset();
    session_destroy();
}
