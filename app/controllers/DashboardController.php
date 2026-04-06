<?php
    class DashboardController {

        /**
         * Muestra el dashboard del usuario activo
         */
        public function mostrarDashboard() {
            $titulo = "Gestionalo | Inicio";
            $transactionModel = new TransactionModel();
            $ultimasTransacciones = $transactionModel->obtenerUltimasTransacciones($_SESSION['usuario']['id_usuario'], 10);
            require_once './../app/views/dashboard/dashboard.php';
        }
    }
?>