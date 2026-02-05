<?php
$host = "aws-0-us-west-2.pooler.supabase.com";
$dbname = "postgres";
$user = "postgres.fbhirrxvzubnwnivrarl";
$password = "B4seD4tosT4ctica8";
$port = "5432";

try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    echo "Conexión exitosa";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

