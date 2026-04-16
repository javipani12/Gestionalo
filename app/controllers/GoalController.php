<?php
    class GoalController {

        /**
         * Muestra la pantalla de objetivos de ahorro del usuario.
         */
        public function mostrarObjetivosAhorro() {
            $titulo = 'Gestionalo | Objetivos de ahorro';
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $limitePorPagina = 10;
            $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $paginaActual = max(1, $paginaActual);

            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no identificado. Por favor, inicia sesión nuevamente.';
                require_once './../app/views/goals/goals.php';
                return;
            }

            try {
                $goalModel = new GoalModel();
                $resumen = $goalModel->obtenerResumenObjetivosConTotales($idUsuario);
                $totales = $resumen['totales'] ?? [
                    'meta' => 0.0,
                    'apartado' => 0.0,
                    'restante' => 0.0,
                    'activos' => 0,
                    'completados' => 0,
                ];

                $totalObjetivos = $goalModel->contarObjetivosPorUsuario($idUsuario);
                $totalPaginas = max(1, (int)ceil($totalObjetivos / $limitePorPagina));
                if ($paginaActual > $totalPaginas) {
                    $paginaActual = $totalPaginas;
                }

                $offset = ($paginaActual - 1) * $limitePorPagina;
                $objetivos = $goalModel->obtenerObjetivosPaginadosPorUsuario($idUsuario, $limitePorPagina, $offset);
            } catch (Throwable $e) {
                $_SESSION['error'] = 'No se pudieron cargar los objetivos de ahorro. Intentalo de nuevo en unos minutos.';
                error_log('GoalController::mostrarObjetivosAhorro -> ' . $e->getMessage());
            }

            require_once './../app/views/goals/goals.php';
        }

        /**
         * Muestra el formulario para crear un nuevo objetivo de ahorro.
         */
        public function mostrarFormularioCrearObjetivo() {
            $titulo = 'Gestionalo | Crear objetivo de ahorro';
            $objetivo = $_SESSION['goal_form_data'] ?? [
                'id_objetivo' => '',
                'nombre_objetivo' => '',
                'descripcion' => '',
                'cantidad_meta' => '',
                'fecha_inicio' => '',
                'fecha_limite' => '',
            ];

            unset($_SESSION['goal_form_data']);

            require_once './../app/views/goals/create_edit_goal.php';
        }

        /**
         * Muestra el formulario para editar un objetivo existente.
         */
        public function mostrarFormularioEditarObjetivo() {
            $titulo = 'Gestionalo | Editar objetivo de ahorro';
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $idObjetivo = (int)($_GET['id_objetivo'] ?? 0);

            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no identificado. Por favor, inicia sesión nuevamente.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            if ($idObjetivo <= 0) {
                $_SESSION['error'] = 'El objetivo seleccionado no es válido.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            try {
                $goalModel = new GoalModel();
                $objetivo = $_SESSION['goal_form_data'] ?? $goalModel->obtenerObjetivoPorIdUsuario($idUsuario, $idObjetivo);

                if (empty($objetivo)) {
                    $_SESSION['error'] = 'No se encontró el objetivo solicitado.';
                    header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                    exit();
                }

                $objetivo['id_objetivo'] = $idObjetivo;
                unset($_SESSION['goal_form_data']);
                require_once './../app/views/goals/create_edit_goal.php';
            } catch (Throwable $e) {
                error_log('GoalController::mostrarFormularioEditarObjetivo -> ' . $e->getMessage());
                $_SESSION['error'] = 'No se pudo cargar el objetivo para editar.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }
        }

        /**
         * Muestra el detalle de un objetivo con su historial asociado.
         */
        public function mostrarDetalleObjetivo() {
            $titulo = 'Gestionalo | Detalle de objetivo';
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $idObjetivo = (int)($_GET['id_objetivo'] ?? 0);

            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no identificado. Por favor, inicia sesión nuevamente.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            if ($idObjetivo <= 0) {
                $_SESSION['error'] = 'El objetivo seleccionado no es válido.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            try {
                $goalModel = new GoalModel();
                $objetivoDetalle = $goalModel->obtenerDetalleObjetivoPorIdUsuario($idUsuario, $idObjetivo);

                if (!$objetivoDetalle) {
                    $_SESSION['error'] = 'No se encontró el objetivo solicitado.';
                    header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                    exit();
                }

                $historialObjetivo = $goalModel->obtenerHistorialTransaccionesObjetivo($idUsuario, $idObjetivo, 30);
                require_once './../app/views/goals/goal_detail.php';
            } catch (Throwable $e) {
                error_log('GoalController::mostrarDetalleObjetivo -> ' . $e->getMessage());
                $_SESSION['error'] = 'No se pudo cargar el detalle del objetivo.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }
        }

        /**
         * Procesa el formulario y guarda un nuevo objetivo de ahorro.
         */
        public function guardarObjetivo() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?controller=goal&action=mostrarFormularioCrearObjetivo');
                exit();
            }

            $idObjetivo = (int)($_POST['id_objetivo'] ?? 0);
            $esEdicion = $idObjetivo > 0;
            $urlFormulario = $esEdicion
                ? 'index.php?controller=goal&action=mostrarFormularioEditarObjetivo&id_objetivo=' . $idObjetivo
                : 'index.php?controller=goal&action=mostrarFormularioCrearObjetivo';

            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no identificado. Por favor, inicia sesión nuevamente.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            $datosObjetivo = [
                'id_objetivo' => $idObjetivo,
                'nombre_objetivo' => trim((string)($_POST['nombre_objetivo'] ?? '')),
                'descripcion' => trim((string)($_POST['descripcion'] ?? '')),
                'cantidad_meta' => trim((string)($_POST['cantidad_meta'] ?? '')),
                'fecha_inicio' => trim((string)($_POST['fecha_inicio'] ?? '')),
                'fecha_limite' => trim((string)($_POST['fecha_limite'] ?? '')),
            ];

            $_SESSION['goal_form_data'] = $datosObjetivo;

            if ($datosObjetivo['nombre_objetivo'] === '') {
                $_SESSION['error'] = 'El nombre del objetivo es obligatorio.';
                header('Location: ' . $urlFormulario);
                exit();
            }

            if (!is_numeric($datosObjetivo['cantidad_meta']) || (float)$datosObjetivo['cantidad_meta'] <= 0) {
                $_SESSION['error'] = 'La cantidad meta debe ser mayor que cero.';
                header('Location: ' . $urlFormulario);
                exit();
            }

            if ($datosObjetivo['fecha_inicio'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datosObjetivo['fecha_inicio'])) {
                $_SESSION['error'] = 'La fecha de inicio no tiene un formato válido.';
                header('Location: ' . $urlFormulario);
                exit();
            }

            if ($datosObjetivo['fecha_limite'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datosObjetivo['fecha_limite'])) {
                $_SESSION['error'] = 'La fecha límite no tiene un formato válido.';
                header('Location: ' . $urlFormulario);
                exit();
            }

            if (
                $datosObjetivo['fecha_inicio'] !== ''
                && $datosObjetivo['fecha_limite'] !== ''
                && $datosObjetivo['fecha_inicio'] > $datosObjetivo['fecha_limite']
            ) {
                $_SESSION['error'] = 'La fecha de inicio no puede ser posterior a la fecha límite.';
                header('Location: ' . $urlFormulario);
                exit();
            }

            try {
                $goalModel = new GoalModel();
                $guardado = $esEdicion
                    ? $goalModel->actualizarObjetivo($idUsuario, $idObjetivo, $datosObjetivo)
                    : $goalModel->crearObjetivo($idUsuario, $datosObjetivo);

                if ($guardado) {
                    unset($_SESSION['goal_form_data']);
                    $_SESSION['correcto'] = $esEdicion
                        ? 'Objetivo de ahorro actualizado correctamente.'
                        : 'Objetivo de ahorro creado correctamente.';
                    header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                    exit();
                }

                $_SESSION['error'] = $esEdicion
                    ? 'No se pudo actualizar el objetivo de ahorro.'
                    : 'No se pudo crear el objetivo de ahorro.';
                header('Location: ' . $urlFormulario);
                exit();
            } catch (Throwable $e) {
                error_log('GoalController::guardarObjetivo -> ' . $e->getMessage());
                $_SESSION['error'] = 'Ha ocurrido un error al guardar el objetivo. Inténtalo de nuevo.';
                header('Location: ' . $urlFormulario);
                exit();
            }
        }

        /**
         * Elimina un objetivo del usuario.
         */
        public function eliminarObjetivo() {
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $idObjetivo = (int)($_GET['id_objetivo'] ?? 0);

            if ($idUsuario <= 0 || $idObjetivo <= 0) {
                $_SESSION['error'] = 'No se pudo eliminar el objetivo seleccionado.';
                header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
            }

            try {
                $goalModel = new GoalModel();
                if ($goalModel->eliminarObjetivo($idUsuario, $idObjetivo)) {
                    $_SESSION['correcto'] = 'Objetivo eliminado correctamente.';
                } else {
                    $_SESSION['error'] = 'No se pudo eliminar el objetivo.';
                }
            } catch (Throwable $e) {
                error_log('GoalController::eliminarObjetivo -> ' . $e->getMessage());
                $_SESSION['error'] = 'Ha ocurrido un error al eliminar el objetivo.';
            }

            header('Location: index.php?controller=goal&action=mostrarObjetivosAhorro');
                exit();
        }
    }
?>