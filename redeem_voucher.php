<?php

session_start();

require_once "auth.php";
require_once "db.php";

requireLogin();

$conn =
getDbConnection();

$qrData =
$_POST[
    "qr_data"
] ?? "";

if (!$qrData) {
    die("Invalid voucher.");
}

$data =
json_decode(
    $qrData,
    true
);

if (!$data) {
    die("Invalid voucher.");
}

$voucherId =
$data[
    "voucher_id"
] ?? "";

$amount =
intval(
    $data[
        "amount"
    ] ?? 0
);

if (
    !$voucherId
    || $amount <= 0
) {
    die(
        "Invalid voucher."
    );
}

$usedFile =
"used_vouchers.json";

if (
    !file_exists(
        $usedFile
    )
) {
    file_put_contents(
        $usedFile,
        json_encode([])
    );
}

$usedVouchers =
json_decode(
    file_get_contents(
        $usedFile
    ),
    true
);

if (
    !$usedVouchers
) {
    $usedVouchers =
    [];
}

if (
    in_array(
        $voucherId,
        $usedVouchers
    )
) {

    die("
    <h3>
    Voucher Already Used
    </h3>

    <p>
    This voucher
    was already redeemed.
    </p>

    <a href='retailer_dashboard.php'>
    Back
    </a>
    ");
}

$usedVouchers[] =
$voucherId;

file_put_contents(
    $usedFile,
    json_encode(
        $usedVouchers
    )
);

echo "
<h2>
Voucher Accepted
</h2>

<p>
₱{$amount}
gas voucher redeemed.
</p>

<a href='retailer_dashboard.php'>
Back
</a>
";
?>
