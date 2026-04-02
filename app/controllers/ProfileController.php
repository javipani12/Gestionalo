<?php
    class ProfileController {

        public function mostrarPerfil() {
            $titulo = 'Gestionalo | Mi Perfil';
            $userModel = new UserModel();
            $datosUsuario = $userModel->obtenerUsuarioActual($_SESSION['usuario']['email']);
            require_once './../app/views/profile/profile.php';
        }

        /**
         * Actualiza el perfil del usuario activo, 
         * permitiéndole modificar su información personal y su contraseña.
         */
        public function actualizarPerfil() {
            $userModel = new UserModel();
            $id_usuario = $_SESSION['usuario']['id_usuario'];
            $nombre = trim($_POST['nombre'] ?? '');
            $apellido1 = trim($_POST['apellido1'] ?? '');
            $apellido2 = trim($_POST['apellido2'] ?? '');
            $localidad = trim($_POST['localidad'] ?? '');
            $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
            $passwd = $_POST['passwd'] ?? '';

            // Realizamos validaciones similares a las del registro
            if($nombre === '' || $apellido1 === '' || $apellido2 === '' || $localidad === '' || $fecha_nacimiento === '') {
                $_SESSION['error'] = 'Los campos no pueden estar vacíos.';
                header('Location: ?controller=profile&action=mostrarPerfil');
                return;
            }

            if($userModel->comprobarContrasennaActual($id_usuario, $passwd) || $passwd === '') {
                // Si es la misma contraseña o esta está vacía, no actualizamos el hash, solo los datos del usuario
                if($userModel->actualizarUsuario($id_usuario, $nombre, $apellido1, $apellido2, $localidad, $fecha_nacimiento)){
                    $this->mostrarMensajeActualizarPerfil('correcto');
                } else {
                    $this->mostrarMensajeActualizarPerfil('error');
                }
            } else {
                // Si la contraseña es diferente y no está vacía, actualizamos el hash con la nueva contraseña
                // y luego actualizamos los datos del usuario
                $hash_contrasena = password_hash($passwd, PASSWORD_DEFAULT);
                if(
                    $userModel->actualizarContrasenaUsuario($id_usuario, $hash_contrasena) && 
                    $userModel->actualizarUsuario($id_usuario, $nombre, $apellido1, $apellido2, $localidad, $fecha_nacimiento)
                ){
                    $this->mostrarMensajeActualizarPerfil('correcto');
                } else {
                    $this->mostrarMensajeActualizarPerfil('error');
                }
            }
        }

        /**
         * Elimina la cuenta del usuario activo, marcándola como eliminada en la base de datos
         * y cerrando su sesión.
         */
        public function eliminarCuenta() {
            $userModel = new UserModel();
            $id_usuario = $_SESSION['usuario']['id_usuario'];

            if($userModel->eliminarUsuario($id_usuario)) {
                header('Location: ?controller=auth&action=cerrarSesion');
                exit();
            } else {
                $_SESSION['error'] = 'Error al eliminar la cuenta. Inténtalo de nuevo.';
                header('Location: ?controller=profile&action=mostrarPerfil');
                return;
            }
        }

        /**
         * Functión auxiliar para mostrar un mensaje de éxito o error después de intentar 
         * actualizar el perfil del usuario. Solamente se usará para esta acción.
         */
        function mostrarMensajeActualizarPerfil($tipo){
            if($tipo === 'correcto') {
                $userModel = new UserModel();
                $usuario = $userModel->obtenerUsuarioActual($_SESSION['usuario']['email']);
                session_regenerate_id(true);
                // Actualizamos los datos de la sesión con la información más reciente del usuario
                $_SESSION['usuario'] = [
                    'id_usuario' => $usuario['id_usuario'],
                    'email' => $usuario['email'],
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['nombre_rol']
                ];
                $_SESSION['correcto'] = 'Perfil actualizado correctamente.';
                header('Location: ?controller=profile&action=mostrarPerfil');
                exit();
            } else if($tipo === 'error') {
                $_SESSION['error'] = 'Error al actualizar el perfil. Inténtalo de nuevo.';
                header('Location: ?controller=profile&action=mostrarPerfil');
                return;
            }
        }
    }
?>