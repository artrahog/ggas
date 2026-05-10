<?php
require_once "auth.php";
require_once "db.php";

requireLogin();

$conn =
getDbConnection();

$sql =
"SELECT
a.log_id,
a.action_type,
a.description,
a.created_at,
u.first_name,
u.last_name
FROM dbo.audit_logs a
LEFT JOIN dbo.users u
ON a.user_id =
u.user_id
ORDER BY
a.created_at DESC";

$stmt =
sqlsrv_query(
    $conn,
    $sql
);

if ($stmt === false) {
    die(
        print_r(
            sqlsrv_errors(),
            true
        )
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Audit Log
</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body {
    background:
    #f5f7fb;
}

.audit-card {
    background:
    white;

    border-radius:
    15px;

    padding:
    30px;

    margin-top:
    40px;

    box-shadow:
    0 4px 20px
    rgba(
        0,
        0,
        0,
        0.08
    );
}

.table th {
    background:
    #0d6efd;

    color:
    white;
}

</style>

</head>

<body>

<div
class="container">

<div
class="audit-card">

<div
class="d-flex
justify-content-between
align-items-center
mb-4">

<h2>
Audit Log
</h2>

<a
href="dashboard.php"
class="btn btn-primary">

Back to Dashboard

</a>

</div>

<div
class="table-responsive">

<table
class="table
table-bordered
table-striped">

<thead>

<tr>

<th>ID</th>
<th>User</th>
<th>Action</th>
<th>Description</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php
while (
$log =
sqlsrv_fetch_array(
$stmt,
SQLSRV_FETCH_ASSOC
)
) {
?>

<tr>

<td>
<?php
echo
$log[
"log_id"
];
?>
</td>

<td>
<?php
echo htmlspecialchars(
trim(
(
$log[
"first_name"
]
??
"Unknown"
)
.
" "
.
(
$log[
"last_name"
]
??
""
)
)
);
?>
</td>

<td>
<?php
echo htmlspecialchars(
$log[
"action_type"
]
);
?>
</td>

<td>
<?php
echo htmlspecialchars(
$log[
"description"
]
);
?>
</td>

<td>
<?php
echo
$log[
"created_at"
]
->format(
"Y-m-d H:i:s"
);
?>
</td>

</tr>

<?php
}
?>

</tbody>

</table>

</div>

</div>

</div>

</body>
</html>
