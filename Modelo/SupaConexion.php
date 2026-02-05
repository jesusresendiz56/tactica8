<?php
$host = "aws-0-us-west-2.pooler.supabase.com";
$dbname = "postgres";
$user = "postgres.fbhirrxvzubnwnivrarl";
$password = "B4seD4tosT4ctica8";
$port = "5432";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

