<?php
if (session_status() === PHP_SESSION_NONE) {

    $secure = (
        isset($_SERVER['HTTPS']) &&
        $_SERVER['HTTPS'] !== 'off'
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function isValidPassword($password)
{
    return preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
        $password
    );
}

function passwordRequirementsText()
{
    return "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
}

function logAudit(
    $conn,
    $userId,
    $actionType,
    $description
) {

    $sql =
    "INSERT INTO
    dbo.audit_logs
    (
        user_id,
        action_type,
        description
    )
    VALUES
    (?, ?, ?)";

    $params =
    array(
        $userId,
        $actionType,
        $description
    );

    sqlsrv_query(
        $conn,
        $sql,
        $params
    );
}
