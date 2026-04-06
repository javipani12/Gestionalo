<?php
    class UserModel {
        private $db;

        public function __construct() {
            $database = new Database();
            $this->db = $database->conectar();
        }

        /**
         * Busca un usuario activo por correo.
         */
        public function validarSiUsuarioExiste($correo){
            $sql = "SELECT u.email
                FROM usuarios u
                WHERE u.email = :correo
                AND u.eliminado = 0
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch();
            return $usuario ?: null;
        }

        /**
         * Comprueba si las credenciales de login son correctas, 
         * verificando el correo y la contraseña ingresados
         */
        public function comprobarUsuarioLogin($correo, $passwd){
            $sql = "SELECT u.id_usuario, u.email, c.contrasenna_hash
                FROM usuarios u
                JOIN contrasenas c ON u.id_usuario = c.id_usuario
                WHERE u.email = :correo
                AND u.eliminado = 0
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch();
            if ($usuario && password_verify($passwd, $usuario['contrasenna_hash'])) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Comprueba si la contraseña actual ingresada por el usuario es la misma 
         * que la almacenada en la base de datos. Se usará a la hora de actualizar
         * la contraseña
        */
        public function comprobarContrasennaActual($id_usuario, $passwd) {
            $sql = "SELECT contrasenna_hash 
                FROM contrasenas 
                WHERE id_usuario = :id_usuario
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            $contrasenna = $stmt->fetch();
            if ($contrasenna && password_verify($passwd, $contrasenna['contrasenna_hash'])) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Devuelve el usuario actual una vez se ha validado el login.
         */
        public function obtenerUsuarioActual($correo) {
            $sql = "SELECT u.id_usuario, u.nombre, u.apellido1, u.apellido2, 
                    u.localidad, u.fecha_nacimiento, u.email, r.nombre AS nombre_rol, c.contrasenna_hash
                FROM usuarios u
                INNER JOIN contrasenas c ON u.id_usuario = c.id_usuario
                INNER JOIN roles r ON u.rol_id = r.id_rol
                WHERE u.email = :correo
                AND u.eliminado = 0
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch();
        }

        /**
         * Obtiene un usuario activo por ID.
         */
        public function obtenerUsuarioPorId($id_usuario) {
            $sql = "SELECT u.id_usuario, u.nombre, u.apellido1, u.apellido2,
                    u.localidad, u.fecha_nacimiento, u.email, r.nombre AS nombre_rol
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id_rol
                WHERE u.id_usuario = :id_usuario
                AND u.eliminado = 0
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        }

        /**
         * Comprueba si un correo ya está en uso por otro usuario activo.
         */
        public function correoEnUsoPorOtroUsuario($id_usuario, $correo) {
            $sql = "SELECT 1
                FROM usuarios u
                WHERE u.email = :correo
                AND u.id_usuario <> :id_usuario
                AND u.eliminado = 0
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetchColumn();
        }

        /**
         * Crea un nuevo usuario en la base de datos con los datos proporcionados y 
         * el hash de contraseña generado previamente.
         */
        public function crearUsuario($nombre, $apellido1, $apellido2, $localidad, 
            $fecha_nacimiento, $correo, $politica_privacidad, $consentimiento_datos, 
            $hash_contrasena) 
        {
            $sql = "INSERT INTO usuarios (nombre, apellido1, apellido2, localidad, fecha_nacimiento, email, politica_privacidad, consentimiento_datos) 
                    VALUES (:nombre, :apellido1, :apellido2, :localidad, :fecha_nacimiento, :correo, :politica_privacidad, :consentimiento_datos)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':apellido1', $apellido1, PDO::PARAM_STR);
            $stmt->bindValue(':apellido2', $apellido2, PDO::PARAM_STR);
            $stmt->bindValue(':localidad', $localidad, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);
            $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindValue(':politica_privacidad', $politica_privacidad, PDO::PARAM_STR);
            $stmt->bindValue(':consentimiento_datos', $consentimiento_datos, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $id_usuario = (int)$this->db->lastInsertId();
                // Insertar la contraseña en la tabla de contraseñas
                $sql_contrasena = "INSERT INTO contrasenas (id_usuario, contrasenna_hash)
                    VALUES (:id_usuario, :contrasenna_hash)
                ";
                $stmt_contrasena = $this->db->prepare($sql_contrasena);
                $stmt_contrasena->bindValue(':id_usuario', $id_usuario, PDO::PARAM_STR);
                $stmt_contrasena->bindValue(':contrasenna_hash', $hash_contrasena, PDO::PARAM_STR);
                $stmt_contrasena->execute();
                return true;
            } else {
                return false;
            }
        }

        /**
         * Actualiza los datos de un usuario existente en la base de datos.
         */
        public function actualizarUsuario($id_usuario, $nombre, $apellido1, $apellido2, $localidad, 
        $fecha_nacimiento) 
        {
            $sql = "UPDATE usuarios 
                    SET 
                        nombre = :nombre, 
                        apellido1 = :apellido1, 
                        apellido2 = :apellido2, 
                        localidad = :localidad, 
                        fecha_nacimiento = :fecha_nacimiento,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id_usuario = :id_usuario
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':apellido1', $apellido1, PDO::PARAM_STR);
            $stmt->bindValue(':apellido2', $apellido2, PDO::PARAM_STR);
            $stmt->bindValue(':localidad', $localidad, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);
            return $stmt->execute();
        }

        /**
         * Actualiza los datos de un usuario y opcionalmente su correo electrónico.
         */
        public function actualizarUsuarioConEmail($id_usuario, $nombre, $apellido1, $apellido2, $localidad, $fecha_nacimiento, $email = null) {
            $sql = "UPDATE usuarios
                    SET
                        nombre = :nombre,
                        apellido1 = :apellido1,
                        apellido2 = :apellido2,
                        localidad = :localidad,
                        fecha_nacimiento = :fecha_nacimiento,
                        updated_at = CURRENT_TIMESTAMP";

            if ($email !== null) {
                $sql .= ", email = :email";
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindValue(':apellido1', $apellido1, PDO::PARAM_STR);
            $stmt->bindValue(':apellido2', $apellido2, PDO::PARAM_STR);
            $stmt->bindValue(':localidad', $localidad, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_nacimiento', $fecha_nacimiento, PDO::PARAM_STR);

            if ($email !== null) {
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            }

            return $stmt->execute();
        }

        /**
         * Actualiza la contraseña de un usuario existente en la base de datos
         */
        public function actualizarContrasenaUsuario($id_usuario, $hash_contrasena) {
            $sql = "UPDATE contrasenas 
                    SET contrasenna_hash = :contrasenna_hash 
                    WHERE id_usuario = :id_usuario
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':contrasenna_hash', $hash_contrasena, PDO::PARAM_STR);
            return $stmt->execute();
        }

        /**
         * Marca un usuario como eliminado en la base de datos, 
         * evitando que pueda iniciar sesión o ser recuperado en consultas normales.
         */
        public function eliminarUsuario($id_usuario) {
            $sql = "UPDATE usuarios 
                SET eliminado = 1 
                WHERE id_usuario = :id_usuario
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
?>