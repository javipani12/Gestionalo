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
                    o.id_estado,
                    o.cantidad_final,
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

            $objetivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($objetivos as &$objetivo) {
                $objetivo = $this->sincronizarObjetivoPorCalculo($idUsuario, $objetivo);
            }
            unset($objetivo);

            return $objetivos;
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
                    o.id_estado,
                    o.cantidad_final,
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

            $objetivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($objetivos as &$objetivo) {
                $objetivo = $this->sincronizarObjetivoPorCalculo($idUsuario, $objetivo);
            }
            unset($objetivo);

            return $objetivos;
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

        /**
         * Obtiene el detalle resumen de un objetivo validando propietario.
         */
        public function obtenerDetalleObjetivoPorIdUsuario($idUsuario, $idObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return null;
            }

            $sql = "SELECT
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.descripcion,
                    o.cantidad_meta,
                    o.fecha_inicio,
                    o.fecha_limite,
                    o.id_estado,
                    o.cantidad_final,
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
                AND o.id_objetivo = :id_objetivo
                GROUP BY
                    o.id_objetivo,
                    o.nombre_objetivo,
                    o.descripcion,
                    o.cantidad_meta,
                    o.fecha_inicio,
                    o.fecha_limite,
                    eo.nombre
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->execute();

            $objetivo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $objetivo ? $this->sincronizarObjetivoPorCalculo($idUsuario, $objetivo) : null;
        }

        /**
         * Devuelve el historial de transacciones asociadas al objetivo.
         */
        public function obtenerHistorialTransaccionesObjetivo($idUsuario, $idObjetivo, $limite = 30, $offset = 0) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;
            $limite = max(1, (int)$limite);
            $offset = max(0, (int)$offset);

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return [];
            }

            $sql = "SELECT
                    t.id_transaccion,
                    tm.nombre AS tipo_movimiento,
                    t.concepto,
                    t.fecha_movimiento,
                    t.importe,
                    c.nombre_categoria,
                    s.nombre_subcategoria,
                    mp.nombre AS metodo_pago
                FROM transacciones t
                INNER JOIN tipos_movimiento tm ON tm.id_tipo = t.id_tipo
                LEFT JOIN categorias c ON c.id_categoria = t.id_categoria
                LEFT JOIN subcategorias s ON s.id_subcategoria = t.id_subcategoria
                LEFT JOIN metodos_pago mp ON mp.id_metodo = t.id_metodo
                WHERE t.id_usuario = :id_usuario
                AND t.id_objetivo = :id_objetivo
                ORDER BY t.fecha_movimiento DESC, t.id_transaccion DESC
                LIMIT :limite OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Cuenta el total de transacciones asociadas al objetivo.
         */
        public function contarHistorialTransaccionesObjetivo($idUsuario, $idObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return 0;
            }

            $sql = "SELECT COUNT(*)
                FROM transacciones
                WHERE id_usuario = :id_usuario
                AND id_objetivo = :id_objetivo";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        /**
         * Devuelve los objetivos en curso del usuario para usar en transacciones.
         * Si se indica un objetivo extra, lo incluye aunque ya no este en curso.
         */
        public function obtenerObjetivosEnCursoPorUsuario($idUsuario, $idObjetivoIncluido = 0) {
            $idUsuario = (int)$idUsuario;
            $idObjetivoIncluido = (int)$idObjetivoIncluido;

            if ($idUsuario <= 0) {
                return [];
            }

            $sql = "SELECT
                    o.id_objetivo,
                    o.nombre_objetivo,
                    eo.nombre AS estado_objetivo
                FROM objetivos_ahorro o
                INNER JOIN estados_objetivo eo ON eo.id_estado = o.id_estado
                WHERE o.id_usuario = :id_usuario
                AND (
                    LOWER(TRIM(eo.nombre)) = 'en curso'
                    OR (:id_objetivo_incluido_habilitado > 0 AND o.id_objetivo = :id_objetivo_incluido)
                )
                ORDER BY o.nombre_objetivo ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo_incluido_habilitado', $idObjetivoIncluido, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo_incluido', $idObjetivoIncluido, PDO::PARAM_INT);
            $stmt->execute();

            $objetivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($objetivos as &$objetivo) {
                $objetivo = $this->sincronizarObjetivoPorCalculo($idUsuario, $objetivo);

                $estadoObjetivo = strtolower(trim((string)($objetivo['estado_objetivo'] ?? '')));
                if ($estadoObjetivo !== 'en curso' && (int)($objetivo['id_objetivo'] ?? 0) !== $idObjetivoIncluido) {
                    $objetivo['__excluir'] = true;
                }
            }
            unset($objetivo);

            return array_values(array_filter($objetivos, function ($objetivo) {
                return empty($objetivo['__excluir']);
            }));
        }

        /**
         * Valida que un objetivo pertenezca al usuario y siga en curso.
         */
        public function esObjetivoEnCursoDeUsuario($idUsuario, $idObjetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)$idObjetivo;

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return false;
            }

            $sql = "SELECT 1
                FROM objetivos_ahorro o
                INNER JOIN estados_objetivo eo ON eo.id_estado = o.id_estado
                WHERE o.id_usuario = :id_usuario
                AND o.id_objetivo = :id_objetivo
                AND LOWER(TRIM(eo.nombre)) = 'en curso'
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();
        }

        /**
         * Recalcula y persiste el estado/cantidad final según fecha límite y progreso.
         */
        private function sincronizarObjetivoPorCalculo($idUsuario, array $objetivo) {
            $idUsuario = (int)$idUsuario;
            $idObjetivo = (int)($objetivo['id_objetivo'] ?? 0);

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                return $objetivo;
            }

            $progresoPct = (float)($objetivo['progreso_pct'] ?? 0);
            $saldoApartado = (float)($objetivo['saldo_apartado'] ?? 0);
            $fechaLimiteTexto = trim((string)($objetivo['fecha_limite'] ?? ''));
            $estadoActual = strtolower(trim((string)($objetivo['estado_objetivo'] ?? '')));
            $idEstadoActual = (int)($objetivo['id_estado'] ?? 0);
            $cantidadFinalActual = $objetivo['cantidad_final'] ?? null;

            $nuevoEstado = 'en curso';
            $debeActualizarCantidadFinal = false;
            $nuevaCantidadFinal = $cantidadFinalActual;

            $fechaLimite = null;
            if ($fechaLimiteTexto !== '') {
                $fechaLimite = DateTime::createFromFormat('Y-m-d', $fechaLimiteTexto) ?: null;
            }

            if ($fechaLimite instanceof DateTimeInterface) {
                $hoy = new DateTime('today');

                if ($hoy >= $fechaLimite) {
                    $debeActualizarCantidadFinal = true;
                    $nuevaCantidadFinal = $saldoApartado;
                    $nuevoEstado = $progresoPct >= 100 ? 'completado' : 'no completado';
                } elseif ($progresoPct >= 100) {
                    $nuevoEstado = 'completado';
                }
            } elseif ($progresoPct >= 100) {
                $nuevoEstado = 'completado';
            }

            $idNuevoEstado = $this->obtenerIdEstadoObjetivoPorNombre($nuevoEstado);
            $requiereCambioEstado = $idNuevoEstado > 0 && $idNuevoEstado !== $idEstadoActual;
            $requiereCambioCantidadFinal = $debeActualizarCantidadFinal && (string)$nuevaCantidadFinal !== (string)$cantidadFinalActual;

            if ($requiereCambioEstado || $requiereCambioCantidadFinal) {
                $sql = "UPDATE objetivos_ahorro
                    SET id_estado = :id_estado,
                        cantidad_final = :cantidad_final,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id_objetivo = :id_objetivo
                    AND id_usuario = :id_usuario";

                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':id_estado', $idNuevoEstado > 0 ? $idNuevoEstado : $idEstadoActual, PDO::PARAM_INT);

                if ($debeActualizarCantidadFinal) {
                    $stmt->bindValue(':cantidad_final', $nuevaCantidadFinal, PDO::PARAM_STR);
                } else {
                    $stmt->bindValue(':cantidad_final', $cantidadFinalActual, is_null($cantidadFinalActual) ? PDO::PARAM_NULL : PDO::PARAM_STR);
                }

                $stmt->bindValue(':id_objetivo', $idObjetivo, PDO::PARAM_INT);
                $stmt->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
                $stmt->execute();

                $objetivo['id_estado'] = $idNuevoEstado > 0 ? $idNuevoEstado : $idEstadoActual;
                $objetivo['estado_objetivo'] = $nuevoEstado;
                $objetivo['cantidad_final'] = $debeActualizarCantidadFinal ? $nuevaCantidadFinal : $cantidadFinalActual;
            }

            return $objetivo;
        }

        /**
         * Obtiene el ID de un estado de objetivo a partir de su nombre.
         */
        private function obtenerIdEstadoObjetivoPorNombre($nombreEstado) {
            $nombreEstado = strtolower(trim((string)$nombreEstado));

            if ($nombreEstado === '') {
                return 0;
            }

            $sql = "SELECT id_estado
                FROM estados_objetivo
                WHERE LOWER(TRIM(nombre)) = :nombre
                LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $nombreEstado, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }
    }
?>