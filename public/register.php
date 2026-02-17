<?php
  $titulo = "Gestionalo - Nuevo usuario";
  require_once __DIR__ . '/header_landing_register_login.php';
?>
  <div class="register-page">
    <a href="index.php" aria-label="Ir a inicio"><img src="./assets/img/gestionalo.png" alt="Gestionalo" class="logo"></a>
    <section class="card">
      <h1>Registrar nuevo usuario</h1>
      <form id="formulario-registro" action="#" method="post" novalidate>
        <div class="steps-wrap">
        <!-- Paso 1: datos de cuenta -->
        <div class="step active" data-step="0">
          <h3>Empecemos por ti, ¿cómo te llamas?</h3>
          <label for="nombre">Nombre completo:</label>
          <input name="nombre" required type="text" placeholder="Tu nombre">
          <label for="correo">Correo electrónico:</label>
          <input name="correo" required type="email" placeholder="usuario@ejemplo.com">
        </div>

        <!-- Paso 2: contraseña -->
        <div class="step" data-step="1">
          <h3>Ahora la contraseña — que sea segura</h3>
          <label for="contrasena">Contraseña:</label>
          <input name="contrasena" required type="password" placeholder="••••••••">
          <label for="contrasena2">Confirmar contraseña:</label>
          <input name="contrasena2" required type="password" placeholder="••••••••">
        </div>

        <!-- Paso 3: información adicional -->
        <div class="step" data-step="2">
          <h3>Un último detalle: ¿tienes un alias?</h3>
          <label for="alias">Alias interno (opcional):</label>
          <input name="alias" type="text" placeholder="Ej: MiEmpresa">
        </div>
        </div>

        <div class="form-nav">
          <button type="button" class="btn btn-volver" aria-label="Volver">Volver</button>
          <button type="button" class="btn btn-siguiente" aria-label="Siguiente">Siguiente</button>
          <button type="submit" class="btn btn-enviar" style="display:none">Crear cuenta</button>
        </div>
      </form>
    </section>

    <script src="js/register.js"></script>
  </div>
</body>
</html>
