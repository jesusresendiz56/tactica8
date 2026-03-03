<?php
/*
Este archivo define la clase SupaConexion, la cual se encarga
de establecer la conexión entre el sistema web y la base
de datos PostgreSQL.

La conexión utiliza variables de entorno para mayor seguridad,
evitando que las credenciales estén escritas directamente
en el código.

FUNCIONAMIENTO GENERAL:
1. Obtiene las credenciales desde variables de entorno.
2. Crea una conexión PDO hacia PostgreSQL.
3. Activa el modo de manejo de errores por excepción.
4. Permite obtener la conexión mediante el método getConexion().
5. Al final del archivo, se crea una instancia lista para usarse.
*/

class SupaConexion {

    // Variable pública que almacenará la conexión activa
    public $conn;

    // Constructor que se ejecuta automáticamente al crear la clase
    public function __construct() {

        // Obtención de credenciales desde variables de entorno
        $host = getenv("DB_HOST");
        $dbname = getenv("DB_NAME");
        $user = getenv("DB_USER");
        $password = getenv("DB_PASS");
        $port = getenv("DB_PORT");

        try {
            // Creación de la conexión usando PDO para PostgreSQL
            $this->conn = new PDO(
                "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require",
                $user,
                $password
            );

            // Configuración para que los errores se manejen como excepciones
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {

            // En caso de error de conexión, muestra el mensaje y detiene la ejecución
            echo "Error de conexión: " . $e->getMessage();
            die();
        }
    }

    // Método público para obtener la conexión desde otros archivos
    public function getConexion() {
        return $this->conn;
    }
}

// Creación de la instancia de la clase
$db = new SupaConexion();

// Obtención de la conexión lista para usarse en consultas
$conn = $db->getConexion();

?>