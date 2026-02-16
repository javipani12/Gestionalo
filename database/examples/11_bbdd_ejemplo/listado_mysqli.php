<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'ejemplo';
$username = 'ejemplo';
$password = 'ejemplo';

// Conexión a la base de datos
$conn = mysqli_connect($host, $username, $password, $dbname);

// Verificar la conexión
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar con la base de datos: ' . mysqli_connect_error()]);
    exit;
}

// Consulta para obtener todos los registros de la tabla
$query = "SELECT * FROM tabla";
$result = mysqli_query($conn, $query);

if ($result) {
    // Obtener los resultados como un arreglo asociativo
    // echar un vistazo al documento .odt explicativo
    
    $resultados = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $resultados[] = $row;
    }

    // Devolver los datos en formato JSON
    header('Content-Type: application/json');
    echo json_encode($resultados);
} else {
    // Manejo de errores en la consulta
    http_response_code(500);
    echo json_encode(['error' => 'Error al realizar la consulta: ' . mysqli_error($conn)]);
}

// Cerrar la conexión
mysqli_close($conn);
?>