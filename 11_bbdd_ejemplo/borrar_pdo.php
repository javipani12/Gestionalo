<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$database = "ejemplo";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    // Configurar el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si se recibió el parámetro 'id' a través de POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = intval($_POST['id']); // Sanitizar el parámetro

        // Consulta para borrar el registro
        $sql = "DELETE FROM tabla WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Registro eliminado correctamente.";
        } else {
            echo "Error al eliminar el registro.";
        }
    } else {
        echo "No se proporcionó un ID válido.";
    }
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>