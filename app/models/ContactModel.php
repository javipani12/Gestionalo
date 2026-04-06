<?php
    class ContactModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Cuenta el total de consultas enviadas por un usuario específico.
         */
        public function contarConsultasPorUsuario($idUsuario) {
            $sql = 
                "SELECT COUNT(*) AS total
                FROM consultas
                WHERE id_usuario = :id_usuario
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }


        /**
         * Cuenta el total de consultas enviadas.
         */
        public function contarTodasConsultas() {
            $sql = 
                "SELECT COUNT(*) AS total
                FROM consultas
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        /**
         * Cuenta cuántas consultas ha enviado hoy un usuario específico.
         */
        public function contarConsultasDiariasPorUsuario($idUsuario) {
            $sql = 
                "SELECT COUNT(*) AS total
                FROM consultas
                WHERE id_usuario = :id_usuario
                AND DATE(created_at) = CURDATE()
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        /**
         * Obtiene las consultas de un usuario paginadas, permitiendo navegar por el historial
         * sin cargar todos los registros de una sola vez.
         */
        public function obtenerConsultasPaginadasPorUsuario($idUsuario, $limite, $offset) {
            $sql = 
                "SELECT 
                    c.id_consulta, 
                    c.created_at AS fecha_creacion,
                    a.nombre AS asunto,
                    c.comentario,  
                    c.respuesta,
                    ec.nombre AS estado
                FROM consultas c
                INNER JOIN asuntos a ON c.id_asunto = a.id_asunto
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                WHERE c.id_usuario = :id_usuario
                ORDER BY c.created_at DESC
                LIMIT :limite OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Obtiene todas las consultas de la aplicación paginadas, permitiendo navegar por el historial
         * sin cargar todos los registros de una sola vez.
         */
        public function obtenerTodasConsultasPaginadas($limite, $offset) {
            $sql = 
                "SELECT 
                    c.id_consulta, 
                    c.created_at AS fecha_creacion,
                    a.nombre AS asunto,
                    c.comentario,  
                    c.respuesta,
                    ec.nombre AS estado,
                    u.email AS email_usuario
                FROM consultas c
                INNER JOIN asuntos a ON c.id_asunto = a.id_asunto
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                ORDER BY c.created_at DESC
                LIMIT :limite OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Permite a un usuario enviar una nueva consulta al equipo de soporte,
         */
        public function crearConsulta($idUsuario, $idAsunto, $comentario) {
            $sql = 
                "INSERT INTO consultas (id_usuario, id_asunto, comentario, id_estado) 
                VALUES (:id_usuario, :id_asunto, :comentario, 1)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_asunto', $idAsunto, PDO::PARAM_INT);
            $stmt->bindValue(':comentario', $comentario, PDO::PARAM_STR);
            return $stmt->execute();
        }

        /**
         * Obtiene los detalles de una consulta específica por su ID
         */
        public function obtenerConsultaPorId($idConsulta) {
            $sql = 
                "SELECT 
                    c.id_consulta, 
                    c.id_estado,
                    c.created_at AS fecha_creacion,
                    a.nombre AS asunto,
                    c.comentario,  
                    c.respuesta,
                    ec.nombre AS estado,
                    u.email AS email_usuario
                FROM consultas c
                INNER JOIN asuntos a ON c.id_asunto = a.id_asunto
                INNER JOIN estados_consulta ec ON c.id_estado = ec.id_estado
                INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.id_consulta = :id_consulta
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_consulta', $idConsulta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Permite al equipo de soporte actualizar la respuesta y el estado de una consulta específica
         */
        public function actualizarRespuestaConsulta($idConsulta, $respuesta, $idEstado) {
            $sql = 
                "UPDATE consultas 
                SET respuesta = :respuesta, id_estado = :id_estado, updated_at = CURRENT_TIMESTAMP 
                WHERE id_consulta = :id_consulta
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':respuesta', $respuesta, PDO::PARAM_STR);
            $stmt->bindValue(':id_estado', $idEstado, PDO::PARAM_INT);
            $stmt->bindValue(':id_consulta', $idConsulta, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
?>