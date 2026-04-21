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
            $graphicModel = new GraphicModel();
            
            $ultimasTransacciones = $transactionModel->obtenerUltimasTransacciones($idUsuario, 10);
            $ultimosObjetivos = [];
            $balanceActual = $this->calcularBalanceMesActual($idUsuario, $graphicModel);

            try {
                $ultimosObjetivos = $goalModel->obtenerObjetivosPaginadosPorUsuario($idUsuario, 5, 0);
            } catch (Throwable $e) {
                error_log('DashboardController::mostrarDashboard objetivos -> ' . $e->getMessage());
            }

            require_once './../app/views/dashboard/dashboard.php';
        }

        /**
         * Calcula el balance (ingresos - gastos) del mes actual del usuario
         */
        private function calcularBalanceMesActual($idUsuario, $graphicModel) {
            $dataset = $graphicModel->obtenerDatasetUsuario($idUsuario);
            $transacciones = $dataset['transacciones'] ?? [];
            
            $hoy = new DateTime();
            $mesActual = (int)$hoy->format('m');
            $anoActual = (int)$hoy->format('Y');
            
            $ingresos = 0;
            $gastos = 0;
            
            foreach ($transacciones as $transaccion) {
                $fecha = new DateTime($transaccion['fecha_movimiento']);
                $mes = (int)$fecha->format('m');
                $ano = (int)$fecha->format('Y');
                
                // Solo considerar transacciones del mes actual
                if ($mes === $mesActual && $ano === $anoActual) {
                    $importe = (float)$transaccion['importe'];
                    $tipo = strtolower($transaccion['tipo_movimiento'] ?? '');
                    
                    // Excluir transferencias internas del balance
                    if (strpos($tipo, 'transferencia interna') === false) {
                        if ($tipo === 'ingreso') {
                            $ingresos += $importe;
                        } elseif ($tipo === 'gasto') {
                            $gastos += $importe;
                        }
                    }
                }
            }
            
            return [
                'balance' => $ingresos - $gastos,
                'ingresos' => $ingresos,
                'gastos' => $gastos,
                'mes' => $mesActual,
                'ano' => $anoActual,
            ];
        }
    }
?>