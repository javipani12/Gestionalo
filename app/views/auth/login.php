<?php
  $titulo = "Gestionalo — Iniciar sesión";
  require_once './../app/views/layout/header_landing_register_login.php';
?>
  <div class="register-page">
    <section class="card">
      <h1>Iniciar sesión</h1>

      <!-- Mostrar mensajes de éxito o error -->
      <?php if(isset($_SESSION['correcto'])): ?>
        <div class="alert success">
          <?= htmlspecialchars($_SESSION['correcto']) ?>
        </div>
      <?php 
        unset($_SESSION['correcto']);
        endif;
      ?>

      <?php if(isset($_SESSION['error'])): ?>
        <div class="alert error">
          <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
      <?php 
        unset($_SESSION['error']); 
        endif; 
      ?>
      <!-- Fin mensajes -->

      <!-- Formulario de login -->
      <form action="?controller=auth&action=enviarLogin" method="post" novalidate>
        <label for="email">Correo electrónico:</label>
        <input id="email" name="email" required type="email" placeholder="usuario@ejemplo.com">

        <label for="passwd">Contraseña:</label>
        <input id="passwd" name="passwd" required type="password" placeholder="••••••••">

        <div class="form-nav">
          <button type="button" class="btn btn-volver js-page-transition" data-url="?controller=home&action=mostrarHome">Volver a inicio</button>
          <button type="submit" class="btn btn-siguiente">Acceder</button>
        </div>
      </form>
      <!-- Fin formulario -->

      <p class="login-link">¿No tienes cuenta? <a class="js-page-transition" href="?controller=auth&action=mostrarRegistro">Regístrate aquí</a>.</p>
    </section>

  </div>
  <script src="./js/alerts.js" defer></script>
</body>
</html>