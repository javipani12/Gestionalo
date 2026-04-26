<?php
    require_once './../app/views/layout/header_admin.php';
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main">
            <div class="transactions-toolbar">
                <h1>Consultas de todos los usuarios</h1>
            </div>
            <div class="transactions-table-wrap">
                <div class="transactions-summary-bar">
                    <div class="transactions-summary-head">
                        <p class="transactions-summary-text">
                            Total de consultas: <?= (int)($totalConsultas ?? count($consultas ?? [])) ?>
                        </p>
                    </div>
                </div>
                <?php
                    $queryBase = [
                        'controller' => 'admin',
                        'action' => 'mostrarConsultasAdmin'
                    ];

                    $buildConsultasUrl = function($extra = []) use ($queryBase) {
                        return 'index.php?' . http_build_query(array_merge($queryBase, $extra));
                    };
                ?>
                <?php if (!empty($consultas)): ?>
                    <table class="transactions-table admin-queries-table">
                        <thead>
                            <tr>
                                <th>Asunto</th>
                                <th>Usuario</th>
                                <th>Comentario</th>
                                <th>Comentario admin</th>
                                <th>Estado</th>
                                <th>Fecha de creación</th>
                                <th class="transactions-actions-col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultas as $consulta): ?>
                                <?php
                                    $estadoConsulta = strtolower(trim((string)($consulta['estado'] ?? '')));
                                    $claseEstadoConsulta = 'query-state';

                                    if ($estadoConsulta === 'enviada') {
                                        $claseEstadoConsulta .= ' query-state--enviada';
                                    } elseif ($estadoConsulta === 'en curso') {
                                        $claseEstadoConsulta .= ' query-state--curso';
                                    } elseif ($estadoConsulta === 'finalizada') {
                                        $claseEstadoConsulta .= ' query-state--finalizada';
                                    }
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($consulta['asunto'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($consulta['email_usuario'] ?? '') ?></td>
                                    <td class="tx-concepto" title="<?= htmlspecialchars($consulta['comentario'] ?? '') ?>">
                                        <?= htmlspecialchars($consulta['comentario'] ?? '') ?>
                                    </td>
                                    <td class="tx-concepto" title="<?= htmlspecialchars($consulta['respuesta'] ?? '') ?>">
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
                                    <td>
                                        <div class="transactions-actions">
                                            <a
                                                href="?controller=admin&action=mostrarEditarConsultaAdmin&id_consulta=<?= (int)($consulta['id_consulta'] ?? 0) ?>"
                                                class="tx-icon-btn tx-icon-btn--edit"
                                                aria-label="Editar consulta"
                                                title="Editar consulta"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
                                                </svg>
                                            </a>
                                        </div>
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
                        <p class="muted">No hay consultas registradas actualmente.</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>