<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "claim_points reached<br>";

require_once "db.php";
require_once "auth.php";
$conn = getDbConnection();

if (!isset($_SESSION["user_id"])) {
    die("Login required.");
}

$userId = $_SESSION["user_id"];

$qrData = $_POST["qr_data"] ?? "";

if (!$qrData) {
    die("Invalid QR.");
}

$qrData =
    urldecode(
        $qrData
    );

$data =
    json_decode(
        $qrData,
        true
    );

if (
    isset(
        $data[
            "voucher_id"
        ]
    )
) {

    $voucherId =
    $data[
        "voucher_id"
    ];

    $vouchersFile =
    "vouchers.json";

    $vouchers =
    json_decode(
        file_get_contents(
            $vouchersFile
        ),
        true
    );

    foreach (
        $vouchers
        as $key =>
        $voucher
    ) {

        if (
            $voucher[
                "voucher_id"
            ] ===
            $voucherId
        ) {

            if (
                $voucher[
                    "used"
                ]
            ) {
                die(
                "Voucher already used."
                );
            }

            $vouchers[
                $key
            ][
                "used"
            ] = true;

            file_put_contents(
                $vouchersFile,
                json_encode(
                    $vouchers
                )
            );

            die("
            <h3>
            Voucher Accepted
            </h3>

            <p>
            ₱100 Fuel
            Discount Applied
            </p>
            ");
        }
    }

    die(
        "Voucher not found."
    );
}

if (!$data) {
    die("Invalid QR.");
}

$transactionId =
    $data["id"] ?? "";

$amountPaid =
    floatval(
        $data["a"] ?? 0
    );

$liters =
    floatval(
        $data["l"] ?? 0
    );

if (!$transactionId) {
    die("Invalid QR.");
}

$usedQrFile =
    "used_qr_codes.json";

if (!file_exists($usedQrFile)) {
    file_put_contents(
        $usedQrFile,
        json_encode([])
    );
}

$usedQrs =
    json_decode(
        file_get_contents(
            $usedQrFile
        ),
        true
    );

if (!$usedQrs) {
    $usedQrs = [];
}

if (
    in_array(
        $transactionId,
        $usedQrs
    )
) {
    die("
    <h5>
    QR Already Used
    </h5>
    <p>
    This QR code
    was already claimed.
    </p>
    ");
}

$pointsEarned =
    floor(
        $amountPaid / 100
    );


$sql = "
UPDATE dbo.users
SET reward_points = reward_points + ?
WHERE user_id = ?
";

$params = array(
    $pointsEarned,
    $userId
);

$stmt = sqlsrv_query(
    $conn,
    $sql,
    $params
);

$usedQrs[] =
    $transactionId;

file_put_contents(
    $usedQrFile,
    json_encode(
        $usedQrs
    )
);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo "
<h4>Success!</h4>
<p>Fuel: {$liters} L</p>
<p>Amount Paid: ₱{$amountPaid}</p>
<p>Points Added: {$pointsEarned}</p>
";
?>
