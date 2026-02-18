<?php
  $titulo = "Gestionalo - Nuevo usuario";
  require_once __DIR__ . '/header_landing_register_login.php';
?>
  <div class="register-page">
    <!--<a href="index.php" aria-label="Ir a inicio"><img src="./assets/img/gestionalo.png" alt="Gestionalo" class="logo"></a>-->
    <section class="card">
      <h1>Registrar nuevo usuario</h1>
      <form id="formulario-registro" action="#" method="post" novalidate>
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

          <!-- Paso 2: localidad y fecha de nacimiento -->
          <div class="step" data-step="1">
            <h3>¡Háblanos más sobre ti!</h3>
            <label for="localidad">Localidad:</label>
            <input name="localidad" type="text" placeholder="Sevilla">

            <label for="fecha_nacimiento">Fecha de nacimiento:</label>
            <input name="fecha_nacimiento" type="date" placeholder="AAAA-MM-DD">
          </div>

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
                Acepto la política de privacidad
                <input name="privacidad" type="checkbox" value="1" required>
              </label>
              
              <label>
                Consiento el tratamiento de mis datos
                <input name="consentimiento" type="checkbox" value="1" required>
              </label>
            </div>
          </div>

          <!-- Paso 4: mensaje final -->
          <div class="step active" data-step="3">
            <h3>¡Cuenta creada con éxito! Comprueba tu correo</h3>
            <p id="mensaje-final">Se ha enviado un correo para verificar la cuenta.</p>
            <div style="margin-top:1rem">
              <a class="btn" href="login.php">Ir al login</a>
            </div>
          </div>
        </div>

        <div class="form-nav">
          <button type="button" class="btn btn-volver" aria-label="Volver a inicio">Volver a inicio</button>
          <button type="button" class="btn btn-siguiente" aria-label="Siguiente">Siguiente</button>
          <button type="submit" class="btn btn-enviar" style="display:none">Crear cuenta</button>
        </div>
      </form>
    </section>

    <script src="js/register.js"></script>
  </div>
</body>
</html>
