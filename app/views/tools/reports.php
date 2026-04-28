<?php
	require_once './../app/views/layout/header.php';
?>

	<div class="dashboard-page tools-page reports-page">
		<section class="dashboard-card dashboard-card--main reports-shell" aria-labelledby="reports-title">
			<div class="tools-hero graphics-hero reports-hero">
				<h1 id="reports-title">Informes generados</h1>
				<p class="dashboard-lead">Consulta y descarga los informes PDF guardados en tu cuenta.</p>
				<div class="graphics-actions">
					<a class="btn transactions-action-btn" href="?controller=tool&action=mostrarHerramientas">Volver a herramientas</a>
				</div>
			</div>

			<section class="dashboard-card" aria-label="Listado de informes">
				<?php
					$buildReportsUrl = function($extra = []) {
						return 'index.php?' . http_build_query(array_merge([
							'controller' => 'report',
							'action' => 'mostrarInformesGenerados'
						], $extra));
					};
				?>
				<?php if (!empty($informes)): ?>
					<div class="transactions-table-wrap">
						<table class="transactions-table reports-table" aria-label="Tabla de informes">
							<thead>
								<tr>
									<th>Nombre</th>
									<th>Tipo</th>
									<th>Fecha de generación</th>
									<th>Archivo</th>
									<th class="is-right">Acciones</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($informes as $informe): ?>
									<?php
										$nombreInforme = trim((string)($informe['nombre_informe'] ?? ''));
										$tipoInforme = trim((string)($informe['tipo_informe'] ?? 'General'));
										$fechaGeneracionRaw = (string)($informe['fecha_generacion'] ?? '');
										$fechaGeneracion = '-';

										if ($fechaGeneracionRaw !== '') {
											$timestamp = strtotime($fechaGeneracionRaw);
											if ($timestamp !== false) {
												$fechaGeneracion = date('d/m/Y H:i', $timestamp);
											}
										}

										if ($nombreInforme === '') {
											$nombreInforme = 'Informe #' . (int)($informe['id_informe'] ?? 0);
										}

										$rutaArchivo = trim((string)($informe['ruta_archivo'] ?? ''));
										$archivoBase = $rutaArchivo !== '' ? basename($rutaArchivo) : '-';
									?>
									<tr>
										<td><?= htmlspecialchars($nombreInforme) ?></td>
										<td><?= htmlspecialchars(ucfirst($tipoInforme)) ?></td>
										<td><?= htmlspecialchars($fechaGeneracion) ?></td>
										<td><?= htmlspecialchars($archivoBase) ?></td>
										<td class="is-right">
											<a
												class="btn transactions-action-btn"
												href="?controller=report&action=descargarInforme&id_informe=<?= (int)($informe['id_informe'] ?? 0) ?>"
											>
												Descargar PDF
											</a>
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
						?>
						<nav class="transactions-pagination" aria-label="Paginación de informes">
							<?php if ($paginaActual > 1): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildReportsUrl(['pagina' => $paginaActual - 1])) ?>">Anterior</a>
							<?php else: ?>
								<span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
							<?php endif; ?>

							<?php if ($inicioPaginas > 1): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildReportsUrl(['pagina' => 1])) ?>">1</a>
								<?php if ($inicioPaginas > 2): ?>
									<span class="transactions-pagination__ellipsis">...</span>
								<?php endif; ?>
							<?php endif; ?>

							<?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
								<?php if ($pagina == $paginaActual): ?>
									<span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
								<?php else: ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildReportsUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
								<?php endif; ?>
							<?php endfor; ?>

							<?php if ($finPaginas < $totalPaginas): ?>
								<?php if ($finPaginas < $totalPaginas - 1): ?>
									<span class="transactions-pagination__ellipsis">...</span>
								<?php endif; ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildReportsUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
							<?php endif; ?>

							<?php if ($paginaActual < $totalPaginas): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildReportsUrl(['pagina' => $paginaActual + 1])) ?>">Siguiente</a>
							<?php else: ?>
								<span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
							<?php endif; ?>
						</nav>
					<?php endif; ?>
				<?php else: ?>
					<p class="muted">Todavía no tienes informes guardados. Cuando implementemos la generación de PDF, aparecerán aquí automáticamente.</p>
				<?php endif; ?>
			</section>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
