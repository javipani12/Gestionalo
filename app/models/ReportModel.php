<?php
    class ReportModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Devuelve los informes de un usuario ordenados por fecha de generacion descendente.
         */
        public function obtenerInformesPorUsuario($idUsuario) {
            $sql = "SELECT
                        i.id_informe,
                        i.nombre_informe,
                        i.ruta_archivo,
                        i.fecha_generacion,
                        ti.nombre AS tipo_informe
                    FROM informes i
                    LEFT JOIN tipos_informe ti ON ti.id_tipo_informe = i.id_tipo_informe
                    WHERE i.id_usuario = :id_usuario
                    ORDER BY i.fecha_generacion DESC, i.id_informe DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Cuenta los informes de un usuario.
         */
        public function contarInformesPorUsuario($idUsuario) {
            $sql = "SELECT COUNT(*) AS total
                    FROM informes
                    WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($resultado['total'] ?? 0);
        }

        /**
         * Devuelve los informes de un usuario paginados ordenados por fecha de generacion descendente.
         */
        public function obtenerInformesPaginadosPorUsuario($idUsuario, $limite, $offset) {
            $sql = "SELECT
                        i.id_informe,
                        i.nombre_informe,
                        i.ruta_archivo,
                        i.fecha_generacion,
                        ti.nombre AS tipo_informe
                    FROM informes i
                    LEFT JOIN tipos_informe ti ON ti.id_tipo_informe = i.id_tipo_informe
                    WHERE i.id_usuario = :id_usuario
                    ORDER BY i.fecha_generacion DESC, i.id_informe DESC
                    LIMIT :limite OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Recupera un informe concreto de un usuario para validar descarga segura.
         */
        public function obtenerInformePorIdYUsuario($idInforme, $idUsuario) {
            $sql = "SELECT
                        i.id_informe,
                        i.id_usuario,
                        i.nombre_informe,
                        i.ruta_archivo,
                        i.fecha_generacion,
                        ti.nombre AS tipo_informe
                    FROM informes i
                    LEFT JOIN tipos_informe ti ON ti.id_tipo_informe = i.id_tipo_informe
                    WHERE i.id_informe = :id_informe
                      AND i.id_usuario = :id_usuario
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_informe', (int)$idInforme, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /**
         * Crea un registro de informe y devuelve el id insertado.
         */
        public function crearInforme($idUsuario, $nombreInforme, $idTipoInforme, $rutaArchivo) {
            $sql = "INSERT INTO informes (id_usuario, nombre_informe, id_tipo_informe, ruta_archivo)
                    VALUES (:id_usuario, :nombre_informe, :id_tipo_informe, :ruta_archivo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre_informe', (string)$nombreInforme, PDO::PARAM_STR);

            if ($idTipoInforme === null) {
                $stmt->bindValue(':id_tipo_informe', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':id_tipo_informe', (int)$idTipoInforme, PDO::PARAM_INT);
            }

            $stmt->bindValue(':ruta_archivo', (string)$rutaArchivo, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$this->db->lastInsertId();
        }

        /**
         * Obtiene el id de tipo de informe por nombre.
         */
        public function obtenerIdTipoInformePorNombre($nombreTipo) {
            $sql = "SELECT id_tipo_informe
                    FROM tipos_informe
                    WHERE nombre = :nombre
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', (string)$nombreTipo, PDO::PARAM_STR);
            $stmt->execute();

            $valor = $stmt->fetchColumn();
            return $valor !== false ? (int)$valor : null;
        }
    }
?>