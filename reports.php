<?php
require_once "auth.php";
require_once "db.php";

requireLogin();

$conn =
getDbConnection();

$sql =
"SELECT
first_name,
last_name,
email,
reward_points
FROM dbo.users
ORDER BY
reward_points DESC";

$stmt =
sqlsrv_query(
    $conn,
    $sql
);
?>

<!DOCTYPE html>
<html>
<head>

<title>
Reports
</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body
class="container mt-4">

<h2>
Generated Report
</h2>

<button
onclick="window.print()"
class="btn btn-success mb-3">

Print Report

</button>

<a
href="dashboard.php"
class="btn btn-primary mb-3">

Dashboard

</a>

<table
class="table table-bordered">

<tr>
<th>Name</th>
<th>Email</th>
<th>Reward Points</th>
</tr>

<?php
while (
$row =
sqlsrv_fetch_array(
$stmt,
SQLSRV_FETCH_ASSOC
)
) {
?>

<tr>

<td>
<?php
echo htmlspecialchars(
$row[
"first_name"
]
.
" "
.
$row[
"last_name"
]
);
?>
</td>

<td>
<?php
echo htmlspecialchars(
$row[
"email"
]
);
?>
</td>

<td>
<?php
echo
$row[
"reward_points"
];
?>
</td>

</tr>

<?php
}
?>

</table>

</body>
</html>
