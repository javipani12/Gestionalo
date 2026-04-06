<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page">

        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['correcto']) ?></div>
        <?php unset($_SESSION['correcto']); endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); endif; ?>

        <section class="dashboard-card dashboard-card--main">
            <div class="transactions-toolbar">
                <h1>Estas son tus transacciones <?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></h1>
                <button
                    class="btn transactions-action-btn"
                    type="button"
                    onclick="window.location.href='index.php?controller=transaction&action=mostrarFormularioCrearTransaccion'"
                >
                    Nueva transacción
                </button>
            </div>
            <div class="transactions-table-wrap">
                <?php
                    $hayFiltrosActivos =
                        !empty($filtrosActivos['concepto'])
                        || !empty($filtrosActivos['id_tipo'])
                        || !empty($filtrosActivos['id_categoria'])
                        || !empty($filtrosActivos['id_subcategoria'])
                        || !empty($filtrosActivos['fecha_desde'])
                        || !empty($filtrosActivos['fecha_hasta'])
                        || !empty($filtrosActivos['id_metodo']);

                    $queryBase = [
                        'controller' => 'transaction',
                        'action' => 'mostrarTransaccionesUsuario',
                        'concepto' => trim($filtrosActivos['concepto'] ?? ''),
                        'id_tipo' => (int)($filtrosActivos['id_tipo'] ?? 0),
                        'id_categoria' => (int)($filtrosActivos['id_categoria'] ?? 0),
                        'id_subcategoria' => (int)($filtrosActivos['id_subcategoria'] ?? 0),
                        'fecha_desde' => trim($filtrosActivos['fecha_desde'] ?? ''),
                        'fecha_hasta' => trim($filtrosActivos['fecha_hasta'] ?? ''),
                        'id_metodo' => (int)($filtrosActivos['id_metodo'] ?? 0),
                        'orden_campo' => $ordenCampo ?? 'fecha',
                        'orden_direccion' => $ordenDireccion ?? 'desc'
                    ];

                    $buildTransactionsUrl = function($extra = []) use ($queryBase) {
                        $params = array_merge($queryBase, $extra);

                        if(empty($params['concepto'])) {
                            unset($params['concepto']);
                        }

                        if(empty($params['id_tipo'])) {
                            unset($params['id_tipo']);
                        }

                        if(empty($params['id_categoria'])) {
                            unset($params['id_categoria']);
                        }

                        if(empty($params['id_subcategoria'])) {
                            unset($params['id_subcategoria']);
                        }

                        if(empty($params['fecha_desde'])) {
                            unset($params['fecha_desde']);
                        }

                        if(empty($params['fecha_hasta'])) {
                            unset($params['fecha_hasta']);
                        }

                        if(empty($params['id_metodo'])) {
                            unset($params['id_metodo']);
                        }

                        return 'index.php?' . http_build_query($params);
                    };

                    $sortDirectionFor = function($field) use ($ordenCampo, $ordenDireccion) {
                        if($ordenCampo === $field && strtolower($ordenDireccion) === 'asc') {
                            return 'desc';
                        }

                        return 'asc';
                    };

                    $sortIndicatorFor = function($field) use ($ordenCampo, $ordenDireccion) {
                        if($ordenCampo !== $field) {
                            return '';
                        }

                        return strtolower($ordenDireccion) === 'asc' ? ' ▲' : ' ▼';
                    };
                ?>
                <div class="transactions-summary-bar">
                    <div class="transactions-summary-head">
                        <p class="transactions-summary-text">
                            <?php if ($hayFiltrosActivos): ?>
                                Total de transacciones con coincidencias: <?= (int)($totalTransacciones ?? count($transacciones)) ?>
                            <?php else: ?>
                                Total de transacciones: <?= (int)($totalTransacciones ?? count($transacciones)) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <!-- Formulario de filtros -->
                    <form class="transaction-search" action="index.php" method="get">
                        <input type="hidden" name="controller" value="transaction" />
                        <input type="hidden" name="action" value="mostrarTransaccionesUsuario" />
                        <input type="hidden" name="pagina" value="1" />
                        <input type="hidden" name="orden_campo" value="<?= htmlspecialchars($ordenCampo ?? 'fecha') ?>" />
                        <input type="hidden" name="orden_direccion" value="<?= htmlspecialchars($ordenDireccion ?? 'desc') ?>" />

                        <input class="transaction-search__input" type="text" name="concepto" placeholder="Buscar por concepto..." value="<?= htmlspecialchars($filtrosActivos['concepto'] ?? '') ?>" />

                        <select class="transaction-search__select" name="id_tipo">
                            <option value="">Tipo</option>
                            <?php foreach ($tiposMovimiento as $tipo): ?>
                                <option value="<?= (int)$tipo['id'] ?>" <?= ((int)($filtrosActivos['id_tipo'] ?? 0) === (int)$tipo['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($tipo['nombre'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select class="transaction-search__select" name="id_categoria" id="id_categoria">
                            <option value="">Categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= (int)$categoria['id'] ?>" <?= ((int)($filtrosActivos['id_categoria'] ?? 0) === (int)$categoria['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select class="transaction-search__select" name="id_subcategoria" id="id_subcategoria">
                            <option value="">Subcategoría</option>
                            <?php foreach ($subcategorias as $subcategoria): ?>
                                <option
                                    value="<?= (int)$subcategoria['id'] ?>"
                                    data-categoria-id="<?= (int)$subcategoria['id_categoria'] ?>"
                                    <?= ((int)($filtrosActivos['id_subcategoria'] ?? 0) === (int)$subcategoria['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($subcategoria['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <fieldset class="transaction-search__date-group">
                            <legend>Desde</legend>
                            <input
                                id="filtro-fecha-desde"
                                class="transaction-search__date"
                                type="date"
                                name="fecha_desde"
                                value="<?= htmlspecialchars($filtrosActivos['fecha_desde'] ?? '') ?>"
                                aria-label="Fecha desde"
                            />
                        </fieldset>

                        <fieldset class="transaction-search__date-group">
                            <legend>Hasta</legend>
                            <input
                                id="filtro-fecha-hasta"
                                class="transaction-search__date"
                                type="date"
                                name="fecha_hasta"
                                value="<?= htmlspecialchars($filtrosActivos['fecha_hasta'] ?? '') ?>"
                                aria-label="Fecha hasta"
                            />
                        </fieldset>

                        <select class="transaction-search__select" name="id_metodo">
                            <option value="">Método</option>
                            <?php foreach ($metodosPago as $metodo): ?>
                                <option value="<?= (int)$metodo['id'] ?>" <?= ((int)($filtrosActivos['id_metodo'] ?? 0) === (int)$metodo['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($metodo['nombre'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn transactions-action-btn transaction-search__button" type="submit">Buscar</button>
                        <button
                            class="btn transactions-action-btn transaction-search__reset"
                            type="button"
                            onclick="window.location.href='index.php?controller=transaction&action=mostrarTransaccionesUsuario'"
                        >
                            Limpiar
                        </button>
                    </form>
                    <!-- Fin formulario de filtros -->
                </div>

                <!-- Listado con todas transacciones -->
                <?php if (!empty($transacciones)): ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'tipo') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'tipo', 'orden_direccion' => $sortDirectionFor('tipo')])) ?>">Tipo<?= $sortIndicatorFor('tipo') ?></a>
                            </th>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'categoria') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'categoria', 'orden_direccion' => $sortDirectionFor('categoria')])) ?>">Categoría<?= $sortIndicatorFor('categoria') ?></a>
                            </th>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'subcategoria') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'subcategoria', 'orden_direccion' => $sortDirectionFor('subcategoria')])) ?>">Subcategoría<?= $sortIndicatorFor('subcategoria') ?></a>
                            </th>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'concepto') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'concepto', 'orden_direccion' => $sortDirectionFor('concepto')])) ?>">Concepto<?= $sortIndicatorFor('concepto') ?></a>
                            </th>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'fecha') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'fecha', 'orden_direccion' => $sortDirectionFor('fecha')])) ?>">Fecha<?= $sortIndicatorFor('fecha') ?></a>
                            </th>
                            <th>
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'metodo') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'metodo', 'orden_direccion' => $sortDirectionFor('metodo')])) ?>">Método<?= $sortIndicatorFor('metodo') ?></a>
                            </th>
                            <th class="is-right">
                                <a class="transactions-sort-link <?= (($ordenCampo ?? 'fecha') === 'importe') ? 'transactions-sort-link--active' : '' ?>" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1, 'orden_campo' => 'importe', 'orden_direccion' => $sortDirectionFor('importe')])) ?>">Importe<?= $sortIndicatorFor('importe') ?></a>
                            </th>
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
                <!-- Fin listado transacciones -->

                <!-- Paginación -->
                <?php if (($totalPaginas ?? 1) > 1): ?>
                    <?php
                        $inicioPaginas = max(1, $paginaActual - 2);
                        $finPaginas = min($totalPaginas, $paginaActual + 2);
                    ?>
                    <nav class="transactions-pagination" aria-label="Paginación de transacciones">
                        <?php if ($paginaActual > 1): ?>
                            <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => $paginaActual - 1])) ?>">Anterior</a>
                        <?php else: ?>
                            <span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
                        <?php endif; ?>

                        <?php if ($inicioPaginas > 1): ?>
                            <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => 1])) ?>">1</a>
                            <?php if ($inicioPaginas > 2): ?>
                                <span class="transactions-pagination__ellipsis">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
                            <?php if ($pagina == $paginaActual): ?>
                                <span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
                            <?php else: ?>
                                <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($finPaginas < $totalPaginas): ?>
                            <?php if ($finPaginas < $totalPaginas - 1): ?>
                                <span class="transactions-pagination__ellipsis">...</span>
                            <?php endif; ?>
                            <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
                        <?php endif; ?>

                        <?php if ($paginaActual < $totalPaginas): ?>
                            <a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTransactionsUrl(['pagina' => $paginaActual + 1])) ?>">Siguiente</a>
                        <?php else: ?>
                            <span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
                <!-- Fin paginación -->

            </div>
            <?php else: ?>
                <p class="muted">No se han registrado transacciones aún.</p>
            <?php endif; ?>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>
<script src="./js/transaction-form.js" defer></script>