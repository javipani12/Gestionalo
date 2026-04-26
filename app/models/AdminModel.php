<?php

    class AdminModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Función para contar el total de usuarios registrados en el sistema excluyendo los eliminados
         */
        public function contarUsuariosActivos() {
            $sql = "SELECT COUNT(*) AS total
                FROM usuarios
                WHERE eliminado = 0
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        /**
         * Función para contar el total de consultas enviadas por los usuarios
         */
        public function contarConsultasTotales() {
            $sql = "SELECT COUNT(*) AS total
                FROM consultas
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        /**
         * Función para contar el total de consultas que no están finalizadas (pendientes)
         */
        public function contarConsultasPendientes() {
            $sql = "SELECT COUNT(*) AS total
                FROM consultas c
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                WHERE ec.nombre <> 'Finalizada'
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        /**
         * Función para contar el total de transacciones realizadas en el sistema
         */
        public function contarTransaccionesTotales() {
            $sql = "SELECT COUNT(*) AS total
                FROM transacciones
            ";

            return (int)$this->db->query($sql)->fetchColumn();
        }

        /**
         * Función para obtener la comparativa de usuarios nuevos registrados entre
         * el mes actual y el mes anterior, excluyendo los usuarios eliminados
         */
        public function obtenerComparativaUsuariosNuevos() {
            return $this->obtenerComparativaEntreFechas('usuarios', 'fecha_registro', 'eliminado = 0');
        }

        /**
         * Función para obtener la comparativa de consultas creadas entre
         * el mes actual y el mes anterior
         */
        public function obtenerComparativaConsultasCreadas() {
            return $this->obtenerComparativaEntreFechas('consultas', 'created_at');
        }

        /**
         * Función para obtener la comparativa de transacciones realizadas entre
         * el mes actual y el mes anterior
         */
        public function obtenerComparativaTransacciones() {
            return $this->obtenerComparativaEntreFechas('transacciones', 'fecha_movimiento');
        }

        /**
         * Obtiene las últimas consultas creadas en el sistema
         */
        public function obtenerUltimasConsultas($limite = 10) {
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

        /**
         * Obtiene los últimos usuarios registrados en el sistema, excluyendo los eliminados
         */
        public function obtenerUltimosUsuarios($limite = 10) {
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

        /**
         * Función genérica para contar registros en una tabla entre dos fechas,
         * con una condición extra opcional
         */
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

        /**
         * Función para obtener la comparativa de usuarios nuevos registrados entre
         * el mes actual y el mes anterior, excluyendo los usuarios eliminados
         */
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