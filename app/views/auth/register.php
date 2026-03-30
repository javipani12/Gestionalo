<?php
  $titulo = "Gestionalo - Nuevo usuario";
  require_once './../app/views/layout/header_landing_register_login.php';
?>
  <div class="register-page">
    <section class="card">
      <h1>Registrar nuevo usuario</h1>

      <!-- Mostramos errores si los hay -->
      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert error">
          <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
      <?php unset($_SESSION['error']); endif; ?>
      <!-- Fin de errores -->

      <!-- Formulario de registro dividido en pasos -->
      <form id="formulario-registro" action="?controller=auth&action=enviarRegistro" method="post" novalidate>

        <!-- Barra de progreso -->
        <div class="progress" aria-hidden="true">
          <div class="progress-track">
            <div id="progress-fill" class="progress-fill"></div>
          </div>
          <div class="progress-steps">
            <span class="progress-step active" data-progress-step="0">1</span>
            <span class="progress-step" data-progress-step="1">2</span>
            <span class="progress-step" data-progress-step="2">3</span>
          </div>
        </div>
        <!-- Fin de barra de progreso -->

        <div class="steps-wrap">

          <!-- Paso 1: nombre y apellidos -->
          <div class="step" data-step="0">
            <h3>Cuéntanos sobre ti, ¿cómo te llamas?</h3>
            <label for="nombre">Nombre:</label>
            <input name="nombre" required type="text" placeholder="Alfonso">

            <label for="apellido1">Primer apellido:</label>
            <input name="apellido1" required type="text" placeholder="García">

            <label for="apellido2">Segundo apellido:</label>
            <input name="apellido2" type="text" placeholder="López">
          </div>
          <!-- Fin paso 1 -->

          <!-- Paso 2: localidad y fecha de nacimiento -->
          <div class="step" data-step="1">
            <h3>¡Háblanos más sobre ti!</h3>
            <label for="localidad">Localidad:</label>
            <input name="localidad" type="text" placeholder="Sevilla">

            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input name="fecha_nacimiento" type="date" placeholder="AAAA-MM-DD">
          </div>
          <!-- Fin paso 2 -->

          <!-- Paso 3: email y contraseña + checkboxes -->
          <div class="step" data-step="2">
            <h3>Por último, introduce tus datos de acceso</h3>
            <label for="correo">Correo electrónico:</label>
            <input name="correo" required type="email" placeholder="usuario@ejemplo.com">

            <label for="contrasena">Contraseña:</label>
            <input name="contrasena" required type="password" placeholder="••••••••">

            <label for="contrasena2">Confirmar contraseña:</label>
            <input name="contrasena2" required type="password" placeholder="••••••••">

            <div class="checkboxes">
              <label>
                <input name="privacidad" type="checkbox" value="1" required>
                Acepto la política de privacidad
              </label>
              
              <label>
                <input name="consentimiento" type="checkbox" value="1" required>
                Consiento el tratamiento de mis datos para la creación de mi cuenta y el envío de correos relacionados con el servicio.
              </label>
            </div>
          </div>
          <!-- Fin paso 3 -->

        </div>

        <!-- Navegación entre pasos -->
        <div class="form-nav">
          <button id="btn-volver" type="button" class="btn btn-volver js-page-transition" aria-label="Volver a inicio" data-url="?controller=home&action=mostrarHome">Volver a inicio</button>
          <button id="btn-anterior" type="button" class="btn btn-anterior" aria-label="Anterior">Anterior</button>
          <button id="btn-siguiente" type="button" class="btn btn-siguiente" aria-label="Siguiente">Siguiente</button>
          <button id="submit" type="submit" class="btn btn-enviar">Crear cuenta</button>
        </div>
        <!-- Fin de navegación -->

      </form>
      <!-- Fin del formulario de registro -->
      <p class="login-link">¿Ya tienes una cuenta? <a class="js-page-transition" href="?controller=auth&action=mostrarLogin">Inicia sesión aquí</a>.</p>
    </section>

    <script src="./js/register.js"></script>
  </div>
</body>
</html>
