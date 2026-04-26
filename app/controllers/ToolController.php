<?php
    class ToolController {

        /**
         * Muestra la vista de herramientas disponibles para los usuarios.
         */
        public function mostrarHerramientas() {
            $titulo = 'Gestionalo | Herramientas';
            require_once './../app/views/tools/tools.php';
        }

        /**
         * Muestra la vista de la calculadora de hipoteca.
         */
        public function mostrarCalculadoraHipoteca() {
            $titulo = 'Gestionalo | Calculadora de hipoteca';
            require_once './../app/views/tools/mortgage.php';
        }

        /**
         * Muestra la vista de objetivos de ahorro
         */
        public function mostrarObjetivosAhorro() {
            $objetivoModel = new GoalController();
            $objetivoModel->mostrarObjetivosAhorro();
        }

        /**
         * Muestra la vista de graficos financieros.
         */
        public function mostrarGraficos() {
            $chartController = new ChartController();
            $chartController->mostrarGraficos();
        }

        /**
         * Muestra la vista de informes generados, donde el usuario podrá
         * consultar y descargar informes de sus movimientos y resultados financieros.
         */
        public function mostrarInformesGenerados() {
            $reportController = new ReportController();
            $reportController->mostrarInformesGenerados();
        }

    }
?>