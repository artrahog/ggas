<?php
require_once "auth.php";
require_once "db.php";

requireLogin();

if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "retailer"
) {
    header("Location: dashboard.php");
    exit();
}

$qrText = "";
$points = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $amount = floatval($_POST["amount"]);
    $liters = floatval($_POST["liters"]);

    $points = floor($amount / 100);

$transactionId = uniqid();

$paymentData = json_encode([
    "id" => $transactionId,
    "a" => round($amount, 2),
    "l" => round($liters, 2)
]);

$qrText = urlencode($paymentData);

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Retailer Dashboard</title>

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

        <a class="navbar-brand brand-text"
           href="#">
            C-Gas Retailer
        </a>

        <div class="ms-auto d-flex gap-2">

            <a href="logout.php"
               class="btn btn-danger btn-outline-light">
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
                    Retailer Dashboard
                </div>

                <div class="welcome-text mt-2">
                    Generate QR payments and reward points
                    for customers.
                </div>

            </div>

        </div>

    </div>

<div class="card section-card p-4 mb-4">

    <div class="section-title mb-3">
        Scan Redeem Voucher
    </div>

    <p>
        Scan customer
        voucher QR.
    </p>

    <div
    id="reader"
    style="
    width:300px;
    max-width:100%;
    ">
    </div>

    <form
    id="voucherForm"
    method="POST"
    action="redeem_voucher.php">

        <input
        type="hidden"
        id="qr_data"
        name="qr_data">

    </form>

</div>

    <div class="row g-4">

        <div class="col-lg-6">

            <div class="card section-card p-4">

                <div class="section-title mb-4">
                    Generate Fuel Payment QR
                </div>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">
                            Gas Liters
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            name="liters"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Amount Paid (₱)
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            name="amount"
                            class="form-control"
                            required
                        >
                    </div>

                    <button
                        class="btn btn-primary btn-main w-100">

                        Generate QR

                    </button>

                </form>

            </div>

        </div>

        <div class="col-lg-6">

            <div class="card section-card p-4 text-center">

                <div class="section-title mb-4">
                    Customer QR Code
                </div>

                <?php if ($qrText !== "") { ?>

                    <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=500x500&margin=40&data=<?php echo urlencode($qrText); ?>"
                    class="img-fluid">

                    <div class="mt-4">

                        <h4>
                            Reward Points:
                            <?php echo $points; ?>
                        </h4>

                    </div>

                <?php } else { ?>

                    <div class="text-muted mt-5">
                        QR code will appear here
                    </div>

                <?php } ?>

            </div>

        </div>

    </div>

</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>

function onScanSuccess(
    decodedText
) {

    document
    .getElementById(
        "qr_data"
    )
    .value =
    decodedText;

    document
    .getElementById(
        "voucherForm"
    )
    .submit();
}

new Html5QrcodeScanner(
    "reader",
    {
        fps: 10,
        qrbox: 250
    }
).render(
    onScanSuccess
);

</script>

</body>

</html>
