<?php
	require_once './../app/views/layout/header_admin.php';
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
				<h1>Gestión de usuarios</h1>
			</div>

			<div class="transactions-table-wrap">
				<?php
					$hayFiltroCorreo = !empty($filtroCorreo ?? '');
					$queryBase = [
						'controller' => 'admin',
						'action' => 'mostrarGestionUsuarios',
						'correo' => trim($filtroCorreo ?? '')
					];

					$buildGestionUsuariosUrl = function($extra = []) use ($queryBase) {
						$params = array_merge($queryBase, $extra);

						if (empty($params['correo'])) {
							unset($params['correo']);
						}

						return 'index.php?' . http_build_query($params);
					};
				?>
				<div class="transactions-summary-bar">
					<div class="transactions-summary-head">
						<p class="transactions-summary-text">
							<?php if ($hayFiltroCorreo): ?>
								Total de usuarios con coincidencias: <?= (int)($totalUsuarios ?? count($usuarios ?? [])) ?>
							<?php else: ?>
								Total de usuarios activos: <?= (int)($totalUsuarios ?? count($usuarios ?? [])) ?>
							<?php endif; ?>
						</p>
					</div>

					<form class="transaction-search transaction-search--compact" action="index.php" method="get">
						<input type="hidden" name="controller" value="admin" />
						<input type="hidden" name="action" value="mostrarGestionUsuarios" />
						<input type="hidden" name="pagina" value="1" />

						<input
							class="transaction-search__input"
							type="text"
							name="correo"
							placeholder="Buscar por correo..."
							value="<?= htmlspecialchars($filtroCorreo ?? '') ?>"
						/>

						<button class="btn transactions-action-btn transaction-search__button" type="submit">Buscar</button>
						<button
							class="btn transactions-action-btn transaction-search__reset"
							type="button"
							onclick="window.location.href='index.php?controller=admin&action=mostrarGestionUsuarios'"
						>
							Limpiar
						</button>
					</form>
				</div>

				<?php if (!empty($usuarios)): ?>
					<table class="transactions-table">
						<thead>
							<tr>
								<th>Nombre y Apellidos</th>
								<th>Correo</th>
								<th>Localidad</th>
								<th>Fecha nacimiento</th>
								<th>Rol</th>
								<th class="transactions-actions-col">Acciones</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($usuarios as $usuario): ?>
								<tr>
									<td><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido1'] ?? '') . ' ' . ($usuario['apellido2'] ?? ''))) ?></td>
									<td><?= htmlspecialchars($usuario['email'] ?? '') ?></td>
									<td><?= htmlspecialchars($usuario['localidad'] ?? '-') ?></td>
									<td>
										<?= !empty($usuario['fecha_nacimiento']) ? date('d/m/Y', strtotime($usuario['fecha_nacimiento'])) : '-' ?>
									</td>
									<td><?= htmlspecialchars(ucfirst($usuario['rol'] ?? '')) ?></td>
									<td>
										<div class="transactions-actions">
											<a
												href="<?= htmlspecialchars($buildGestionUsuariosUrl([
													'action' => 'mostrarEditarUsuario',
													'id_usuario' => (int)($usuario['id_usuario'] ?? 0),
													'pagina' => $paginaActual ?? 1,
													'return_to' => 'gestionUsuarios'
												])) ?>"
												class="tx-icon-btn tx-icon-btn--edit"
												aria-label="Editar usuario"
												title="Editar usuario"
											>
												<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
													<path d="M3 17.25V21h3.75l11-11-3.75-3.75-11 11Zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0l-1.96 1.96 3.75 3.75 2.13-1.79Z"/>
												</svg>
											</a>

											<?php if ((int)($usuario['id_usuario'] ?? 0) !== (int)($_SESSION['usuario']['id_usuario'] ?? 0)): ?>
												<a
													href="<?= htmlspecialchars($buildGestionUsuariosUrl([
														'action' => 'eliminarUsuario',
														'id_usuario' => (int)($usuario['id_usuario'] ?? 0),
														'pagina' => $paginaActual ?? 1
													])) ?>"
													class="tx-icon-btn tx-icon-btn--delete"
													aria-label="Eliminar usuario"
													title="Eliminar usuario"
													onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?');"
												>
													<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
														<path d="M6 7h12v2H6V7Zm2 3h8l-.7 9.2c-.04.46-.42.8-.88.8H9.58c-.46 0-.84-.34-.88-.8L8 10Zm3-5h2c.55 0 1 .45 1 1v1h-4V6c0-.55.45-1 1-1Z"/>
													</svg>
												</a>
											<?php endif; ?>
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
						<nav class="transactions-pagination" aria-label="Paginación de usuarios">
							<?php if (($paginaActual ?? 1) > 1): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGestionUsuariosUrl(['pagina' => ($paginaActual - 1)])) ?>">Anterior</a>
							<?php else: ?>
								<span class="transactions-pagination__link transactions-pagination__link--disabled">Anterior</span>
							<?php endif; ?>

							<?php if ($inicioPaginas > 1): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGestionUsuariosUrl(['pagina' => 1])) ?>">1</a>
								<?php if ($inicioPaginas > 2): ?>
									<span class="transactions-pagination__ellipsis">...</span>
								<?php endif; ?>
							<?php endif; ?>

							<?php for ($pagina = $inicioPaginas; $pagina <= $finPaginas; $pagina++): ?>
								<?php if ($pagina == ($paginaActual ?? 1)): ?>
									<span class="transactions-pagination__link transactions-pagination__link--active"><?= $pagina ?></span>
								<?php else: ?>
									<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGestionUsuariosUrl(['pagina' => $pagina])) ?>"><?= $pagina ?></a>
								<?php endif; ?>
							<?php endfor; ?>

							<?php if ($finPaginas < $totalPaginas): ?>
								<?php if ($finPaginas < $totalPaginas - 1): ?>
									<span class="transactions-pagination__ellipsis">...</span>
								<?php endif; ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGestionUsuariosUrl(['pagina' => $totalPaginas])) ?>"><?= $totalPaginas ?></a>
							<?php endif; ?>

							<?php if (($paginaActual ?? 1) < $totalPaginas): ?>
								<a class="transactions-pagination__link" href="<?= htmlspecialchars($buildGestionUsuariosUrl(['pagina' => ($paginaActual + 1)])) ?>">Siguiente</a>
							<?php else: ?>
								<span class="transactions-pagination__link transactions-pagination__link--disabled">Siguiente</span>
							<?php endif; ?>
						</nav>
					<?php endif; ?>
				<?php else: ?>
					<div class="transactions-summary-bar">
						<p class="muted">No hay usuarios disponibles actualmente.</p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
