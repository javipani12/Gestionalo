<?php
// Export transactions for a user as CSV or JSON
require_once __DIR__ . '/../src/db.php';

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'csv';
if ($userId <= 0) {
    http_response_code(400);
    echo 'Missing user_id';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM transacciones WHERE id_usuario = ? ORDER BY fecha_movimiento DESC');
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rows);
    exit;
}

// CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="transacciones_user_' . $userId . '.csv"');
$out = fopen('php://output', 'w');
if ($out === false) {
    http_response_code(500);
    exit;
}
// header row
if (!empty($rows)) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $r) {
        fputcsv($out, $r);
    }
}
fclose($out);
