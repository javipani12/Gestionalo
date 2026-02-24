<?php
  $titulo = 'Gestionalo — Verificar correo';
  require_once __DIR__ . '/header_landing_register_login.php';

  $token = $_GET['token'] ?? '';
  $mensaje = '';

  if ($token === '') {
    $mensaje = '<p>Token no proporcionado.</p>';
  } else {
    try {
      $cfg = include __DIR__ . '/../config/config.php';
      $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset={$cfg['charset']}";
      $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);

      $stmt = $pdo->prepare('SELECT id_usuario, token_created, email_verificado FROM usuarios WHERE verification_token = :token');
      $stmt->execute(['token' => $token]);
      $row = $stmt->fetch();

      if (!$row) {
        $mensaje = '<p>Token inválido o ya utilizado.</p>';
      } elseif ($row['email_verificado']) {
        $mensaje = '<p>La cuenta ya está verificada. Puedes iniciar sesión.</p>';
      } else {
        // Comprobar expiración: 48 horas
        $created = new DateTime($row['token_created']);
        $now = new DateTime();
        $interval = $now->getTimestamp() - $created->getTimestamp();
        if ($interval > 48 * 3600) {
          $mensaje = '<p>El enlace de verificación ha expirado. Solicita uno nuevo desde la página de inicio de sesión.</p>';
        } else {
          $upd = $pdo->prepare('UPDATE usuarios SET email_verificado = 1, verification_token = NULL, token_created = NULL WHERE id_usuario = :id');
          $upd->execute(['id' => $row['id_usuario']]);
          $mensaje = '<p>Correo verificado con éxito. Ya puedes iniciar sesión.</p>';
        }
      }
    } catch (Exception $e) {
      $mensaje = '<p>Error al verificar la cuenta. Inténtalo más tarde.</p>';
      error_log('verify.php error: ' . $e->getMessage());
    }
  }
?>

  <div class="register-page">
    <section class="card">
      <h1>Verificación de correo</h1>
      <div>
        <?php echo $mensaje; ?>
      </div>
      <div style="margin-top:1rem">
        <a class="btn" href="login.php">Ir al login</a>
      </div>
    </section>
  </div>
</body>
</html>
