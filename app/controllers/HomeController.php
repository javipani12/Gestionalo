<?php
    class HomeController {
        /**
         * Muestra la página de inicio (landing page) para usuarios no autenticados.
         */
        public function mostrarHome() {
            $titulo = "Gestionalo — Organiza tus finanzas personales";
            require_once './../app/views/landing/home.php';
        }
    }
?>