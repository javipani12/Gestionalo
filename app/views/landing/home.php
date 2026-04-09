<?php
  require_once './../app/views/layout/header_landing_register_login.php';
?>
  <div class="split split--landing">
    <!-- Zona de presentación -->
    <aside class="left left--landing">
      <img src="./img/gestionalo.png" alt="Gestionalo" class="logo">
      <p class="lead">Organiza tus finanzas y transacciones en un solo lugar. Rápido, sencillo y seguro.</p>
      <p class="muted">Gestiona gastos, ingresos y exporta reportes.</p>
    </aside>

    <!-- Zona de acciones -->
    <main class="right right--landing" aria-live="polite">
        <p class="lead">Accede con tu cuenta o crea una nueva.</p>
        <a class="card cta-box cta-primary js-page-transition" href="?controller=auth&action=mostrarLogin" role="button" aria-label="Iniciar sesión">Iniciar sesión</a>
        <a class="card cta-box cta-primary js-page-transition" href="?controller=auth&action=mostrarRegistro" role="button" aria-label="Registrarse">Registrarse</a>
    </main>
  </div>
</body>
</html>