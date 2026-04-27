<?php
    ini_set('display_errors',0);
    error_reporting(E_ALL);
    session_start();

    require_once "./../vendor/autoload.php";

    $controllerParam = strtolower($_GET['controller'] ?? 'home');
    $action = strtolower($_GET['action'] ?? 'mostrarHome');

    // Permitir acceso público a auth y legal (registro/login y textos legales).
    if(!isset($_SESSION['usuario']['id_usuario']) && !in_array($controllerParam, ['auth', 'legal'], true)) {
        $controllerParam = 'home';
        $action = 'mostrarHome';
    }

    $controllerClass = ucfirst($controllerParam) . "Controller";

    try {
        if(class_exists($controllerClass)) {
            $controller = new $controllerClass();
            if(method_exists($controller, $action)) {
                $controller->$action();
            } else {
                error_log("Acción no encontrada: " . $action);
                $_SESSION['error'] = 'La acción solicitada no está disponible.';
                header('Location: index.php?controller=home&action=mostrarHome');
                exit();
            }
        } else {
            error_log("Controlador no encontrado: " . $controllerParam);
            $_SESSION['error'] = 'La sección solicitada no está disponible.';
            header('Location: index.php?controller=home&action=mostrarHome');
            exit();
        }
    } catch (Throwable $e) {
        error_log('Error no controlado en index.php: ' . $e->getMessage());
        $_SESSION['error'] = 'Ha ocurrido un error interno. Inténtalo de nuevo en unos minutos.';
        header('Location: index.php?controller=home&action=mostrarHome');
        exit();
    }
?>