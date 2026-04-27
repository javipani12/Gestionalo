<?php
    class Database {
        private $host = "localhost";
        private $db_name = "gestionalo";
        private $user = "root";
        private $pass = "";

        public function conectar(){
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
                return new PDO($dsn, $this->user, $this->pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                throw new RuntimeException("Error de conexión a la base de datos.", 0, $e);
            }
        }
    }
?>