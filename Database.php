<?php
class Database {
    private $host = "localhost";
    private $db_name = "toko_baju";  // Ganti dengan nama database Anda
    private $username = "root";      // Ganti dengan username database Anda
    private $password = "";          // Ganti dengan password database Anda
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
