<?php
    class GraphicModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Devuelve los datos base para la herramienta de graficos.
         */
        public function obtenerDatasetUsuario($idUsuario) {
            $idUsuario = (int)$idUsuario;

            if ($idUsuario <= 0) {
                return [
                    'transacciones' => [],
                    'objetivos' => [],
                    'catalogos' => [
                        'tipos_movimiento' => [],
                        'categorias' => [],
                        'subcategorias' => [],
                        'metodos_pago' => [],
                    ],
                ];
            }

            return [
                'transacciones' => $this->obtenerTransaccionesUsuario($idUsuario),
                'objetivos' => $this->obtenerObjetivosUsuario($idUsuario),
                'catalogos' => $this->obtenerCatalogos(),
            ];
        }

        /**
         * Devuelve todas las transacciones del usuario para filtros en cliente.
         */
        private function obtenerTransaccionesUsuario($idUsuario) {
            $sql = "SELECT
                    t.id_transaccion,
                    t.id_usuario,
                    t.id_categoria,
                    t.id_subcategoria,
                    t.id_objetivo,
                    t.id_tipo,
                    t.concepto,
                    t.fecha_movimiento,
                    t.id_metodo,
                    t.importe,
                    tm.nombre AS tipo_movimiento,
                    c.nombre_categoria,
                    s.nombre_subcategoria,
                    mp.nombre AS metodo_pago
                FROM transacciones t
                INNER JOIN tipos_movimiento tm ON tm.id_tipo = t.id_tipo
                LEFT JOIN categorias c ON c.id_categoria = t.id_categoria
                LEFT JOIN subcategorias s ON s.id_subcategoria = t.id_subcategoria
                LEFT JOIN metodos_pago mp ON mp.id_metodo = t.id_metodo
                WHERE t.id_usuario = :id_usuario
                ORDER BY t.fecha_movimiento DESC, t.id_transaccion DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Devuelve objetivos con ahorro actual y progreso.
         */
        private function obtenerObjetivosUsuario($idUsuario) {
            $sql = "SELECT
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.cantidad_meta,
                    o.fecha_inicio,
                    o.fecha_limite,
                    eo.nombre AS estado_objetivo,
                    COALESCE(SUM(
                        CASE
                            WHEN tm.nombre = 'Transferencia Interna Aporte' THEN t.importe
                            WHEN tm.nombre = 'Transferencia Interna Retiro' THEN -t.importe
                            ELSE 0
                        END
                    ), 0) AS saldo_apartado,
                    CASE
                        WHEN o.cantidad_meta > 0 THEN ROUND(
                            (COALESCE(SUM(
                                CASE
                                    WHEN tm.nombre = 'Transferencia Interna Aporte' THEN t.importe
                                    WHEN tm.nombre = 'Transferencia Interna Retiro' THEN -t.importe
                                    ELSE 0
                                END
                            ), 0) / o.cantidad_meta) * 100,
                            2
                        )
                        ELSE 0
                    END AS progreso_pct
                FROM objetivos_ahorro o
                INNER JOIN estados_objetivo eo ON eo.id_estado = o.id_estado
                LEFT JOIN transacciones t ON t.id_objetivo = o.id_objetivo
                    AND t.id_usuario = o.id_usuario
                LEFT JOIN tipos_movimiento tm ON tm.id_tipo = t.id_tipo
                WHERE o.id_usuario = :id_usuario
                GROUP BY
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.cantidad_meta,
                    o.fecha_inicio,
                    o.fecha_limite,
                    eo.nombre
                ORDER BY o.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Devuelve catalogos para construir filtros en cliente.
         */
        private function obtenerCatalogos() {
            $defaultDataModel = new DefaultDataModel();

            return [
                'tipos_movimiento' => $defaultDataModel->obtenerTodos('tipos_movimiento'),
                'categorias' => $defaultDataModel->obtenerTodos('categorias'),
                'subcategorias' => $defaultDataModel->obtenerSubcategoriasConCategoria(),
                'metodos_pago' => $defaultDataModel->obtenerTodos('metodos_pago'),
            ];
        }
    }
?>