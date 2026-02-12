<?php
// Modelo/SupaConexion.php - VERSIÓN CON CLASE
class SupaConexion {
    private $host = "aws-0-us-west-2.pooler.supabase.com";
    private $dbname = "postgres";
    private $user = "postgres.fbhirrxvzubnwnivrarl";
    private $password = "B4seD4tosT4ctica8";
    private $port = "5432";
    public $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname};sslmode=require",
                $this->user,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "";
            
        } catch (PDOException $e) {
            echo "❌ Error de conexión: " . $e->getMessage();
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