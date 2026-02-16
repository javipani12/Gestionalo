<?php
// Minimal transactions API: GET list, POST insert, DELETE remove
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List transactions for a user (minimal). Usage: ?user_id=1
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    if ($userId <= 0) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM transacciones WHERE id_usuario = ? ORDER BY fecha_movimiento DESC LIMIT 100');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();
    echo json_encode($rows);
    exit;
}

if ($method === 'POST') {
    // Accept JSON or form-encoded
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = $_POST;
    }

    $required = ['id_usuario', 'id_tipo', 'importe', 'fecha_movimiento'];
    foreach ($required as $r) {
        if (!isset($input[$r]) || $input[$r] === '') {
            http_response_code(400);
            echo json_encode(['error' => "Missing field: $r"]);
            exit;
        }
    }

    $fields = [
        'id_usuario','id_categoria','id_subcategoria','id_tipo','concepto','fecha_movimiento','id_metodo','importe','comentario'
    ];

    $placeholders = [];
    $params = [];
    foreach ($fields as $f) {
        if (isset($input[$f]) && $input[$f] !== '') {
            $placeholders[] = $f;
            $params[$f] = $input[$f];
        }
    }

    if (empty($params)) {
        http_response_code(400);
        echo json_encode(['error' => 'No data to insert']);
        exit;
    }

    $cols = implode(',', array_keys($params));
    $binds = ':' . implode(',:', array_keys($params));

    $sql = "INSERT INTO transacciones ($cols) VALUES ($binds)";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        // basic typing: if importe => float
        if ($k === 'importe') {
            $stmt->bindValue(':' . $k, (float)$v);
        } else {
            $stmt->bindValue(':' . $k, $v);
        }
    }
    try {
        $stmt->execute();
        $id = (int)$pdo->lastInsertId();
        echo json_encode(['ok' => true, 'id' => $id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Insert failed']);
    }
    exit;
}

if ($method === 'DELETE') {
    // Delete by id: use query param id
    parse_str(file_get_contents('php://input'), $delParams);
    $id = isset($delParams['id']) ? (int)$delParams['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing id']);
        exit;
    }
    $stmt = $pdo->prepare('DELETE FROM transacciones WHERE id_transaccion = ?');
    try {
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Delete failed']);
    }
    exit;
}

// Fallback
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
