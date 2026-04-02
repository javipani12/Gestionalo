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

            $filtrosActivos = [
                'concepto' => trim($_GET['concepto'] ?? ''),
                'id_tipo' => (int)($_GET['id_tipo'] ?? 0),
                'id_categoria' => (int)($_GET['id_categoria'] ?? 0),
                'id_subcategoria' => (int)($_GET['id_subcategoria'] ?? 0),
                'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
                'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
                'id_metodo' => (int)($_GET['id_metodo'] ?? 0)
            ];

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

            $camposOrdenPermitidos = ['tipo', 'categoria', 'subcategoria', 'concepto', 'fecha', 'metodo', 'importe'];
            $ordenCampo = $_GET['orden_campo'] ?? 'fecha';
            if(!in_array($ordenCampo, $camposOrdenPermitidos, true)) {
                $ordenCampo = 'fecha';
            }

            $ordenDireccion = strtolower($_GET['orden_direccion'] ?? 'desc');
            if($ordenDireccion !== 'asc' && $ordenDireccion !== 'desc') {
                $ordenDireccion = 'desc';
            }

            $totalTransacciones = $transactionModel->contarTransaccionesPorUsuario($idUsuario, $filtrosActivos);
            $totalPaginas = max(1, (int)ceil($totalTransacciones / $limitePorPagina));
            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }

            $offset = ($paginaActual - 1) * $limitePorPagina;
            $transacciones = $transactionModel->obtenerTransaccionesPaginadasPorUsuario(
                $idUsuario,
                $limitePorPagina,
                $offset,
                $filtrosActivos,
                $ordenCampo,
                $ordenDireccion
            );

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
            $limiteDiarioTransacciones = 20;
            $transaccionesHoy = $transactionModel->contarTransaccionesDiariasPorUsuario($_SESSION['usuario']['id_usuario']);
            $puedeCrearTransaccion = $transaccionesHoy < $limiteDiarioTransacciones;
            $categorias = $defaultDataModel->obtenerTodos('categorias');
            $subcategorias = $defaultDataModel->obtenerSubcategoriasConCategoria();
            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $metodosPago = $defaultDataModel->obtenerTodos('metodos_pago');
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
            $categorias = $defaultDataModel->obtenerTodos('categorias');
            $subcategorias = $defaultDataModel->obtenerSubcategoriasConCategoria();
            $tiposMovimiento = $defaultDataModel->obtenerTodos('tipos_movimiento');
            $metodosPago = $defaultDataModel->obtenerTodos('metodos_pago');
            $transaccion = $transactionModel->obtenerTransaccionPorId($_GET['id_transaccion']);
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
            $limiteDiarioTransacciones = 20;

            if (empty($_POST['id_transaccion'])) {
                $transaccionesHoy = $transactionModel->contarTransaccionesDiariasPorUsuario($_SESSION['usuario']['id_usuario']);
                if ($transaccionesHoy >= $limiteDiarioTransacciones) {
                    $_SESSION['error'] = "Has alcanzado el límite diario de {$limiteDiarioTransacciones} transacciones. Podrás crear más mañana.";
                    header('Location: index.php?controller=transaction&action=mostrarFormularioCrearTransaccion');
                    exit();
                }
            }
            // Recogemos los datos del formulario
            $datosTransaccion = [
                'id_transaccion' => $_POST['id_transaccion'] ?? null,
                'id_categoria' => $_POST['id_categoria'],
                'id_subcategoria' => $_POST['id_subcategoria'],
                'id_tipo' => $_POST['id_tipo'],
                'concepto' => $_POST['concepto'],
                'fecha_movimiento' => $_POST['fecha_movimiento'],
                'id_metodo' => $_POST['id_metodo'],
                'importe' => $_POST['importe']
            ];

            // Hacemos validaciones básicas
            $esCategoriaValida = $defaultDataModel->existeId('categorias', $datosTransaccion['id_categoria']);
            $esSubcategoriaValida = $defaultDataModel->existeId('subcategorias', $datosTransaccion['id_subcategoria']);
            $esTipoValido = $defaultDataModel->existeId('tipos_movimiento', $datosTransaccion['id_tipo']);
            $esMetodoValido = $defaultDataModel->existeId('metodos_pago', $datosTransaccion['id_metodo']);
            $esRelacionCategoriaSubcategoriaValida = $defaultDataModel->validarSubcategoriaDeCategoria(
                $datosTransaccion['id_subcategoria'],
                $datosTransaccion['id_categoria']
            );

            if(!$esCategoriaValida || !$esSubcategoriaValida || !$esTipoValido
                || !$esMetodoValido || !$esRelacionCategoriaSubcategoriaValida ) 
            {
                $_SESSION['error'] = "Los datos de selección enviados no son válidos.";
                header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                exit();
            }

            if($_POST['id_transaccion'] !== '') {
                // Actualizamos la transacción existente
                if($transactionModel->modificarTransaccion($_POST['id_transaccion'], $datosTransaccion)) {
                    $_SESSION['correcto'] = "Transacción modificada correctamente.";
                    header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                    exit();
                } else {
                    $_SESSION['error'] = "Error al modificar la transacción.";
                    header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                    echo "Error al modificar la transacción.";
                }
            } else {
                // Agregamos una nueva transacción para el usuario
                if($transactionModel->agregarTransaccion($_SESSION['usuario']['id_usuario'], $datosTransaccion)) {
                    $_SESSION['correcto'] = "Transacción agregada correctamente.";
                    header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                    exit();
                } else {
                    $_SESSION['error'] = "Error al agregar la transacción.";
                    header('Location: index.php?controller=transaction&action=mostrarTransaccionesUsuario');
                    echo "Error al agregar la transacción.";
                }
            }
        }

        /**
         * Elimina una transacción existente de un usuario
         */
        public function eliminarTransaccion() {
            $transactionModel = new TransactionModel();
            if($transactionModel->eliminarTransaccion($_GET['id_transaccion'])) {
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