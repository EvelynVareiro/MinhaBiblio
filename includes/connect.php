
<?php
$servername = "localhost";
$username = "root";
$password = "fajo";
$dbname = "db_minhabiblio";

$conn = new mysqli($servername, $username, $password, $dbname, 3306);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>