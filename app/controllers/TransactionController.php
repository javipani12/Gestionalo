<?php
    class TransactionController {
        
        /**
         * Muestra la lista de transacciones del usuario en la vista correspondiente
         */
        public function mostrarTransaccionesUsuario(){
            $titulo = "Gestionalo | Mis Transacciones";
            $transactionModel = new TransactionModel();
            $defaultDataModel = new DefaultDataModel();
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $limitePorPagina = 10;
            $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $paginaActual = max(1, $paginaActual);

            // Recogemos y validamos los filtros de búsqueda
            $filtrosActivos = [
                'concepto' => trim($_GET['concepto'] ?? ''),
                'id_tipo' => (int)($_GET['id_tipo'] ?? 0),
                'id_categoria' => (int)($_GET['id_categoria'] ?? 0),
                'id_subcategoria' => (int)($_GET['id_subcategoria'] ?? 0),
                'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
                'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
                'id_metodo' => (int)($_GET['id_metodo'] ?? 0)
            ];

            // Validamos que los filtros numéricos no sean negativos
            if($filtrosActivos['id_tipo'] < 0) {
                $filtrosActivos['id_tipo'] = 0;
            }

            if($filtrosActivos['id_categoria'] < 0) {
                $filtrosActivos['id_categoria'] = 0;
            }

            if($filtrosActivos['id_subcategoria'] < 0) {
                $filtrosActivos['id_subcategoria'] = 0;
            }

            if($filtrosActivos['id_metodo'] < 0) {
                $filtrosActivos['id_metodo'] = 0;
            }

            // Validamos que las fechas tengan el formato correcto (YYYY-MM-DD)
            // y que la fecha desde no sea mayor que la fecha hasta
            if(
                $filtrosActivos['fecha_desde'] !== ''
                && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtrosActivos['fecha_desde'])
            ) {
                $filtrosActivos['fecha_desde'] = '';
            }

            if(
                $filtrosActivos['fecha_hasta'] !== ''
                && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtrosActivos['fecha_hasta'])
            ) {
                $filtrosActivos['fecha_hasta'] = '';
            }

            if(
                $filtrosActivos['fecha_desde'] !== ''
                && $filtrosActivos['fecha_hasta'] !== ''
                && $filtrosActivos['fecha_desde'] > $filtrosActivos['fecha_hasta']
            ) {
                $fechaTemporal = $filtrosActivos['fecha_desde'];
                $filtrosActivos['fecha_desde'] = $filtrosActivos['fecha_hasta'];
                $filtrosActivos['fecha_hasta'] = $fechaTemporal;
            }

            // Validamos que la subcategoría seleccionada corresponda a la categoría seleccionada
            if(
                $filtrosActivos['id_subcategoria'] > 0
                && $filtrosActivos['id_categoria'] > 0
                && !$defaultDataModel->validarSubcategoriaDeCategoria(
                    $filtrosActivos['id_subcategoria'],
                    $filtrosActivos['id_categoria']
                )
            ) {
                $filtrosActivos['id_subcategoria'] = 0;
            }

            // Validamos los parámetros de ordenación
            $camposOrdenPermitidos = ['tipo', 'categoria', 'subcategoria', 'concepto', 'fecha', 'metodo', 'importe'];
            $ordenCampo = $_GET['orden_campo'] ?? 'fecha';
            if(!in_array($ordenCampo, $camposOrdenPermitidos, true)) {
                $ordenCampo = 'fecha';
            }

            // Validamos que la dirección de ordenación sea 'asc' o 'desc'
            $ordenDireccion = strtolower($_GET['orden_direccion'] ?? 'desc');
            if($ordenDireccion !== 'asc' && $ordenDireccion !== 'desc') {
                $ordenDireccion = 'desc';
            }

            // Obtenemos el total de transacciones para el usuario con los filtros aplicados
            $totalTransacciones = $transactionModel->contarTransaccionesPorUsuario($idUsuario, $filtrosActivos);
            $totalPaginas = max(1, (int)ceil($totalTransacciones / $limitePorPagina));
            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }

            // Obtenemos las transacciones paginadas para el usuario con los filtros y ordenación aplicados
            $offset = ($paginaActual - 1) * $limitePorPagina;
            $transacciones = $transactionModel->obtenerTransaccionesPaginadasPorUsuario(
                $idUsuario,
                $limitePorPagina,
                $offset,
                $filtrosActivos,
                $ordenCampo,
                $ordenDireccion
            );
            
            // Obtenemos los datos necesarios para los filtros y la visualización
            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $categorias = $defaultDataModel->obtenerTodos('categorias');
            $subcategorias = $defaultDataModel->obtenerSubcategoriasConCategoria();
            $metodosPago = $defaultDataModel->obtenerTodos('metodos_pago');

            require_once './../app/views/transaction/transactions.php';
            exit();
        }

        /**
         * Muestra el formulario para crear una nueva transacción
         */
        public function mostrarFormularioCrearTransaccion(){
            $titulo = "Gestionalo | Crear Transacción";
            $transactionModel = new TransactionModel();
            $defaultDataModel = new DefaultDataModel();
            $goalModel = new GoalModel();
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $limiteDiarioTransacciones = 20;
            $transaccionesHoy = $transactionModel->contarTransaccionesDiariasPorUsuario($idUsuario);
            $puedeCrearTransaccion = $transaccionesHoy < $limiteDiarioTransacciones;
            $categorias = $defaultDataModel->obtenerTodos('categorias');
            $subcategorias = $defaultDataModel->obtenerSubcategoriasConCategoria();
            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $metodosPago = $defaultDataModel->obtenerTodos('metodos_pago');

            $idObjetivoPreseleccionado = max(0, (int)($_GET['id_objetivo'] ?? 0));
            $modoObjetivo = strtolower(trim((string)($_GET['modo_objetivo'] ?? '')));
            $idsTransferenciasInternas = $this->obtenerIdsTransferenciasInternas($tiposMovimiento);
            $idTipoPreseleccionado = 0;
            $redirigirAObjetivoId = $idObjetivoPreseleccionado;

            if ($modoObjetivo === 'aporte') {
                $idTipoPreseleccionado = (int)($idsTransferenciasInternas['aporte'] ?? 0);
            } elseif ($modoObjetivo === 'retiro') {
                $idTipoPreseleccionado = (int)($idsTransferenciasInternas['retiro'] ?? 0);
            }

            $objetivosEnCurso = $goalModel->obtenerObjetivosEnCursoPorUsuario($idUsuario, $idObjetivoPreseleccionado);
            if ($idObjetivoPreseleccionado > 0 && !$this->listaContieneObjetivo($objetivosEnCurso, $idObjetivoPreseleccionado)) {
                $idObjetivoPreseleccionado = 0;
            }

            require_once './../app/views/transaction/create_edit_transaction.php';
            exit();
        }

        /**
         * Muestra el formulario para editar una transacción existente
         */
        public function mostrarFormularioEditarTransaccion(){
            $titulo = "Gestionalo | Editar Transacción";
            $transactionModel = new TransactionModel();
            $defaultDataModel = new DefaultDataModel();
            $goalModel = new GoalModel();
            $categorias = $defaultDataModel->obtenerTodos('categorias');
            $subcategorias = $defaultDataModel->obtenerSubcategoriasConCategoria();
            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $metodosPago = $defaultDataModel->obtenerTodos('metodos_pago');
            $transaccion = $transactionModel->obtenerTransaccionPorId($_GET['id_transaccion']);
            $idObjetivoTransaccion = (int)($transaccion['id_objetivo'] ?? 0);
            $objetivosEnCurso = $goalModel->obtenerObjetivosEnCursoPorUsuario($_SESSION['usuario']['id_usuario'], $idObjetivoTransaccion);
            $idTipoPreseleccionado = 0;
            $idObjetivoPreseleccionado = 0;

            require_once './../app/views/transaction/create_edit_transaction.php';
            exit();
        }

        /**
         * Procesa el formulario para crear o actualizar una transacción
         * Si se recibe un id_transaccion, se actualiza la transacción existente, 
         * de lo contrario se crea una nueva transacción para el usuario
         */
        public function guardarTransaccion() {
            $transactionModel = new TransactionModel();
            $defaultDataModel = new DefaultDataModel();
            $goalModel = new GoalModel();
            $limiteDiarioTransacciones = 20;
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $redirigirAObjetivoId = max(0, (int)($_POST['redirigir_objetivo_id'] ?? 0));
            $urlRedireccionExito = 'index.php?controller=transaction&action=mostrarTransaccionesUsuario';

            if ($redirigirAObjetivoId > 0) {
                $urlRedireccionExito = 'index.php?controller=goal&action=mostrarDetalleObjetivo&id_objetivo=' . $redirigirAObjetivoId;
            }

            if (empty($_POST['id_transaccion'])) {
                $transaccionesHoy = $transactionModel->contarTransaccionesDiariasPorUsuario($idUsuario);
                if ($transaccionesHoy >= $limiteDiarioTransacciones) {
                    $_SESSION['error'] = "Has alcanzado el límite diario de {$limiteDiarioTransacciones} transacciones. Podrás crear más mañana.";
                    header('Location: index.php?controller=transaction&action=mostrarFormularioCrearTransaccion');
                    exit();
                }
            }

            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $idsTransferenciasInternas = $this->obtenerIdsTransferenciasInternas($tiposMovimiento);

            // Recogemos los datos del formulario
            $datosTransaccion = [
                'id_transaccion' => $_POST['id_transaccion'] ?? null,
                'id_categoria' => $_POST['id_categoria'] ?? null,
                'id_subcategoria' => $_POST['id_subcategoria'] ?? null,
                'id_objetivo' => $_POST['id_objetivo'] ?? null,
                'id_tipo' => $_POST['id_tipo'] ?? null,
                'concepto' => trim((string)($_POST['concepto'] ?? '')),
                'fecha_movimiento' => trim((string)($_POST['fecha_movimiento'] ?? '')),
                'id_metodo' => $_POST['id_metodo'] ?? null,
                'importe' => $_POST['importe'] ?? null
            ];

            $idTipo = (int)$datosTransaccion['id_tipo'];
            $idObjetivo = (int)$datosTransaccion['id_objetivo'];
            $idsInternos = array_filter([
                (int)($idsTransferenciasInternas['aporte'] ?? 0),
                (int)($idsTransferenciasInternas['retiro'] ?? 0)
            ]);
            $esTransferenciaInterna = in_array($idTipo, $idsInternos, true);
            $objetivoAnterior = null;

            if (!empty($_POST['id_transaccion'])) {
                $objetivoAnterior = $transactionModel->obtenerTransaccionPorId((int)$_POST['id_transaccion']);
            }

            // Hacemos validaciones básicas
            $esCategoriaValida = $defaultDataModel->existeId('categorias', $datosTransaccion['id_categoria']);
            $esSubcategoriaValida = $defaultDataModel->existeId('subcategorias', $datosTransaccion['id_subcategoria']);
            $esTipoValido = $defaultDataModel->existeId('tipos_movimiento', $datosTransaccion['id_tipo']);
            $esMetodoValido = $defaultDataModel->existeId('metodos_pago', $datosTransaccion['id_metodo']);
            $esRelacionCategoriaSubcategoriaValida = $defaultDataModel->validarSubcategoriaDeCategoria(
                $datosTransaccion['id_subcategoria'],
                $datosTransaccion['id_categoria']
            );

            if (!$esTipoValido || !$esMetodoValido) {
                $_SESSION['error'] = "Los datos de selección enviados no son válidos.";
                header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                exit();
            }

            if ($esTransferenciaInterna) {
                $esEdicion = !empty($_POST['id_transaccion']);
                $objetivoPerteneceUsuario = $goalModel->obtenerObjetivoPorIdUsuario($idUsuario, $idObjetivo) !== null;

                if ($idObjetivo <= 0 || !$objetivoPerteneceUsuario) {
                    $_SESSION['error'] = "Debes seleccionar un objetivo en curso válido para la transferencia interna.";
                    header('Location: index.php?controller=transaction&action=mostrarFormularioCrearTransaccion');
                    exit();
                }

                if (!$esEdicion && !$goalModel->esObjetivoEnCursoDeUsuario($idUsuario, $idObjetivo)) {
                    $_SESSION['error'] = "Debes seleccionar un objetivo en curso válido para la transferencia interna.";
                    header('Location: index.php?controller=transaction&action=mostrarFormularioCrearTransaccion');
                    exit();
                }

                $datosTransaccion['id_objetivo'] = $idObjetivo;
                $datosTransaccion['id_categoria'] = null;
                $datosTransaccion['id_subcategoria'] = null;
            } else {
                $datosTransaccion['id_objetivo'] = null;

                if(!$esCategoriaValida || !$esSubcategoriaValida || !$esRelacionCategoriaSubcategoriaValida) {
                    $_SESSION['error'] = "Los datos de selección enviados no son válidos.";
                    header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                    exit();
                }
            }

            if($_POST['id_transaccion'] !== '') {
                // Actualizamos la transacción existente
                if($transactionModel->modificarTransaccion($_POST['id_transaccion'], $datosTransaccion)) {
                    $objetivosASincronizar = [];

                    if (!empty($objetivoAnterior['id_objetivo'])) {
                        $objetivosASincronizar[] = (int)$objetivoAnterior['id_objetivo'];
                    }

                    if (!empty($datosTransaccion['id_objetivo'])) {
                        $objetivosASincronizar[] = (int)$datosTransaccion['id_objetivo'];
                    }

                    foreach (array_values(array_unique(array_filter($objetivosASincronizar))) as $idObjetivoSincronizar) {
                        $goalModel->obtenerDetalleObjetivoPorIdUsuario($idUsuario, $idObjetivoSincronizar);
                    }

                    $_SESSION['correcto'] = "Transacción modificada correctamente.";
                    header('Location: ' . $urlRedireccionExito);
                    exit();
                } else {
                    $_SESSION['error'] = "Error al modificar la transacción.";
                    header('Location: ' . $urlRedireccionExito);
                    echo "Error al modificar la transacción.";
                }
            } else {
                // Agregamos una nueva transacción para el usuario
                if($transactionModel->agregarTransaccion($_SESSION['usuario']['id_usuario'], $datosTransaccion)) {
                    if (!empty($datosTransaccion['id_objetivo'])) {
                        $goalModel->obtenerDetalleObjetivoPorIdUsuario($idUsuario, (int)$datosTransaccion['id_objetivo']);
                    }

                    $_SESSION['correcto'] = "Transacción agregada correctamente.";
                    header('Location: ' . $urlRedireccionExito);
                    exit();
                } else {
                    $_SESSION['error'] = "Error al agregar la transacción.";
                    header('Location: ' . $urlRedireccionExito);
                    echo "Error al agregar la transacción.";
                }
            }
        }

        /**
         * Devuelve IDs de tipos de movimiento internos por nombre.
         */
        private function obtenerIdsTransferenciasInternas($tiposMovimiento) {
            $ids = [
                'aporte' => 0,
                'retiro' => 0
            ];

            foreach ($tiposMovimiento as $tipo) {
                $nombre = strtolower(trim((string)($tipo['nombre'] ?? '')));
                $id = (int)($tipo['id'] ?? 0);

                if ($nombre === 'transferencia interna aporte') {
                    $ids['aporte'] = $id;
                }

                if ($nombre === 'transferencia interna retiro') {
                    $ids['retiro'] = $id;
                }
            }

            return $ids;
        }

        /**
         * Comprueba si una lista contiene un objetivo por ID.
         */
        private function listaContieneObjetivo($objetivos, $idObjetivo) {
            $idObjetivo = (int)$idObjetivo;

            foreach ($objetivos as $objetivo) {
                if ((int)($objetivo['id_objetivo'] ?? 0) === $idObjetivo) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Elimina una transacción existente de un usuario
         */
        public function eliminarTransaccion() {
            $transactionModel = new TransactionModel();
            $goalModel = new GoalModel();
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $transaccion = $transactionModel->obtenerTransaccionPorId((int)($_GET['id_transaccion'] ?? 0));

            if($transactionModel->eliminarTransaccion($_GET['id_transaccion'])) {
                if (!empty($transaccion['id_objetivo'])) {
                    $goalModel->obtenerDetalleObjetivoPorIdUsuario($idUsuario, (int)$transaccion['id_objetivo']);
                }

                $_SESSION['correcto'] = "Transacción eliminada correctamente.";
                header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                exit();
            } else {
                $_SESSION['error'] = "Error al eliminar la transacción.";
                header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                echo "Error al eliminar la transacción.";
            }
        }
    }
?>