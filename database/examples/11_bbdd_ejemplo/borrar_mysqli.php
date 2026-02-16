<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "ejemplo";
$password = "ejemplo";
$database = "ejemplo";

$conn = mysqli_connect($servername, $username, $password, $database);

// Verificar conexión
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Verificar si se recibió el parámetro 'id' a través de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']); // Sanitizar el parámetro

    // Consulta para borrar el registro
    $sql = "DELETE FROM tabla WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "Registro eliminado correctamente.";
    } else {
        echo "Error al eliminar el registro: " . mysqli_error($conn);
    }
} else {
    echo "No se proporcionó un ID válido.";
}

// Cerrar conexión
mysqli_close($conn);
?>