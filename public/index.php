<?php
  $titulo = "Gestionalo — Organiza tus finanzas personales";
  require_once __DIR__ . '/header_landing_register_login.php';
?>
  <div class="split">
    <!-- Zona de presentación -->
    <aside class="left">
      <img src="./assets/img/gestionalo.png" alt="Gestionalo" class="logo">
      <p class="lead">Organiza tus finanzas y transacciones en un solo lugar. Rápido, sencillo y seguro.</p>
      <p class="muted">Gestiona gastos, ingresos y exporta reportes.</p>
    </aside>

    <!-- Zona de acciones -->
    <main class="right" aria-live="polite">
        <p class="lead">Accede con tu cuenta o crea una nueva.</p>
        <a class="card cta-box cta-primary" href="login.php" role="button" aria-label="Iniciar sesión">Iniciar sesión</a>
        <a class="card cta-box cta-primary" href="register.php" role="button" aria-label="Registrarse">Registrarse</a>
    </main>
  </div>
</body>
</html>