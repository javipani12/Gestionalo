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

		<?php
			$modoEdicion = (bool)($modoEdicion ?? false);
			$tituloFormulario = $modoEdicion
				? 'Editar elemento de ' . ($definicionTabla['label'] ?? 'tabla maestra')
				: 'Nuevo elemento de ' . ($definicionTabla['label'] ?? 'tabla maestra');
		?>

		<section class="dashboard-card dashboard-card--main">
			<div class="transactions-toolbar">
				<h1><?= htmlspecialchars($tituloFormulario) ?></h1>
				<a class="btn transactions-action-btn" href="<?= htmlspecialchars($urlVolver ?? '?controller=admin&action=mostrarGestionTablasMaestras') ?>">Volver al listado</a>
			</div>

			<div class="profile-form-card">
				<form action="?controller=admin&action=guardarTablaMaestra" method="post" novalidate>
					<input type="hidden" name="tabla" value="<?= htmlspecialchars($tabla ?? '') ?>">
					<input type="hidden" name="id" value="<?= (int)($registro['id'] ?? 0) ?>">
					<input type="hidden" name="buscar" value="<?= htmlspecialchars($buscar ?? '') ?>">
					<input type="hidden" name="pagina" value="<?= (int)($pagina ?? 1) ?>">

					<div class="profile-grid">
						<?php if (($tabla ?? '') === 'subcategorias'): ?>
							<div class="profile-field">
								<label for="id_categoria">Categoría padre</label>
								<select id="id_categoria" name="id_categoria" required>
									<option value="">Selecciona una categoría</option>
									<?php foreach (($categorias ?? []) as $categoria): ?>
										<option
											value="<?= (int)($categoria['id'] ?? 0) ?>"
											<?= ((int)($registro['id_categoria'] ?? 0) === (int)($categoria['id'] ?? 0)) ? 'selected' : '' ?>
										>
											<?= htmlspecialchars($categoria['nombre'] ?? '') ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endif; ?>

						<div class="profile-field <?= (($tabla ?? '') === 'subcategorias') ? '' : 'profile-field--full' ?>">
							<label for="nombre">Nombre</label>
							<input
								id="nombre"
								name="nombre"
								type="text"
								maxlength="120"
								value="<?= htmlspecialchars($registro['nombre'] ?? '') ?>"
								placeholder="Introduce el nombre"
								required
							>
						</div>
					</div>

					<div class="form-nav profile-actions">
						<a class="btn" href="<?= htmlspecialchars($urlVolver ?? '?controller=admin&action=mostrarGestionTablasMaestras') ?>">Cancelar</a>
						<button type="submit" class="btn btn-enviar"><?= $modoEdicion ? 'Guardar cambios' : 'Crear elemento' ?></button>
					</div>
				</form>
			</div>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
