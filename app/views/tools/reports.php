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
				<?php if (!empty($informes)): ?>
					<div class="transactions-table-wrap">
						<table class="transactions-table" aria-label="Tabla de informes">
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
				<?php else: ?>
					<p class="muted">Todavía no tienes informes guardados. Cuando implementemos la generación de PDF, aparecerán aquí automáticamente.</p>
				<?php endif; ?>
			</section>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
