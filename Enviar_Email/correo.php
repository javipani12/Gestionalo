<?php

//////////Cambiar los siguientes para cada proyecto ///////////
define('ROOT', $_SERVER['DOCUMENT_ROOT'] . "/huerto/");

///////////CORREO/////////////////
define("EMAIL_HOST", 'smtp.office365.com');         //SMTP server para enviar
define("EMAIL_USER", 'agora@iesagora.es');          //SMTP Usuario
define("EMAIL_PASS", 'clave');                      //SMTP password

// Cargar clases del PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require ROOT . 'PHPMailer/src/Exception.php';
require ROOT . 'PHPMailer/src/PHPMailer.php';
require ROOT . 'PHPMailer/src/SMTP.php';

function enviarCorreo($dest, $nombre, $asunto, $mensaje, $adjunto = "")
{

	$mail = new PHPMailer(true);
	try {
		//Server settings
		$mail->CharSet = "UTF-8";
		$mail->SMTPDebug = SMTP::DEBUG_OFF;            //Enable verbose debug output
		$mail->isSMTP();                               //Send using SMTP
		$mail->Host       = EMAIL_HOST;                //Set the SMTP server to send through
		$mail->SMTPAuth   = true;                      //Enable SMTP authentication
		$mail->Username   = EMAIL_USER;                //SMTP username
		$mail->Password   = EMAIL_PASS;                //SMTP password
		//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
		$mail->Port       = 587;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		//TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
		//if ($ical) $mail->Ical = $ical;
		//if ($ical) $mail->addStringAttachment($ical, "anotacion.ics");
		//Recipients
		$mail->setFrom('agora@iesagora.es', 'El Huerto del IES Ágora');
		$mail->addAddress($dest, $nombre);     //Add a recipien
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');

		//Attachments
		if (!empty($adjunto)) {
			$mail->addAttachment($adjunto);         //Add attachments
			//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
		}
		$mail->AddEmbeddedImage(ROOT . "imagenes/logoAgorap.png", 'imagen'); //ruta de archivo de imagen
		//Content
		$mail->isHTML(true);
		//Set email format to HTML
		//cargar archivo css para cuerpo de mensaje
		$rcss = ROOT . "css/estilo_correo.css"; //ruta de archivo css
		$fcss = fopen($rcss, "r"); //abrir archivo css
		$scss = fread($fcss, filesize($rcss)); //leer contenido de css
		fclose($fcss); //cerrar archivo css
		//Cargar archivo html   
		$shtml = file_get_contents(ROOT . "css/correo.html");
		//reemplazar sección de plantilla html con el css cargado y mensaje creado
		$incss  = str_replace('<style id="estilo"></style>', "<style>$scss</style>", $shtml);
		$cuerpo = str_replace('<div id="mensaje"></div>', $mensaje, $incss);
		$mail->Subject = $asunto;
		$mail->Body    = $cuerpo;

		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		$mail->send();
		//echo 'Message has been sent';
	} catch (Exception $e) {
		error_log("El mensaje no se ha podido enviar. Error: {$mail->ErrorInfo}");
	}
}
