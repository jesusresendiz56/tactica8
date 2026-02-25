<?php
class SupaConexion {

    public $conn;

    public function __construct() {

        $host = getenv("DB_HOST");
        $dbname = getenv("DB_NAME");
        $user = getenv("DB_USER");
        $password = getenv("DB_PASS");
        $port = getenv("DB_PORT");

        try {
            $this->conn = new PDO(
                "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require",
                $user,
                $password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            die();
        }
    }

    public function getConexion() {
        return $this->conn;
    }
}

$db = new SupaConexion();
$conn = $db->getConexion();
?>