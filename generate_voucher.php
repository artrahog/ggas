<?php

session_start();

require_once "auth.php";
require_once "db.php";

requireLogin();

$conn =
getDbConnection();

$userId =
$_SESSION[
    "user_id"
];

$sql =
"SELECT reward_points
FROM dbo.users
WHERE user_id = ?";

$params =
array(
    $userId
);

$stmt =
sqlsrv_query(
    $conn,
    $sql,
    $params
);

$user =
sqlsrv_fetch_array(
    $stmt,
    SQLSRV_FETCH_ASSOC
);

if (
    !$user
    || $user[
        "reward_points"
    ] < 100
) {
    die(
        "Not enough points."
    );
}

sqlsrv_query(
    $conn,
    "
    UPDATE dbo.users
    SET reward_points =
    reward_points - 100
    WHERE user_id = ?
    ",
    array(
        $userId
    )
);

$voucherData =
json_encode([
    "voucher_id" =>
    uniqid(),

    "amount" =>
    100
]);

?>

<!DOCTYPE html>
<html>
<head>

<title>
Voucher QR
</title>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

</head>

<body>

<h2>
₱100 Fuel Voucher
</h2>

<p>
Show this QR
to retailer.
</p>

<div id="qrcode"></div>

<script>

new QRCode(
    document.getElementById(
        "qrcode"
    ),
    <?php
    echo json_encode(
        $voucherData
    );
    ?>
);

</script>

</body>
</html>
