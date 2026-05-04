<?php
    use Dompdf\Dompdf;
    use Dompdf\Options;

    class ReportController {

        /**
         * Muestra la vista de informes disponibles del usuario.
         */
        public function mostrarInformesGenerados() {
            $titulo = 'Gestionalo | Informes generados';
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $informes = [];
            $paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
            $limitePorPagina = 5;
            $totalInformes = 0;
            $totalPaginas = 1;

            if ($idUsuario <= 0) {
                header('Location: index.php?controller=dashboard&action=mostrarDashboard');
                exit();
            }

            try {
                $reportModel = new ReportModel();
                $totalInformes = $reportModel->contarInformesPorUsuario($idUsuario);
                $totalPaginas = max(1, (int)ceil($totalInformes / $limitePorPagina));

                if ($paginaActual > $totalPaginas) {
                    $paginaActual = $totalPaginas;
                }

                $offset = ($paginaActual - 1) * $limitePorPagina;
                $informes = $reportModel->obtenerInformesPaginadosPorUsuario($idUsuario, $limitePorPagina, $offset);
            } catch (Throwable $e) {
                error_log('ReportController::mostrarInformesGenerados -> ' . $e->getMessage());
            }

            require_once './../app/views/tools/reports.php';
        }

        /**
         * Descarga un informe validando que pertenece al usuario autenticado.
         */
        public function descargarInforme() {
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            $idInforme = (int)($_GET['id_informe'] ?? 0);

            if ($idUsuario <= 0 || $idInforme <= 0) {
                $this->redirigirConErrorInformes('Informe no válido.');
            }

            try {
                $reportModel = new ReportModel();
                $informe = $reportModel->obtenerInformePorIdYUsuario($idInforme, $idUsuario);

                if (!$informe) {
                    $this->redirigirConErrorInformes('El informe no existe o no está disponible.');
                }

                $rutaRelativa = (string)($informe['ruta_archivo'] ?? '');
                if ($rutaRelativa === '') {
                    $this->redirigirConErrorInformes('La ruta del informe no es válida.');
                }

                $raizProyecto = dirname(__DIR__, 2);
                $rutaNormalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/\\'));
                $rutaAbsoluta = $raizProyecto . DIRECTORY_SEPARATOR . $rutaNormalizada;
                $rutaRealInforme = realpath($rutaAbsoluta);

                $rutaBaseReportes = $raizProyecto . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'reports';
                $rutaRealBaseReportes = realpath($rutaBaseReportes);

                if (
                    !$rutaRealInforme
                    || !is_file($rutaRealInforme)
                    || !$rutaRealBaseReportes
                    || strpos($rutaRealInforme, $rutaRealBaseReportes) !== 0
                ) {
                    $this->redirigirConErrorInformes('El archivo del informe no existe o no es accesible.');
                }

                $nombreDescarga = trim((string)($informe['nombre_informe'] ?? ''));
                if ($nombreDescarga === '') {
                    $nombreDescarga = 'informe_' . $idInforme;
                }
                $nombreDescarga .= '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $this->sanearNombreArchivo($nombreDescarga) . '"');
                header('Content-Length: ' . filesize($rutaRealInforme));

                readfile($rutaRealInforme);
                exit();
            } catch (Throwable $e) {
                error_log('ReportController::descargarInforme -> ' . $e->getMessage());
                $this->redirigirConErrorInformes('No se pudo descargar el informe.');
            }
        }

        /**
         * Genera y guarda un informe PDF de hipoteca.
         */
        public function generarInformeHipotecaAjax() {
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $this->responderJson(['ok' => false, 'mensaje' => 'Usuario no autenticado.'], 401);
                return;
            }

            $body = $this->obtenerBodyJson();
            $datos = is_array($body['datos'] ?? null) ? $body['datos'] : [];

            if (empty($datos)) {
                $this->responderJson(['ok' => false, 'mensaje' => 'No se recibieron datos para el informe.'], 400);
                return;
            }

            // Use server-generated name + timestamp (minus 3 hours) to ensure consistent times
            $ts = (new DateTimeImmutable())->modify('-3 hours');
            $nombreInforme = 'Informe hipoteca ' . $ts->format('d/m/Y H:i');

            $html = $this->renderHtmlInformeHipoteca($datos);

            $_SESSION['estado_hipoteca'] = $datos['estado'] ?? [];

            try {
                $resultado = $this->guardarInformePdf($idUsuario, 'hipoteca', $nombreInforme, $html);
                $_SESSION['correcto'] = 'Informe de hipoteca guardado correctamente.';
                $this->responderJson([
                    'ok' => true,
                    'mensaje' => 'Informe de hipoteca guardado correctamente.',
                    'id_informe' => $resultado['id_informe'],
                    'ruta_archivo' => $resultado['ruta_relativa'],
                ], 201);
            } catch (Throwable $e) {
                error_log('ReportController::generarInformeHipotecaAjax -> ' . $e->getMessage());
                $_SESSION['error'] = 'No se pudo generar el informe de hipoteca.';
                $this->responderJson([
                    'ok' => false,
                    'mensaje' => 'No se pudo generar el informe de hipoteca: ' . $e->getMessage(),
                ], 500);
            }
        }

        /**
         * Genera y guarda un informe PDF de graficos personalizados.
         */
        public function generarInformeGraficosAjax() {
            $idUsuario = (int)($_SESSION['usuario']['id_usuario'] ?? 0);
            if ($idUsuario <= 0) {
                $_SESSION['error'] = 'Usuario no autenticado para generar informes.';
                $this->responderJson(['ok' => false, 'mensaje' => 'Usuario no autenticado.'], 401);
                return;
            }

            $body = $this->obtenerBodyJson();
            $datos = is_array($body['datos'] ?? null) ? $body['datos'] : [];

            if (empty($datos)) {
                $_SESSION['error'] = 'No se recibieron datos para generar el informe de gráficos.';
                $this->responderJson(['ok' => false, 'mensaje' => 'No se recibieron datos para el informe.'], 400);
                return;
            }

            // Use server-generated name + timestamp (minus 3 hours) to ensure consistent times
            $ts = (new DateTimeImmutable())->modify('-3 hours');
            $nombreInforme = 'Graficos ' . $ts->format('d/m/Y H:i');

            $html = $this->renderHtmlInformeGraficos($datos);

            try {
                $resultado = $this->guardarInformePdf($idUsuario, 'general', $nombreInforme, $html);
                $_SESSION['correcto'] = 'Informe de gráficos guardado correctamente. Puedes acceder a tus informes en Herramientas > Informes generados.';
                $this->responderJson([
                    'ok' => true,
                    'mensaje' => 'Informe de graficos guardado correctamente. Puedes acceder desde Herramientas > Informes generados.',
                    'id_informe' => $resultado['id_informe'],
                    'ruta_archivo' => $resultado['ruta_relativa'],
                ], 201);
            } catch (Throwable $e) {
                error_log('ReportController::generarInformeGraficosAjax -> ' . $e->getMessage());
                $_SESSION['error'] = 'No se pudo generar el informe de gráficos.';
                $this->responderJson(['ok' => false, 'mensaje' => 'No se pudo generar el informe de graficos.'], 500);
            }
        }

        /**
         * Elimina caracteres conflictivos para cabecera Content-Disposition.
         */
        private function sanearNombreArchivo($nombre) {
            $nombre = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', (string)$nombre);
            return $nombre ?: 'informe.pdf';
        }

        /**
         * Redirige al listado de informes mostrando un mensaje de error en sesión.
         */
        private function redirigirConErrorInformes($mensaje) {
            $_SESSION['error'] = $mensaje;
            header('Location: index.php?controller=report&action=mostrarInformesGenerados');
            exit();
        }

        /**
         * Genera el PDF, lo guarda en storage/reports/{idUsuario} y registra la ruta en BD.
         */
        private function guardarInformePdf($idUsuario, $tipoInforme, $nombreInforme, $html) {
            $raizProyecto = dirname(__DIR__, 2);
            $baseDir = $raizProyecto . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'reports';
            $userDir = $baseDir . DIRECTORY_SEPARATOR . (int)$idUsuario;

            if (!is_dir($userDir) && !mkdir($userDir, 0775, true) && !is_dir($userDir)) {
                throw new RuntimeException('No se pudo crear la carpeta de informes del usuario.');
            }

            $tipoSeguro = preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower((string)$tipoInforme));
            $fileName = sprintf('informe_%s_%s.pdf', $tipoSeguro, date('Ymd_His'));
            $absolutePath = $userDir . DIRECTORY_SEPARATOR . $fileName;
            $relativePath = 'storage/reports/' . (int)$idUsuario . '/' . $fileName;

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdf = $dompdf->output();
            if (file_put_contents($absolutePath, $pdf) === false) {
                throw new RuntimeException('No se pudo escribir el archivo PDF en disco.');
            }

            $reportModel = new ReportModel();
            $idTipoInforme = $reportModel->obtenerIdTipoInformePorNombre($tipoInforme);
            $idInforme = $reportModel->crearInforme($idUsuario, $nombreInforme, $idTipoInforme, $relativePath);

            return [
                'id_informe' => $idInforme,
                'ruta_relativa' => $relativePath,
            ];
        }

        private function obtenerBodyJson() {
            $raw = file_get_contents('php://input');
            if (!$raw) {
                return [];
            }

            $json = json_decode($raw, true);
            return is_array($json) ? $json : [];
        }

        private function responderJson($payload, $status = 200) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        private function renderHtmlInformeHipoteca($datos) {
            $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
            $entradas = is_array($datos['entradas'] ?? null) ? $datos['entradas'] : [];
            $escenarios = is_array($datos['escenarios'] ?? null) ? $datos['escenarios'] : [];
            $amortizacion = is_array($datos['amortizacion'] ?? null) ? $datos['amortizacion'] : [];
            $graficoBase64 = (string)($datos['graficoBase64'] ?? '');

            $filasEscenarios = '';
            foreach ($escenarios as $fila) {
                if (!is_array($fila)) {
                    continue;
                }
                $filasEscenarios .= '<tr>'
                    . '<td>' . htmlspecialchars((string)($fila['escenario'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['interes'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['plazo'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['cuota'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['intereses'] ?? '-')) . '</td>'
                    . '</tr>';
            }

            $filasAmort = '';
            foreach ($amortizacion as $fila) {
                if (!is_array($fila)) {
                    continue;
                }
                $filasAmort .= '<tr>'
                    . '<td>' . htmlspecialchars((string)($fila['mes'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['cuota'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['interes'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['capital'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['extra'] ?? '-')) . '</td>'
                    . '<td>' . htmlspecialchars((string)($fila['saldo'] ?? '-')) . '</td>'
                    . '</tr>';
            }

            $imgHtml = '';
            if ($graficoBase64 !== '') {
                $imgHtml = '<h2>Grafico de amortizacion</h2><img src="' . htmlspecialchars($graficoBase64) . '" style="width:100%;max-width:740px;" alt="Grafico">';
            }

            $ts = (new DateTimeImmutable())->modify('-3 hours');

            $html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><style>'
                . 'body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111;} h1{font-size:20px;} h2{font-size:16px;margin-top:18px;} '
                . '.grid{width:100%;border-collapse:collapse;} .grid td,.grid th{border:1px solid #ddd;padding:6px;vertical-align:top;} .kpi td{padding:4px 8px;} '
                . '.page-break{page-break-before:always;} thead{display:table-header-group;} tr{page-break-inside:avoid;}'
                . '</style></head><body>'
                . '<h1>Informe de hipoteca</h1>'
                . '<p>Generado: ' . $ts->format('d/m/Y H:i') . '</p>'
                . '<h2>Resumen</h2>'
                . '<table class="kpi">'
                . '<tr><td>Cuota mensual</td><td><strong>' . htmlspecialchars((string)($resumen['cuota'] ?? '-')) . '</strong></td></tr>'
                . '<tr><td>Capital financiado</td><td>' . htmlspecialchars((string)($resumen['capital'] ?? '-')) . '</td></tr>'
                . '<tr><td>Total intereses</td><td>' . htmlspecialchars((string)($resumen['intereses'] ?? '-')) . '</td></tr>'
                . '<tr><td>Total pagado</td><td>' . htmlspecialchars((string)($resumen['pagado'] ?? '-')) . '</td></tr>'
                . '<tr><td>Ratio esfuerzo</td><td>' . htmlspecialchars((string)($resumen['ratio'] ?? '-')) . '</td></tr>'
                . '<tr><td>Plazo final</td><td>' . htmlspecialchars((string)($resumen['plazoFinal'] ?? '-')) . '</td></tr>'
                . '</table>'
                . '<h2>Entradas</h2>'
                . '<table class="kpi">'
                . '<tr><td>Precio vivienda</td><td>' . htmlspecialchars((string)($entradas['precioVivienda'] ?? '-')) . '</td></tr>'
                . '<tr><td>Entrada</td><td>' . htmlspecialchars((string)($entradas['entradaInicial'] ?? '-')) . '</td></tr>'
                . '<tr><td>Gastos compra</td><td>' . htmlspecialchars((string)($entradas['gastosCompra'] ?? '-')) . '</td></tr>'
                . '<tr><td>Interes anual</td><td>' . htmlspecialchars((string)($entradas['interesAnual'] ?? '-')) . '</td></tr>'
                . '<tr><td>Plazo (meses)</td><td>' . htmlspecialchars((string)($entradas['plazoMeses'] ?? '-')) . '</td></tr>'
                . '</table>'
                . $imgHtml
                . '<h2>Comparador de escenarios</h2>'
                . '<table class="grid"><thead><tr><th>Escenario</th><th>Interes</th><th>Plazo</th><th>Cuota</th><th>Intereses</th></tr></thead><tbody>' . $filasEscenarios . '</tbody></table>'
                . '<h2>Cuadro de amortizacion completo</h2>'
                . '<table class="grid"><thead><tr><th>Mes</th><th>Cuota</th><th>Interes</th><th>Capital</th><th>Extra</th><th>Saldo</th></tr></thead><tbody>' . $filasAmort . '</tbody></table>'
                . '</body></html>';

            return $html;
        }

        private function renderHtmlInformeGraficos($datos) {
            $filtros = is_array($datos['filtros'] ?? null) ? $datos['filtros'] : [];
            $resumen = is_array($datos['resumen'] ?? null) ? $datos['resumen'] : [];
            $imagenes = is_array($datos['imagenes'] ?? null) ? $datos['imagenes'] : [];

            $htmlImagenes = '';
            $titulos = [
                'balance' => 'Balance del periodo',
                'evolucion' => 'Evolucion mensual',
                'objetivos' => 'Evolucion de objetivos',
                'ingresos' => 'Top categorias / subcategorias de ingreso',
                'gastos' => 'Top categorias / subcategorias de gasto',
            ];

            foreach ($titulos as $key => $titulo) {
                $img = trim((string)($imagenes[$key] ?? ''));
                if ($img === '') {
                    continue;
                }

                $htmlImagenes .= '<h2>' . htmlspecialchars($titulo) . '</h2>'
                    . '<img src="' . htmlspecialchars($img) . '" style="width:100%;max-width:740px;" alt="' . htmlspecialchars($titulo) . '">';
            }

            $ts = (new DateTimeImmutable())->modify('-3 hours');

            $html = '<!doctype html><html lang="es"><head><meta charset="utf-8"><style>'
                . 'body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111;} h1{font-size:20px;} h2{font-size:16px;margin-top:18px;} .kpi td{padding:4px 8px;}'
                . '</style></head><body>'
                . '<h1>Informe de graficos personalizados</h1>'
                . '<p>Generado: ' . $ts->format('d/m/Y H:i') . '</p>'
                . '<h2>Filtros aplicados</h2>'
                . '<table class="kpi">'
                . '<tr><td>Tipo</td><td>' . htmlspecialchars((string)($filtros['tipo'] ?? 'Todos')) . '</td></tr>'
                . '<tr><td>Categoria</td><td>' . htmlspecialchars((string)($filtros['categoria'] ?? 'Todas')) . '</td></tr>'
                . '<tr><td>Subcategoria</td><td>' . htmlspecialchars((string)($filtros['subcategoria'] ?? 'Todas')) . '</td></tr>'
                . '<tr><td>Desde</td><td>' . htmlspecialchars((string)($filtros['fechaDesde'] ?? '-')) . '</td></tr>'
                . '<tr><td>Hasta</td><td>' . htmlspecialchars((string)($filtros['fechaHasta'] ?? '-')) . '</td></tr>'
                . '<tr><td>Metodo</td><td>' . htmlspecialchars((string)($filtros['metodo'] ?? 'Todos')) . '</td></tr>'
                . '</table>'
                . '<h2>Resumen del periodo</h2>'
                . '<table class="kpi">'
                . '<tr><td>Ingresos</td><td><strong>' . htmlspecialchars((string)($resumen['ingresos'] ?? '-')) . '</strong></td></tr>'
                . '<tr><td>Gastos</td><td><strong>' . htmlspecialchars((string)($resumen['gastos'] ?? '-')) . '</strong></td></tr>'
                . '<tr><td>Balance</td><td><strong>' . htmlspecialchars((string)($resumen['balance'] ?? '-')) . '</strong></td></tr>'
                . '</table>'
                . $htmlImagenes
                . '</body></html>';

            return $html;
        }
    }
?>