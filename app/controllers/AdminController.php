<?php
    class AdminController {

        /**
         * Verifica que el usuario actual tiene rol de admin antes de permitir 
         * el acceso a las funciones del controlador. Si no es admin, redirige al home.
         */
        private function requerirAdmin() {
            // Verificar que el usuario tiene rol de admin
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ?controller=home&action=mostrarHome");
                exit();
            }
        }

        /**
         * Función interna que construye la URL para volver a la gestión de usuarios,
         * manteniendo los filtros y paginación actuales.
         */
        private function construirUrlGestionUsuarios() {
            $params = [
                'controller' => 'admin',
                'action' => 'mostrarGestionUsuarios'
            ];

            $correo = trim($_GET['correo'] ?? '');
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));

            if ($correo !== '') {
                $params['correo'] = $correo;
            }

            if ($pagina > 1) {
                $params['pagina'] = $pagina;
            }

            return 'index.php?' . http_build_query($params);
        }

        /**
         * Función interna que construye la URL para editar un usuario,
         * manteniendo los filtros y paginación actuales.
         */
        private function construirUrlEditarUsuario($idUsuario) {
            $params = [
                'controller' => 'admin',
                'action' => 'mostrarEditarUsuario',
                'id_usuario' => (int)$idUsuario
            ];

            $correo = trim($_GET['correo'] ?? '');
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));

            if ($correo !== '') {
                $params['correo'] = $correo;
            }

            if ($pagina > 1) {
                $params['pagina'] = $pagina;
            }

            if (($_GET['return_to'] ?? '') === 'gestionUsuarios') {
                $params['return_to'] = 'gestionUsuarios';
            }

            return 'index.php?' . http_build_query($params);
        }

        /**
         * Función interna que construye la URL para volver a la gestión de tablas maestras,
         * manteniendo la tabla seleccionada, filtros de búsqueda y paginación actuales.
         */
        private function construirUrlGestionTablasMaestras($tabla = '', $extras = []) {
            $params = [
                'controller' => 'admin',
                'action' => 'mostrarGestionTablasMaestras'
            ];

            $tabla = trim((string)$tabla);
            $buscar = trim((string)($extras['buscar'] ?? ($_GET['buscar'] ?? $_POST['buscar'] ?? '')));
            $pagina = max(1, (int)($extras['pagina'] ?? ($_GET['pagina'] ?? $_POST['pagina'] ?? 1)));

            if ($tabla !== '') {
                $params['tabla'] = $tabla;
            }

            if ($buscar !== '') {
                $params['buscar'] = $buscar;
            }

            if ($pagina > 1) {
                $params['pagina'] = $pagina;
            }

            return 'index.php?' . http_build_query($params);
        }

        /**
         * Función interna que construye la URL para el formulario de tabla maestra,
         * manteniendo la tabla seleccionada, el ID del registro (si es edición)
         * y cualquier filtro de búsqueda o paginación para volver a la lista después de guardar.
         */
        private function construirUrlFormularioTablaMaestra($tabla, $id = 0, $extras = []) {
            $params = [
                'controller' => 'admin',
                'action' => 'mostrarFormularioTablaMaestra',
                'tabla' => trim((string)$tabla)
            ];

            if ((int)$id > 0) {
                $params['id'] = (int)$id;
            }

            $buscar = trim((string)($extras['buscar'] ?? ($_GET['buscar'] ?? $_POST['buscar'] ?? '')));
            $pagina = max(1, (int)($extras['pagina'] ?? ($_GET['pagina'] ?? $_POST['pagina'] ?? 1)));

            if ($buscar !== '') {
                $params['buscar'] = $buscar;
            }

            if ($pagina > 1) {
                $params['pagina'] = $pagina;
            }

            return 'index.php?' . http_build_query($params);
        }

        /**
         * Muestra el dashboard con estadísticas clave, comparativas
         * y listados recientes para la gestión del sitio.
         */
        public function mostrarDashboardAdmin() {
            $this->requerirAdmin();
            $adminModel = new AdminModel();

            $titulo = "Gestionalo | Dashboard Admin";
            $dashboardStats = [
                'usuarios_activos' => $adminModel->contarUsuariosActivos(),
                'consultas_totales' => $adminModel->contarConsultasTotales(),
                'consultas_pendientes' => $adminModel->contarConsultasPendientes(),
                'transacciones_totales' => $adminModel->contarTransaccionesTotales(),
            ];

            $comparativaUsuarios = $adminModel->obtenerComparativaUsuariosNuevos();
            $comparativaConsultas = $adminModel->obtenerComparativaConsultasCreadas();
            $comparativaTransacciones = $adminModel->obtenerComparativaTransacciones();

            $comparativas = [
                [
                    'titulo' => 'Usuarios nuevos',
                    'valor_actual' => $comparativaUsuarios['actual'],
                    'valor_anterior' => $comparativaUsuarios['anterior'],
                ],
                [
                    'titulo' => 'Consultas creadas',
                    'valor_actual' => $comparativaConsultas['actual'],
                    'valor_anterior' => $comparativaConsultas['anterior'],
                ],
                [
                    'titulo' => 'Transacciones',
                    'valor_actual' => $comparativaTransacciones['actual'],
                    'valor_anterior' => $comparativaTransacciones['anterior'],
                ],
            ];

            foreach ($comparativas as &$comparativa) {
                $comparativa['variacion'] = $this->calcularVariacionPorcentual(
                    $comparativa['valor_actual'],
                    $comparativa['valor_anterior']
                );
            }
            unset($comparativa);

            $ultimasConsultas = $adminModel->obtenerUltimasConsultas(10);
            $ultimosUsuarios = $adminModel->obtenerUltimosUsuarios(10);
            $ultimaActualizacion = date('d/m/Y H:i');

            require_once './../app/views/admin/dashboard/dashboard_admin.php';
        }

        /**
         * Función interna que calcula la variación porcentual entre dos valores,
         * manejando casos de división por cero.
         */
        private function calcularVariacionPorcentual($actual, $anterior) {
            if ((int)$anterior === 0) {
                return (int)$actual > 0 ? 100 : 0;
            }

            return round((($actual - $anterior) / $anterior) * 100, 1);
        }

        /**
         * Muestra el perfil del admin con la posibilidad de editar su información
         * personal y cambiar su contraseña.
         */
        public function mostrarPerfilAdmin() {
            $this->requerirAdmin();

            $titulo = "Gestionalo | Perfil Admin";
            $userModel = new UserModel();
            $datosUsuario = $userModel->obtenerUsuarioActual($_SESSION['usuario']['email']);
            require_once './../app/views/admin/profile/profile_admin.php';
        }

        /**
         * Muestra la gestión de usuarios con filtros, paginación y acciones
         * para editar o eliminar usuarios.
         */
        public function mostrarGestionUsuarios() {
            $this->requerirAdmin();
            $titulo = "Gestionalo | Gestión de usuarios";

            $userModel = new UserModel();

            $filtroCorreo = trim($_GET['correo'] ?? '');
            $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
            $limitePorPagina = 10;
            $resumenUsuarios = $userModel->contarUsuariosPorEstado();

            $totalUsuarios = $userModel->contarUsuariosFiltrados($filtroCorreo);
            $totalPaginas = max(1, (int)ceil($totalUsuarios / $limitePorPagina));
            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }

            $offset = ($paginaActual - 1) * $limitePorPagina;
            $usuarios = $userModel->obtenerUsuariosPaginados($limitePorPagina, $offset, $filtroCorreo);

            require_once './../app/views/admin/manage_users/manage_users.php';
        }

        /**
         * Muestra el formulario para editar un usuario desde el panel admin, 
         * prellenando los datos actuales y permitiendo cambiar su información personal y contraseña.
         */
        public function mostrarEditarUsuario() {
            $this->requerirAdmin();

            $idUsuario = (int)($_GET['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no válido.';
                header('Location: ' . $this->construirUrlGestionUsuarios());
                exit();
            }

            $userModel = new UserModel();
            $datosUsuario = $userModel->obtenerUsuarioPorId($idUsuario);

            if (!$datosUsuario) {
                $_SESSION['error'] = 'No se encontró el usuario solicitado.';
                header('Location: ?controller=admin&action=mostrarGestionUsuarios');
                exit();
            }

            $titulo = 'Gestionalo | Editar usuario';
            $profileHeading = 'Admin editando el perfil de ' . trim(($datosUsuario['nombre'] ?? '') . ' ' . ($datosUsuario['apellido1'] ?? ''));
            $profileFormAction = '?controller=admin&action=actualizarUsuario&id_usuario=' . $idUsuario;

            if (!empty($_GET['correo'])) {
                $profileFormAction .= '&correo=' . urlencode(trim($_GET['correo']));
            }

            if (!empty($_GET['pagina'])) {
                $profileFormAction .= '&pagina=' . (int)$_GET['pagina'];
            }

            if (($_GET['return_to'] ?? '') === 'gestionUsuarios') {
                $profileFormAction .= '&return_to=gestionUsuarios';
            }

            $isAdminEditingUser = true;
            $returnToGestionUsuarios = (($_GET['return_to'] ?? '') === 'gestionUsuarios');
            $backToGestionUsuariosUrl = $returnToGestionUsuarios ? $this->construirUrlGestionUsuarios() : '';

            require_once './../app/views/admin/profile/profile_admin.php';
        }

        /**
         * Actualiza la información de un usuario desde el panel admin, 
         * validando los datos y permitiendo cambiar su contraseña si se proporciona una nueva. 
         * Si el admin está editando su propio perfil y cambia su contraseña,
         *  se refresca la sesión para mantenerlo autenticado con los nuevos datos.
         */
        public function actualizarUsuario() {
            $this->requerirAdmin();

            $idUsuario = (int)($_GET['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no válido.';
                header('Location: ' . $this->construirUrlGestionUsuarios());
                exit();
            }

            $userModel = new UserModel();
            $usuarioObjetivo = $userModel->obtenerUsuarioPorId($idUsuario);

            if (!$usuarioObjetivo) {
                $_SESSION['error'] = 'No se encontró el usuario solicitado.';
                header('Location: ' . $this->construirUrlGestionUsuarios());
                exit();
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $apellido1 = trim($_POST['apellido1'] ?? '');
            $apellido2 = trim($_POST['apellido2'] ?? '');
            $localidad = trim($_POST['localidad'] ?? '');
            $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $passwd = $_POST['passwd'] ?? '';

            if ($nombre === '' || $apellido1 === '' || $apellido2 === '' || $localidad === '' || $fechaNacimiento === '' || $email === '') {
                $_SESSION['error'] = 'Los campos no pueden estar vacíos.';
                header('Location: ' . $this->construirUrlEditarUsuario($idUsuario));
                exit();
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'El formato del correo electrónico no es válido.';
                header('Location: ' . $this->construirUrlEditarUsuario($idUsuario));
                exit();
            }

            if ($userModel->correoEnUsoPorOtroUsuario($idUsuario, $email)) {
                $_SESSION['error'] = 'Ese correo electrónico ya está en uso.';
                header('Location: ' . $this->construirUrlEditarUsuario($idUsuario));
                exit();
            }

            $actualizado = false;

            // Si está vacía la contraseña, solo actualizamos los datos sin cambiar la contraseña
            if ($passwd === '' || $userModel->comprobarContrasennaActual($idUsuario, $passwd)) {
                $actualizado = $userModel->actualizarUsuarioConEmail(
                    $idUsuario,
                    $nombre,
                    $apellido1,
                    $apellido2,
                    $localidad,
                    $fechaNacimiento,
                    $email
                );
            } else {
                $hashContrasena = password_hash($passwd, PASSWORD_DEFAULT);
                $actualizado =
                    $userModel->actualizarContrasenaUsuario($idUsuario, $hashContrasena)
                    && $userModel->actualizarUsuarioConEmail(
                        $idUsuario,
                        $nombre,
                        $apellido1,
                        $apellido2,
                        $localidad,
                        $fechaNacimiento,
                        $email
                    );
            }

            if ($actualizado) {
                $_SESSION['correcto'] = 'Perfil actualizado correctamente.';
                if ((int)($_SESSION['usuario']['id_usuario'] ?? 0) === $idUsuario) {
                    $this->refrescarSesionAdmin();
                }
            } else {
                $_SESSION['error'] = 'Error al actualizar el perfil. Inténtalo de nuevo.';
            }

            header('Location: ' . $this->construirUrlEditarUsuario($idUsuario));
            exit();
        }

        /**
         * Elimina un usuario desde el panel admin, validando que no se elimine a sí mismo
         * y que el ID del usuario sea válido.
         */
        public function eliminarUsuario() {
            $this->requerirAdmin();

            $idUsuario = (int)($_GET['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no válido.';
                header('Location: ?controller=admin&action=mostrarGestionUsuarios');
                exit();
            }

            if ((int)($_SESSION['usuario']['id_usuario'] ?? 0) === $idUsuario) {
                $_SESSION['error'] = 'No puedes eliminar tu propio usuario desde esta sección.';
                header('Location: ' . $this->construirUrlGestionUsuarios());
                exit();
            }

            $userModel = new UserModel();
            if ($userModel->eliminarUsuario($idUsuario)) {
                $_SESSION['correcto'] = 'Usuario eliminado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al eliminar el usuario.';
            }

            header('Location: ' . $this->construirUrlGestionUsuarios());
            exit();
        }

        /**
         * Reactiva un usuario inactivo desde el panel admin.
         */
        public function reactivarUsuario() {
            $this->requerirAdmin();

            $idUsuario = (int)($_GET['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no válido.';
                header('Location: ' . $this->construirUrlGestionUsuarios());
                exit();
            }

            $userModel = new UserModel();
            if ($userModel->reactivarUsuario($idUsuario)) {
                $_SESSION['correcto'] = 'Usuario reactivado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al reactivar el usuario.';
            }

            header('Location: ' . $this->construirUrlGestionUsuarios());
            exit();
        }

        /**
         * Muestra una página con todas las consultas enviadas por los usuarios, permitiendo al admin gestionarlas.
         */
        public function mostrarConsultasAdmin() {
            $this->requerirAdmin();

            $titulo = 'Gestionalo | Consultas de usuarios';
            $contactModel = new ContactModel();
            $limitePorPagina = 10;
            $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $paginaActual = max(1, $paginaActual);

            $totalConsultas = $contactModel->contarTodasConsultas();
            $totalPaginas = max(1, (int)ceil($totalConsultas / $limitePorPagina));
            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }

            $offset = ($paginaActual - 1) * $limitePorPagina;
            $consultas = $contactModel->obtenerTodasConsultasPaginadas($limitePorPagina, $offset);
            require_once './../app/views/admin/queries/queries.php';
        }

        /**
         * Muestra el formulario para editar una consulta desde el panel admin.
         */
        public function mostrarEditarConsultaAdmin() {
            $this->requerirAdmin();

            $id_consulta = (int)($_GET['id_consulta'] ?? 0);
            if ($id_consulta <= 0) {
                $_SESSION['error'] = 'Consulta no válida.';
                header('Location: ?controller=admin&action=mostrarConsultasAdmin');
                exit();
            }

            $contactModel = new ContactModel();
            $consulta = $contactModel->obtenerConsultaPorId($id_consulta);

            if (!$consulta) {
                $_SESSION['error'] = 'La consulta no existe o no está disponible.';
                header('Location: ?controller=admin&action=mostrarConsultasAdmin');
                exit();
            }

            $defaultDataModel = new DefaultDataModel();
            $estadosConsulta = $defaultDataModel->obtenerTodos('estados_consulta');
            $titulo = 'Gestionalo | Editar consulta';

            require_once './../app/views/admin/queries/edit_query.php';
        }

        /**
         * Actualiza la respuesta y estado de una consulta desde el panel admin.
         */
        public function actualizarConsultaAdmin() {
            $this->requerirAdmin();

            $id_consulta = (int)($_POST['id_consulta'] ?? 0);
            $respuesta = trim($_POST['respuesta'] ?? '');
            $id_estado = (int)($_POST['id_estado'] ?? 0);

            if ($id_consulta <= 0 || $id_estado <= 0) {
                $_SESSION['error'] = 'Datos de actualización no válidos.';
                header('Location: ?controller=admin&action=mostrarConsultasAdmin');
                exit();
            }

            $defaultDataModel = new DefaultDataModel();
            if (!$defaultDataModel->existeId('estados_consulta', $id_estado)) {
                $_SESSION['error'] = 'El estado seleccionado no es válido.';
                header('Location: ?controller=admin&action=mostrarEditarConsultaAdmin&id_consulta=' . $id_consulta);
                exit();
            }

            $contactModel = new ContactModel();
            $consulta = $contactModel->obtenerConsultaPorId($id_consulta);
            if (!$consulta) {
                $_SESSION['error'] = 'La consulta no existe o no está disponible.';
                header('Location: ?controller=admin&action=mostrarConsultasAdmin');
                exit();
            }

            if ($contactModel->actualizarRespuestaConsulta($id_consulta, $respuesta, $id_estado)) {
                $_SESSION['correcto'] = 'Consulta actualizada correctamente.';
                header('Location: ?controller=admin&action=mostrarConsultasAdmin');
                exit();
            }

            $_SESSION['error'] = 'No se pudo actualizar la consulta. Inténtalo de nuevo.';
            header('Location: ?controller=admin&action=mostrarEditarConsultaAdmin&id_consulta=' . $id_consulta);
            exit();
        }

        /**
         * Muestra la gestión de tablas maestras mediante cards y listado de una tabla seleccionada.
         */
        public function mostrarGestionTablasMaestras() {
            $this->requerirAdmin();

            $titulo = 'Gestionalo | Gestión de tablas maestras';
            $defaultDataModel = new DefaultDataModel();

            $tablasMaestras = $defaultDataModel->obtenerDefinicionesTablasMaestras();
            $resumenTablas = $defaultDataModel->obtenerResumenTablasMaestras();

            $tablaActual = trim($_GET['tabla'] ?? '');
            $buscar = trim($_GET['buscar'] ?? '');
            $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
            $limitePorPagina = 10;

            $definicionTablaActual = null;
            $registros = [];
            $totalRegistros = 0;
            $totalPaginas = 1;

            if ($tablaActual !== '') {
                if (!isset($tablasMaestras[$tablaActual])) {
                    $_SESSION['error'] = 'La tabla maestra seleccionada no es válida.';
                    header('Location: ' . $this->construirUrlGestionTablasMaestras());
                    exit();
                }

                $definicionTablaActual = $tablasMaestras[$tablaActual];
                $totalRegistros = $defaultDataModel->contarRegistrosTablaMaestra($tablaActual, $buscar);
                $totalPaginas = max(1, (int)ceil($totalRegistros / $limitePorPagina));

                if ($paginaActual > $totalPaginas) {
                    $paginaActual = $totalPaginas;
                }

                $offset = ($paginaActual - 1) * $limitePorPagina;
                $registros = $defaultDataModel->obtenerRegistrosTablaMaestraPaginados(
                    $tablaActual,
                    $limitePorPagina,
                    $offset,
                    $buscar
                );
            }

            require_once './../app/views/admin/master_tables/manage_master_tables.php';
        }

        /**
         * Muestra el formulario de creación/edición de un elemento de tabla maestra.
         */
        public function mostrarFormularioTablaMaestra() {
            $this->requerirAdmin();

            $defaultDataModel = new DefaultDataModel();
            $tablasMaestras = $defaultDataModel->obtenerDefinicionesTablasMaestras();

            $tabla = trim($_GET['tabla'] ?? '');
            $id = (int)($_GET['id'] ?? 0);
            $buscar = trim($_GET['buscar'] ?? '');
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));

            if (!isset($tablasMaestras[$tabla])) {
                $_SESSION['error'] = 'La tabla maestra seleccionada no es válida.';
                header('Location: ' . $this->construirUrlGestionTablasMaestras());
                exit();
            }

            $definicionTabla = $tablasMaestras[$tabla];
            $registro = null;
            $modoEdicion = $id > 0;

            if ($modoEdicion) {
                $registro = $defaultDataModel->obtenerRegistroTablaMaestraPorId($tabla, $id);

                if (!$registro) {
                    $_SESSION['error'] = 'El registro seleccionado no existe.';
                    header('Location: ' . $this->construirUrlGestionTablasMaestras($tabla, [
                        'buscar' => $buscar,
                        'pagina' => $pagina
                    ]));
                    exit();
                }
            }

            $categorias = [];
            if ($tabla === 'subcategorias') {
                $categorias = $defaultDataModel->obtenerTodos('categorias');
            }

            $titulo = $modoEdicion
                ? 'Gestionalo | Editar elemento de tabla maestra'
                : 'Gestionalo | Crear elemento de tabla maestra';

            $urlVolver = $this->construirUrlGestionTablasMaestras($tabla, [
                'buscar' => $buscar,
                'pagina' => $pagina
            ]);

            require_once './../app/views/admin/master_tables/create_edit_master_table.php';
        }

        /**
         * Guarda (crea o edita) un registro de tabla maestra.
         */
        public function guardarTablaMaestra() {
            $this->requerirAdmin();

            $defaultDataModel = new DefaultDataModel();
            $tablasMaestras = $defaultDataModel->obtenerDefinicionesTablasMaestras();

            $tabla = trim($_POST['tabla'] ?? '');
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $buscar = trim($_POST['buscar'] ?? '');
            $pagina = max(1, (int)($_POST['pagina'] ?? 1));

            if (!isset($tablasMaestras[$tabla])) {
                $_SESSION['error'] = 'La tabla maestra seleccionada no es válida.';
                header('Location: ' . $this->construirUrlGestionTablasMaestras());
                exit();
            }

            if ($nombre === '') {
                $_SESSION['error'] = 'El nombre no puede estar vacío.';
                header('Location: ' . $this->construirUrlFormularioTablaMaestra($tabla, $id, [
                    'buscar' => $buscar,
                    'pagina' => $pagina
                ]));
                exit();
            }

            $datos = ['nombre' => $nombre];

            if ($tabla === 'subcategorias') {
                $idCategoria = (int)($_POST['id_categoria'] ?? 0);

                if ($idCategoria <= 0 || !$defaultDataModel->existeId('categorias', $idCategoria)) {
                    $_SESSION['error'] = 'Debes seleccionar una categoría válida.';
                    header('Location: ' . $this->construirUrlFormularioTablaMaestra($tabla, $id, [
                        'buscar' => $buscar,
                        'pagina' => $pagina
                    ]));
                    exit();
                }

                $datos['id_categoria'] = $idCategoria;
            }

            try {
                $guardado = false;

                if ($id > 0) {
                    $guardado = $defaultDataModel->actualizarRegistroTablaMaestra($tabla, $id, $datos);
                } else {
                    $guardado = $defaultDataModel->crearRegistroTablaMaestra($tabla, $datos);
                }

                if ($guardado) {
                    $_SESSION['correcto'] = $id > 0
                        ? 'Registro actualizado correctamente.'
                        : 'Registro creado correctamente.';
                } else {
                    $_SESSION['error'] = $id > 0
                        ? 'No se pudo actualizar el registro.'
                        : 'No se pudo crear el registro.';
                }
            } catch (PDOException $exception) {
                if (($exception->getCode() ?? '') === '23000') {
                    $_SESSION['error'] = 'No se pudo guardar el registro porque ya existe o no cumple las restricciones de datos.';
                } else {
                    $_SESSION['error'] = 'Se produjo un error al guardar el registro.';
                }
            }

            header('Location: ' . $this->construirUrlGestionTablasMaestras($tabla, [
                'buscar' => $buscar,
                'pagina' => $pagina
            ]));
            exit();
        }

        /**
         * Elimina un registro de una tabla maestra.
         */
        public function eliminarRegistroTablaMaestra() {
            $this->requerirAdmin();

            $defaultDataModel = new DefaultDataModel();
            $tablasMaestras = $defaultDataModel->obtenerDefinicionesTablasMaestras();

            $tabla = trim($_GET['tabla'] ?? '');
            $id = (int)($_GET['id'] ?? 0);

            if (!isset($tablasMaestras[$tabla]) || $id <= 0) {
                $_SESSION['error'] = 'Los datos para eliminar no son válidos.';
                header('Location: ' . $this->construirUrlGestionTablasMaestras());
                exit();
            }

            try {
                if ($defaultDataModel->eliminarRegistroTablaMaestra($tabla, $id)) {
                    $_SESSION['correcto'] = 'Registro eliminado correctamente.';
                } else {
                    $_SESSION['error'] = 'No se pudo eliminar el registro.';
                }
            } catch (PDOException $exception) {
                if (($exception->getCode() ?? '') === '23000') {
                    $_SESSION['error'] = 'No se puede eliminar este registro porque está siendo utilizado en otras secciones.';
                } else {
                    $_SESSION['error'] = 'Se produjo un error al eliminar el registro.';
                }
            }

            header('Location: ' . $this->construirUrlGestionTablasMaestras($tabla));
            exit();
        }

        /**
         * Función interna que refresca la sesión del admin después de actualizar su propio perfil
         */
        private function refrescarSesionAdmin() {
            $userModel = new UserModel();
            $usuario = $userModel->obtenerUsuarioPorId((int)$_SESSION['usuario']['id_usuario']);

            if (!$usuario) {
                return;
            }

            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'email' => $usuario['email'],
                'nombre' => $usuario['nombre'],
                'rol' => $usuario['nombre_rol']
            ];
        }

    }
?>