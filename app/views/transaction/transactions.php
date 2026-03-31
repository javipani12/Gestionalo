<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">
        <div class="transactions-toolbar">
            <h1>Estas son tus transacciones <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
            <a class="btn btn-enviar" href="index.php?controller=transaction&action=mostrarFormularioCrearTransaccion">
                Nueva transacción
            </a>
        </div>

        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['correcto']) ?></div>
        <?php unset($_SESSION['correcto']); endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); endif; ?>

        <section class="dashboard-card dashboard-card--main">
            <!-- Listado con todas transacciones -->
            <?php if (!empty($transacciones)): ?>
                <div class="transactions-table-wrap">
                    <table class="transactions-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Subcategoría</th>
                                <th>Concepto</th>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th class="is-right">Importe</th>
                                <th class="transactions-actions-col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transacciones as $transaccion): ?>
                                <?php
                                    $esIngreso = strtolower($transaccion['tipo_movimiento']) === 'ingreso';
                                    $claseTipo = $esIngreso ? 'tx-type tx-type--ingreso' : 'tx-type tx-type--gasto';
                                    $claseImporte = $esIngreso ? 'tx-amount tx-amount--ingreso' : 'tx-amount tx-amount--gasto';
                                ?>
                                <tr>
                                    <td><span class="<?= $claseTipo ?>"><?= htmlspecialchars(ucfirst($transaccion['tipo_movimiento'])) ?></span></td>
                                    <td><?= htmlspecialchars($transaccion['nombre_categoria']) ?></td>
                                    <td><?= htmlspecialchars($transaccion['nombre_subcategoria']) ?></td>
                                    <td class="tx-concepto"><?= htmlspecialchars($transaccion['concepto']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($transaccion['fecha_movimiento'])) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($transaccion['metodo_pago'])) ?></td>
                                    <td class="is-right"><span class="<?= $claseImporte ?>"><?= number_format($transaccion['importe'], 2) ?> €</span></td>
                                    <td>
                                        <div class="transactions-actions">
                                            <a
                                                href="index.php?controller=transaction&action=mostrarFormularioEditarTransaccion&id_transaccion=<?= (int)$transaccion['id_transaccion'] ?>"
                                                class="tx-icon-btn tx-icon-btn--edit"
                                                aria-label="Editar transacción"
                                                title="Editar transacción"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
                                                </svg>
                                            </a>

                                            <a
                                                href="index.php?controller=transaction&action=eliminarTransaccion&id_transaccion=<?= (int)$transaccion['id_transaccion'] ?>"
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
            <?php else: ?>
                <p class="muted">No se han registrado transacciones aún.</p>
            <?php endif; ?>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>