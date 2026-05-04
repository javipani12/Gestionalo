<?php
    require_once './../app/views/layout/header.php';

    $objetivoDetalle = $objetivoDetalle ?? [];
    $historialObjetivo = $historialObjetivo ?? [];

    $meta = (float)($objetivoDetalle['cantidad_meta'] ?? 0);
    $apartado = (float)($objetivoDetalle['saldo_apartado'] ?? 0);
    $restante = max($meta - $apartado, 0);
    $progreso = max(0, min(100, (float)($objetivoDetalle['progreso_pct'] ?? 0)));
    $descripcionObjetivo = trim((string)($objetivoDetalle['descripcion'] ?? ''));
    $paginaHistorialActual = max(1, (int)($paginaHistorialActual ?? 1));
    $totalPaginasHistorial = max(1, (int)($totalPaginasHistorial ?? 1));
    $totalHistorialObjetivo = max(0, (int)($totalHistorialObjetivo ?? count($historialObjetivo)));
    $fechaLimiteTexto = '-';
    $claseFechaLimite = 'goal-deadline-badge goal-deadline-badge--none';

    if (!empty($objetivoDetalle['fecha_limite'])) {
        $fechaLimite = DateTime::createFromFormat('Y-m-d', (string)$objetivoDetalle['fecha_limite']);

        if ($fechaLimite !== false) {
            $hoy = new DateTime('today');
            $fechaLimiteTexto = $fechaLimite->format('d/m/Y');
            $diasRestantes = (int)$hoy->diff($fechaLimite)->format('%r%a');

            if ($diasRestantes < 0) {
                $claseFechaLimite = 'goal-deadline-badge goal-deadline-badge--overdue';
            } elseif ($diasRestantes <= 7) {
                $claseFechaLimite = 'goal-deadline-badge goal-deadline-badge--soon';
            } else {
                $claseFechaLimite = 'goal-deadline-badge goal-deadline-badge--upcoming';
            }
        }
    }

    $claseEstadoObjetivo = 'objective-state';
    $estadoObjetivo = strtolower(trim((string)($objetivoDetalle['estado_objetivo'] ?? '')));
    if ($estadoObjetivo === 'en curso') {
        $claseEstadoObjetivo .= ' objective-state--curso';
    } elseif ($estadoObjetivo === 'completado') {
        $claseEstadoObjetivo .= ' objective-state--completado';
    } elseif ($estadoObjetivo === 'no completado') {
        $claseEstadoObjetivo .= ' objective-state--cancelado';
    }

    // Mapear estado del objetivo a clase CSS del progreso
    $sufijo = 'medium'; // Valor por defecto
    if (in_array($estadoObjetivo, ['completado', 'completada'], true)) {
        $sufijo = 'full';
    } elseif (in_array($estadoObjetivo, ['cancelado', 'cancelada', 'no completado', 'no completada'], true)) {
        $sufijo = 'low';
    }
    $claseProgresoObjetivo = 'objective-progress objective-progress--' . $sufijo;
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main" aria-labelledby="goal-detail-title">
            <div class="transactions-toolbar">
                <div>
                    <h1 id="goal-detail-title"><?= htmlspecialchars((string)($objetivoDetalle['nombre_objetivo'] ?? 'Detalle de objetivo')) ?></h1>
                    <p class="dashboard-lead">Consulta el estado del objetivo, su avance y el historial de transacciones internas asociadas.</p>
                </div>

                <div class="actions" style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; margin:0;">
                    <a class="btn transactions-action-btn" href="index.php?controller=goal&action=mostrarObjetivosAhorro">Volver a objetivos</a>
                    <a class="btn transactions-action-btn" href="index.php?controller=transaction&action=mostrarFormularioCrearTransaccion&id_objetivo=<?= (int)($objetivoDetalle['id_objetivo'] ?? 0) ?>&modo_objetivo=aporte">Realizar aporte</a>
                    <a class="btn transactions-action-btn" href="index.php?controller=transaction&action=mostrarFormularioCrearTransaccion&id_objetivo=<?= (int)($objetivoDetalle['id_objetivo'] ?? 0) ?>&modo_objetivo=retiro">Realizar retiro</a>
                </div>
            </div>

            <div class="dashboard-grid dashboard-grid--goal-detail">
                <section class="dashboard-card">
                    <h2>Resumen del objetivo</h2>
                    <div class="goal-detail-highlights">
                        <article class="goal-detail-highlight">
                            <h3>Descripción</h3>
                            <p class="goal-detail-highlight__text">
                                <?= $descripcionObjetivo !== ''
                                    ? nl2br(htmlspecialchars($descripcionObjetivo))
                                    : 'Sin descripción añadida para este objetivo.' ?>
                            </p>
                        </article>

                        <article class="dashboard-tx-wrap">
                            <div class="dashboard-tx-head dashboard-tx-head--goal-summary" aria-hidden="true">
                                <span>Ahorrado</span>
                                <span>Meta</span>
                                <span>Restante</span>
                                <span>Progreso</span>
                                <span>Estado</span>
                                <span>Fecha límite</span>
                            </div>
                            <ul class="transaction-list transaction-list--goal-summary">
                                <li>
                                    <span class="tx-cell"><?= number_format($apartado, 2, ',', '.') ?> €</span>
                                    <span class="tx-cell"><?= number_format($meta, 2, ',', '.') ?> €</span>
                                    <span class="tx-cell"><?= number_format($restante, 2, ',', '.') ?> €</span>
                                    <span class="tx-cell"><span class="<?= htmlspecialchars($claseProgresoObjetivo) ?>"><?= number_format($progreso, 2, ',', '.') ?>%</span></span>
                                    <span class="tx-cell"><span class="<?= htmlspecialchars($claseEstadoObjetivo) ?>"><?= htmlspecialchars(ucfirst((string)($objetivoDetalle['estado_objetivo'] ?? '-'))) ?></span></span>
                                    <span class="tx-cell"><span class="<?= htmlspecialchars($claseFechaLimite) ?>"><?= htmlspecialchars($fechaLimiteTexto) ?></span></span>
                                </li>
                            </ul>
                        </article>
                    </div>
                    
                </section>

                <section class="dashboard-card">
                    <h2>Historial del objetivo</h2>
                    <p class="muted">Total de transacciones asociadas: <?= $totalHistorialObjetivo ?></p>
                    <?php if (empty($historialObjetivo)): ?>
                        <p class="muted">Aún no hay transacciones asociadas a este objetivo.</p>
                    <?php else: ?>
                        <?php
                            $inicioPaginas = max(1, $paginaHistorialActual - 2);
                            $finPaginas = min($totalPaginasHistorial, $paginaHistorialActual + 2);

                            $buildGoalDetailUrl = function($extra = []) use ($objetivoDetalle, $paginaHistorialActual) {
                                $params = array_merge([
                                    'controller' => 'goal',
                                    'action' => 'mostrarDetalleObjetivo',
                                    'id_objetivo' => (int)($objetivoDetalle['id_objetivo'] ?? 0),
                                    'pagina_historial' => $paginaHistorialActual,
                                ], $extra);

                                return 'index.php?' . http_build_query($params);
                            };
                        ?>
                        <div class="transactions-table-wrap transactions-table-wrap--goal-history" role="region" aria-label="Historial de transacciones del objetivo">
                            <table class="transactions-table transactions-table--goal-history">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Fecha</th>
                                        <th>Método</th>
                                        <th class="is-right">Importe</th>
                                        <th class="transactions-actions-col">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historialObjetivo as $movimiento): ?>
                                        <?php
                                            $tipoMovimiento = strtolower(trim((string)($movimiento['tipo_movimiento'] ?? '')));
                                            $esInterna = in_array($tipoMovimiento, ['transferencia interna aporte', 'transferencia interna retiro'], true);
                                            $esAporte = $tipoMovimiento === 'transferencia interna aporte';

                                            if ($esInterna) {
                                                $claseTipo = 'tx-type tx-type--internal';
                                            } else {
                                                $claseTipo = $esAporte ? 'tx-type tx-type--ingreso' : 'tx-type tx-type--gasto';
                                            }

                                            $claseImporte = $esAporte ? 'tx-amount tx-amount--ingreso' : 'tx-amount tx-amount--gasto';
                                        ?>
                                        <tr>
                                            <td><span class="<?= $claseTipo ?>"><?= htmlspecialchars((string)($movimiento['tipo_movimiento'] ?? '')) ?></span></td>
                                            <td class="tx-concepto"><?= htmlspecialchars((string)($movimiento['concepto'] ?? '-')) ?></td>
                                            <td><?= !empty($movimiento['fecha_movimiento']) ? htmlspecialchars(date('d/m/Y', strtotime($movimiento['fecha_movimiento']))) : '-' ?></td>
                                            <td><?= htmlspecialchars(ucfirst((string)($movimiento['metodo_pago'] ?? '-'))) ?></td>
                                            <td class="is-right"><span class="<?= $claseImporte ?>"><?= number_format((float)($movimiento['importe'] ?? 0), 2, ',', '.') ?> €</span></td>
                                            <td class="tx-actions-cell">
                                                <div class="transactions-actions">
                                                    <a
                                                        href="index.php?controller=transaction&action=mostrarFormularioEditarTransaccion&id_transaccion=<?= (int)($movimiento['id_transaccion'] ?? 0) ?>&redirigir_objetivo_id=<?= (int)($objetivoDetalle['id_objetivo'] ?? 0) ?>&redirigir_pagina_historial=<?= (int)$paginaHistorialActual ?>"
                                                        class="tx-icon-btn tx-icon-btn--edit"
                                                        aria-label="Editar transacción"
                                                        title="Editar transacción"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
                                                        </svg>
                                                    </a>

                                                    <a
                                                        href="index.php?controller=transaction&action=eliminarTransaccion&id_transaccion=<?= (int)($movimiento['id_transaccion'] ?? 0) ?>"
                                                        class="tx-icon-btn tx-icon-btn--delete"
                                                        aria-label="Eliminar transacción"
                                                        title="Eliminar transacción"
                                                        onclick="return confirm('¿Estás seguro de que quieres eliminar esta transacción?');"
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

                        <?php if ($totalPaginasHistorial > 1): ?>
                            <nav class="transactions-pagination" aria-label="Paginación del historial del objetivo">
                                <?php if ($paginaHistorialActual > 1): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalDetailUrl(['pagina_historial' => $paginaHistorialActual - 1])) ?>">Anterior</a>
                                <?php else: ?>
                                    <span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
                                <?php endif; ?>

                                <?php if ($inicioPaginas > 1): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalDetailUrl(['pagina_historial' => 1])) ?>">1</a>
                                    <?php if ($inicioPaginas > 2): ?>
                                        <span class="transactions-pagination__ellipsis">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
                                    <?php if ($pagina === $paginaHistorialActual): ?>
                                        <span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
                                    <?php else: ?>
                                        <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalDetailUrl(['pagina_historial' => $pagina])) ?>"><?= $pagina ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($finPaginas < $totalPaginasHistorial): ?>
                                    <?php if ($finPaginas < $totalPaginasHistorial - 1): ?>
                                        <span class="transactions-pagination__ellipsis">...</span>
                                    <?php endif; ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalDetailUrl(['pagina_historial' => $totalPaginasHistorial])) ?>"><?= $totalPaginasHistorial ?></a>
                                <?php endif; ?>

                                <?php if ($paginaHistorialActual < $totalPaginasHistorial): ?>
                                    <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGoalDetailUrl(['pagina_historial' => $paginaHistorialActual + 1])) ?>">Siguiente</a>
                                <?php else: ?>
                                    <span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>