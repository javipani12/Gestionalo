<?php
    class TransactionController {
        
        /**
         * Muestra la lista de transacciones del usuario en la vista correspondiente
         */
        public function mostrarTransaccionesUsuario(){
            $titulo = "Mis Transacciones";
            $transactionModel = new TransactionModel();
            $transacciones = $transactionModel->obtenerTransaccionesPorUsuario($_SESSION['usuario']['id_usuario']);
            require_once './../app/views/transaction/transactions.php';
            exit();
        }

        /**
         * Muestra el formulario para crear una nueva transacción
         */
        public function mostrarFormularioCrearTransaccion(){
            $titulo = "Crear Transacción";
            $defaultDataModel = new DefaultDataModel();
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
            $titulo = "Editar Transacción";
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