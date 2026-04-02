<?php
    class AboutController {

        public function mostrarSobreNosotros() {
            $titulo = 'Gestionalo | Sobre nosotros';
            require_once './../app/views/about/about_us.php';
        }
    }
?>