<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/index.css" type="text/css">
    <title>Gestionalo</title>
</head>
<body>
    <?php 
        require_once("config.php");
        require_once("DBConnection.php");
        require_once("User.php");

        $hoy = date('Y-m-d');
	    $hora = date('H:i');

        // Comprobamos si se ha enviado el formulario
	    if( isset ( $_POST['submit'] ) ) {
		    // Convertimos la fecha y hora del form a formato americano que el que admite MySQL
            $myDate = new DateTime( $hoy . " " . $hora );
		    $fecha_registro = $myDate->format( 'Y-m-d H:i' );

            if( empty( $_POST[ 'email' ] ) 
                || empty( $_POST[ 'contrasenna' ] ) 
                || empty( $_POST[ 'nombre' ] ) 
                || empty( $_POST[ 'apellido1' ] ) 
                || empty( $_POST[ 'apellido2' ] ) 
                || empty( $_POST[ 'localidad' ] )
                || empty( $_POST[ 'fecha_nacimiento' ] )
                || empty( $_POST[ 'edad' ] )   
            ){
                $error = 2;
                $errormsg = "Hay campos obligatorios que están vacíos";
            } else {
                $my_user = new User($_POST);
                $dbc = new DBConnection($dbsettings);
                if( !$dbc ){
					// Si falla error
					$error = 2;
					$errormsg = "Error connecting DataBase...";
				} else {
					$my_fields = $my_user->getFields();
					$sql = "SELECT * FROM usuarios WHERE
                           `nombre` = " . $my_fields['nombre'] . "
                            AND `apellido1` = " . $my_fields['apellido1'] ."
                            AND `apellido2` = " . $my_fields['apellido2']."
                            AND `email` = " . $my_fields['email'];

                    $resulset = $dbc->getQuery($sql);
					if( $resulset->rowCount() == 0 ) {
                        $sql = "INSERT INTO usuarios VALUES ( NULL, " . 
                                implode( ",", $my_user->getFields() ) . " NULL, NULL, NULL)";
                        $numTuplas = $dbc->runQuery($sql);
                        
                        if( $numTuplas == 1 ) {
                            $error = 0;
                            $errormsg = "Se ha registrado con éxito tu récord";
                        } else {
                            $error = 2;
                            $errormsg = "Ha habido un error al registrar tu récord";
                        }
                    }
                }
            }
        }
    ?>
    <div id="contenedor_sesion">
        <form id="inicio_sesion" method="post">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email">
            <br><br>
            <label for="contrasenna">Contraseña</label>
            <input type="password" id="contrasenna" name="contrasenna">
            <br><br>
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre">
            <br><br>
            <label for="apellido1">Primer apellido</label>
            <input type="text" id="apellido1" name="apellido1">
            <br><br>
            <label for="apellido2">Segundo apellido</label>
            <input type="text" id="apellido2" name="apellido2">
            <br><br>
            <label for="localidad">Localidad</label>
            <input type="text" id="localidad" name="localidad">
            <br><br>
            <label for="fecha_nacimiento">Fecha nacimiento</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
            <br><br>
            <label for="edad">Edad</label>
            <input type="number" id="edad" name="edad">
            <br><br>
            <input type="button" value="Registrarse">
        </form>
    </div>
</body>
</html>