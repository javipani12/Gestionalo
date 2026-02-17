<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrarse — Gestionalo</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="register-page">
    <img src="./assets/img/gestionalo.png" alt="Gestionalo" class="logo">
    <h1>Registrar nuevo usuario</h1>

    <section class="card" style="max-width:520px;">
      <form id="register-form" action="#" method="post" novalidate>
        <!-- Paso 1: datos de cuenta -->
        <div class="step active" data-step="0">
          <label>Nombre completo: <input name="name" required type="text" placeholder="Tu nombre"></label>
          <label>Email: <input name="email" required type="email" placeholder="usuario@ejemplo.com"></label>
        </div>

        <!-- Paso 2: contraseña -->
        <div class="step" data-step="1">
          <label>Contraseña: <input name="password" required type="password" placeholder="••••••••"></label>
          <label>Confirmar contraseña: <input name="password2" required type="password" placeholder="••••••••"></label>
        </div>

        <!-- Paso 3: información adicional -->
        <div class="step" data-step="2">
          <label>Alias interno (opcional): <input name="alias" type="text" placeholder="Ej: MiEmpresa"></label>
        </div>

        <div class="form-nav">
          <button type="button" class="btn btn-back" aria-label="Volver">Volver</button>
          <button type="button" class="btn btn-next" aria-label="Siguiente">Siguiente</button>
          <button type="submit" class="btn btn-submit" style="display:none">Crear cuenta</button>
        </div>
      </form>
      <div id="reg-result" class="result" style="display:none"></div>
    </section>

    <script src="js/register.js"></script>
  </div>
</body>
</html>
