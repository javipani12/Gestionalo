<?php
	require_once './../app/views/layout/header_admin.php';
?>

	<div class="dashboard-page">
		<section class="dashboard-card dashboard-card--main">
			<div class="transactions-toolbar">
				<h1>Gestión de tablas maestras</h1>
			</div>

			<div class="master-tables-grid">
				<?php foreach (($resumenTablas ?? []) as $tablaResumen): ?>
					<?php
						$esActiva = (($tablaActual ?? '') === ($tablaResumen['tabla'] ?? ''));
						$urlGestionTabla = 'index.php?' . http_build_query([
							'controller' => 'admin',
							'action' => 'mostrarGestionTablasMaestras',
							'tabla' => $tablaResumen['tabla'] ?? ''
						]);
					?>
					<article class="master-table-card <?= $esActiva ? 'master-table-card--active' : '' ?>">
						<h3><?= htmlspecialchars($tablaResumen['label'] ?? 'Tabla maestra') ?></h3>
						<p class="master-table-card__meta">
							Registros: <strong><?= (int)($tablaResumen['total'] ?? 0) ?></strong>
						</p>
						<a class="btn transactions-action-btn" href="<?= htmlspecialchars($urlGestionTabla) ?>">Gestionar</a>
					</article>
				<?php endforeach; ?>
			</div>

			<?php if (!empty($tablaActual) && !empty($definicionTablaActual)): ?>
				<?php
					$queryBase = [
						'controller' => 'admin',
						'action' => 'mostrarGestionTablasMaestras',
						'tabla' => $tablaActual,
						'buscar' => trim($buscar ?? '')
					];

					$buildTablaMaestraUrl = function($extra = []) use ($queryBase) {
						$params = array_merge($queryBase, $extra);

						if (empty($params['buscar'])) {
							unset($params['buscar']);
						}

						return 'index.php?' . http_build_query($params);
					};
				?>
				<div class="transactions-table-wrap master-table-list-wrap">
					<div class="transactions-summary-bar">
						<div class="transactions-summary-head">
							<p class="transactions-summary-text">
								<?= htmlspecialchars($definicionTablaActual['label'] ?? 'Tabla maestra') ?>: <?= (int)($totalRegistros ?? count($registros ?? [])) ?> registros
							</p>
						</div>

						<div class="master-table-toolbar-actions">
							<form class="transaction-search transaction-search--compact" action="index.php" method="get">
								<input type="hidden" name="controller" value="admin" />
								<input type="hidden" name="action" value="mostrarGestionTablasMaestras" />
								<input type="hidden" name="tabla" value="<?= htmlspecialchars($tablaActual) ?>" />
								<input type="hidden" name="pagina" value="1" />

								<input
									class="transaction-search__input"
									type="text"
									name="buscar"
									placeholder="Buscar por nombre..."
									value="<?= htmlspecialchars($buscar ?? '') ?>"
								/>

								<button class="btn transactions-action-btn transaction-search__button" type="submit">Buscar</button>
								<button
									class="btn transactions-action-btn transaction-search__reset"
									type="button"
									onclick="window.location.href='<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => 1, 'buscar' => ''])) ?>'"
								>
									Limpiar
								</button>
							</form>

							<a
								class="btn transactions-action-btn"
								href="<?= htmlspecialchars('index.php?' . http_build_query([
									'controller' => 'admin',
									'action' => 'mostrarFormularioTablaMaestra',
									'tabla' => $tablaActual,
									'pagina' => $paginaActual ?? 1,
									'buscar' => $buscar ?? ''
								])) ?>"
							>
								Nuevo elemento
							</a>
						</div>
					</div>

					<?php if (!empty($registros)): ?>
						<table class="transactions-table master-table-records <?= $tablaActual === 'subcategorias' ? 'master-table-records--subcategories' : '' ?>">
							<thead>
								<tr>
									<th>ID</th>
									<th>Nombre</th>
									<?php if ($tablaActual === 'subcategorias'): ?>
										<th>Categoría</th>
									<?php endif; ?>
									<th class="transactions-actions-col">Acciones</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($registros as $registro): ?>
									<tr>
										<td><?= (int)($registro['id'] ?? 0) ?></td>
										<td><?= htmlspecialchars($registro['nombre'] ?? '') ?></td>
										<?php if ($tablaActual === 'subcategorias'): ?>
											<td><?= htmlspecialchars($registro['categoria'] ?? '-') ?></td>
										<?php endif; ?>
										<td>
											<div class="transactions-actions">
												<a
													href="<?= htmlspecialchars('index.php?' . http_build_query([
														'controller' => 'admin',
														'action' => 'mostrarFormularioTablaMaestra',
														'tabla' => $tablaActual,
														'id' => (int)($registro['id'] ?? 0),
														'pagina' => $paginaActual ?? 1,
														'buscar' => $buscar ?? ''
													])) ?>"
													class="tx-icon-btn tx-icon-btn--edit"
													aria-label="Editar registro"
													title="Editar registro"
												>
													<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
														<path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
													</svg>
												</a>

												<a
													href="<?= htmlspecialchars('index.php?' . http_build_query([
														'controller' => 'admin',
														'action' => 'eliminarRegistroTablaMaestra',
														'tabla' => $tablaActual,
														'id' => (int)($registro['id'] ?? 0),
														'pagina' => $paginaActual ?? 1,
														'buscar' => $buscar ?? ''
													])) ?>"
													class="tx-icon-btn tx-icon-btn--delete"
													aria-label="Eliminar registro"
													title="Eliminar registro"
													onclick="return confirm('¿Estás seguro de que quieres eliminar este registro?');"
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

						<?php if (($totalPaginas ?? 1) > 1): ?>
							<?php
								$inicioPaginas = max(1, ($paginaActual ?? 1) - 2);
								$finPaginas = min($totalPaginas, ($paginaActual ?? 1) + 2);
							?>
							<nav class="transactions-pagination" aria-label="Paginación de tabla maestra">
								<?php if (($paginaActual ?? 1) > 1): ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => $paginaActual - 1])) ?>">Anterior</a>
								<?php else: ?>
									<span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
								<?php endif; ?>

								<?php if ($inicioPaginas > 1): ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => 1])) ?>">1</a>
									<?php if ($inicioPaginas > 2): ?>
										<span class="transactions-pagination__ellipsis">...</span>
									<?php endif; ?>
								<?php endif; ?>

								<?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
									<?php if ($pagina == ($paginaActual ?? 1)): ?>
										<span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
									<?php else: ?>
										<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
									<?php endif; ?>
								<?php endfor; ?>

								<?php if ($finPaginas < $totalPaginas): ?>
									<?php if ($finPaginas < $totalPaginas - 1): ?>
										<span class="transactions-pagination__ellipsis">...</span>
									<?php endif; ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
								<?php endif; ?>

								<?php if (($paginaActual ?? 1) < $totalPaginas): ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildTablaMaestraUrl(['pagina' => $paginaActual + 1])) ?>">Siguiente</a>
								<?php else: ?>
									<span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
								<?php endif; ?>
							</nav>
						<?php endif; ?>
					<?php else: ?>
						<div class="transactions-summary-bar">
							<p class="muted">No hay registros para esta tabla maestra.</p>
						</div>
					<?php endif; ?>
				</div>
			<?php else: ?>
				<p class="muted master-table-empty-state">Selecciona una tabla maestra para gestionar su contenido.</p>
			<?php endif; ?>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
