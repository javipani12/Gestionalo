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
         * Muestra la vista de objetivos de ahorro.
         */
        public function mostrarObjetivosAhorro() {
            $titulo = 'Gestionalo | Objetivos de ahorro';
            $nombreHerramienta = 'Objetivos de ahorro';
            $descripcionHerramienta = 'Configura metas economicas y sigue el progreso de tus ahorros paso a paso.';
            require_once './../app/views/tools/tool_detail.php';
        }

        /**
         * Muestra la vista de graficos financieros.
         */
        public function mostrarGraficos() {
            $titulo = 'Gestionalo | Graficos financieros';
            $nombreHerramienta = 'Graficos financieros';
            $descripcionHerramienta = 'Visualiza tu comportamiento financiero con graficos claros para identificar tendencias.';
            require_once './../app/views/tools/tool_detail.php';
        }

        /**
         * Muestra la vista de informes generados, donde el usuario podrá
         * consultar y descargar informes de sus movimientos y resultados financieros.
         */
        public function mostrarInformesGenerados() {
            $titulo = 'Gestionalo | Informes generados';
            $nombreHerramienta = 'Informes generados';
            $descripcionHerramienta = 'Consulta y descarga informes para revisar tus movimientos y resultados financieros.';
            require_once './../app/views/tools/tool_detail.php';
        }

    }
?>