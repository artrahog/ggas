<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once '../db.php';

$client = new Google_Client();

$client->setClientId(' ');

$client->setClientSecret(' ');

$client->setRedirectUri('http://cgascashless.xyz/ggas_app/oauth/callback.php');

$client->addScope("email");
$client->addScope("profile");

if (!isset($_GET['code'])) {
    die("No Google authorization code received.");
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die("Google token error.");
}

$client->setAccessToken($token);

$oauth = new Google_Service_Oauth2($client);

$userInfo = $oauth->userinfo->get();

if (!$userInfo) {
    die("Failed to retrieve Google user information.");
}

$email = $userInfo->email ?? '';
$fullName = $userInfo->name ?? 'Google User';
$givenName = $userInfo->givenName ?? '';
$familyName = $userInfo->familyName ?? '';

if (empty($givenName)) {
    $nameParts = explode(' ', trim($fullName), 2);
    $givenName = $nameParts[0] ?? 'Google';
}

if (empty($familyName)) {
    $familyName = $nameParts[1] ?? 'User';
}
$conn = getDbConnection();

$sql = "SELECT user_id, first_name, last_name, email, role, reward_points, rfid_uid
        FROM dbo.users
        WHERE email = ?";

$params = array($email);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$row) {

    // Check if account already exists
    $checkSql = "SELECT * FROM dbo.users WHERE email = ?";
    $checkParams = array($email);

    $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);

    $existingUser = $checkStmt
        ? sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)
        : null;

    if ($existingUser) {

        // Login existing user
        $_SESSION["user_id"] = $existingUser["user_id"];
        $_SESSION["first_name"] = $existingUser["first_name"];
        $_SESSION["last_name"] = $existingUser["last_name"];
        $_SESSION["email"] = $existingUser["email"];
        $_SESSION["rfid_uid"] = $existingUser["rfid_uid"];
        $_SESSION["is_admin"] =
            isset($existingUser["role"]) &&
            $existingUser["role"] === "admin";
$_SESSION["role"] = $existingUser["role"];
$_SESSION["is_admin"] =
    $existingUser["role"] === "admin";
$_SESSION["is_retailer"] =
    $existingUser["role"] === "retailer";


        header("Location: ../dashboard.php");
        exit();

    } else {

        echo "Account not registered. Creating account automatically...";

        $insertSql = "
            INSERT INTO dbo.users (
                first_name,
                last_name,
                email,
                phone,
                rfid_uid,
                password_hash,
                role,
                reward_points,
                account_status,
                created_at
            )
            OUTPUT INSERTED.user_id
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE())
        ";

$insertParams = array(
    $givenName ?: "Google",
    $familyName ?: "User",
            $email,
            "",
            uniqid("RFID_"),
            null,
            "user",
            0,
            "Active"
        );

        $insertStmt = sqlsrv_query(
            $conn,
            $insertSql,
            $insertParams
        );

        if (!$insertStmt) {
            die(print_r(sqlsrv_errors(), true));
        }

        $newUser = sqlsrv_fetch_array(
            $insertStmt,
            SQLSRV_FETCH_ASSOC
        );

$_SESSION["user_id"] = $newUser["user_id"];
$_SESSION["first_name"] = $givenName;
$_SESSION["last_name"] =
    $familyName ?: "GoogleUser";
$_SESSION["email"] = $email;

$_SESSION["role"] = "user";

$_SESSION["is_admin"] = false;
$_SESSION["is_retailer"] = false;

        header("Location: ../dashboard.php");
        exit();
    }

} else {

    // Existing RFID user login
    $_SESSION["user_id"] = $row["user_id"];
    $_SESSION["first_name"] = $row["first_name"];
    $_SESSION["last_name"] = $row["last_name"];
    $_SESSION["email"] = $row["email"];
    $_SESSION["rfid_uid"] = $row["rfid_uid"];
    $_SESSION["is_admin"] =
        isset($row["role"]) &&
        $row["role"] === "admin";

    header("Location: ../dashboard.php");
    exit();
}
?>
