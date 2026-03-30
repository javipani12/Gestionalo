<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);
    session_start();

    require_once "./../vendor/autoload.php";

    $controllerParam = strtolower($_GET['controller'] ?? 'home');
    $action = strtolower($_GET['action'] ?? 'mostrarHome');

    // Si no está autenticado y no pide 'auth', forzamos la landing pública
    if(!isset($_SESSION['usuario']['id_usuario']) && $controllerParam !== 'auth') {
        $controllerParam = 'home';
        $action = 'mostrarHome';
    }

    $controllerClass = ucfirst($controllerParam) . "Controller";

    if(class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if(method_exists($controller, $action)) {
            $controller->$action();
        } else {
            error_log("Acción no encontrada: " . $action);
            die("Acción no encontrada: " . htmlspecialchars($action));
        }
    } else {
        error_log("Controlador no encontrado: " . $controllerParam);
        die("Controlador no encontrado: " . htmlspecialchars($controllerParam));
    }
?>