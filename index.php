<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Location: ' . (isLoggedIn() ? 'dashboard.php' : 'login.php'));
exit;
