<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page tools-page graphics-page">
        <section class="dashboard-card dashboard-card--main graphics-shell" aria-labelledby="graphics-title">
            <div class="tools-hero graphics-hero">
                <h1 id="graphics-title">Gráficos financieros</h1>
                <p class="dashboard-lead">Filtra tus movimientos y actualiza los gráficos al instante.</p>
                <div class="graphics-actions">
                    <a class="btn transactions-action-btn" href="?controller=tool&action=mostrarHerramientas">Volver a herramientas</a>
                </div>
            </div>

            <section class="dashboard-card graphics-filters" aria-label="Filtros de gráficos">
                <form class="transaction-search transaction-search--graphics" action="#" method="get" onsubmit="return false;">
                    <select class="transaction-search__select" id="graphics-tipo">
                        <option value="">Tipo</option>
                        <?php foreach (($datosGraficos['catalogos']['tipos_movimiento'] ?? []) as $tipo): ?>
                            <?php if (in_array((string)($tipo['nombre'] ?? ''), ['Transferencia Interna Aporte', 'Transferencia Interna Retiro'], true)) { continue; } ?>
                            <option value="<?= (int)($tipo['id'] ?? 0) ?>"><?= htmlspecialchars(ucfirst((string)($tipo['nombre'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="transaction-search__select" id="graphics-categoria">
                        <option value="">Categoría</option>
                        <?php foreach (($datosGraficos['catalogos']['categorias'] ?? []) as $categoria): ?>
                            <option value="<?= (int)($categoria['id'] ?? 0) ?>"><?= htmlspecialchars((string)($categoria['nombre'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="transaction-search__select" id="graphics-subcategoria">
                        <option value="">Subcategoría</option>
                        <?php foreach (($datosGraficos['catalogos']['subcategorias'] ?? []) as $subcategoria): ?>
                            <option
                                value="<?= (int)($subcategoria['id'] ?? 0) ?>"
                                data-categoria-id="<?= (int)($subcategoria['id_categoria'] ?? 0) ?>"
                            >
                                <?= htmlspecialchars((string)($subcategoria['nombre'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <fieldset class="transaction-search__date-group">
                        <legend>Desde</legend>
                        <input class="transaction-search__date" type="date" id="graphics-fecha-desde" aria-label="Fecha desde">
                    </fieldset>

                    <fieldset class="transaction-search__date-group">
                        <legend>Hasta</legend>
                        <input class="transaction-search__date" type="date" id="graphics-fecha-hasta" aria-label="Fecha hasta">
                    </fieldset>

                    <select class="transaction-search__select" id="graphics-metodo">
                        <option value="">Método</option>
                        <?php foreach (($datosGraficos['catalogos']['metodos_pago'] ?? []) as $metodo): ?>
                            <option value="<?= (int)($metodo['id'] ?? 0) ?>"><?= htmlspecialchars(ucfirst((string)($metodo['nombre'] ?? ''))) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="button" class="btn transactions-action-btn transaction-search__reset" id="graphics-limpiar">Limpiar</button>
                </form>
            </section>

            <section class="graphics-grid" aria-label="Panel de gráficos">
                <article class="dashboard-card graphics-kpis-card" aria-label="Resumen">
                    <h2>Resumen del periodo</h2>
                    <div class="graphics-kpis">
                        <article class="graphics-kpi-card graphics-kpi-card--ingreso">
                            <p class="graphics-kpi-card__label">Ingresos</p>
                            <p class="graphics-kpi-card__value" id="graphics-kpi-ingresos">0,00 EUR</p>
                        </article>
                        <article class="graphics-kpi-card graphics-kpi-card--gasto">
                            <p class="graphics-kpi-card__label">Gastos</p>
                            <p class="graphics-kpi-card__value" id="graphics-kpi-gastos">0,00 EUR</p>
                        </article>
                        <article class="graphics-kpi-card graphics-kpi-card--balance">
                            <p class="graphics-kpi-card__label">Balance</p>
                            <p class="graphics-kpi-card__value" id="graphics-kpi-balance">0,00 EUR</p>
                        </article>
                    </div>
                </article>

                <article class="dashboard-card graphics-chart-card graphics-chart-card--donut">
                    <h2>Ingresos vs gastos (periodo filtrado)</h2>
                    <div id="graphics-balance-donut" class="graphics-chart" aria-label="Gráfico de balance"></div>
                </article>

                

                <article class="dashboard-card graphics-chart-card graphics-chart-card--wide">
                    <h2>Evolución mensual</h2>
                    <div id="graphics-evolution" class="graphics-chart" aria-label="Gráfico de evolución"></div>
                </article>

                <article class="dashboard-card graphics-chart-card graphics-chart-card--wide" id="graphics-goals-card">
                    <h2>Evolución de objetivos</h2>
                    <div id="graphics-goals-evolution" class="graphics-chart" aria-label="Gráfico de evolución de objetivos"></div>
                </article>

                <article class="dashboard-card graphics-chart-card graphics-chart-card--wide" id="graphics-income-card">
                    <h2>Top categorías / subcategorías de ingreso</h2>
                    <div id="graphics-income-categories" class="graphics-chart" aria-label="Gráfico de categorías de ingreso"></div>
                </article>

                <article class="dashboard-card graphics-chart-card graphics-chart-card--wide" id="graphics-expenses-card">
                    <h2>Top categorías / subcategorías de gasto</h2>
                    <div id="graphics-categories" class="graphics-chart" aria-label="Gráfico de categorías"></div>
                </article>
            </section>


        </section>
    </div>

    <div id="graphics-data" data-payload='<?= htmlspecialchars($datosGraficosJson, ENT_QUOTES, 'UTF-8') ?>' hidden></div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="./js/graphics.js" defer></script>

<?php
    require_once './../app/views/layout/footer.php';
?>