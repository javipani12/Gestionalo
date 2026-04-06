<?php
    class AdminController {

        public function mostrarDashboardAdmin() {
            // Verificar que el usuario tiene rol de admin
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ?controller=home&action=mostrarHome");
                exit();
            }

            $adminModel = new AdminModel();

            $titulo = "Gestionalo | Dashboard Admin";
            $dashboardStats = [
                'usuarios_totales' => $adminModel->contarUsuariosTotales(),
                'consultas_totales' => $adminModel->contarConsultasTotales(),
                'consultas_pendientes' => $adminModel->contarConsultasPendientes(),
                'transacciones_mes' => $adminModel->contarTransaccionesDelMes(),
            ];

            $comparativaUsuarios = $adminModel->obtenerComparativaUsuariosNuevos();
            $comparativaConsultas = $adminModel->obtenerComparativaConsultasCreadas();
            $comparativaTransacciones = $adminModel->obtenerComparativaTransacciones();

            $comparativas = [
                [
                    'titulo' => 'Usuarios nuevos',
                    'valor_actual' => $comparativaUsuarios['actual'],
                    'valor_anterior' => $comparativaUsuarios['anterior'],
                ],
                [
                    'titulo' => 'Consultas creadas',
                    'valor_actual' => $comparativaConsultas['actual'],
                    'valor_anterior' => $comparativaConsultas['anterior'],
                ],
                [
                    'titulo' => 'Transacciones',
                    'valor_actual' => $comparativaTransacciones['actual'],
                    'valor_anterior' => $comparativaTransacciones['anterior'],
                ],
            ];

            foreach ($comparativas as &$comparativa) {
                $comparativa['variacion'] = $this->calcularVariacionPorcentual(
                    $comparativa['valor_actual'],
                    $comparativa['valor_anterior']
                );
            }
            unset($comparativa);

            $ultimasConsultas = $adminModel->obtenerUltimasConsultas(5);
            $ultimasTransacciones = $adminModel->obtenerUltimasTransacciones(5);
            $ultimosUsuarios = $adminModel->obtenerUltimosUsuarios(5);
            $ultimosInformes = $adminModel->obtenerUltimosInformes(5);
            $ultimaActualizacion = date('d/m/Y H:i');

            require_once './../app/views/admin/dashboard/dashboard_admin.php';
        }

        private function calcularVariacionPorcentual($actual, $anterior) {
            if ((int)$anterior === 0) {
                return (int)$actual > 0 ? 100 : 0;
            }

            return round((($actual - $anterior) / $anterior) * 100, 1);
        }


        public function mostrarPerfilAdmin() {
            // Verificar que el usuario tiene rol de admin
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
                header("Location: ?controller=home&action=mostrarHome");
                exit();
            }

            $titulo = "Gestionalo | Perfil Admin";
            $userModel = new UserModel();
            $datosUsuario = $userModel->obtenerUsuarioActual($_SESSION['usuario']['email']);
            require_once './../app/views/admin/profile/profile_admin.php';
        }
    }
?>