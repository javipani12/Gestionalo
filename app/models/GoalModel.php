<?php
    class GoalModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Devuelve el resumen de objetivos con saldo real apartado y progreso.
         */
        public function obtenerResumenObjetivosUsuario($idUsuario) {
            $idUsuario = (int)$idUsuario;

            if ($idUsuario <= 0) {
                return [];
            }

            $sql = "SELECT
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.descripcion,
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
                    o.descripcion,
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
         * Devuelve objetivos y totales agregados para la vista.
         */
        public function obtenerResumenObjetivosConTotales($idUsuario) {
            $objetivos = $this->obtenerResumenObjetivosUsuario($idUsuario);
            $totales = [
                'meta' => 0.0,
                'apartado' => 0.0,
                'restante' => 0.0,
                'activos' => 0,
                'completados' => 0,
            ];

            foreach ($objetivos as $objetivo) {
                $meta = (float)($objetivo['cantidad_meta'] ?? 0);
                $apartado = (float)($objetivo['saldo_apartado'] ?? 0);
                $restante = max($meta - $apartado, 0);
                $estado = strtolower(trim((string)($objetivo['estado_objetivo'] ?? '')));

                $totales['meta'] += $meta;
                $totales['apartado'] += $apartado;
                $totales['restante'] += $restante;

                if ($estado === 'completado') {
                    $totales['completados']++;
                } elseif ($estado !== '') {
                    $totales['activos']++;
                }
            }

            return [
                'objetivos' => $objetivos,
                'totales' => $totales,
            ];
        }

        /**
         * Cuenta el total de objetivos del usuario.
         */
        public function contarObjetivosPorUsuario($idUsuario) {
            $idUsuario = (int)$idUsuario;

            if ($idUsuario <= 0) {
                return 0;
            }

            $sql = "SELECT COUNT(*)
                FROM objetivos_ahorro
                WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        /**
         * Devuelve objetivos paginados del usuario.
         */
        public function obtenerObjetivosPaginadosPorUsuario($idUsuario, $limite, $offset) {
            $idUsuario = (int)$idUsuario;
            $limite = max(1, (int)$limite);
            $offset = max(0, (int)$offset);

            if ($idUsuario <= 0) {
                return [];
            }

            $sql = "SELECT
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.descripcion,
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
                    o.descripcion,
                    o.cantidad_meta,
                    o.fecha_inicio,
                    o.fecha_limite,
                    eo.nombre
                ORDER BY o.created_at DESC
                LIMIT :limite OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Inserta un nuevo objetivo de ahorro para el usuario.
         */
        public function crearObjetivo($idUsuario, $datosObjetivo) {
            $idUsuario = (int)$idUsuario;

            if ($idUsuario <= 0) {
                return false;
            }

            $nombreObjetivo = trim((string)($datosObjetivo['nombre_objetivo'] ?? ''));
            $descripcion = trim((string)($datosObjetivo['descripcion'] ?? ''));
            $cantidadMeta = (float)($datosObjetivo['cantidad_meta'] ?? 0);
            $fechaInicio = trim((string)($datosObjetivo['fecha_inicio'] ?? ''));
            $fechaLimite = trim((string)($datosObjetivo['fecha_limite'] ?? ''));

            $sql = "INSERT INTO objetivos_ahorro (
                    id_usuario,
                    nombre_objetivo,
                    descripcion,
                    cantidad_meta,
                    fecha_inicio,
                    fecha_limite
                ) VALUES (
                    :id_usuario,
                    :nombre_objetivo,
                    :descripcion,
                    :cantidad_meta,
                    :fecha_inicio,
                    :fecha_limite
                )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre_objetivo', $nombreObjetivo, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad_meta', $cantidadMeta, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_limite', $fechaLimite, PDO::PARAM_STR);
            
            return $stmt->execute();
        }

        /**
         * Obtiene un objetivo por ID validando propietario.
         */
        public function obtenerObjetivoPorIdUsuario($idUsuario, $idObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return null;
            }

            $sql = "SELECT
                    id_objetivo,
                    nombre_objetivo,
                    descripcion,
                    cantidad_meta,
                    fecha_inicio,
                    fecha_limite
                FROM objetivos_ahorro
                WHERE id_objetivo = :id_objetivo
                AND id_usuario = :id_usuario
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $objetivo ?: null;
        }

        /**
         * Actualiza un objetivo existente validando propietario.
         */
        public function actualizarObjetivo($idUsuario, $idObjetivo, $datosObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return false;
            }

            $nombreObjetivo = trim((string)($datosObjetivo['nombre_objetivo'] ?? ''));
            $descripcion = trim((string)($datosObjetivo['descripcion'] ?? ''));
            $cantidadMeta = (float)($datosObjetivo['cantidad_meta'] ?? 0);
            $fechaInicio = trim((string)($datosObjetivo['fecha_inicio'] ?? ''));
            $fechaLimite = trim((string)($datosObjetivo['fecha_limite'] ?? ''));

            $sql = "UPDATE objetivos_ahorro
                SET nombre_objetivo = :nombre_objetivo,
                    descripcion = :descripcion,
                    cantidad_meta = :cantidad_meta,
                    fecha_inicio = :fecha_inicio,
                    fecha_limite = :fecha_limite,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id_objetivo = :id_objetivo
                AND id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre_objetivo', $nombreObjetivo, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            $stmt->bindValue(':cantidad_meta', $cantidadMeta, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_limite', $fechaLimite, PDO::PARAM_STR);
            
            return $stmt->execute();
        }

        /**
         * Elimina un objetivo validando propietario.
         */
        public function eliminarObjetivo($idUsuario, $idObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return false;
            }

            $sql = "DELETE FROM objetivos_ahorro
                WHERE id_objetivo = :id_objetivo
                AND id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);

            return $stmt->execute();
        }
    }
?>