<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $titulo ?></title>
  <link rel="stylesheet" href="./css/style.css">
  <link rel="icon" type="image/png" sizes="32x32" href="./img/favicon-32x32.png?v=2">
  <link rel="icon" type="image/png" sizes="16x16" href="./img/favicon-16x16.png?v=2">
  <link rel="shortcut icon" href="./img/favicon.ico?v=2">
  <link rel="apple-touch-icon" href="./img/apple-touch-icon.png?v=2">
  <meta name="theme-color" content="#0b5cff">
  <script src="./js/page-transition.js" defer></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <!--
                Inicio
                Transacciones
                Herramientas
                Agente IA
                Contacto (dropdown con opciones: Nueva consulta, Mis consultas)
                Sobre nosotros
                Icono perfil (dropdown con opciones: Ver perfil, Cerrar sesión)
            -->

            <ul class="navbar__menu">
                <li><a href="?controller=dashboard&action=mostrarDashboard" class="navbar__link">Inicio</a></li>
                <li class="navbar__item navbar__item--dropdown">
                    <a href="?controller=transaction&action=mostrarTransaccionesUsuario" class="navbar__link navbar__link--dropdown" aria-haspopup="true">
                        Transacciones
                    </a>
                    <ul class="navbar__dropdown" aria-label="Submenú de transacciones">
                        <li>
                            <a href="?controller=transaction&action=mostrarTransaccionesUsuario" class="navbar__dropdown-link">
                                Ver transacciones
                            </a>
                        </li>
                        <li>
                            <a href="?controller=transaction&action=mostrarFormularioCrearTransaccion" class="navbar__dropdown-link">
                                Nueva transacción
                            </a>
                        </li>
                    </ul>
                </li>
                <li><a href="#" class="navbar__link">Herramientas</a></li>
                <li><a href="#" class="navbar__link">Agente IA</a></li>
                <li><a href="#" class="navbar__link">Sobre nosotros</a></li>
                <li><a href="#" class="navbar__link">Contacto</a></li>
                <li><a href="#" class="navbar__link">Perfil</a></li>
            </ul>
        </nav>
    </header>