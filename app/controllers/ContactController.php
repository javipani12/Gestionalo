<?php
    class ContactController {

        /**
         * Muestra una página con las consultas que el usuario ha enviado previamente,
         * permitiéndole hacer un seguimiento de sus solicitudes al equipo de soporte.
         */
        public function mostrarMisConsultas() {
            $titulo = 'Gestionalo | Mis consultas';
            $contactModel = new ContactModel();
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $limitePorPagina = 10;
            $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $paginaActual = max(1, $paginaActual);

            $totalConsultas = $contactModel->contarConsultasPorUsuario($idUsuario);
            $totalPaginas = max(1, (int)ceil($totalConsultas / $limitePorPagina));
            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }

            $offset = ($paginaActual - 1) * $limitePorPagina;
            $consultas = $contactModel->obtenerConsultasPaginadasPorUsuario($idUsuario, $limitePorPagina, $offset);
            require_once './../app/views/contact/my_queries.php';
        }

        /**
         * Muestra el formulario de contacto para que el usuario pueda enviar una consulta al equipo de soporte.
         */
        public function mostrarCrearConsulta() {
            $titulo = 'Gestionalo | Contacta con nosotros';
            $contactModel = new ContactModel();
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $defaultDataModel = new DefaultDataModel();
            $asuntos = $defaultDataModel->obtenerTodos('asuntos');
            $consultasHoy = $contactModel->contarConsultasDiariasPorUsuario($idUsuario);
            $limiteDiarioConsultas = 2;
            $puedeEnviarConsulta = $consultasHoy < $limiteDiarioConsultas;
            require_once './../app/views/contact/create_query.php';
        }

        /**
         * Procesa el envío de una nueva consulta por parte del usuario.
         */
        public function enviarConsulta() {
            $contactModel = new ContactModel();
            $id_usuario = $_SESSION['usuario']['id_usuario'];
            $id_asunto = $_POST['asunto'] ?? '';
            $comentario = trim($_POST['comentario'] ?? '');

            if ($contactModel->contarConsultasDiariasPorUsuario($id_usuario) >= 2) {
                $_SESSION['error'] = 'Has alcanzado el límite diario de 2 consultas. Vuelve a intentarlo mañana.';
                header('Location: ?controller=contact&action=mostrarCrearConsulta');
                return;
            }

            if($id_asunto === '' || $comentario === '') {
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
                header('Location: ?controller=contact&action=mostrarCrearConsulta');
                return;
            }

            if($contactModel->crearConsulta($id_usuario, $id_asunto, $comentario)) {
                $_SESSION['correcto'] = 'Consulta enviada correctamente. Nuestro equipo de soporte se pondrá en contacto contigo lo antes posible.';
                header('Location: ?controller=contact&action=mostrarMisConsultas');
                exit();
            } else {
                $_SESSION['error'] = 'Error al enviar la consulta. Inténtalo de nuevo.';
                header('Location: ?controller=contact&action=mostrarCrearConsulta');
                return;
            }
        }
    }
?>