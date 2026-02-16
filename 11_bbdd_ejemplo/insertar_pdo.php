<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ejemplo";

try {
    // Conexión a la base de datos usando PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si la solicitud es AJAX y si se enviaron los datos necesarios
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['email'], $_POST['telefono'])) {
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];

        // Insertar datos en la tabla
        $sql = "INSERT INTO tabla (nombre, email, telefono) VALUES (:nombre, :email, :telefono)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Datos insertados correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al insertar datos."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Solicitud inválida o datos incompletos."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error de conexión: " . $e->getMessage()]);
}
?>