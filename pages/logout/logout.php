<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
logoutUser();
header('Location: ./../dashboard/dashboard.php');
exit;
