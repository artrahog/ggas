<?php

require_once "config.php";

$login_url = $client->createAuthUrl();

header('Location: ' . filter_var($login_url, FILTER_SANITIZE_URL));
exit();

