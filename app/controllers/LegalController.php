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
         * Muestra la página de política de privacidad
         */
        public function mostrarPrivacidad() {
            $titulo = "Gestionalo | Política de Privacidad";
            require_once './../app/views/legal/privacy.php';
        }
    }
?>