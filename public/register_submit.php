<?php
// Modo debug temporal para capturar errores y devolver JSON en lugar de HTML
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/conexion.php';

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
    http_response_code(500);
    $msg = ['success' => false, 'error' => 'Server error', 'details' => $e->getMessage()];
    error_log('register_submit exception: ' . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine());
    echo json_encode($msg);
    exit;
});

// Recoger y validar inputs
$nombre = trim($_POST['nombre'] ?? '');
$apellido1 = trim($_POST['apellido1'] ?? '');
$apellido2 = trim($_POST['apellido2'] ?? null);
$localidad = trim($_POST['localidad'] ?? null);
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? null);
$correo = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$contrasena2 = $_POST['contrasena2'] ?? '';
$privacidad = isset($_POST['privacidad']);
$consentimiento = isset($_POST['consentimiento']);

if ($nombre === '' || $apellido1 === '' || $correo === '' || $contrasena === '' || $contrasena2 === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
    exit;
}

if (!$privacidad || !$consentimiento) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Debe aceptar la política y el consentimiento de datos']);
    exit;
}

if ($contrasena !== $contrasena2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($contrasena) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

try {
    // Comprobar email único
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email');
    $stmt->execute(['email' => $correo]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => 'El correo ya está registrado']);
        exit;
    }

    $pdo->beginTransaction();

    $sql = 'INSERT INTO usuarios (nombre, apellido1, apellido2, email, localidad, fecha_nacimiento, politica_privacidad, consentimiento_datos) VALUES (:nombre, :apellido1, :apellido2, :email, :localidad, :fecha_nacimiento, :politica_privacidad, :consentimiento_datos)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre' => $nombre,
        'apellido1' => $apellido1,
        'apellido2' => $apellido2 ?: null,
        'email' => $correo,
        'localidad' => $localidad ?: null,
        'fecha_nacimiento' => $fecha_nacimiento ?: null,
        'politica_privacidad' => $privacidad ? 1 : 0,
        'consentimiento_datos' => $consentimiento ? 1 : 0,
    ]);

    $idUsuario = (int)$pdo->lastInsertId();

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO contrasenas (id_usuario, hash, actual) VALUES (:id_usuario, :hash, 1)');
    $stmt->execute(['id_usuario' => $idUsuario, 'hash' => $hash]);

    $pdo->commit();
    // Generar token de verificación y almacenarlo
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare('UPDATE usuarios SET verification_token = :token, token_created = NOW() WHERE id_usuario = :id');
    $stmt->execute(['token' => $token, 'id' => $idUsuario]);

    // Enviar correo de verificación
    try {
        require_once __DIR__ . '/../enviar_email/clave.php';
        require_once __DIR__ . '/../enviar_email/correo.php';

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $verifyUrl = $protocol . '://' . $host . $basePath . '/verify.php?token=' . $token;
        $nombre_completo = $nombre . ' ' . $apellido1 . ' ' . $apellido2;
        $asunto = 'Verifica tu cuenta en Gestionalo';
        $mensaje = "<p>Hola " . htmlspecialchars($nombre_completo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ",</p>" .
                   "<p>Gracias por registrarte en Gestionalo. Para activar tu cuenta, pulsa el siguiente enlace:</p>" .
                   "<p><a href=\"$verifyUrl\">Verificar mi correo</a></p>" .
                   "<p>El enlace expirará en 48 horas.</p>";

        enviarCorreo($correo, $nombre_completo, $asunto, $mensaje);
    } catch (Exception $e) {
        // No bloquear el registro si falla el envío; sólo dejar registro del error
        error_log('Error enviando correo verificación: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Se ha creado la cuenta. Se ha enviado un correo para verificarla.']);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al crear el usuario']);
    exit;
}
