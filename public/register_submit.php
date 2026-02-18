<?php
header('Content-Type: application/json; charset=utf-8');
try {
    $cfg = include __DIR__ . '/../config/config.php';
    $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset={$cfg['charset']}";
    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

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

    // Aquí podríamos generar y enviar correo de verificación (se implementará más adelante)
    echo json_encode(['success' => true, 'message' => 'Se ha creado la cuenta. Se ha enviado un correo para verificarla (simulado).']);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al crear el usuario']);
    exit;
}
