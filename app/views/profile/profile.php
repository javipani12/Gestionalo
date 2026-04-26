<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page profile-page">
        <section class="dashboard-card dashboard-card--main profile-card">
            <div class="transactions-toolbar">
                <h1>Estos son tus datos de perfil <?= htmlspecialchars($_SESSION['usuario']['nombre'] ?? '') ?></h1>
                <button type="button" class="btn" id="profile-edit-btn">Editar</button>
            </div>
            <div class="profile-form-card">
                <form action="?controller=profile&action=actualizarPerfil" method="post" id="profile-form" data-can-edit-email="0" novalidate>
                    <div class="profile-grid">
                        <div class="profile-field">
                            <label for="nombre">Nombre</label>
                            <input id="nombre" name="nombre" type="text" value="<?= htmlspecialchars($datosUsuario['nombre'] ?? '') ?>" disabled required>
                        </div>

                        <div class="profile-field">
                            <label for="apellido1">Primer apellido</label>
                            <input id="apellido1" name="apellido1" type="text" value="<?= htmlspecialchars($datosUsuario['apellido1'] ?? '') ?>" disabled required>
                        </div>

                        <div class="profile-field">
                            <label for="apellido2">Segundo apellido</label>
                            <input id="apellido2" name="apellido2" type="text" value="<?= htmlspecialchars($datosUsuario['apellido2'] ?? '') ?>" disabled required>
                        </div>

                        <div class="profile-field">
                            <label for="localidad">Localidad</label>
                            <input id="localidad" name="localidad" type="text" value="<?= htmlspecialchars($datosUsuario['localidad'] ?? '') ?>" disabled required>
                        </div>

                        <div class="profile-field">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" value="<?= !empty($datosUsuario['fecha_nacimiento']) ? htmlspecialchars(date('Y-m-d', strtotime($datosUsuario['fecha_nacimiento']))) : '' ?>" disabled required>
                        </div>

                        <div class="profile-field">
                            <label for="email">Correo electrónico</label>
                            <input id="email" name="email" type="email" value="<?= htmlspecialchars($datosUsuario['email'] ?? '') ?>" disabled>
                            <p class="muted">Para cambiar el correo electrónico, abre una nueva consulta <a class="link" href="?controller=contact&action=mostrarCrearConsulta">aquí</a>.</p>
                        </div>

                        <div class="profile-field profile-field--full">
                            <label for="passwd">Nueva contraseña</label>
                            <input id="passwd" name="passwd" type="password" minlength="8" placeholder="Introduce la nueva contraseña" disabled required>
                            <p class="muted">Solo se actualizará la contraseña si introduces una distinta a la actual.</p>
                        </div>
                    </div>

                </form>
                </div>
                <div class="form-nav profile-actions" id="profile-actions" hidden>
                    <form action="?controller=profile&action=eliminarCuenta" method="post" class="profile-delete"
                        onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');">
                        <button type="submit" class="btn btn-danger">Eliminar cuenta</button>
                    </form>
                    <button type="submit" form="profile-form" class="btn btn-enviar" id="profile-save-btn" disabled>Guardar cambios</button>
                </div>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>

<script src="./js/profile.js" defer></script>