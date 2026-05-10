<?php

$serverName = "localhost";

$connectionOptions = array(
    "Database" => "Ggas",
    "Uid" => "sa",
    "PWD" => "Betlog1Betlog",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

?><?php

$serverName = "LAPTOP-ENEJPVPG\\SQLEXPRESS";

$connectionOptions = array(
    "Database" => "Ggas",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>
