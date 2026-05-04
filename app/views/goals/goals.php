<?php
    require_once './../app/views/layout/header.php';
    $paginaActual = $paginaActual ?? 1;
    $totalPaginas = $totalPaginas ?? 1;
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main goals-shell" aria-labelledby="goals-title">
            <div class="transactions-toolbar">
                <div>
                    <h1 id="goals-title">Objetivos de ahorro</h1>
                    <p class="dashboard-lead">Mantén un seguimiento del dinero apartado para cada objetivo y controla su progreso real.</p>
                </div>

                <div class="actions" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; margin:0;">
                    <a class="btn transactions-action-btn" href="?controller=goal&action=mostrarFormularioCrearObjetivo">Nuevo objetivo</a>
                    <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarHerramientas">Volver a herramientas</a>
                </div>
            </div>

            <div class="dashboard-grid">
                <section class="dashboard-card goals-summary">
                    <h2>Resumen de objetivos</h2>
                    <?php if (empty($objetivos)): ?>
                        <p class="muted">Todavía no tienes objetivos de ahorro creados.</p>
                    <?php else: ?>
                        <div class="transactions-table-wrap" role="region" aria-label="Resumen de objetivos de ahorro">
                            <table class="transactions-table goals-table">
                                <thead>
                                    <tr>
                                        <th>Objetivo</th>
                                        <th>Meta</th>
                                        <th>Apartado</th>
                                        <th>Restante</th>
                                        <th>Progreso</th>
                                        <th>Estado</th>
                                        <th>Fecha límite</th>
                                        <th class="transactions-actions-col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($objetivos as $objetivo): ?>
                                        <?php
                                            $fechaLimiteTexto = '-';
                                            if (!empty($objetivo['fecha_limite'])) {
                                                $fechaLimite = DateTime::createFromFormat('Y-m-d', (string)$objetivo['fecha_limite']);
                                                if ($fechaLimite !== false) {
                                                    $fechaLimiteTexto = $fechaLimite->format('d/m/Y');
                                                }
                                            }

                                            $meta = (float)($objetivo['cantidad_meta'] ?? 0);
                                            $apartado = (float)($objetivo['saldo_apartado'] ?? 0);
                                            $restante = max($meta - $apartado, 0);
                                            $progreso = max(0, min(100, (float)($objetivo['progreso_pct'] ?? 0)));
                                            $estadoObjetivo = strtolower(trim((string)($objetivo['estado_objetivo'] ?? '')));
                                            $claseEstadoObjetivo = 'objective-state';
                                            
                                            // Mapear estado del objetivo a clase CSS del progreso
                                            $sufijo = 'medium'; // Valor por defecto
                                            if (in_array($estadoObjetivo, ['completado', 'completada'], true)) {
                                                $sufijo = 'full';
                                            } elseif (in_array($estadoObjetivo, ['cancelado', 'cancelada', 'no completado', 'no completada'], true)) {
                                                $sufijo = 'low';
                                            }
                                            $claseProgresoObjetivo = 'objective-progress objective-progress--' . $sufijo;

                                            if ($estadoObjetivo === 'en curso') {
                                                $claseEstadoObjetivo .= ' objective-state--curso';
                                                $claseFilaObjetivo = 'goal-row goal-row--curso';
                                            } elseif ($estadoObjetivo === 'completado') {
                                                $claseEstadoObjetivo .= ' objective-state--completado';
                                                $claseFilaObjetivo = 'goal-row goal-row--completado';
                                            } elseif ($estadoObjetivo === 'no completado') {
                                                $claseEstadoObjetivo .= ' objective-state--cancelado';
                                                $claseFilaObjetivo = 'goal-row goal-row--cancelado';
                                            } else {
                                                $claseFilaObjetivo = 'goal-row goal-row--default';
                                            }
                                        ?>
                                        <tr class="<?= htmlspecialchars($claseFilaObjetivo) ?>">
                                            <td><?= htmlspecialchars((string)($objetivo['nombre_objetivo'] ?? '')) ?></td>
                                            <td><?= number_format($meta, 2, ',', '.') ?> €</td>
                                            <td><?= number_format($apartado, 2, ',', '.') ?> €</td>
                                            <td><?= number_format($restante, 2, ',', '.') ?> €</td>
                                            <td><span class="<?= htmlspecialchars($claseProgresoObjetivo) ?>"><?= number_format($progreso, 2, ',', '.') ?>%</span></td>
                                            <td><span class="<?= htmlspecialchars($claseEstadoObjetivo) ?>"><?= htmlspecialchars(ucfirst((string)($objetivo['estado_objetivo'] ?? '-'))) ?></span></td>
                                            <td><?= htmlspecialchars($fechaLimiteTexto) ?></td>
                                            <td>
                                                <div class="transactions-actions">
                                                    <a
                                                        href="index.php?controller=goal&action=mostrarDetalleObjetivo&id_objetivo=<?= (int)($objetivo['id_objetivo'] ?? 0) ?>"
                                                        class="tx-icon-btn tx-icon-btn--view"
                                                        aria-label="Abrir objetivo"
                                                        title="Abrir objetivo"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M12 5c-5.45 0-9.27 4.88-9.43 5.09a1 1 0 0 0 0 1.22C2.73 11.52 6.55 16.4 12 16.4s9.27-4.88 9.43-5.09a1 1 0 0 0 0-1.22C21.27 9.88 17.45 5 12 5Zm0 9a3.4 3.4 0 1 1 0-6.8 3.4 3.4 0 0 1 0 6.8Zm0-1.8a1.6 1.6 0 1 0 0-3.2 1.6 1.6 0 0 0 0 3.2Z"/>
                                                        </svg>
                                                    </a>

                                                    <a
                                                        href="index.php?controller=goal&action=mostrarFormularioEditarObjetivo&id_objetivo=<?= (int)($objetivo['id_objetivo'] ?? 0) ?>"
                                                        class="tx-icon-btn tx-icon-btn--edit"
                                                        aria-label="Editar objetivo"
                                                        title="Editar objetivo"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
                                                        </svg>
                                                    </a>

                                                    <a
                                                        href="index.php?controller=goal&action=eliminarObjetivo&id_objetivo=<?= (int)($objetivo['id_objetivo'] ?? 0) ?>"
                                                        class="tx-icon-btn tx-icon-btn--delete"
                                                        aria-label="Eliminar objetivo"
                                                        title="Eliminar objetivo"
                                                        onclick="return confirm('¿Estás seguro de que quieres eliminar este objetivo?');"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M6 7h12v2H6V7Zm2 3h8l-.7 9.2c-.04.46-.42.8-.88.8H9.58c-.46 0-.84-.34-.88-.8L8 10Zm3-5h2c.55 0 1 .45 1 1v1h-4V6c0-.55.45-1 1-1Z"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (($totalPaginas ?? 1) > 1): ?>
                            <?php
                                $inicioPaginas = max(1, $paginaActual - 2);
                                $finPaginas = min($totalPaginas, $paginaActual + 2);

                                $buildGoalsUrl = function($extra = []) use ($paginaActual) {
                                    $params = array_merge([
                                        'controller' => 'goal',
                                        'action' => 'mostrarObjetivosAhorro',
                                        'pagina' => $paginaActual,
                                    ], $extra);

                                    return 'index.php?' . http_build_query($params);
                                };
                            ?>
                            <nav class="transactions-pagination" aria-label="Paginación de objetivos">
                                <?php if ($paginaActual > 1): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalsUrl(['pagina' => $paginaActual - 1])) ?>">Anterior</a>
                                <?php else: ?>
                                    <span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
                                <?php endif; ?>

                                <?php if ($inicioPaginas > 1): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalsUrl(['pagina' => 1])) ?>">1</a>
                                    <?php if ($inicioPaginas > 2): ?>
                                        <span class="transactions-pagination__ellipsis">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
                                    <?php if ($pagina == $paginaActual): ?>
                                        <span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
                                    <?php else: ?>
                                        <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalsUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($finPaginas < $totalPaginas): ?>
                                    <?php if ($finPaginas < $totalPaginas - 1): ?>
                                        <span class="transactions-pagination__ellipsis">...</span>
                                    <?php endif; ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalsUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
                                <?php endif; ?>

                                <?php if ($paginaActual < $totalPaginas): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalsUrl(['pagina' => $paginaActual + 1])) ?>">Siguiente</a>
                                <?php else: ?>
                                    <span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

                <section class="dashboard-card goals-status">
                    <h2>Estado general</h2>
                    <div class="goals-status-grid">
                        <article class="goals-status-card goals-status-card--active">
                            <p class="goals-status-card__label">Objetivos activos</p>
                            <p class="goals-status-card__value"><?= (int)$totales['activos'] ?></p>
                        </article>
                        <article class="goals-status-card goals-status-card--completed">
                            <p class="goals-status-card__label">Objetivos completados</p>
                            <p class="goals-status-card__value"><?= (int)$totales['completados'] ?></p>
                        </article>
                        <article class="goals-status-card goals-status-card--active">
                            <p class="goals-status-card__label">Meta total</p>
                            <p class="goals-status-card__value"><?= number_format($totales['meta'], 0) ?> €</p>
                        </article>
                        <article class="goals-status-card goals-status-card--completed">
                            <p class="goals-status-card__label">Dinero apartado</p>
                            <p class="goals-status-card__value"><?= number_format($totales['apartado'], 0) ?> €</p>
                        </article>
                        <article class="goals-status-card goals-status-card--pending">
                            <p class="goals-status-card__label">Pendiente por apartar</p>
                            <p class="goals-status-card__value"><?= number_format($totales['restante'], 0) ?> €</p>
                        </article>
                    </div>
                </section>
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>