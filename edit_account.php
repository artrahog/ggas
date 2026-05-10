<?php
require_once "auth.php";
require_once "db.php";

requireLogin();

$conn = getDbConnection();

$error = "";
$success = "";
$csrfToken = generateCsrfToken();


$user_id =
intval(
    $_GET["id"] ?? 0
);

if (!$user_id) {
    die("Invalid user.");
}

$sql = "
SELECT
user_id,
first_name,
last_name,
email,
phone,
rfid_uid,
reward_points
FROM dbo.users
WHERE user_id = ?
";

$params = array(
    $user_id
);

$stmt = sqlsrv_query($conn, $sql, $params);
$user = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

if (!$user) {
    if ($stmt) {
        sqlsrv_free_stmt($stmt);
    }
    sqlsrv_close($conn);
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

$first_name = $user["first_name"];
$last_name = $user["last_name"];
$email = $user["email"];
$phone = $user["phone"];
$rfid_uid = $user["rfid_uid"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedToken = $_POST["csrf_token"] ?? "";

    if (!verifyCsrfToken($postedToken)) {
        $error = "Invalid request token. Please try again.";
    } else {
        $first_name = trim($_POST["first_name"] ?? "");
        $last_name = trim($_POST["last_name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
$rfid_uid =
trim(
    $_POST[
        "rfid_uid"
    ] ?? ""
);

$reward_points =
intval(
    $_POST[
        "reward_points"
    ] ?? 0
);

$new_password =
$_POST[
    "new_password"
] ?? "";
        $confirm_password = $_POST["confirm_password"] ?? "";

        if ($first_name === "" || $last_name === "" || $email === "" || $rfid_uid === "") {
            $error = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($new_password !== "" && !isValidPassword($new_password)) {
            $error = passwordRequirementsText();
        } elseif ($new_password !== "" && $new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } else {
$checkSql =
"SELECT user_id
FROM dbo.users
WHERE
(email = ? OR rfid_uid = ?)
AND user_id <> ?";
            $checkParams = array($email, $rfid_uid, $user_id);
            $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);

            if ($checkStmt && sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
                $error = "Email or RFID UID is already used by another account.";
            } else {

if ($new_password !== "") {

    $password_hash =
    password_hash(
        $new_password,
        PASSWORD_DEFAULT
    );

    $updateSql =
    "UPDATE dbo.users
    SET
    first_name = ?,
    last_name = ?,
    email = ?,
    phone = ?,
    rfid_uid = ?,
    reward_points = ?,
    password_hash = ?,
    updated_at = GETDATE()
    WHERE user_id = ?";

    $updateParams = array(
        $first_name,
        $last_name,
        $email,
        $phone,
        $rfid_uid,
$reward_points,
        $password_hash,
        $user_id
    );

} else {

    $updateSql =
    "UPDATE dbo.users
    SET
    first_name = ?,
    last_name = ?,
    email = ?,
    phone = ?,
    rfid_uid = ?,
    reward_points = ?,
    updated_at = GETDATE()
    WHERE user_id = ?";

$updateParams = array(
    $first_name,
    $last_name,
    $email,
    $phone,
    $rfid_uid,
    $reward_points,
    $user_id
);

}

                $updateStmt = sqlsrv_query($conn, $updateSql, $updateParams);

                if ($updateStmt) {
                    $_SESSION["first_name"] = $first_name;
                    $_SESSION["last_name"] = $last_name;
                    $_SESSION["email"] = $email;
                    $_SESSION["rfid_uid"] = $rfid_uid;
$user["reward_points"] =
$reward_points;

                    $success = "Account updated successfully.";
                } else {
                    $error = "Failed to update account.";
                }

                if ($updateStmt) {
                    sqlsrv_free_stmt($updateStmt);
                }
            }

            if ($checkStmt) {
                sqlsrv_free_stmt($checkStmt);
            }
        }
    }
}

if ($stmt) {
    sqlsrv_free_stmt($stmt);
}
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account | C-Gas</title>
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
            min-height: calc(100vh - 72px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .main-card {
            width: 100%;
            max-width: 1100px;
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.28);
            background: rgba(255, 255, 255, 0.95);
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
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 18px;
            width: fit-content;
        }
        .brand-title {
            font-size: 2.1rem;
            font-weight: 800;
            margin-bottom: 14px;
        }
        .brand-text-small {
            opacity: 0.95;
            margin-bottom: 24px;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-list li {
            margin-bottom: 12px;
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
            padding: 42px 36px;
            background: rgba(255, 255, 255, 0.97);
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
        .btn-main,
        .btn-back {
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
        }
        .btn-main {
            background: linear-gradient(90deg, #0d6efd, #0b5ed7);
            border: none;
        }
        .password-note {
            font-size: 0.9rem;
            color: #6b7280;
        }
        @media (max-width: 767px) {
            .brand-side {
                padding: 32px 24px;
            }
            .form-side {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg topbar">
        <div class="container">
            <a class="navbar-brand brand-text" href="dashboard.php">C-Gas</a>
            <div class="ms-auto d-flex gap-2">
                <a href="dashboard.php" class="btn btn-light btn-back">Dashboard</a>
                <a href="logout.php" class="btn btn-danger btn-back">Logout</a>
            </div>
        </div>
    </nav>

    <div class="page-wrap">
        <div class="card main-card">
            <div class="row g-0">
                <div class="col-md-5 d-none d-md-block">
                    <div class="brand-side">
                        <div class="brand-badge">Manage Profile</div>
                        <div class="brand-title">Edit Your Account</div>
                        <div class="brand-text-small">
                            Update your profile details, email, RFID UID, and password from one secure page.
                        </div>

                        <ul class="feature-list">
                            <li><span>✓</span> Update personal information</li>
                            <li><span>✓</span> Change RFID UID details</li>
                            <li><span>✓</span> Modify password securely</li>
                            <li><span>✓</span> Keep account information current</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="form-side">
                        <h1 class="form-title">Edit Account</h1>
                        <p class="form-subtitle">Review and update your C-Gas profile information.</p>

                        <?php if ($error !== "") { ?>
                            <div class="alert alert-danger rounded-3"><?php echo sanitize($error); ?></div>
                        <?php } ?>

                        <?php if ($success !== "") { ?>
                            <div class="alert alert-success rounded-3"><?php echo sanitize($success); ?></div>
                        <?php } ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo sanitize($csrfToken); ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo sanitize($first_name); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo sanitize($last_name); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($email); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo sanitize($phone ?? ""); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="rfid_uid" class="form-label">RFID UID</label>
                                    <input type="text" class="form-control" id="rfid_uid" name="rfid_uid" value="<?php echo sanitize($rfid_uid); ?>" required>
                                </div>
                            </div>

<div class="col-md-6 mb-3">

    <label
    for="reward_points"
    class="form-label">
        Reward Points
    </label>

    <input
    type="text"
    class="form-control"
    id="reward_points"
    name="reward_points"
    value="<?php echo $user['reward_points'] ?? 0; ?>">

</div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="new_password"
                                        name="new_password"
                                        pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$"
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <div class="password-note mb-4">
                                Leave the password blank if you do not want to change it. New password must meet the password rules.
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-main">Save Changes</button>
                            </div>

                            <div class="d-grid">
                                <a href="manage_accounts.php" class="btn btn-outline-success btn-back">Back to Edit Accounts</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
