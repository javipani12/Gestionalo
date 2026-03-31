<?php
    class AuthController {
        /**
         * Muestra el formulario de login
         */
        public function mostrarLogin() {
            require_once './../app/views/auth/login.php';
        }

        /**
         * Muestra el formulario de registro
         */
        public function mostrarRegistro() {
            require_once './../app/views/auth/register.php';
        }

        /**
         * Procesa el formulario de registro, validando los datos ingresados por el usuario,
         * creando un nuevo registro en la base de datos si todo es correcto
         * y redirigiendo al usuario a la página de login o mostrando errores si hay problemas con los datos ingresados.
         */
        public function enviarRegistro() {
            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: ?controller=auth&action=mostrarLogin");
                $_SESSION['error'] = 'No te cueles';
                exit();
            } else {
                // Recoger y sanitizar datos del formulario
                $nombre = trim($_POST['nombre'] ?? '');
                $apellido1 = trim($_POST['apellido1'] ?? '');
                $apellido2 = trim($_POST['apellido2'] ?? null);
                $localidad = trim($_POST['localidad'] ?? null);
                $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? null);
                $correo = trim($_POST['correo'] ?? '');
                $contrasena = $_POST['contrasena'] ?? '';
                $contrasena2 = $_POST['contrasena2'] ?? '';
                $privacidad = isset($_POST['privacidad']);
                $consentimiento = isset($_POST['consentimiento']);

                // Validar datos
                if($nombre === '' || $apellido1 === '' || $correo === '' || $contrasena === '' || $contrasena2 === '') {
                    $_SESSION['error'] = 'Los campos obligatorios no pueden estar vacíos.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                if($fecha_nacimiento) {
                    $fecha_nacimiento_obj = DateTimeImmutable::createFromFormat('Y-m-d', $fecha_nacimiento);
                    $fecha_valida = $fecha_nacimiento_obj
                        && $fecha_nacimiento_obj->format('Y-m-d') === $fecha_nacimiento;

                    if (!$fecha_valida) {
                        $_SESSION['error'] = 'La fecha de nacimiento no es válida.';
                        require_once './../app/views/auth/register.php';
                        return;
                    }

                    $fecha_limite_mayoria_edad = (new DateTimeImmutable('today'))->modify('-18 years');
                    if ($fecha_nacimiento_obj > $fecha_limite_mayoria_edad) {
                        $_SESSION['error'] = 'Debes tener al menos 18 años para registrarte.';
                        require_once './../app/views/auth/register.php';
                        return;
                    }
                }

                if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error'] = 'El correo electrónico no es válido.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                if($contrasena !== $contrasena2) {
                    $_SESSION['error'] = 'Las contraseñas no coinciden.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                if(!$privacidad) {
                    $_SESSION['error'] = 'Debes aceptar la política de privacidad.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                if(!$consentimiento) {
                    $_SESSION['error'] = 'Debes aceptar el consentimiento sobre el tratamiento de datos.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                $userModel = new UserModel();
                if($userModel->validarSiUsuarioExiste($correo)) {
                    $_SESSION['error'] = 'Ya existe un usuario registrado con ese correo electrónico.';
                    require_once './../app/views/auth/register.php';
                    return;
                }

                $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);
                $userModel->crearUsuario($nombre, $apellido1, $apellido2, $localidad, 
                    $fecha_nacimiento, $correo, $privacidad ? 1 : 0, $consentimiento ? 1 : 0, 
                    $hash_contrasena);
                $_SESSION['correcto'] = 'Registro exitoso. ¡Ya puedes iniciar sesión!';
                header("Location: ?controller=auth&action=mostrarLogin");
                exit();
            }
        }

        /**
         * Procesa el formulario de login, validando las credenciales ingresadas por el usuario,
         * iniciando una sesión si las credenciales son correctas y redirigiendo al
         * usuario a la página principal o mostrando errores si las credenciales son incorrectas.
         */
        public function enviarLogin() {
            if($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header("Location: ?controller=auth&action=mostrarLogin");
                $_SESSION['error'] = 'No te cueles';
                exit();
            } else {
                // Recoger y sanitizar datos del formulario
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['passwd'] ?? '';
                if($email === '' || $password === '') {
                    $_SESSION['error'] = 'Los campos no pueden estar vacíos.';
                    require_once './../app/views/auth/login.php';
                    return;
                } else {
                    $userModel = new UserModel();
                    $existe_usuario = $userModel->comprobarUsuarioLogin($email, $password);
                    if(!$existe_usuario) {
                        $_SESSION['error'] = 'Credenciales incorrectas.';
                        require_once './../app/views/auth/login.php';
                        return;
                    } else {
                        $usuario = $userModel->obtenerUsuarioActual($email);
                        session_regenerate_id(true);
                        // Guardamos información esencial del usuario activo en la sesión
                        $_SESSION['usuario'] = [
                            'id_usuario' => $usuario['id_usuario'],
                            'email' => $usuario['email'],
                            'nombre' => $usuario['nombre'],
                            'rol' => $usuario['nombre_rol']
                        ];
                        ($_SESSION['usuario']['rol'] === 'admin') 
                            ? header("Location: ?controller=admin&action=mostrarAdmin")
                            : header("Location: ?controller=dashboard&action=mostrarDashboard");
                        exit();
                    }
                }
            }
        }

        /**
         * Cierra la sesión del usuario de forma segura, eliminando toda la información de sesión 
         * tanto en el servidor como en el cliente, y redirige al inicio.
         */
        public function cerrarSesion() {
            // 1. Vaciar el array en memoria RAM
            $_SESSION = [];
            // 2. Eliminar la cookie PHPSESSID del navegador del usuario
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            // 3. Destruir el archivo físico en el servidor
            session_destroy();
            // 4. Redirigir al inicio
            header("Location: ?controller=home&action=mostrarHome");
            exit();
        }
    }
?>