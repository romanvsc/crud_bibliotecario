<?php
/**
 * Clase Database para conexión PDO
 * Utilizada para los endpoints de la API REST
 */
class Database {
    private $host = "localhost";
    private $db_name = "biblioteca";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Obtener conexión a la base de datos
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }
}
?>