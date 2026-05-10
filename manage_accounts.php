<?php
require_once "auth.php";
require_once "db.php";

requireLogin();

if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("Location: dashboard.php");
    exit();
}

$conn = getDbConnection();

$error = "";
$success = "";

/* DELETE USER */
if (isset($_GET["delete"])) {
    $delete_id = (int) $_GET["delete"];

    // Prevent admin from deleting themselves
    if ($delete_id === $_SESSION["user_id"]) {
        $error = "You cannot delete your own account.";
    } else {
        $deleteSql = "DELETE FROM dbo.users WHERE user_id = ?";
        $deleteParams = array($delete_id);

        $deleteStmt = sqlsrv_query($conn, $deleteSql, $deleteParams);

        if ($deleteStmt) {
            $success = "Account deleted successfully.";
logAudit(
    $conn,
    $_SESSION["user_id"],
    "DELETE_ACCOUNT",
    "Deleted user ID: " .
    $deleteId
);
        } else {
            $error = "Failed to delete account.";
        }

        if ($deleteStmt) {
            sqlsrv_free_stmt($deleteStmt);
        }
    }
}

/* GET USERS */
$sql = "SELECT 
            user_id,
            first_name,
            last_name,
            email,
            phone,
            rfid_uid,
            role,
            reward_points,
            account_status,
            created_at
        FROM dbo.users
        ORDER BY user_id DESC";

$stmt = sqlsrv_query($conn, $sql);

if (!$stmt) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts | C-Gas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0d6efd, #198754);
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }

        .page-title {
            font-weight: 800;
        }

        .table th {
            background: #0d6efd;
            color: white;
        }

        .badge-admin {
            background: #dc3545;
        }

        .badge-user {
            background: #198754;
        }
    </style>
</head>

<body>

<div class="container py-5">

    <div class="card card-custom p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    Manage Accounts
                </h2>

                <p class="text-muted mb-0">
                    View and manage all registered users.
                </p>
            </div>

            <div>
                <a href="dashboard.php" class="btn btn-success">
                    Back Dashboard
                </a>
            </div>
        </div>

        <?php if ($error !== "") { ?>
            <div class="alert alert-danger">
                <?php echo sanitize($error); ?>
            </div>
        <?php } ?>

        <?php if ($success !== "") { ?>
            <div class="alert alert-success">
                <?php echo sanitize($success); ?>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>RFID UID</th>
                        <th>Points</th>
                        <th>Status</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>

                    <tr>

                        <td>
                            <?php echo sanitize($row["user_id"]); ?>
                        </td>

                        <td>
                            <?php
                            echo sanitize(
                                $row["first_name"] . " " .
                                $row["last_name"]
                            );
                            ?>
                        </td>

                        <td>
                            <?php echo sanitize($row["email"]); ?>
                        </td>

                        <td>
                            <?php echo sanitize($row["phone"] ?? "N/A"); ?>
                        </td>

                        <td>
                            <?php echo sanitize($row["rfid_uid"]); ?>
                        </td>

                        <td>
                            <?php echo sanitize($row["reward_points"]); ?>
                        </td>

                        <td>
                            <?php echo sanitize($row["account_status"]); ?>
                        </td>

                        <td>
                            <?php if ($row["role"] === "admin") { ?>
                                <span class="badge badge-admin">
                                    ADMIN
                                </span>
                            <?php } else { ?>
                                <span class="badge badge-user">
                                    USER
                                </span>
                            <?php } ?>
                        </td>

                        <td>
                            <?php
                            echo $row["created_at"]
                                ? $row["created_at"]->format("Y-m-d")
                                : "N/A";
                            ?>
                        </td>

                        <td>

                            <a
                                href="edit_account.php?id=<?php echo $row["user_id"]; ?>"
                                class="btn btn-primary btn-sm">
                                Edit
                            </a>

                            <?php if ($row["user_id"] != $_SESSION["user_id"]) { ?>

                                <a
                                    href="?delete=<?php echo $row["user_id"]; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this user account?');">
                                    Delete
                                </a>

                            <?php } ?>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>
        </div>

    </div>

</div>

</body>
</html>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
