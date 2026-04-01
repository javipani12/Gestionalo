<?php
    require_once './../app/views/layout/header.php';
?>

    <div class="dashboard-page profile-page">
        <div class="transactions-toolbar">
            <h1>Mi perfil</h1>
            <button type="button" class="btn" id="profile-edit-btn">Editar</button>
        </div>

        <?php if(isset($_SESSION['correcto'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['correcto']) ?></div>
        <?php unset($_SESSION['correcto']); endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); endif; ?>

        <section class="dashboard-card dashboard-card--main profile-card">
            <form action="?controller=profile&action=actualizarPerfil" method="post" id="profile-form" novalidate>
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
                    </div>

                    <div class="profile-field profile-field--full">
                        <label for="passwd">Nueva contraseña</label>
                        <input id="passwd" name="passwd" type="password" placeholder="Introduce la nueva contraseña" disabled required>
                        <p class="muted">Solo se actualizará la contraseña si introduces una distinta a la actual.</p>
                    </div>
                </div>

                <div class="form-nav profile-actions" id="profile-actions" hidden>
                    <button type="button" class="btn btn-volver" id="profile-cancel-btn">Cancelar</button>
                    <button type="submit" class="btn btn-enviar">Guardar cambios</button>
                </div>
            </form>
        </section>
    </div>

<?php
    require_once './../app/views/layout/footer.php';
?>

<script src="./js/profile.js" defer></script>