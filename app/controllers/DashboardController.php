<?php
    class DashboardController {

        /**
         * Muestra el dashboard del usuario activo
         */
        public function mostrarDashboard() {
            $titulo = "Gestionalo | Inicio";
            $idUsuario = (int)$_SESSION['usuario']['id_usuario'];
            $transactionModel = new TransactionModel();
            $goalModel = new GoalModel();
            $ultimasTransacciones = $transactionModel->obtenerUltimasTransacciones($idUsuario, 10);
            $ultimosObjetivos = [];

            try {
                $ultimosObjetivos = $goalModel->obtenerObjetivosPaginadosPorUsuario($idUsuario, 5, 0);
            } catch (Throwable $e) {
                error_log('DashboardController::mostrarDashboard objetivos -> ' . $e->getMessage());
            }

            require_once './../app/views/dashboard/dashboard.php';
        }
    }
?>