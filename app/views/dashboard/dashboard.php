<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <section class="dashboard-card dashboard-card--main">
            <h1>Bienvenido al panel de control <?= $_SESSION['usuario']['nombre'] ?></h1>

            <!-- Contenedor grid con tres columnas -->
            <div class="dashboard-grid"> 
                <!-- Fila 1: Últimas transacciones (2 cols) + Balance (1 col) -->
                <section class="dashboard-card dashboard-card--transactions">
                    <div class="transactions-toolbar">
                        <h2>Últimas transacciones</h2>
                        <a class="btn btn-enviar" href="index.php?controller=transaction&action=mostrarFormularioCrearTransaccion">
                            Nueva transacción
                        </a>
                    </div>
                    <!-- Listado de las últimas 10 transacciones -->
                    <?php if (!empty($ultimasTransacciones)): ?>
                        <div class="dashboard-tx-wrap">
                            <div class="dashboard-tx-head dashboard-tx-head--transactions" aria-hidden="true">
                                <span>Categoría</span>
                                <span>Subcategoría</span>
                                <span>Concepto</span>
                                <span>Fecha</span>
                                <span class="is-right">Importe</span>
                            </div>

                            <ul class="transaction-list transaction-list--transactions">
                            <?php foreach ($ultimasTransacciones as $transaccion): ?>
                                <?php $claseFila = (strtolower($transaccion['tipo_movimiento']) === 'gasto') ? 'dashboard-tx-row--gasto' : 'dashboard-tx-row--ingreso'; ?>
                                <li class="<?= $claseFila ?>">
                                    <span class="tx-cell tx-cell--categoria"><?= $transaccion['nombre_categoria'] ?? '-' ?></span>
                                    <span class="tx-cell"><?= $transaccion['nombre_subcategoria'] ?? '-' ?></span>
                                    <span class="tx-cell tx-cell--concepto"><?= htmlspecialchars($transaccion['concepto']) ?></span>
                                    <span class="tx-cell tx-cell--fecha"><?= date('d/m/Y', strtotime($transaccion['fecha_movimiento'])) ?></span>
                                    <span class="tx-cell tx-cell--importe is-right"><?= number_format($transaccion['importe'], 2) ?> €</span>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="muted">No se han registrado transacciones aún.</p>
                    <?php endif; ?>
                </section>

                <section class="dashboard-card dashboard-card--balance">
                    <h2>Balance mensual</h2>
                    <div 
                        class="dashboard-balance-chart"
                        id="dashboard-balance-chart"
                        data-balance="<?= htmlspecialchars(json_encode($balanceActual)) ?>"
                    >
                        <!-- Gráfico de tarta renderizado aquí -->
                    </div>
                    <div class="dashboard-balance-info">
                        <p class="dashboard-balance-summary">
                            <span class="dashboard-balance-label">Balance neto:</span>
                            <span class="dashboard-balance-value <?= htmlspecialchars($claseBalance) ?>">
                                <?= number_format($balanceActual['balance'], 2, ',', '.') ?> €
                            </span>
                        </p>
                    </div>
                </section>

                <!-- Fila 2: Objetivos actuales (3 cols) -->
                <section class="dashboard-card dashboard-card--goals">
                    <div class="transactions-toolbar">
                        <h2>Objetivos actuales</h2>
                        <a class="btn transactions-action-btn" href="index.php?controller=goal&action=mostrarObjetivosAhorro">Ver todos los objetivos</a>
                    </div>
                    <?php if (!empty($ultimosObjetivos)): ?>
                        <div class="dashboard-tx-wrap">
                            <div class="dashboard-tx-head dashboard-tx-head--goals" aria-hidden="true">
                                <span>Objetivo</span>
                                <span class="is-right">Ahorrado</span>
                                <span class="is-right">Meta</span>
                                <span class="is-right">Progreso</span>
                                <span>Fecha límite</span>
                            </div>

                            <ul class="transaction-list transaction-list--goals">
                                <?php foreach ($ultimosObjetivos as $objetivo): ?>
                                    <?php
                                        $cantidadMeta = (float)($objetivo['cantidad_meta'] ?? 0);
                                        $saldoApartado = (float)($objetivo['saldo_apartado'] ?? 0);
                                        $progreso = max(0, min(100, (float)($objetivo['progreso_pct'] ?? 0)));
                                        $claseProgresoObjetivo = 'objective-progress';

                                        if ($progreso >= 100) {
                                            $claseProgresoObjetivo .= ' objective-progress--full';
                                        } elseif ($progreso >= 70) {
                                            $claseProgresoObjetivo .= ' objective-progress--high';
                                        } elseif ($progreso >= 30) {
                                            $claseProgresoObjetivo .= ' objective-progress--medium';
                                        } else {
                                            $claseProgresoObjetivo .= ' objective-progress--low';
                                        }

                                        $fechaLimite = !empty($objetivo['fecha_limite'])
                                            ? date('d/m/Y', strtotime($objetivo['fecha_limite']))
                                            : '-';
                                    ?>
                                    <li>
                                        <span class="tx-cell tx-cell--goal-name"><?= htmlspecialchars((string)($objetivo['nombre_objetivo'] ?? '')) ?></span>
                                        <span class="tx-cell tx-cell--goal-saved is-right"><?= number_format($saldoApartado, 2) ?> €</span>
                                        <span class="tx-cell tx-cell--goal-target is-right"><?= number_format($cantidadMeta, 2) ?> €</span>
                                        <span class="tx-cell tx-cell--goal-progress is-right"><span class="<?= htmlspecialchars($claseProgresoObjetivo) ?>"><?= number_format($progreso, 2) ?>%</span></span>
                                        <span class="tx-cell tx-cell--goal-deadline"><?= htmlspecialchars($fechaLimite) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p class="muted">Todavía no tienes objetivos creados. Empieza creando uno para seguir su progreso.</p>
                    <?php endif; ?>
                </section>
            </div>
        </section>
    </div>
    <script src="./js/dashboard.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php 
    require_once './../app/views/layout/footer.php';
?>