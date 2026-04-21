<?php
	require_once './../app/views/layout/header.php';
?>

	<div class="dashboard-page mortgage-page">
		<section class="dashboard-card dashboard-card--main mortgage-shell" aria-labelledby="mortgage-title">
			<div class="tools-hero mortgage-hero">
				<h1 id="mortgage-title">Calculadora de hipoteca</h1>
				<p class="dashboard-lead">Simula tu hipoteca con distintos escenarios y analiza cuota, intereses, esfuerzo y amortización.</p>
			</div>

			<div class="mortgage-layout">
				<section class="dashboard-card mortgage-form-card" aria-labelledby="mortgage-form-title">
					<h2 id="mortgage-form-title">Formulario completo</h2>

					<form id="mortgage-form" class="mortgage-form" novalidate>
						<fieldset class="mortgage-block">
							<legend>Vivienda</legend>
							<div class="mortgage-grid mortgage-grid--three">
								<div class="mortgage-field">
									<label for="precioVivienda" title="Precio total de compra de la vivienda.">Precio</label>
									<input type="number" id="precioVivienda" name="precioVivienda" min="0" step="1000" value="220000" required>
								</div>
								<div class="mortgage-field">
									<label for="entradaInicial" title="Cantidad que aportas con tus ahorros y no financias con la hipoteca.">Entrada</label>
									<input type="number" id="entradaInicial" name="entradaInicial" min="0" step="1000" value="40000" required>
								</div>
								<div class="mortgage-field">
									<label for="gastosCompra" title="Costes iniciales de la compra, como notaría, tasación, gestoría o impuestos.">Gastos</label>
									<input type="number" id="gastosCompra" name="gastosCompra" min="0" step="100" value="20000" required>
								</div>
							</div>
						</fieldset>

						<fieldset class="mortgage-block">
							<legend>Hipoteca</legend>
							<div class="mortgage-grid mortgage-grid--three">
								<div class="mortgage-field">
									<label for="plazoAnos" title="Número de años en los que devolverás el préstamo.">Plazo (años)</label>
									<input type="number" id="plazoAnos" name="plazoAnos" min="1" max="45" step="1" value="30" required>
								</div>
								<div class="mortgage-field">
									<label for="interesAnual" title="Porcentaje de interés anual que aplica el banco sobre el capital prestado.">Interés nominal anual (TIN) (%)</label>
									<input type="number" id="interesAnual" name="interesAnual" min="0" step="0.01" value="3.2" required>
								</div>
								<div class="mortgage-field">
									<label for="tipoHipoteca" title="Modalidad de la hipoteca: fija, variable o mixta.">Tipo de interés</label>
									<select id="tipoHipoteca" name="tipoHipoteca">
										<option value="fija" selected>Fija</option>
										<option value="variable">Variable</option>
										<option value="mixta">Mixta</option>
									</select>
								</div>
							</div>
						</fieldset>

						<fieldset class="mortgage-block">
							<legend>Perfil</legend>
							<div class="mortgage-grid mortgage-grid--two">
								<div class="mortgage-field">
									<label for="ingresosMensuales" title="Dinero que ingresas cada mes después de impuestos.">Ingresos mensuales netos</label>
									<input type="number" id="ingresosMensuales" name="ingresosMensuales" min="0" step="10" value="2600" required>
								</div>
								<div class="mortgage-field">
									<label for="deudasMensuales" title="Pagos fijos mensuales que ya tienes, como préstamos o financiación.">Deudas mensuales</label>
									<input type="number" id="deudasMensuales" name="deudasMensuales" min="0" step="10" value="180" required>
								</div>
							</div>
						</fieldset>

						<fieldset class="mortgage-block">
							<legend>Amortización anticipada</legend>
							<div class="mortgage-grid mortgage-grid--three">
								<div class="mortgage-field">
									<label for="extraMensual" title="Cantidad adicional que quieres pagar cada mes para reducir deuda antes de tiempo.">Aporte extra mensual</label>
									<input type="number" id="extraMensual" name="extraMensual" min="0" step="10" value="0">
								</div>
								<div class="mortgage-field">
									<label for="pagoUnico" title="Ingreso puntual que quieres destinar a amortizar parte de la hipoteca.">Pago único extraordinario</label>
									<input type="number" id="pagoUnico" name="pagoUnico" min="0" step="100" value="0">
								</div>
								<div class="mortgage-field">
									<label for="mesPagoUnico" title="Mes en el que se aplicará ese pago extraordinario.">Mes del pago único</label>
									<input type="number" id="mesPagoUnico" name="mesPagoUnico" min="1" step="1" value="24">
								</div>
							</div>
						</fieldset>

						<fieldset class="mortgage-block">
							<legend>Comparador de escenarios</legend>
							<div class="mortgage-grid mortgage-grid--three">
								<div class="mortgage-field">
									<label for="variacionInteresBaja" title="Variación del interés para simular un escenario más favorable.">Interés escenario bajo (%)</label>
									<input type="number" id="variacionInteresBaja" name="variacionInteresBaja" step="0.01" value="-0.5">
								</div>
								<div class="mortgage-field">
									<label for="variacionInteresAlta" title="Variación del interés para simular un escenario menos favorable.">Interés escenario alto (%)</label>
									<input type="number" id="variacionInteresAlta" name="variacionInteresAlta" step="0.01" value="0.75">
								</div>
								<div class="mortgage-field">
									<label for="variacionPlazo" title="Cambio en la duración de la hipoteca para comparar como afecta a la cuota e intereses.">Variación plazo (años)</label>
									<input type="number" id="variacionPlazo" name="variacionPlazo" step="1" value="-5">
								</div>
							</div>
						</fieldset>

						<div class="mortgage-actions">
							<button type="submit" class="btn transactions-action-btn">Calcular hipoteca</button>
							<button type="button" id="mortgage-reset" class="btn transactions-action-btn">Restablecer</button>
						</div>
					</form>
				</section>

				<section class="dashboard-card mortgage-results-card" aria-labelledby="mortgage-results-title">
					<h2 id="mortgage-results-title">Resultados</h2>

					<div class="mortgage-kpi">
						<p class="mortgage-kpi__label">Cuota mensual</p>
						<p id="resultadoCuota" class="mortgage-kpi__value">-</p>
						<p id="resultadoCapital" class="muted">Capital financiado: -</p>
					</div>

					<div class="mortgage-summary-grid">
                        <article class="mortgage-summary-card">
							<h3>Total intereses</h3>
							<p id="resultadoIntereses">-</p>
						</article>
                        <article class="mortgage-summary-card">
							<h3>Aportación inicial</h3>
                            <span class="muted">Entrada + Gastos</span>
							<p id="resultadoAportacionInicial">-</p>
						</article>
						<article class="mortgage-summary-card">
							<h3>Total pagado</h3>
                            <span class="muted">Intereses + Capital financiado</span>
							<p id="resultadoPagado">-</p>
						</article>
                        <article class="mortgage-summary-card">
							<h3>Coste total de la operación</h3>
                            <span class="muted">Aportación inicial + Intereses + Capital financiado</span>
							<p id="resultadoCosteOperacion">-</p>
						</article>
						<article class="mortgage-summary-card">
							<h3>Ratio de esfuerzo</h3>
							<p id="resultadoRatio" class="mortgage-ratio">-</p>
						</article>
						<article class="mortgage-summary-card">
							<h3>Plazo estimado</h3>
							<p id="resultadoPlazoFinal">-</p>
						</article>
						<article class="mortgage-summary-card">
							<h3>Ahorro en intereses</h3>
							<p id="resultadoAhorroIntereses">-</p>
						</article>
						<article class="mortgage-summary-card">
							<h3>Reducción de plazo</h3>
							<p id="resultadoAhorroPlazo">-</p>
						</article>
					</div>
				</section>
			</div>

			<section class="dashboard-card mortgage-analysis-card" aria-labelledby="mortgage-analysis-title">
				<h2 id="mortgage-analysis-title">Análisis detallado</h2>

				<div class="mortgage-analysis-grid">
					<section class="mortgage-section" aria-labelledby="chart-title">
						<h3 id="chart-title">Gráfico</h3>
						<canvas id="mortgageChart" width="760" height="300" aria-label="Gráfico de amortización" role="img"></canvas>
						<div class="mortgage-chart-legend" aria-label="Leyenda del gráfico">
							<span class="mortgage-chart-legend__item">
								<span class="mortgage-chart-legend__dot mortgage-chart-legend__dot--capital" aria-hidden="true"></span>
								Capital pendiente
							</span>
							<span class="mortgage-chart-legend__item">
								<span class="mortgage-chart-legend__dot mortgage-chart-legend__dot--interes" aria-hidden="true"></span>
								Interés acumulado
							</span>
						</div>
						<p class="muted">Evolución anual de capital pendiente e intereses acumulados.</p>
					</section>

					<section class="mortgage-section" aria-labelledby="scenario-title">
						<h3 id="scenario-title">Comparador de escenarios</h3>
						<div class="transactions-table-wrap">
							<table class="transactions-table mortgage-table mortgage-table--scenario" id="tablaEscenarios">
								<thead>
									<tr>
										<th>Escenario</th>
										<th>Interés</th>
										<th>Plazo</th>
										<th>Cuota</th>
										<th>Intereses</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</section>
				</div>

				<section class="mortgage-section" aria-labelledby="amort-title">
					<h3 id="amort-title">Cuadro de amortización</h3>
					<div class="transactions-table-wrap">
						<table class="transactions-table mortgage-table" id="tablaAmortizacion">
							<thead>
								<tr>
									<th>Mes</th>
									<th>Cuota</th>
									<th>Interés</th>
									<th>Capital</th>
									<th>Extra</th>
									<th>Saldo</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div class="transactions-pagination mortgage-amort-pagination" aria-label="Paginación del cuadro de amortización">
						<button type="button" id="amort-prev" class="transactions-pagination__link">Anterior</button>
						<span id="amort-page-info" class="transactions-pagination__ellipsis">Año 1 de 1</span>
						<button type="button" id="amort-next" class="transactions-pagination__link">Siguiente</button>
					</div>
				</section>
			</section>
		</section>
	</div>

	<script src="./js/mortgage.js" defer></script>

<?php
	require_once './../app/views/layout/footer.php';
?>