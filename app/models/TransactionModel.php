<?php
    class TransactionModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }
        
        /**
         * Obtiene las transacciones de un usuario específico
         */
        public function obtenerTransaccionesPorUsuario($id_usuario) {
            $sql = 
                "SELECT 
                    t.id_transaccion, 
                    tm.nombre AS tipo_movimiento, 
                    c.nombre_categoria, 
                    s.nombre_subcategoria,
                    t.concepto, 
                    t.fecha_movimiento, 
                    mp.nombre AS metodo_pago, 
                    t.importe
                FROM transacciones t
                INNER JOIN tipos_movimiento tm ON t.id_tipo = tm.id_tipo
                INNER JOIN categorias c ON t.id_categoria = c.id_categoria
                INNER JOIN subcategorias s ON t.id_subcategoria = s.id_subcategoria
                INNER JOIN metodos_pago mp ON t.id_metodo = mp.id_metodo
                WHERE id_usuario = :id_usuario
                ORDER BY fecha_movimiento DESC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Obtiene las últimas 10 transacciones de un usuario específico
         */
        public function obtenerUltimasDiezTransacciones($id_usuario) {
            $sql = 
                "SELECT
                    tm.nombre AS tipo_movimiento,
                    t.id_transaccion, 
                    c.nombre_categoria, 
                    s.nombre_subcategoria,
                    t.concepto,
                    t.fecha_movimiento, 
                    t.importe
                FROM transacciones t
                INNER JOIN categorias c ON t.id_categoria = c.id_categoria
                INNER JOIN subcategorias s ON t.id_subcategoria = s.id_subcategoria
                INNER JOIN tipos_movimiento tm ON t.id_tipo = tm.id_tipo
                WHERE id_usuario = :id_usuario
                ORDER BY fecha_movimiento DESC
                LIMIT 10
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Obtiene una transacción específica por su ID para un usuario determinado
         */
        public function obtenerTransaccionPorId($id_transaccion) {
            $sql = 
                "SELECT 
                    t.id_transaccion, 
                    t.id_usuario,
                    t.id_tipo,
                    tm.nombre AS tipo_movimiento, 
                    c.id_categoria,
                    c.nombre_categoria, 
                    s.id_subcategoria,
                    s.nombre_subcategoria,
                    t.concepto, 
                    t.fecha_movimiento, 
                    mp.id_metodo,
                    mp.nombre AS metodo_pago, 
                    t.importe
                FROM transacciones t
                INNER JOIN tipos_movimiento tm ON t.id_tipo = tm.id_tipo
                INNER JOIN categorias c ON t.id_categoria = c.id_categoria
                INNER JOIN subcategorias s ON t.id_subcategoria = s.id_subcategoria
                INNER JOIN metodos_pago mp ON t.id_metodo = mp.id_metodo
                WHERE id_transaccion = :id_transaccion
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_transaccion', $id_transaccion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Agrega una nueva transacción para un usuario
         */
        public function agregarTransaccion($id_usuario, $datosTransaccion) {
            $sql = 
                "INSERT INTO transacciones (
                    id_usuario, 
                    id_categoria,
                    id_subcategoria,
                    id_tipo,
                    concepto,
                    fecha_movimiento,
                    id_metodo,   
                    importe,
                    created_at
                )
                VALUES (
                    :id_usuario,
                    :id_categoria,
                    :id_subcategoria,
                    :id_tipo,
                    :concepto,
                    :fecha_movimiento,
                    :id_metodo, 
                    :importe,
                    CURRENT_TIMESTAMP
                )";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_categoria', $datosTransaccion['id_categoria'], PDO::PARAM_INT);
            $stmt->bindValue(':id_subcategoria', $datosTransaccion['id_subcategoria'], PDO::PARAM_INT);
            $stmt->bindValue(':id_tipo', $datosTransaccion['id_tipo'], PDO::PARAM_INT);
            $stmt->bindValue(':concepto', $datosTransaccion['concepto'], PDO::PARAM_STR);
            $stmt->bindValue(':fecha_movimiento', $datosTransaccion['fecha_movimiento'], PDO::PARAM_STR);
            $stmt->bindValue(':id_metodo', $datosTransaccion['id_metodo'], PDO::PARAM_INT);
            $stmt->bindValue(':importe', $datosTransaccion['importe'], PDO::PARAM_STR);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }

        }

        /**
         * Modifica una transacción existente para un usuario
         */
        public function modificarTransaccion($id_transaccion, $datosTransaccion) {
            $sql = 
                "UPDATE transacciones 
                SET 
                    id_categoria = :id_categoria, 
                    id_subcategoria = :id_subcategoria,
                    id_tipo = :id_tipo,
                    concepto = :concepto,
                    fecha_movimiento = :fecha_movimiento,
                    id_metodo = :id_metodo,   
                    importe = :importe,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id_transaccion = :id_transaccion
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_transaccion', $id_transaccion, PDO::PARAM_INT);
            $stmt->bindValue(':id_categoria', $datosTransaccion['id_categoria'], PDO::PARAM_INT);
            $stmt->bindValue(':id_subcategoria', $datosTransaccion['id_subcategoria'], PDO::PARAM_INT);
            $stmt->bindValue(':id_tipo', $datosTransaccion['id_tipo'], PDO::PARAM_INT);
            $stmt->bindValue(':concepto', $datosTransaccion['concepto'], PDO::PARAM_STR);
            $stmt->bindValue(':id_metodo', $datosTransaccion['id_metodo'], PDO::PARAM_INT);
            $stmt->bindValue(':fecha_movimiento', $datosTransaccion['fecha_movimiento'], PDO::PARAM_STR);
            $stmt->bindValue(':importe', $datosTransaccion['importe'], PDO::PARAM_STR);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Elimina una transacción específica para un usuario
         */
        public function eliminarTransaccion($id_transaccion) {
            $sql = "DELETE FROM transacciones 
                WHERE id_transaccion = :id_transaccion
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_transaccion', $id_transaccion, PDO::PARAM_INT);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }
    }
?>