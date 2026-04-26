<?php
	require_once './../app/views/layout/header_admin.php';
?>

	<div class="dashboard-page">
		<section class="dashboard-card dashboard-card--main">
			<div class="transactions-toolbar">
				<h1>Editar consulta</h1>
				<a class="btn transactions-action-btn" href="?controller=admin&action=mostrarConsultasAdmin">Volver al listado</a>
			</div>

			<div class="profile-form-card">
				<form action="?controller=admin&action=actualizarConsultaAdmin" method="post" novalidate>
					<input type="hidden" name="id_consulta" value="<?= (int)($consulta['id_consulta'] ?? 0) ?>">

					<div class="profile-grid">
						<div class="profile-field">
							<label>Asunto</label>
							<input type="text" value="<?= htmlspecialchars($consulta['asunto'] ?? '') ?>" disabled>
						</div>

						<div class="profile-field">
							<label>Usuario</label>
							<input type="text" value="<?= htmlspecialchars($consulta['email_usuario'] ?? '') ?>" disabled>
						</div>

						<div class="profile-field profile-field--full">
							<label>Comentario del usuario</label>
							<textarea rows="4" disabled><?= htmlspecialchars($consulta['comentario'] ?? '') ?></textarea>
						</div>

						<div class="profile-field profile-field--full">
							<label for="respuesta">Comentario del admin</label>
							<textarea id="respuesta" name="respuesta" rows="5" placeholder="Escribe aquí la respuesta para el usuario..."><?= htmlspecialchars($consulta['respuesta'] ?? '') ?></textarea>
						</div>

						<div class="profile-field">
							<label for="id_estado">Estado</label>
							<select id="id_estado" name="id_estado" required>
								<option value="">Selecciona un estado</option>
								<?php foreach ($estadosConsulta as $estado): ?>
									<option value="<?= (int)$estado['id'] ?>" <?= ((int)($consulta['id_estado'] ?? 0) === (int)$estado['id']) ? 'selected' : '' ?>>
										<?= htmlspecialchars($estado['nombre']) ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="profile-field">
							<label>Fecha de creación</label>
							<input type="text" value="<?= !empty($consulta['fecha_creacion']) ? htmlspecialchars(date('d/m/Y H:i', strtotime($consulta['fecha_creacion']))) : '-' ?>" disabled>
						</div>
					</div>

					<div class="form-nav profile-actions">
						<a class="btn btn-volver" href="?controller=admin&action=mostrarConsultasAdmin">Cancelar</a>
						<button type="submit" class="btn btn-enviar">Guardar cambios</button>
					</div>
				</form>
			</div>
		</section>
	</div>

<?php
	require_once './../app/views/layout/footer.php';
?>
