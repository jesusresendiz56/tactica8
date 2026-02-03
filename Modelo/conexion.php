<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tactica8";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Falló la conexión: " . mysqli_connect_error());
}


 echo "Conexión exitosa";
