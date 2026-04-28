<!doctype html>
<html lang="es">
<head>
    <?php $assetVersion = @filemtime(dirname(__DIR__, 3) . '/public/css/style.css') ?: time(); ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $titulo ?></title>
    <link rel="stylesheet" href="./css/style.css?v=<?= $assetVersion ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon-16x16.png?v=2">
  <link rel="shortcut icon" href="./img/favicon.ico?v=2">
  <link rel="apple-touch-icon" href="./img/apple-touch-icon.png?v=2">
  <meta name="theme-color" content="#0b5cff">
    <script src="./js/page-transition.js?v=<?= $assetVersion ?>" defer></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <ul class="navbar__menu">
                <li><a href="?controller=admin&action=mostrarDashboardAdmin" class="navbar__link">Inicio</a></li>
                <li><a href="?controller=admin&action=mostrarGestionUsuarios" class="navbar__link">Gestión Usuarios</a></li>
                <li><a href="?controller=admin&action=mostrarGestionTablasMaestras" class="navbar__link">Gestión Tablas Maestras</a></li>
                <li><a href="?controller=admin&action=mostrarConsultasAdmin" class="navbar__link">Gestión Consultas</a></li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="?controller=admin&action=mostrarPerfilAdmin" class="navbar__link navbar__link--dropdown" aria-haspopup="true">
                        Perfil
                    </a>
                    <ul class="navbar__dropdown" aria-label="Submenú de transacciones">
                        <li>
                            <a href="?controller=admin&action=mostrarPerfilAdmin" class="navbar__dropdown-link">
                                Mi Perfil
                            </a>
                        </li>
                        <li>
                            <a href="?controller=legal&action=descargarManualAdmin" class="navbar__dropdown-link">
                                Descargar Manual de Administrador
                            </a>
                        </li>
                        <li>
                            <a href="?controller=auth&action=cerrarSesion" class="navbar__dropdown-link">
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <?php if(isset($_SESSION['correcto'])): ?>
			<div class="alert success"><?= htmlspecialchars($_SESSION['correcto']) ?></div>
		<?php unset($_SESSION['correcto']); endif; ?>

		<?php if(isset($_SESSION['error'])): ?>
			<div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
		<?php unset($_SESSION['error']); endif; ?>