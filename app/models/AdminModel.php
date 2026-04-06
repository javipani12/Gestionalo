<?php

    class AdminModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        public function contarUsuariosTotales() {
            $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE eliminado = 0
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        public function contarConsultasTotales() {
            $sql = "SELECT COUNT(*) AS total
                FROM consultas
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        public function contarConsultasPendientes() {
            $sql = "SELECT COUNT(*) AS total
                FROM consultas c
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                WHERE ec.nombre <> 'Finalizada'
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        public function contarTransaccionesDelMes() {
            $inicioMes = date('Y-m-01 00:00:00');
            $inicioMesSiguiente = date('Y-m-01 00:00:00', strtotime('first day of next month'));

            return $this->contarRegistrosEntreFechas('transacciones', 'fecha_movimiento', $inicioMes, $inicioMesSiguiente);
        }

        public function obtenerComparativaUsuariosNuevos() {
            return $this->obtenerComparativaEntreFechas('usuarios', 'fecha_registro', 'eliminado = 0');
        }

        public function obtenerComparativaConsultasCreadas() {
            return $this->obtenerComparativaEntreFechas('consultas', 'created_at');
        }

        public function obtenerComparativaTransacciones() {
            return $this->obtenerComparativaEntreFechas('transacciones', 'fecha_movimiento');
        }

        public function obtenerUltimasConsultas($limite = 5) {
            $sql = "SELECT
                    c.id_consulta,
                    c.created_at,
                    c.comentario,
                    a.nombre AS asunto,
                    ec.nombre AS estado,
                    u.nombre,
                    u.apellido1
                FROM consultas c
                INNER JOIN asuntos a ON c.id_asunto = a.id_asunto
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                ORDER BY c.created_at DESC
                LIMIT :limite
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function obtenerUltimasTransacciones($limite = 5) {
            $sql = "SELECT
                    t.id_transaccion,
                    tm.nombre AS tipo_movimiento,
                    c.nombre_categoria,
                    s.nombre_subcategoria,
                    t.concepto,
                    t.fecha_movimiento,
                    t.importe,
                    u.nombre,
                    u.apellido1
                FROM transacciones t
                INNER JOIN tipos_movimiento tm ON t.id_tipo = tm.id_tipo
                LEFT JOIN categorias c ON t.id_categoria = c.id_categoria
                LEFT JOIN subcategorias s ON t.id_subcategoria = s.id_subcategoria
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                ORDER BY t.fecha_movimiento DESC
                LIMIT :limite
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function obtenerUltimosUsuarios($limite = 5) {
            $sql = "SELECT
                    id_usuario,
                    nombre,
                    apellido1,
                    email,
                    fecha_registro
                FROM usuarios
                WHERE eliminado = 0
                ORDER BY fecha_registro DESC
                LIMIT :limite
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function obtenerUltimosInformes($limite = 5) {
            $sql = "SELECT
                    i.id_informe,
                    i.nombre_informe,
                    i.ruta_archivo,
                    i.fecha_generacion,
                    ti.nombre AS tipo_informe,
                    u.nombre,
                    u.apellido1
                FROM informes i
                INNER JOIN usuarios u ON i.id_usuario = u.id_usuario
                LEFT JOIN tipos_informe ti ON i.id_tipo_informe = ti.id_tipo_informe
                ORDER BY i.fecha_generacion DESC
                LIMIT :limite
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        private function contarRegistrosEntreFechas($tabla, $campoFecha, $inicio, $fin, $condicionExtra = '') {
            $sql = "SELECT COUNT(*) AS total
                FROM {$tabla}
                WHERE {$campoFecha} >= :inicio
                AND {$campoFecha} < :fin
            ";

            if (!empty($condicionExtra)) {
                $sql .= " AND {$condicionExtra}";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':inicio', $inicio, PDO::PARAM_STR);
            $stmt->bindValue(':fin', $fin, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        private function obtenerComparativaEntreFechas($tabla, $campoFecha, $condicionExtra = '') {
            $inicioMesActual = date('Y-m-01 00:00:00');
            $inicioMesSiguiente = date('Y-m-01 00:00:00', strtotime('first day of next month'));
            $inicioMesAnterior = date('Y-m-01 00:00:00', strtotime('first day of previous month'));

            $actual = $this->contarRegistrosEntreFechas($tabla, $campoFecha, $inicioMesActual, $inicioMesSiguiente, $condicionExtra);
            $anterior = $this->contarRegistrosEntreFechas($tabla, $campoFecha, $inicioMesAnterior, $inicioMesActual, $condicionExtra);

            return [
                'actual' => $actual,
                'anterior' => $anterior
            ];
        }
    }

?>