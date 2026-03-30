<?php
    class DashboardController {

        /**
         * Muestra el dashboard del usuario activo
         */
        public function mostrarDashboard() {
            require_once './../app/views/dashboard/dashboard.php';
        }
    }
?>