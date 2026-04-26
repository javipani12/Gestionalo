<?php
    class LegalController {
        
        /**
         * Muestra la página de consentimiento para el tratamiento de datos
         */
        public function mostrarConsentimiento() {
            $titulo = "Gestionalo | Consentimiento";
            require_once './../app/views/legal/consent.php';
        }

        /**
         * Descarga el manual de usuario en PDF.
         */
        public function descargarManualUsuario() {
            $raizProyecto = dirname(__DIR__, 2);
            $rutaManual = $raizProyecto . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'Manual_Usuario_Gestionalo.pdf';
            $rutaReal = realpath($rutaManual);

            if (!$rutaReal || !is_file($rutaReal)) {
                http_response_code(404);
                die('No se encontró el manual de usuario.');
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Manual_Usuario_Gestionalo.pdf"');
            header('Content-Length: ' . filesize($rutaReal));

            readfile($rutaReal);
            exit();
        }

        /**
         * Descarga el manual de administrador en PDF.
         */
        public function descargarManualAdmin() {
            $raizProyecto = dirname(__DIR__, 2);
            $rutaManual = $raizProyecto . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'Manual_Administrador_Gestionalo.pdf';
            $rutaReal = realpath($rutaManual);

            if (!$rutaReal || !is_file($rutaReal)) {
                http_response_code(404);
                die('No se encontró el manual de administrador.');
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Manual_Administrador_Gestionalo.pdf"');
            header('Content-Length: ' . filesize($rutaReal));

            readfile($rutaReal);
            exit();
        }

        /**
         * Muestra la página de política de privacidad
         */
        public function mostrarPrivacidad() {
            $titulo = "Gestionalo | Política de Privacidad";
            require_once './../app/views/legal/privacy.php';
        }
    }
?>