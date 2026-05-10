<?php
require_once "auth.php";
require_once "db.php";

requireLogin();
if (
    isset($_SESSION["role"]) &&
    $_SESSION["role"] === "retailer"
) {
    header("Location: retailer_dashboard.php");
    exit();
}

$conn = getDbConnection();

$totalUsers =
0;

$totalPoints =
0;

$totalRedeems =
0;

$userQuery =
sqlsrv_query(
    $conn,
    "SELECT COUNT(*) AS total
    FROM dbo.users"
);

if ($userQuery) {

    $row =
    sqlsrv_fetch_array(
        $userQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalUsers =
    $row["total"];
}

$pointsQuery =
sqlsrv_query(
    $conn,
    "SELECT
    ISNULL(
        SUM(reward_points),
        0
    ) AS total
    FROM dbo.users"
);

if ($pointsQuery) {

    $row =
    sqlsrv_fetch_array(
        $pointsQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalPoints =
    $row["total"];
}

$redeemQuery =
sqlsrv_query(
    $conn,
    "SELECT COUNT(*) AS total
    FROM dbo.audit_logs
    WHERE action_type =
    'REDEEM_VOUCHER'"
);

if ($redeemQuery) {

    $row =
    sqlsrv_fetch_array(
        $redeemQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalRedeems =
    $row["total"];
}

$totalUsers =
0;

$totalPoints =
0;

$totalRedeems =
0;

$totalLogs =
0;

$userQuery =
sqlsrv_query(
    $conn,
    "SELECT COUNT(*) AS total
    FROM dbo.users"
);

if ($userQuery) {
    $row =
    sqlsrv_fetch_array(
        $userQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalUsers =
    $row["total"];
}

$pointsQuery =
sqlsrv_query(
    $conn,
    "SELECT
    ISNULL(
        SUM(
            reward_points
        ),
        0
    ) AS total
    FROM dbo.users"
);

if ($pointsQuery) {
    $row =
    sqlsrv_fetch_array(
        $pointsQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalPoints =
    $row["total"];
}

$redeemQuery =
sqlsrv_query(
    $conn,
    "SELECT COUNT(*) AS total
    FROM dbo.audit_logs
    WHERE
    action_type =
    'REDEEM_VOUCHER'"
);

if ($redeemQuery) {
    $row =
    sqlsrv_fetch_array(
        $redeemQuery,
        SQLSRV_FETCH_ASSOC
    );

    $totalRedeems =
    $row["total"];
}

$sql = "
SELECT
    user_id,
    first_name,
    last_name,
    email,
    phone,
    rfid_uid,
    reward_points,
    account_status,
    created_at
FROM dbo.users
WHERE user_id = ?
";

$params = array($_SESSION["user_id"]);

$stmt = sqlsrv_query(
    $conn,
    $sql,
    $params
);

$user = null;

if ($stmt) {
    $user = sqlsrv_fetch_array(
        $stmt,
        SQLSRV_FETCH_ASSOC
    );
}

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$_SESSION["first_name"] = $user["first_name"];
$_SESSION["last_name"] = $user["last_name"];
$_SESSION["email"] = $user["email"];
$_SESSION["reward_points"] = $user["reward_points"];
$_SESSION["rfid_uid"] = $user["rfid_uid"];

if ($stmt) {
    sqlsrv_free_stmt($stmt);
}
sqlsrv_close($conn);

$created_at = "";
if ($user["created_at"] instanceof DateTime) {
    $created_at = $user["created_at"]->format("F d, Y");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | C-Gas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(11, 94, 215, 0.88), rgba(25, 135, 84, 0.82)),
                url('https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
            font-family: Arial, Helvetica, sans-serif;
        }

        .topbar {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(6px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }

        .brand-text {
            font-weight: 800;
            color: #fff;
            font-size: 1.3rem;
        }

        .page-wrap {
            padding: 30px 15px 40px;
        }

        .glass-card {
            border: none;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.22);
        }

        .hero-card {
            padding: 28px;
        }

        .welcome-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
        }

        .welcome-text {
            color: #6b7280;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            color: #fff;
            padding: 22px;
            height: 100%;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
        }

        .stat-blue {
            background: linear-gradient(135deg, #0d6efd, #3b82f6);
        }

        .stat-green {
            background: linear-gradient(135deg, #198754, #34d399);
        }

        .stat-dark {
            background: linear-gradient(135deg, #374151, #111827);
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 800;
        }

        .section-card {
            border: none;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.97);
            box-shadow: 0 14px 35px rgba(0, 0, 0, 0.14);
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #1f2937;
        }

        .info-label {
            color: #6b7280;
            font-size: 0.92rem;
            margin-bottom: 4px;
        }

        .info-value {
            color: #111827;
            font-weight: 700;
            margin-bottom: 16px;
            word-break: break-word;
        }

        .btn-main,
        .btn-outline-main {
            border-radius: 12px;
            padding: 11px 16px;
            font-weight: 700;
        }

        .btn-main {
            background: linear-gradient(90deg, #0d6efd, #0b5ed7);
            border: none;
        }

        .placeholder-box {
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08), rgba(25, 135, 84, 0.08));
            padding: 22px;
            border: 1px solid rgba(13, 110, 253, 0.1);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg topbar">
    <div class="container">

        <a class="navbar-brand brand-text" href="dashboard.php">
            C-Gas
        </a>

        <div class="ms-auto d-flex gap-2">

            <a href="logout.php" class="btn btn-danger btn-outline-light">
                Logout
            </a>

        </div>

    </div>
</nav>
    <div class="container page-wrap">
        <div class="glass-card hero-card mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="welcome-title">
                        Welcome, <?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?>
                    </div>
                    <div class="welcome-text mt-2">
                        Here is your C-Gas account overview, RFID details, and current rewards status.
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">

<?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) { ?>

    <a
    href="manage_accounts.php"
    class="btn btn-primary btn-main">

        Manage Accounts

    </a>

    <a
    href="audit_log.php"
    class="btn btn-dark btn-main">

        Audit Log

    </a>

    <a
    href="reports.php"
    class="btn btn-success btn-main">

        Reports

    </a>

<?php } else { ?>

<a
href="edit_account.php?id=<?php echo $_SESSION['user_id']; ?>"
class="btn btn-primary btn-main">

Manage Profile

</a>

<?php } ?>


               </div>
            </div>
        </div>

<?php
if (
isset(
$_SESSION["is_admin"]
)
&&
$_SESSION["is_admin"]
===
true
) {
?>

<div
class="row g-4 mb-4">

<div
class="col-md-4">

<div
class="stat-card stat-blue">

<div
class="stat-label">

Total Users

</div>

<div
class="stat-value">

<?php
echo
$totalUsers;
?>

</div>

</div>

</div>

<div
class="col-md-4">

<div
class="stat-card stat-green">

<div
class="stat-label">

Reward Points

</div>

<div
class="stat-value">

<?php
echo
$totalPoints;
?>

</div>

</div>

</div>

<div
class="col-md-4">

<div
class="stat-card stat-dark">

<div
class="stat-label">

Voucher Redeems

</div>

<div
class="stat-value">

<?php
echo
$totalRedeems;
?>

</div>

</div>

</div>

</div>

<?php
}
?>

<div class="row g-4 mb-4">

<div class="col-md-4">

    <div class="stat-card stat-blue">

        <div class="stat-label">
            Reward Points
        </div>

        <div class="stat-value">
            <?php
            echo htmlspecialchars(
                (string)
                $user["reward_points"]
            );
            ?>
        </div>

        <?php
        if (
            $user[
                "reward_points"
            ] >= 100
        ) {
        ?>

        <form
        action="generate_voucher.php"
        method="POST"
        class="mt-3">

            <button
            class="btn btn-success w-100">

                Redeem
                100 Points
                (₱100 Voucher)

            </button>

        </form>

        <?php } ?>

    </div>

</div>

    <div class="col-md-4">
        <div class="stat-card stat-green">
            <div class="stat-label">RFID UID</div>
            <div class="stat-value" style="font-size: 1.15rem;">
                <?php echo htmlspecialchars($user["rfid_uid"]); ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card stat-dark">
            <div class="stat-label">Account Status</div>
            <div class="stat-value">
                <?php echo htmlspecialchars($user["account_status"]); ?>
            </div>
        </div>
    </div>

</div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card section-card p-4 h-100">
                    <div class="section-title mb-4">Account Information</div>

                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["first_name"] . " " . $user["last_name"]); ?></div>

                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["email"]); ?></div>

                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($user["phone"] ?? "Not provided"); ?></div>

                    <div class="info-label">Member Since</div>
                    <div class="info-value"><?php echo htmlspecialchars($created_at !== "" ? $created_at : "N/A"); ?></div>
                </div>
            </div>

<div class="card section-card p-4 mt-4">
    <div class="section-title mb-3">
        Scan Payment QR
    </div>

    <p>
        Scan retailer QR to claim fuel points.
    </p>

<div id="reader" style="width:100%;"></div>

<div id="scan-result" class="mt-3">
    Waiting for QR scan...
</div>
            <div class="col-lg-6">
                <div class="card section-card p-4 h-100">
                    <div class="section-title mb-4">System Snapshot</div>

                    <div class="placeholder-box mb-3">
                        <h5 class="fw-bold mb-2">Cashless Fuel Payments</h5>
                        <div class="text-muted">
                            Your account is ready to support RFID-based payment processing and point accumulation.
                        </div>
                    </div>

                    <div class="placeholder-box">
                        <h5 class="fw-bold mb-2">Rewards Mechanism</h5>
                        <div class="text-muted">
                            Every valid transaction can contribute to your reward balance, making each refill more valuable.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>

window.onload = function () {

    const scanner =
    new Html5QrcodeScanner(
        "reader",
        {
            fps: 10,
            qrbox: 250
        }
    );

    function onScanSuccess(decodedText) {

        scanner.clear();

        document.getElementById(
            "scan-result"
        ).innerHTML =
        "QR detected... adding points...";

        fetch("claim_points.php", {
            method: "POST",
            headers: {
                "Content-Type":
                "application/x-www-form-urlencoded"
            },
            body:
            "qr_data=" +
            encodeURIComponent(decodedText)
        })
        .then(response => response.text())
.then(data => {

    document.getElementById(
        "scan-result"
    ).innerHTML = data;

    setTimeout(() => {
        location.reload();
    }, 1500);

})

        .catch(error => {

            document.getElementById(
                "scan-result"
            ).innerHTML =
            "Failed to add points.";

        });
    }

    scanner.render(
        onScanSuccess
    );
};

</script>

</body>
</html>
