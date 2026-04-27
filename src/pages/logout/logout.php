<?php
declare(strict_types=1);
require_once __DIR__ . '/../../shared/auth.php';
logoutUser();
redirect('../dashboard/dashboard.php');
