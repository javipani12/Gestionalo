<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <h1>Bienvenido al panel de control <?= $_SESSION['usuario']['nombre'] ?></h1>

        <!-- Contenedor grid con dos columnas -->
        <div class="dashboard-grid"> 
            <section class="dashboard-card dashboard-card--main">
                <div class="transactions-toolbar">
                    <h2>Últimas transacciones</h2>
                    <a class="btn btn-enviar" href="index.php?controller=transaction&action=mostrarFormularioCrearTransaccion">
                        Nueva transacción
                    </a>
                </div>
                <!-- Listado de las últimas 10 transacciones -->
                <?php if (!empty($ultimasTransacciones)): ?>
                    <div class="dashboard-tx-wrap">
                        <div class="dashboard-tx-head" aria-hidden="true">
                            <span>Categoría</span>
                            <span>Subcategoría</span>
                            <span>Concepto</span>
                            <span>Fecha</span>
                            <span class="is-right">Importe</span>
                        </div>

                        <ul class="transaction-list">
                        <?php foreach ($ultimasTransacciones as $transaccion): ?>
                            <?php $claseFila = (strtolower($transaccion['tipo_movimiento']) === 'gasto') ? 'dashboard-tx-row--gasto' : 'dashboard-tx-row--ingreso'; ?>
                            <li class="<?= $claseFila ?>">
                                <span class="tx-cell tx-cell--categoria"><?= htmlspecialchars($transaccion['nombre_categoria']) ?></span>
                                <span class="tx-cell"><?= htmlspecialchars($transaccion['nombre_subcategoria']) ?></span>
                                <span class="tx-cell tx-cell--concepto"><?= htmlspecialchars($transaccion['concepto']) ?></span>
                                <span class="tx-cell"><?= date('d/m/Y', strtotime($transaccion['fecha_movimiento'])) ?></span>
                                <span class="tx-cell tx-cell--importe is-right"><?= number_format($transaccion['importe'], 2) ?> €</span>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p>No se han registrado transacciones aún.</p>
                <?php endif; ?>
            </section>

            <div class="dashboard-col">
                <section class="dashboard-card">
                    <!-- Fila superior: Objetivos actuales -->
                    <h2>Objetivos actuales</h2>
                    <p class="muted">Cantidad ahorrada | Cantidad restante | % alcanzado</p>
                </section>

                <section class="dashboard-card">
                    <!-- Fila inferior: Gráficos de progreso -->
                    <h2>Últimos informes</h2>
                    <p class="muted">Título | Fecha | Enlace de descarga</p>
                </section>
            </div>
        </div>
    </div>

<?php 
    require_once './../app/views/layout/footer.php';
?>