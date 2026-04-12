<?php
    class AboutController {

        /**
         * Muestra la página "Sobre nosotros" con información relativa al proyecto
         */
        public function mostrarSobreNosotros() {
            $titulo = 'Gestionalo | Sobre nosotros';
            require_once './../app/views/about/about_us.php';
        }
    }
?>