<?php
    class ChartController {

        /**
         * Muestra la herramienta de graficos con dataset inicial.
         */
        public function mostrarGraficos() {
            $titulo = 'Gestionalo | Graficos financieros';
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);

            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no identificado. Inicia sesion de nuevo.';
                header('Location: index.php?controller=dashboard&action=mostrarDashboard');
                exit();
            }

            try {
                $graphicModel = new GraphicModel();
                $datosGraficos = $graphicModel->obtenerDatasetUsuario($idUsuario);
            } catch (Throwable $e) {
                error_log('ChartController::mostrarGraficos -> ' . $e->getMessage());
                $_SESSION['error'] = 'No se pudieron cargar los datos de graficos.';
                $datosGraficos = [
                    'transacciones' => [],
                    'objetivos' => [],
                    'catalogos' => [
                        'tipos_movimiento' => [],
                        'categorias' => [],
                        'subcategorias' => [],
                        'metodos_pago' => [],
                    ],
                ];
            }

            $datosGraficosJson = json_encode(
                $datosGraficos,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($datosGraficosJson === false) {
                $datosGraficosJson = '{"transacciones":[],"objetivos":[],"catalogos":{}}';
            }

            require_once './../app/views/tools/graphics.php';
        }
    }
?>