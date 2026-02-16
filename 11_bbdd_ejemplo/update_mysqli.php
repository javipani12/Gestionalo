<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "ejemplo";
$password = "ejemplo";
$dbname = "ejemplo";

// Conexión a la base de datos
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Verificar conexión
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Verificar si la solicitud es AJAX y si se enviaron los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['email'], $_POST['telefono'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
    $id = intval($_POST['id']); // Asegurarse de que el ID es un entero

    // Insertar datos en la tabla
    $sql = "UPDATE tabla SET nombre = '$nombre', email = '$email', telefono = '$telefono' WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true, "message" => "Datos actualizados correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar datos: " . mysqli_error($conn)]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Solicitud inválida o datos incompletos."]);
}

// Cerrar conexión
mysqli_close($conn);
?>