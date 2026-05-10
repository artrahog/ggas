<?php
require_once "auth.php";
require_once "db.php";

if (isLoggedIn()) {
if ($_SESSION["is_admin"]) {

    header("Location: dashboard.php");

} elseif ($_SESSION["is_retailer"]) {

    header("Location: retailer_dashboard.php");

} else {

    header("Location: dashboard.php");
}

exit();

}

$error = "";
$csrfToken = generateCsrfToken();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedToken = $_POST["csrf_token"] ?? "";

    if (!verifyCsrfToken($postedToken)) {
        $error = "Invalid request token. Please try again.";
    } else {
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($email === "" || $password === "") {
            $error = "Please enter your email and password.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $conn = getDbConnection();

$sql = "SELECT user_id, first_name, last_name, email,
               password_hash, account_status,
               reward_points, rfid_uid,
               failed_login_attempts, lockout_until,
               role
        FROM dbo.users
        WHERE email = ?";

            $params = array($email);

            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($row["account_status"] !== "Active") {
                    $error = "Your account is inactive.";
                } else {
                    $now = new DateTime();
                    $lockoutUntil = $row["lockout_until"];

                    if ($lockoutUntil instanceof DateTime && $lockoutUntil > $now) {
                        $error = "Account temporarily locked due to too many failed login attempts. Please try again later.";
                    } elseif (password_verify($password, $row["password_hash"])) {
                        $resetSql = "UPDATE dbo.users
                                     SET failed_login_attempts = 0,
                                         lockout_until = NULL,
                                         last_login_at = GETDATE(),
                                         updated_at = GETDATE()
                                     WHERE user_id = ?";
                        $resetParams = array($row["user_id"]);
                        $resetStmt = sqlsrv_query($conn, $resetSql, $resetParams);

                        if ($resetStmt) {
                            sqlsrv_free_stmt($resetStmt);
                        }

                        session_regenerate_id(true);

$_SESSION["user_id"] = $row["user_id"];
$_SESSION["first_name"] = $row["first_name"];
$_SESSION["last_name"] = $row["last_name"];
$_SESSION["email"] = $row["email"];
$_SESSION["role"] = $row["role"];
$_SESSION["reward_points"] = $row["reward_points"];
$_SESSION["rfid_uid"] = $row["rfid_uid"];

$_SESSION["is_admin"] =
    ($row["role"] === "admin");

$_SESSION["is_retailer"] =
    ($row["role"] === "retailer");

if ($row["role"] === "admin") {

    header("Location: dashboard.php");

} elseif ($row["role"] === "retailer") {

    header("Location: retailer_dashboard.php");

} else {

    header("Location: dashboard.php");
}

exit();

                    } else {
$failedAttempts = (int)$row["failed_login_attempts"];

$failedAttempts++;
if ($failedAttempts >= 3) {

    $updateSql = "UPDATE dbo.users
                  SET failed_login_attempts = failed_login_attempts + 1,
                      lockout_until = DATEADD(MINUTE, 15, GETDATE()),
                      updated_at = GETDATE()
                  WHERE user_id = ?";

    $updateParams = array($row["user_id"]);

    $error = "Too many failed login attempts. Your account has been locked for 15 minutes.";

} else {
    // Wrong password - handle failed attempts
    $failedAttempts = (int)$row["failed_login_attempts"] + 1;
    
    if ($failedAttempts >= 3) {
        $updateSql = "UPDATE dbo.users
                      SET failed_login_attempts = ?,
                          lockout_until = DATEADD(MINUTE, 15, GETDATE()),
                          updated_at = GETDATE()
                      WHERE user_id = ?";
        $updateParams = array($failedAttempts, $row["user_id"]);
        $error = "Too many failed login attempts. Account locked for 15 minutes.";
    } else {
        $updateSql = "UPDATE dbo.users
                      SET failed_login_attempts = ?,
                          updated_at = GETDATE()
                      WHERE user_id = ?";
        $updateParams = array($failedAttempts, $row["user_id"]);
        $remaining = 3 - $failedAttempts;
        $error = "Invalid password. {$remaining} attempt(s) remaining.";
    }
    
    $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);
    if ($updateStmt) {
        sqlsrv_free_stmt($updateStmt);
    }
}

                    }
                }
            } else {
                $error = "Account not found.";
            }

            if ($stmt) {
                sqlsrv_free_stmt($stmt);
            }
            sqlsrv_close($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C-Gas Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                linear-gradient(135deg, rgba(11, 94, 215, 0.88), rgba(25, 135, 84, 0.82)),
                url('https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
            font-family: Arial, Helvetica, sans-serif;
        }
        .page-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .login-card {
            width: 100%;
            max-width: 980px;
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.28);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
        }
        .brand-side {
            background: linear-gradient(160deg, #0d6efd, #198754);
            color: #fff;
            padding: 50px 42px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.16);
            color: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 18px;
            width: fit-content;
        }
        .brand-title {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .brand-text {
            font-size: 1rem;
            opacity: 0.95;
            margin-bottom: 28px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-list li {
            margin-bottom: 12px;
            font-size: 0.97rem;
            display: flex;
            align-items: center;
        }
        .feature-list li span {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: bold;
        }
        .form-side {
            padding: 48px 38px;
            background: rgba(255, 255, 255, 0.96);
        }
        .form-title {
            font-size: 1.9rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .form-subtitle {
            color: #6b7280;
            margin-bottom: 28px;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.16);
        }
        .btn-login {
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            background: linear-gradient(90deg, #0d6efd, #0b5ed7);
            border: none;
        }
        .btn-register {
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
        }
        .mini-note {
            font-size: 0.92rem;
            color: #6b7280;
            text-align: center;
            margin-top: 20px;
        }
        .system-title {
            font-weight: 800;
            color: #198754;
        }
        @media (max-width: 767px) {
            .brand-side {
                padding: 32px 24px;
            }
            .form-side {
                padding: 32px 24px;
            }
            .brand-title {
                font-size: 1.7rem;
            }
            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="card login-card">
            <div class="row g-0">
                <div class="col-md-6 d-none d-md-block">
                    <div class="brand-side">
                        <div class="brand-badge">RFID • Cashless • Rewards</div>
                        <div class="brand-title">C-Gas Payment Portal</div>
                        <div class="brand-text">
                            A smart gasoline payment system designed for fast RFID-based transactions,
                            secure account access, and a digital rewards mechanism.
                        </div>

                        <ul class="feature-list">
                            <li><span>✓</span> Faster cashless fuel payments</li>
                            <li><span>✓</span> RFID-enabled account access</li>
                            <li><span>✓</span> Real-time reward point tracking</li>
                            <li><span>✓</span> Secure user account management</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-side">
                        <h1 class="form-title">Welcome Back</h1>
                        <p class="form-subtitle">
                            Sign in to access your <span class="system-title">C-Gas</span> dashboard.
                        </p>

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger rounded-3"><?php echo sanitize($error); ?></div>
                        <?php } ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo sanitize($csrfToken); ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="Enter your email"
                                    required
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-login">Login</button>
                            </div>

                            <div class="d-grid">
                                <a href="register.php" class="btn btn-success btn-register">Create Account</a>
                            </div>
<div class="d-grid mt-3">
    <a href="oauth/login.php" class="btn btn-danger btn-lg">
        Login with Google
    </a>
</div>
                        </form>

                        <div class="mini-note">
                            Secure login for RFID-enabled gasoline payment users.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
