<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main">
            <div class="transactions-toolbar">
                <h1>Estas son tus consultas <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
                <button class="btn transactions-action-btn transaction-search__reset" type="button"
                    onclick="window.location.href='index.php?controller=contact&action=mostrarCrearConsulta'">
                    Nueva consulta
                </button>
            </div>
            <div class="transactions-table-wrap">
                <div class="transactions-summary-bar">
                    <div class="transactions-summary-head">
                        <p class="transactions-summary-text">
                            Total de consultas: <?= (int)($totalConsultas ?? count($consultas ?? [])) ?>
                        </p>
                    </div>
                    <p class="muted">También puedes enviar tus consultas al correo electrónico: gestionalo2026@gmail.com</p>
                </div>
                <?php
                    $queryBase = [
                        'controller' => 'contact',
                        'action' => 'mostrarMisConsultas'
                    ];

                    $buildConsultasUrl = function($extra = []) use ($queryBase) {
                        return 'index.php?' . http_build_query(array_merge($queryBase, $extra));
                    };
                ?>
                <?php if (!empty($consultas)): ?>
                    <table class="transactions-table queries-table">
                        <thead>
                            <tr>
                                <th>Asunto</th>
                                <th>Comentario</th>
                                <th>Respuesta</th>
                                <th>Estado</th>
                                <th>Fecha de creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultas as $consulta): ?>
                                <?php
                                    $estadoConsulta = strtolower(trim((string)($consulta['estado'] ?? '')));
                                    $claseEstadoConsulta = 'query-state';
                                    $claseFilaConsulta = 'query-row';

                                    if ($estadoConsulta === 'enviada') {
                                        $claseEstadoConsulta .= ' query-state--enviada';
                                        $claseFilaConsulta .= ' query-row--enviada';
                                    } elseif ($estadoConsulta === 'en curso') {
                                        $claseEstadoConsulta .= ' query-state--curso';
                                        $claseFilaConsulta .= ' query-row--curso';
                                    } elseif ($estadoConsulta === 'finalizada') {
                                        $claseEstadoConsulta .= ' query-state--finalizada';
                                        $claseFilaConsulta .= ' query-row--finalizada';
                                    } else {
                                        $claseFilaConsulta .= ' query-row--default';
                                    }
                                ?>
                                <tr class="<?= htmlspecialchars($claseFilaConsulta) ?>">
                                    <td><?= htmlspecialchars($consulta['asunto'] ?? '') ?></td>
                                    <td class="tx-concepto query-text-cell" title="<?= htmlspecialchars($consulta['comentario'] ?? '') ?>">
                                        <?= htmlspecialchars($consulta['comentario'] ?? '') ?>
                                    </td>
                                    <td class="tx-concepto query-text-cell" title="<?= htmlspecialchars($consulta['respuesta'] ?? '') ?>">
                                        <?php if (!empty($consulta['respuesta'])): ?>
                                            <?= htmlspecialchars($consulta['respuesta']) ?>
                                        <?php else: ?>
                                            <span class="muted">Sin respuesta aún</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="<?= htmlspecialchars($claseEstadoConsulta) ?>"><?= htmlspecialchars($consulta['estado'] ?? '') ?></span></td>
                                    <td>
                                        <?= !empty($consulta['fecha_creacion']) ? date('d/m/Y H:i', strtotime($consulta['fecha_creacion'])) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (($totalPaginas ?? 1) > 1): ?>
                        <?php
                            $inicioPaginas = max(1, $paginaActual - 2);
                            $finPaginas = min($totalPaginas, $paginaActual + 2);
                        ?>
                        <nav class="transactions-pagination" aria-label="Paginación de consultas">
                            <?php if ($paginaActual > 1): ?>
                                <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildConsultasUrl(['pagina' => $paginaActual - 1])) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
                            <?php endif; ?>

                            <?php if ($inicioPaginas > 1): ?>
                                <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildConsultasUrl(['pagina' => 1])) ?>">1</a>
                                <?php if ($inicioPaginas > 2): ?>
                                    <span class="transactions-pagination__ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
                                <?php if ($pagina == $paginaActual): ?>
                                    <span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
                                <?php else: ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildConsultasUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($finPaginas < $totalPaginas): ?>
                                <?php if ($finPaginas < $totalPaginas - 1): ?>
                                    <span class="transactions-pagination__ellipsis">...</span>
                                <?php endif; ?>
                                <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildConsultasUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
                            <?php endif; ?>

                            <?php if ($paginaActual < $totalPaginas): ?>
                                <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildConsultasUrl(['pagina' => $paginaActual + 1])) ?>">Siguiente</a>
                            <?php else: ?>
                                <span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="transactions-summary-bar">
                        <p class="muted">Todavía no has enviado ninguna consulta. Puedes crear una desde el botón "Nueva consulta".</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>